<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SaldoPanenReportController extends Controller
{
    const PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2'];

    public function index(Request $request)
    {
        $companycode = Session::get('companycode');

        // Mandor list: role jabatan = 7
        $mandors = DB::table('user')
            ->where('companycode', $companycode)
            ->where('idjabatan', 5)
            ->where('isactive', 1)
            ->select('userid', 'name')
            ->orderBy('name')
            ->get();

        return view('report.saldo-panen.index', [
            'title'   => 'Saldo Panen',
            'navbar'  => 'Report',
            'nav'     => 'Saldo Panen',
            'mandors' => $mandors,
        ]);
    }

    public function getData(Request $request)
    {
        $companycode = Session::get('companycode');

        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');
        $mandorId   = $request->input('mandor_id');   // null = all
        $statusFilter = $request->input('status', 'ongoing'); // ongoing|complete|all

        if (!$startDate || !$endDate) {
            return response()->json(['success' => false, 'message' => 'Tanggal harus diisi']);
        }

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();

            // ----------------------------------------------------------------
            // STEP 1: Get all batches where tanggalpanen falls in date range
            //         These are batches that were being harvested in this period
            // ----------------------------------------------------------------
            $batchQuery = DB::table('batch as b')
                ->join('masterlist as m', function ($j) {
                    $j->on('b.plot', '=', 'm.plot')
                      ->on('b.companycode', '=', 'm.companycode');
                })
                ->where('b.companycode', $companycode)
                ->whereNotNull('b.tanggalpanen')
                ->whereBetween('b.tanggalpanen', [$start->toDateString(), $end->toDateString()])
                ->select([
                    'b.id as batch_id',
                    'b.batchno',
                    'b.plot',
                    'm.blok',
                    'b.batcharea',
                    'b.tanggalpanen',
                    'b.lifecyclestatus',
                    'b.isactive',
                    'b.closedat',
                ]);

            $batches = $batchQuery->get();

            if ($batches->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data'    => [],
                    'summary' => $this->emptyHeader(),
                ]);
            }

            $batchIds  = $batches->pluck('batch_id')->toArray();
            $batchMap  = $batches->keyBy('batch_id');

            // ----------------------------------------------------------------
            // STEP 2: Get approved panen LKH detail per batch within date range
            //         Using batchid (surrogate FK) — opsi A
            // ----------------------------------------------------------------
            $harvestRows = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
                ->where('lh.companycode', $companycode)
                ->where('lh.approvalstatus', '1')
                ->whereIn('lh.activitycode', self::PANEN_ACTIVITIES)
                ->whereIn('ldp.batchid', $batchIds)
                ->whereBetween('lh.lkhdate', [$start->toDateString(), $end->toDateString()])
                ->select([
                    'ldp.batchid',
                    'lh.mandorid',
                    DB::raw('SUM(ldp.luashasil) as total_hc'),
                ])
                ->groupBy('ldp.batchid', 'lh.mandorid')
                ->get();

            // Group: batchid → [mandorid → total_hc]
            $harvestByBatch = [];
            foreach ($harvestRows as $row) {
                $harvestByBatch[$row->batchid][$row->mandorid] = (float) $row->total_hc;
            }

            // ----------------------------------------------------------------
            // STEP 3: Get mandor names
            // ----------------------------------------------------------------
            $mandorIds = $harvestRows->pluck('mandorid')->unique()->filter()->toArray();
            $mandorNames = DB::table('user')
                ->whereIn('userid', $mandorIds)
                ->pluck('name', 'userid');

            // ----------------------------------------------------------------
            // STEP 4: Build per-plot rows, keyed by mandorid
            // ----------------------------------------------------------------
            // Structure: mandorid → [ plot_rows ]
            $grouped = [];

            foreach ($batches as $batch) {
                $bid = $batch->batch_id;

                if (!isset($harvestByBatch[$bid])) {
                    // No approved panen LKH for this batch in range
                    // Still show if status filter allows, under "unknown" mandor
                    $mandorRows = [null => 0.0];
                } else {
                    $mandorRows = $harvestByBatch[$bid];
                }

                foreach ($mandorRows as $mid => $totalHC) {
                    $batchArea = (float) $batch->batcharea;

                    // STC = batcharea (all area before today's work)
                    // HC  = total harvested in range by this mandor
                    // BC  = batcharea - total ALL approved HC (not just this mandor)
                    $totalHCAllMandors = array_sum($harvestByBatch[$bid] ?? []);
                    $bc = max(0, $batchArea - $totalHCAllMandors);

                    // Status
                    // complete = batch.isactive = 0
                    // ongoing  = isactive = 1 && tanggalpanen NOT NULL && bc > 0
                    $status = $batch->isactive == 0 ? 'complete' : 'ongoing';

                    // Apply filters
                    if ($statusFilter !== 'all' && $status !== $statusFilter) {
                        continue;
                    }
                    if ($mandorId && $mid !== $mandorId) {
                        continue;
                    }

                    $grouped[$mid ?? 'unknown'][] = [
                        'plot'          => $batch->plot,
                        'blok'          => $batch->blok,
                        'batchno'       => $batch->batchno,
                        'lifecyclestatus' => $batch->lifecyclestatus,
                        'batcharea'     => $batchArea,
                        'tanggalpanen'  => $batch->tanggalpanen,
                        'hc'            => round($totalHC, 2),
                        'hc_all'        => round($totalHCAllMandors, 2),
                        'stc'           => round($batchArea - $totalHCAllMandors + $totalHC, 2),
                        'bc'            => round($bc, 2),
                        'status'        => $status,
                    ];
                }
            }

            // ----------------------------------------------------------------
            // STEP 5: Build output array grouped by mandor
            // ----------------------------------------------------------------
            $result = [];
            foreach ($grouped as $mid => $plots) {
                $result[] = [
                    'mandorid'   => $mid,
                    'mandorname' => $mid === 'unknown' ? '-' : ($mandorNames[$mid] ?? $mid),
                    'plots'      => collect($plots)->sortBy(['blok', 'plot'])->values()->toArray(),
                    'total_hc'   => round(collect($plots)->sum('hc'), 2),
                    'total_bc'   => round(collect($plots)->sum('bc'), 2),
                    'total_area' => round(collect($plots)->sum('batcharea'), 2),
                ];
            }

            // Sort by mandor name
            usort($result, fn($a, $b) => strcmp($a['mandorname'], $b['mandorname']));

            // ----------------------------------------------------------------
            // STEP 6: Header summary (company-wide, ignoring mandor filter)
            // ----------------------------------------------------------------
            $allBatchIds    = $batchIds;
            $allHCByBatch   = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
                ->where('lh.companycode', $companycode)
                ->where('lh.approvalstatus', '1')
                ->whereIn('lh.activitycode', self::PANEN_ACTIVITIES)
                ->whereIn('ldp.batchid', $allBatchIds)
                ->whereBetween('lh.lkhdate', [$start->toDateString(), $end->toDateString()])
                ->select('ldp.batchid', DB::raw('SUM(ldp.luashasil) as total_hc'))
                ->groupBy('ldp.batchid')
                ->pluck('total_hc', 'batchid');

            $totalPlots    = $batches->count();
            $completePlots = $batches->where('isactive', 0)->count();
            $ongoingPlots  = $batches->where('isactive', 1)->count();

            $summary = [
                'total_plots'    => $totalPlots,
                'ongoing_plots'  => $ongoingPlots,
                'complete_plots' => $completePlots,
                'total_hc'       => round($allHCByBatch->sum(), 2),
                'total_area'     => round($batches->sum('batcharea'), 2),
            ];

            return response()->json([
                'success' => true,
                'data'    => $result,
                'summary' => $summary,
            ]);

        } catch (\Exception $e) {
            \Log::error('SaldoPanenReport error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function emptyHeader(): array
    {
        return [
            'total_plots'    => 0,
            'ongoing_plots'  => 0,
            'complete_plots' => 0,
            'total_hc'       => 0,
            'total_area'     => 0,
        ];
    }
}