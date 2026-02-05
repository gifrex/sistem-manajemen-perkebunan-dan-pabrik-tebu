<?php
// app/Http/Controllers/ItSupport/DeleteRkhController.php

namespace App\Http\Controllers\ItSupport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DeleteRkhController extends Controller
{
    /**
     * Display main page (simple search form only)
     */
    public function index()
    {
        if (Auth::user()->idjabatan !== 17) {
            abort(403, 'Akses ditolak. Hanya untuk IT Support.');
        }
        return view('it-support.delete-rkh.index', [
            'title' => 'Delete RKH',
            'navbar' => 'IT Support',
            'nav' => 'Delete RKH',
        ]);
    }

    /**
     * Search RKH and get preview (AJAX)
     */
    public function search(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string'
        ]);

        try {
            $companycode = Session::get('companycode');
            $rkhno = strtoupper(trim($request->rkhno));
            
            // Get RKH basic info
            $rkhInfo = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
            
            if (!$rkhInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH tidak ditemukan'
                ], 404);
            }
            
            // Generate LKH pattern
            $lkhPattern = str_replace('RKH', 'LKH', $rkhno) . '-%';
            
            // Count affected records
            $impact = [
                'rkhhdr' => DB::table('rkhhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'rkhlst' => DB::table('rkhlst')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'rkhlstworker' => DB::table('rkhlstworker')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'rkhlstkendaraan' => DB::table('rkhlstkendaraan')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'lkhhdr' => DB::table('lkhhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailplot' => DB::table('lkhdetailplot as ldp')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldp.lkhno', '=', 'lh.lkhno')
                             ->on('ldp.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailworker' => DB::table('lkhdetailworker as ldw')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldw.lkhno', '=', 'lh.lkhno')
                             ->on('ldw.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailkendaraan' => DB::table('lkhdetailkendaraan as ldk')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldk.lkhno', '=', 'lh.lkhno')
                             ->on('ldk.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailmaterial' => DB::table('lkhdetailmaterial as ldm')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldm.lkhno', '=', 'lh.lkhno')
                             ->on('ldm.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'lkhdetailbsm' => DB::table('lkhdetailbsm as ldb')
                    ->join('lkhhdr as lh', function($join) {
                        $join->on('ldb.lkhno', '=', 'lh.lkhno')
                             ->on('ldb.companycode', '=', 'lh.companycode');
                    })
                    ->where('lh.companycode', $companycode)
                    ->where('lh.rkhno', $rkhno)
                    ->count(),
                    
                'usematerialhdr' => DB::table('usematerialhdr')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'usemateriallst' => DB::table('usemateriallst')
                    ->where('companycode', $companycode)
                    ->where('rkhno', $rkhno)
                    ->count(),
                    
                'suratjalanpos' => DB::table('suratjalanpos')
                    ->where('companycode', $companycode)
                    ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                    ->count(),
                    
                'timbanganpayload' => DB::table('timbanganpayload')
                    ->where('companycode', $companycode)
                    ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                    ->count(),
            ];
            
            // Check material status - CRITICAL SAFETY CHECK
            $materialStatus = $this->checkMaterialStatus($companycode, $rkhno);
            
            // Check if deletion is allowed
            $canDelete = $this->canDeleteRkh($rkhInfo, $materialStatus, $impact);
            
            // Check critical impacts
            $hasCriticalImpact = (
                $impact['usematerialhdr'] > 0 || 
                $impact['usemateriallst'] > 0 || 
                $impact['suratjalanpos'] > 0 || 
                $impact['timbanganpayload'] > 0
            );
            
            // Format dates
            $rkhInfo->formatted_date = $rkhInfo->rkhdate ? date('d M Y', strtotime($rkhInfo->rkhdate)) : '-';
            $rkhInfo->formatted_createdat = $rkhInfo->createdat ? date('d M Y H:i', strtotime($rkhInfo->createdat)) : '-';
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rkhInfo' => $rkhInfo,
                    'impact' => $impact,
                    'materialStatus' => $materialStatus,
                    'canDelete' => $canDelete['allowed'],
                    'blockReason' => $canDelete['reason'] ?? null,
                    'hasCriticalImpact' => $hasCriticalImpact,
                    'hasmaterialimpact' => ($impact['usematerialhdr'] > 0 || $impact['usemateriallst'] > 0) ? 1 : 0,
                    'hassuratjalanimpact' => $impact['suratjalanpos'] > 0 ? 1 : 0,
                    'hastimbanganimpact' => $impact['timbanganpayload'] > 0 ? 1 : 0,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Search RKH error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete RKH with audit log
     */
    public function destroy(Request $request, $rkhno)
    {
        $request->validate([
            'deletionreason' => 'required|string|max:1000',
            'confirmation' => 'required|string|in:hapus aman',
        ], [
            'confirmation.in' => 'Konfirmasi tidak sesuai. Ketik "hapus aman" untuk melanjutkan.',
        ]);
        
        DB::beginTransaction();
        
        try {
            $companycode = Session::get('companycode');
            $userid = Auth::user()->userid;
            
            // Get RKH info and impact data for audit
            $searchResponse = $this->search(new Request(['rkhno' => $rkhno]));
            $searchData = json_decode($searchResponse->getContent());
            
            if (!$searchData->success) {
                throw new \Exception('RKH tidak ditemukan');
            }
            
            $data = $searchData->data;
            
            // CRITICAL: Check if deletion is allowed
            if (!$data->canDelete) {
                throw new \Exception($data->blockReason);
            }
            
            // Get material used summary BEFORE deletion (with nouse)
            $materialUsedSummary = $this->getMaterialUsedSummary($companycode, $rkhno);
            
            // Insert audit log BEFORE deletion
            DB::table('rkhauditlog')->insert([
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'actiontype' => 'DELETE',
                'rkhdate' => $data->rkhInfo->rkhdate,
                'olddate' => null,
                'newdate' => null,
                'affectedtablessummary' => json_encode($data->impact),
                'hasmaterialimpact' => $data->hasmaterialimpact,
                'hassuratjalanimpact' => $data->hassuratjalanimpact,
                'hastimbanganimpact' => $data->hastimbanganimpact,
                'materialusedsummary' => $materialUsedSummary ? json_encode($materialUsedSummary) : null,
                'actionreason' => $request->deletionreason,
                'actionby' => $userid,
                'actionat' => now(),
            ]);
            
            // Execute deletion
            $this->executeRkhDeletion($companycode, $rkhno);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "RKH {$rkhno} berhasil dihapus dan tercatat di audit log"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Delete RKH error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus RKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check material status
     */
    private function checkMaterialStatus($companycode, $rkhno)
    {
        $material = DB::table('usematerialhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->first();
        
        if (!$material) {
            return [
                'exists' => false,
                'status' => null,
                'can_delete' => true,
                'message' => 'Tidak ada material'
            ];
        }
        
        // CRITICAL: Block deletion if DISPATCHED
        $canDelete = ($material->flagstatus !== 'DISPATCHED');
        
        return [
            'exists' => true,
            'status' => $material->flagstatus,
            'can_delete' => $canDelete,
            'message' => $canDelete 
                ? "Material berstatus {$material->flagstatus} (dapat dihapus)"
                : "Material berstatus DISPATCHED (TIDAK DAPAT DIHAPUS)"
        ];
    }

    /**
     * Determine if RKH can be deleted
     */
    private function canDeleteRkh($rkhInfo, $materialStatus, $impact)
    {
        // Block if RKH is Completed
        if ($rkhInfo->status === 'Completed') {
            return [
                'allowed' => false,
                'reason' => 'RKH sudah Completed. Gunakan fitur "Uncomplete" terlebih dahulu.'
            ];
        }
        
        // CRITICAL: Block if material is DISPATCHED
        if ($materialStatus['exists'] && !$materialStatus['can_delete']) {
            return [
                'allowed' => false,
                'reason' => 'Material sudah DISPATCHED. Tidak dapat dihapus karena sudah diserahkan ke gudang. Gunakan proses retur material jika diperlukan.'
            ];
        }
        
        // Warn if has external dependencies (but still allow if not DISPATCHED)
        if ($impact['suratjalanpos'] > 0 || $impact['timbanganpayload'] > 0) {
            return [
                'allowed' => true,
                'warning' => "PERINGATAN: RKH ini memiliki data eksternal (Surat Jalan/Timbangan). Pastikan data di aplikasi lain sudah dikoordinasikan."
            ];
        }
        
        return ['allowed' => true];
    }

    /**
     * Get material used summary with nouse
     */
    private function getMaterialUsedSummary($companycode, $rkhno)
    {
        $materials = DB::table('usemateriallst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->select([
                'lkhno',
                'plot',
                'itemcode',
                'itemname',
                'qty',
                'qtydigunakan',
                'qtyretur',
                'unit',
                'nouse',
                'noretur',
                'dosageperha',
                'returby',
                'tglretur',
                'tglterimaretur',  // ✅ FIXED: Nama kolom yang benar
                'terimareturby',
                'mobiledate',
                'itemprice',
                'costcenter',
                'startstock',
                'endstock',
                'tgluse'
            ])
            ->get();
        
        if ($materials->isEmpty()) {
            return null;
        }
        
        return $materials->map(function($material) {
            return [
                'lkhno' => $material->lkhno,
                'plot' => $material->plot,
                'itemcode' => $material->itemcode,
                'itemname' => $material->itemname,
                'qty' => $material->qty,
                'qtydigunakan' => $material->qtydigunakan,
                'qtyretur' => $material->qtyretur,
                'unit' => $material->unit,
                'nouse' => $material->nouse,
                'noretur' => $material->noretur,
                'dosageperha' => $material->dosageperha,
                'returby' => $material->returby,
                'tglretur' => $material->tglretur,
                'tglterimaretur' => $material->tglterimaretur,  // ✅ FIXED
                'terimareturby' => $material->terimareturby,
                'mobiledate' => $material->mobiledate,
                'itemprice' => $material->itemprice,
                'costcenter' => $material->costcenter,
                'startstock' => $material->startstock,
                'endstock' => $material->endstock,
                'tgluse' => $material->tgluse,
            ];
        })->toArray();
    }

    /**
     * Execute RKH deletion in correct order
     */
    private function executeRkhDeletion($companycode, $rkhno)
    {
        $lkhPattern = str_replace('RKH', 'LKH', $rkhno) . '-%';
        
        // 1. Timbangan payload
        DB::table('timbanganpayload')
            ->where('companycode', $companycode)
            ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
            ->delete();
        
        // 2. Surat jalan pos
        DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
            ->delete();
        
        // 3. Use material lst
        $lkhNos = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->pluck('lkhno');
        
        if ($lkhNos->isNotEmpty()) {
            DB::table('usemateriallst')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->delete();
        }
        
        // 4. Use material hdr
        DB::table('usematerialhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
        
        // 5-9. LKH detail tables
        if ($lkhNos->isNotEmpty()) {
            DB::table('lkhdetailbsm')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->delete();
            
            DB::table('lkhdetailmaterial')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->delete();
            
            DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->delete();
            
            DB::table('lkhdetailworker')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->delete();
            
            DB::table('lkhdetailplot')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->delete();
        }
        
        // 10. LKH hdr
        DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
        
        // 11-13. RKH detail tables
        DB::table('rkhlstkendaraan')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
        
        DB::table('rkhlstworker')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
        
        DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
        
        // 14. RKH hdr (last)
        DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }
}