<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

/**
 * GudangBbmController
 *
 * Flow konfirmasi solar:
 *   1. Gudang melihat daftar order yang sudah approved (approvalstatus='1')
 *   2. confirmItem()  — input solar real per kendaraan
 *   3. finalizeAll()  — submit ke approval pengeluaran (gudangstatus='SUBMITTED')
 *   4. Setelah gudangapprovalstatus='1' → ready sync Citrix
 */
class GudangBbmController extends Controller
{
    // -----------------------------------------------------------------------
    // INDEX
    // -----------------------------------------------------------------------
    public function index(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $search      = $request->input('search');
            $filterDate  = $request->input('filter_date', now()->format('Y-m-d'));
            $showAll     = $request->boolean('show_all');

            $query = DB::table('orderbbmhdr as oh')
                ->where('oh.companycode', $companycode)
                ->where('oh.approvalstatus', '1') // hanya yang sudah diapprove permintaan
                ->select([
                    'oh.*',
                    DB::raw('(SELECT COUNT(*) FROM orderbbmlst ol WHERE ol.orderno = oh.orderno AND ol.companycode = oh.companycode) as jumlahkendaraan'),
                    DB::raw('(SELECT GROUP_CONCAT(DISTINCT ol2.nokendaraan ORDER BY ol2.nokendaraan SEPARATOR ", ")
                              FROM orderbbmlst ol2
                              WHERE ol2.orderno = oh.orderno AND ol2.companycode = oh.companycode) as daftarkendaraan'),
                ]);

            if ($search) {
                $query->where(fn($q) => $q
                    ->where('oh.orderno',  'like', "%{$search}%")
                    ->orWhere('oh.sourceno', 'like', "%{$search}%")
                );
            }

            if ($filterDate && !$showAll) {
                $query->whereDate('oh.orderdate', $filterDate);
            }

            $bbmData = $query
                ->orderByRaw("CASE 
                    WHEN oh.gudangconfirm = 0 THEN 0 
                    WHEN oh.gudangconfirm = 1 AND (oh.gudangapprovalstatus IS NULL OR oh.gudangapprovalstatus = '0') THEN 1
                    ELSE 2 
                END")
                ->orderBy('oh.orderdate', 'desc')
                ->orderBy('oh.orderno',  'desc')
                ->paginate(20);

            // Stats
            $base = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('approvalstatus', '1');

            if ($filterDate && !$showAll) {
                $base->whereDate('orderdate', $filterDate);
            }

            $stats = [
                'pending_input'         => (clone $base)->where('gudangconfirm', 0)->count(),
                'pending_approval'      => (clone $base)->where('gudangconfirm', 1)->where('gudangstatus', 'SUBMITTED')->whereNull('gudangapprovalstatus')->count(),
                'approved'              => (clone $base)->where('gudangapprovalstatus', '1')->count(),
                'rejected'              => (clone $base)->where('gudangapprovalstatus', '0')->count(),
                'total_solar_requested' => (clone $base)->sum('totalsolarrequested'),
                'total_solar_real'      => (clone $base)->where('gudangconfirm', 1)->sum('totalsolarreal'),
            ];

            return view('transaction.gudang-bbm.index', compact(
                'bbmData', 'stats', 'search', 'filterDate', 'showAll'
            ))->with([
                'title'  => 'Gudang BBM — Konfirmasi Pengeluaran',
                'navbar' => 'Input Gudang BBM',
                'nav'    => 'BBM',
            ]);
        } catch (\Exception $e) {
            Log::error('GudangBbmController@index', ['msg' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // SHOW
    // -----------------------------------------------------------------------
    public function show($orderno)
    {
        try {
            $companycode = Session::get('companycode');

            $header = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->where('approvalstatus', '1')
                ->first();

            if (!$header) {
                return redirect()->route('transaction.gudang-bbm.index')
                    ->with('error', 'Order tidak ditemukan atau belum diapprove');
            }

            $detail = DB::table('orderbbmlst as ol')
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

            $sourceInfo = $this->getSourceInfo($header, $companycode);
            $printDate = now()->translatedFormat('d F Y H:i');

            return view('transaction.gudang-bbm.show', compact(
                'header', 'detail', 'sourceInfo', 'printDate'
            ))->with([
                'title'  => 'Konfirmasi BBM — Order #' . $orderno,
                'navbar' => 'Gudang BBM',
                'nav'    => 'Detail',
            ]);
        } catch (\Exception $e) {
            Log::error('GudangBbmController@show', ['msg' => $e->getMessage(), 'orderno' => $orderno]);
            return redirect()->route('transaction.gudang-bbm.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // CONFIRM ITEM — input solar real per kendaraan
    // -----------------------------------------------------------------------
    public function confirmItem(Request $request, $orderno)
    {
        try {
            $request->validate([
                'item_id'   => 'required|integer',
                'solarreal' => 'required|numeric|min:0.001',
            ]);

            $companycode = Session::get('companycode');

            $header = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->where('approvalstatus', '1')
                ->where('gudangconfirm', 0)
                ->first();

            if (!$header) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan, belum diapprove, atau sudah difinalisasi'
                ]);
            }

            $item = DB::table('orderbbmlst')
                ->where('id', $request->item_id)
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->first();

            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item tidak ditemukan']);
            }

            if ($request->solarreal > $item->solarrequested) {
                return response()->json([
                    'success' => false,
                    'message' => "Solar real ({$request->solarreal} L) melebihi batas maks ({$item->solarrequested} L)"
                ]);
            }

            DB::table('orderbbmlst')
                ->where('id', $request->item_id)
                ->where('companycode', $companycode)
                ->update([
                    'solarreal' => $request->solarreal,
                    'updatedat' => now(),
                ]);

            return response()->json([
                'success'   => true,
                'message'   => "Solar real {$item->nokendaraan} berhasil disimpan",
                'solarreal' => $request->solarreal,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            Log::error('GudangBbmController@confirmItem', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // FINALIZE ALL — lock order & submit ke approval pengeluaran
    // -----------------------------------------------------------------------
    public function finalizeAll(Request $request, $orderno)
    {
        try {
            $companycode = Session::get('companycode');
            $user        = auth()->user();

            $header = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->where('approvalstatus', '1')
                ->where('gudangconfirm', 0)
                ->first();

            if (!$header) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan atau sudah difinalisasi'
                ]);
            }

            // Pastikan SEMUA kendaraan sudah terisi solar real
            $totalItems     = DB::table('orderbbmlst')
                ->where('companycode', $companycode)->where('orderno', $orderno)->count();
            $confirmedItems = DB::table('orderbbmlst')
                ->where('companycode', $companycode)->where('orderno', $orderno)
                ->whereNotNull('solarreal')->where('solarreal', '>', 0)->count();

            if ($totalItems !== $confirmedItems) {
                $belum = $totalItems - $confirmedItems;
                return response()->json([
                    'success' => false,
                    'message' => "Masih ada {$belum} kendaraan yang belum diisi solar real"
                ]);
            }

            $totalReal = DB::table('orderbbmlst')
                ->where('companycode', $companycode)->where('orderno', $orderno)
                ->sum('solarreal');

            // Ambil config approval pengeluaran
            $approvalConfig = DB::table('approval')
                ->where('companycode', $companycode)
                ->where('category', 'Approval Pengeluaran BBM')
                ->first();

            $gudangJumlahApproval = $approvalConfig->jumlahapproval ?? 0;

            $updateData = [
                'totalsolarreal'            => round($totalReal, 3),
                'gudangconfirm'             => 1,
                'gudangconfirmedby'         => $user->name,
                'gudangconfirmedat'         => now(),
                'gudangstatus'              => 'SUBMITTED',
                'gudangjumlahapproval'      => $gudangJumlahApproval,
                'gudangapproval1idjabatan'  => $approvalConfig->idjabatanapproval1 ?? null,
                'gudangapproval2idjabatan'  => $approvalConfig->idjabatanapproval2 ?? null,
                'gudangapproval3idjabatan'  => $approvalConfig->idjabatanapproval3 ?? null,
                'gudangapprovalstatus'      => null,
                'updateby'                  => $user->name,
                'updatedat'                 => now(),
            ];

            // Jika jumlahapproval = 0, langsung approved
            if ($gudangJumlahApproval == 0) {
                $updateData['gudangapprovalstatus'] = '1';
            }

            DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $orderno)
                ->update($updateData);

            Log::info('BBM order finalized & submitted for gudang approval', [
                'orderno'        => $orderno,
                'totalsolarreal' => $totalReal,
                'confirmedby'    => $user->name,
                'jumlahapproval' => $gudangJumlahApproval,
            ]);

            $msg = $gudangJumlahApproval == 0
                ? "Order #{$orderno} difinalisasi dan langsung approved (Total: {$totalReal} L)"
                : "Order #{$orderno} difinalisasi dan masuk antrian approval pengeluaran (Total: {$totalReal} L)";

            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        } catch (\Exception $e) {
            Log::error('GudangBbmController@finalizeAll', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // HELPER
    // -----------------------------------------------------------------------
    private function getSourceInfo($header, $companycode)
    {
        if ($header->sourcetype === 'LKH') {
            return DB::table('lkhhdr as lh')
                ->leftJoin('activity as act', 'lh.activitycode', '=', 'act.activitycode')
                ->leftJoin('user as u', 'lh.mandorid', '=', 'u.userid')
                ->where('lh.companycode', $companycode)
                ->where('lh.lkhno', $header->sourceno)
                ->select('lh.*', 'act.activityname', 'u.name as mandor_nama')
                ->first();
        }

        if ($header->sourcetype === 'SJS') {
            return DB::table('kendaraansupply')
                ->where('companycode', $companycode)
                ->where('sjsno', $header->sourceno)
                ->first();
        }

        return null;
    }
}