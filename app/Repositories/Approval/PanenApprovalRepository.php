<?php

namespace App\Repositories\Approval;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * PanenApprovalRepository
 *
 * Handles panen-related approvals (Koreksi SJ Panen, etc.)
 */
class PanenApprovalRepository
{
    /**
     * Get pending panen approvals for specific user jabatan
     */
    public function getPendingApprovals(string $companycode, int $idjabatan, array $filters = []): Collection
    {
        $query = DB::table('approvaltransaction as at')
            ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
            ->leftJoin('koreksisuratjalanpanen as k', function ($join) use ($companycode) {
                $join->on('at.transactionnumber', '=', 'k.transactionnumber')
                    ->where('k.companycode', '=', $companycode);
            })
            ->leftJoin('suratjalanpostemp as sjt', function ($join) use ($companycode) {
                $join->on('at.transactionnumber', '=', 'sjt.transactionnumber')
                    ->where('sjt.companycode', '=', $companycode);
            })
            ->leftJoin('user as u', 'at.inputby', '=', 'u.userid')
            ->where('at.companycode', $companycode)
            ->whereIn('am.category', ['Approval Koreksi Surat Jalan Panen', 'Input SJ Non-NFC'])
            ->where(function ($query) use ($idjabatan) {
                $query->where(function ($q) use ($idjabatan) {
                    $q->where('at.approval1idjabatan', $idjabatan)
                        ->whereNull('at.approval1flag');
                })
                ->orWhere(function ($q) use ($idjabatan) {
                    $q->where('at.approval2idjabatan', $idjabatan)
                        ->where('at.approval1flag', '1')
                        ->whereNull('at.approval2flag');
                })
                ->orWhere(function ($q) use ($idjabatan) {
                    $q->where('at.approval3idjabatan', $idjabatan)
                        ->where('at.approval1flag', '1')
                        ->where('at.approval2flag', '1')
                        ->whereNull('at.approval3flag');
                });
            });

        if (empty($filters['all_date'])) {
            $dateToFilter = $filters['date'] ?? date('Y-m-d');
            $query->whereDate('at.createdat', $dateToFilter);
        }

        return $query->select([
            'at.*',
            'am.category',
            'u.name as inputby_name',
            // Koreksi SJ Panen fields
            'k.suratjalanno',
            'k.perubahan',
            'k.alasan',
            // Input SJ Non-NFC fields
            DB::raw('COALESCE(k.suratjalanno, sjt.suratjalanno) as suratjalanno_display'),
            'sjt.id as nonnfc_temp_id',
            'sjt.suratjalanno as nonnfc_suratjalanno',
            'sjt.plot as nonnfc_plot',
            'sjt.varietas as nonnfc_varietas',
            'sjt.nomorpolisi as nonnfc_nomorpolisi',
            'sjt.namasupir as nonnfc_namasupir',
            'sjt.keterangan as nonnfc_keterangan',
            DB::raw("DATE_FORMAT(at.createdat, '%d/%m/%Y') as formatted_date"),
            DB::raw('CASE
                WHEN at.approval1idjabatan = ' . $idjabatan . ' AND at.approval1flag IS NULL THEN 1
                WHEN at.approval2idjabatan = ' . $idjabatan . ' AND at.approval1flag = "1" AND at.approval2flag IS NULL THEN 2
                WHEN at.approval3idjabatan = ' . $idjabatan . ' AND at.approval1flag = "1" AND at.approval2flag = "1" AND at.approval3flag IS NULL THEN 3
                ELSE 0
            END as approval_level'),
        ])
        ->orderBy('at.createdat', 'desc')
        ->get();
    }

    public function findByApprovalno(string $companycode, string $approvalno): ?object
    {
        return DB::table('approvaltransaction as at')
            ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
            ->where('at.companycode', $companycode)
            ->where('at.approvalno', $approvalno)
            ->select(['at.*', 'am.category'])
            ->first();
    }

    public function processApproval(
        string $companycode,
        string $approvalno,
        int $level,
        string $action,
        array $userData
    ): bool {
        $approvalValue     = $action === 'approve' ? '1' : '0';
        $approvalField     = "approval{$level}flag";
        $approvalDateField = "approval{$level}date";
        $approvalUserField = "approval{$level}userid";

        $updateData = [
            $approvalField     => $approvalValue,
            $approvalDateField => now(),
            $approvalUserField => $userData['userid'],
            'updateby'         => $userData['userid'],
            'updatedat'        => now(),
        ];

        if ($action === 'approve') {
            $approval              = $this->findByApprovalno($companycode, $approvalno);
            $tempApproval          = clone $approval;
            $tempApproval->$approvalField = '1';

            $updateData['approvalstatus'] = $this->isFullyApproved($tempApproval) ? '1' : null;
        } else {
            $updateData['approvalstatus'] = '0';
        }

        $affected = DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('approvalno', $approvalno)
            ->update($updateData);

        return $affected > 0;
    }

    public function isFullyApproved(object $approval): bool
    {
        if (!$approval->jumlahapproval || $approval->jumlahapproval == 0) {
            return true;
        }

        return match ((int) $approval->jumlahapproval) {
            1 => $approval->approval1flag === '1',
            2 => $approval->approval1flag === '1' && $approval->approval2flag === '1',
            3 => $approval->approval1flag === '1' && $approval->approval2flag === '1' && $approval->approval3flag === '1',
            default => false,
        };
    }

    public function validateApprovalAuthority(object $approval, int $idjabatan, int $level): array
    {
        $approvalJabatanField = "approval{$level}idjabatan";
        $approvalField        = "approval{$level}flag";

        if (!isset($approval->$approvalJabatanField) || $approval->$approvalJabatanField != $idjabatan) {
            return ['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk approve level ini'];
        }

        if (isset($approval->$approvalField) && $approval->$approvalField !== null) {
            return ['success' => false, 'message' => 'Approval level ini sudah diproses sebelumnya'];
        }

        if ($level > 1) {
            $prevApprovalField = "approval" . ($level - 1) . "flag";
            if (!isset($approval->$prevApprovalField) || $approval->$prevApprovalField !== '1') {
                return ['success' => false, 'message' => 'Approval level sebelumnya belum disetujui'];
            }
        }

        return ['success' => true];
    }

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
                'at.approvalno', 'at.transactionnumber', 'at.approvalstatus', 'at.jumlahapproval', 'am.category',
                'at.approval1flag', 'at.approval1date', 'at.approval1userid',
                'u1.name as approval1_user_name', 'j1.namajabatan as jabatan1_name',
                'at.approval2flag', 'at.approval2date', 'at.approval2userid',
                'u2.name as approval2_user_name', 'j2.namajabatan as jabatan2_name',
                'at.approval3flag', 'at.approval3date', 'at.approval3userid',
                'u3.name as approval3_user_name', 'j3.namajabatan as jabatan3_name',
            ])
            ->first();
    }

    public function getKoreksiSJDetail(string $companycode, string $transactionnumber): ?object
    {
        return DB::table('koreksisuratjalanpanen')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transactionnumber)
            ->first();
    }

    public function getSJNonNfcDetail(string $companycode, string $transactionnumber): ?object
    {
        return DB::table('suratjalanpostemp')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transactionnumber)
            ->first();
    }
}
