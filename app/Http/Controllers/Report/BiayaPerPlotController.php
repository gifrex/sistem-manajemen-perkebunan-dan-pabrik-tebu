<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class BiayaPerPlotController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }

    /**
     * Halaman utama Report Biaya Per Plot
     */
    public function index()
    {
        $title = "Report Biaya Per Plot";
        $nav = "Biaya Per Plot";
        $companycode = session('companycode');

        // Get list blok yang punya active batch
        $bloks = DB::table('masterlist as m')
            ->join('batch as b', function($join) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                     ->on('m.companycode', '=', 'b.companycode');
            })
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->where('b.isactive', 1)
            ->select('m.blok', DB::raw('COUNT(*) as plot_count'))
            ->groupBy('m.blok')
            ->orderBy('m.blok')
            ->get();

        return view('report.biaya-per-plot.index', compact('title', 'nav', 'bloks'));
    }

    /**
     * Get data biaya per plot berdasarkan blok yang dipilih
     */
    public function getData(Request $request)
    {
        $companycode = session('companycode');
        $selectedBloks = $request->bloks ?? [];

        if (empty($selectedBloks)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal 1 blok'
            ]);
        }

        // Get plot data dengan active batch info
        $plots = DB::table('masterlist as m')
            ->join('batch as b', function($join) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                     ->on('m.companycode', '=', 'b.companycode');
            })
            ->leftJoin('varietas as v', 'b.kodevarietas', '=', 'v.kodevarietas')
            ->where('m.companycode', $companycode)
            ->whereIn('m.blok', $selectedBloks)
            ->where('m.isactive', 1)
            ->where('b.isactive', 1)
            ->select(
                'm.plot',
                'm.blok',
                'b.batchno',
                'b.lifecyclestatus',
                'b.batchdate',
                'b.tanggalulangtahun',
                'b.kodevarietas',
                'v.description as varietas_name',
                'b.batcharea'
            )
            ->orderBy('m.blok')
            ->orderBy('m.plot')
            ->get();

        // Kumpulkan semua batchno dan plot untuk query biaya
        $batchnos = $plots->pluck('batchno')->unique()->toArray();
        $plotCodes = $plots->pluck('plot')->unique()->toArray();

        // Get biaya TK per batch
        $biayaTKPerBatch = $this->getBiayaTKPerBatch($companycode, $batchnos);

        // Get biaya Material per batch
        $biayaMaterialPerBatch = $this->getBiayaMaterialPerBatch($companycode, $batchnos);

        // Get biaya Kontraktor per plot (dari panen/timbangan)
        $biayaKontraktorPerPlot = $this->getBiayaKontraktorPerPlot($companycode, $plotCodes);

        // Assign biaya ke masing-masing plot
        foreach ($plots as $plot) {
            $bn = $plot->batchno;
            $pl = $plot->plot;
            
            $plot->biaya_tk = $biayaTKPerBatch[$bn] ?? 0;
            $plot->biaya_material = $biayaMaterialPerBatch[$bn] ?? 0;
            $plot->biaya_kontraktor = $biayaKontraktorPerPlot[$pl] ?? 0;
            $plot->total_biaya = $plot->biaya_tk + $plot->biaya_material + $plot->biaya_kontraktor;

            // Hitung umur tanaman
            if ($plot->tanggalulangtahun) {
                $tglTanam = Carbon::parse($plot->tanggalulangtahun);
                $now = Carbon::now();
                $diffDays = $tglTanam->diffInDays($now);
                $diffMonths = $tglTanam->diffInMonths($now);
                
                $plot->umur_hari = (int) $diffDays;
                $plot->umur_bulan = (int) $diffMonths;
            } else {
                $plot->umur_hari = null;
                $plot->umur_bulan = null;
            }
        }

        // Summary per blok
        $summaryPerBlok = $plots->groupBy('blok')->map(function($items, $blok) {
            return [
                'blok' => $blok,
                'total_plot' => $items->count(),
                'biaya_tk' => $items->sum('biaya_tk'),
                'biaya_material' => $items->sum('biaya_material'),
                'biaya_kontraktor' => $items->sum('biaya_kontraktor'),
                'total_biaya' => $items->sum('total_biaya'),
            ];
        })->values();

        // Grand total
        $grandTotal = [
            'total_plot' => $plots->count(),
            'biaya_tk' => $plots->sum('biaya_tk'),
            'biaya_material' => $plots->sum('biaya_material'),
            'biaya_kontraktor' => $plots->sum('biaya_kontraktor'),
            'total_biaya' => $plots->sum('total_biaya'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'plots' => $plots,
                'summaryPerBlok' => $summaryPerBlok,
                'grandTotal' => $grandTotal
            ]
        ]);
    }

    /**
     * Detail biaya per batch
     */
    public function show($batchno)
    {
        $title = "Detail Biaya Plot";
        $nav = "Biaya Per Plot";
        $companycode = session('companycode');

        // Get batch info
        $batch = DB::table('batch as b')
            ->leftJoin('varietas as v', 'b.kodevarietas', '=', 'v.kodevarietas')
            ->leftJoin('masterlist as m', function($join) {
                $join->on('b.batchno', '=', 'm.activebatchno')
                     ->on('b.companycode', '=', 'm.companycode');
            })
            ->where('b.companycode', $companycode)
            ->where('b.batchno', $batchno)
            ->select(
                'b.batchno',
                'b.plot',
                'm.blok',
                'b.lifecyclestatus',
                'b.batchdate',
                'b.tanggalulangtahun',
                'b.kodevarietas',
                'v.description as varietas_name',
                'b.batcharea',
                'b.isactive'
            )
            ->first();

        if (!$batch) {
            abort(404, 'Batch tidak ditemukan');
        }

        return view('report.biaya-per-plot.show', compact('title', 'nav', 'batch'));
    }

    /**
     * API: Get detail biaya untuk batch tertentu
     */
    public function getDetail($batchno)
    {
        $companycode = session('companycode');

        // Get batch info
        $batch = DB::table('batch as b')
            ->leftJoin('varietas as v', 'b.kodevarietas', '=', 'v.kodevarietas')
            ->where('b.companycode', $companycode)
            ->where('b.batchno', $batchno)
            ->select(
                'b.batchno',
                'b.plot',
                'b.lifecyclestatus',
                'b.batchdate',
                'b.tanggalulangtahun',
                'b.kodevarietas',
                'v.description as varietas_name',
                'b.batcharea'
            )
            ->first();

        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan']);
        }

        // Get detail LKH dengan proporsi biaya TK
        $lkhDetails = $this->getLKHDetailForBatch($companycode, $batchno);

        // Get detail Material
        $materialDetails = $this->getMaterialDetailForBatch($companycode, $batchno);

        // Get detail Kontraktor (Panen)
        $kontraktorDetails = $this->getKontraktorDetailForBatch($companycode, $batch->plot);

        // Summary
        $summary = [
            'biaya_tk' => collect($lkhDetails)->sum('biaya_proporsional'),
            'biaya_material' => collect($materialDetails)->sum('total_biaya'),
            'biaya_kontraktor' => collect($kontraktorDetails)->sum('total_biaya'),
        ];
        $summary['total'] = $summary['biaya_tk'] + $summary['biaya_material'] + $summary['biaya_kontraktor'];

        return response()->json([
            'success' => true,
            'data' => [
                'batch' => $batch,
                'lkh_details' => $lkhDetails,
                'material_details' => $materialDetails,
                'kontraktor_details' => $kontraktorDetails,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Get LKH detail dengan proporsi untuk batch tertentu
     */
    private function getLKHDetailForBatch($companycode, $batchno)
    {
        // Get semua LKH yang terkait batch ini
        $data = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            ->where('lh.companycode', $companycode)
            ->where('ldp.batchno', $batchno)
            ->select(
                'lh.id as lkhid',
                'lh.lkhno',
                'lh.lkhdate',
                'lh.activitycode',
                'a.activityname',
                'lh.jenistenagakerja',
                'lh.totalworkers',
                'lh.totalupahall',
                'ldp.plot',
                'ldp.luashasil',
                'ldp.luasrkh'
            )
            ->orderBy('lh.lkhdate')
            ->orderBy('lh.lkhno')
            ->get();

        if ($data->isEmpty()) return [];

        // Get total luas per LKH (dari SEMUA plot di LKH)
        $lkhIds = $data->pluck('lkhid')->unique()->toArray();
        $totalLuasPerLKH = DB::table('lkhdetailplot')
            ->whereIn('lkhhdrid', $lkhIds)
            ->select('lkhhdrid', DB::raw('SUM(COALESCE(luashasil, 0)) as total_luas'))
            ->groupBy('lkhhdrid')
            ->pluck('total_luas', 'lkhhdrid')
            ->toArray();

        // Hitung proporsi
        $result = [];
        foreach ($data as $row) {
            $totalLuasLKH = $totalLuasPerLKH[$row->lkhid] ?? 0;
            $proporsi = $totalLuasLKH > 0 ? ($row->luashasil / $totalLuasLKH) : 0;
            $biayaProporsional = $proporsi * ($row->totalupahall ?? 0);

            $result[] = [
                'lkhno' => $row->lkhno,
                'lkhdate' => $row->lkhdate,
                'activitycode' => $row->activitycode,
                'activityname' => $row->activityname,
                'jenistenagakerja' => $row->jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                'totalworkers' => $row->totalworkers,
                'luashasil' => (float) $row->luashasil,
                'total_luas_lkh' => (float) $totalLuasLKH,
                'proporsi' => round($proporsi * 100, 2),
                'totalupahall' => (float) $row->totalupahall,
                'biaya_proporsional' => round($biayaProporsional, 2)
            ];
        }

        return $result;
    }

    /**
     * Get Material detail untuk batch tertentu
     */
    private function getMaterialDetailForBatch($companycode, $batchno)
    {
        $data = DB::table('usemateriallst as uml')
            ->join('lkhhdr as lh', function($join) {
                $join->on('uml.lkhno', '=', 'lh.lkhno')
                     ->on('uml.companycode', '=', 'lh.companycode');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid')
                     ->on('uml.plot', '=', 'ldp.plot');
            })
            ->join('herbisida as h', function($join) {
                $join->on('uml.itemcode', '=', 'h.itemcode')
                     ->on('uml.companycode', '=', 'h.companycode');
            })
            ->where('uml.companycode', $companycode)
            ->where('ldp.batchno', $batchno)
            ->select(
                'uml.lkhno',
                'lh.lkhdate',
                'uml.itemcode',
                'h.itemname',
                'uml.qtydigunakan',
                'h.measure',
                'uml.itemprice',
                DB::raw('COALESCE(uml.itemprice, 0) * COALESCE(uml.qtydigunakan, 0) as total_biaya')
            )
            ->orderBy('lh.lkhdate')
            ->get();

        return $data->toArray();
    }

    /**
     * Get Kontraktor (Panen) detail untuk plot tertentu
     */
    private function getKontraktorDetailForBatch($companycode, $plot)
    {
        // Get harga panen aktif
        $hargaPanen = DB::table('hargapanentebu')
            ->where('companycode', $companycode)
            ->where('active', 1)
            ->first();

        if (!$hargaPanen) return [];

        $data = DB::table('timbanganpayload as t')
            ->join('suratjalanpos as s', function($join) {
                $join->on('t.companycode', '=', 's.companycode')
                     ->on('t.suratjalanno', '=', 's.suratjalanno');
            })
            ->leftJoin('subkontraktor as sk', function($join) {
                $join->on('s.namasubkontraktor', '=', 'sk.id')
                     ->on('s.companycode', '=', 'sk.companycode');
            })
            ->leftJoin('kontraktor as k', function($join) {
                $join->on('sk.kontraktorid', '=', 'k.id')
                     ->on('sk.companycode', '=', 'k.companycode');
            })
            ->where('t.companycode', $companycode)
            ->where('s.plot', $plot)
            ->select(
                't.suratjalanno',
                's.tanggalangkut',
                's.namasupir',
                's.nomorpolisi',
                'k.namakontraktor',
                'sk.namasubkontraktor',
                't.netto',
                't.traf',
                's.muatgl',
                's.kendaraankontraktor',
                's.tebusulit',
                's.langsir'
            )
            ->orderBy('s.tanggalangkut')
            ->get();

        $result = [];
        foreach ($data as $row) {
            $beratBersih = ($row->netto ?? 0) - ($row->traf ?? 0);
            $beratTon = $beratBersih / 1000;

            // Hitung biaya berdasarkan jenis
            $biaya = $this->hitungBiayaKontraktor($row, $beratTon, $hargaPanen);

            $result[] = [
                'suratjalanno' => $row->suratjalanno,
                'tanggalangkut' => $row->tanggalangkut,
                'namasupir' => $row->namasupir,
                'nomorpolisi' => $row->nomorpolisi,
                'namakontraktor' => $row->namakontraktor,
                'namasubkontraktor' => $row->namasubkontraktor,
                'netto_kg' => (float) $row->netto,
                'traf_kg' => (float) $row->traf,
                'berat_bersih_kg' => $beratBersih,
                'berat_ton' => round($beratTon, 3),
                'jenis' => $row->kendaraankontraktor == 1 ? 'GL Kontraktor' : ($row->muatgl == 1 ? 'GL Kebun' : 'Manual'),
                'tebusulit' => $row->tebusulit == 1,
                'langsir' => $row->langsir == 1,
                'total_biaya' => round($biaya, 2)
            ];
        }

        return $result;
    }

    /**
     * Hitung biaya kontraktor per record timbangan
     */
    private function hitungBiayaKontraktor($row, $beratTon, $hargaPanen)
    {
        $biayaTebang = 0;
        $biayaMuat = 0;
        $biayaAngkut = 0;
        $biayaLainnya = 0;

        if ($row->kendaraankontraktor == 1) {
            // GL Kontraktor
            $biayaTebang = $beratTon * ($hargaPanen->glkontraktortebang ?? 0);
            $biayaMuat = $beratTon * ($hargaPanen->glkontraktormuat ?? 0);
            $biayaAngkut = $beratTon * ($hargaPanen->glkontraktorangkutan ?? 0);
            $biayaLainnya += $beratTon * ($hargaPanen->glkontraktorfeekont ?? 0);
            $biayaLainnya += $beratTon * ($hargaPanen->glkontraktorbsm ?? 0);
            if ($row->tebusulit == 1) {
                $biayaLainnya += $beratTon * ($hargaPanen->glkontraktortebusulit ?? 0);
            }
        } else {
            if ($row->muatgl == 1) {
                // GL Kebun
                $biayaTebang = $beratTon * ($hargaPanen->glkebuntebang ?? 0);
                $biayaMuat = $beratTon * ($hargaPanen->glkebunmuat ?? 0);
                $biayaAngkut = $beratTon * ($hargaPanen->glkebunangkutan ?? 0);
                $biayaLainnya += $beratTon * ($hargaPanen->glkebunfeekont ?? 0);
                $biayaLainnya += $beratTon * ($hargaPanen->glkebunbsm ?? 0);
                if ($row->tebusulit == 1) {
                    $biayaLainnya += $beratTon * ($hargaPanen->glkebuntebusulit ?? 0);
                }
            } else {
                // Manual
                $biayaTebang = $beratTon * ($hargaPanen->manualtebang ?? 0);
                $biayaMuat = $beratTon * ($hargaPanen->manualmuat ?? 0);
                $biayaAngkut = $beratTon * ($hargaPanen->manualangkutan ?? 0);
                $biayaLainnya += $beratTon * ($hargaPanen->manualfeekont ?? 0);
                $biayaLainnya += $beratTon * ($hargaPanen->manualbsm ?? 0);
                if ($row->tebusulit == 1) {
                    $biayaLainnya += $beratTon * ($hargaPanen->manualtebusulit ?? 0);
                }
            }
        }

        if ($row->langsir == 1) {
            $biayaLainnya += $beratTon * ($hargaPanen->langsir ?? 0);
        }
        $biayaLainnya += $beratTon * ($hargaPanen->extrafooding ?? 0);

        return $biayaTebang + $biayaMuat + $biayaAngkut + $biayaLainnya;
    }

    /**
     * Get Biaya Tenaga Kerja per Batch
     * Sumber: lkhhdr.totalupahall, dibagi proporsional berdasarkan luashasil
     * Formula: Biaya Plot = (luashasil Plot / total luashasil LKH) × totalupahall
     */
    private function getBiayaTKPerBatch($companycode, $batchnos)
    {
        if (empty($batchnos)) return [];

        // Step 1: Ambil data LKH dengan detail plot dan hitung total luas per LKH
        $data = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
            ->where('lh.companycode', $companycode)
            ->whereIn('ldp.batchno', $batchnos)
            ->whereNotNull('ldp.batchno')
            ->select(
                'ldp.batchno',
                'lh.id as lkhid',
                'lh.lkhno',
                'lh.totalupahall',
                'ldp.luashasil',
                'ldp.plot'
            )
            ->get();

        if ($data->isEmpty()) return [];

        // DEBUG: Log untuk batch tertentu
        $debugBatch = 'BATCH999000520';
        if (in_array($debugBatch, $batchnos)) {
            $debugData = $data->where('batchno', $debugBatch);
            \Log::info("=== DEBUG BIAYA TK untuk {$debugBatch} ===");
            \Log::info("Jumlah row: " . $debugData->count());
            foreach ($debugData as $row) {
                \Log::info("LKH: {$row->lkhno}, Plot: {$row->plot}, Luas: {$row->luashasil}, Upah: {$row->totalupahall}");
            }
        }

        // Step 2: Hitung total luashasil per LKH (dari SEMUA plot di LKH, bukan cuma yang di-filter)
        $lkhIds = $data->pluck('lkhid')->unique()->toArray();
        $totalLuasPerLKH = DB::table('lkhdetailplot')
            ->whereIn('lkhhdrid', $lkhIds)
            ->select('lkhhdrid', DB::raw('SUM(COALESCE(luashasil, 0)) as total_luas'))
            ->groupBy('lkhhdrid')
            ->pluck('total_luas', 'lkhhdrid')
            ->toArray();

        // DEBUG: Log total luas per LKH
        if (in_array($debugBatch, $batchnos)) {
            \Log::info("Total Luas per LKH: " . json_encode($totalLuasPerLKH));
        }

        // Step 3: Hitung biaya proporsional per batch
        $biayaPerBatch = [];

        foreach ($data as $row) {
            $batchno = $row->batchno;
            $lkhid = $row->lkhid;
            $totalupahall = $row->totalupahall ?? 0;
            $luashasil = $row->luashasil ?? 0;
            $totalLuasLKH = $totalLuasPerLKH[$lkhid] ?? 0;

            // Hitung proporsi: (luashasil plot / total luashasil LKH) × totalupahall
            $biayaProporsional = 0;
            if ($totalLuasLKH > 0) {
                $biayaProporsional = ($luashasil / $totalLuasLKH) * $totalupahall;
            }

            // DEBUG: Log perhitungan untuk batch tertentu
            if ($batchno === $debugBatch) {
                $proporsi = $totalLuasLKH > 0 ? ($luashasil / $totalLuasLKH) : 0;
                \Log::info("LKH {$row->lkhno}: ({$luashasil} / {$totalLuasLKH}) x {$totalupahall} = {$biayaProporsional} (proporsi: {$proporsi})");
            }

            // Accumulate per batch
            if (!isset($biayaPerBatch[$batchno])) {
                $biayaPerBatch[$batchno] = 0;
            }
            $biayaPerBatch[$batchno] += $biayaProporsional;
        }

        // DEBUG: Log total per batch
        if (in_array($debugBatch, $batchnos) && isset($biayaPerBatch[$debugBatch])) {
            \Log::info("TOTAL BIAYA TK {$debugBatch}: " . $biayaPerBatch[$debugBatch]);
        }

        return $biayaPerBatch;
    }

    /**
     * Get Biaya Material per Batch
     * Sumber: usemateriallst.itemprice * qtydigunakan
     */
    private function getBiayaMaterialPerBatch($companycode, $batchnos)
    {
        if (empty($batchnos)) return [];

        $data = DB::table('usemateriallst as uml')
            ->join('lkhhdr as lh', function($join) {
                $join->on('uml.lkhno', '=', 'lh.lkhno')
                     ->on('uml.companycode', '=', 'lh.companycode');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid')
                     ->on('uml.plot', '=', 'ldp.plot');
            })
            ->where('uml.companycode', $companycode)
            ->whereIn('ldp.batchno', $batchnos)
            ->whereNotNull('ldp.batchno')
            ->select('ldp.batchno', DB::raw('SUM(COALESCE(uml.itemprice, 0) * COALESCE(uml.qtydigunakan, 0)) as total'))
            ->groupBy('ldp.batchno')
            ->pluck('total', 'batchno')
            ->toArray();

        return $data;
    }

    /**
     * Get Biaya Kontraktor per Plot (Panen)
     * Sumber: timbanganpayload + suratjalanpos + hargapanentebu
     */
    private function getBiayaKontraktorPerPlot($companycode, $plotCodes)
    {
        if (empty($plotCodes)) return [];

        // Get harga panen yang aktif
        $hargaPanen = DB::table('hargapanentebu')
            ->where('companycode', $companycode)
            ->where('active', 1)
            ->first();

        if (!$hargaPanen) {
            return [];
        }

        // Get data timbangan per plot
        $timbanganData = DB::table('timbanganpayload as t')
            ->join('suratjalanpos as s', function($join) {
                $join->on('t.companycode', '=', 's.companycode')
                     ->on('t.suratjalanno', '=', 's.suratjalanno');
            })
            ->leftJoin('trash as tr', function($join) {
                $join->on('t.companycode', '=', 'tr.companycode')
                     ->on('t.suratjalanno', '=', 'tr.suratjalanno');
            })
            ->where('t.companycode', $companycode)
            ->whereIn('s.plot', $plotCodes)
            ->select(
                's.plot',
                't.netto',
                't.traf',
                's.muatgl',
                's.kendaraankontraktor',
                's.tebusulit',
                's.langsir',
                'tr.nettotrash as trash_percentage'
            )
            ->get();

        // Hitung biaya per plot
        $biayaPerPlot = [];
        
        foreach ($timbanganData as $row) {
            $plot = $row->plot;
            
            // Hitung berat bersih (kg)
            $beratBersih = ($row->netto ?? 0) - ($row->traf ?? 0);
            $beratTon = $beratBersih / 1000; // Convert ke ton
            
            // Tentukan jenis biaya berdasarkan kondisi
            $biayaTebang = 0;
            $biayaMuat = 0;
            $biayaAngkut = 0;
            $biayaLainnya = 0;

            // Cek apakah pakai GL Kontraktor atau Manual
            if ($row->kendaraankontraktor == 1) {
                // GL Kontraktor
                $biayaTebang = $beratTon * ($hargaPanen->glkontraktortebang ?? 0);
                $biayaMuat = $beratTon * ($hargaPanen->glkontraktormuat ?? 0);
                $biayaAngkut = $beratTon * ($hargaPanen->glkontraktorangkutan ?? 0);
                
                // Fee kontraktor
                $biayaLainnya += $beratTon * ($hargaPanen->glkontraktorfeekont ?? 0);
                
                // BSM
                $biayaLainnya += $beratTon * ($hargaPanen->glkontraktorbsm ?? 0);
                
                // Tebu sulit
                if ($row->tebusulit == 1) {
                    $biayaLainnya += $beratTon * ($hargaPanen->glkontraktortebusulit ?? 0);
                }
            } else {
                // Manual / GL Kebun
                if ($row->muatgl == 1) {
                    // GL Kebun
                    $biayaTebang = $beratTon * ($hargaPanen->glkebuntebang ?? 0);
                    $biayaMuat = $beratTon * ($hargaPanen->glkebunmuat ?? 0);
                    $biayaAngkut = $beratTon * ($hargaPanen->glkebunangkutan ?? 0);
                    $biayaLainnya += $beratTon * ($hargaPanen->glkebunfeekont ?? 0);
                    $biayaLainnya += $beratTon * ($hargaPanen->glkebunbsm ?? 0);
                    
                    if ($row->tebusulit == 1) {
                        $biayaLainnya += $beratTon * ($hargaPanen->glkebuntebusulit ?? 0);
                    }
                } else {
                    // Manual
                    $biayaTebang = $beratTon * ($hargaPanen->manualtebang ?? 0);
                    $biayaMuat = $beratTon * ($hargaPanen->manualmuat ?? 0);
                    $biayaAngkut = $beratTon * ($hargaPanen->manualangkutan ?? 0);
                    $biayaLainnya += $beratTon * ($hargaPanen->manualfeekont ?? 0);
                    $biayaLainnya += $beratTon * ($hargaPanen->manualbsm ?? 0);
                    
                    if ($row->tebusulit == 1) {
                        $biayaLainnya += $beratTon * ($hargaPanen->manualtebusulit ?? 0);
                    }
                }
            }

            // Langsir
            if ($row->langsir == 1) {
                $biayaLainnya += $beratTon * ($hargaPanen->langsir ?? 0);
            }

            // Extra fooding
            $biayaLainnya += $beratTon * ($hargaPanen->extrafooding ?? 0);

            $totalBiaya = $biayaTebang + $biayaMuat + $biayaAngkut + $biayaLainnya;

            // Accumulate per plot
            if (!isset($biayaPerPlot[$plot])) {
                $biayaPerPlot[$plot] = 0;
            }
            $biayaPerPlot[$plot] += $totalBiaya;
        }

        return $biayaPerPlot;
    }
}