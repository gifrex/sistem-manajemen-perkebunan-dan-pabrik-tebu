<?php

namespace App\Services\Approval;

use App\Repositories\Approval\LkhApprovalRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterlistBatchRepository;
use App\Services\Transaction\RencanaKerjaHarian\Generator\GenerateNewBatchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * LkhApprovalService
 * 
 * Business logic untuk LKH approval workflow
 * Handles: validation, approval processing, post-approval actions
 */
class LkhApprovalService
{
    protected $repository;
    protected $batchRepo;

    public function __construct(LkhApprovalRepository $repository, MasterlistBatchRepository $batchRepo)
    {
        $this->repository = $repository;
        $this->batchRepo  = $batchRepo;
    }

    /**
     * Process LKH approval (approve/decline)
     * Main entry point dengan complete workflow
     * 
     * COPIED FROM: ApprovalController::processLKHApproval()
     * Logic 100% sama
     * 
     * @param string $lkhno
     * @param string $companycode
     * @param int $level
     * @param string $action 'approve' or 'decline'
     * @param array $userData ['userid' => '...', 'idjabatan' => ...]
     * @return array ['success' => bool, 'message' => string]
     */
    public function processApproval(
        string $lkhno,
        string $companycode,
        int $level,
        string $action,
        array $userData
    ): array {
        DB::beginTransaction();
        
        try {
            // Step 1: Get LKH data
            $lkh = $this->repository->findByLkhno($companycode, $lkhno);
            
            if (!$lkh) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'LKH tidak ditemukan'
                ];
            }

            // Step 2: Validate approval authority
            $validation = $this->repository->validateApprovalAuthority($lkh, $userData['idjabatan'], $level);
            
            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            // Step 3: Validate luas on approve (block if over batch saldo)
            if ($action === 'approve') {
                $luasCheck = $this->validateLuasNotExceeded($lkhno, $companycode, $lkh->activitycode);
                if (!$luasCheck['valid']) {
                    DB::rollBack();
                    return ['success' => false, 'message' => $luasCheck['message']];
                }
            }

            // Step 4: Process approval in database
            $processed = $this->repository->processApproval(
                $companycode,
                $lkhno,
                $level,
                $action,
                $userData
            );

            if (!$processed) {
                throw new \Exception('Gagal update approval di database');
            }

            $message = 'LKH ' . $lkhno . ' berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Step 4: Execute post-approval actions if fully approved
            if ($action === 'approve') {
                // Refresh data to check if fully approved
                $updatedLkh = $this->repository->findByLkhno($companycode, $lkhno);
                
                if ($this->repository->isFullyApproved($updatedLkh)) {
                    Log::info("LKH fully approved, executing post-approval actions", [
                        'lkhno' => $lkhno,
                        'companycode' => $companycode
                    ]);
                    
                    $postActionResult = $this->executePostApprovalActions($lkhno, $companycode);
                    
                    if ($postActionResult['success']) {
                        $message .= $postActionResult['message'];
                    } else {
                        // Post-actions failed, but approval already saved
                        Log::warning("Post-approval actions failed for LKH", [
                            'lkhno' => $lkhno,
                            'error' => $postActionResult['message']
                        ]);
                        // Don't throw - batch generation is optional
                    }
                }
            }

