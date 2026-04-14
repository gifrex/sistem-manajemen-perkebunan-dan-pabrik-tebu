<?php

namespace App\Services\Approval;

use App\Repositories\Approval\RkhApprovalRepository;
use App\Services\Transaction\RencanaKerjaHarian\Generator\LkhGeneratorService;
use App\Services\Transaction\RencanaKerjaHarian\Generator\MaterialUsageGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * RkhApprovalService
 * 
 * Business logic untuk RKH approval workflow
 * Handles: validation, approval processing, post-approval actions
 */
class RkhApprovalService
{
    protected $repository;

    public function __construct(RkhApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Process RKH approval (approve/decline)
     * Main entry point dengan complete workflow
     * 
     * @param string $rkhno
     * @param string $companycode
     * @param int $level
     * @param string $action 'approve' or 'decline'
     * @param array $userData ['userid' => '...', 'idjabatan' => ...]
     * @return array ['success' => bool, 'message' => string]
     */
    public function processApproval(
        string $rkhno,
        string $companycode,
        int $level,
        string $action,
        array $userData
    ): array {
        DB::beginTransaction();
        
        try {
            // Step 1: Get RKH data
            $rkh = $this->repository->findByRkhno($companycode, $rkhno);
            
            if (!$rkh) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ];
            }

            // Step 2: Validate approval authority
            $validation = $this->repository->validateApprovalAuthority($rkh, $userData['idjabatan'], $level);
            
            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            // Step 3: Process approval in database
            $processed = $this->repository->processApproval(
                $companycode,
                $rkhno,
                $level,
                $action,
                $userData
            );

            if (!$processed) {
                throw new \Exception('Gagal update approval di database');
            }

            $message = 'RKH ' . $rkhno . ' berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Step 4: Execute post-approval actions if fully approved
            if ($action === 'approve') {
                // Refresh data to check if fully approved
                $updatedRkh = $this->repository->findByRkhno($companycode, $rkhno);
                
                if ($this->repository->isFullyApproved($updatedRkh)) {
                    Log::info("RKH fully approved, executing post-approval actions", [
                        'rkhno' => $rkhno,
                        'companycode' => $companycode
                    ]);
                    
                    $postActionResult = $this->executePostApprovalActions($rkhno, $companycode);
                    
                    if ($postActionResult['success']) {
                        $message .= $postActionResult['message'];
                    } else {
                        // Post-actions failed, but approval already saved
                        Log::error("Post-approval actions failed", [
                            'rkhno' => $rkhno,
                            'error' => $postActionResult['message']
                        ]);
                        throw new \Exception($postActionResult['message']);
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
            
            Log::error("RKH approval process failed", [
                'rkhno' => $rkhno,
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
     * Execute post-approval actions after RKH fully approved
     * 
     * COPIED FROM: ApprovalController::handlePostApprovalActionsTransactional()
     * Logic 100% sama, cuma dipindah ke Service
     * 
     * @param string $rkhno
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string]
     */
    public function executePostApprovalActions(string $rkhno, string $companycode): array
    {
        $additionalMessage = '';
        
        try {
            // STEP 1: Generate LKH
            Log::info("STEP 1: Starting LKH generation", ['rkhno' => $rkhno]);
            
            $lkhGenerator = new LkhGeneratorService();
            $lkhResult = $lkhGenerator->generateLkhFromRkh($rkhno, $companycode);
            
            if (!$lkhResult['success']) {
                throw new \Exception("LKH auto-generation gagal: " . ($lkhResult['message'] ?? 'Unknown error'));
            }
            
            Log::info("STEP 1 SUCCESS: LKH generated", [
                'rkhno' => $rkhno,
                'total_lkh' => $lkhResult['total_lkh']
            ]);
            
            $additionalMessage .= '. LKH auto-generated (' . $lkhResult['total_lkh'] . ' LKH)';
            
            // STEP 2: Handle Planting Activities
            Log::info("STEP 2: Checking planting activities", ['rkhno' => $rkhno]);
            
            if ($this->repository->hasPlantingActivities($companycode, $rkhno)) {
                try {
                    Log::info("STEP 2: Starting batch creation for planting", ['rkhno' => $rkhno]);
                    
                    $createdBatches = $this->handlePlantingActivity($rkhno, $companycode);
                    
                    if (!empty($createdBatches)) {
                        $additionalMessage .= '. Batch penanaman dibuat: ' . implode(', ', $createdBatches);
                        
                        Log::info("STEP 2 SUCCESS: Batches created", [
                            'rkhno' => $rkhno,
                            'batches' => $createdBatches
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("STEP 2 FAILED: Batch creation error", [
                        'rkhno' => $rkhno,
                        'error' => $e->getMessage()
                    ]);
                    throw new \Exception('Batch creation gagal: ' . $e->getMessage());
                }
            } else {
                Log::info("STEP 2 SKIPPED: No planting activities", ['rkhno' => $rkhno]);
            }
            
            // STEP 3: Generate Material Usage
            Log::info("STEP 3: Checking material usage requirements", ['rkhno' => $rkhno]);
            
            if ($this->repository->needsMaterialUsage($companycode, $rkhno)) {
                try {
                    Log::info("STEP 3: Starting material usage generation", ['rkhno' => $rkhno]);
                    
                    $materialUsageGenerator = new MaterialUsageGeneratorService();
                    $materialResult = $materialUsageGenerator->generateMaterialUsageFromRkh($rkhno);
                    
                    if (!$materialResult['success']) {
                        throw new \Exception('Material usage auto-generation gagal: ' . $materialResult['message']);
                    }
                    
                    if (($materialResult['total_items'] ?? 0) > 0) {
                        $additionalMessage .= '. Material usage auto-generated (' . $materialResult['total_items'] . ' items)';
                        
                        Log::info("STEP 3 SUCCESS: Material usage generated", [
                            'rkhno' => $rkhno,
                            'total_items' => $materialResult['total_items']
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("STEP 3 FAILED: Material usage generation error", [
                        'rkhno' => $rkhno,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            } else {
                Log::info("STEP 3 SKIPPED: No material usage needed", ['rkhno' => $rkhno]);
            }
            
            Log::info("All post-approval steps completed", ['rkhno' => $rkhno]);
            
            return [
                'success' => true,
                'message' => $additionalMessage
            ];
            
        } catch (\Exception $e) {
            Log::error("Post-approval actions failed", [
                'rkhno' => $rkhno,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle planting activity - create batch
     * 
     * COPIED FROM: ApprovalController::handlePlantingActivity()
     * Logic 100% sama
     * 
     * @param string $rkhno
     * @param string $companycode
     * @return array Created batch numbers
     */
    private function handlePlantingActivity(string $rkhno, string $companycode): array
    {
        $plantingPlots = $this->repository->getPlantingPlots($companycode, $rkhno);
        
        if ($plantingPlots->isEmpty()) {
            return [];
        }
        
        $createdBatches = [];
        
        foreach ($plantingPlots as $plotData) {
            $batchNo = $this->generateBatchNo($companycode, $plotData->plot);
            
            // Insert batch
            DB::table('batch')->insert([
                'batchno' => $batchNo,
                'companycode' => $companycode,
                'plot' => $plotData->plot,
                'batchdate' => $plotData->rkhdate,
                'batcharea' => $plotData->luasarea,
                'plantinglkhno' => $rkhno,
                'inputby' => Auth::user()->userid,
                'createdat' => now()
            ]);
            
            // Update masterlist
            DB::table('masterlist')->updateOrInsert(
                ['companycode' => $companycode, 'plot' => $plotData->plot],
                [
                    'batchno' => $batchNo,
                    'batchdate' => $plotData->rkhdate,
                    'batcharea' => $plotData->luasarea,
                    'kodestatus' => 'PC',
                    'cyclecount' => DB::raw('COALESCE(cyclecount, 0) + 1'),
                    'isactive' => 1
                ]
            );
            
            $createdBatches[] = $batchNo;
        }
        
        return $createdBatches;
    }

    /**
     * Generate batch number
     * 
     * COPIED FROM: ApprovalController::generateBatchNo()
     * Logic 100% sama
     * 
     * @param string $companycode
     * @param string $plot
     * @return string
     */
    private function generateBatchNo(string $companycode, string $plot): string
    {
        $date = date('dm');
        $year = date('y');
        
        $sequence = DB::table('batch')
            ->where('companycode', $companycode)
            ->whereDate('batchdate', date('Y-m-d'))
            ->count() + 1;
        
        return "BATCH{$date}{$plot}{$year}" . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get approval detail with history
     * 
     * @param string $rkhno
     * @param string $companycode
     * @return array
     */
    public function getApprovalDetail(string $rkhno, string $companycode): array
    {
        $rkh = $this->repository->findByRkhno($companycode, $rkhno);
        
        if (!$rkh) {
            return [
                'success' => false,
                'message' => 'RKH tidak ditemukan'
            ];
        }

        $history = $this->repository->getApprovalHistory($companycode, $rkhno);
        
        // Build approval status
        $approvalStatus = $this->buildApprovalStatus($rkh);
        
        return [
            'success' => true,
            'data' => [
                'rkh' => $rkh,
                'history' => $history,
                'status' => $approvalStatus,
                'activities' => $this->repository->getActivitiesSummary($companycode, $rkhno),
                'has_material' => $this->repository->hasMaterial($companycode, $rkhno),
                'has_kendaraan' => $this->repository->hasKendaraan($companycode, $rkhno)
            ]
        ];
    }

    /**
     * Build approval status display
     * 
     * @param object $rkh
     * @return array
     */
    private function buildApprovalStatus(object $rkh): array
    {
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return [
                'status' => 'no_approval',
                'message' => 'No Approval Required',
                'color' => 'gray'
            ];
        }

        // Check declined
        if ($rkh->approval1flag === '0' || $rkh->approval2flag === '0' || $rkh->approval3flag === '0') {
            return [
                'status' => 'declined',
                'message' => 'Declined',
                'color' => 'red'
            ];
        }

        // Check fully approved
        if ($this->repository->isFullyApproved($rkh)) {
            return [
                'status' => 'approved',
                'message' => 'Approved',
                'color' => 'green'
            ];
        }

        // Waiting
        $waitingLevel = 1;
        if ($rkh->approval1flag === '1' && $rkh->jumlahapproval >= 2) {
            $waitingLevel = 2;
        }
        if ($rkh->approval2flag === '1' && $rkh->jumlahapproval >= 3) {
            $waitingLevel = 3;
        }

        return [
            'status' => 'waiting',
            'message' => "Waiting Level {$waitingLevel}",
            'color' => 'yellow'
        ];
    }
}