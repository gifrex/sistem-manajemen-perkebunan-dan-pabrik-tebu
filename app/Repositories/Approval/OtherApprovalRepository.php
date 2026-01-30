<?php

namespace App\Repositories\Approval;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * OtherApprovalRepository
 *
 * Handles generic approvals (approvaltransaction table)
 * Used for: Split/Merge, Purchase Request, Open Rework, etc.
 * RULE: All generic approval queries here
 */
class OtherApprovalRepository
{
    /**
     * Get pending other approvals for specific user jabatan
     *
     * @param string $companycode
     * @param int $idjabatan
     * @param array $filters ['date' => 'Y-m-d', 'all_date' => bool]
     * @return Collection
     */
    public function getPendingApprovals(string $companycode, int $idjabatan, array $filters = []): Collection
    {
        $query = DB::table('approvaltransaction as at')
            ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
            ->leftJoin('plottransaction as pt', function($join) use ($companycode) {
                $join->on('at.transactionnumber', '=', 'pt.transactionnumber')
                    ->where('pt.companycode', '=', $companycode);
            })
            ->leftJoin('openrework as rw', function($join) use ($companycode) {
                $join->on('at.transactionnumber', '=', 'rw.transactionnumber')
                    ->where('rw.companycode', '=', $companycode);
            })
            ->leftJoin('user as u', 'at.inputby', '=', 'u.userid')
            ->where('at.companycode', $companycode)
            ->where(function($query) use ($idjabatan) {
                // Level 1: Waiting for first approval
                $query->where(function($q) use ($idjabatan) {
                    $q->where('at.approval1idjabatan', $idjabatan)
                      ->whereNull('at.approval1flag');
                })
                // Level 2: Level 1 approved, waiting for level 2
                ->orWhere(function($q) use ($idjabatan) {
                    $q->where('at.approval2idjabatan', $idjabatan)
                      ->where('at.approval1flag', '1')
                      ->whereNull('at.approval2flag');
                })
                // Level 3: Level 1 & 2 approved, waiting for level 3
                ->orWhere(function($q) use ($idjabatan) {
                    $q->where('at.approval3idjabatan', $idjabatan)
                      ->where('at.approval1flag', '1')
                      ->where('at.approval2flag', '1')
                      ->whereNull('at.approval3flag');
                });
            });

        // Apply date filter (default: today)
        if (empty($filters['all_date'])) {
            $dateToFilter = $filters['date'] ?? date('Y-m-d');
            $query->whereDate('at.createdat', $dateToFilter);
        }

        return $query->select([
                'at.*',
                'am.category',
                'u.name as inputby_name',
                // Split/Merge fields
                'pt.transactiontype',
                'pt.sourceplots',
                'pt.resultplots',
                'pt.sourcebatches',
                'pt.resultbatches',
                'pt.areamap',
                'pt.dominantplot',
                'pt.splitmergedreason',
                // Open Rework fields
                'rw.plots as rework_plots',
                'rw.activities as rework_activities',
                'rw.reason as rework_reason',
                DB::raw("DATE_FORMAT(COALESCE(pt.transactiondate, rw.requestdate), '%d/%m/%Y') as formatted_date"),
                DB::raw('CASE
                    WHEN at.approval1idjabatan = '.$idjabatan.' AND at.approval1flag IS NULL THEN 1
                    WHEN at.approval2idjabatan = '.$idjabatan.' AND at.approval1flag = "1" AND at.approval2flag IS NULL THEN 2
                    WHEN at.approval3idjabatan = '.$idjabatan.' AND at.approval1flag = "1" AND at.approval2flag = "1" AND at.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('at.createdat', 'desc')
            ->get();
    }

    /**
     * Get approval detail by approvalno
     *
     * @param string $companycode
     * @param string $approvalno
     * @return object|null
     */
    public function findByApprovalno(string $companycode, string $approvalno): ?object
    {
        return DB::table('approvaltransaction as at')
            ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
            ->where('at.companycode', $companycode)
            ->where('at.approvalno', $approvalno)
            ->select(['at.*', 'am.category'])
            ->first();
    }

    /**
     * Process approval update (approve or decline)
     *
     * @param string $companycode
     * @param string $approvalno
     * @param int $level
     * @param string $action 'approve' or 'decline'
     * @param array $userData ['userid' => '...', 'idjabatan' => ...]
     * @return bool
     */
    public function processApproval(
        string $companycode,
        string $approvalno,
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
            $approval = $this->findByApprovalno($companycode, $approvalno);

            // Simulate the approval to check final status
            $tempApproval = clone $approval;
            $tempApproval->$approvalField = '1';

            if ($this->isFullyApproved($tempApproval)) {
                $updateData['approvalstatus'] = '1';
            } else {
                $updateData['approvalstatus'] = null;
            }
        } else {
            $updateData['approvalstatus'] = '0';
        }

        $affected = DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('approvalno', $approvalno)
            ->update($updateData);

        return $affected > 0;
    }

    /**
     * Check if approval is fully approved
     *
     * @param object $approval
     * @return bool
     */
    public function isFullyApproved(object $approval): bool
    {
        // No approval required
        if (!$approval->jumlahapproval || $approval->jumlahapproval == 0) {
            return true;
        }

        switch ($approval->jumlahapproval) {
            case 1:
                return $approval->approval1flag === '1';
            case 2:
                return $approval->approval1flag === '1' && $approval->approval2flag === '1';
            case 3:
                return $approval->approval1flag === '1' &&
                       $approval->approval2flag === '1' &&
                       $approval->approval3flag === '1';
            default:
                return false;
        }
    }

    /**
     * Validate if user has authority to approve at specific level
     *
     * @param object $approval
     * @param int $idjabatan
     * @param int $level
     * @return array ['success' => bool, 'message' => string]
     */
    public function validateApprovalAuthority(object $approval, int $idjabatan, int $level): array
    {
        $approvalJabatanField = "approval{$level}idjabatan";
        $approvalField = "approval{$level}flag";

        // Check if user has authority for this level
        if (!isset($approval->$approvalJabatanField) || $approval->$approvalJabatanField != $idjabatan) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk approve level ini'
            ];
        }

        // Check if already processed
        if (isset($approval->$approvalField) && $approval->$approvalField !== null) {
            return [
                'success' => false,
                'message' => 'Approval level ini sudah diproses sebelumnya'
            ];
        }

        // Check if previous level is approved (for level 2 and 3)
        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($approval->$prevApprovalField) || $approval->$prevApprovalField !== '1') {
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
     * @param string $approvalno
     * @return object|null
     */
    public function getApprovalHistory(string $companycode, string $approvalno): ?object
    {
        return DB::table('approvaltransaction as at')
            ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
            ->leftJoin('user as u1', 'at.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'at.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'at.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'at.approval1idjabatan', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'at.approval2idjabatan', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'at.approval3idjabatan', '=', 'j3.idjabatan')
            ->where('at.companycode', $companycode)
            ->where('at.approvalno', $approvalno)
            ->select([
                'at.approvalno',
                'at.transactionnumber',
                'at.approvalstatus',
                'at.jumlahapproval',
                'am.category',
                // Level 1
                'at.approval1flag',
                'at.approval1date',
                'at.approval1userid',
                'u1.name as approval1_user_name',
                'j1.namajabatan as jabatan1_name',
                // Level 2
                'at.approval2flag',
                'at.approval2date',
                'at.approval2userid',
                'u2.name as approval2_user_name',
                'j2.namajabatan as jabatan2_name',
                // Level 3
                'at.approval3flag',
                'at.approval3date',
                'at.approval3userid',
                'u3.name as approval3_user_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Get Split/Merge transaction details
     *
     * @param string $companycode
     * @param string $transactionnumber
     * @return object|null
     */
    public function getSplitMergeTransaction(string $companycode, string $transactionnumber): ?object
    {
        return DB::table('plottransaction')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transactionnumber)
            ->first();
    }

    /**
     * Get Open Rework request details
     *
     * @param string $companycode
     * @param string $transactionnumber
     * @return object|null
     */
    public function getOpenReworkRequest(string $companycode, string $transactionnumber): ?object
    {
        return DB::table('openrework')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transactionnumber)
            ->first();
    }

    public function getApprovalUseMaterialDetail( $companycode, $approvalno )
    {
      $joinmaterial = DB::select('SELECT a.companycode,a.rkhno,a.itemseq,a.lkhno,a.plot,
               a.itemcode AS old_itemcode,b.itemcode AS new_itemcode,
               c.itemname AS old_itemname, b.itemname AS new_itemname,
               a.qty AS old_qty,b.qty AS new_qty,
               a.dosageperha AS old_dosage,b.dosageperha AS new_dosage,
               c.measure AS old_measure,b.unit AS new_measure,
               a.unit AS old_unit,b.unit AS new_unit,
               a.nouse,a.tgluse,
               b.approvalno,b.flagstatus,b.approved,b.createdat
        FROM usemateriallst a
        INNER JOIN usematerialapproval b ON b.companycode=a.companycode AND b.rkhno=a.rkhno AND b.itemseq=a.itemseq
        LEFT JOIN herbisida c ON a.companycode = c.companycode AND a.itemcode = c.itemcode
        WHERE a.companycode= ?  AND b.approvalno= ?
        ORDER BY a.itemseq',[ $companycode, $approvalno ]);
      return $joinmaterial;
    }
}
