<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * OrderBbmController
 *
 * Status flow:
 *   DRAFT      → bisa diedit kalibrasi, bisa dihapus item
 *   SUBMITTED  → terkunci, masuk approval queue
 *   (approvalstatus ditentukan oleh ApprovalController)
 */
class OrderBbmController extends Controller
{
    // -----------------------------------------------------------------------
    // INDEX — antrian pending + daftar order
    // -----------------------------------------------------------------------
    public function index(Request $request)
    {
        try {
            $companycode  = Session::get('companycode');
            $search       = $request->input('search');
            $filterDate   = $request->input('filter_date', now()->format('Y-m-d'));
            $showAllDate  = $request->boolean('show_all_date');
            $filterStatus = $request->input('filter_status');

            // Antrian LKH approved belum ada order
            $pendingLkh = DB::table('lkhhdr as lh')
                ->join('lkhdetailkendaraan as ldk', fn($j) => $j
                    ->on('lh.lkhno', '=', 'ldk.lkhno')
                    ->on('lh.companycode', '=', 'ldk.companycode')
                )
                ->leftJoin('lkhdetailplot as ldp', fn($j) => $j
                    ->on('lh.lkhno', '=', 'ldp.lkhno')
                    ->on('lh.companycode', '=', 'ldp.companycode')
                )
                ->leftJoin('orderbbmhdr as oh', fn($j) => $j
                    ->on('lh.lkhno', '=', 'oh.sourceno')
                    ->where('oh.companycode', Session::get('companycode'))
                    ->where('oh.sourcetype', 'LKH')
                )
                ->leftJoin('activity as act', 'lh.activitycode', '=', 'act.activitycode')
                ->leftJoin('user as u', 'lh.mandorid', '=', 'u.userid')
                ->where('lh.companycode', $companycode)
                ->where('lh.approvalstatus', '1')
                ->whereNull('oh.id')
                ->select([
                    'lh.lkhno', 'lh.lkhdate', 'lh.activitycode',
                    'act.activityname', 'lh.totalluasactual',
                    'u.name as mandor_nama',
                    DB::raw('COUNT(DISTINCT ldk.id) as jumlahkendaraan'),
                    DB::raw('COALESCE(SUM(ldp.luashasil), lh.totalluasactual, 0) as total_luashasil'),
                ])
                ->groupBy('lh.lkhno', 'lh.lkhdate', 'lh.activitycode', 'act.activityname', 'lh.totalluasactual', 'u.name')
                ->orderBy('lh.lkhdate', 'desc')
                ->get();

            // Antrian SJS submitted + STOCK belum ada order
            $pendingSjs = DB::table('kendaraansupply as ks')
                ->leftJoin('orderbbmhdr as oh', fn($j) => $j
                    ->on('ks.sjsno', '=', 'oh.sourceno')
                    ->where('oh.companycode', $companycode)
                    ->where('oh.sourcetype', 'SJS')
                )
                ->leftJoin('kendaraan as k', fn($j) => $j
                    ->on('ks.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', $companycode)
                )
                ->leftJoin('user as u', 'ks.mandorkendaraanid', '=', 'u.userid')
                ->where('ks.companycode', $companycode)
                ->where('ks.sumberbbm', 'STOCK')
                ->where('ks.status', 'SUBMITTED')
                ->whereNull('oh.id')
                ->select(['ks.*', 'k.jenis', 'u.name as mandor_nama'])
                ->orderBy('ks.sjsdate', 'desc')
                ->get();

            // Daftar order
            $query = DB::table('orderbbmhdr as oh')
                ->where('oh.companycode', $companycode)
                ->select([
                    'oh.*',
                    DB::raw('(SELECT COUNT(*) FROM orderbbmlst ol WHERE ol.orderno = oh.orderno AND ol.companycode = oh.companycode) as jumlahkendaraan'),
                ]);

            if ($search) {
                $query->where(fn($q) => $q
                    ->where('oh.orderno', 'like', "%{$search}%")
                    ->orWhere('oh.sourceno', 'like', "%{$search}%")
                );
            }
            if ($filterDate && !$showAllDate) {
                $query->whereDate('oh.orderdate', $filterDate);
            }
            if ($filterStatus !== null && $filterStatus !== '') {
                if ($filterStatus === 'DRAFT') {
                    $query->where('oh.status', 'DRAFT');
                } elseif ($filterStatus === 'SUBMITTED') {
                    $query->where('oh.status', 'SUBMITTED')->whereNull('oh.approvalstatus');
                } elseif ($filterStatus === '1') {
                    $query->where('oh.approvalstatus', '1');
                } elseif ($filterStatus === '0') {
                    $query->where('oh.approvalstatus', '0');
                }
            }

            $orderData = $query->orderBy('oh.orderdate', 'desc')->orderBy('oh.orderno', 'desc')->paginate(20);

            $base = DB::table('orderbbmhdr')->where('companycode', $companycode);
            if ($filterDate && !$showAllDate) $base->whereDate('orderdate', $filterDate);

            $stats = [
                'total'     => (clone $base)->count(),
                'draft'     => (clone $base)->where('status', 'DRAFT')->count(),
                'pending'   => (clone $base)->where('status', 'SUBMITTED')->whereNull('approvalstatus')->count(),
                'approved'  => (clone $base)->where('approvalstatus', '1')->count(),
                'confirmed' => (clone $base)->where('gudangconfirm', 1)->count(),
            ];

            return view('transaction.order-bbm.index', compact(
                'pendingLkh', 'pendingSjs', 'orderData', 'stats',
                'search', 'filterDate', 'showAllDate', 'filterStatus'
            ))->with(['title' => 'Order Pengeluaran BBM', 'navbar' => 'Order BBM', 'nav' => 'Order BBM']);
        } catch (\Exception $e) {
            Log::error('OrderBbmController@index', ['msg' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // API: kendaraan dari LKH
    // -----------------------------------------------------------------------
    public function getKendaraanFromLkh($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $kendaraan = DB::table('lkhdetailkendaraan as ldk')
                ->join('kendaraan as k', fn($j) => $j
                    ->on('ldk.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', $companycode)->where('k.isactive', 1)
                )
                ->leftJoin('tenagakerja as tk', fn($j) => $j
                    ->on('ldk.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', $companycode)
                )
                ->join('lkhhdr as lh', fn($j) => $j
                    ->on('ldk.lkhno', '=', 'lh.lkhno')->on('ldk.companycode', '=', 'lh.companycode')
                )
                ->leftJoin('lkhdetailplot as ldp', fn($j) => $j
                    ->on('ldk.companycode', '=', 'ldp.companycode')->on('ldk.lkhno', '=', 'ldp.lkhno')
                )
                ->where('ldk.companycode', $companycode)->where('ldk.lkhno', $lkhno)
                ->select([
                    'ldk.id', 'ldk.nokendaraan', 'ldk.operatorid',
                    'k.jenis', 'k.id as kendaraanid', 'tk.nama as operator_nama',
                    'lh.activitycode', 'lh.totalluasactual',
                    DB::raw('SUM(COALESCE(ldp.luashasil, 0)) as total_luashasil'),
                ])
                ->groupBy('ldk.id', 'ldk.nokendaraan', 'ldk.operatorid', 'k.jenis', 'k.id', 'tk.nama', 'lh.activitycode', 'lh.totalluasactual')
                ->get();

            return response()->json(['success' => true, 'lkhno' => $lkhno, 'data' => $kendaraan]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // STORE — buat order baru (status DRAFT)
    // -----------------------------------------------------------------------
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sourcetype'               => 'required|in:LKH,SJS',
                'sourceno'                 => 'required|string',
                'orderdate'                => 'required|date',
                'items'                    => 'required|array|min:1',
                'items.*.nokendaraan'      => 'required|string',
                'items.*.hasilkerja'       => 'required|numeric|min:0',
                'items.*.satuanhasil'      => 'required|in:HA,RIT',
                'items.*.nilaikalibrasi'   => 'required|numeric|min:0',
                'items.*.satuankalibrasi'  => 'required|in:L/HA,L/RIT',
                'items.*.ismanualoverride' => 'nullable|boolean',
                'items.*.solaroverride'    => 'nullable|numeric|min:0',
                'items.*.overridereason'   => 'nullable|string',
                'items.*.catatan'          => 'nullable|string',
            ]);

            $companycode = Session::get('companycode');
            $user        = auth()->user();

            $exists = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('sourcetype', $request->sourcetype)
                ->where('sourceno', $request->sourceno)
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => "Order untuk {$request->sourcetype} {$request->sourceno} sudah ada"]);
            }

            DB::beginTransaction();

            $orderno        = $this->generateOrderNo($companycode);
            $approvalConfig = DB::table('approval')->where('companycode', $companycode)->where('category', 'Approval BBM')->first();
            $totalSolar     = 0;
            $itemsToInsert  = [];

            foreach ($request->items as $item) {
                $solarcalculated = round($item['hasilkerja'] * $item['nilaikalibrasi'], 3);
                $isOverride      = !empty($item['ismanualoverride']);
                $solarrequested  = $isOverride ? ($item['solaroverride'] ?? 0) : $solarcalculated;
                $kendaraanid     = DB::table('kendaraan')->where('companycode', $companycode)->where('nokendaraan', $item['nokendaraan'])->where('isactive', 1)->value('id');
                $totalSolar     += $solarrequested;

                $itemsToInsert[] = [
                    'companycode'      => $companycode,
                    'orderno'          => $orderno,
                    'nokendaraan'      => $item['nokendaraan'],
                    'kendaraanid'      => $kendaraanid,
                    'operatorid'       => $item['operatorid'] ?? null,
                    'hasilkerja'       => $item['hasilkerja'],
                    'satuanhasil'      => $item['satuanhasil'],
                    'nilaikalibrasi'   => $item['nilaikalibrasi'],
                    'satuankalibrasi'  => $item['satuankalibrasi'],
                    'solarcalculated'  => $solarcalculated,
                    'ismanualoverride' => $isOverride ? 1 : 0,
                    'solaroverride'    => $isOverride ? ($item['solaroverride'] ?? null) : null,
                    'overridereason'   => $isOverride ? ($item['overridereason'] ?? null) : null,
                    'solarrequested'   => $solarrequested,
                    'catatan'          => $item['catatan'] ?? null,
                    'createdat'        => now(),
                ];
            }

            $hdrId = DB::table('orderbbmhdr')->insertGetId([
                'companycode'         => $companycode,
                'orderno'             => $orderno,
                'orderdate'           => $request->orderdate,
                'sourcetype'          => $request->sourcetype,
                'sourceno'            => $request->sourceno,
                'status'              => 'DRAFT',
                'totalsolarrequested' => round($totalSolar, 3),
                'jumlahapproval'      => $approvalConfig->jumlahapproval ?? 0,
                'approval1idjabatan'  => $approvalConfig->idjabatanapproval1 ?? null,
                'approval2idjabatan'  => $approvalConfig->idjabatanapproval2 ?? null,
                'approval3idjabatan'  => $approvalConfig->idjabatanapproval3 ?? null,
                'approvalstatus'      => null,
                'inputby'             => $user->name,
                'createdat'           => now(),
            ]);

            foreach ($itemsToInsert as &$i) { $i['orderbbmhdrid'] = $hdrId; }
            DB::table('orderbbmlst')->insert($itemsToInsert);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order BBM #{$orderno} (DRAFT) berhasil dibuat",
                'orderno' => $orderno,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => implode(', ', $e->validator->errors()->all())]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrderBbmController@store', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // UPDATE — edit kalibrasi (hanya saat DRAFT)
    // -----------------------------------------------------------------------
    public function update(Request $request, $orderno)
    {
        try {
            $request->validate([
                'items'                    => 'required|array|min:1',
                'items.*.id'               => 'required|integer',
                'items.*.hasilkerja'       => 'required|numeric|min:0',
                'items.*.nilaikalibrasi'   => 'required|numeric|min:0',
                'items.*.ismanualoverride' => 'nullable|boolean',
                'items.*.solaroverride'    => 'nullable|numeric|min:0',
                'items.*.overridereason'   => 'nullable|string',
                'items.*.catatan'          => 'nullable|string',
            ]);

            $companycode = Session::get('companycode');
            $user        = auth()->user();

            $header = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->where('status', 'DRAFT')
                ->first();

            if (!$header) {
                return response()->json(['success' => false, 'message' => 'Order tidak ditemukan atau sudah disubmit']);
            }

            DB::beginTransaction();

            $totalSolar = 0;

            foreach ($request->items as $item) {
                $solarcalculated = round($item['hasilkerja'] * $item['nilaikalibrasi'], 3);
                $isOverride      = !empty($item['ismanualoverride']);
                $solarrequested  = $isOverride ? ($item['solaroverride'] ?? 0) : $solarcalculated;
                $totalSolar     += $solarrequested;

                DB::table('orderbbmlst')
                    ->where('id', $item['id'])
                    ->where('companycode', $companycode)
                    ->where('orderno', $orderno)
                    ->update([
                        'hasilkerja'       => $item['hasilkerja'],
                        'nilaikalibrasi'   => $item['nilaikalibrasi'],
                        'solarcalculated'  => $solarcalculated,
                        'ismanualoverride' => $isOverride ? 1 : 0,
                        'solaroverride'    => $isOverride ? ($item['solaroverride'] ?? null) : null,
                        'overridereason'   => $isOverride ? ($item['overridereason'] ?? null) : null,
                        'solarrequested'   => $solarrequested,
                        'catatan'          => $item['catatan'] ?? null,
                        'updatedat'        => now(),
                    ]);
            }

            DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->update([
                    'totalsolarrequested' => round($totalSolar, 3),
                    'updateby'            => $user->name,
                    'updatedat'           => now(),
                ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => "Order #{$orderno} berhasil diupdate"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // SUBMIT — DRAFT → SUBMITTED (masuk approval queue)
    // -----------------------------------------------------------------------
    public function submit(Request $request, $orderno)
    {
        try {
            $companycode = Session::get('companycode');
            $user        = auth()->user();

            $header = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->where('status', 'DRAFT')
                ->first();

            if (!$header) {
                return response()->json(['success' => false, 'message' => 'Order tidak ditemukan atau sudah disubmit']);
            }

            $hasDetail = DB::table('orderbbmlst')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->exists();

            if (!$hasDetail) {
                return response()->json(['success' => false, 'message' => 'Order tidak memiliki detail kendaraan']);
            }

            // Validasi semua item solarrequested > 0
            $invalidItem = DB::table('orderbbmlst')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->where('solarrequested', '<=', 0)
                ->value('nokendaraan');

            if ($invalidItem) {
                return response()->json(['success' => false, 'message' => "Solar diminta untuk {$invalidItem} harus lebih dari 0"]);
            }

            // Jika jumlahapproval = 0, langsung approved
            $updateData = [
                'status'      => 'SUBMITTED',
                'submittedby' => $user->name,
                'submittedat' => now(),
                'updateby'    => $user->name,
                'updatedat'   => now(),
            ];

            if ($header->jumlahapproval == 0) {
                $updateData['approvalstatus'] = '1';
            }

            DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->update($updateData);

            $msg = $header->jumlahapproval == 0
                ? "Order #{$orderno} disubmit dan langsung approved (tidak perlu approval)"
                : "Order #{$orderno} berhasil disubmit ke antrian approval";

            Log::info('Order BBM submitted', ['orderno' => $orderno, 'user' => $user->name]);

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            Log::error('OrderBbmController@submit', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // SHOW — print preview
    // -----------------------------------------------------------------------
    public function show($orderno)
    {
        try {
            $companycode = Session::get('companycode');

            $header = DB::table('orderbbmhdr')->where('companycode', $companycode)->where('orderno', $orderno)->first();
            if (!$header) {
                return redirect()->route('transaction.order-bbm.index')->with('error', 'Order tidak ditemukan');
            }

            $detail = DB::table('orderbbmlst as ol')
                ->leftJoin('kendaraan as k', fn($j) => $j->on('ol.nokendaraan', '=', 'k.nokendaraan')->where('k.companycode', $companycode))
                ->leftJoin('tenagakerja as tk', fn($j) => $j->on('ol.operatorid', '=', 'tk.tenagakerjaid')->where('tk.companycode', $companycode))
                ->where('ol.companycode', $companycode)->where('ol.orderno', $orderno)
                ->select('ol.*', 'k.jenis', 'tk.nama as operator_nama')
                ->orderBy('ol.nokendaraan')->get();

            $sourceInfo = null;
            if ($header->sourcetype === 'LKH') {
                $sourceInfo = DB::table('lkhhdr as lh')
                    ->leftJoin('activity as act', 'lh.activitycode', '=', 'act.activitycode')
                    ->leftJoin('user as u', 'lh.mandorid', '=', 'u.userid')
                    ->where('lh.companycode', $companycode)->where('lh.lkhno', $header->sourceno)
                    ->select('lh.*', 'act.activityname', 'u.name as mandor_nama')->first();
            } elseif ($header->sourcetype === 'SJS') {
                $sourceInfo = DB::table('kendaraansupply')->where('companycode', $companycode)->where('sjsno', $header->sourceno)->first();
            }

            $printDate = now()->translatedFormat('d F Y H:i');

            return view('transaction.order-bbm.show', compact('header', 'detail', 'sourceInfo', 'printDate'))
                ->with(['title' => 'Order BBM #' . $orderno, 'navbar' => 'Order BBM', 'nav' => 'Detail']);
        } catch (\Exception $e) {
            return redirect()->route('transaction.order-bbm.index')->with('error', $e->getMessage());
        }
    }

    private function generateOrderNo($companycode): string
    {
        $last = DB::table('orderbbmhdr')->where('companycode', $companycode)->orderByDesc('orderno')->value('orderno');
        $next = $last ? ((int) $last + 1) : 1;
        return str_pad($next, 8, '0', STR_PAD_LEFT);
    }

    // -----------------------------------------------------------------------
    // GET ITEMS — untuk modal edit di index page
    // -----------------------------------------------------------------------
    public function getItems($orderno)
    {
        try {
            $companycode = Session::get('companycode');

            $header = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->first();

            if (!$header) {
                return response()->json(['success' => false, 'message' => 'Order tidak ditemukan']);
            }

            $items = DB::table('orderbbmlst as ol')
                ->leftJoin('kendaraan as k', fn($j) => $j
                    ->on('ol.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', $companycode)
                )
                ->leftJoin('tenagakerja as tk', fn($j) => $j
                    ->on('ol.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', $companycode)
                )
                ->where('ol.companycode', $companycode)
                ->where('ol.orderno', $orderno)
                ->select('ol.*', 'k.jenis', 'tk.nama as operator_nama')
                ->orderBy('ol.nokendaraan')
                ->get();

            return response()->json([
                'success' => true,
                'header'  => $header,
                'items'   => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}