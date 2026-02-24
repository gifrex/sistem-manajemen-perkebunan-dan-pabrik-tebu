<?php

namespace App\Http\Controllers\Report;

use NumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class RekapUpahMingguanController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
            'nav' => 'Rekap Upah Mingguan',
        ]);
    }

    public function index(Request $request)
    {
        $title = 'Rekap Upah Mingguan';
        $search = $request->input('search', '');

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        if ($request->has('tenagakerjarum')) {
            session(['tenagakerjarum' => $request->tenagakerjarum]);
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $perPage = $request->session()->get('perPage', 10);
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $companycode = session('companycode');

        // OPTIMIZED: Single query with conditional search
        $rum = DB::table('lkhhdr as a')
            ->leftJoin('activity as c', 'c.activitycode', '=', 'a.activitycode')
            ->where('a.companycode', $companycode)
            ->where('a.status', 'APPROVED')
            ->where('c.active', 1)
            ->where('a.jenistenagakerja', $tk)
            ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('a.activitycode', 'like', '%' . $search . '%')
                        ->orWhere('c.activityname', 'like', '%' . $search . '%');
                });
            })
            ->select(
                'a.lkhno',
                'a.activitycode',
                'a.companycode',
                'a.status',
                'a.jenistenagakerja',
                'a.totalupahall',
                'a.lkhdate',
                'a.totalworkers',
                'c.activityname'
            )
            ->distinct()
            ->orderBy('a.lkhdate', 'desc')
            ->orderBy('a.lkhno', 'desc')
            ->paginate($perPage);

        // OPTIMIZED: Batch fetch plots for current page only
        if ($rum->count() > 0) {
            $lkhNos = $rum->pluck('lkhno')->toArray();

            $plots = DB::table('lkhdetailplot')
                ->whereIn('lkhno', $lkhNos)
                ->where('companycode', $companycode)
                ->select('lkhno', DB::raw("GROUP_CONCAT(DISTINCT plot ORDER BY plot SEPARATOR ', ') as plots"))
                ->groupBy('lkhno')
                ->pluck('plots', 'lkhno');

            // Format data
            foreach ($rum as $index => $item) {
                $item->no = ($rum->currentPage() - 1) * $rum->perPage() + $index + 1;
                $item->plots = $plots[$item->lkhno] ?? '';
                $item->totalupahall = $item->totalupahall == 0
                    ? '-'
                    : Number::currency($item->totalupahall, 'IDR', 'id');
            }
        }

        if ($request->ajax()) {
            return view('report.rum.index', compact('title', 'search', 'perPage', 'rum', 'startDate', 'endDate'));
        }

        return view('report.rum.index', compact('title', 'perPage', 'search', 'rum', 'startDate', 'endDate'));
    }

    public function show($lkhno)
    {
        $companycode = session('companycode');

        // OPTIMIZED: Single query to get header
        $header = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companycode)
            ->select('jenistenagakerja')
            ->first();

        if (!$header) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        if ($header->jenistenagakerja == 1) {
            // OPTIMIZED: Harian - Agregasi plot details seperti previewReport
            $plotDetails = DB::table('lkhdetailplot')
                ->where('lkhno', $lkhno)
                ->where('companycode', $companycode)
                ->select(
                    DB::raw('GROUP_CONCAT(DISTINCT plot ORDER BY plot SEPARATOR ", ") as plots'),
                    DB::raw('SUM(luasrkh) as total_luasrkh'),
                    DB::raw('SUM(luashasil) as total_luashasil')
                )
                ->first();

            // Ambil data batch dari plot pertama
            $firstPlot = DB::table('lkhdetailplot')
                ->where('lkhno', $lkhno)
                ->where('companycode', $companycode)
                ->orderBy('plot')
                ->value('plot');

            $batchInfo = null;
            if ($firstPlot) {
                $batchInfo = DB::table('batch')
                    ->where('companycode', $companycode)
                    ->where('plot', $firstPlot)
                    ->where('isactive', 1)
                    ->select(
                        'lifecyclestatus',
                        DB::raw("CONCAT(
                        CASE MONTH(batchdate)
                            WHEN 1 THEN 'JAN' WHEN 2 THEN 'FEB' WHEN 3 THEN 'MAR'
                            WHEN 4 THEN 'APR' WHEN 5 THEN 'MEI' WHEN 6 THEN 'JUN'
                            WHEN 7 THEN 'JUL' WHEN 8 THEN 'AGU' WHEN 9 THEN 'SEP'
                            WHEN 10 THEN 'OKT' WHEN 11 THEN 'NOV' WHEN 12 THEN 'DES'
                        END, 
                        \"'\", 
                        RIGHT(YEAR(batchdate), 2)
                    ) AS batchdate")
                    )
                    ->first();
            }

            $datas = DB::table('lkhhdr as a')
                ->join('lkhdetailworker as d', function ($join) {
                    $join->on('a.lkhno', '=', 'd.lkhno')
                        ->on('a.companycode', '=', 'd.companycode');
                })
                ->join('tenagakerja as tk', 'd.tenagakerjaid', '=', 'tk.tenagakerjaid')
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->where('a.lkhno', $lkhno)
                ->where('a.companycode', $companycode)
                ->select(
                    'a.lkhno',
                    'a.activitycode',
                    'c.activityname',
                    'd.tenagakerjaid',
                    'tk.nama as namatenagakerja',
                    'd.upahharian as upah',
                    'd.totalupah as total'
                )
                ->orderBy('tk.nama', 'asc')
                ->get();

            // Tambahkan plot details ke setiap item
            foreach ($datas as $item) {
                $item->plot = $plotDetails->plots ?? '';
                $item->luasrkh = $plotDetails->total_luasrkh ?? 0;
                $item->luashasil = $plotDetails->total_luashasil ?? 0;
                $item->lifecyclestatus = $batchInfo->lifecyclestatus ?? '-';
                $item->batchdate = $batchInfo->batchdate ?? '-';
            }

        } else {
            // OPTIMIZED: Borongan - Single query with subquery for nearest batch
            $datas = DB::table('lkhhdr as a')
                ->join('lkhdetailplot as b', function ($join) {
                    $join->on('a.lkhno', '=', 'b.lkhno')
                        ->on('a.companycode', '=', 'b.companycode');
                })
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin('upahborongan as ub', function ($join) {
                    $join->on('ub.activitycode', '=', 'a.activitycode')
                        ->on('ub.companycode', '=', 'a.companycode');
                })
                ->leftJoin(DB::raw('(
                SELECT companycode, plot, MAX(tanggalulangtahun) AS tanggalulangtahun_terdekat
                FROM batch
                WHERE isactive = 1
                GROUP BY companycode, plot
            ) as nearest'), function ($join) {
                    $join->on('a.companycode', '=', 'nearest.companycode')
                        ->on('b.plot', '=', 'nearest.plot');
                })
                ->leftJoin('batch as e', function ($join) {
                    $join->on('nearest.companycode', '=', 'e.companycode')
                        ->on('nearest.plot', '=', 'e.plot')
                        ->on('nearest.tanggalulangtahun_terdekat', '=', 'e.tanggalulangtahun');
                })
                ->where('a.lkhno', $lkhno)
                ->where('a.companycode', $companycode)
                ->whereColumn('nearest.tanggalulangtahun_terdekat', '<', 'a.lkhdate')
                ->select(
                    'a.lkhno',
                    'a.activitycode',
                    'c.activityname',
                    'b.plot',
                    'b.luasrkh',
                    'b.luashasil',
                    DB::raw("COALESCE(e.lifecyclestatus, '-') as lifecyclestatus"),
                    DB::raw("COALESCE(
                    CONCAT(
                        CASE MONTH(e.batchdate)
                            WHEN 1 THEN 'JAN' WHEN 2 THEN 'FEB' WHEN 3 THEN 'MAR'
                            WHEN 4 THEN 'APR' WHEN 5 THEN 'MEI' WHEN 6 THEN 'JUN'
                            WHEN 7 THEN 'JUL' WHEN 8 THEN 'AGU' WHEN 9 THEN 'SEP'
                            WHEN 10 THEN 'OKT' WHEN 11 THEN 'NOV' WHEN 12 THEN 'DES'
                        END, 
                        \"'\", 
                        RIGHT(YEAR(e.batchdate), 2)
                    ), '-'
                ) AS batchdate"),
                    'ub.amount as upah'
                )
                ->orderBy('b.plot', 'asc')
                ->get();
        }

        if ($datas->isEmpty()) {
            return response()->json(['error' => 'Tidak ada data detail untuk LKH ini'], 404);
        }

        // Format data
        foreach ($datas as $index => $item) {
            $item->no = $index + 1;

            if ($header->jenistenagakerja == 2) {
                $upahValue = is_numeric($item->upah) ? floatval($item->upah) : 0;
                $luasHasil = is_numeric($item->luashasil) ? floatval($item->luashasil) : 0;
                $totalValue = $upahValue * $luasHasil;

                $item->upah = Number::currency($upahValue, 'IDR', 'id');
                $item->total = Number::currency($totalValue, 'IDR', 'id');
            } else {
                $upahValue = is_numeric($item->upah) ? floatval($item->upah) : 0;
                $totalValue = is_numeric($item->total) ? floatval($item->total) : 0;

                $item->upah = Number::currency($upahValue, 'IDR', 'id');
                $item->total = Number::currency($totalValue, 'IDR', 'id');
            }
        }

        return response()->json(['data' => $datas]);
    }

    public function previewReport(Request $request)
    {
        $title = "Preview Rekap Upah Mingguan";
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $companycode = session('companycode');

        if ($tk == 1) {
            // OPTIMIZED: Harian - Agregasi plot details per lkhno
            $plotDetails = DB::table('lkhdetailplot')
                ->select(
                    'lkhno',
                    'companycode',
                    DB::raw('GROUP_CONCAT(DISTINCT plot ORDER BY plot SEPARATOR ", ") as plots'),
                    DB::raw('SUM(luasrkh) as total_luasrkh'),
                    DB::raw('SUM(luashasil) as total_luashasil')
                )
                ->groupBy('lkhno', 'companycode')
                ->get()
                ->keyBy('lkhno');

            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailworker as dw', function ($join) {
                    $join->on('a.lkhno', '=', 'dw.lkhno')
                        ->on('a.companycode', '=', 'dw.companycode');
                })
                // ->join('tenagakerja as tk', 'dw.tenagakerjaid', '=', 'tk.tenagakerjaid')
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin(
                    DB::raw('(SELECT lkhno, companycode, MIN(plot) as first_plot 
                    FROM lkhdetailplot 
                    GROUP BY lkhno, companycode) as b'),
                    function ($join) {
                        $join->on('a.lkhno', '=', 'b.lkhno')
                            ->on('a.companycode', '=', 'b.companycode');
                    }
                )
                ->leftJoin('batch as e', function ($join) {
                    $join->on('a.companycode', '=', 'e.companycode')
                        ->on('b.first_plot', '=', 'e.plot')
                        ->where('e.isactive', '=', 1);
                })
                ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
                ->where('a.companycode', $companycode)
                ->where('a.status', 'APPROVED')
                ->where('a.jenistenagakerja', $tk)
                ->where('c.active', 1)
                ->select(
                    'a.lkhno',
                    'a.lkhdate',
                    'a.activitycode',
                    'a.companycode',
                    'a.status',
                    'a.jenistenagakerja',
                    'a.totalupahall',
                    'a.totalworkers',
                    'c.activityname',
                    'dw.tenagakerjaid',
                    DB::raw("(SELECT nama FROM tenagakerja WHERE tenagakerjaid = dw.tenagakerjaid LIMIT 1) as namatenagakerja"),
                    // 'tk.nama as namatenagakerja',
                    'dw.upahharian as upah',
                    'dw.totalupah as total',
                    'e.lifecyclestatus',
                    DB::raw("CONCAT(
                    CASE MONTH(e.batchdate)
                        WHEN 1 THEN 'JAN' WHEN 2 THEN 'FEB' WHEN 3 THEN 'MAR'
                        WHEN 4 THEN 'APR' WHEN 5 THEN 'MEI' WHEN 6 THEN 'JUN'
                        WHEN 7 THEN 'JUL' WHEN 8 THEN 'AGU' WHEN 9 THEN 'SEP'
                        WHEN 10 THEN 'OKT' WHEN 11 THEN 'NOV' WHEN 12 THEN 'DES'
                    END,
                    \"'\", 
                    RIGHT(YEAR(e.batchdate), 2)
                ) AS batchdate")
                )
                ->orderBy('a.lkhno', 'asc')
                ->orderBy('dw.tenagakerjaid', 'asc')
                ->get();

            // Tambahkan plot details ke setiap item
            foreach ($data as $item) {
                $plotDetail = $plotDetails->get($item->lkhno);
                $item->plot = $plotDetail->plots ?? '';
                $item->luasan = $plotDetail->total_luasrkh ?? 0;
                $item->hasil = $plotDetail->total_luashasil ?? 0;
            }

        } elseif ($tk == 2) {
            // OPTIMIZED: Borongan - Agregasi per lkhno dan plot
            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailplot as b', function ($join) {
                    $join->on('a.lkhno', '=', 'b.lkhno')
                        ->on('a.companycode', '=', 'b.companycode');
                })
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin('upahborongan as ub', function ($join) {
                    $join->on('ub.activitycode', '=', 'a.activitycode')
                        ->on('ub.companycode', '=', 'a.companycode');
                })
                ->leftJoin('batch as e', function ($join) {
                    $join->on('a.companycode', '=', 'e.companycode')
                        ->on('b.plot', '=', 'e.plot')
                        ->where('e.isactive', '=', 1);
                })
                ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
                ->where('a.companycode', $companycode)
                ->where('a.status', 'APPROVED')
                ->where('a.jenistenagakerja', $tk)
                ->where('c.active', 1)
                ->select(
                    'a.lkhno',
                    'a.lkhdate',
                    'a.activitycode',
                    'a.companycode',
                    'a.status',
                    'a.jenistenagakerja',
                    'a.totalupahall',
                    'a.totalworkers',
                    'b.plot',
                    'b.luasrkh as luasan',
                    'b.luashasil as hasil',
                    'c.activityname',
                    'e.lifecyclestatus',
                    DB::raw("CONCAT(
                    CASE MONTH(e.batchdate)
                        WHEN 1 THEN 'JAN' WHEN 2 THEN 'FEB' WHEN 3 THEN 'MAR'
                        WHEN 4 THEN 'APR' WHEN 5 THEN 'MEI' WHEN 6 THEN 'JUN'
                        WHEN 7 THEN 'JUL' WHEN 8 THEN 'AGU' WHEN 9 THEN 'SEP'
                        WHEN 10 THEN 'OKT' WHEN 11 THEN 'NOV' WHEN 12 THEN 'DES'
                    END,
                    \"'\", 
                    RIGHT(YEAR(e.batchdate), 2)
                ) AS batchdate"),
                    'ub.amount as upah'
                )
                ->orderBy('a.lkhno', 'asc')
                ->orderBy('b.plot', 'asc')
                ->get();
        } else {
            $data = collect();
        }

        // Format results
        foreach ($data as $item) {
            if ($tk == 2) {
                $upahValue = is_numeric($item->upah) ? $item->upah : 0;
                $total = $upahValue * ($item->hasil ?? 0);
                $item->upah = Number::currency($upahValue, 'IDR', 'id');
                $item->total = Number::currency($total, 'IDR', 'id');
            } else {
                $item->upah = Number::currency($item->upah ?? 0, 'IDR', 'id');
                $item->total = Number::currency($item->total ?? 0, 'IDR', 'id');
            }

            $item->totalupahall = Number::currency($item->totalupahall ?? 0, 'IDR', 'id');
        }

        return view('report.rum.print', compact('title', 'startDate', 'endDate', 'data'));
    }

    public function printBp(Request $request)
    {
        $title = "Print Bukti Pembayaran";
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;

        // OPTIMIZED: Single query for sum
        $totalAmount = DB::table('lkhhdr')
            ->whereBetween('lkhdate', [$startDate, $endDate])
            ->where('jenistenagakerja', $tk)
            ->where('status', 'APPROVED')
            ->where('companycode', session('companycode'))
            ->sum('totalupahall');

        // Convert to words (Indonesian)
        $formatter = new NumberFormatter('id_ID', NumberFormatter::SPELLOUT);
        $amountInWords = ucfirst($formatter->format($totalAmount)) . ' rupiah';

        return view('report.rum.printbp', compact('title', 'totalAmount', 'amountInWords', 'startDate', 'endDate', 'tk', 'tenagakerjarum'));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $companycode = session('companycode');

        if ($tk == 1) {
            // OPTIMIZED: Harian - Sama seperti previewReport
            $plotDetails = DB::table('lkhdetailplot')
                ->select(
                    'lkhno',
                    'companycode',
                    DB::raw('GROUP_CONCAT(DISTINCT plot ORDER BY plot SEPARATOR ", ") as plots'),
                    DB::raw('SUM(luasrkh) as total_luasrkh'),
                    DB::raw('SUM(luashasil) as total_luashasil')
                )
                ->groupBy('lkhno', 'companycode')
                ->get()
                ->keyBy('lkhno');

            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailworker as dw', function ($join) {
                    $join->on('a.lkhno', '=', 'dw.lkhno')
                        ->on('a.companycode', '=', 'dw.companycode');
                })
                ->join('tenagakerja as tk', 'dw.tenagakerjaid', '=', 'tk.tenagakerjaid')
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin(
                    DB::raw('(SELECT lkhno, companycode, MIN(plot) as first_plot 
                FROM lkhdetailplot 
                GROUP BY lkhno, companycode) as b'),
                    function ($join) {
                        $join->on('a.lkhno', '=', 'b.lkhno')
                            ->on('a.companycode', '=', 'b.companycode');
                    }
                )
                ->leftJoin('batch as e', function ($join) {
                    $join->on('a.companycode', '=', 'e.companycode')
                        ->on('b.first_plot', '=', 'e.plot')
                        ->where('e.isactive', '=', 1);
                })
                ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
                ->where('a.companycode', $companycode)
                ->where('a.status', 'APPROVED')
                ->where('a.jenistenagakerja', $tk)
                ->where('c.active', 1)
                ->select(
                    'a.lkhno',
                    'a.lkhdate',
                    'a.activitycode',
                    'a.companycode',
                    'a.status',
                    'a.jenistenagakerja',
                    'a.totalupahall',
                    'a.totalworkers',
                    'c.activityname',
                    'dw.tenagakerjaid',
                    'tk.nama as namatenagakerja',
                    'dw.upahharian as upah',
                    'dw.totalupah as total',
                    'e.lifecyclestatus',
                    DB::raw("CONCAT(
                CASE MONTH(e.batchdate)
                    WHEN 1 THEN 'JAN' WHEN 2 THEN 'FEB' WHEN 3 THEN 'MAR'
                    WHEN 4 THEN 'APR' WHEN 5 THEN 'MEI' WHEN 6 THEN 'JUN'
                    WHEN 7 THEN 'JUL' WHEN 8 THEN 'AGU' WHEN 9 THEN 'SEP'
                    WHEN 10 THEN 'OKT' WHEN 11 THEN 'NOV' WHEN 12 THEN 'DES'
                END,
                \"'\", 
                RIGHT(YEAR(e.batchdate), 2)
            ) AS batchdate")
                )
                ->orderBy('a.lkhno', 'asc')
                ->orderBy('tk.nama', 'asc')
                ->get();

            // Tambahkan plot details ke setiap item
            foreach ($data as $item) {
                $plotDetail = $plotDetails->get($item->lkhno);
                $item->plot = $plotDetail->plots ?? '';
                $item->luasan = $plotDetail->total_luasrkh ?? 0;
                $item->hasil = $plotDetail->total_luashasil ?? 0;
                $item->statustanam = $item->batchdate . "/" . $item->lifecyclestatus;
            }

        } elseif ($tk == 2) {
            // OPTIMIZED: Borongan - Sama seperti previewReport
            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailplot as b', function ($join) {
                    $join->on('a.lkhno', '=', 'b.lkhno')
                        ->on('a.companycode', '=', 'b.companycode');
                })
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin('upahborongan as ub', function ($join) {
                    $join->on('ub.activitycode', '=', 'a.activitycode')
                        ->on('ub.companycode', '=', 'a.companycode');
                })
                ->leftJoin('batch as e', function ($join) {
                    $join->on('a.companycode', '=', 'e.companycode')
                        ->on('b.plot', '=', 'e.plot')
                        ->where('e.isactive', '=', 1);
                })
                ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
                ->where('a.companycode', $companycode)
                ->where('a.status', 'APPROVED')
                ->where('a.jenistenagakerja', $tk)
                ->where('c.active', 1)
                ->select(
                    'a.lkhno',
                    'a.lkhdate',
                    'a.activitycode',
                    'a.companycode',
                    'a.status',
                    'a.jenistenagakerja',
                    'a.totalupahall',
                    'a.totalworkers',
                    'b.plot',
                    'b.luasrkh as luasan',
                    'b.luashasil as hasil',
                    'c.activityname',
                    'e.lifecyclestatus',
                    DB::raw("CONCAT(
                CASE MONTH(e.batchdate)
                    WHEN 1 THEN 'JAN' WHEN 2 THEN 'FEB' WHEN 3 THEN 'MAR'
                    WHEN 4 THEN 'APR' WHEN 5 THEN 'MEI' WHEN 6 THEN 'JUN'
                    WHEN 7 THEN 'JUL' WHEN 8 THEN 'AGU' WHEN 9 THEN 'SEP'
                    WHEN 10 THEN 'OKT' WHEN 11 THEN 'NOV' WHEN 12 THEN 'DES'
                END,
                \"'\", 
                RIGHT(YEAR(e.batchdate), 2)
            ) AS batchdate"),
                    'ub.amount as upah'
                )
                ->orderBy('a.lkhno', 'asc')
                ->orderBy('b.plot', 'asc')
                ->get();

            foreach ($data as $item) {
                $item->statustanam = $item->batchdate . "/" . $item->lifecyclestatus;
            }
        } else {
            $data = collect();
        }

        // Group data by activityname first, then by lkhno (sama seperti preview)
        $groupedByActivity = [];
        foreach ($data as $item) {
            $activityName = $item->activityname;
            $lkhno = $item->lkhno;

            if (!isset($groupedByActivity[$activityName])) {
                $groupedByActivity[$activityName] = [];
            }
            if (!isset($groupedByActivity[$activityName][$lkhno])) {
                $groupedByActivity[$activityName][$lkhno] = [];
            }
            $groupedByActivity[$activityName][$lkhno][] = $item;
        }

        // Create temp directories
        $tempDir = storage_path('app/temp');
        $spoutTempDir = storage_path('app/spout-temp');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        if (!file_exists($spoutTempDir)) {
            mkdir($spoutTempDir, 0755, true);
        }

        putenv('SPOUT_TEMP_FOLDER=' . $spoutTempDir);

        $filename = 'Rekap_Upah_Mingguan_' . date('Y-m-d_His') . '.xlsx';
        $filePath = $tempDir . '/' . $filename;

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->setTempFolder($spoutTempDir);
        $writer->openToFile($filePath);

        // Styles
        $borderBuilder = new BorderBuilder();
        $border = $borderBuilder
            ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN)
            ->setBorderTop(Color::BLACK, Border::WIDTH_THIN)
            ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN)
            ->setBorderRight(Color::BLACK, Border::WIDTH_THIN)
            ->build();

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(14)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();

        $subHeaderStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(12)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();

        $tableHeaderStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(240, 240, 240))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setBorder($border)
            ->build();

        $normalStyle = (new StyleBuilder())
            ->setBorder($border)
            ->build();

        $centerStyle = (new StyleBuilder())
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();

        $rightStyle = (new StyleBuilder())
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->build();

        $activityHeaderStyle = (new StyleBuilder())
            ->setBorder($border)
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(239, 246, 255)) // bg-blue-50
            ->build();

        $subtotalStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(254, 252, 232)) // bg-yellow-50
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->build();

        $totalStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(220, 252, 231)) // bg-green-100
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setFontSize(12)
            ->build();

        // Parse dates for header
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        \Carbon\Carbon::setLocale('id');

        $periode = $start->format('m Y') === $end->format('m Y')
            ? $start->translatedFormat('d') . ' s.d ' . $end->translatedFormat('d F Y')
            : $start->translatedFormat('d F Y') . ' s.d ' . $end->translatedFormat('d F Y');

        $jenistk = $tenagakerjarum == 'Harian' ? 'Harian' : 'Borongan';

        // Header rows
        $writer->addRow(WriterEntityFactory::createRowFromArray(['', '', '', '', 'Rekap Upah Mingguan'], $headerStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['', '', '', '', 'Tenaga Kerja ' . $jenistk], $subHeaderStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['', '', '', '', 'Divisi ' . $companycode], $subHeaderStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['', '', '', '', 'Periode: ' . $periode], $subHeaderStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['No. Voucher:']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));

        // Table header
        if ($tenagakerjarum == 'Harian') {
            $headerRow = ['No.', 'Tenaga Kerja', 'Plot', 'Luas (Ha)', 'Status Tanam', 'Hasil (Ha)', 'Tanggal Kegiatan', 'Cost/Unit', 'Biaya (Rp)'];
        } else {
            $headerRow = ['No.', 'Plot', 'Luas (Ha)', 'Status Tanam', 'Hasil (Ha)', 'Tanggal Kegiatan', 'Biaya (Rp)'];
        }
        $writer->addRow(WriterEntityFactory::createRowFromArray($headerRow, $tableHeaderStyle));

        // Data rows
        $rowNumber = 1;
        $totalKeseluruhan = 0;

        foreach ($groupedByActivity as $activityName => $lkhGroups) {
            $activitySubtotal = 0;

            // Activity header row
            $activityHeaderRow = $tenagakerjarum == 'Harian'
                ? ['Kegiatan: ' . $activityName, '', '', '', '', '', '', '', '']
                : ['Kegiatan: ' . $activityName, '', '', '', '', '', ''];

            $cells = [];
            foreach ($activityHeaderRow as $cellValue) {
                $cells[] = WriterEntityFactory::createCell($cellValue, $activityHeaderStyle);
            }
            $writer->addRow(WriterEntityFactory::createRow($cells));

            foreach ($lkhGroups as $lkhno => $items) {
                $subtotal = 0;
                $itemCount = count($items);

                foreach ($items as $index => $item) {
                    if ($tenagakerjarum == 'Harian') {
                        $rowData = [
                            WriterEntityFactory::createCell($rowNumber, $centerStyle),
                            WriterEntityFactory::createCell($item->namatenagakerja ?? '', $normalStyle),
                            WriterEntityFactory::createCell($item->plot, $normalStyle),
                            WriterEntityFactory::createCell(number_format($item->luasan, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell($item->statustanam ?? '', $normalStyle),
                            WriterEntityFactory::createCell(number_format($item->hasil, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(\Carbon\Carbon::parse($item->lkhdate)->format('Y-m-d'), $centerStyle),
                            WriterEntityFactory::createCell(number_format($item->upah ?? 0, 0, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->total ?? 0, 0, ',', '.'), $rightStyle),
                        ];

                        $totalValue = floatval($item->total ?? 0);
                    } else {
                        // Borongan - hanya tampilkan tanggal dan biaya di row pertama
                        if ($index === 0) {
                            $rowData = [
                                WriterEntityFactory::createCell($rowNumber, $centerStyle),
                                WriterEntityFactory::createCell($item->plot, $normalStyle),
                                WriterEntityFactory::createCell(number_format($item->luasan, 2, ',', '.'), $rightStyle),
                                WriterEntityFactory::createCell($item->statustanam, $normalStyle),
                                WriterEntityFactory::createCell(number_format($item->hasil, 2, ',', '.'), $rightStyle),
                                WriterEntityFactory::createCell(\Carbon\Carbon::parse($item->lkhdate)->format('Y-m-d'), $centerStyle),
                                WriterEntityFactory::createCell(number_format($item->totalupahall ?? 0, 0, ',', '.'), $rightStyle),
                            ];
                            $totalValue = floatval($item->totalupahall ?? 0);
                        } else {
                            $rowData = [
                                WriterEntityFactory::createCell($rowNumber, $centerStyle),
                                WriterEntityFactory::createCell($item->plot, $normalStyle),
                                WriterEntityFactory::createCell(number_format($item->luasan, 2, ',', '.'), $rightStyle),
                                WriterEntityFactory::createCell($item->statustanam, $normalStyle),
                                WriterEntityFactory::createCell(number_format($item->hasil, 2, ',', '.'), $rightStyle),
                                WriterEntityFactory::createCell('', $centerStyle),
                                WriterEntityFactory::createCell('', $rightStyle),
                            ];
                            $totalValue = 0;
                        }
                    }

                    $writer->addRow(WriterEntityFactory::createRow($rowData));
                    $subtotal += $totalValue;
                    $rowNumber++;
                }

                $activitySubtotal += $subtotal;
            }

            // Subtotal row
            if ($tenagakerjarum == 'Harian') {
                $subtotalRow = [
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('Subtotal ' . $activityName, $subtotalStyle),
                    WriterEntityFactory::createCell('Rp ' . number_format($activitySubtotal, 2, ',', '.'), $subtotalStyle),
                ];
            } else {
                $subtotalRow = [
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('Subtotal ' . $activityName, $subtotalStyle),
                    WriterEntityFactory::createCell('Rp ' . number_format($activitySubtotal, 2, ',', '.'), $subtotalStyle),
                ];
            }

            $writer->addRow(WriterEntityFactory::createRow($subtotalRow));
            $totalKeseluruhan += $activitySubtotal;
        }

        // Total keseluruhan row
        if ($tenagakerjarum == 'Harian') {
            $totalRow = [
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('TOTAL KESELURUHAN', $totalStyle),
                WriterEntityFactory::createCell('Rp ' . number_format($totalKeseluruhan, 2, ',', '.'), $totalStyle),
            ];
        } else {
            $totalRow = [
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('TOTAL KESELURUHAN', $totalStyle),
                WriterEntityFactory::createCell('Rp ' . number_format($totalKeseluruhan, 2, ',', '.'), $totalStyle),
            ];
        }

        $writer->addRow(WriterEntityFactory::createRow($totalRow));

        // Footer
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['', '', '', '', '', 'Dicetak pada: ' . Carbon::now()->format('d/m/Y H:i')]));

        $writer->close();

        // Cleanup temp files
        $tempFiles = glob($spoutTempDir . '/*');
        foreach ($tempFiles as $tempFile) {
            if (is_file($tempFile)) {
                @unlink($tempFile);
            }
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}