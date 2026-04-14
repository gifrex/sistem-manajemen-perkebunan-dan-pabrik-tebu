<?php

namespace App\Repositories\Approval;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * LkhApprovalRepository
 * 
 * Handles LKH approval queries (lkhhdr with embedded approval fields)
 * RULE: All LKH approval-related queries here
 */
class LkhApprovalRepository
{
    /**
     * Get pending LKH approvals for specific user jabatan
     * 
     * @param string $companycode
     * @param int $idjabatan
     * @param array $filters ['date' => 'Y-m-d', 'all_date' => bool]
     * @return Collection
     */
    public function getPendingApprovals(string $companycode, int $idjabatan, string $userid, array $filters = []): Collection
    {
        $query = DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            // Join ke rkhhdr untuk dapat activitygroup
            ->join('rkhhdr as r', function($join) {
                $join->on('h.rkhno', '=', 'r.rkhno')
                     ->on('h.companycode', '=', 'r.companycode');
            })
            // Filter by useractivity permission
            ->join('useractivity as ua', function($join) use ($companycode, $userid) {
                $join->on('r.activitygroup', '=', 'ua.activitygroup')
                     ->where('ua.companycode', '=', $companycode)
                     ->where('ua.userid', '=', $userid)
                     ->where('ua.isactive', '=', 1);
            })
            ->where('h.companycode', $companycode)
            ->where('h.issubmit', 1)
            ->where(function($query) use ($idjabatan) {
                $query->where(function($q) use ($idjabatan) {
                    $q->where('h.approval1idjabatan', $idjabatan)
                      ->whereNull('h.approval1flag');
                })
                ->orWhere(function($q) use ($idjabatan) {
                    $q->where('h.approval2idjabatan', $idjabatan)
                      ->where('h.approval1flag', '1')
                      ->whereNull('h.approval2flag');
                })
                ->orWhere(function($q) use ($idjabatan) {
                    $q->where('h.approval3idjabatan', $idjabatan)
                      ->where('h.approval1flag', '1')
                      ->where('h.approval2flag', '1')
                      ->whereNull('h.approval3flag');
                });
            });

        if (empty($filters['all_date'])) {
            $dateToFilter = $filters['date'] ?? date('Y-m-d');
            $query->whereDate('h.lkhdate', $dateToFilter);
        }

        return $query->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                DB::raw('CASE 
                    WHEN h.approval1idjabatan = '.$idjabatan.' AND h.approval1flag IS NULL THEN 1
                    WHEN h.approval2idjabatan = '.$idjabatan.' AND h.approval1flag = "1" AND h.approval2flag IS NULL THEN 2
                    WHEN h.approval3idjabatan = '.$idjabatan.' AND h.approval1flag = "1" AND h.approval2flag = "1" AND h.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('h.lkhdate', 'desc')
            ->get();
    }

    /**
     * Get LKH detail by lkhno
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function findByLkhno(string $companycode, string $lkhno): ?object
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->first();
    }

    /**
     * Process approval update (approve or decline)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param int $level
     * @param string $action 'approve' or 'decline'
     * @param array $userData ['userid' => '...', 'idjabatan' => ...]
     * @return bool
     */
    public function processApproval(
        string $companycode,
        string $lkhno,
        int $level,
        string $action,
        array $userData
    ): bool {
        $approvalValue = $action === 'approve' ? '1' : '0';
        $approvalField = "approval{$level}flag";
        $approvalDateField = "approval{$level}date";
        $approvalUserField = "approval{$level}userid";
        
        $updateData = [
            $approvalField => $approvalValue,
            $approvalDateField => now(),
            $approvalUserField => $userData['userid'],
            'updateby' => $userData['userid'],
            'updatedat' => now()
        ];

        // Calculate status and approvalstatus
        if ($action === 'approve') {
            // Need to get fresh data to check if fully approved
            $lkh = $this->findByLkhno($companycode, $lkhno);
            
            // Simulate the approval to check final status
            $tempLkh = clone $lkh;
            $tempLkh->$approvalField = '1';
            
            if ($this->isFullyApproved($tempLkh)) {
                $updateData['status'] = 'APPROVED';
                $updateData['approvalstatus'] = '1';
            } else {
                $updateData['approvalstatus'] = null;
            }
        } else {
            $updateData['status'] = 'DECLINED';
            $updateData['approvalstatus'] = '0';
        }

        $affected = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update($updateData);

        return $affected > 0;
    }

    /**
     * Check if LKH is fully approved
     * 
     * @param object $lkh
     * @return bool
     */
    public function isFullyApproved(object $lkh): bool
    {
        // No approval required
        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return true;
        }

        switch ($lkh->jumlahapproval) {
            case 1:
                return $lkh->approval1flag === '1';
            case 2:
                return $lkh->approval1flag === '1' && $lkh->approval2flag === '1';
            case 3:
                return $lkh->approval1flag === '1' && 
                       $lkh->approval2flag === '1' && 
                       $lkh->approval3flag === '1';
            default:
                return false;
        }
    }

