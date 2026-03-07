<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * OrderBbmApprovalController
 *
 * Handle 2 jenis approval:
 * 1. Approval Permintaan BBM  (Admin Kendaraan submit → approval → approved)
 * 2. Approval Pengeluaran BBM (Gudang BBM finalize → approval → sync citrix)
 */
class OrderBbmApprovalController extends Controller
{
    // ===================================================================
    // APPROVAL PERMINTAAN BBM (dari Admin Kendaraan)
    // ===================================================================

    /**
     * Get pending approval PERMINTAAN BBM
     */
    public static function getPendingPermintaan($companycode, $idjabatan, $filters = [])
    {
        $query = DB::table('orderbbmhdr as oh')
            ->where('oh.companycode', $companycode)
            ->where('oh.status', 'SUBMITTED')
            ->whereNull('oh.approvalstatus')
            ->where(function ($q) use ($idjabatan) {
                $q->where(function ($q1) use ($idjabatan) {
                    $q1->where('oh.approval1idjabatan', $idjabatan)
                        ->whereNull('oh.approval1flag');
                })
                ->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('oh.approval2idjabatan', $idjabatan)
                        ->where('oh.approval1flag', '1')
                        ->whereNull('oh.approval2flag');
                })
                ->orWhere(function ($q3) use ($idjabatan) {
                    $q3->where('oh.approval3idjabatan', $idjabatan)
                        ->where('oh.approval1flag', '1')
                        ->where('oh.approval2flag', '1')
                        ->whereNull('oh.approval3flag');
                });
            })
            ->select([
                'oh.*',
                DB::raw("'PERMINTAAN' as approval_type"),
                DB::raw('(SELECT COUNT(*) FROM orderbbmlst ol WHERE ol.orderno = oh.orderno AND ol.companycode = oh.companycode) as jumlahkendaraan'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT ol2.nokendaraan ORDER BY ol2.nokendaraan SEPARATOR ", ") FROM orderbbmlst ol2 WHERE ol2.orderno = oh.orderno AND ol2.companycode = oh.companycode) as daftarkendaraan'),
                DB::raw('CASE 
                    WHEN oh.approval1flag IS NULL THEN 1
                    WHEN oh.approval1flag = "1" AND oh.approval2flag IS NULL THEN 2
                    WHEN oh.approval1flag = "1" AND oh.approval2flag = "1" AND oh.approval3flag IS NULL THEN 3
                    ELSE 1
                END as approval_level'),
            ]);

        if (!empty($filters['date']) && empty($filters['all_date'])) {
            $query->whereDate('oh.orderdate', $filters['date']);
        }

        return $query->orderBy('oh.orderdate', 'desc')->orderBy('oh.orderno', 'desc')->get();
    }

    // ===================================================================
    // APPROVAL PENGELUARAN BBM (dari Gudang BBM)
    // ===================================================================

    /**
     * Get pending approval PENGELUARAN BBM
     */
    public static function getPendingPengeluaran($companycode, $idjabatan, $filters = [])
    {
        $query = DB::table('orderbbmhdr as oh')
            ->where('oh.companycode', $companycode)
            ->where('oh.approvalstatus', '1')           // permintaan sudah approved
            ->where('oh.gudangconfirm', 1)               // gudang sudah finalize
            ->where('oh.gudangstatus', 'SUBMITTED')      // sudah disubmit ke approval
            ->whereNull('oh.gudangapprovalstatus')        // belum diapprove/reject
            ->where(function ($q) use ($idjabatan) {
                $q->where(function ($q1) use ($idjabatan) {
                    $q1->where('oh.gudangapproval1idjabatan', $idjabatan)
                        ->whereNull('oh.gudangapproval1flag');
                })
                ->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('oh.gudangapproval2idjabatan', $idjabatan)
                        ->where('oh.gudangapproval1flag', '1')
                        ->whereNull('oh.gudangapproval2flag');
                })
                ->orWhere(function ($q3) use ($idjabatan) {
                    $q3->where('oh.gudangapproval3idjabatan', $idjabatan)
                        ->where('oh.gudangapproval1flag', '1')
                        ->where('oh.gudangapproval2flag', '1')
                        ->whereNull('oh.gudangapproval3flag');
                });
            })
            ->select([
                'oh.*',
                DB::raw("'PENGELUARAN' as approval_type"),
                DB::raw('(SELECT COUNT(*) FROM orderbbmlst ol WHERE ol.orderno = oh.orderno AND ol.companycode = oh.companycode) as jumlahkendaraan'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT ol2.nokendaraan ORDER BY ol2.nokendaraan SEPARATOR ", ") FROM orderbbmlst ol2 WHERE ol2.orderno = oh.orderno AND ol2.companycode = oh.companycode) as daftarkendaraan'),
                DB::raw('CASE 
                    WHEN oh.gudangapproval1flag IS NULL THEN 1
                    WHEN oh.gudangapproval1flag = "1" AND oh.gudangapproval2flag IS NULL THEN 2
                    WHEN oh.gudangapproval1flag = "1" AND oh.gudangapproval2flag = "1" AND oh.gudangapproval3flag IS NULL THEN 3
                    ELSE 1
                END as approval_level'),
            ]);

        if (!empty($filters['date']) && empty($filters['all_date'])) {
            $query->whereDate('oh.orderdate', $filters['date']);
        }

        return $query->orderBy('oh.orderdate', 'desc')->orderBy('oh.orderno', 'desc')->get();
    }

    // ===================================================================
    // COMBINED — untuk Approval Dashboard
    // ===================================================================

    /**
     * Get ALL pending BBM approvals (permintaan + pengeluaran) gabungan
     */
    public static function getPendingApprovals($companycode, $idjabatan, $filters = [])
    {
        $permintaan  = self::getPendingPermintaan($companycode, $idjabatan, $filters);
        $pengeluaran = self::getPendingPengeluaran($companycode, $idjabatan, $filters);

        return $permintaan->merge($pengeluaran)->sortByDesc('orderdate')->values();
    }

    // ===================================================================
    // PROCESS — approve/reject
    // ===================================================================

    /**
     * Process approval (approve/reject) untuk kedua jenis
     */
    public function process(Request $request)
    {
        try {
            $request->validate([
                'orderno'       => 'required|string',
                'action'        => 'required|in:approve,decline',
                'approval_type' => 'required|in:PERMINTAAN,PENGELUARAN',
            ]);

            $companycode = Session::get('companycode');
            $user        = Auth::user();
            $type        = $request->approval_type;

            $header = DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $request->orderno)
                ->first();

            if (!$header) {
                return back()->with('error', 'Order tidak ditemukan');
            }

            // Validasi berdasarkan tipe approval
            if ($type === 'PERMINTAAN') {
                if ($header->status !== 'SUBMITTED' || $header->approvalstatus !== null) {
                    return back()->with('error', 'Order tidak dalam status pending approval permintaan');
                }
                $level = $this->getCurrentLevel($header, $user->idjabatan, 'approval');
                $prefix = 'approval';
                $jumlahField = 'jumlahapproval';
                $statusField = 'approvalstatus';
            } else {
                if ($header->gudangconfirm != 1 || $header->gudangstatus !== 'SUBMITTED' || $header->gudangapprovalstatus !== null) {
                    return back()->with('error', 'Order tidak dalam status pending approval pengeluaran');
                }
                $level = $this->getCurrentLevel($header, $user->idjabatan, 'gudangapproval');
                $prefix = 'gudangapproval';
                $jumlahField = 'gudangjumlahapproval';
                $statusField = 'gudangapprovalstatus';
            }

            if (!$level) {
                return back()->with('error', 'Anda tidak memiliki akses untuk approve order ini');
            }

            DB::beginTransaction();

            $flagValue  = $request->action === 'approve' ? '1' : '0';
            $updateData = [
                "{$prefix}{$level}userid" => $user->userid,
                "{$prefix}{$level}flag"   => $flagValue,
                "{$prefix}{$level}date"   => now(),
                'updateby'                => $user->name,
                'updatedat'               => now(),
            ];

            // Reject → langsung set status
            if ($request->action === 'decline') {
                $updateData[$statusField] = '0';
            }

            // Approve → cek apakah level terakhir
            if ($request->action === 'approve') {
                if ($level >= $header->$jumlahField) {
                    $updateData[$statusField] = '1';
                }
            }

            DB::table('orderbbmhdr')
                ->where('companycode', $companycode)
                ->where('orderno', $request->orderno)
                ->update($updateData);

            DB::commit();

            $typeLabel  = $type === 'PERMINTAAN' ? 'Permintaan' : 'Pengeluaran';
            $actionText = $request->action === 'approve' ? 'diapprove' : 'ditolak';

            Log::info("Order BBM {$typeLabel} {$actionText}", [
                'orderno' => $request->orderno,
                'type'    => $type,
                'level'   => $level,
                'action'  => $request->action,
                'user'    => $user->userid,
            ]);

            return back()->with('success', "Order #{$request->orderno} ({$typeLabel}) berhasil {$actionText}");
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('OrderBbmApprovalController@process', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ===================================================================
    // DETAIL (API)
    // ===================================================================

    public function detail($orderno)
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

            // Approval history - permintaan
            $approvalHistory = $this->buildApprovalHistory($header, 'approval', $companycode);

            // Approval history - pengeluaran
            $gudangApprovalHistory = $this->buildApprovalHistory($header, 'gudangapproval', $companycode);

            return response()->json([
                'success'               => true,
                'header'                => $header,
                'detail'                => $detail,
                'approvalhistory'       => $approvalHistory,
                'gudangapprovalhistory' => $gudangApprovalHistory,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ===================================================================
    // HELPERS
    // ===================================================================

    /**
     * Determine current approval level
     * @param string $prefix  'approval' atau 'gudangapproval'
     */
    private function getCurrentLevel($header, $idjabatan, $prefix = 'approval')
    {
        $jumlahField = $prefix === 'approval' ? 'jumlahapproval' : 'gudangjumlahapproval';
        $maxLevel    = $header->$jumlahField ?? 3;

        for ($i = 1; $i <= $maxLevel; $i++) {
            $jabatanField = "{$prefix}{$i}idjabatan";
            $flagField    = "{$prefix}{$i}flag";

            if (!isset($header->$jabatanField)) continue;

            if ($header->$jabatanField == $idjabatan && $header->$flagField === null) {
                if ($i === 1) return 1;
                $prevFlag = "{$prefix}" . ($i - 1) . "flag";
                if ($header->$prevFlag === '1') return $i;
            }
        }
        return null;
    }

    /**
     * Build approval history array
     */
    private function buildApprovalHistory($header, $prefix, $companycode)
    {
        $jumlahField = $prefix === 'approval' ? 'jumlahapproval' : 'gudangjumlahapproval';
        $history     = [];

        for ($i = 1; $i <= ($header->$jumlahField ?? 0); $i++) {
            $jabatanField = "{$prefix}{$i}idjabatan";
            $userField    = "{$prefix}{$i}userid";
            $flagField    = "{$prefix}{$i}flag";
            $dateField    = "{$prefix}{$i}date";

            $jabatanName = null;
            if (isset($header->$jabatanField) && $header->$jabatanField) {
                $jabatanName = DB::table('jabatan')
                    ->where('idjabatan', $header->$jabatanField)
                    ->value('namajabatan');
            }

            $history[] = [
                'level'        => $i,
                'idjabatan'    => $header->$jabatanField ?? null,
                'namajabatan'  => $jabatanName,
                'userid'       => $header->$userField ?? null,
                'flag'         => $header->$flagField ?? null,
                'date'         => $header->$dateField ?? null,
            ];
        }

        return $history;
    }
}