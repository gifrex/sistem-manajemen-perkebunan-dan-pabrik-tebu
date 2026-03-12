<?php

namespace App\Http\Controllers\Report;

use NumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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

        $filterMandors = $request->input('mandor_ids', []);
        if (is_string($filterMandors)) {
            $filterMandors = array_filter(explode(',', $filterMandors));
        }

        $mandorList = DB::table('user')
            ->whereIn('userid', function ($q) use ($companycode, $tk) {
                $q->select('mandorid')
                    ->from('lkhhdr')
                    ->where('companycode', $companycode)
                    ->where('jenistenagakerja', $tk)
                    ->where('status', 'APPROVED');
            })
            ->select('userid', 'name')
            ->orderBy('name')
            ->get();

        // OPTIMIZED: Single query with conditional search
        $rum = DB::table('lkhhdr as a')
            ->leftJoin('activity as c', 'c.activitycode', '=', 'a.activitycode')
            ->leftJoin('user as u', function ($join) {
                $join->on('u.userid', '=', 'a.mandorid')
                    ->on('u.companycode', '=', 'a.companycode');
            })
            ->where('a.companycode', $companycode)
            ->where('a.status', 'APPROVED')
            ->where('c.active', 1)
            ->where('a.jenistenagakerja', $tk)
            ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
            ->when(!empty($filterMandors), fn($q) => $q->whereIn('a.mandorid', $filterMandors))
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('a.activitycode', 'like', '%' . $search . '%')
                        ->orWhere('c.activityname', 'like', '%' . $search . '%')
                        ->orWhere('a.lkhno', 'like', '%' . $search . '%');
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
                'c.activityname',
                'u.name as mandorname'
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
            return view('report.rum.index', compact('title', 'search', 'perPage', 'rum', 'startDate', 'endDate', 'mandorList', 'filterMandors'));
        }

        return view('report.rum.index', compact('title', 'perPage', 'search', 'rum', 'startDate', 'endDate', 'mandorList', 'filterMandors'));
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
                ->join('tenagakerja as tk', function ($join) {
                    $join->on('d.tenagakerjaid', '=', 'tk.tenagakerjaid')
                        ->on('d.companycode', '=', 'tk.companycode');
                })
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
                    'd.upahlembur',
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
                $upahlembur = is_numeric($item->upahlembur) ? floatval($item->upahlembur) : 0;
                $totalValue = is_numeric($item->total) ? floatval($item->total) : 0;

                $item->upah = Number::currency($upahValue, 'IDR', 'id');
                $item->upahlembur = Number::currency($upahlembur, 'IDR', 'id');
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

        $filterMandors = $request->input('mandor_ids', []);
        if (is_string($filterMandors)) {
            $filterMandors = array_filter(explode(',', $filterMandors));
        }

        $plotDetailsByLkh = collect();
        $materialDetails = collect();

        if ($tk == 1) {
            // Harian - Plot rows individual per lkhno+plot dengan batch info
            $plotDetailsByLkh = DB::table('lkhdetailplot as p')
                ->leftJoin('batch as e', function ($join) {
                    $join->on('p.companycode', '=', 'e.companycode')
                        ->on('p.plot', '=', 'e.plot')
                        ->where('e.isactive', '=', 1);
                })
                ->where('p.companycode', $companycode)
                ->select(
                    'p.lkhno',
                    'p.plot',
                    'p.luasrkh',
                    'p.luashasil',
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
                ->orderBy('p.lkhno')
                ->orderBy('p.plot')
                ->get()
                ->groupBy('lkhno');

            // Harian - Material per lkhno+plot, dicocokkan dengan lkhdetailplot
            $materialDetails = DB::table('lkhdetailmaterial as m')
                ->join('lkhhdr as h', function ($join) {
                    $join->on('m.lkhno', '=', 'h.lkhno')
                        ->on('m.companycode', '=', 'h.companycode')
                        ->on('m.lkhhdrid', '=', 'h.id');
                })
                ->join('lkhdetailplot as p', function ($join) {
                    $join->on('m.companycode', '=', 'p.companycode')
                        ->on('m.lkhno', '=', 'p.lkhno')
                        ->on('m.plot', '=', 'p.plot');
                })
                ->leftJoin('herbisida as herb', function ($join) {
                    $join->on('m.itemcode', '=', 'herb.itemcode')
                        ->on('m.companycode', '=', 'herb.companycode');
                })
                ->where('m.companycode', $companycode)
                ->select(
                    'm.lkhno',
                    'm.plot',
                    DB::raw("GROUP_CONCAT(CONCAT(COALESCE(herb.itemname, m.itemcode), ' = ', m.qtydigunakan, ' ', COALESCE(herb.measure, '')) ORDER BY m.itemcode SEPARATOR ', ') as materials")
                )
                ->groupBy('m.lkhno', 'm.plot', 'm.companycode')
                ->get()
                ->keyBy(fn($item) => $item->lkhno . '_' . $item->plot);

            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailworker as dw', function ($join) {
                    $join->on('a.lkhno', '=', 'dw.lkhno')
                        ->on('a.companycode', '=', 'dw.companycode');
                })
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin('user as u', function ($join) {
                    $join->on('u.userid', '=', 'a.mandorid')
                        ->on('u.companycode', '=', 'a.companycode');
                })
                ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
                ->when(!empty($filterMandors), fn($q) => $q->whereIn('a.mandorid', $filterMandors))
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
                    'u.name as mandorname',
                    'dw.tenagakerjaid',
                    DB::raw("(SELECT nama FROM tenagakerja WHERE tenagakerjaid = dw.tenagakerjaid LIMIT 1) as namatenagakerja"),
                    'dw.upahharian as upah',
                    'dw.upahlembur',
                    'dw.totalupah as total'
                )
                ->orderBy('a.lkhno', 'asc')
                ->orderBy('dw.tenagakerjaid', 'asc')
                ->get();

        } elseif ($tk == 2) {
            // Borongan - Material details per lkhno dan plot
            $materialDetails = DB::table('lkhdetailmaterial as m')
                ->join('lkhhdr as h', function ($join) {
                    $join->on('m.lkhno', '=', 'h.lkhno')
                        ->on('m.companycode', '=', 'h.companycode')
                        ->on('m.lkhhdrid', '=', 'h.id');
                })
                ->leftJoin('herbisida as herb', function ($join) {
                    $join->on('m.itemcode', '=', 'herb.itemcode')
                        ->on('m.companycode', '=', 'herb.companycode');
                })
                ->where('m.companycode', $companycode)
                ->select(
                    'm.lkhno',
                    'm.plot',
                    DB::raw("GROUP_CONCAT(CONCAT('- ', COALESCE(herb.itemname, m.itemcode), ' = ', CAST(m.qtydigunakan AS CHAR), ' ', COALESCE(herb.measure, '')) ORDER BY m.itemcode SEPARATOR '\n') as materials")
                )
                ->groupBy('m.lkhno', 'm.plot', 'm.companycode')
                ->get()
                ->keyBy(fn($item) => $item->lkhno . '_' . $item->plot);

            // OPTIMIZED: Borongan - Agregasi per lkhno dan plot
            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailplot as b', function ($join) {
                    $join->on('a.lkhno', '=', 'b.lkhno')
                        ->on('a.companycode', '=', 'b.companycode');
                })
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin('user as u', function ($join) {
                    $join->on('u.userid', '=', 'a.mandorid')
                        ->on('u.companycode', '=', 'a.companycode');
                })
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
                ->when(!empty($filterMandors), fn($q) => $q->whereIn('a.mandorid', $filterMandors))
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
                    'u.name as mandorname',
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

            // Tambahkan material details ke setiap item borongan
            foreach ($data as $item) {
                $key = $item->lkhno . '_' . $item->plot;
                $item->materials = $materialDetails->get($key)?->materials ?? '';
            }
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
                $item->upahlembur = Number::currency($item->upahlembur ?? 0, 'IDR', 'id');
                $item->total = Number::currency($item->total ?? 0, 'IDR', 'id');
            }

            $item->totalupahall = Number::currency($item->totalupahall ?? 0, 'IDR', 'id');
        }

        return view('report.rum.print', compact('title', 'startDate', 'endDate', 'data', 'plotDetailsByLkh', 'materialDetails'));
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

        $filterMandors = $request->input('mandor_ids', []);
        if (is_string($filterMandors)) {
            $filterMandors = array_filter(explode(',', $filterMandors));
        }

        if ($tk == 1) {
            // Plot info per lkhno (individual rows per plot, same as previewReport)
            $plotDetailsByLkh = DB::table('lkhdetailplot as p')
                ->leftJoin('batch as e', function ($join) {
                    $join->on('p.companycode', '=', 'e.companycode')
                        ->on('p.plot', '=', 'e.plot')
                        ->where('e.isactive', '=', 1);
                })
                ->where('p.companycode', $companycode)
                ->select(
                    'p.lkhno',
                    'p.plot',
                    'p.luasrkh',
                    'p.luashasil',
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
                ->orderBy('p.lkhno')
                ->orderBy('p.plot')
                ->get()
                ->groupBy('lkhno');

            // Material per lkhno+plot (same as previewReport)
            $materialDetails = DB::table('lkhdetailmaterial as m')
                ->join('lkhhdr as h', function ($join) {
                    $join->on('m.lkhno', '=', 'h.lkhno')
                        ->on('m.companycode', '=', 'h.companycode')
                        ->on('m.lkhhdrid', '=', 'h.id');
                })
                ->join('lkhdetailplot as p', function ($join) {
                    $join->on('m.companycode', '=', 'p.companycode')
                        ->on('m.lkhno', '=', 'p.lkhno')
                        ->on('m.plot', '=', 'p.plot');
                })
                ->leftJoin('herbisida as herb', function ($join) {
                    $join->on('m.itemcode', '=', 'herb.itemcode')
                        ->on('m.companycode', '=', 'herb.companycode');
                })
                ->where('m.companycode', $companycode)
                ->select(
                    'm.lkhno',
                    'm.plot',
                    DB::raw("GROUP_CONCAT(CONCAT(COALESCE(herb.itemname, m.itemcode), ' = ', m.qtydigunakan, ' ', COALESCE(herb.measure, '')) ORDER BY m.itemcode SEPARATOR ', ') as materials")
                )
                ->groupBy('m.lkhno', 'm.plot', 'm.companycode')
                ->get()
                ->keyBy(fn($item) => $item->lkhno . '_' . $item->plot);

            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailworker as dw', function ($join) {
                    $join->on('a.lkhno', '=', 'dw.lkhno')
                        ->on('a.companycode', '=', 'dw.companycode');
                })
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin('user as u', function ($join) {
                    $join->on('u.userid', '=', 'a.mandorid')
                        ->on('u.companycode', '=', 'a.companycode');
                })
                ->when($startDate, fn($q) => $q->whereDate('a.lkhdate', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('a.lkhdate', '<=', $endDate))
                ->when(!empty($filterMandors), fn($q) => $q->whereIn('a.mandorid', $filterMandors))
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
                    'u.name as mandorname',
                    'dw.tenagakerjaid',
                    DB::raw("(SELECT nama FROM tenagakerja WHERE tenagakerjaid = dw.tenagakerjaid LIMIT 1) as namatenagakerja"),
                    'dw.upahharian as upah',
                    'dw.upahlembur',
                    'dw.totalupah as total'
                )
                ->orderBy('a.lkhno', 'asc')
                ->orderBy('dw.tenagakerjaid', 'asc')
                ->get();

        } elseif ($tk == 2) {
            $data = DB::table('lkhhdr as a')
                ->join('lkhdetailplot as b', function ($join) {
                    $join->on('a.lkhno', '=', 'b.lkhno')
                        ->on('a.companycode', '=', 'b.companycode');
                })
                ->leftJoin('activity as c', 'a.activitycode', '=', 'c.activitycode')
                ->leftJoin('user as u', function ($join) {
                    $join->on('u.userid', '=', 'a.mandorid')
                        ->on('u.companycode', '=', 'a.companycode');
                })
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
                ->when(!empty($filterMandors), fn($q) => $q->whereIn('a.mandorid', $filterMandors))
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
                    'u.name as mandorname',
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

            $exportMaterialDetails = DB::table('lkhdetailmaterial as m')
                ->join('lkhhdr as h', function ($join) {
                    $join->on('m.lkhno', '=', 'h.lkhno')
                        ->on('m.companycode', '=', 'h.companycode')
                        ->on('m.lkhhdrid', '=', 'h.id');
                })
                ->leftJoin('herbisida as herb', function ($join) {
                    $join->on('m.itemcode', '=', 'herb.itemcode')
                        ->on('m.companycode', '=', 'herb.companycode');
                })
                ->where('m.companycode', $companycode)
                ->select(
                    'm.lkhno',
                    'm.plot',
                    DB::raw("GROUP_CONCAT(CONCAT('- ', COALESCE(herb.itemname, m.itemcode), ' = ', CAST(m.qtydigunakan AS CHAR), ' ', COALESCE(herb.measure, '')) ORDER BY m.itemcode SEPARATOR '\n') as materials")
                )
                ->groupBy('m.lkhno', 'm.plot', 'm.companycode')
                ->get()
                ->keyBy(fn($m) => $m->lkhno . '_' . $m->plot);

            foreach ($data as $item) {
                $item->statustanam = $item->batchdate . "/" . $item->lifecyclestatus;
                $key = $item->lkhno . '_' . $item->plot;
                $item->materials = $exportMaterialDetails->get($key)?->materials ?? '';
            }
        } else {
            $data = collect();
        }

        if ($data->isEmpty()) {
            return response()->json(['error' => 'Tidak ada data untuk diekspor pada periode yang dipilih.'], 404);
        }

        return $this->writeExcelFile($data, $tenagakerjarum, $companycode, $startDate, $endDate, $plotDetailsByLkh ?? collect(), $materialDetails ?? collect());
    }

    private function writeExcelFile($data, $tenagakerjarum, $companycode, $startDate, $endDate, $plotDetailsByLkh = null, $materialDetails = null)
    {
        $isHarian = $tenagakerjarum == 'Harian';
        $colCount = $isHarian ? 6 : 8;
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $prevCol = Coordinate::stringFromColumnIndex($colCount - 1);

        // Group data
        $groupedByActivity = [];
        foreach ($data as $item) {
            $groupedByActivity[$item->activityname][$item->lkhno][] = $item;
        }

        // Parse dates
        \Carbon\Carbon::setLocale('id');
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $periode = $start->format('m Y') === $end->format('m Y')
            ? $start->translatedFormat('d') . ' s.d ' . $end->translatedFormat('d F Y')
            : $start->translatedFormat('d F Y') . ' s.d ' . $end->translatedFormat('d F Y');

        // Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Upah');

        // Column widths
        $colWidths = $isHarian
            ? ['A' => 5, 'B' => 30, 'C' => 15, 'D' => 20, 'E' => 20, 'F' => 20]
            : ['A' => 5, 'B' => 12, 'C' => 35, 'D' => 12, 'E' => 15, 'F' => 12, 'G' => 15, 'H' => 20];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Border style array (reused)
        $thin = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
            ],
        ];

        $row = 1;

        // === DOCUMENT HEADER ===
        $jenistk = $isHarian ? 'Harian' : 'Borongan';
        foreach ([
            ['Rekap Upah Mingguan', 14, true],
            ["Tenaga Kerja {$jenistk}", 12, true],
            ["Divisi {$companycode}", 12, false],
            ["Periode: {$periode}", 12, false],
        ] as [$text, $size, $bold]) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", $text);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => $bold, 'size' => $size],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }
        $row++; // empty
        $sheet->setCellValue("A{$row}", 'No. Voucher: ___________________________');
        $row += 2; // + empty row

        // === TABLE HEADER ===
        $headers = $isHarian
            ? ['No.', 'Nama Tenaga Kerja', 'Tgl Kegiatan', 'Upah Pokok (Rp)', 'Upah Lembur (Rp)', 'Total Upah (Rp)']
            : ['No.', 'Plot', 'Material', 'Luas (Ha)', 'Status Tanam', 'Hasil (Ha)', 'Tgl Kegiatan', 'Biaya (Rp)'];
        foreach ($headers as $ci => $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($ci + 1) . $row, $h);
        }
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($thin, [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]));
        $sheet->getRowDimension($row)->setRowHeight(18);
        $row++;

        // === DATA ROWS ===
        $rowNumber = 1;
        $totalKeseluruhan = 0;

        foreach ($groupedByActivity as $activityName => $lkhGroups) {
            $activitySubtotal = 0;

            // Activity header (full-width merge, dark bg)
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", "▶  {$activityName}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($thin, [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            ]));
            $row++;

            foreach ($lkhGroups as $lkhno => $items) {
                $subtotal = 0;
                $mandorname = $items[0]->mandorname ?? '-';

                // LKH sub-header (merged, blue tint)
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", "No. LKH: {$lkhno}  |  Mandor: {$mandorname}");
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($thin, [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF1D4ED8']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                ]));
                $row++;

                // Plot info rows — Harian only (merged, green tint)
                if ($isHarian && $plotDetailsByLkh !== null) {
                    foreach ($plotDetailsByLkh->get($lkhno, collect()) as $plotRow) {
                        $matKey = $lkhno . '_' . $plotRow->plot;
                        $plotMaterial = $materialDetails ? ($materialDetails->get($matKey)?->materials ?? '') : '';
                        $txt = "Plot: {$plotRow->plot}  |  Luas: " . number_format($plotRow->luasrkh, 2, ',', '.') . " Ha  |  Status Tanam: {$plotRow->batchdate}/{$plotRow->lifecyclestatus}  |  Hasil: " . number_format($plotRow->luashasil, 2, ',', '.') . " Ha";
                        if ($plotMaterial) {
                            $txt .= "  |  Material: {$plotMaterial}";
                        }
                        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                        $sheet->setCellValue("A{$row}", $txt);
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($thin, [
                            'font' => ['color' => ['argb' => 'FF166534']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FDF4']],
                        ]));
                        $row++;
                    }
                }

                // Data rows
                foreach ($items as $index => $item) {
                    $bgArgb = $index % 2 === 0 ? 'FFFFFFFF' : 'FFF9FAFB';

                    if ($isHarian) {
                        $sheet->setCellValue("A{$row}", $rowNumber);
                        $sheet->setCellValue("B{$row}", $item->namatenagakerja ?? '');
                        $sheet->setCellValue("C{$row}", \Carbon\Carbon::parse($item->lkhdate)->format('Y-m-d'));
                        $sheet->setCellValue("D{$row}", number_format($item->upah ?? 0, 2, ',', '.'));
                        $sheet->setCellValue("E{$row}", number_format($item->upahlembur ?? 0, 2, ',', '.'));
                        $sheet->setCellValue("F{$row}", number_format($item->total ?? 0, 2, ',', '.'));
                        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("D{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $subtotal += floatval($item->total ?? 0);
                    } else {
                        $sheet->setCellValue("A{$row}", $rowNumber);
                        $sheet->setCellValue("B{$row}", $item->plot);
                        $sheet->setCellValue("C{$row}", $item->materials ?? '');
                        $sheet->setCellValue("D{$row}", number_format($item->luasan, 2, ',', '.'));
                        $sheet->setCellValue("E{$row}", $item->statustanam);
                        $sheet->setCellValue("F{$row}", number_format($item->hasil, 2, ',', '.'));
                        $sheet->getStyle("C{$row}")->getAlignment()->setWrapText(true);
                        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                        // Tgl Kegiatan & Biaya: merge vertically for all rows in LKH (rowspan)
                        if ($index === 0) {
                            $itemCount = count($items);
                            $endRow = $row + $itemCount - 1;
                            $sheet->setCellValue("G{$row}", \Carbon\Carbon::parse($item->lkhdate)->format('Y-m-d'));
                            $sheet->setCellValue("H{$row}", number_format($item->totalupahall ?? 0, 2, ',', '.'));
                            if ($itemCount > 1) {
                                $sheet->mergeCells("G{$row}:G{$endRow}");
                                $sheet->mergeCells("H{$row}:H{$endRow}");
                            }
                            $sheet->getStyle("G{$row}")->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                            $sheet->getStyle("H{$row}")->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                            $subtotal += floatval($item->totalupahall ?? 0);
                        }
                    }

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($thin, [
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                    ]));
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $rowNumber++;
                    $row++;
                }

                $activitySubtotal += $subtotal;
            }

            // Subtotal per kegiatan
            $sheet->mergeCells("A{$row}:{$prevCol}{$row}");
            $sheet->setCellValue("A{$row}", "Subtotal \u{2014} {$activityName}");
            $sheet->setCellValue("{$lastCol}{$row}", 'Rp ' . number_format($activitySubtotal, 2, ',', '.'));
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($thin, [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF713F12']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]));
            $row++;
            $totalKeseluruhan += $activitySubtotal;
        }

        // Grand total
        $sheet->mergeCells("A{$row}:{$prevCol}{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
        $sheet->setCellValue("{$lastCol}{$row}", 'Rp ' . number_format($totalKeseluruhan, 2, ',', '.'));
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($thin, [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]));
        $row += 2;

        // Footer
        $sheet->setCellValue("A{$row}", 'Dicetak pada: ' . Carbon::now()->format('d/m/Y H:i'));

        // Save & stream
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $filename = 'Rekap_Upah_Mingguan_' . date('Y-m-d_His') . '.xlsx';
        $filePath = $tempDir . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}