            DB::commit();
            
            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("LKH approval process failed", [
                'lkhno' => $lkhno,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal memproses approval LKH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Execute post-approval actions after LKH fully approved
     * 
     * COPIED FROM: ApprovalController::processLKHApproval() - batch generation part
     * Logic 100% sama
     * 
     * @param string $lkhno
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string]
     */
    public function executePostApprovalActions(string $lkhno, string $companycode): array
    {
        $additionalMessage = '';
        
        try {
            // Trigger batch generation setelah LKH fully approved
            Log::info("Triggering batch generation for approved LKH", ['lkhno' => $lkhno]);
            
            $batchService = new GenerateNewBatchService();
            $batchResult = $batchService->checkAndGenerate($lkhno, $companycode);
            
            if ($batchResult['success']) {
                // Handle transitions (lifecycle changes)
                if (!empty($batchResult['transitions'])) {
                    foreach ($batchResult['transitions'] as $transition) {
                        if ($transition['success']) {
                            $additionalMessage .= ". New Batch: {$transition['new_batchno']} ({$transition['lifecycle']}) for Plot {$transition['plot']}";
                            
                            Log::info("Batch transition created", [
                                'lkhno' => $lkhno,
                                'batch' => $transition['new_batchno'],
                                'plot' => $transition['plot']
                            ]);
                        }
                    }
                }
                
                // Handle new PC batches
                if (!empty($batchResult['batches'])) {
                    foreach ($batchResult['batches'] as $batch) {
                        if ($batch['success']) {
                            $additionalMessage .= ". New PC Batch: {$batch['batchno']} for Plot {$batch['plot']}";
                            
                            Log::info("PC batch created", [
                                'lkhno' => $lkhno,
                                'batch' => $batch['batchno'],
                                'plot' => $batch['plot']
                            ]);
                        }
                    }
                }
            } else {
                Log::warning("Batch generation failed for LKH", [
                    'lkhno' => $lkhno,
                    'message' => $batchResult['message'] ?? 'Unknown error'
                ]);
            }
            
            return [
                'success' => true,
                'message' => $additionalMessage
            ];
            
        } catch (\Exception $e) {
            Log::error("Post-approval actions failed for LKH", [
                'lkhno' => $lkhno,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get approval detail with history
     * 
     * @param string $lkhno
     * @param string $companycode
     * @return array
     */
    public function getApprovalDetail(string $lkhno, string $companycode): array
    {
        $lkh = $this->repository->findByLkhno($companycode, $lkhno);
        
        if (!$lkh) {
            return [
                'success' => false,
                'message' => 'LKH tidak ditemukan'
            ];
        }

        $history = $this->repository->getApprovalHistory($companycode, $lkhno);
        
        // Build approval status
        $approvalStatus = $this->buildApprovalStatus($lkh);
        
        return [
            'success' => true,
            'data' => [
                'lkh' => $lkh,
                'history' => $history,
                'status' => $approvalStatus,
                'has_material' => $this->repository->hasMaterial($companycode, $lkhno),
                'has_kendaraan' => $this->repository->hasKendaraan($companycode, $lkhno)
            ]
        ];
    }

    /**
     * Validate that this LKH's luashasil per plot does not exceed remaining batch saldo.
     * Panen activities share a single saldo. PIAS activities are excluded.
     */
    private function validateLuasNotExceeded(string $lkhno, string $companycode, string $activitycode): array
    {
        $panenActivities = ['4.3.3', '4.4.3', '4.5.2', '2.2.2a', '2.2.2b'];
        $piasActivities  = ['5.2.1', '5.2.3a'];

        if (in_array($activitycode, $piasActivities)) {
            return ['valid' => true];
        }

        $activityFilter = in_array($activitycode, $panenActivities)
            ? $panenActivities
            : $activitycode;

        $plots = $this->repository->getPlotsWithBatchForValidation($companycode, $lkhno);

        foreach ($plots as $plot) {
            if (!$plot->batchno || !$plot->batcharea) continue;

            $alreadyApproved = $this->batchRepo->getTotalApprovedWorkByPlotExcludingLkh(
                $companycode,
                $plot->plot,
                $activityFilter,
                $plot->batchno,
                $lkhno
            );

            $total     = $alreadyApproved + (float) $plot->luashasil;
            $batcharea = (float) $plot->batcharea;

            if ($total > $batcharea) {
                $excess = number_format($total - $batcharea, 2);
                return [
                    'valid'   => false,
                    'message' => "Plot {$plot->plot}: total luas melebihi kapasitas batch "
                        . "({$total} Ha > {$batcharea} Ha, kelebihan: {$excess} Ha). "
                        . "Kemungkinan ada LKH lain untuk plot yang sama yang sudah disetujui."
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Build approval status display
     * 
     * @param object $lkh
     * @return array
     */
    private function buildApprovalStatus(object $lkh): array
    {
        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return [
                'status' => 'no_approval',
                'message' => 'No Approval Required',
                'color' => 'gray'
            ];
        }

        // Check declined
        if ($lkh->approval1flag === '0' || $lkh->approval2flag === '0' || $lkh->approval3flag === '0') {
            return [
                'status' => 'declined',
                'message' => 'Declined',
                'color' => 'red'
            ];
        }

        // Check fully approved
        if ($this->repository->isFullyApproved($lkh)) {
            return [
                'status' => 'approved',
                'message' => 'Approved',
                'color' => 'green'
            ];
        }

        // Waiting
        $waitingLevel = 1;
        if ($lkh->approval1flag === '1' && $lkh->jumlahapproval >= 2) {
            $waitingLevel = 2;
        }
        if ($lkh->approval2flag === '1' && $lkh->jumlahapproval >= 3) {
            $waitingLevel = 3;
        }

        return [
            'status' => 'waiting',
            'message' => "Waiting Level {$waitingLevel}",
            'color' => 'yellow'
        ];
    }
}