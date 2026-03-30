<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PanenTrackPlotReportController extends Controller
{
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        
        // Get all plots with batch info (optimized join)
        $plots = DB::table('masterlist as m')
            ->leftJoin('batch as b', function($join) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                    ->on('m.companycode', '=', 'b.companycode'); // ✅ Proper composite key join
            })
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->select([
                'm.plot',
                'm.blok',
                'm.activebatchno',
                'b.id as batch_id',           // ✅ Add surrogate ID
                'b.tanggalpanen',
                'b.lifecyclestatus'
            ])
            ->orderBy('m.blok')
            ->orderBy('m.plot')
            ->get();

        return view('report.panen-track-plot.index', [
            'title' => 'Tracking Panen per Plot',
            'navbar' => 'Report',
            'nav' => 'Tracking Panen per Plot',
            'plots' => $plots
        ]);
    }

    public function getBatches(Request $request)
    {
        $companycode = Session::get('companycode');
        $plot = $request->input('plot');

        if (!$plot) {
            return response()->json([
                'success' => false,
                'message' => 'Plot harus dipilih'
            ]);
        }

        // Get active batch from masterlist
        $activeBatchno = DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('plot', $plot)
            ->value('activebatchno');

        // Get all batches for this plot
        $batches = DB::table('batch as b')
            ->where('b.companycode', $companycode)
            ->where('b.plot', $plot)
            ->whereNotNull('b.tanggalpanen')
            ->select([
                'b.id',                        // ✅ Add surrogate ID
                'b.batchno',
                'b.tanggalpanen',
                'b.batcharea',
                'b.lifecyclestatus',
                'b.kodevarietas',
                'b.closedat',
                DB::raw("IF(b.batchno = '{$activeBatchno}', 1, 0) as is_active")
            ])
            ->orderByRaw("IF(b.batchno = '{$activeBatchno}', 0, 1)")
            ->orderBy('b.tanggalpanen', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'batches' => $batches,
            'active_batchno' => $activeBatchno
        ]);
    }

    public function getData(Request $request)
    {
        $companycode = Session::get('companycode');
        $plot = $request->input('plot');
        $batchno = $request->input('batchno');

        if (!$plot || !$batchno) {
            return response()->json([
                'success' => false,
                'message' => 'Plot dan Batch harus dipilih'
            ]);
        }

        try {
            // ✅ STEP 1: Get batch info with surrogate ID
            $batchInfo = DB::table('batch as b')
                ->join('masterlist as m', function($join) {
                    $join->on('b.plot', '=', 'm.plot')
                        ->on('b.companycode', '=', 'm.companycode'); // ✅ Proper composite key
                })
                ->where('b.companycode', $companycode)
                ->where('b.batchno', $batchno)
                ->select([
                    'b.id as batch_id',        // ✅ Add surrogate ID
                    'b.batchno',
                    'b.plot',
                    'm.blok',
                    'b.tanggalpanen',
                    'b.batcharea',
                    'b.lifecyclestatus as kodestatus',
                    'b.kodevarietas',
                    'b.closedat'
                ])
                ->first();

            if (!$batchInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch tidak ditemukan'
                ]);
            }

            // Calculate date range
            $startDate = Carbon::parse($batchInfo->tanggalpanen);
            $endDate = Carbon::now();

            $panenActivities = ['4.3.3', '4.4.3', '4.5.2', '2.2.2a', '2.2.2b'];

            // STEP 2: Get daily harvest data — panen activities only, grouped by date
            $harvestRows = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
                ->leftJoin('user as u', 'lh.mandorid', '=', 'u.userid')
                ->where('lh.companycode', $companycode)
                ->where('ldp.batchid', $batchInfo->batch_id)
                ->where('lh.approvalstatus', '1')
                ->whereIn('lh.activitycode', $panenActivities)
                ->select([
                    'lh.lkhdate',
                    'lh.lkhno',
                    'lh.mandorid',
                    'u.name as mandor_name',
                    'lh.activitycode',
                    'ldp.luashasil as hc',
                    'ldp.fieldbalancerit',
                    'ldp.fieldbalanceton',
                ])
                ->orderBy('lh.lkhdate')
                ->get();

            // Group by date — sum HC, collect mandors & lkhnos per date
            $harvestData = $harvestRows->groupBy('lkhdate')->map(function ($rows) {
                return (object) [
                    'lkhdate'          => $rows->first()->lkhdate,
                    'hc'               => $rows->sum('hc'),
                    'fieldbalancerit'  => $rows->sum('fieldbalancerit'),
                    'fieldbalanceton'  => $rows->sum('fieldbalanceton'),
                    'lkhno'            => $rows->pluck('lkhno')->unique()->implode(', '),
                    'mandors'          => $rows->map(fn($r) => [
                        'mandorid'    => $r->mandorid,
                        'mandor_name' => $r->mandor_name,
                        'activitycode'=> $r->activitycode,
                        'hc'          => $r->hc,
                    ])->values()->toArray(),
                ];
            });

            // Get surat jalan data grouped by date
            $suratJalanData = DB::table('suratjalanpos as sj')
                ->leftJoin('timbanganpayload as tp', function($join) {
                    $join->on('sj.companycode', '=', 'tp.companycode')
                        ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
                })
                ->where('sj.companycode', $companycode)
                ->where('sj.plot', $plot)
                ->whereNotNull('sj.tanggalcetakpossecurity')
                ->select([
                    DB::raw('DATE(sj.tanggalcetakpossecurity) as tanggal'),
                    DB::raw('COUNT(*) as jumlah_sj'),
                    DB::raw('SUM(tp.netto) as total_netto'),
                    DB::raw('GROUP_CONCAT(sj.suratjalanno ORDER BY sj.suratjalanno SEPARATOR ",") as list_sj')
                ])
                ->groupBy(DB::raw('DATE(sj.tanggalcetakpossecurity)'))
                ->get()
                ->keyBy('tanggal');

            // Calculate finish date
            $finishDate = null;
            $cumulativeHC = 0;
            foreach ($harvestData as $harvest) {
                $cumulativeHC += $harvest->hc;
                if ($cumulativeHC >= $batchInfo->batcharea) {
                    $finishDate = $harvest->lkhdate;
                    break;
                }
            }

            // If finished, use finish date as end date
            if ($finishDate) {
                $endDate = Carbon::parse($finishDate);
            }

            $totalDays = $startDate->diffInDays($endDate) + 1;

            // Build daily timeline
            $timeline = [];
            $cumulativeHC = 0;
            $currentDate = $startDate->copy();
            $dayNumber = 1;

            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                
                $harvest = $harvestData->get($dateStr);
                $sj = $suratJalanData->get($dateStr);

                if ($harvest) {
                    $cumulativeHC += $harvest->hc;
                }

                $remainingArea = $batchInfo->batcharea - $cumulativeHC;

                $timeline[] = [
                    'tanggal'           => $dateStr,
                    'hari_ke'           => $dayNumber,
                    'day_name'          => $currentDate->locale('id')->isoFormat('dddd'),
                    'has_harvest'       => $harvest ? true : false,
                    'hc'                => $harvest ? $harvest->hc : 0,
                    'cumulative_hc'     => $cumulativeHC,
                    'remaining_area'    => max(0, $remainingArea),
                    'field_balance_rit' => $harvest ? $harvest->fieldbalancerit : null,
                    'field_balance_ton' => $harvest ? $harvest->fieldbalanceton : null,
                    'jumlah_sj'         => $sj ? $sj->jumlah_sj : 0,
                    'netto_ton'         => $sj && $sj->total_netto ? $sj->total_netto / 1000 : null,
                    'list_sj'           => $sj ? explode(',', $sj->list_sj) : [],
                    'lkhno'             => $harvest ? $harvest->lkhno : null,
                    'mandors'           => $harvest ? $harvest->mandors : [],
                ];

                $currentDate->addDay();
                $dayNumber++;
            }

            // Calculate summary
            $totalHarvestDays = $harvestData->count();
            $ongoingDays = 0;
            $today = Carbon::now()->format('Y-m-d');
            if ($totalDays > 0 && !$harvestData->has($today) && $endDate->format('Y-m-d') >= $today) {
                $ongoingDays = 1;
            }

            $totalSkippedDays = $totalDays - $totalHarvestDays - $ongoingDays;
            $totalHC = $harvestData->sum('hc');
            $totalSJ = $suratJalanData->sum('jumlah_sj');
            $totalNettoKg = $suratJalanData->sum('total_netto');
            $totalNettoTon = $totalNettoKg / 1000;
            $totalFieldBalanceRit = $harvestData->sum('fieldbalancerit');
            $totalFieldBalanceTon = $harvestData->sum('fieldbalanceton');
            $remainingArea = max(0, $batchInfo->batcharea - $totalHC);
            $percentageComplete = $batchInfo->batcharea > 0 ? ($totalHC / $batchInfo->batcharea) * 100 : 0;
            $avgHCPerDay = $totalHarvestDays > 0 ? $totalHC / $totalHarvestDays : 0;
            $estimatedDaysRemaining = $avgHCPerDay > 0 ? ceil($remainingArea / $avgHCPerDay) : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'batch_info' => [
                        'batchno' => $batchInfo->batchno,
                        'plot' => $batchInfo->plot,
                        'blok' => $batchInfo->blok,
                        'tanggalpanen' => $batchInfo->tanggalpanen,
                        'tanggalselesai' => $finishDate,
                        'batcharea' => $batchInfo->batcharea,
                        'kodestatus' => $batchInfo->kodestatus,
                        'kodevarietas' => $batchInfo->kodevarietas
                    ],
                    'timeline' => $timeline,
                    'summary' => [
                        'total_days' => $totalDays,
                        'total_harvest_days' => $totalHarvestDays,
                        'total_ongoing_days' => $ongoingDays,
                        'total_skipped_days' => $totalSkippedDays,
                        'total_hc' => $totalHC,
                        'total_sj' => $totalSJ,
                        'total_netto_kg' => $totalNettoKg,
                        'total_netto_ton' => $totalNettoTon,
                        'total_field_balance_rit' => $totalFieldBalanceRit,
                        'total_field_balance_ton' => $totalFieldBalanceTon,
                        'remaining_area' => $remainingArea,
                        'percentage_complete' => $percentageComplete,
                        'avg_hc_per_day' => $avgHCPerDay,
                        'estimated_days_remaining' => $estimatedDaysRemaining
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Error in PanenTrackPlotReport: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}