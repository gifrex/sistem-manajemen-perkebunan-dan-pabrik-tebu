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
use Illuminate\Http\JsonResponse;
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
                    ->where('jenistenagakerja', $tk)
                    ->where('status', 'APPROVED');
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
                'p.approvalstatus as approval_status',
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

            // ── Approval progress dari approvaltransaction ───────────────────────
            $approvalRows = DB::table('approvaltransaction')
                ->whereIn('transactionnumber', $transNos)
                ->where('companycode', $companycode)
                ->select(
                    'transactionnumber',
                    'jumlahapproval',
                    'approval1flag',
                    'approval2flag',
                    'approval3flag',
                    'approval4flag',
                    'approval5flag',
                )
                ->get()
                ->keyBy('transactionnumber');

            foreach ($rum as $idx => $item) {
                $item->no = ($rum->currentPage() - 1) * $rum->perPage() + $idx + 1;
                $item->plots = $plots[$item->transno] ?? '';
                $item->totalworkers = $workerCounts[$item->transno] ?? '-';
                $item->lkhcount = $lkhCounts[$item->transno] ?? 0;
                $item->grandtotal = $item->grandtotal == 0
                    ? '-'
                    : Number::currency($item->grandtotal, 'IDR', 'id');
                // approval_status sudah diambil dari p.approvalstatus: APPROVED / DRAFT / DECLINED
                $item->approval_status = $item->approval_status ?? 'DRAFT';

                // Hitung progress approval
                $aRow = $approvalRows[$item->transno] ?? null;
                if ($aRow) {
                    $total = intval($aRow->jumlahapproval ?? 0);
                    $done = 0;
                    for ($i = 1; $i <= 5; $i++) {
                        $col = "approval{$i}flag";
                        if (($aRow->$col ?? null) === '1')
                            $done++;
                    }
                    $item->approval_progress = "{$done}/{$total}";
                } else {
                    $item->approval_progress = null;
                }
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
    // GENERATE — validasi, pre-check, dan persiapan data
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

        // Sinkronkan session agar AJAX refresh setelah generate menampilkan jenis yang benar
        session(['tenagakerjarum' => $tenagakerjarum]);
        $userid = Auth::user()->userid;

        $existing = DB::table('pembayaranupahhdr')
            ->where('companycode', $companycode)
            ->where('jenistenagakerja', $tk)
            ->where(fn($q) => $q->whereDate('startdate', '<=', $endDate)->whereDate('enddate', '>=', $startDate))
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

        $lkhByGroup = $lkhList->groupBy(fn($l) => $l->mandorid . '|' . $l->activitycode);

        try {
            $generatedNos = $this->executeGenerate(
                $lkhByGroup,
                $workersByLkh,
                $plotsByLkh,
                $upahBoronganAll,
                $companycode,
                $tenagakerjarum,
                $startDate,
                $endDate,
                $tk,
                $userid
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }

        $count = count($generatedNos);
        $noList = implode(', ', $generatedNos);
        return response()->json([
            'success' => true,
            'message' => "Generate berhasil! {$count} transaksi dibuat: <strong>{$noList}</strong>",
            'transno' => $generatedNos,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // EXECUTE GENERATE — DB transaction: insert hdr, lst, dan approval
    // ─────────────────────────────────────────────────────────────────────────────
    private function executeGenerate(
        $lkhByGroup,
        $workersByLkh,
        $plotsByLkh,
        $upahBoronganAll,
        string $companycode,
        string $tenagakerjarum,
        string $startDate,
        string $endDate,
        int $tk,
        string $userid
    ): array {
        DB::beginTransaction();
        try {
            $now = Carbon::now();
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
                        $upahRows = $upahBoronganAll[$activitycode] ?? collect();
                        $upah = $upahRows->first(
                            fn($row) => $row->effectivedate <= $lkh->lkhdate &&
                            (is_null($row->enddate) || $row->enddate >= $lkh->lkhdate)
                        );
                        $upahAmount = $upah ? floatval($upah->amount) : 0.0;
                        foreach ($plotsByLkh[$lkh->lkhno] ?? [] as $p) {
                            $total = $upahAmount * floatval($p->luashasil ?? 0);
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
                    'approvalstatus' => 'DRAFT',
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
            return $generatedNos;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // SHOW — Optimized: 2 query flat, hilangkan nested subquery berat
    // ─────────────────────────────────────────────────────────────────────────────
    public function show($transno)
    {
        $companycode = session('companycode');

        $header = DB::table('pembayaranupahhdr as p')
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->leftJoin('user as u', fn($j) => $j->on('u.userid', '=', 'p.mandoruserid')->on('u.companycode', '=', 'p.companycode'))
            ->where('p.transno', $transno)
            ->where('p.companycode', $companycode)
            ->select('p.*', 'ac.activityname', 'u.name as mandorname')
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
                    DB::raw('MAX(dw.upahlembur) as upahlembur'),
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
            $item->upahlembur = Number::currency(\floatval($item->upahlembur ?? 0), 'IDR', 'id');
            $item->total = Number::currency($totalVal, 'IDR', 'id');
        }

        return response()->json(['data' => $datas, 'header' => $header]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // EXPORT EXCEL  (entry point)
    // ─────────────────────────────────────────────────────────────────────────────
    public function exportExcel(Request $request): StreamedResponse|JsonResponse
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

        // ── 1. Headers — hanya yang sudah APPROVED, urutkan per mandor ──────────
        $headers = DB::table('pembayaranupahhdr as p')
            ->leftJoin('user as u', fn($j) => $j->on('u.userid', '=', 'p.mandoruserid')
                ->on('u.companycode', '=', 'p.companycode'))
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->where('p.companycode', $companycode)
            ->where('p.jenistenagakerja', $tk)
            ->where('p.approvalstatus', 'APPROVED')
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
            ->orderBy('p.mandoruserid')
            ->orderBy('p.activitycode')
            ->orderByDesc('p.startdate')
            ->get();

        if ($headers->isEmpty()) {
            return response()->json(['error' => 'Tidak ada data APPROVED untuk diekspor pada periode dan filter yang dipilih.'], 200);
        }

        $transNos = $headers->pluck('transno')->toArray();
        $mandorGroups = $headers->groupBy('mandoruserid');

        // ── 2. Detail & plot info ────────────────────────────────────────────────
        $plotInfoByMandor = collect();

        if ($tk === 1) {
            // Harian: satu baris per TKH, dirangkum dari semua aktivitas.
            // tanggal_awal/akhir = rentang kerja, jumlah_hari = distinct hari kerja,
            // upahharian = MAX dari lkhdetailworker, total = SUM semua daily payment.
            $detailByMandor = DB::table('pembayaranupahlst as pl')
                ->join('pembayaranupahhdr as ph', fn($j) =>
                    $j->on('ph.transno', '=', 'pl.transno')
                        ->on('ph.companycode', '=', 'pl.companycode'))
                ->leftJoin('tenagakerja as tkj', fn($j) =>
                    $j->on('tkj.tenagakerjaid', '=', 'pl.tenagakerjaid')
                        ->on('tkj.companycode', '=', 'pl.companycode'))
                ->leftJoin('lkhhdr as lh', fn($j) =>
                    $j->on('lh.companycode', '=', 'ph.companycode')
                        ->on('lh.mandorid', '=', 'ph.mandoruserid')
                        ->on('lh.activitycode', '=', 'ph.activitycode')
                        ->whereColumn('lh.lkhdate', '=', 'pl.tanggal')
                        ->where('lh.status', 'APPROVED'))
                ->leftJoin('lkhdetailworker as dw', fn($j) =>
                    $j->on('dw.tenagakerjaid', '=', 'pl.tenagakerjaid')
                        ->on('dw.companycode', '=', 'pl.companycode')
                        ->on('dw.lkhno', '=', 'lh.lkhno'))
                ->whereIn('pl.transno', $transNos)
                ->where('pl.companycode', $companycode)
                ->groupBy('ph.mandoruserid', 'pl.tenagakerjaid', 'tkj.nama')
                ->select(
                    'ph.mandoruserid',
                    'pl.tenagakerjaid',
                    'tkj.nama as namaworker',
                    DB::raw('MIN(pl.tanggal) as tanggal_awal'),
                    DB::raw('MAX(pl.tanggal) as tanggal_akhir'),
                    DB::raw('COUNT(DISTINCT pl.tanggal) as jumlah_hari'),
                    DB::raw('MAX(dw.upahharian) as upahharian'),
                    DB::raw('MAX(dw.upahlembur) as upahlembur'),
                    DB::raw('SUM(pl.total) as total')
                )
                ->orderBy('ph.mandoruserid')
                ->orderBy('tkj.nama')
                ->get()
                ->groupBy('mandoruserid');

            // Plot per aktivitas per mandor — ditampilkan di atas tabel
            $plotInfoByMandor = DB::table('pembayaranupahhdr as ph')
                ->join('lkhhdr as lh', fn($j) =>
                    $j->on('lh.companycode', '=', 'ph.companycode')
                        ->on('lh.mandorid', '=', 'ph.mandoruserid')
                        ->on('lh.activitycode', '=', 'ph.activitycode')
                        ->whereColumn('lh.lkhdate', '>=', 'ph.startdate')
                        ->whereColumn('lh.lkhdate', '<=', 'ph.enddate')
                        ->where('lh.status', 'APPROVED'))
                ->join('lkhdetailplot as ldp', fn($j) =>
                    $j->on('ldp.lkhno', '=', 'lh.lkhno')
                        ->on('ldp.companycode', '=', 'lh.companycode'))
                ->leftJoin('activity as ac', 'ac.activitycode', '=', 'ph.activitycode')
                ->whereIn('ph.transno', $transNos)
                ->where('ph.companycode', $companycode)
                ->groupBy('ph.mandoruserid', 'ph.activitycode', 'ac.activityname')
                ->select(
                    'ph.mandoruserid',
                    'ph.activitycode',
                    'ac.activityname',
                    DB::raw("GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ', ') as plots")
                )
                ->orderBy('ph.mandoruserid')
                ->orderBy('ph.activitycode')
                ->get()
                ->groupBy('mandoruserid');
        } else {
            // Borongan: satu baris per (plot, aktivitas, tanggal)
            $detailByMandor = DB::table('pembayaranupahlst as pl')
                ->join('pembayaranupahhdr as ph', fn($j) =>
                    $j->on('ph.transno', '=', 'pl.transno')
                        ->on('ph.companycode', '=', 'pl.companycode'))
                ->leftJoin('activity as ac', 'ac.activitycode', '=', 'ph.activitycode')
                ->leftJoin('lkhhdr as lh', fn($j) =>
                    $j->on('lh.companycode', '=', 'ph.companycode')
                        ->on('lh.mandorid', '=', 'ph.mandoruserid')
                        ->on('lh.activitycode', '=', 'ph.activitycode')
                        ->whereColumn('lh.lkhdate', '=', 'pl.tanggal')
                        ->where('lh.status', 'APPROVED'))
                ->leftJoin('lkhdetailplot as ldp', fn($j) =>
                    $j->on('ldp.lkhno', '=', 'lh.lkhno')
                        ->on('ldp.companycode', '=', 'lh.companycode')
                        ->on('ldp.plot', '=', 'pl.tenagakerjaid'))
                ->whereIn('pl.transno', $transNos)
                ->where('pl.companycode', $companycode)
                ->groupBy(
                    'ph.mandoruserid',
                    'pl.transno',
                    'ph.activitycode',
                    'ac.activityname',
                    'pl.tenagakerjaid',
                    'pl.tanggal',
                    'pl.total'
                )
                ->select(
                    'ph.mandoruserid',
                    'pl.transno',
                    'ph.activitycode',
                    'ac.activityname',
                    'pl.tenagakerjaid as plot',
                    'pl.tanggal',
                    'pl.total',
                    DB::raw("COALESCE(SUM(ldp.luashasil), 0) as luashasil")
                )
                ->orderBy('ph.mandoruserid')
                ->orderBy('ph.activitycode')
                ->orderBy('pl.tenagakerjaid')
                ->orderBy('pl.tanggal')
                ->get()
                ->groupBy('mandoruserid');
        }

        // ── 3. Build & stream spreadsheet ───────────────────────────────────────
        $filename = 'Pembayaran_Upah_Mingguan_' . $tenagakerjarum
            . '_' . ($startDate ?? date('Ymd'))
            . '_sd_' . ($endDate ?? date('Ymd')) . '.xlsx';

        return response()->streamDownload(
            fn() => $this->buildSpreadsheet(
                $mandorGroups,
                $detailByMandor,
                $plotInfoByMandor,
                $tk,
                $tenagakerjarum,
                $startDate,
                $endDate
            ),
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
    // Harian : 1 baris per TKH (dirangkum), plot & aktivitas di atas tabel, TTD 1x.
    // Borongan: 1 baris per (plot, aktivitas, tanggal).
    // ─────────────────────────────────────────────────────────────────────────────
    private function buildSpreadsheet(
        $mandorGroups,
        $detailByMandor,
        $plotInfoByMandor,
        int $tk,
        string $tenagakerjarum,
        ?string $startDate,
        ?string $endDate
    ): void {
        $isHarian = $tk === 1;

        // ── Konfigurasi kolom ────────────────────────────────────────────────────
        if ($isHarian) {
            // A: No | B: ID TKH | C: Nama TKH | D: Periode Kerja | E: Jml.Hari | F: Upah Harian | G: Upah Lembur | H: Total | I: TTD
            $cols = ['No.', 'ID TKH', 'Nama TKH', 'Periode Kerja', 'Jml. Hari', 'Upah Harian (Rp)', 'Upah Lembur (Rp)', 'Total (Rp)', 'TTD'];
            $lastCol = 'I';
            $totalCol = 'H';
            $mergeEnd = 'G';
        } else {
            // A: No | B: Plot | C: Aktivitas | D: Luas Hasil (Ha) | E: Tanggal | F: Total | G: TTD
            $cols = ['No.', 'Plot', 'Aktivitas', 'Luas Hasil (Ha)', 'Tanggal', 'Total (Rp)', 'TTD'];
            $lastCol = 'G';
            $totalCol = 'F';
            $mergeEnd = 'E';
            $dateCol = 'E';
        }

        // ── Styles ───────────────────────────────────────────────────────────────
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
        $sMandorHdr = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E1B4B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C7D2FE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6366F1']]],
        ];
        $sPlotInfo = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1E3A5F']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => '93C5FD']]],
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
        $sStripeA = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]];
        $sStripeB = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']]];
        $numFmt = '#,##0.00';

        // ── Init spreadsheet ─────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Upah Mingguan');
        $row = 1;

        // Judul & periode
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

        // ── Loop per mandor ───────────────────────────────────────────────────────
        foreach ($mandorGroups as $mandorid => $mandorHdrs) {
            $mandorName = $mandorHdrs->first()->mandorname ?? $mandorid;
            $mandorDetail = $detailByMandor[$mandorid] ?? collect();

            $actList = $mandorHdrs->map(fn($h) => $h->activityname ?? $h->activitycode)->unique()->values()->implode(' | ');
            $transnoStr = $mandorHdrs->pluck('transno')->implode(', ');
            $periodeStr = Carbon::parse($mandorHdrs->min('startdate'))->format('d-m-Y')
                . ' s/d '
                . Carbon::parse($mandorHdrs->max('enddate'))->format('d-m-Y');

            // ── Mandor section header ─────────────────────────────────────────────
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", "MANDOR : {$mandorName}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sMandorHdr);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;

            // Info: No. Transaksi, Aktivitas, Periode
            foreach ([
                'No. Transaksi' => $transnoStr,
                'Aktivitas' => $actList,
                'Periode LKH' => $periodeStr,
            ] as $label => $value) {
                $sheet->mergeCells("A{$row}:B{$row}");
                $sheet->setCellValue("A{$row}", $label);
                $sheet->setCellValue("C{$row}", $value);
                $sheet->mergeCells("C{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sBorderHair);
                $row++;
            }

            // ── Plot per aktivitas (Harian only) — di atas tabel ─────────────────
            if ($isHarian) {
                $mandorPlots = $plotInfoByMandor[$mandorid] ?? collect();
                if ($mandorPlots->isNotEmpty()) {
                    foreach ($mandorPlots as $plotRow) {
                        $label = 'Plot ' . ($plotRow->activityname ?? $plotRow->activitycode ?? '-');
                        $sheet->mergeCells("A{$row}:B{$row}");
                        $sheet->setCellValue("A{$row}", $label);
                        $sheet->setCellValue("C{$row}", $plotRow->plots ?? '-');
                        $sheet->mergeCells("C{$row}:{$lastCol}{$row}");
                        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sPlotInfo);
                        $row++;
                    }
                }
            }
            $row++; // baris kosong sebelum tabel

            // ── Table header ──────────────────────────────────────────────────────
            $sheet->fromArray($cols, null, "A{$row}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sTblHdr);
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;

            $subtotal = 0;
            $no = 1;

            if ($isHarian) {
                // ── Harian: 1 baris per TKH ──────────────────────────────────────
                foreach ($mandorDetail as $wr) {
                    $total = \floatval($wr->total ?? 0);
                    $upah = \floatval($wr->upahharian ?? 0);
                    $subtotal += $total;

                    $periodeKerja = Carbon::parse($wr->tanggal_awal)->format('d-m-Y')
                        . ' s/d '
                        . Carbon::parse($wr->tanggal_akhir)->format('d-m-Y');

                    $lembur = \floatval($wr->upahlembur ?? 0);

                    $sheet->setCellValue("A{$row}", $no);
                    $sheet->setCellValue("B{$row}", $wr->tenagakerjaid);
                    $sheet->setCellValue("C{$row}", $wr->namaworker ?? '-');
                    $sheet->setCellValue("D{$row}", $periodeKerja);
                    $sheet->setCellValue("E{$row}", \intval($wr->jumlah_hari ?? 0));
                    $sheet->setCellValue("F{$row}", $upah);
                    $sheet->setCellValue("G{$row}", $lembur);
                    $sheet->setCellValue("H{$row}", $total);
                    // I = TTD (kosong, tanda tangan sekali di sini)

                    $stripe = ($no % 2 === 1) ? $sStripeB : $sStripeA;
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($stripe);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sBorderThin);
                    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $no++;
                    $row++;
                }
            } else {
                // ── Borongan: baris per (plot, aktivitas, tanggal) ───────────────
                foreach ($mandorDetail as $item) {
                    $total = \floatval($item->total ?? 0);
                    $luas = \floatval($item->luashasil ?? 0);
                    $subtotal += $total;

                    $sheet->setCellValue("A{$row}", $no);
                    $sheet->setCellValue("B{$row}", $item->plot);
                    $sheet->setCellValue("C{$row}", $item->activityname ?? $item->activitycode ?? '-');
                    $sheet->setCellValue("D{$row}", $luas > 0 ? $luas : '-');
                    $sheet->setCellValue("E{$row}", Carbon::parse($item->tanggal)->format('d-m-Y'));
                    $sheet->setCellValue("F{$row}", $total);
                    // G = TTD (kosong)

                    $stripe = ($no % 2 === 1) ? $sStripeB : $sStripeA;
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($stripe);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sBorderThin);
                    $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode($numFmt);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$dateCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $no++;
                    $row++;
                }
            }

            // ── Subtotal per mandor ───────────────────────────────────────────────
            $grandSumAll += $subtotal;
            $sheet->mergeCells("A{$row}:{$mergeEnd}{$row}");
            $sheet->setCellValue("A{$row}", "Subtotal Mandor : {$mandorName}");
            $sheet->setCellValue("{$totalCol}{$row}", $subtotal);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sSubtotal);
            $sheet->getStyle("A{$row}:{$mergeEnd}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode($numFmt);
            $row += 2;
        }

        // ── Grand Total ───────────────────────────────────────────────────────────
        $sheet->mergeCells("A{$row}:{$mergeEnd}{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->setCellValue("{$totalCol}{$row}", $grandSumAll);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($sGrandTotal);
        $sheet->getStyle("A{$row}:{$mergeEnd}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode($numFmt);
        $sheet->getRowDimension($row)->setRowHeight(22);

        // ── Lebar kolom ──────────────────────────────────────────────────────────
        // Kolom A dibuat sempit untuk "No." di tabel; label info-rows (No. Transaksi
        // dll.) ditulis di sel A:B yang di-merge sehingga cukup lebar.
        if ($isHarian) {
            $sheet->getColumnDimension('A')->setWidth(8);  // No.
            $sheet->getColumnDimension('B')->setWidth(14); // ID TKH (+ bagian merge label)
            $sheet->getColumnDimension('C')->setWidth(22); // Nama TKH
            $sheet->getColumnDimension('D')->setWidth(26); // Periode Kerja
            $sheet->getColumnDimension('E')->setWidth(10); // Jml. Hari
            $sheet->getColumnDimension('F')->setWidth(18); // Upah Harian
            $sheet->getColumnDimension('G')->setWidth(18); // Upah Lembur
            $sheet->getColumnDimension('H')->setWidth(18); // Total
            $sheet->getColumnDimension('I')->setWidth(26); // TTD
        } else {
            $sheet->getColumnDimension('A')->setWidth(8);  // No.
            $sheet->getColumnDimension('B')->setWidth(14); // Plot (+ bagian merge label)
            $sheet->getColumnDimension('C')->setWidth(22); // Aktivitas
            $sheet->getColumnDimension('D')->setWidth(16); // Luas Hasil
            $sheet->getColumnDimension('E')->setWidth(13); // Tanggal
            $sheet->getColumnDimension('F')->setWidth(18); // Total
            $sheet->getColumnDimension('G')->setWidth(26); // TTD
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