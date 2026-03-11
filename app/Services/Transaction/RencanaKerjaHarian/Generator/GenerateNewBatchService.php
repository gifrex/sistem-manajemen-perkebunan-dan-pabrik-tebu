<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Generator;

use App\Models\MasterData\Batch;
use App\Models\Transaction\LkhHdr;
use App\Models\Transaction\LkhDetailPlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * GenerateNewBatchService - FIXED VERSION
 * 
 * Auto-generate new batch based on LKH approval:
 * 1. Panen completed (4.3.3/4.4.3/4.5.2) → PC→RC1, RC1→RC2, RC2→RC3, RC3→PC (ALL with tanggalulangtahun=NULL)
 * 2. Planting completed (2.2.7) → UPDATE PC batch (set tanggalulangtahun & plantinglkhno)
 * 3. Trash Muchler completed (3.2.1) → UPDATE RC1/RC2/RC3 batch (set tanggalulangtahun)
 * 4. Set tanggalpanen on first panen LKH approval
 * 
 * FIXED: batchdate now uses lkhdate instead of approval date
 */
class GenerateNewBatchService
{
    const PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2', '2.2.2a', '2.2.2b'];
    const PLANTING_ACTIVITY = '2.2.7';
    const TRASH_MUCHLER_ACTIVITY = '3.2.1';
    const TOLERANCE = 0.0;
    
