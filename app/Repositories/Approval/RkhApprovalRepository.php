<?php

namespace App\Repositories\Approval;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * RkhApprovalRepository
 * 
 * Handles RKH approval queries (rkhhdr with embedded approval fields)
 * RULE: All RKH approval-related queries here
 */
class RkhApprovalRepository
{
    /**
     * Get pending RKH approvals for specific user jabatan
     * 
     * @param string $companycode
     * @param int $idjabatan
     * @param array $filters ['date' => 'Y-m-d', 'all_date' => bool]
     * @return Collection
     */
    public function getPendingApprovals(string $companycode, int $idjabatan, string $userid, array $filters = []): Collection
    {
        $query = DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            // Filter by useractivity permission
            ->join('useractivity as ua', function($join) use ($companycode, $userid) {
                $join->on('r.activitygroup', '=', 'ua.activitygroup')
                     ->where('ua.companycode', '=', $companycode)
                     ->where('ua.userid', '=', $userid)
                     ->where('ua.isactive', '=', 1);
            })
            ->where('r.companycode', $companycode)
            ->where(function($query) use ($idjabatan) {
                $query->where(function($q) use ($idjabatan) {
                    $q->where('app.idjabatanapproval1', $idjabatan)
                      ->whereNull('r.approval1flag');
                })
                ->orWhere(function($q) use ($idjabatan) {
                    $q->where('app.idjabatanapproval2', $idjabatan)
                      ->where('r.approval1flag', '1')
                      ->whereNull('r.approval2flag');
                })
                ->orWhere(function($q) use ($idjabatan) {
                    $q->where('app.idjabatanapproval3', $idjabatan)
                      ->where('r.approval1flag', '1')
                      ->where('r.approval2flag', '1')
                      ->whereNull('r.approval3flag');
                });
            });

        if (empty($filters['all_date'])) {
            $dateToFilter = $filters['date'] ?? date('Y-m-d');
            $query->whereDate('r.rkhdate', $dateToFilter);
        }

        return $query->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                DB::raw('CASE 
                    WHEN app.idjabatanapproval1 = '.$idjabatan.' AND r.approval1flag IS NULL THEN 1
                    WHEN app.idjabatanapproval2 = '.$idjabatan.' AND r.approval1flag = "1" AND r.approval2flag IS NULL THEN 2
                    WHEN app.idjabatanapproval3 = '.$idjabatan.' AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('r.rkhdate', 'desc')
            ->get();
    }

    /**
     * Get RKH detail by rkhno with approval metadata
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function findByRkhno(string $companycode, string $rkhno): ?object
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * Process approval update (approve or decline)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param int $level
     * @param string $action 'approve' or 'decline'
     * @param array $userData ['userid' => '...', 'idjabatan' => ...]
     * @return bool
     */
    public function processApproval(
        string $companycode,
        string $rkhno,
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

        // Calculate approvalstatus
        if ($action === 'approve') {
            // Need to get fresh data to check if fully approved
            $rkh = $this->findByRkhno($companycode, $rkhno);
            
            // Simulate the approval to check final status
            $tempRkh = clone $rkh;
            $tempRkh->$approvalField = '1';
            
            if ($this->isFullyApproved($tempRkh)) {
                $updateData['approvalstatus'] = '1';
            } else {
                $updateData['approvalstatus'] = null;
            }
        } else {
            $updateData['approvalstatus'] = '0';
        }

        $affected = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update($updateData);

        return $affected > 0;
    }

    /**
     * Check if RKH is fully approved
     * 
     * @param object $rkh
     * @return bool
     */
    public function isFullyApproved(object $rkh): bool
    {
        // No approval required
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

        switch ($rkh->jumlahapproval) {
            case 1:
                return $rkh->approval1flag === '1';
            case 2:
                return $rkh->approval1flag === '1' && $rkh->approval2flag === '1';
            case 3:
                return $rkh->approval1flag === '1' && 
                       $rkh->approval2flag === '1' && 
                       $rkh->approval3flag === '1';
            default:
                return false;
        }
    }

    /**
     * Validate if user has authority to approve at specific level
     * 
     * @param object $rkh
     * @param int $idjabatan
     * @param int $level
     * @return array ['success' => bool, 'message' => string]
     */
    public function validateApprovalAuthority(object $rkh, int $idjabatan, int $level): array
    {
        $approvalJabatanField = "idjabatanapproval{$level}";
        $approvalField = "approval{$level}flag";

        // Check if user has authority for this level
        if (!isset($rkh->$approvalJabatanField) || $rkh->$approvalJabatanField != $idjabatan) {
            return [
                'success' => false, 
                'message' => 'Anda tidak memiliki wewenang untuk approve level ini'
            ];
        }

        // Check if already processed
        if (isset($rkh->$approvalField) && $rkh->$approvalField !== null) {
            return [
                'success' => false, 
                'message' => 'Approval level ini sudah diproses sebelumnya'
            ];
        }

        // Check if previous level is approved (for level 2 and 3)
        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($rkh->$prevApprovalField) || $rkh->$prevApprovalField !== '1') {
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
     * @param string $rkhno
     * @return object|null
     */
    public function getApprovalHistory(string $companycode, string $rkhno): ?object
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('user as u1', 'r.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'r.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'r.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'app.idjabatanapproval1', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'app.idjabatanapproval2', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'app.idjabatanapproval3', '=', 'j3.idjabatan')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.rkhno',
                'r.rkhdate',
                'r.approvalstatus',
                'app.jumlahapproval',
                // Level 1
                'r.approval1flag',
                'r.approval1date',
                'r.approval1userid',
                'u1.name as approval1_user_name',
                'j1.namajabatan as jabatan1_name',
                // Level 2
                'r.approval2flag',
                'r.approval2date',
                'r.approval2userid',
                'u2.name as approval2_user_name',
                'j2.namajabatan as jabatan2_name',
                // Level 3
                'r.approval3flag',
                'r.approval3date',
                'r.approval3userid',
                'u3.name as approval3_user_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Check if RKH has planting activities (activitycode = 2.2.7)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function hasPlantingActivities(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->exists();
    }

    /**
     * Check if RKH needs material usage generation
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function needsMaterialUsage(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->whereNotNull('herbisidagroupid')
            ->exists();
    }

    /**
     * Get planting plots for batch creation
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return Collection
     */
    public function getPlantingPlots(string $companycode, string $rkhno): Collection
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->get();
    }

    /**
     * Get RKH activities summary for display
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return string
     */
    public function getActivitiesSummary(string $companycode, string $rkhno): string
    {
        $activities = DB::table('rkhlst as l')
            ->join('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('l.companycode', $companycode)
            ->where('l.rkhno', $rkhno)
            ->select('a.activityname')
            ->distinct()
            ->pluck('activityname')
            ->join(', ');

        return $activities ?: '-';
    }

    /**
     * Check if RKH has material usage
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function hasMaterial(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->exists();
    }

    /**
     * Check if RKH has kendaraan
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function hasKendaraan(string $companycode, string $rkhno): bool
    {
        return DB::table('rkhlstkendaraan')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->exists();
    }
}