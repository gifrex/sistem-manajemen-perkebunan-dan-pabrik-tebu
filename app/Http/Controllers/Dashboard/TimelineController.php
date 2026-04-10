<?php
namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\usematerialhdr;
use App\Models\MasterData\Batch;
use Arr;


class TimelineController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Dashboard',
        ]);

    } 
    public function index()
    {
      $title = "Dashboard Timeline";
      $nav = "Timeline";

      return view('dashboard.timeline.index')->with([
        'title' => $title,
        'nav'   => $nav
      ]);
    }
   
     
    public function plotQuery(Request $request)
    {
        $companyCode = session('companycode');
        $plot = $request->get('plot');

        if (!$plot) {
            return response()->json(['success' => false, 'message' => 'Plot tidak ditemukan.'], 422);
        }

        // Ambil activebatchno dari masterlist untuk tanda "aktif"
        $activeBatchNo = DB::table('masterlist')
            ->where('companycode', $companyCode)
            ->where('plot', $plot)
            ->value('activebatchno');

        // Semua batch history untuk plot ini
        $batches = DB::table('batch as b')
            ->where('b.companycode', $companyCode)
            ->where('b.plot', $plot)
            ->select(
                'b.batchno',
                'b.batcharea',
                'b.lifecyclestatus',
                'b.batchdate',
                'b.tanggalpanen',
                DB::raw('DATEDIFF(CURDATE(), b.batchdate) as umur_hari'),
                DB::raw('TIMESTAMPDIFF(MONTH, b.batchdate, CURDATE()) as umur_bulan')
            )
            ->orderByDesc('b.batchdate')
            ->get()
            ->map(function ($b) use ($activeBatchNo, $plot) {
                $b->plot      = $plot;
                $b->is_active = ($b->batchno === $activeBatchNo);
                return $b;
            });

        // Map sub-kode ke parent (sama dengan $activityGrouping di plot())
        $activityGrouping = [
            '2.1.11' => ['2.1.11a', '2.1.11b', '2.1.12'],
            '2.2.7'  => ['2.2.7a', '2.2.7b'],
            '3.1.1'  => ['3.1.1a', '3.1.1b'],
            '3.1.2'  => ['3.1.2a', '3.1.2b'],
            '3.1.5'  => ['3.1.5a', '3.1.5b'],
            '3.2.4'  => ['3.2.4a', '3.2.4b'],
            '3.2.5'  => ['3.2.5a', '3.2.5b'],
            '3.2.6'  => ['3.2.6a', '3.2.6b'],
        ];
        // Buat reverse map: sub-kode -> parent
        $subToParent = [];
        foreach ($activityGrouping as $parent => $subs) {
            foreach ($subs as $sub) {
                $subToParent[$sub] = $parent;
            }
        }

        // Semua aktivitas di semua batch untuk plot ini
        $activities = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function ($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companyCode)
            ->where('ldp.plot', $plot)
            ->select(
                'lh.activitycode',
                'lh.lkhno',
                'lh.lkhdate',
                'ldp.batchno',
                'ldp.luashasil'
            )
            ->orderBy('ldp.batchno', 'desc')
            ->orderBy('lh.lkhdate', 'desc')
            ->get()
            ->map(function ($r) use ($subToParent) {
                // normalisasi sub-kode ke parent
                $r->activitycode = $subToParent[$r->activitycode] ?? $r->activitycode;
                return $r;
            });

        // Batch aktif untuk info ringkas di header modal
        $activeBatch = $batches->firstWhere('is_active', true);

        // Panen: SuratJalan + timbangan per batchno, dan estimasi ton dari fieldbalanceton
        $suratJalanData = collect();
        $estimasiTonPerBatch = collect();
        $crop = $request->get('crop', 'pc');
        if ($crop === 'p') {
            $suratJalanData = DB::table('suratjalanpos as sj')
                ->leftJoin('timbanganpayload as tp', function ($j) {
                    $j->on('sj.companycode', '=', 'tp.companycode')
                      ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
                })
                ->where('sj.companycode', $companyCode)
                ->where('sj.plot', $plot)
                ->select(
                    'sj.suratjalanno',
                    'sj.tanggalangkut',
                    'sj.tanggaltebang',
                    'sj.nomorpolisi',
                    'sj.namakontraktor',
                    DB::raw('COALESCE(tp.netto, 0) as netto'),
                    DB::raw('COALESCE(tp.bruto, 0) as bruto'),
                    DB::raw('CASE WHEN tp.netto IS NOT NULL THEN 1 ELSE 0 END as sudah_timbang')
                )
                ->orderBy('sj.tanggalangkut', 'desc')
                ->get();

            // Estimasi ton (fieldbalanceton) per batchno dari panen activities
            $estimasiTonPerBatch = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', function ($j) {
                    $j->on('ldp.lkhno', '=', 'lh.lkhno')
                      ->on('ldp.companycode', '=', 'lh.companycode');
                })
                ->where('ldp.companycode', $companyCode)
                ->where('ldp.plot', $plot)
                ->whereIn('lh.activitycode', ['4.3.3', '4.4.3', '4.5.2'])
                ->select(
                    'ldp.batchno',
                    DB::raw('COALESCE(SUM(ldp.fieldbalanceton), 0) as total_estimasi_ton'),
                    DB::raw('COALESCE(SUM(ldp.luashasil), 0) as total_luas_panen')
                )
                ->groupBy('ldp.batchno')
                ->get()
                ->keyBy('batchno');
        }

        return response()->json([
            'success'            => true,
            'batch'              => $activeBatch,
            'batches'            => $batches,
            'active_batchno'     => $activeBatchNo,
            'activities'         => $activities,
            'surat_jalan'        => $suratJalanData,
            'estimasi_ton_batch' => $estimasiTonPerBatch,
        ]);
    }


    public function plot(Request $request)
    {
            $companyCode = session('companycode');
            $fillFilter  = $request->get('fill', 'all');
            $cropType    = $request->get('crop', 'pc');
            
            $tab      = $request->get('tab', 'table');  // table|map (dari view)
            $isExport = $request->get('export') === 'excel';

            $plotHeaders = DB::table('batch as b')  // ✅ GANTI: Mulai dari batch
            ->join('masterlist as m', function($join) {
                $join->on('b.batchno', '=', 'm.activebatchno')
                    ->on('b.companycode', '=', 'm.companycode');
            })
            ->where('b.companycode', $companyCode)
            ->where('b.isactive', 1)  // ✅ FILTER: Hanya batch aktif
            ->select(
                'b.plot',  // ✅ Plot dari batch
                'b.batcharea',  // ✅ Luas dari batch
                'b.lifecyclestatus',
                'b.batchdate',
                'b.tanggalpanen',
                'b.isactive', 
                DB::raw('DATEDIFF(CURDATE(), b.batchdate) as umur_hari'),
                DB::raw('TIMESTAMPDIFF(MONTH, b.batchdate, CURDATE()) as umur_bulan')
            )
            ->orderBy('b.plot')
            ->get();

        // ✅ Activity map DAN grouping berdasarkan crop type
        if ($cropType === 'p') {
            $activityMap = [
                '4.2.2'  => 'ZPK',
                '4.3.3'  => 'Pengangkutan (P.Manual)',
                '4.4.3'  => 'Pengangkutan (P.Semi)',
                '4.5.2'  => 'Pengangkutan (P.Mekanis)',
            ];
        } else { // pc (default)
            $activityMap = [
                '2.1.5'  => 'Brushing',
                '2.1.3'  => 'Soil sampling',
                '2.1.6'  => 'Lime applicating',
                '2.1.7'  => 'Ploughing I',
                '2.1.8'  => 'Harrowing I',
                '2.1.9'  => 'Ploughing II',
                '2.1.10' => 'Harrowing II',
                '2.1.11' => 'Ridging & Basalt dressing',
                '2.2.4'  => 'Seed placing',
                '2.2.6'  => 'Fungicide applicating',
                '2.2.7'  => 'Covering',
                '2.3.2'  => 'Post-covering irrigating',
                '3.1.1'  => 'Pre Emergence',
                '3.1.4'  => 'Cultivating',
                '3.1.5'  => 'Top dress fertilizing',
                '3.1.6'  => 'Weeding I',
                '3.1.8'  => 'Weeding II',
                '3.1.7'  => 'Post Emergence I',
                '3.1.9'  => 'Post Emergence II',
                '3.1.2'  => 'Late Pre Emergence',
                //rc
                '3.2.1'  => 'Trash Mulcher',
                '3.2.2'  => 'Cultivating',
                '3.2.4'  => 'Single dress fertilizing',
                '3.2.5'  => 'Pre Emergence',
                '3.2.7'  => 'Cultivating II',
                // '3.2.8'  => 'Hand Weeding I',
                // '3.2.10' => 'Hand Weeding II',
                // '3.2.9'  => 'Post Emergence I',
                // '3.2.11' => 'Post Emergence II',
                '3.2.6'  => 'Late Pre Emergence',
            ];
        }
            //validasi
            $activityFilter = $request->get('activity', 'all');
            if ($activityFilter !== 'all' && !array_key_exists($activityFilter, $activityMap)) {
                $activityFilter = 'all';
            }
            
            // dd([
            //     'FULL_URL' => $request->fullUrl(),
            //     'QUERY' => $request->query(),
            //     'tab_raw' => $request->query('tab'),
            //     'tab_var' => $tab,
            //     'activity' => $activityFilter,
            //     'crop' => $cropType,
            //   ]);

            // ✅ Activity yang perlu digabung (berlaku untuk semua crop type)
            $activityGrouping = [
                '2.1.11' => ['2.1.11a', '2.1.11b','2.1.12'],
                '2.2.7'  => ['2.2.7a', '2.2.7b'],
                '3.1.1'  => ['3.1.1a', '3.1.1b'],
                '3.1.2'  => ['3.1.2a', '3.1.2b'],
                '3.1.5'  => ['3.1.5a', '3.1.5b'],
                '3.2.4'  => ['3.2.4a', '3.2.4b'],
                '3.2.5'  => ['3.2.5a', '3.2.5b'],
                '3.2.6'  => ['3.2.6a', '3.2.6b'],
            ];

            // ✅ Buat daftar semua activity codes (termasuk yang dipecah)
            $allActivityCodes = [];
            foreach (array_keys($activityMap) as $mainCode) {
                if (isset($activityGrouping[$mainCode])) {
                    $allActivityCodes = array_merge($allActivityCodes, $activityGrouping[$mainCode]);
                } else {
                    $allActivityCodes[] = $mainCode;
                }
            }
 
        // ✅ Query 1: Aggregate untuk total LUAS dan AVG PERCENTAGE
        $activityDataRaw = DB::table('lkhdetailplot as ldp')
        ->join('lkhhdr as lh', function($join) {
            $join->on('ldp.lkhno', '=', 'lh.lkhno')
                ->on('ldp.companycode', '=', 'lh.companycode'); 
        })
        ->join('masterlist as m', function($join) {
            $join->on('ldp.plot', '=', 'm.plot')
                ->on('ldp.companycode', '=', 'm.companycode')
                ;
        })
        ->join('batch as b', function($join) {
            $join->on('m.activebatchno', '=', 'b.batchno')
                ->on('m.companycode', '=', 'b.companycode')
                ->where('b.isactive', '=', 1);  // ✅ FILTER: Batch harus aktif
        })
        ->where('ldp.companycode', $companyCode)
        ->whereRaw('ldp.batchno = m.activebatchno')  // ✅ FILTER: Hanya LKH dari batch aktif
        ->whereIn('lh.activitycode', $allActivityCodes)
        ->select(
            'ldp.plot', 
            'lh.activitycode', 
            DB::raw('SUM(ldp.luashasil) as total_luas'),
            DB::raw('LEAST((SUM(ldp.luashasil) / MAX(b.batcharea)) * 100, 100) as avg_percentage'),  // ✅ Pakai batcharea
            DB::raw('MAX(lh.lkhdate) as tanggal_terbaru')
        )
        ->groupBy('ldp.plot', 'lh.activitycode')
        ->get();

        // ✅ Query 2: Detail per LKH dengan persentase
        $activityDetailRaw = DB::table('lkhdetailplot as ldp')
        ->join('lkhhdr as lh', function($join) {
            $join->on('ldp.lkhno', '=', 'lh.lkhno')
                ->on('ldp.companycode', '=', 'lh.companycode'); // 
        })
        ->join('masterlist as m', function($join) {
            $join->on('ldp.plot', '=', 'm.plot')
                ->on('ldp.companycode', '=', 'm.companycode')
                ;
        })
        ->join('batch as b', function($join) {
            $join->on('m.activebatchno', '=', 'b.batchno')
                ->on('m.companycode', '=', 'b.companycode')
                ->where('b.isactive', '=', 1);  // ✅ FILTER: Batch harus aktif
        })
        ->where('ldp.companycode', $companyCode)
        ->whereRaw('ldp.batchno = m.activebatchno')  // ✅ FILTER: Hanya LKH dari batch aktif
        ->whereIn('lh.activitycode', $allActivityCodes)
        ->select(
            'ldp.plot', 
            'lh.activitycode',
            'lh.lkhno',
            'ldp.luashasil',
            'lh.lkhdate',
            DB::raw('LEAST((ldp.luashasil / b.batcharea) * 100, 100) as percentage')  // ✅ Pakai batcharea
        )
        ->orderBy('ldp.plot')
        ->orderBy('lh.activitycode')
        ->orderBy('lh.lkhdate', 'desc')
        ->get();

        // ✅ Group detail by plot & activity dengan persentase
        $lkhDetails = [];
        foreach ($activityDetailRaw as $detail) {
        $lkhDetails[$detail->plot][$detail->activitycode][] = [
            'lkhno' => $detail->lkhno,
            'luas_hasil' => (float)$detail->luashasil,
            'tanggal' => $detail->lkhdate,
            'percentage' => (float)$detail->percentage
        ];
        }


        foreach ($lkhDetails as $plotCode => $byAct) {
            foreach ($activityGrouping as $mainCode => $subCodes) {
                $merged = [];
                foreach ($subCodes as $sub) {
                    if (!empty($lkhDetails[$plotCode][$sub])) {
                        $merged = array_merge($merged, $lkhDetails[$plotCode][$sub]);
                    }
                }
                if (!empty($merged)) {
                    usort($merged, fn($a,$b) => strcmp($b['tanggal'], $a['tanggal']));
                    $lkhDetails[$plotCode][$mainCode] = $merged;
                }
            }
        }

            // ✅ Gabungkan activity yang dipecah
            $activityData = collect();
            
            foreach ($plotHeaders as $plot) {
                $plotActivities = collect();
                
                foreach (array_keys($activityMap) as $mainCode) {
                    if (isset($activityGrouping[$mainCode])) {
                        $subCodes = $activityGrouping[$mainCode];
                        
                        $combinedLuas = 0;
                        $latestDate   = null;

                        foreach ($subCodes as $subCode) {
                            $subActivity = $activityDataRaw->first(function($item) use ($plot, $subCode) {
                                return $item->plot === $plot->plot && $item->activitycode === $subCode;
                            });

                            if ($subActivity) {
                                $combinedLuas += (float)$subActivity->total_luas;

                                if (!empty($subActivity->tanggal_terbaru)) {
                                    if (!$latestDate || $subActivity->tanggal_terbaru > $latestDate) {
                                        $latestDate = $subActivity->tanggal_terbaru;
                                    }
                                }
                            }
                        }

                        if ($combinedLuas > 0) {
                            $batchArea = (float)($plot->batcharea ?? 0);
                            $pct = $batchArea > 0 ? min(($combinedLuas / $batchArea) * 100, 100) : 0;

                            $plotActivities->put($mainCode, (object)[
                                'activitycode' => $mainCode,
                                'total_luas' => $combinedLuas,
                                'avg_percentage' => $pct,
                                'tanggal_terbaru' => $latestDate
                            ]);
                        }


                    } else {
                        $activity = $activityDataRaw->first(function($item) use ($plot, $mainCode) {
                            return $item->plot === $plot->plot && $item->activitycode === $mainCode;
                        });
                        
                        if ($activity) {
                            $plotActivities->put($mainCode, $activity);
                        }
                    }
                }
                
                if ($plotActivities->isNotEmpty()) {
                    $activityData->put($plot->plot, $plotActivities);
                }
            }

            // ✅ Filter plot hanya untuk TAB TABLE
            if ($activityFilter !== 'all' && $tab !== 'map') {
                $plotHeaders = $plotHeaders->filter(function($plot) use ($activityData, $activityFilter) {
                    return $activityData->has($plot->plot) &&
                        $activityData->get($plot->plot)->has($activityFilter);
                });
            }


            $filteredPlots = $plotHeaders->pluck('plot')->toArray();

            // ✅ Query map data (tetap sama)
            $plotDataForMap = DB::table('testgpslst as a')
                ->leftJoin('testgpshdr as d', 'a.plot', '=', 'd.plot')
                ->where('a.companycode', $companyCode)
                ->whereIn('a.plot', $filteredPlots)
                ->select('a.plot', 'a.latitude', 'a.longitude', 'd.centerlatitude', 'd.centerlongitude')
                ->get();
            
            // ✅ Panen: query tonase (realisasi netto timbangan) & estimasi ton (fieldbalanceton LKH)
            $tonasePerPlot      = collect();
            $estimasiTonPerPlot = collect();
            if ($cropType === 'p') {
                $tonasePerPlot = DB::table('suratjalanpos as sj')
                    ->leftJoin('timbanganpayload as tp', function ($j) {
                        $j->on('sj.companycode', '=', 'tp.companycode')
                          ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
                    })
                    ->where('sj.companycode', $companyCode)
                    ->whereIn('sj.plot', $filteredPlots)
                    ->select(
                        'sj.plot',
                        DB::raw('COUNT(sj.suratjalanno) as total_rit'),
                        DB::raw('SUM(CASE WHEN tp.netto IS NOT NULL THEN 1 ELSE 0 END) as sudah_timbang'),
                        DB::raw('COALESCE(SUM(tp.netto), 0) as total_netto_kg')
                    )
                    ->groupBy('sj.plot')
                    ->get()
                    ->keyBy('plot');

                $estimasiTonPerPlot = DB::table('lkhdetailplot as ldp')
                    ->join('lkhhdr as lh', function ($j) {
                        $j->on('ldp.lkhno', '=', 'lh.lkhno')
                          ->on('ldp.companycode', '=', 'lh.companycode');
                    })
                    ->join('masterlist as m', function ($j) {
                        $j->on('ldp.plot', '=', 'm.plot')
                          ->on('ldp.companycode', '=', 'm.companycode');
                    })
                    ->where('ldp.companycode', $companyCode)
                    ->whereRaw('ldp.batchno = m.activebatchno')
                    ->whereIn('lh.activitycode', ['4.3.3', '4.4.3', '4.5.2'])
                    ->whereIn('ldp.plot', $filteredPlots)
                    ->select(
                        'ldp.plot',
                        DB::raw('COALESCE(SUM(ldp.fieldbalanceton), 0) as total_estimasi_ton')
                    )
                    ->groupBy('ldp.plot')
                    ->get()
                    ->keyBy('plot');
            }

            // ✅ Query: tanggal terakhir panen LKH dan trash mulcher per plot (untuk alert timeline)
            $panenLastDatePerPlot = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', function ($j) {
                    $j->on('ldp.lkhno', '=', 'lh.lkhno')
                      ->on('ldp.companycode', '=', 'lh.companycode');
                })
                ->join('masterlist as m', function ($j) {
                    $j->on('ldp.plot', '=', 'm.plot')
                      ->on('ldp.companycode', '=', 'm.companycode');
                })
                ->where('ldp.companycode', $companyCode)
                ->whereRaw('ldp.batchno = m.activebatchno')
                ->whereIn('lh.activitycode', ['4.3.3', '4.4.3', '4.5.2'])
                ->whereIn('ldp.plot', $filteredPlots)
                ->select('ldp.plot', DB::raw('MAX(lh.lkhdate) as last_panen_date'))
                ->groupBy('ldp.plot')
                ->get()->keyBy('plot');

            $trashMulcherLastDatePerPlot = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', function ($j) {
                    $j->on('ldp.lkhno', '=', 'lh.lkhno')
                      ->on('ldp.companycode', '=', 'lh.companycode');
                })
                ->join('masterlist as m', function ($j) {
                    $j->on('ldp.plot', '=', 'm.plot')
                      ->on('ldp.companycode', '=', 'm.companycode');
                })
                ->where('ldp.companycode', $companyCode)
                ->whereRaw('ldp.batchno = m.activebatchno')
                ->where('lh.activitycode', '3.2.1')
                ->whereIn('ldp.plot', $filteredPlots)
                ->select('ldp.plot', DB::raw('MAX(lh.lkhdate) as last_trash_date'))
                ->groupBy('ldp.plot')
                ->get()->keyBy('plot');

            // ✅ GABUNG: Process plotHeadersForMap + plotActivityDetails sekaligus
            $plotHeadersForMap = [];
            $plotActivityDetails = [];
            
            foreach ($filteredPlots as $plotCode) {
                // Ambil center coordinates dari plotDataForMap
                $centerData = $plotDataForMap->firstWhere('plot', $plotCode);
                
                if ($centerData) {
                    // Data untuk map markers
                    $plotHeadersForMap[] = (object)[
                        'plot' => $plotCode,
                        'centerlatitude' => $centerData->centerlatitude,
                        'centerlongitude' => $centerData->centerlongitude
                    ];
                }
                
                // Data untuk activity details
        $plotInfo = $plotHeaders->firstWhere('plot', $plotCode);
        $activities = $activityData->get($plotCode);
        $luasRkh = $plotInfo->batcharea ?? 0;  // ✅ GANTI: Pakai batcharea (bukan luasarea)

        // ✅ Pindahkan ke luar if untuk efisiensi
        $lifecycleStatus = $plotInfo->lifecyclestatus ?? '-';
        $umurHari = $plotInfo->umur_hari ?? 0;
        $umurBulan = $plotInfo->umur_bulan ?? 0;

        $hasPanen  = !empty($plotInfo->tanggalpanen);
        $lastPanen = $plotInfo->tanggalpanen ?? null;

        $match = 1;
        if ($activityFilter !== 'all') {
            $match = ($activities && $activities->has($activityFilter)) ? 1 : 0;
        }

        if ($activities && $luasRkh > 0) {
            $activityList = [];
            $totalPercentage = 0;
            $totalLuasHasil = 0;
            $activityCount = 0;
            $allComplete = true;
            $hasActivity = false;
            
            foreach ($activities as $actCode => $act) {
                $luasHasil  = (float)($act->total_luas ?? 0);
                $percentage = (float)($act->avg_percentage ?? 0); // ✅ pakai % luas (sudah clamp max 100)
                $lkhList    = $lkhDetails[$plotCode][$actCode] ?? [];
            
                $activityList[] = [
                    'code'        => $actCode,
                    'label'       => $activityMap[$actCode] ?? $actCode,
                    'luas_hasil'  => $luasHasil,
                    'percentage'  => $percentage,
                    'tanggal'     => $act->tanggal_terbaru ?? null,
                    'lkh_details' => $lkhList,
                ];
            
                $totalPercentage += $percentage;
                if ($actCode !== '4.2.2') $totalLuasHasil += $luasHasil;
                $activityCount++;
                $hasActivity = true;
            
                if ($percentage < 100) $allComplete = false;
            }
            
            $avgPercentage = $activityCount > 0 ? ($totalPercentage / $activityCount) : 0;
            // $avgPercentage = $activityCount > 0 ? (($totalPercentage / $activityCount) * 100) : 0;
            
            if (!$hasActivity || $avgPercentage == 0) {
                $markerColor = 'black';
            } elseif ($allComplete) {
                $markerColor = 'green';
            } else {
                $markerColor = 'orange';
            }

            if ($tab === 'map' && $activityFilter !== 'all' && $match === 0) {
                $markerColor = 'black'; // atau '#6b7280'
            }

            // tambahan persentase realisasi — filter activityMap sesuai status plot
            $isRcPlot = str_starts_with(strtoupper($lifecycleStatus), 'RC');
            $isPcPlot = strtoupper($lifecycleStatus) === 'PC';

            $relevantCodes = array_filter(array_keys($activityMap), function($code) use ($isRcPlot) {
                $isRcActivity = str_starts_with($code, '3.2');
                return $isRcPlot ? $isRcActivity : !$isRcActivity;
            });

            $stageCount = count($relevantCodes);
            $doneCount  = 0;

            foreach ($relevantCodes as $code) {
                $a = $activities?->get($code);
                $pct = (float)($a->avg_percentage ?? 0);
                if ($pct >= 100) $doneCount++;
            }

            $stagePct = $stageCount > 0 ? ($doneCount / $stageCount) * 100 : 0;
            
            $plotActivityDetails[$plotCode] = [
                'activities' => $activityList,
                'stage_total' => $stageCount,
                'stage_done' => $doneCount,
                'stage_percentage' => $stagePct,
                'avg_percentage' => $avgPercentage,
                'marker_color' => $markerColor,
                'luas_rkh' => $luasRkh,
                'total_luas_hasil' => $totalLuasHasil,
                'lifecyclestatus' => $lifecycleStatus,
                'umur_hari' => $umurHari,
                'umur_bulan' => $umurBulan,
                'is_panen'                 => $hasPanen ? 1 : 0,
                'tanggal_panen_terakhir'   => $lastPanen,
                'is_match' => $match,
                // timeline alert data
                'last_panen_lkh_date'   => $panenLastDatePerPlot->get($plotCode)?->last_panen_date ?? null,
                'last_trash_mulcher_date' => $trashMulcherLastDatePerPlot->get($plotCode)?->last_trash_date ?? null,
                // panen tonase
                'total_rit'       => (int)($tonasePerPlot->get($plotCode)?->total_rit ?? 0),
                'sudah_timbang'   => (int)($tonasePerPlot->get($plotCode)?->sudah_timbang ?? 0),
                'total_netto_ton' => round((float)($tonasePerPlot->get($plotCode)?->total_netto_kg ?? 0) / 1000, 2),
                'estimasi_ton'    => round((float)($estimasiTonPerPlot->get($plotCode)?->total_estimasi_ton ?? 0), 2),
            ];
        } else {
            $markerColor = 'black';
            if ($tab === 'map' && $activityFilter !== 'all' && $match === 0) {
                $markerColor = 'black'; // atau '#6b7280'
            }

            $plotActivityDetails[$plotCode] = [
                'activities' => [],
                'stage_total' => count($activityMap),
                'stage_done' => 0,
                'stage_percentage' => 0,
                'avg_percentage' => 0,
                'marker_color' => $markerColor,
                'luas_rkh' => $luasRkh,
                'total_luas_hasil' => 0,
                'lifecyclestatus' => $lifecycleStatus,
                'umur_hari' => $umurHari,
                'umur_bulan' => $umurBulan,
                'is_panen' => $hasPanen ? 1 : 0,
                'tanggal_panen_terakhir' => $lastPanen,
                'is_match' => $match,
                // timeline alert data
                'last_panen_lkh_date'    => $panenLastDatePerPlot->get($plotCode)?->last_panen_date ?? null,
                'last_trash_mulcher_date' => $trashMulcherLastDatePerPlot->get($plotCode)?->last_trash_date ?? null,
                // panen tonase
                'total_rit'       => (int)($tonasePerPlot->get($plotCode)?->total_rit ?? 0),
                'sudah_timbang'   => (int)($tonasePerPlot->get($plotCode)?->sudah_timbang ?? 0),
                'total_netto_ton' => round((float)($tonasePerPlot->get($plotCode)?->total_netto_kg ?? 0) / 1000, 2),
                'estimasi_ton'    => round((float)($estimasiTonPerPlot->get($plotCode)?->total_estimasi_ton ?? 0), 2),
            ];
        }
        }

            // Convert array ke collection untuk consistency
            $plotHeadersForMap = collect($plotHeadersForMap);

            // ✅ EXPORT (harus setelah map + plotActivityDetails siap)
        if ($isExport) {

            // =======================
            // A) EXPORT MAP (kalau tab=map)
            // =======================
            if ($tab === 'map') {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Map Detail');

                // Header (plot info + activity + lkh)
                $mapHdr = ['Plot','Blok',
                    'Luas RKH (HA)', $cropType === 'p' ? 'Total Realisasi Panen (HA)' : 'Total Hasil (HA)', 'Progress (%)','Marker',
                    'Status','Umur (Hari)','Umur (Bulan)','Is Panen','Tgl Panen Terakhir',
                    'Tgl Panen LKH Terakhir','Tgl Trash Mulcher Terakhir',
                ];
                if ($cropType === 'p') {
                    $mapHdr = array_merge($mapHdr, ['Estimasi Tonase (Ton)','Realisasi Tonase/Netto (Ton)','% Tonase','Total Rit','Sudah Timbang']);
                }
                $mapHdr = array_merge($mapHdr, [
                    'Activity Code','Activity Label','Activity Luas (HA)','Activity (%)','Activity Tanggal',
                    'LKH No','LKH Tanggal','LKH Luas (HA)','LKH (%)'
                ]);
                $sheet->fromArray([$mapHdr], null, 'A1');

                $lastMapHdrCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($mapHdr));
                $sheet->getStyle("A1:{$lastMapHdrCol}1")->getFont()->setBold(true);
                $sheet->freezePane('A2');

                $row = 2;

                foreach ($plotHeadersForMap as $h) {
                    $plotCode = $h->plot;
                    $blok     = substr($plotCode, 0, 1);

                    $detail = $plotActivityDetails[$plotCode] ?? [
                        'avg_percentage' => 0,
                        'marker_color' => 'black',
                        'luas_rkh' => 0,
                        'total_luas_hasil' => 0,
                        'lifecyclestatus' => '-',
                        'umur_hari' => 0,
                        'is_panen' => 0,
                        'tanggal_panen_terakhir' => null,
                        'activities' => [],
                    ];

                    $tglPanen = $detail['tanggal_panen_terakhir'] ?? null;

                    $lastPanenLkh  = $detail['last_panen_lkh_date'] ?? null;
                    $lastTrashDate = $detail['last_trash_mulcher_date'] ?? null;
                    $base = [
                        $plotCode,
                        $blok,
                        (float)($detail['luas_rkh'] ?? 0),
                        (float)($detail['total_luas_hasil'] ?? 0),
                        (float)($detail['avg_percentage'] ?? 0),
                        $detail['marker_color'] ?? 'black',
                        $detail['lifecyclestatus'] ?? '-',
                        (int)($detail['umur_hari'] ?? 0),
                        (int)($detail['umur_bulan'] ?? 0),
                        (int)($detail['is_panen'] ?? 0),
                        $tglPanen    ? \Carbon\Carbon::parse($tglPanen)->format('Y-m-d')    : '-',
                        $lastPanenLkh  ? \Carbon\Carbon::parse($lastPanenLkh)->format('Y-m-d')  : '-',
                        $lastTrashDate ? \Carbon\Carbon::parse($lastTrashDate)->format('Y-m-d') : '-',
                    ];
                    if ($cropType === 'p') {
                        $estTonMap  = (float)($detail['estimasi_ton'] ?? 0);
                        $realTonMap = (float)($detail['total_netto_ton'] ?? 0);
                        $pctTonMap  = $estTonMap > 0 ? round(($realTonMap / $estTonMap) * 100, 2) : 0;
                        $base = array_merge($base, [
                            $estTonMap, $realTonMap, $pctTonMap,
                            (int)($detail['total_rit'] ?? 0),
                            (int)($detail['sudah_timbang'] ?? 0),
                        ]);
                    }

                    $acts = $detail['activities'] ?? [];

                    // kalau tidak ada activity sama sekali, tetap tulis 1 row plot
                    if (empty($acts)) {
                        $sheet->fromArray([array_merge($base, ['-','-','-','-','-','-','-','-','-'])], null, "A{$row}");
                        $row++;
                        continue;
                    }

                    foreach ($acts as $act) {
                        $actCode  = $act['code'] ?? '';
                        $actLabel = $act['label'] ?? '';
                        $actLuas  = (float)($act['luas_hasil'] ?? 0);
                        $actPct   = (float)($act['percentage'] ?? 0);
                        $actTgl   = !empty($act['tanggal']) ? \Carbon\Carbon::parse($act['tanggal'])->format('Y-m-d') : '-';

                        $lkhList = $act['lkh_details'] ?? [];

                        // kalau activity tidak punya lkh detail, tetap tulis 1 row activity
                        if (empty($lkhList)) {
                            $sheet->fromArray([array_merge($base, [
                                $actCode, $actLabel, $actLuas, $actPct, $actTgl,
                                '-', '-', 0, 0
                            ])], null, "A{$row}");
                            $row++;
                            continue;
                        }

                        // kalau ada lkh, tulis 1 row per lkh
                        foreach ($lkhList as $lkh) {
                            $lkhNo   = $lkh['lkhno'] ?? '';
                            $lkhTgl  = !empty($lkh['tanggal']) ? \Carbon\Carbon::parse($lkh['tanggal'])->format('Y-m-d') : '-';
                            $lkhLuas = (float)($lkh['luas_hasil'] ?? 0);
                            $lkhPct  = (float)($lkh['percentage'] ?? 0);

                            $sheet->fromArray([array_merge($base, [
                                $actCode, $actLabel, $actLuas, $actPct, $actTgl,
                                $lkhNo, $lkhTgl, $lkhLuas, $lkhPct
                            ])], null, "A{$row}");
                            $row++;
                        }
                    }
                }

                // Autosize (A..S, aman)
                foreach (range('A','S') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $filename = "map_detail_{$cropType}_" . now()->format('Ymd_His') . ".xlsx";
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                return response()->stream(function () use ($writer) {
                    $writer->save('php://output');
                }, 200, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                    'Cache-Control' => 'max-age=0',
                ]);
            }


            // =======================
            // B) EXPORT TIMELINE (default tab=table)
            // =======================
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // ========== HEADER ROW ==========
            $col = 'A';
            $sheet->setCellValue($col++ . '1', 'Blok');
            $sheet->setCellValue($col++ . '1', 'Plot');
            $sheet->setCellValue($col++ . '1', 'Saldo (HA)');

            foreach ($activityMap as $code => $label) {
                $sheet->setCellValue($col++ . '1', "$code - $label (HA)");
                $sheet->setCellValue($col++ . '1', "$code (%)");
                $sheet->setCellValue($col++ . '1', "$code (Tanggal)");
            }

            $sheet->setCellValue($col++ . '1', $cropType === 'p' ? 'Realisasi Panen (HA)' : 'Realisasi Tanam (HA)');
            if ($cropType === 'p') {
                $sheet->setCellValue($col++ . '1', 'Estimasi Tonase (Ton)');
                $sheet->setCellValue($col++ . '1', 'Realisasi Tonase/Netto (Ton)');
                $sheet->setCellValue($col++ . '1', '% Tonase');
                $sheet->setCellValue($col++ . '1', 'Total Rit');
                $sheet->setCellValue($col++ . '1', 'Sudah Timbang');
            } else {
                $sheet->setCellValue($col++ . '1', 'Persentase (%)');
            }

            // ========== STYLE HEADER ==========
            $lastCol = $sheet->getHighestColumn();
            $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
            $sheet->getStyle("A1:{$lastCol}1")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('166534');
            $sheet->getStyle("A1:{$lastCol}1")->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->freezePane('A2');

            // ========== DATA ROWS ==========
            $row = 2;
            $blokPlots = $plotHeaders->groupBy(fn($item) => substr($item->plot, 0, 1));

            foreach ($blokPlots as $blok => $plots) {
                foreach ($plots as $index => $plot) {
                    $col = 'A';

                    if ($index === 0) {
                        $sheet->setCellValue($col . $row, $blok);
                    }
                    $col++;

                    $sheet->setCellValue($col++ . $row, $plot->plot);

                    // NB: biar excel numeric, jangan number_format (opsional tapi bagus)
                    $sheet->setCellValue($col++ . $row, $plot->batcharea ? (float)$plot->batcharea : 0);

                    $totalRealisasiPlot = 0;

                    foreach ($activityMap as $activitycode => $label) {
                        $activity = $activityData->get($plot->plot)?->get($activitycode);
                        $value = $activity->total_luas ?? 0;
                        $percentage = $activity->avg_percentage ?? 0;
                        $tanggal = $activity->tanggal_terbaru ?? null;
                        if ($activitycode !== '4.2.2') $totalRealisasiPlot += $value;

                        $sheet->setCellValue($col++ . $row, $value > 0 ? (float)$value : 0);
                        $sheet->setCellValue($col++ . $row, $value > 0 ? (float)$percentage : 0);
                        $sheet->setCellValue($col++ . $row, $tanggal ? \Carbon\Carbon::parse($tanggal)->format('Y-m-d') : '-');
                    }

                        $sheet->setCellValue($col++ . $row, $totalRealisasiPlot > 0 ? (float)$totalRealisasiPlot : 0);

                        if ($cropType === 'p') {
                            $detail = $plotActivityDetails[$plot->plot] ?? [];
                            $estTon   = (float)($detail['estimasi_ton'] ?? 0);
                            $realTon  = (float)($detail['total_netto_ton'] ?? 0);
                            $pctTon   = $estTon > 0 ? round(($realTon / $estTon) * 100, 2) : 0;
                            $sheet->setCellValue($col++ . $row, $estTon);
                            $sheet->setCellValue($col++ . $row, $realTon);
                            $sheet->setCellValue($col++ . $row, $pctTon);
                            $sheet->setCellValue($col++ . $row, (int)($detail['total_rit'] ?? 0));
                            $sheet->setCellValue($col++ . $row, (int)($detail['sudah_timbang'] ?? 0));
                        } else {
                            $stageCount = count($activityMap);
                            $doneCount  = 0;
                            foreach ($activityMap as $code => $label) {
                                $a = $activityData->get($plot->plot)?->get($code);
                                $pct = (float)($a->avg_percentage ?? 0);
                                if ($pct >= 100) $doneCount++;
                            }
                            $stagePct = $stageCount > 0 ? ($doneCount / $stageCount) * 100 : 0;
                            $sheet->setCellValue($col++ . $row, (float)$stagePct);
                        }

                        $row++;
                    }
                }

                $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }

                $filename = "timeline_{$cropType}_" . now()->format('Ymd_His') . ".xlsx";
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                return response()->stream(function () use ($writer) {
                    $writer->save('php://output');
                }, 200, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                    'Cache-Control' => 'max-age=0',
                ]);
            }
            //export excel
                
                return view('dashboard.timeline-plot.index', [
                    'title'             => 'Timeline',
                    'nav'               => 'Timeline',
                    'navbar'            => 'Timeline',
                    'plotHeaders'       => $plotHeaders,
                    'plotHeadersForMap' => $plotHeadersForMap,
                    'activityMap'       => $activityMap,
                    'activityData'      => $activityData,
                    'activityGrouping'  => $activityGrouping,
                    'activityFilter'    => $activityFilter,
                    'plotData'          => $plotDataForMap,
                    'plotActivityDetails'=> $plotActivityDetails,
                    'fillFilter'        => $fillFilter,
                    'cropType'          => $cropType,
                ]);
    }






}