    /**
     * Main entry point - check LKH and trigger appropriate batch generation
     * Called after LKH approved
     */
    public function checkAndGenerate($lkhno, $companycode)
    {
        try {
            $lkh = Lkhhdr::where('lkhno', $lkhno)
                ->where('companycode', $companycode)
                ->first();
            
            if (!$lkh) {
                return ['success' => false, 'message' => 'LKH not found'];
            }
            
            // Route 1: Panen activities - transition batch lifecycle
            if (in_array($lkh->activitycode, self::PANEN_ACTIVITIES)) {
                // Set tanggalpanen first (before transition check)
                $this->setTanggalPanen($lkhno, $companycode, $lkh);
                
                // Then check for batch transition
                return $this->generateFromPanen($lkhno, $companycode, $lkh);
            }
            
            // Route 2: Planting activity - UPDATE existing PC batch
            if ($lkh->activitycode === self::PLANTING_ACTIVITY) {
                return $this->updatePCBatchFromPlanting($lkhno, $companycode, $lkh);
            }
            
            // Route 3: Trash Muchler activity - UPDATE existing RC batch
            if ($lkh->activitycode === self::TRASH_MUCHLER_ACTIVITY) {
                return $this->updateRCBatchFromTrashMuchler($lkhno, $companycode, $lkh);
            }
            
            // Not a batch-generating activity
            return ['success' => true, 'message' => 'No batch generation needed'];
            
        } catch (\Exception $e) {
            Log::error("Batch generation check failed for LKH {$lkhno}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Set tanggalpanen for first panen LKH approval
     * 
     * @param string $lkhno
     * @param string $companycode
     * @param Lkhhdr $lkh
     * @return void
     */
    private function setTanggalPanen($lkhno, $companycode, $lkh)
    {
        try {
            // Get all batches from this panen LKH
            $plots = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->whereNotNull('batchno')
                ->select('batchno', 'plot')
                ->distinct()
                ->get();
            
            if ($plots->isEmpty()) {
                Log::warning("No plots found for panen LKH {$lkhno}");
                return;
            }
            
            foreach ($plots as $plot) {
                $batch = Batch::where('batchno', $plot->batchno)
                    ->where('companycode', $companycode)
                    ->first();
                
                if (!$batch) {
                    Log::warning("Batch {$plot->batchno} not found for plot {$plot->plot}");
                    continue;
                }
                
                // ONLY set tanggalpanen if NULL (first panen)
                if ($batch->tanggalpanen === null) {
                    $batch->update([
                        'tanggalpanen' => $lkh->lkhdate,
                        'lastactivity' => $lkh->activitycode,
                        'updatedat' => now()
                    ]);
                    
                    Log::info("Set tanggalpanen for batch {$plot->batchno}", [
                        'plot' => $plot->plot,
                        'tanggalpanen' => $lkh->lkhdate,
                        'lkhno' => $lkhno,
                        'activitycode' => $lkh->activitycode
                    ]);
                } else {
                    // Just update lastactivity for subsequent panen
                    $batch->update([
                        'lastactivity' => $lkh->activitycode,
                        'updatedat' => now()
                    ]);
                    
                    Log::info("Updated lastactivity for batch {$plot->batchno}", [
                        'plot' => $plot->plot,
                        'existing_tanggalpanen' => $batch->tanggalpanen,
                        'lkhno' => $lkhno
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to set tanggalpanen for LKH {$lkhno}: " . $e->getMessage());
            // Don't throw - allow batch generation to continue
        }
    }
    
    /**
     * Generate next batch from panen completion (PC→RC1, RC1→RC2, RC2→RC3, RC3→PC)
     * ALL new batches created with tanggalulangtahun=NULL
     * FIXED: Now accepts $lkh parameter to use lkhdate for batchdate
     * 
     * @param string $lkhno
     * @param string $companycode
     * @param Lkhhdr $lkh
     * @return array
     */
    private function generateFromPanen($lkhno, $companycode, $lkh)
    {
        try {
            // Get all batches from this panen LKH
            $batchNumbers = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->whereNotNull('batchno')
                ->pluck('batchno')
                ->unique();
            
            if ($batchNumbers->isEmpty()) {
                return ['success' => false, 'message' => 'No batch found in panen LKH'];
            }
            
            $results = [];
            
            foreach ($batchNumbers as $batchno) {
                if ($this->isPanenCompleted($batchno, $companycode)) {
                    // Pass $lkh to use lkhdate
                    $result = $this->createNextBatchFromPanen($batchno, $companycode, $lkh);
                    $results[] = $result;
                }
            }
            
            if (empty($results)) {
                return ['success' => true, 'message' => 'Panen not yet completed'];
            }
            
            return [
                'success' => true,
                'message' => count($results) . ' batch(es) transitioned from panen',
                'transitions' => $results
            ];
            
        } catch (\Exception $e) {
            Log::error("Generate from panen failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * UPDATE existing PC batch from planting activity
     * Set tanggalulangtahun & plantinglkhno
     * 
     * @param string $lkhno
     * @param string $companycode
     * @param Lkhhdr $lkh
     * @return array
     */
    private function updatePCBatchFromPlanting($lkhno, $companycode, $lkh)
    {
        DB::beginTransaction();
        
        try {
            // Get plots from planting LKH
            $plantingPlots = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->get();
            
            if ($plantingPlots->isEmpty()) {
                DB::rollBack();
                return ['success' => false, 'message' => 'No plots found in planting LKH'];
            }
            
            $updatedBatches = [];
            
            foreach ($plantingPlots as $plotData) {
                // Get active PC batch for this plot
                $pcBatch = Batch::where('companycode', $companycode)
                    ->where('plot', $plotData->plot)
                    ->where('lifecyclestatus', 'PC')
                    ->where('isactive', 1)
                    ->whereNull('tanggalulangtahun') // PC yang belum di-tanam
                    ->first();
                
                if (!$pcBatch) {
                    Log::warning("No empty PC batch found for plot {$plotData->plot}, skipping planting update");
                    continue;
                }
                
                // UPDATE PC batch with planting info
                $pcBatch->update([
                    'tanggalulangtahun' => $lkh->lkhdate,
                    'plantinglkhno' => $lkhno,
                    'lastactivity' => self::PLANTING_ACTIVITY,
                    'updateby' => Auth::user()->userid ?? 'SYSTEM',
                    'updatedat' => now()
                ]);
                
                $updatedBatches[] = [
                    'batchno' => $pcBatch->batchno,
                    'plot' => $plotData->plot,
                    'tanggalulangtahun' => $lkh->lkhdate
                ];
                
                Log::info("PC batch updated with planting info", [
                    'batchno' => $pcBatch->batchno,
                    'plot' => $plotData->plot,
                    'lkhno' => $lkhno,
                    'tanggalulangtahun' => $lkh->lkhdate
                ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => count($updatedBatches) . ' PC batch(es) updated with planting info',
                'batches' => $updatedBatches
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update PC batch from planting: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * UPDATE existing RC batch from trash muchler activity
     * Set tanggalulangtahun when trash muchler completed
     * 
     * @param string $lkhno
     * @param string $companycode
     * @param Lkhhdr $lkh
     * @return array
     */
    private function updateRCBatchFromTrashMuchler($lkhno, $companycode, $lkh)
    {
        try {
            // Get all batches from this trash muchler LKH
            $batchNumbers = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->whereNotNull('batchno')
                ->pluck('batchno')
                ->unique();
            
            if ($batchNumbers->isEmpty()) {
                return ['success' => false, 'message' => 'No batch found in trash muchler LKH'];
            }
            
            $updatedBatches = [];
            
            foreach ($batchNumbers as $batchno) {
                // Check if trash muchler completed for this batch
                if ($this->isTrashMuchlerCompleted($batchno, $companycode)) {
                    $batch = Batch::where('batchno', $batchno)
                        ->where('companycode', $companycode)
                        ->whereIn('lifecyclestatus', ['RC1', 'RC2', 'RC3'])
                        ->where('isactive', 1)
                        ->whereNull('tanggalulangtahun')
                        ->first();
                    
                    if ($batch) {
                        $batch->update([
                            'tanggalulangtahun' => $lkh->lkhdate,
                            'lastactivity' => self::TRASH_MUCHLER_ACTIVITY,
                            'updateby' => Auth::user()->userid ?? 'SYSTEM',
                            'updatedat' => now()
                        ]);
                        
                        $updatedBatches[] = [
                            'batchno' => $batchno,
                            'plot' => $batch->plot,
                            'lifecycle' => $batch->lifecyclestatus,
                            'tanggalulangtahun' => $lkh->lkhdate
                        ];
                        
                        Log::info("RC batch updated with trash muchler completion", [
                            'batchno' => $batchno,
                            'lifecycle' => $batch->lifecyclestatus,
                            'plot' => $batch->plot,
                            'lkhno' => $lkhno,
                            'tanggalulangtahun' => $lkh->lkhdate
                        ]);
                    }
                }
            }
            
            if (empty($updatedBatches)) {
                return ['success' => true, 'message' => 'Trash muchler not yet completed or no eligible batch'];
            }
            
            return [
                'success' => true,
                'message' => count($updatedBatches) . ' RC batch(es) updated with trash muchler completion',
                'batches' => $updatedBatches
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to update RC batch from trash muchler: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if panen completed (approved LKH only)
     * 
     * @param string $batchno
     * @param string $companycode
     * @return bool
     */
    private function isPanenCompleted($batchno, $companycode)
    {
        $batch = Batch::where('batchno', $batchno)
            ->where('companycode', $companycode)
            ->first();
        
        if (!$batch) return false;
        
        // Query with detail per LKH for debugging
        $panenDetails = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.batchno', $batchno)
            ->where('lh.approvalstatus', '1')
            ->whereIn('lh.activitycode', self::PANEN_ACTIVITIES)
            ->select('ldp.lkhno', 'ldp.luashasil', 'lh.lkhdate', 'lh.activitycode')
            ->get();
        
        $totalPanen = $panenDetails->sum('luashasil') ?? 0;
        $sisaArea = $batch->batcharea - $totalPanen;
        $threshold = $batch->batcharea - self::TOLERANCE;
        $isCompleted = $totalPanen >= $threshold;
        
        Log::info("🔍 Panen Completion Check", [
            'batchno' => $batchno,
            'batcharea' => $batch->batcharea,
            'total_panen' => $totalPanen,
            'sisa_area' => $sisaArea,
            'tolerance' => self::TOLERANCE,
            'threshold' => $threshold,
            'is_completed' => $isCompleted,
            'detail_lkh' => $panenDetails->map(fn($d) => [
                'lkhno' => $d->lkhno,
                'date' => $d->lkhdate,
                'activity' => $d->activitycode,
                'luas' => $d->luashasil
            ])->toArray()
        ]);
        
        return $isCompleted;
    }
    
    /**
     * Check if trash muchler completed (approved LKH only)
     * 
     * @param string $batchno
     * @param string $companycode
     * @return bool
     */
    private function isTrashMuchlerCompleted($batchno, $companycode)
    {
        $batch = Batch::where('batchno', $batchno)
            ->where('companycode', $companycode)
            ->first();
        
        if (!$batch) return false;
        
        $totalTrashMuchler = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.batchno', $batchno)
            ->where('lh.approvalstatus', '1')
            ->where('lh.activitycode', self::TRASH_MUCHLER_ACTIVITY)
            ->sum('ldp.luashasil') ?? 0;
        
        $threshold = $batch->batcharea - self::TOLERANCE;
        $isCompleted = $totalTrashMuchler >= $threshold;
        
        Log::info("Check trash muchler completion", [
            'batchno' => $batchno,
            'companycode' => $companycode,
            'batcharea' => $batch->batcharea,
            'total_trash_muchler' => $totalTrashMuchler,
            'threshold' => $threshold,
            'is_completed' => $isCompleted
        ]);
        
        return $isCompleted;
    }
    
    /**
     * Create next batch from panen
     * ALL lifecycles: tanggalulangtahun = NULL
     * FIXED: Now uses lkhdate for batchdate instead of approval date
     * 
     * @param string $currentBatchNo
     * @param string $companycode
     * @param Lkhhdr $lkh
     * @return array
     */
    private function createNextBatchFromPanen($currentBatchNo, $companycode, $lkh)
    {
        DB::beginTransaction();
        
        try {
            $currentBatch = Batch::where('batchno', $currentBatchNo)
                ->where('companycode', $companycode)
                ->lockForUpdate()
                ->first();
            
            if (!$currentBatch || !$currentBatch->isactive) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Batch already closed or not found'];
            }
            
            // STEP 1: Close old batch
            $currentBatch->update([
                'isactive' => 0,
                'closedat' => now()
            ]);
            
            // STEP 2: Generate new batch - USE LKHDATE!
            $nextLifecycle = $this->getNextLifecycle($currentBatch->lifecyclestatus);
            $batchDate = Carbon::parse($lkh->lkhdate); // ← Use LKH work date
            $newBatchNo = $this->generateBatchNo($companycode, $batchDate);
            
            // STEP 3: Create new batch - ALL with tanggalulangtahun = NULL
            $batchData = [
                'batchno' => $newBatchNo,
                'companycode' => $companycode,
                'plot' => $currentBatch->plot,
                'batcharea' => $currentBatch->batcharea,
                'batchdate' => $batchDate->format('Y-m-d'), // ← Use lkhdate
                'lifecyclestatus' => $nextLifecycle,
                'previousbatchno' => $currentBatchNo,
                'tanggalpanen' => null,
                'tanggalulangtahun' => null, // ALL cycles start with NULL
                'plantinglkhno' => null, // Will be filled by planting LKH (for PC only)
                'kontraktorid' => $currentBatch->kontraktorid,
                'kodevarietas' => $currentBatch->kodevarietas,
                'pkp' => $currentBatch->pkp,
                'plottype' => $currentBatch->plottype,
                'isactive' => 1,
                'inputby' => Auth::user()->userid ?? 'SYSTEM',
                'createdat' => now() // Audit timestamp - can use current time
            ];
            
            $newBatch = Batch::create($batchData);
            
            // STEP 4: Update masterlist
            DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $currentBatch->plot)
                ->update(['activebatchno' => $newBatchNo]);
            
            DB::commit();
            
            Log::info("✅ Batch transitioned from panen", [
                'old_batch' => $currentBatchNo,
                'old_lifecycle' => $currentBatch->lifecyclestatus,
                'new_batch' => $newBatchNo,
                'new_lifecycle' => $nextLifecycle,
                'batchdate' => $batchDate->format('Y-m-d'),
                'lkhno' => $lkh->lkhno,
                'lkhdate' => $lkh->lkhdate,
                'plot' => $currentBatch->plot
            ]);
            
            return [
                'success' => true,
                'old_batchno' => $currentBatchNo,
                'new_batchno' => $newBatchNo,
                'lifecycle' => $nextLifecycle,
                'plot' => $currentBatch->plot,
                'batchdate' => $batchDate->format('Y-m-d')
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create next batch: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get next lifecycle (PC→RC1→RC2→RC3→PC)
     * 
     * @param string $current
     * @return string
     */
    private function getNextLifecycle($current)
    {
        return match($current) {
            'PC' => 'RC1',
            'RC1' => 'RC2',
            'RC2' => 'RC3',
            'RC3' => 'PC',
            default => 'PC'
        };
    }
    
    /**
     * Generate unique batch number
     * Format: BATCHyymmddNNN
     * 
     * @param string $companycode
     * @param Carbon $date
     * @return string
     */
    private function generateBatchNo($companycode, $date)
    {
        $dateStr = $date->format('ymd');
        
        $sequence = DB::table('batch')
            ->where('companycode', $companycode)
            ->whereDate('batchdate', $date)
            ->count() + 1;
        
        return "BATCH{$dateStr}" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}