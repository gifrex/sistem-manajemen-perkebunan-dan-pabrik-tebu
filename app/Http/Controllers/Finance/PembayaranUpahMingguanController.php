<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Repositories\Approval\UpahMingguanApprovalRepository;
use App\Services\Approval\UpahMingguanApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembayaranUpahMingguanController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Finance',
            'nav' => 'Pembayaran Upah Mingguan',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $title = 'Pembayaran Upah Mingguan';
        $search = $request->input('search', '');

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'nullable|integer|min:1']);
            if ($request->filled('perPage')) {
                $request->session()->put('perPage', $request->input('perPage'));
            }
        }

        if ($request->has('tenagakerjarum')) {
            session(['tenagakerjarum' => $request->tenagakerjarum]);
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));

        $filterMandors = $request->input('mandor_ids', []);
        if (is_string($filterMandors)) {
            $filterMandors = array_filter(explode(',', $filterMandors));
        }

        $perPage = $request->session()->get('perPage', 10);
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $companycode = session('companycode');

        // Mandor list — hanya kolom yang diperlukan
        $mandorList = DB::table('user')
            ->whereIn('userid', function ($q) use ($companycode, $tk) {
                $q->select('mandorid')
                    ->from('lkhhdr')
                    ->where('companycode', $companycode)
                    ->where('jenistenagakerja', $tk);
            })
            ->select('userid', 'name')
            ->orderBy('name')
            ->get();

        // ── Query utama: hilangkan LEFT JOIN activity & user dari subquery pagination,
        //    ambil setelah paginate agar window function tidak membebani COUNT(*) ──
        $rum = DB::table('pembayaranupahhdr as p')
            ->leftJoin('user as u', fn($j) => $j->on('u.userid', '=', 'p.mandoruserid')
                ->on('u.companycode', '=', 'p.companycode'))
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->where('p.companycode', $companycode)
            ->where('p.jenistenagakerja', $tk)
            ->when($startDate, fn($q) => $q->whereDate('p.generatedate', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('p.generatedate', '<=', $endDate))
            ->when(!empty($filterMandors), fn($q) => $q->whereIn('p.mandoruserid', $filterMandors))
            ->when(!empty($search), fn($q) => $q->where(
                fn($q2) =>
                $q2->where('p.transno', 'like', "%{$search}%")
                    ->orWhere('ac.activityname', 'like', "%{$search}%")
            ))
            ->select(
                'p.transno',
                'p.companycode',
                'p.activitycode',
                'p.jenistenagakerja',
                'p.grandtotal',
                'p.startdate',
                'p.enddate',
                'p.generatedate',
                'p.mandoruserid',
                'ac.activityname',
                'u.name as mandorname'
            )
            ->distinct()
            ->orderByDesc('p.startdate')
            ->orderByDesc('p.transno')
            ->paginate($perPage);

        if ($rum->count() > 0) {
            $transNos = $rum->pluck('transno')->toArray();

            // ── Plots: satu query dengan subquery join LKH ──────────────────────
            $plots = DB::table('pembayaranupahhdr as ph')
                ->join(
                    'lkhhdr as lh',
                    fn($j) =>
                    $j->on('lh.companycode', '=', 'ph.companycode')
                        ->on('lh.mandorid', '=', 'ph.mandoruserid')
                        ->on('lh.jenistenagakerja', '=', 'ph.jenistenagakerja')
                        ->on('lh.activitycode', '=', 'ph.activitycode')
                        ->whereColumn('lh.lkhdate', '>=', 'ph.startdate')
                        ->whereColumn('lh.lkhdate', '<=', 'ph.enddate')
                )
                ->join(
                    'lkhdetailplot as ldp',
                    fn($j) =>
                    $j->on('ldp.lkhno', '=', 'lh.lkhno')
                        ->on('ldp.companycode', '=', 'lh.companycode')
                )
                ->whereIn('ph.transno', $transNos)
                ->groupBy('ph.transno')
                ->select('ph.transno', DB::raw("GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ', ') as plots"))
                ->pluck('plots', 'transno');

            // ── Worker count & LKH count dalam SATU query ───────────────────────
            $workerCounts = DB::table('pembayaranupahlst')
                ->whereIn('transno', $transNos)
                ->where('companycode', $companycode)
                ->groupBy('transno')
                ->select('transno', DB::raw('COUNT(DISTINCT tenagakerjaid) as totalworkers'))
                ->pluck('totalworkers', 'transno');

            $lkhCounts = DB::table('pembayaranupahhdr as ph')
                ->join(
                    'lkhhdr as lh',
                    fn($j) =>
                    $j->on('lh.companycode', '=', 'ph.companycode')
                        ->on('lh.mandorid', '=', 'ph.mandoruserid')
                        ->on('lh.jenistenagakerja', '=', 'ph.jenistenagakerja')
                        ->whereColumn('lh.lkhdate', '>=', 'ph.startdate')
                        ->whereColumn('lh.lkhdate', '<=', 'ph.enddate')
                )
                ->whereIn('ph.transno', $transNos)
                ->groupBy('ph.transno')
                ->select('ph.transno', DB::raw('COUNT(DISTINCT lh.lkhno) as lkhcount'))
                ->pluck('lkhcount', 'transno');

            foreach ($rum as $idx => $item) {
                $item->no = ($rum->currentPage() - 1) * $rum->perPage() + $idx + 1;
                $item->plots = $plots[$item->transno] ?? '';
                $item->totalworkers = $workerCounts[$item->transno] ?? '-';
                $item->lkhcount = $lkhCounts[$item->transno] ?? 0;
                $item->grandtotal = $item->grandtotal == 0
                    ? '-'
                    : Number::currency($item->grandtotal, 'IDR', 'id');
            }
        }

        $sudahGenerate = false;
        if (session('tenagakerjarum') && $startDate && $endDate) {
            $sudahGenerate = DB::table('pembayaranupahhdr')
                ->where('companycode', $companycode)
                ->where('jenistenagakerja', $tk)
                ->where(
                    fn($q) =>
                    $q->whereDate('startdate', '<=', $endDate)
                        ->whereDate('enddate', '>=', $startDate)
                )
                ->exists();
        }

        return view('finance.pembayaranupahmingguan.index', compact(
            'title',
            'search',
            'perPage',
            'rum',
            'startDate',
            'endDate',
            'sudahGenerate',
            'mandorList',
            'filterMandors'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // GENERATE
    // ─────────────────────────────────────────────────────────────────────────────
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'tenagakerjarum' => 'required|in:Harian,Borongan',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $tenagakerjarum = $request->tenagakerjarum;
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $companycode = session('companycode');
        $userid = Auth::user()->userid;

        $existing = DB::table('pembayaranupahhdr')
            ->where('companycode', $companycode)
            ->where('jenistenagakerja', $tk)
            ->where(
                fn($q) =>
                $q->whereDate('startdate', '<=', $endDate)
                    ->whereDate('enddate', '>=', $startDate)
            )
            ->select('transno', 'startdate', 'enddate')
            ->first();

        if ($existing) {
            $s = Carbon::parse($existing->startdate)->format('d-m-Y');
            $e = Carbon::parse($existing->enddate)->format('d-m-Y');
            return response()->json([
                'success' => false,
                'message' => "Periode overlap dengan transaksi <strong>{$existing->transno}</strong> ({$s} s/d {$e}). Tidak dapat di-generate ulang.",
            ], 422);
        }

        $lkhList = DB::table('lkhhdr as a')
            ->join('activity as c', 'c.activitycode', '=', 'a.activitycode')
            ->where('a.companycode', $companycode)
            ->where('a.status', 'APPROVED')
            ->where('c.active', 1)
            ->where('a.jenistenagakerja', $tk)
            ->whereDate('a.lkhdate', '>=', $startDate)
            ->whereDate('a.lkhdate', '<=', $endDate)
            ->select('a.lkhno', 'a.activitycode', 'a.mandorid', 'a.totalupahall', 'a.lkhdate')
            ->get();

        if ($lkhList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data LKH APPROVED yang bisa di-generate untuk periode ini.',
            ], 422);
        }

        $allLkhNos = $lkhList->pluck('lkhno')->toArray();

        $upahBoronganAll = collect();
        if ($tk == 2) {
            $activitycodes = $lkhList->pluck('activitycode')->unique()->toArray();
            $upahBoronganAll = DB::table('upahborongan')
                ->where('companycode', $companycode)
                ->whereIn('activitycode', $activitycodes)
                ->select('activitycode', 'amount', 'effectivedate', 'enddate')
                ->orderBy('activitycode')->orderByDesc('effectivedate')
                ->get()->groupBy('activitycode');
        }

        $workersByLkh = collect();
        $plotsByLkh = collect();
        if ($tk == 1) {
            $workersByLkh = DB::table('lkhdetailworker')
                ->whereIn('lkhno', $allLkhNos)
                ->where('companycode', $companycode)
                ->select('lkhno', 'tenagakerjaid', 'totalupah')
                ->get()->groupBy('lkhno');
        } else {
            $plotsByLkh = DB::table('lkhdetailplot')
                ->whereIn('lkhno', $allLkhNos)
                ->where('companycode', $companycode)
                ->select('lkhno', 'plot', 'luashasil')
                ->get()->groupBy('lkhno');
        }

        $resolveUpah = function (string $activitycode, string $lkhdate) use ($upahBoronganAll): float {
            $rows = $upahBoronganAll[$activitycode] ?? collect();
            $match = $rows->first(
                fn($row) =>
                $row->effectivedate <= $lkhdate &&
                (is_null($row->enddate) || $row->enddate >= $lkhdate)
            );
            return $match ? floatval($match->amount) : 0.0;
        };

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $lkhByGroup = $lkhList->groupBy(fn($l) => $l->mandorid . '|' . $l->activitycode);
            $generatedNos = [];
            $usedTransnos = [];

            foreach ($lkhByGroup as $groupKey => $lkhGroup) {
                [$mandorId, $activitycode] = explode('|', $groupKey, 2);
                $transno = $this->generateTransno($now, $companycode, $tenagakerjarum, $usedTransnos);
                $grandTotal = 0;
                $listInserts = [];

                foreach ($lkhGroup as $lkh) {
                    if ($tk == 1) {
                        foreach ($workersByLkh[$lkh->lkhno] ?? [] as $w) {
                            $total = floatval($w->totalupah ?? 0);
                            $grandTotal += $total;
                            $listInserts[] = [
                                'transno' => $transno,
                                'companycode' => $companycode,
                                'tenagakerjaid' => $w->tenagakerjaid,
                                'tanggal' => $lkh->lkhdate,
                                'total' => $total,
                            ];
                        }
                    } else {
                        $upah = $resolveUpah($lkh->activitycode, $lkh->lkhdate);
                        foreach ($plotsByLkh[$lkh->lkhno] ?? [] as $p) {
                            $total = $upah * floatval($p->luashasil ?? 0);
                            $grandTotal += $total;
                            $listInserts[] = [
                                'transno' => $transno,
                                'companycode' => $companycode,
                                'tenagakerjaid' => $p->plot,
                                'tanggal' => $lkh->lkhdate,
                                'total' => $total,
                            ];
                        }
                    }
                }

                DB::table('pembayaranupahhdr')->insert([
                    'transno' => $transno,
                    'companycode' => $companycode,
                    'startdate' => $startDate,
                    'enddate' => $endDate,
                    'generatedate' => $now,
                    'mandoruserid' => $mandorId,
                    'activitycode' => $activitycode,
                    'jenistenagakerja' => $tk,
                    'grandtotal' => $grandTotal,
                    'approvalstatus' => null,
                    'createdat' => $now,
                    'inputby' => $userid,
                ]);

                foreach (array_chunk($listInserts, 500) as $chunk) {
                    DB::table('pembayaranupahlst')->insert($chunk);
                }

                $approvalService = new UpahMingguanApprovalService(
                    new UpahMingguanApprovalRepository()
                );
                $approvalService->createApprovalAfterGenerate(
                    $companycode,
                    $transno,
                    $userid,
                    Auth::user()->idjabatan
                );

                $generatedNos[] = $transno;
            }

            DB::commit();

            $count = count($generatedNos);
            $noList = implode(', ', $generatedNos);
            return response()->json([
                'success' => true,
                'message' => "Generate berhasil! {$count} transaksi dibuat: <strong>{$noList}</strong>",
                'transno' => $generatedNos,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // SHOW — Optimized: 2 query flat, hilangkan nested subquery berat
    // ─────────────────────────────────────────────────────────────────────────────
    public function show($transno)
    {
        $companycode = session('companycode');

        $header = DB::table('pembayaranupahhdr')
            ->where('transno', $transno)
            ->where('companycode', $companycode)
            ->first();

        if (!$header) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ambil lkhno sekaligus activitycode dalam satu query
        $lkhRows = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('mandorid', $header->mandoruserid)
            ->where('activitycode', $header->activitycode)
            ->where('jenistenagakerja', $header->jenistenagakerja)
            ->whereDate('lkhdate', '>=', $header->startdate)
            ->whereDate('lkhdate', '<=', $header->enddate)
            ->where('status', 'APPROVED')
            ->select('lkhno', 'activitycode')
            ->get();

        $lkhNos = $lkhRows->pluck('lkhno')->toArray();
        $activitycode = $lkhRows->first()->activitycode ?? $header->activitycode;

        if ($header->jenistenagakerja == 1) {
            // ── HARIAN ──────────────────────────────────────────────────────────
            // Plot summary dalam satu query aggregate
            $plotDetails = DB::table('lkhdetailplot')
                ->whereIn('lkhno', $lkhNos)
                ->where('companycode', $companycode)
                ->selectRaw("GROUP_CONCAT(DISTINCT plot ORDER BY plot SEPARATOR ', ') as plots,
                              SUM(luasrkh)   as total_luasrkh,
                              SUM(luashasil) as total_luashasil")
                ->first();

            // Batch info: ambil satu plot lalu satu query
            $firstPlot = DB::table('lkhdetailplot')
                ->whereIn('lkhno', $lkhNos)
                ->where('companycode', $companycode)
                ->orderBy('plot')->value('plot');

            $batchInfo = null;
            if ($firstPlot) {
                $batchInfo = DB::table('batch')
                    ->where('companycode', $companycode)
                    ->where('plot', $firstPlot)
                    ->where('isactive', 1)
                    ->selectRaw("lifecyclestatus,
                        CONCAT(
                            CASE MONTH(batchdate)
                                WHEN 1 THEN 'JAN' WHEN 2  THEN 'FEB' WHEN 3  THEN 'MAR'
                                WHEN 4 THEN 'APR' WHEN 5  THEN 'MEI' WHEN 6  THEN 'JUN'
                                WHEN 7 THEN 'JUL' WHEN 8  THEN 'AGU' WHEN 9  THEN 'SEP'
                                WHEN 10 THEN 'OKT' WHEN 11 THEN 'NOV' WHEN 12 THEN 'DES'
                            END, \"'\", RIGHT(YEAR(batchdate), 2)
                        ) AS batchdate")
                    ->first();
            }

            // Detail worker: satu query flat dengan aggregate upah per worker
            $datas = DB::table('pembayaranupahlst as pl')
                ->leftJoin('tenagakerja as tk', fn($j) =>
                    $j->on('tk.tenagakerjaid', '=', 'pl.tenagakerjaid')
                        ->on('tk.companycode', '=', 'pl.companycode'))
                ->leftJoin('lkhdetailworker as dw', function ($j) use ($lkhNos) {
                    $j->on('dw.tenagakerjaid', '=', 'pl.tenagakerjaid')
                        ->on('dw.companycode', '=', 'pl.companycode')
                        ->whereIn('dw.lkhno', $lkhNos);
                })
                ->where('pl.transno', $transno)
                ->where('pl.companycode', $companycode)
                ->groupBy('pl.transno', 'pl.tenagakerjaid', 'tk.nama', 'pl.tanggal', 'pl.total')
                ->select(
                    'pl.transno',
                    'pl.tenagakerjaid',
                    'tk.nama as namatenagakerja',
                    DB::raw('MAX(dw.upahharian) as upah'),
                    'pl.tanggal',
                    'pl.total'
                )
                ->orderBy('pl.tanggal')->orderBy('tk.nama')
                ->get();

            $extraPlot = $plotDetails->plots ?? '';
            $extraLuasrkh = $plotDetails->total_luasrkh ?? 0;
            $extraHasil = $plotDetails->total_luashasil ?? 0;
            $extraLC = $batchInfo->lifecyclestatus ?? '-';
            $extraBD = $batchInfo->batchdate ?? '-';

            foreach ($datas as $item) {
                $item->plot = $extraPlot;
                $item->luasrkh = $extraLuasrkh;
                $item->luashasil = $extraHasil;
                $item->lifecyclestatus = $extraLC;
                $item->batchdate = $extraBD;
            }

        } else {
            // ── BORONGAN ─────────────────────────────────────────────────────────
            // Hapus nested subquery berat (tanggalulangtahun) — tidak dipakai di view.
            // Upah borongan cukup ambil amount yang efektif, bukan MAX tak-terkorelasi.
            $upahMap = DB::table('upahborongan')
                ->where('companycode', $companycode)
                ->where('activitycode', $activitycode)
                ->select('amount', 'effectivedate', 'enddate')
                ->orderByDesc('effectivedate')
                ->get();

            // Ambil pl + ldp aggregate, lalu resolve upah di PHP (lebih ringan daripada JOIN)
            $datas = DB::table('pembayaranupahlst as pl')
                ->leftJoin('lkhdetailplot as ldp', function ($j) use ($lkhNos) {
                    $j->on('ldp.plot', '=', 'pl.tenagakerjaid')
                        ->on('ldp.companycode', '=', 'pl.companycode')
                        ->whereIn('ldp.lkhno', $lkhNos);
                })
                ->where('pl.transno', $transno)
                ->where('pl.companycode', $companycode)
                ->groupBy('pl.transno', 'pl.tenagakerjaid', 'pl.tanggal')
                ->select(
                    'pl.transno',
                    'pl.tenagakerjaid as plot',
                    DB::raw('SUM(ldp.luasrkh)   as luasrkh'),
                    DB::raw('SUM(ldp.luashasil) as luashasil'),
                    DB::raw('SUM(pl.total)      as total'),
                    'pl.tanggal'
                )
                ->orderBy('pl.tenagakerjaid')
                ->get();

            // Resolve upah per tanggal di PHP — hindari JOIN N-to-N di SQL
            foreach ($datas as $item) {
                $tanggal = $item->tanggal;
                $matched = $upahMap->first(
                    fn($r) =>
                    $r->effectivedate <= $tanggal &&
                    (is_null($r->enddate) || $r->enddate >= $tanggal)
                );
                $item->upah = $matched ? floatval($matched->amount) : 0.0;
            }
        }

        if ($datas->isEmpty()) {
            return response()->json(['error' => 'Tidak ada data detail untuk transaksi ini'], 404);
        }

        foreach ($datas as $idx => $item) {
            $item->no = $idx + 1;
            $upahVal = floatval($item->upah ?? 0);
            $totalVal = floatval($item->total ?? 0);

            if ($header->jenistenagakerja == 2) {
                $totalVal = $upahVal * floatval($item->luashasil ?? 0);
            }

            $item->upah = Number::currency($upahVal, 'IDR', 'id');
            $item->total = Number::currency($totalVal, 'IDR', 'id');
        }

        return response()->json(['data' => $datas, 'header' => $header]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
// EXPORT EXCEL  (entry point)
// ─────────────────────────────────────────────────────────────────────────────
    public function exportExcel(Request $request): StreamedResponse
    {
        $companycode = session('companycode');
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $filterMandors = $request->input('mandor_ids', []);
        if (is_string($filterMandors)) {
            $filterMandors = array_filter(explode(',', $filterMandors));
        }

        // ── 1. Headers ──────────────────────────────────────────────────────────
        $headers = DB::table('pembayaranupahhdr as p')
            ->leftJoin('user as u', fn($j) => $j->on('u.userid', '=', 'p.mandoruserid')
                ->on('u.companycode', '=', 'p.companycode'))
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->where('p.companycode', $companycode)
            ->where('p.jenistenagakerja', $tk)
            ->when($startDate, fn($q) => $q->whereDate('p.generatedate', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('p.generatedate', '<=', $endDate))
            ->when(!empty($filterMandors), fn($q) => $q->whereIn('p.mandoruserid', $filterMandors))
            ->select(
                'p.transno',
                'p.activitycode',
                'p.grandtotal',
                'p.startdate',
                'p.enddate',
                'p.generatedate',
                'p.mandoruserid',
                'p.jenistenagakerja',
                'ac.activityname',
                'u.name as mandorname'
            )
            ->distinct()
            ->orderByDesc('p.startdate')
            ->orderByDesc('p.transno')
            ->get();

        if ($headers->isEmpty()) {
            abort(404, 'Tidak ada data untuk diekspor.');
        }

        $transNos = $headers->pluck('transno')->toArray();

        // ── 2. Detail — satu query untuk semua transno ──────────────────────────
        if ($tk === 1) {
            // Harian: join tenagakerja, kelompokkan di PHP
            $detailMap = DB::table('pembayaranupahlst as pl')
                ->leftJoin('tenagakerja as tkj', fn($j) =>
                    $j->on('tkj.tenagakerjaid', '=', 'pl.tenagakerjaid')
                        ->on('tkj.companycode', '=', 'pl.companycode'))
                ->whereIn('pl.transno', $transNos)
                ->where('pl.companycode', $companycode)
                ->select('pl.transno', 'pl.tenagakerjaid', 'tkj.nama as namaworker', 'pl.tanggal', 'pl.total')
                ->orderBy('pl.transno')->orderBy('pl.tanggal')->orderBy('tkj.nama')
                ->get()
                ->groupBy('transno');

            $upahMap = collect(); // tidak dipakai pada harian
        } else {
            // Borongan: tanpa join extra, upah di-resolve di PHP
            $detailMap = DB::table('pembayaranupahlst as pl')
                ->whereIn('pl.transno', $transNos)
                ->where('pl.companycode', $companycode)
                ->select('pl.transno', 'pl.tenagakerjaid as plot', 'pl.tanggal', 'pl.total')
                ->orderBy('pl.transno')->orderBy('pl.tenagakerjaid')
                ->get()
                ->groupBy('transno');

            // Semua upah borongan yang relevan, dikelompokkan per activitycode
            $activitycodes = $headers->pluck('activitycode')->unique()->toArray();
            $upahMap = DB::table('upahborongan')
                ->where('companycode', $companycode)
                ->whereIn('activitycode', $activitycodes)
                ->select('activitycode', 'amount', 'effectivedate', 'enddate')
                ->orderByDesc('effectivedate')
                ->get()
                ->groupBy('activitycode');
        }

        // ── 3. Build & stream spreadsheet ───────────────────────────────────────
        $filename = 'Pembayaran_Upah_Mingguan_' . $tenagakerjarum
            . '_' . ($startDate ?? date('Ymd'))
            . '_sd_' . ($endDate ?? date('Ymd')) . '.xlsx';

        return response()->streamDownload(
            fn() => $this->buildSpreadsheet($headers, $detailMap, $upahMap, $tk, $tenagakerjarum, $startDate, $endDate),
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────────
// BUILD SPREADSHEET  (writer — hanya urusan presentasi)
// ─────────────────────────────────────────────────────────────────────────────
    private function buildSpreadsheet(
        $headers,
        $detailMap,
        $upahMap,
        int $tk,
        string $tenagakerjarum,
        ?string $startDate,
        ?string $endDate
    ): void {
        $isHarian = $tk === 1;
        $cols = $isHarian
            ? ['No.', 'ID TKH', 'Nama TKH', 'Tanggal', 'Total (Rp)']
            : ['No.', 'Plot', 'Tanggal', 'Total (Rp)'];
        $lastCol = $isHarian ? 'E' : 'D';
        $totalCol = $lastCol;              // kolom angka Total
        $mergeEnd = $isHarian ? 'D' : 'C';// kolom sebelum Total (untuk merge label)

        // ── Styles (didefinisikan sekali) ────────────────────────────────────────
        $sTitle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sTblHdr = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sSection = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E1B4B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C7D2FE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6366F1']]],
        ];
        $sSubtotal = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E7FF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6366F1']]],
        ];
        $sGrandTotal = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4338CA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '312E81']]],
        ];
        $sBorderHair = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'D1D5DB']]]];
        $sBorderThin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]]];
        $sStripe = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']]];
        $numFmt = '#,##0.00';

        // ── Init sheet ───────────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Upah Mingguan');

        $row = 1;

        // Judul
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'LAPORAN PEMBAYARAN UPAH MINGGUAN - ' . strtoupper($tenagakerjarum));
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sTitle);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'Periode: ' . ($startDate ?? '-') . ' s/d ' . ($endDate ?? '-'));
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        $grandSumAll = 0;

        // ── Loop transaksi ───────────────────────────────────────────────────────
        foreach ($headers as $hdr) {
            $transno = $hdr->transno;
            $activity = $hdr->activityname ?? $hdr->activitycode;
            $mandor = $hdr->mandorname ?? $hdr->mandoruserid;
            $start = Carbon::parse($hdr->startdate)->format('d-m-Y');
            $end = Carbon::parse($hdr->enddate)->format('d-m-Y');
            $genDate = $hdr->generatedate ? Carbon::parse($hdr->generatedate)->format('d-m-Y') : '-';

            // Section title
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", "No. Transaksi : {$transno}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sSection);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;

            // Info rows
            foreach ([
                'Aktivitas' => $activity,
                'Mandor' => $mandor,
                'Periode LKH' => "{$start} s/d {$end}",
                'Tgl. Generate' => $genDate,
            ] as $label => $value) {
                $sheet->setCellValue("A{$row}", $label);
                $sheet->setCellValue("B{$row}", $value);
                if (strlen($lastCol) === 1 && $lastCol > 'B') {
                    $sheet->mergeCells("B{$row}:{$lastCol}{$row}");
                }
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sBorderHair);
                $row++;
            }
            $row++;

            // Table header
            $sheet->fromArray($cols, null, "A{$row}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sTblHdr);
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;

            // ── Detail rows ──────────────────────────────────────────────────────
            $subtotal = 0;
            $no = 1;
            $items = $detailMap[$transno] ?? collect();

            if ($isHarian) {
                foreach ($items as $item) {
                    $total = floatval($item->total ?? 0);
                    $subtotal += $total;
                    $sheet->fromArray([
                        $no++,
                        $item->tenagakerjaid,
                        $item->namaworker ?? '-',
                        Carbon::parse($item->tanggal)->format('d-m-Y'),
                        $total
                    ], null, "A{$row}");
                    $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    if ($no % 2 === 0)
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sStripe);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sBorderThin);
                    $row++;
                }
            } else {
                $upahRows = $upahMap[$hdr->activitycode] ?? collect();
                foreach ($items as $item) {
                    $matched = $upahRows->first(
                        fn($r) =>
                        $r->effectivedate <= $item->tanggal &&
                        (is_null($r->enddate) || $r->enddate >= $item->tanggal)
                    );
                    $total = floatval($item->total ?? 0);
                    $subtotal += $total;
                    $sheet->fromArray([
                        $no++,
                        $item->plot,
                        Carbon::parse($item->tanggal)->format('d-m-Y'),
                        $total
                    ], null, "A{$row}");
                    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    if ($no % 2 === 0)
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sStripe);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sBorderThin);
                    $row++;
                }
            }

            // Subtotal
            $grandSumAll += $subtotal;
            $sheet->mergeCells("A{$row}:{$mergeEnd}{$row}");
            $sheet->setCellValue("A{$row}", 'Subtotal ' . $transno);
            $sheet->setCellValue("{$totalCol}{$row}", $subtotal);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sSubtotal);
            $sheet->getStyle("A{$row}:{$mergeEnd}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode($numFmt);
            $row += 2;
        }

        // Grand Total
        $sheet->mergeCells("A{$row}:{$mergeEnd}{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->setCellValue("{$totalCol}{$row}", $grandSumAll);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sGrandTotal);
        $sheet->getStyle("A{$row}:{$mergeEnd}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode($numFmt);
        $sheet->getRowDimension($row)->setRowHeight(22);

        // Auto width
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        (new Xlsx($spreadsheet))->save('php://output');
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────────────────────────────────────────
    private function generateTransno(Carbon $now, string $companycode, string $tenagakerjarum, array &$usedTransnos): string
    {
        $day = $now->format('d');
        $month = $now->format('m');
        $year = $now->format('y');
        $prefix = $tenagakerjarum === 'Harian' ? 'TKH' : 'TKB';
        $pLen = strlen($prefix);
        $like = "{$prefix}{$day}{$month}%{$year}";

        $last = DB::table('pembayaranupahhdr')
            ->where('companycode', $companycode)
            ->where('transno', 'like', $like)
            ->orderByDesc(DB::raw("CAST(SUBSTRING(transno, " . ($pLen + 5) . ", 2) AS UNSIGNED)"))
            ->value('transno');

        $seq = $last ? (int) substr($last, $pLen + 4, 2) : 0;

        do {
            $seq++;
            $transno = "{$prefix}{$day}{$month}" . str_pad($seq, 2, '0', STR_PAD_LEFT) . "{$year}";
        } while (in_array($transno, $usedTransnos));

        $usedTransnos[] = $transno;
        return $transno;
    }
}