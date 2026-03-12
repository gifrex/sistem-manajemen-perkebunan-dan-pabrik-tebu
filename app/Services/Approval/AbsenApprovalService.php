<?php

namespace App\Services\Approval;

use App\Repositories\Approval\AbsenApprovalRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AbsenApprovalService
 * 
 * Business logic untuk Absen approval workflow
 * Handles: header approval (bulk) and individual foto approval
 */
class AbsenApprovalService
{
    protected $repository;

    public function __construct(AbsenApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Process header approval (approve/decline entire absen)
     * 
     * @param string $absenno
     * @param string $companycode
     * @param string $action 'approve' or 'decline'
     * @param array $userData ['userid' => '...', 'idjabatan' => ...]
     * @return array ['success' => bool, 'message' => string]
     */
    public function processHeaderApproval(
        string $absenno,
        string $companycode,
        string $action,
        array $userData
    ): array {
        DB::beginTransaction();
        
        try {
            // Step 1: Validate authority (must be HRD)
            $validation = $this->repository->validateApprovalAuthority($userData['idjabatan']);
            
            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            // Step 2: Get absen data
            $absen = $this->repository->findByAbsenno($companycode, $absenno);
            
            if (!$absen) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Absen tidak ditemukan'
                ];
            }

            // Step 3: Check if already approved
            if ($this->repository->isAlreadyApproved($absen)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Absen sudah pernah diproses'
                ];
            }

            // Step 3.5: Guard — kalau approve, pastikan LKH sudah diupload
            if ($action === 'approve') {
                $date = \Carbon\Carbon::parse($absen->uploaddate)->toDateString();
                $hasLKH = DB::table('lkhhdr')
                    ->where('companycode', $companycode)
                    ->where('mandorid', $absen->mandorid)
                    ->where('lkhdate', $date)
                    ->where('status', '<>', 'EMPTY')
                    ->exists();

                if (!$hasLKH) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Tidak bisa approve — LKH belum diupload oleh mandor untuk tanggal ini'
                    ];
                }
            }

            // Step 4: Process approval
            $processed = $this->repository->processHeaderApproval(
                $companycode,
                $absenno,
                $action,
                $userData['userid']
            );

            if (!$processed) {
                throw new \Exception('Gagal update approval di database');
            }

            $message = 'Absen ' . $absenno . ' berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            DB::commit();
            
            Log::info("Absen header approval processed", [
                'absenno' => $absenno,
                'action' => $action,
                'userid' => $userData['userid']
            ]);
            
            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Absen header approval failed", [
                'absenno' => $absenno,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process individual foto approval (approve/reject specific worker foto)
     * 
     * @param string $absenno
     * @param string $companycode
     * @param string $tenagakerjaid
     * @param string $action 'approve' or 'decline'
     * @param string|null $reason Rejection reason (required if decline)
     * @param array $userData
     * @return array ['success' => bool, 'message' => string]
     */
    public function processFotoApproval(
        string $absenno,
        string $companycode,
        string $tenagakerjaid,
        string $action,
        ?string $reason,
        array $userData
    ): array {
        DB::beginTransaction();
        
        try {
            // Validate authority (must be HRD)
            $validation = $this->repository->validateApprovalAuthority($userData['idjabatan']);
            
            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            // Validate rejection reason if declining
            if ($action === 'decline' && empty($reason)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Alasan penolakan harus diisi'
                ];
            }

            // Process foto approval
            $processed = $this->repository->processFotoApproval(
                $companycode,
                $absenno,
                $tenagakerjaid,
                $action,
                $reason
            );

            if (!$processed) {
                throw new \Exception('Gagal update foto approval');
            }

            $message = 'Foto absen berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            DB::commit();
            
            Log::info("Foto absen approval processed", [
                'absenno' => $absenno,
                'tenagakerjaid' => $tenagakerjaid,
                'action' => $action
            ]);
            
            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Foto approval failed", [
                'absenno' => $absenno,
                'tenagakerjaid' => $tenagakerjaid,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal memproses approval foto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get absen approval detail with workers list
     * 
     * @param string $absenno
     * @param string $companycode
     * @return array
     */
    public function getApprovalDetail(string $absenno, string $companycode): array
    {
        $absen = $this->repository->findByAbsenno($companycode, $absenno);
        
        if (!$absen) {
            return [
                'success' => false,
                'message' => 'Absen tidak ditemukan'
            ];
        }

        $workers = $this->repository->getAbsenDetails($companycode, $absenno);
        $history = $this->repository->getApprovalHistory($companycode, $absenno);
        $fotoStats = $this->repository->countFotoApprovalStatus($companycode, $absenno);
        
        // Build status
        $status = $this->buildApprovalStatus($absen);
        
        return [
            'success' => true,
            'data' => [
                'absen' => $absen,
                'workers' => $workers,
                'history' => $history,
                'status' => $status,
                'foto_stats' => $fotoStats
            ]
        ];
    }

    /**
     * Build approval status display
     * 
     * @param object $absen
     * @return array
     */
    private function buildApprovalStatus(object $absen): array
    {
        if (is_null($absen->approvalstatus)) {
            return [
                'status' => 'pending',
                'message' => 'Waiting Approval',
                'color' => 'yellow'
            ];
        }

        if ($absen->approvalstatus === '1') {
            return [
                'status' => 'approved',
                'message' => 'Approved',
                'color' => 'green'
            ];
        }

        if ($absen->approvalstatus === '0') {
            return [
                'status' => 'rejected',
                'message' => 'Rejected',
                'color' => 'red'
            ];
        }

        return [
            'status' => 'unknown',
            'message' => 'Unknown',
            'color' => 'gray'
        ];
    }
}