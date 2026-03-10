<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Generator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Refactored MaterialUsageGeneratorService
 * 
 * CHANGES:
 * - Generate material usage PER PLOT (tidak merge berdasarkan itemcode)
 * - Setiap plot memiliki herbisidagroup sendiri
 * - Itemcode yang sama dari plot berbeda TIDAK di-merge
 * - Primary key: (companycode, lkhno, plot, itemcode)
 */
class MaterialUsageGeneratorService
{
    /**
     * Generate material usage data from approved RKH
     */
    public function generateMaterialUsageFromRkh($rkhno, $companycode = null)
    {
        try {
            Log::info("===== MaterialUsageGenerator START =====", [
                'rkhno' => $rkhno,
                'companycode_param' => $companycode,
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // PENTING: Gunakan companycode dari parameter
            if (!$companycode) {
                $companycode = session('companycode');
            }
            
            if (!$companycode) {
                throw new \Exception("Company code tidak ditemukan");
            }
            
            Log::info("Using company code from parameter/session", ['companycode' => $companycode]);
            
            // Step 1: Get RKH header data - WITH COMPANYCODE FILTER
            Log::info("Step 1: Fetching RKH header", [
                'rkhno' => $rkhno,
                'companycode' => $companycode
            ]);
            
            $rkhHeader = DB::table('rkhhdr')
                ->where('rkhno', $rkhno)
                ->where('companycode', $companycode)  // PENTING: Filter by companycode
                ->first();
                
            if (!$rkhHeader) {
                Log::error("Step 1 FAILED: RKH not found", [
                    'rkhno' => $rkhno,
                    'companycode' => $companycode
                ]);
                throw new \Exception("RKH tidak ditemukan: {$rkhno} untuk company {$companycode}");
            }
            
            // Gunakan companycode dari parameter, BUKAN dari header
            Log::info("Step 1 SUCCESS: RKH header found", [
                'rkhno' => $rkhno,
                'companycode_used' => $companycode,
                'rkhdate' => $rkhHeader->rkhdate,
                'mandorid' => $rkhHeader->mandorid
            ]);
            
            // Step 2: Check if material usage already exists
            Log::info("Step 2: Checking existing material usage");
            
            $existingUsage = DB::table('usematerialhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
                
            if ($existingUsage) {
                Log::warning("Step 2: Material usage already exists", [
                    'rkhno' => $rkhno,
                    'created_at' => $existingUsage->createdat,
                    'created_by' => $existingUsage->inputby
                ]);
                throw new \Exception("Material usage sudah pernah di-generate untuk RKH: {$rkhno}");
            }
            
            Log::info("Step 2 SUCCESS: No existing material usage found");
            
            // Step 3: Get LKH list untuk RKH ini
            Log::info("Step 3: Fetching LKH list");
            
            $lkhList = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->get();
            
            Log::info("Step 3: LKH list retrieved", [
                'lkh_count' => $lkhList->count(),
                'lkh_numbers' => $lkhList->pluck('lkhno')->toArray()
            ]);
                
            if ($lkhList->isEmpty()) {
                Log::error("Step 3 FAILED: No LKH found", ['rkhno' => $rkhno]);
                throw new \Exception("Tidak ada LKH ditemukan untuk RKH: {$rkhno}. Generate LKH terlebih dahulu.");
            }
            
            // Step 4: Get RKH details that use material
            Log::info("Step 4: Fetching RKH details with material usage");
            
            $rkhDetails = DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->where('usingmaterial', 1)
                ->get();
            
            Log::info("Step 4: RKH details retrieved", [
                'total_details' => $rkhDetails->count(),
                'details_with_herbisida' => $rkhDetails->whereNotNull('herbisidagroupid')->count(),
                'plots' => $rkhDetails->pluck('plot')->unique()->toArray(),
                'activity_codes' => $rkhDetails->pluck('activitycode')->unique()->toArray()
            ]);
                
            if ($rkhDetails->isEmpty()) {
                Log::info("Step 4: No material usage activities found");
                return [
                    'success' => true,
                    'message' => 'Tidak ada aktivitas yang menggunakan material',
                    'total_items' => 0
                ];
            }
            
            // Step 5: Calculate total luas for header
            $totalLuas = $rkhDetails->sum('luasarea');
            Log::info("Step 5: Total luas calculated", ['total_luas' => $totalLuas]);
            
            // Step 6: Get current user - FIX FOR PRODUCTION
            Log::info("Step 6: Getting current user");
            
            $userid = 'SYSTEM'; // Default value
            
            try {
                $currentUser = Auth::user();
                if ($currentUser && isset($currentUser->userid)) {
                    $userid = $currentUser->userid;
                    Log::info("Step 6: Auth user found", [
                        'userid' => $userid,
                        'user_name' => $currentUser->name ?? 'N/A'
                    ]);
                } else {
                    Log::warning("Step 6: No auth user, using SYSTEM", [
                        'auth_check' => Auth::check(),
                        'guard' => Auth::getDefaultDriver()
                    ]);
                }
            } catch (\Exception $authError) {
                Log::warning("Step 6: Auth error, using SYSTEM", [
                    'error' => $authError->getMessage()
                ]);
            }
            
            // Step 7: Create material usage header
            Log::info("Step 7: Creating material usage header", [
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'totalluas' => $totalLuas,
                'inputby' => $userid
            ]);
            
            try {
                DB::table('usematerialhdr')->insert([
                    'companycode' => $companycode,
                    'rkhno' => $rkhno,
                    'totalluas' => $totalLuas,
                    'flagstatus' => 'ACTIVE',
                    'inputby' => $userid,
                    'createdat' => now(),
                    'updateby' => null,
                    'updatedat' => null
                ]);
                
                Log::info("Step 7 SUCCESS: Header created");
            } catch (\Exception $e) {
                Log::error("Step 7 FAILED: Cannot create header", [
                    'error' => $e->getMessage(),
                    'sql_error' => $e->getCode()
                ]);
                throw new \Exception("Failed to create material usage header: " . $e->getMessage());
            }
            
            // Step 8: Process each LKH
            Log::info("Step 8: Processing LKH details");
            
            $totalItemsInserted = 0;
            $errors = [];
            $successfulLKH = [];
            
            foreach ($lkhList as $index => $lkh) {
                Log::info("Step 8.{$index}: Processing LKH", [
                    'lkhno' => $lkh->lkhno,
                    'activitycode' => $lkh->activitycode,
                    'jenistenagakerja' => $lkh->jenistenagakerja
                ]);
                
                try {
                    $itemsInserted = $this->processLkhMaterialUsagePerPlot(
                        $lkh, 
                        $companycode, 
                        $rkhno, 
                        $rkhDetails
                    );
                    
                    $totalItemsInserted += $itemsInserted;
                    
                    if ($itemsInserted > 0) {
                        $successfulLKH[] = $lkh->lkhno;
                    }
                    
                    Log::info("Step 8.{$index} SUCCESS", [
                        'lkhno' => $lkh->lkhno,
                        'items_inserted' => $itemsInserted,
                        'total_so_far' => $totalItemsInserted
                    ]);
                    
                } catch (\Exception $e) {
                    $errorMsg = "Error processing LKH {$lkh->lkhno}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    
                    Log::error("Step 8.{$index} FAILED", [
                        'lkhno' => $lkh->lkhno,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Step 9: Final validation
            Log::info("Step 9: Final validation", [
                'total_items_inserted' => $totalItemsInserted,
                'successful_lkh_count' => count($successfulLKH),
                'error_count' => count($errors)
            ]);
            
            if ($totalItemsInserted === 0) {
                $errorDetail = empty($errors) ? 
                    "No matching material configuration found (check herbisidagroup and herbisidadosage data)" : 
                    implode('; ', $errors);
                    
                Log::warning("Step 9 WARNING: No items generated", [
                    'error_detail' => $errorDetail,
                    'errors' => $errors
                ]);
                
                // Return success with 0 items (don't throw error)
                return [
                    'success' => true,
                    'message' => 'Material usage header created but no items generated (check herbisida configuration)',
                    'total_items' => 0,
                    'total_lkh_processed' => $lkhList->count(),
                    'errors' => $errors
                ];
            }
            
            $message = "Material usage berhasil di-generate per plot ({$totalItemsInserted} items)";
            if (!empty($errors)) {
                $message .= ". Warnings: " . implode('; ', $errors);
            }
            
            Log::info("===== MaterialUsageGenerator SUCCESS =====", [
                'rkhno' => $rkhno,
                'total_items' => $totalItemsInserted,
                'total_lkh_processed' => $lkhList->count(),
                'successful_lkh' => $successfulLKH,
                'message' => $message
            ]);
            
            return [
                'success' => true,
                'message' => $message,
                'total_items' => $totalItemsInserted,
                'total_lkh_processed' => $lkhList->count(),
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            Log::error("===== MaterialUsageGenerator FAILED =====", [
                'rkhno' => $rkhno,
                'companycode' => $companycode ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Process individual LKH to generate material usage items PER PLOT
     * REFACTORED: Generate per plot, tidak merge itemcode
     */
    private function processLkhMaterialUsagePerPlot($lkh, $companycode, $rkhno, $rkhDetails)
    {
        // Find matching RKH details for this LKH activity
        $matchingRkhDetails = $rkhDetails->where('activitycode', $lkh->activitycode)
                                    ->where('jenistenagakerja', $lkh->jenistenagakerja);
        
        if ($matchingRkhDetails->isEmpty()) {
            return 0;
        }
        
        // Get plot details for this LKH
        $lkhPlots = DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkh->lkhno)
            ->get();
            
        if ($lkhPlots->isEmpty()) {
            throw new \Exception("Plot details tidak ditemukan untuk LKH: {$lkh->lkhno}");
        }
        
        $itemsInserted = 0;
        
        // Process each plot SEPARATELY (tidak merge)
        foreach ($lkhPlots as $lkhPlot) {
            // Find corresponding RKH detail for this specific plot
            $rkhForPlot = $matchingRkhDetails->where('blok', $lkhPlot->blok)
                                        ->where('plot', $lkhPlot->plot)
                                        ->first();
            
            if (!$rkhForPlot || !$rkhForPlot->herbisidagroupid) {
                continue;
            }
            
            $plotLuas = (float)$lkhPlot->luasrkh;
            
            // Get herbisida dosage data for this plot's herbisida group
            $herbisidaDosages = DB::table('herbisidadosage as hd')
                ->join('herbisidagroup as hg', function($join) {
                    $join->on('hd.herbisidagroupid', '=', 'hg.herbisidagroupid');
                })
                ->join('herbisida as h', function($join) use ($companycode) {
                    $join->on('hd.companycode', '=', 'h.companycode')
                        ->on('hd.itemcode', '=', 'h.itemcode');
                })
                ->where('hd.companycode', $companycode)
                ->where('hd.herbisidagroupid', $rkhForPlot->herbisidagroupid)
                ->where('hg.activitycode', $lkh->activitycode)
                ->select([
                    'hd.itemcode',
                    'hd.dosageperha',
                    'h.itemname',
                    'h.measure',
                    'hg.rounddosage',
                ])
                ->get();
                
            if ($herbisidaDosages->isEmpty()) {
                continue;
            }
            
            // Insert each item for this specific plot (NO MERGING)
            foreach ($herbisidaDosages as $dosage) {
                $qtyRaw = $plotLuas * $dosage->dosageperha;

                if ($qtyRaw > 0) {
                    if ($dosage->rounddosage == 1) {
                        // Normal rounding ke kelipatan 0.05
                        $truncated = floor($qtyRaw * 100) / 100;
                        $qty = round($truncated / 0.05) * 0.05;
                        if ($qty == 0) $qty = 0.05;
                    } else {
                        // No rounding, round normal 2 desimal
                        $qty = round($qtyRaw, 2);
                        if ($qty == 0) $qty = 0.01;
                    }
                } else {
                    $qty = 0;
                }
                
                // Insert record per plot
                DB::table('usemateriallst')->insert([
                    'companycode' => $companycode,
                    'rkhno' => $rkhno,
                    'lkhno' => $lkh->lkhno,
                    'plot' => $lkhPlot->plot, // PLOT SPECIFIC
                    'itemcode' => $dosage->itemcode,
                    'qty' => $qty,
                    'qtydigunakan' => null,
                    'qtyretur' => 0,
                    'unit' => $dosage->measure,
                    'nouse' => null,
                    'noretur' => null,
                    'itemname' => $dosage->itemname,
                    'dosageperha' => $dosage->dosageperha,
                    'returby' => null,
                    'tglretur' => null,
                    'tglterimaretur' => null,
                    'terimareturby' => null
                ]);
                
                $itemsInserted++;
            }
        }
        
        return $itemsInserted;
    }
    
    /**
     * Check if material usage can be generated for RKH
     * 
     * @param string $rkhno
     * @return bool
     */
    public function canGenerateMaterialUsage($rkhno)
    {
        // Check if RKH exists
        $rkhHeader = DB::table('rkhhdr')->where('rkhno', $rkhno)->first();
        if (!$rkhHeader) {
            return false;
        }
        
        // Check if already generated
        $existingUsage = DB::table('usematerialhdr')
            ->where('companycode', $rkhHeader->companycode)
            ->where('rkhno', $rkhno)
            ->exists();
            
        if ($existingUsage) {
            return false;
        }
        
        // Check if LKH exists
        $hasLkh = DB::table('lkhhdr')
            ->where('companycode', $rkhHeader->companycode)
            ->where('rkhno', $rkhno)
            ->exists();
            
        if (!$hasLkh) {
            return false;
        }
        
        // Check if has material usage
        $hasMaterialUsage = DB::table('rkhlst')
            ->where('companycode', $rkhHeader->companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->exists();
            
        return $hasMaterialUsage;
    }
    
    /**
     * Get material usage summary per LKH (UPDATED untuk per plot)
     * 
     * @param string $lkhno
     * @return array
     */
    public function getLkhMaterialUsageSummary($lkhno)
    {
        try {
            $materialUsage = DB::table('usemateriallst as uml')
                ->leftJoin('herbisida as h', function($join) {
                    $join->on('uml.companycode', '=', 'h.companycode')
                         ->on('uml.itemcode', '=', 'h.itemcode');
                })
                ->where('uml.lkhno', $lkhno)
                ->select([
                    'uml.*',
                    'h.unitprice',
                    DB::raw('(uml.qty * COALESCE(h.unitprice, 0)) as total_cost')
                ])
                ->orderBy('uml.plot')
                ->orderBy('uml.itemcode')
                ->get();
            
            // Group by plot for better display
            $materialsByPlot = $materialUsage->groupBy('plot');
            
            $totalCost = $materialUsage->sum('total_cost');
            $totalQty = $materialUsage->sum('qty');
            
            return [
                'success' => true,
                'lkhno' => $lkhno,
                'materials_by_plot' => $materialsByPlot,
                'materials_all' => $materialUsage,
                'total_items' => $materialUsage->count(),
                'total_plots' => $materialsByPlot->count(),
                'total_qty' => $totalQty,
                'total_cost' => $totalCost
            ];
            
        } catch (\Exception $e) {
            Log::error("Error getting LKH material usage summary: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error getting material usage summary: ' . $e->getMessage(),
                'materials_by_plot' => collect(),
                'materials_all' => collect(),
                'total_cost' => 0
            ];
        }
    }
    
    /**
     * Get material usage summary per plot untuk specific LKH
     * NEW METHOD: Specific untuk per plot analysis
     * 
     * @param string $lkhno
     * @param string $plot
     * @return array
     */
    public function getPlotMaterialUsageSummary($lkhno, $plot)
    {
        try {
            $materialUsage = DB::table('usemateriallst as uml')
                ->leftJoin('herbisida as h', function($join) {
                    $join->on('uml.companycode', '=', 'h.companycode')
                         ->on('uml.itemcode', '=', 'h.itemcode');
                })
                ->where('uml.lkhno', $lkhno)
                ->where('uml.plot', $plot)
                ->select([
                    'uml.*',
                    'h.unitprice',
                    DB::raw('(uml.qty * COALESCE(h.unitprice, 0)) as total_cost')
                ])
                ->orderBy('uml.itemcode')
                ->get();
            
            $totalCost = $materialUsage->sum('total_cost');
            $totalQty = $materialUsage->sum('qty');
            
            return [
                'success' => true,
                'lkhno' => $lkhno,
                'plot' => $plot,
                'materials' => $materialUsage,
                'total_items' => $materialUsage->count(),
                'total_qty' => $totalQty,
                'total_cost' => $totalCost
            ];
            
        } catch (\Exception $e) {
            Log::error("Error getting plot material usage summary: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error getting plot material usage summary: ' . $e->getMessage(),
                'materials' => collect(),
                'total_cost' => 0
            ];
        }
    }
    
    /**
     * Debug method to check data availability (UPDATED)
     * 
     * @param string $rkhno
     * @return array
     */
    public function debugMaterialUsageData($rkhno)
    {
        try {
            $rkhHeader = DB::table('rkhhdr')->where('rkhno', $rkhno)->first();
            if (!$rkhHeader) {
                return ['error' => 'RKH not found'];
            }
            
            $companycode = $rkhHeader->companycode;
            
            // Check RKH details with material
            $rkhDetails = DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->get();
                
            $rkhDetailsWithMaterial = $rkhDetails->where('usingmaterial', 1);
            
            // Check LKH list and their plots
            $lkhList = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->get();
            
            $lkhPlotDetails = [];
            foreach ($lkhList as $lkh) {
                $plots = DB::table('lkhdetailplot')
                    ->where('companycode', $companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->get();
                $lkhPlotDetails[$lkh->lkhno] = $plots->toArray();
            }
            
            // Check herbisida groups and dosages
            $herbisidaGroups = DB::table('herbisidagroup')
                ->where('companycode', $companycode)
                ->get();
                
            $herbisidaDosages = DB::table('herbisidadosage')
                ->where('companycode', $companycode)
                ->get();
            
            return [
                'rkh_header' => $rkhHeader,
                'rkh_details_total' => $rkhDetails->count(),
                'rkh_details_with_material' => $rkhDetailsWithMaterial->count(),
                'rkh_details_with_material_data' => $rkhDetailsWithMaterial->toArray(),
                'lkh_count' => $lkhList->count(),
                'lkh_data' => $lkhList->toArray(),
                'lkh_plot_details' => $lkhPlotDetails,
                'herbisida_groups_count' => $herbisidaGroups->count(),
                'herbisida_dosages_count' => $herbisidaDosages->count()
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}