    /**
     * Validate if user has authority to approve at specific level
     * 
     * @param object $lkh
     * @param int $idjabatan
     * @param int $level
     * @return array ['success' => bool, 'message' => string]
     */
    public function validateApprovalAuthority(object $lkh, int $idjabatan, int $level): array
    {
        $approvalJabatanField = "approval{$level}idjabatan";
        $approvalField = "approval{$level}flag";

        // Check if user has authority for this level
        if (!isset($lkh->$approvalJabatanField) || $lkh->$approvalJabatanField != $idjabatan) {
            return [
                'success' => false, 
                'message' => 'Anda tidak memiliki wewenang untuk approve level ini'
            ];
        }

        // Check if already processed
        if (isset($lkh->$approvalField) && $lkh->$approvalField !== null) {
            return [
                'success' => false, 
                'message' => 'Approval level ini sudah diproses sebelumnya'
            ];
        }

        // Check if previous level is approved (for level 2 and 3)
        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($lkh->$prevApprovalField) || $lkh->$prevApprovalField !== '1') {
                return [
                    'success' => false, 
                    'message' => 'Approval level sebelumnya belum disetujui'
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Get approval history with user details
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getApprovalHistory(string $companycode, string $lkhno): ?object
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as u1', 'h.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'h.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'h.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'h.approval1idjabatan', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'h.approval2idjabatan', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'h.approval3idjabatan', '=', 'j3.idjabatan')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.lkhno',
                'h.lkhdate',
                'h.approvalstatus',
                'h.status',
                'h.jumlahapproval',
                // Level 1
                'h.approval1flag',
                'h.approval1date',
                'h.approval1userid',
                'u1.name as approval1_user_name',
                'j1.namajabatan as jabatan1_name',
                // Level 2
                'h.approval2flag',
                'h.approval2date',
                'h.approval2userid',
                'u2.name as approval2_user_name',
                'j2.namajabatan as jabatan2_name',
                // Level 3
                'h.approval3flag',
                'h.approval3date',
                'h.approval3userid',
                'u3.name as approval3_user_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Check if LKH has material usage
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return bool
     */
    public function hasMaterial(string $companycode, string $lkhno): bool
    {
        return DB::table('lkhdetailmaterial')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->exists();
    }

    /**
     * Check if LKH has kendaraan
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return bool
     */
    public function hasKendaraan(string $companycode, string $lkhno): bool
    {
        return DB::table('lkhdetailkendaraan')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->exists();
    }

    /**
     * Get plot rows with batcharea for luas over-limit validation.
     */
    public function getPlotsWithBatchForValidation(string $companycode, string $lkhno)
    {
        return DB::table('lkhdetailplot as ldp')
            ->leftJoin('batch as b', 'ldp.batchid', '=', 'b.id')
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->whereNotNull('ldp.plot')
            ->select(['ldp.plot', 'ldp.batchno', 'ldp.luashasil', 'b.batcharea'])
            ->get();
    }
}