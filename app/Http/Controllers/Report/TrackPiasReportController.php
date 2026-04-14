<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TrackPiasReportController extends Controller
{
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');

        $currentYear = (int) date('Y');
        $years = array_reverse(range(2020, $currentYear + 1));

        $bloks = DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->distinct()
            ->orderBy('blok')
            ->pluck('blok');

        return view('report.track-pias.index', [
            'title'       => 'Report Track Pias',
            'navbar'      => 'Report',
            'nav'         => 'Track Pias',
            'years'       => $years,
            'bloks'       => $bloks,
            'currentYear' => $currentYear,
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $year  = $request->input('year', date('Y'));
            $bloks = $request->input('bloks', []);

            if (empty($bloks)) {
                return response()->json(['success' => false, 'message' => 'Pilih minimal 1 blok'], 400);
            }

            $plots = DB::table('masterlist as m')
                ->join('batch as b', function ($join) use ($companycode) {
                    $join->on('m.activebatchno', '=', 'b.batchno')
                        ->where('b.companycode', '=', $companycode)
                        ->where('b.isactive', '=', 1);
                })
                ->where('m.companycode', $companycode)
                ->where('m.isactive', 1)
                ->whereIn('m.blok', $bloks)
                ->select([
                    'm.blok', 'm.plot', 'b.batchno',
                    'b.lifecyclestatus', 'b.kodevarietas', 'b.tanggalpanen',
                ])
                ->orderBy('m.blok')
                ->orderBy('m.plot')
                ->get();

            $startDate = "{$year}-01-01";
            $endDate   = "{$year}-12-31";

            $piasActivities = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', function ($join) use ($companycode) {
                    $join->on('ldp.lkhno', '=', 'lh.lkhno')
                        ->on('ldp.companycode', '=', 'lh.companycode');
                })
                ->where('ldp.companycode', $companycode)
                ->where('lh.activitycode', '5.2.1')
                ->where('lh.approvalstatus', '1')
                ->whereBetween('lh.lkhdate', [$startDate, $endDate])
                ->whereIn('ldp.plot', $plots->pluck('plot'))
                ->select(['ldp.plot', 'ldp.batchno', 'lh.lkhdate', 'lh.mandorid', 'ldp.luashasil'])
                ->orderBy('lh.lkhdate')
                ->get();

            $trackData = $this->processTrackData($plots, $piasActivities, $year);

            // Hitung max RON across semua data
            $maxRon = 2; // minimal selalu 2
            foreach ($trackData as $plot) {
                foreach ($plot['months'] as $month) {
                    $ronCount = count($month['rons']);
                    if ($ronCount > $maxRon) {
                        $maxRon = $ronCount;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'year'    => $year,
                'data'    => $trackData,
                'max_ron' => $maxRon,
                'summary' => $this->calculateSummary($trackData, $maxRon),
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting Track Pias data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function processTrackData($plots, $activities, $year)
    {
        $result = [];

        // Group activities by plot and month
        $activitiesByPlot = [];
        foreach ($activities as $activity) {
            $plotKey = $activity->plot;
            $month   = Carbon::parse($activity->lkhdate)->format('Y-m');

            $activitiesByPlot[$plotKey][$month][] = $activity;
        }

        foreach ($plots as $plot) {
            $plotKey    = $plot->plot;
            $monthsData = [];

            for ($m = 1; $m <= 12; $m++) {
                $monthKey        = sprintf('%d-%02d', $year, $m);
                $monthActivities = $activitiesByPlot[$plotKey][$monthKey] ?? [];

                // Ambil unique dates, sorted
                $dates = [];
                foreach ($monthActivities as $act) {
                    $dates[] = Carbon::parse($act->lkhdate)->format('d');
                }
                $dates = array_values(array_unique($dates));

                $monthsData[] = [
                    'month'      => $m,
                    'month_name' => Carbon::create($year, $m, 1)->format('M'),
                    'rons'       => $dates, // array of tanggal: index 0=RON1, 1=RON2, 2=RON3, dst
                    'count'      => count($monthActivities),
                ];
            }

            $result[] = [
                'blok'          => $plot->blok,
                'plot'          => $plot->plot,
                'batchno'       => $plot->batchno,
                'lifecycle'     => $plot->lifecyclestatus,
                'varietas'      => $plot->kodevarietas,
                'tanggal_panen' => $plot->tanggalpanen ? Carbon::parse($plot->tanggalpanen)->format('d/m/Y') : '-',
                'months'        => $monthsData,
            ];
        }

        return $result;
    }

    private function calculateSummary($data, $maxRon)
    {
        $totalPlots = count($data);
        $ronTotals  = array_fill(0, $maxRon, 0); // index 0 = RON1, 1 = RON2, ...

        foreach ($data as $plot) {
            foreach ($plot['months'] as $month) {
                for ($r = 0; $r < $maxRon; $r++) {
                    if (isset($month['rons'][$r])) {
                        $ronTotals[$r]++;
                    }
                }
            }
        }

        $maxPossible = $totalPlots * 12;
        $ronRates = [];
        foreach ($ronTotals as $i => $total) {
            $ronRates[] = $maxPossible > 0 ? round(($total / $maxPossible) * 100, 1) : 0;
        }

        return [
            'total_plots' => $totalPlots,
            'max_ron'     => $maxRon,
            'ron_totals'  => $ronTotals,  // [ron1_total, ron2_total, ron3_total...]
            'ron_rates'   => $ronRates,   // [ron1_%, ron2_%, ron3_%...]
        ];
    }
}