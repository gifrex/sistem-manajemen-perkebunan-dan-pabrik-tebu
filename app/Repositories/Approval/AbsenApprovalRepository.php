<?php

namespace App\Repositories\Approval;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * AbsenApprovalRepository
 * 
 * Handles Absen approval queries (absenhdr with embedded approval fields)
 * RULE: All Absen approval-related queries here
 * 
 * Approval Flow:
 * - Header approval (absenhdr) = Approve seluruh absen mandor
 * - Detail foto approval (absenlst) = Reject/approve individual foto masuk
 */
class AbsenApprovalRepository
{
    /**
     * Get pending absen approvals for HRD (idjabatan = 3)
     * 
     * @param string $companycode
     * @param int $idjabatan (should be 3 for HRD)
     * @param array $filters ['date' => 'Y-m-d', 'all_date' => bool]
     * @return Collection
     */
    public function getPendingApprovals(string $companycode, int $idjabatan, array $filters = []): Collection
    {
        // Only HRD (idjabatan = 3) can approve absen
        if ($idjabatan != 3) {
            return collect([]);
        }

        $query = DB::table('absenhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->where('h.companycode', $companycode)
            ->whereNull('h.approvalstatus'); // Only pending approvals

        // Apply date filter (default: today)
        if (empty($filters['all_date'])) {
            $dateToFilter = $filters['date'] ?? date('Y-m-d');
            $query->whereDate('h.uploaddate', $dateToFilter);
        }

        return $query->select([
                'h.*',
                'm.name as mandor_nama'
            ])
            ->orderBy('h.uploaddate', 'desc')
            ->get();
    }

    /**
     * Get absen header by absenno
     * 
     * @param string $companycode
     * @param string $absenno
     * @return object|null
     */
    public function findByAbsenno(string $companycode, string $absenno): ?object
    {
        return DB::table('absenhdr')
            ->where('companycode', $companycode)
            ->where('absenno', $absenno)
            ->first();
    }

    /**
     * Process header approval (approve/reject entire absen)
     * 
     * @param string $companycode
     * @param string $absenno
     * @param string $action 'approve' or 'decline'
     * @param string $userid
     * @return bool
     */
    public function processHeaderApproval(
        string $companycode,
        string $absenno,
        string $action,
        string $userid
    ): bool {
        $approvalValue = $action === 'approve' ? '1' : '0';
        
        $updateData = [
            'approvalstatus' => $approvalValue,
            'approvaluserid' => $userid,
            'approvaldate' => now()
        ];

        $affected = DB::table('absenhdr')
            ->where('companycode', $companycode)
            ->where('absenno', $absenno)
            ->update($updateData);

        return $affected > 0;
    }

    /**
     * Get absen detail (workers list)
     * 
     * @param string $companycode
     * @param string $absenno
     * @return Collection
     */
    public function getAbsenDetails(string $companycode, string $absenno): Collection
    {
        return DB::table('absenlst as l')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode);
            })
            ->where('l.companycode', $companycode)
            ->where('l.absenno', $absenno)
            ->select([
                'l.*',
                'tk.nama as worker_name'
            ])
            ->orderBy('l.id')
            ->get();
    }

    /**
     * Process individual foto approval (approve/reject specific worker foto)
     * 
     * @param string $companycode
     * @param string $absenno
     * @param string $tenagakerjaid
     * @param string $action 'approve' or 'decline'
     * @param string|null $reason Rejection reason (required if decline)
     * @return bool
     */
    public function processFotoApproval(
        string $companycode,
        string $absenno,
        string $tenagakerjaid,
        string $action,
        ?string $reason = null
    ): bool {
        $approvalValue = $action === 'approve' ? '1' : '0';
        
        $updateData = [
            'fotomasukapprovalstatus' => $approvalValue
        ];

        if ($action === 'decline' && $reason) {
            $updateData['fotomasukapprovalreason'] = $reason;
        }

        $affected = DB::table('absenlst')
            ->where('companycode', $companycode)
            ->where('absenno', $absenno)
            ->where('tenagakerjaid', $tenagakerjaid)
            ->update($updateData);

        return $affected > 0;
    }

    /**
     * Check if user has authority to approve (must be idjabatan = 3)
     * 
     * @param int $idjabatan
     * @return array ['success' => bool, 'message' => string]
     */
    public function validateApprovalAuthority(int $idjabatan): array
    {
        if ($idjabatan != 3) {
            return [
                'success' => false,
                'message' => 'Hanya HRD yang dapat melakukan approval absen'
            ];
        }

        return ['success' => true];
    }

    /**
     * Check if absen already approved
     * 
     * @param object $absen
     * @return bool
     */
    public function isAlreadyApproved(object $absen): bool
    {
        return !is_null($absen->approvalstatus);
    }

    /**
     * Get approval history
     * 
     * @param string $companycode
     * @param string $absenno
     * @return object|null
     */
    public function getApprovalHistory(string $companycode, string $absenno): ?object
    {
        return DB::table('absenhdr as h')
            ->leftJoin('user as u', 'h.approvaluserid', '=', 'u.userid')
            ->where('h.companycode', $companycode)
            ->where('h.absenno', $absenno)
            ->select([
                'h.absenno',
                'h.uploaddate',
                'h.approvalstatus',
                'h.approvaldate',
                'h.approvaluserid',
                'u.name as approval_user_name'
            ])
            ->first();
    }

    /**
     * Count workers by foto approval status
     * 
     * @param string $companycode
     * @param string $absenno
     * @return array
     */
    public function countFotoApprovalStatus(string $companycode, string $absenno): array
    {
        $counts = DB::table('absenlst')
            ->where('companycode', $companycode)
            ->where('absenno', $absenno)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN fotomasukapprovalstatus = '1' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN fotomasukapprovalstatus = '0' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN fotomasukapprovalstatus IS NULL THEN 1 ELSE 0 END) as pending
            ")
            ->first();

        return [
            'total' => $counts->total ?? 0,
            'approved' => $counts->approved ?? 0,
            'rejected' => $counts->rejected ?? 0,
            'pending' => $counts->pending ?? 0
        ];
    }

    /**
     * Get workers with rejected foto (need re-upload)
     * 
     * @param string $companycode
     * @param string $absenno
     * @return Collection
     */
    public function getRejectedFotos(string $companycode, string $absenno): Collection
    {
        return DB::table('absenlst as l')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode);
            })
            ->where('l.companycode', $companycode)
            ->where('l.absenno', $absenno)
            ->where('l.fotomasukapprovalstatus', '0')
            ->select([
                'l.tenagakerjaid',
                'l.fotomasukapprovalreason',
                'tk.nama as worker_name'
            ])
            ->get();
    }

    
    /**
     * Batch check: apakah mandor sudah punya minimal 1 LKH bukan EMPTY
     * untuk tanggal yang sama dengan absen.
     *
     * Join by: companycode + mandorid + date(uploaddate) = lkhdate
     *
     * @param string $companycode
     * @param Collection $pendingAbsen — harus punya ->mandorid & ->uploaddate
     * @return array<string, bool>  absenno => true/false
     */
    public function checkLKHUploadedForAbsens(string $companycode, Collection $pendingAbsen): array
    {
        if ($pendingAbsen->isEmpty()) {
            return [];
        }

        // Kumpulkan unique (mandorid, date) pairs
        $pairs = $pendingAbsen->map(function ($absen) {
            return [
                'mandorid' => $absen->mandorid,
                'date'     => \Carbon\Carbon::parse($absen->uploaddate)->toDateString(),
            ];
        })->unique(fn($p) => $p['mandorid'] . '|' . $p['date'])->values();

        // Batch query: cari semua (mandorid, lkhdate) yang status <> EMPTY
        $uploadedLKH = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('status', '<>', 'EMPTY')
            ->where(function ($query) use ($pairs) {
                foreach ($pairs as $pair) {
                    $query->orWhere(function ($q) use ($pair) {
                        $q->where('mandorid', $pair['mandorid'])
                        ->where('lkhdate', $pair['date']);
                    });
                }
            })
            ->select('mandorid', 'lkhdate')
            ->distinct()
            ->get();

        // Build lookup set: "mandorid|date" => true
        $uploadedSet = [];
        foreach ($uploadedLKH as $row) {
            $uploadedSet[$row->mandorid . '|' . $row->lkhdate] = true;
        }

        // Map ke absenno
        $result = [];
        foreach ($pendingAbsen as $absen) {
            $date = \Carbon\Carbon::parse($absen->uploaddate)->toDateString();
            $key  = $absen->mandorid . '|' . $date;
            $result[$absen->absenno] = isset($uploadedSet[$key]);
        }

        return $result;
    }
}