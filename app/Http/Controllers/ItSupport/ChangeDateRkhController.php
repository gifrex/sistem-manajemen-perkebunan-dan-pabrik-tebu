<?php
// app/Http/Controllers/ItSupport/ChangeDateRkhController.php

namespace App\Http\Controllers\ItSupport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ChangeDateRkhController extends Controller
{
    /**
     * Display main page
     */
    public function index()
    {
        if (Auth::user()->idjabatan !== 17) {
            abort(403, 'Akses ditolak. Hanya untuk IT Support.');
        }
        return view('it-support.change-date-rkh.index', [
            'title' => 'Change RKH Date',
            'navbar' => 'IT Support',
            'nav' => 'Change RKH Date',
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
            
            // Check if RKH is cancelled
            if ($rkhInfo->status === 'Batal') {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH sudah dibatalkan, tidak dapat mengubah tanggal'
                ], 400);
            }
            
            // Count affected records
            $affectedTables = $this->getAffectedTablesCount($companycode, $rkhno);
            
            // Check material status
            $materialStatus = $this->checkMaterialStatus($companycode, $rkhno);
            
            // Check if has external dependencies (Surat Jalan, Timbangan)
            $externalDeps = $this->checkExternalDependencies($companycode, $rkhno);
            
            // Format dates
            $rkhInfo->formatted_date = $rkhInfo->rkhdate ? Carbon::parse($rkhInfo->rkhdate)->format('d M Y') : '-';
            $rkhInfo->formatted_createdat = $rkhInfo->createdat ? Carbon::parse($rkhInfo->createdat)->format('d M Y H:i') : '-';
            
            // Check if date can be changed
            $canChangeDate = $this->canChangeDate($rkhInfo, $materialStatus, $externalDeps);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rkhInfo' => $rkhInfo,
                    'affectedTables' => $affectedTables,
                    'materialStatus' => $materialStatus,
                    'externalDeps' => $externalDeps,
                    'canChangeDate' => $canChangeDate['allowed'],
                    'blockReason' => $canChangeDate['reason'] ?? null,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Search RKH for date change error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update RKH date
     */
    public function update(Request $request, $rkhno)
    {
        // ✅ HAPUS SEMUA VALIDASI TANGGAL - TANGGAL BEBAS
        $request->validate([
            'new_date' => 'required|date',
            'reason' => 'required|string|min:10|max:1000',
            'confirmation' => 'required|string|in:ubah tanggal aman',
        ], [
            'new_date.required' => 'Tanggal baru harus diisi',
            'new_date.date' => 'Format tanggal tidak valid',
            'confirmation.in' => 'Konfirmasi tidak sesuai. Ketik "ubah tanggal aman" untuk melanjutkan.',
        ]);
        
        DB::beginTransaction();
        
        try {
            $companycode = Session::get('companycode');
            $userid = Auth::user()->userid;
            $newDate = $request->new_date;
            
            // Re-validate RKH exists
            $rkhInfo = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
            
            if (!$rkhInfo) {
                throw new \Exception('RKH tidak ditemukan');
            }
            
            $oldDate = $rkhInfo->rkhdate;
            
            // Safety: Check if date is actually different
            if ($oldDate === $newDate) {
                throw new \Exception('Tanggal baru sama dengan tanggal lama');
            }
            
            // Safety: Check material status
            $materialStatus = $this->checkMaterialStatus($companycode, $rkhno);
            $externalDeps = $this->checkExternalDependencies($companycode, $rkhno);
            $canChange = $this->canChangeDate($rkhInfo, $materialStatus, $externalDeps);
            
            if (!$canChange['allowed']) {
                throw new \Exception($canChange['reason']);
            }
            
            // Get material used summary BEFORE date change (with nouse)
            $materialUsedSummary = $this->getMaterialUsedSummary($companycode, $rkhno);
            
            // Execute date change
            $affectedCounts = $this->executeDateChange($companycode, $rkhno, $newDate);
            
            // Create audit log
            $this->createAuditLog([
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'old_date' => $oldDate,
                'new_date' => $newDate,
                'reason' => $request->reason,
                'affected_tables' => $affectedCounts,
                'material_used_summary' => $materialUsedSummary,
                'changedby' => $userid,
                'changedat' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Tanggal RKH {$rkhno} berhasil diubah dari " . 
                            Carbon::parse($oldDate)->format('d/m/Y') . " ke " . 
                            Carbon::parse($newDate)->format('d/m/Y'),
                'data' => [
                    'old_date' => Carbon::parse($oldDate)->format('d M Y'),
                    'new_date' => Carbon::parse($newDate)->format('d M Y'),
                    'affected_counts' => $affectedCounts
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Change RKH date error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of affected tables
     */
    private function getAffectedTablesCount($companycode, $rkhno)
    {
        $lkhNos = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->pluck('lkhno');
        
        return [
            'rkhhdr' => DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->count(),
                
            'rkhlst' => DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->count(),
                
            'lkhhdr' => DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->count(),
                
            'lkhdetailplot' => $lkhNos->isEmpty() ? 0 : DB::table('lkhdetailplot')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->count(),
                
            'lkhdetailworker' => $lkhNos->isEmpty() ? 0 : DB::table('lkhdetailworker')
                ->where('companycode', $companycode)
                ->whereIn('lkhno', $lkhNos)
                ->count(),
                
            'usematerialhdr' => DB::table('usematerialhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->count(),
        ];
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
                'can_change' => true,
                'message' => 'Tidak ada material'
            ];
        }
        
        $allowedStatuses = ['ACTIVE', 'DISPATCHED', 'UPLOADED'];
        $canChange = in_array($material->flagstatus, $allowedStatuses);
        
        return [
            'exists' => true,
            'status' => $material->flagstatus,
            'can_change' => $canChange,
            'message' => $canChange 
                ? "Material berstatus {$material->flagstatus} (aman untuk diubah)"
                : "Material berstatus {$material->flagstatus} (tidak dapat diubah)"
        ];
    }

    /**
     * Check external dependencies
     */
    private function checkExternalDependencies($companycode, $rkhno)
    {
        $lkhPattern = str_replace('RKH', 'LKH', $rkhno) . '-%';
        
        $suratJalanCount = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
            ->count();
        
        $timbanganCount = DB::table('timbanganpayload')
            ->where('companycode', $companycode)
            ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
            ->count();
        
        return [
            'has_surat_jalan' => $suratJalanCount > 0,
            'surat_jalan_count' => $suratJalanCount,
            'has_timbangan' => $timbanganCount > 0,
            'timbangan_count' => $timbanganCount,
            'total_external' => $suratJalanCount + $timbanganCount
        ];
    }

    /**
     * Determine if date can be changed
     */
    private function canChangeDate($rkhInfo, $materialStatus, $externalDeps)
    {
        // Block if RKH is cancelled
        if ($rkhInfo->status === 'Batal') {
            return [
                'allowed' => false,
                'reason' => 'RKH sudah dibatalkan'
            ];
        }
        
        // Block if RKH is completed
        if ($rkhInfo->status === 'Completed') {
            return [
                'allowed' => false,
                'reason' => 'RKH sudah Completed. Gunakan fitur "Uncomplete" terlebih dahulu jika diperlukan.'
            ];
        }
        
        // Block if material status not allowed
        if ($materialStatus['exists'] && !$materialStatus['can_change']) {
            return [
                'allowed' => false,
                'reason' => $materialStatus['message']
            ];
        }
        
        // Warn if has external dependencies (but still allow)
        if ($externalDeps['total_external'] > 0) {
            return [
                'allowed' => true,
                'warning' => "PERINGATAN: RKH ini memiliki {$externalDeps['total_external']} data eksternal (Surat Jalan/Timbangan). Pastikan data di aplikasi lain sudah dikoordinasikan."
            ];
        }
        
        return ['allowed' => true];
    }

    /**
     * Execute date change on all affected tables
     */
    private function executeDateChange($companycode, $rkhno, $newDate)
    {
        $counts = [];
        
        // 1. Update RKH header date
        $counts['rkhhdr'] = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update([
                'rkhdate' => $newDate,
                'updateby' => Auth::user()->userid,
                'updatedat' => now()
            ]);
        
        // 2. Update RKH list date
        $counts['rkhlst'] = DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update(['rkhdate' => $newDate]);
        
        // 3. Update LKH header date
        $counts['lkhhdr'] = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update([
                'lkhdate' => $newDate,
                'updateby' => Auth::user()->userid,
                'updatedat' => now()
            ]);
        
        return $counts;
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
                'tglterimaretur',
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
                'tglterimaretur' => $material->tglterimaretur,
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
     * Create audit log for date change
     */
    private function createAuditLog($data)
    {
        try {
            // Check external impacts
            $lkhPattern = str_replace('RKH', 'LKH', $data['rkhno']) . '-%';
            
            $hasMaterialImpact = DB::table('usematerialhdr')
                ->where('companycode', $data['companycode'])
                ->where('rkhno', $data['rkhno'])
                ->exists() ? 1 : 0;
            
            $hasSuratJalanImpact = DB::table('suratjalanpos')
                ->where('companycode', $data['companycode'])
                ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                ->exists() ? 1 : 0;
            
            $hasTimbanganImpact = DB::table('timbanganpayload')
                ->where('companycode', $data['companycode'])
                ->where('suratjalanno', 'like', "SJ-%-{$lkhPattern}")
                ->exists() ? 1 : 0;
            
            DB::table('rkhauditlog')->insert([
                'companycode' => $data['companycode'],
                'rkhno' => $data['rkhno'],
                'actiontype' => 'CHANGE_DATE',
                'rkhdate' => $data['new_date'],
                'olddate' => $data['old_date'],
                'newdate' => $data['new_date'],
                'affectedtablessummary' => json_encode($data['affected_tables']),
                'hasmaterialimpact' => $hasMaterialImpact,
                'hassuratjalanimpact' => $hasSuratJalanImpact,
                'hastimbanganimpact' => $hasTimbanganImpact,
                'materialusedsummary' => $data['material_used_summary'] ? json_encode($data['material_used_summary']) : null,
                'actionreason' => $data['reason'],
                'actionby' => $data['changedby'],
                'actionat' => $data['changedat'],
            ]);
            
        } catch (\Exception $e) {
            \Log::warning('Could not create audit log: ' . $e->getMessage());
        }
    }
}