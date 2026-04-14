<?php

namespace App\Services\Approval;

use App\Repositories\Approval\OtherApprovalRepository;
use App\Services\SplitMergePlotService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OtherApprovalService
 * 
 * Business logic untuk generic approval workflow
 * Handles: Split/Merge, Purchase Request, Open Rework, etc.
 */
class OtherApprovalService
{
    protected $repository;

    public function __construct(OtherApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Process other approval (approve/decline)
     * Main entry point dengan complete workflow
     * 
     * COPIED FROM: ApprovalController::processOtherApproval()
     * Logic 100% sama
     * 
     * @param string $approvalno
     * @param string $companycode
     * @param int $level
     * @param string $action 'approve' or 'decline'
     * @param array $userData ['userid' => '...', 'idjabatan' => ...]
     * @return array ['success' => bool, 'message' => string]
     */
    public function processApproval(
        string $approvalno,
        string $companycode,
        int $level,
        string $action,
        array $userData
    ): array {
        DB::beginTransaction();
        
        try {
            // Step 1: Get approval data
            $approval = $this->repository->findByApprovalno($companycode, $approvalno);
            
            if (!$approval) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Approval tidak ditemukan'
                ];
            }

            // Step 2: Validate approval authority
            $validation = $this->repository->validateApprovalAuthority($approval, $userData['idjabatan'], $level);
            
            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            // Step 3: Process approval in database
            $processed = $this->repository->processApproval(
                $companycode,
                $approvalno,
                $level,
                $action,
                $userData
            );

            if (!$processed) {
                throw new \Exception('Gagal update approval di database');
            }

            $message = "{$approval->category} [{$approval->transactionnumber}] berhasil " . 
                      ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Step 4: Execute post-approval actions if fully approved
            if ($action === 'approve') {
                // Refresh data to check if fully approved
                $updatedApproval = $this->repository->findByApprovalno($companycode, $approvalno);
                
                if ($this->repository->isFullyApproved($updatedApproval)) {
                    Log::info("Other approval fully approved, executing post-approval actions", [
                        'approvalno' => $approvalno,
                        'category' => $approval->category,
                        'transactionnumber' => $approval->transactionnumber
                    ]);
                    
                    $postActionResult = $this->executePostApprovalActions($approval, $companycode);
                    
                    if ($postActionResult['success']) {
                        $message .= $postActionResult['message'];
                    } else {
                        Log::error("Post-approval actions failed", [
                            'approvalno' => $approvalno,
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
            
            Log::error("Other approval process failed", [
                'approvalno' => $approvalno,
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
     * Execute post-approval actions
     * 
     * COPIED FROM: ApprovalController::executeOtherApprovalActions()
     * Logic 100% sama
     * 
     * @param object $approval
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string]
     */
    public function executePostApprovalActions(object $approval, string $companycode): array
    {
        $additionalMessage = '';
        //cio
        $category = strtoupper(trim($approval->category ?? ''));
        //cio

        
        try {
            //cio
            if ($category === 'USE MATERIAL') {
                try {
                    Log::info('USE_MATERIAL_FINALIZE_START', [
                        'approvalno' => $approval->approvalno,
                        'companycode' => $companycode,
                        'transactionnumber' => $approval->transactionnumber,
                        'user' => auth()->user()->userid ?? null,
                    ]);
                    
                    $this->finalizeUseMaterialAndSubmit($approval->approvalno, $companycode, auth()->user());
                    Log::info('USE_MATERIAL_FINALIZE_OK', [
                        'approvalno' => $approval->approvalno,
                        'companycode' => $companycode,
                    ]);
                    
                    return ['success' => true, 'message' => '. Use Material finalized'];
                } catch (\Throwable $e) {
                    Log::error('USE_MATERIAL_FINALIZE_FAIL', [
                        'approvalno' => $approval->approvalno,
                        'companycode' => $companycode,
                        'err' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    return ['success' => false, 'message' => 'Finalize Use Material failed: '.$e->getMessage()];
                }
            }
            //cio
            // Check if it's Split/Merge transaction
            $transaction = $this->repository->getSplitMergeTransaction($companycode, $approval->transactionnumber);

            if ($transaction) {
                // Execute Split/Merge
                $service = new SplitMergePlotService();

                if ($transaction->transactiontype === 'SPLIT') {
                    Log::info("Executing SPLIT transaction", [
                        'transactionnumber' => $approval->transactionnumber
                    ]);
                    
                    $result = $service->executeSplit($approval->transactionnumber, $companycode);
                    
                    if ($result['success']) {
                        $additionalMessage .= '. ' . $result['message'];
                        Log::info("SPLIT executed successfully", [
                            'transactionnumber' => $approval->transactionnumber
                        ]);
                    } else {
                        throw new \Exception($result['message']);
                    }
                    
                } elseif ($transaction->transactiontype === 'MERGE') {
                    Log::info("Executing MERGE transaction", [
                        'transactionnumber' => $approval->transactionnumber
                    ]);
                    
                    $result = $service->executeMerge($approval->transactionnumber, $companycode);
                    
                    if ($result['success']) {
                        $additionalMessage .= '. ' . $result['message'];
                        Log::info("MERGE executed successfully", [
                            'transactionnumber' => $approval->transactionnumber
                        ]);
                    } else {
                        throw new \Exception($result['message']);
                    }
                }
                
            } else {
                // Check if it's Open Rework transaction
                $reworkRequest = $this->repository->getOpenReworkRequest($companycode, $approval->transactionnumber);
                
                if ($reworkRequest) {
                    Log::info("Executing Open Rework", [
                        'transactionnumber' => $approval->transactionnumber
                    ]);
                    
                    // Decode plots and activities
                    $plots = json_decode($reworkRequest->plots, true);
                    $activities = json_decode($reworkRequest->activities, true);
                    
                    // Update rework flag in lkhdetailplot for all LKH matching the criteria
                    $affectedRows = DB::table('lkhdetailplot as ldp')
                        ->join('lkhhdr as lh', function($join) use ($companycode) {
                            $join->on('ldp.lkhno', '=', 'lh.lkhno')
                                ->where('lh.companycode', '=', $companycode);
                        })
                        ->where('ldp.companycode', $companycode)
                        ->whereIn('ldp.plot', $plots)
                        ->whereIn('lh.activitycode', $activities)
                        ->update(['ldp.rework' => 1]);
                    
                    $plotCount = count($plots);
                    $activityCount = count($activities);
                    
                    $additionalMessage .= ". Rework telah dibuka untuk {$plotCount} plot dan {$activityCount} activity. {$affectedRows} LKH detail diupdate.";
                    
                    Log::info("Open Rework executed successfully", [
                        'transactionnumber' => $approval->transactionnumber,
                        'affected_rows' => $affectedRows
                    ]);
                }
            }
            
            return [
                'success' => true,
                'message' => $additionalMessage
            ];
            
        } catch (\Exception $e) {
            Log::error("Post-approval actions failed for other approval", [
                'approvalno' => $approval->approvalno,
                'transactionnumber' => $approval->transactionnumber,
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
     * @param string $approvalno
     * @param string $companycode
     * @return array
     */
    public function getApprovalDetail(string $approvalno, string $companycode): array
    {
        $approval = $this->repository->findByApprovalno($companycode, $approvalno);
        
        if (!$approval) {
            return [
                'success' => false,
                'message' => 'Approval tidak ditemukan'
            ];
        }

        $history = $this->repository->getApprovalHistory($companycode, $approvalno);
        
        // Get related transaction details based on category
        $transactionDetails = null;
        
        $splitMerge = $this->repository->getSplitMergeTransaction($companycode, $approval->transactionnumber);
        if ($splitMerge) {
            $transactionDetails = [
                'type' => 'split_merge',
                'data' => $splitMerge
            ];
        }
        
        $rework = $this->repository->getOpenReworkRequest($companycode, $approval->transactionnumber);
        if ($rework) {
            $transactionDetails = [
                'type' => 'open_rework',
                'data' => $rework
            ];
        }
        
        // Build approval status
        $approvalStatus = $this->buildApprovalStatus($approval);
        
        return [
            'success' => true,
            'data' => [
                'approval' => $approval,
                'history' => $history,
                'status' => $approvalStatus,
                'transaction_details' => $transactionDetails
            ]
        ];
    }

    /**
     * Build approval status display
     * 
     * @param object $approval
     * @return array
     */
    private function buildApprovalStatus(object $approval): array
    {
        if (!$approval->jumlahapproval || $approval->jumlahapproval == 0) {
            return [
                'status' => 'no_approval',
                'message' => 'No Approval Required',
                'color' => 'gray'
            ];
        }

        // Check declined
        if ($approval->approval1flag === '0' || $approval->approval2flag === '0' || $approval->approval3flag === '0') {
            return [
                'status' => 'declined',
                'message' => 'Declined',
                'color' => 'red'
            ];
        }

        // Check fully approved
        if ($this->repository->isFullyApproved($approval)) {
            return [
                'status' => 'approved',
                'message' => 'Approved',
                'color' => 'green'
            ];
        }

        // Waiting
        $waitingLevel = 1;
        if ($approval->approval1flag === '1' && $approval->jumlahapproval >= 2) {
            $waitingLevel = 2;
        }
        if ($approval->approval2flag === '1' && $approval->jumlahapproval >= 3) {
            $waitingLevel = 3;
        }

        return [
            'status' => 'waiting',
            'message' => "Waiting Level {$waitingLevel}",
            'color' => 'yellow'
        ];
    }

//cio
private function finalizeUseMaterialAndSubmit(string $approvalno, string $companycode, $currentUser): void
{

        Log::info('USE_MATERIAL_FINALIZE_HIT', [
            'approvalno' => $approvalno,
            'companycode' => $companycode,
            'user' => $currentUser->userid ?? null,
            'file' => __FILE__,
        ]);

        $approval = $this->repository->findByApprovalno($companycode, $approvalno);
        if (!$approval) {
            throw new \Exception('Approval tidak ditemukan.');
        }
    
    try{    
        $rkhno = trim((string) $approval->transactionnumber);

        $snap = DB::table('usematerialapproval')
            ->where('companycode', $companycode)
            ->where('approvalno', $approvalno)
            ->where('rkhno', $rkhno)
            ->get();

        if ($snap->isEmpty()) {
            throw new \Exception('Snapshot usematerialapproval kosong.');
        }

        // build request format yg dipakai GudangController::submit
        $itemcode = [];
        $dosage   = [];
        $unit     = [];
        $luas     = [];

        foreach ($snap as $row) {
            $lkhno   = trim((string) $row->lkhno);
            $plotKey = trim((string) $row->plot);
            $ic = preg_replace('/\s+/', '', trim((string) $row->itemcode));
        
            $itemcode[$lkhno][$ic][$plotKey] = $ic;
            $dosage[$lkhno][$ic][$plotKey]   = (float) ($row->dosageperha ?? 0);
            $unit[$lkhno][$ic][$plotKey]     = $row->unit ?? null;
        
            // submit: qty = luas * dosage
            // supaya qty hasilnya sesuai snapshot: luas = qty/dosage
            $d = (float) ($row->dosageperha ?? 0);
            $q = (float) ($row->qty ?? 0);
            $luas[$lkhno][$ic][$plotKey] = $d > 0 ? ($q / $d) : 0;
        }

        // ✅ TAMBAH LOG INI (setelah loop):
        Log::info('APPROVAL_KIRIM_REQUEST', [
            'rkhno' => $rkhno,
            'itemcode_sample' => array_slice($itemcode, 0, 1, true),
            'dosage_sample' => array_slice($dosage, 0, 1, true),
        ]);

        // pastikan session companycode ada (GudangController masih pakai session)
        session(['companycode' => $companycode]);

        $req = new \Illuminate\Http\Request([
            'rkhno'      => $rkhno,
            'costcenter' => $snap->first()->costcenter ?? null,
            'approvalno' => $approvalno,
            'itemcode'   => $itemcode,
            'dosage'     => $dosage,
            'unit'       => $unit,
            'luas'       => $luas,
        ]);

        Log::info('USE_MATERIAL_CALL_SUBMIT', [
            'approvalno' => $approvalno,
            'companycode' => $companycode,
            'rkhno' => $rkhno,
            'snap_count' => $snap->count(),
            'costcenter' => $snap->first()->costcenter ?? null,
        ]);

        // ✅ Panggil GudangController submit
        app(\App\Http\Controllers\Transaction\GudangController::class)->submit($req);

        // ✅ Check apakah submit berhasil
        $sessionError = session()->get('error');
        $sessionWarning = session()->get('warning');

        if ($sessionError || $sessionWarning) {
            $errorMessage = $sessionError ?? $sessionWarning;
            
            Log::error('USE_MATERIAL_SUBMIT_FAILED', [
                'approvalno' => $approvalno,
                'rkhno' => $rkhno,
                'error' => $errorMessage,
            ]);
            
            // ✅ Throw exception - approval gagal
            throw new \Exception('Submit gagal: ' . $errorMessage);
        }

        Log::info('USE_MATERIAL_SUBMIT_SUCCESS', [
            'approvalno' => $approvalno,
            'rkhno' => $rkhno,
        ]);



    } catch (\Throwable $e) {

        $lastLevel = (int)($approval->jumlahapproval ?? 1);
        if ($lastLevel < 1) $lastLevel = 1;
        if ($lastLevel > 3) $lastLevel = 3;

        DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('approvalno', $approval->approvalno)
            ->update([
                "approval{$lastLevel}flag" => null,
                "approval{$lastLevel}date" => null,
                "approvalstatus" => null,
            ]);

        Log::warning('USE_MATERIAL_ROLLBACK_LAST_APPROVAL', [
            'approvalno' => $approval->approvalno,
            'companycode' => $companycode,
            'lastLevel' => $lastLevel,
            'error' => $e->getMessage(),
        ]);

        throw $e;
    }



}

//cio



}