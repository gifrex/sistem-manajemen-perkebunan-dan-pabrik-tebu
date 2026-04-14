<?php

namespace App\Http\Controllers\Transaction;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\Timbangan;
use App\Models\Transaction\RkhHdr;

class MappingBsmController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Input',
        ]);
    }


    public function index(Request $request)
    {
        // dd(session(), auth()->user()->userid);
        $title = "Mapping BSM";
        $nav = "Mapping BSM";
        $companycode = session('companycode');
        
        // Initialize data variable
        $data = collect(); // Empty collection by default
        
        // Validate form inputs if submitted
        if ($request->isMethod('post')) {
            $request->validate([
                'tanggalawal' => 'required|date',
                'tanggalakhir' => 'required|date|after_or_equal:tanggalawal',
            ], [
                'tanggalawal.required' => 'Tanggal awal wajib diisi',
                'tanggalawal.date' => 'Format tanggal awal tidak valid',
                'tanggalakhir.required' => 'Tanggal akhir wajib diisi',
                'tanggalakhir.date' => 'Format tanggal akhir tidak valid',
                'tanggalakhir.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal',
            ]);

            // Get data from model when form is submitted
            $Rkhhdr = new Rkhhdr();
            $data = $Rkhhdr->getDataBsmSJ($companycode, $request->tanggalawal, $request->tanggalakhir);
            // dd($data);
            
            // Ensure data is a collection
            if (!$data instanceof \Illuminate\Support\Collection) {
                $data = collect($data);
            }
        }

        return view('transaction.mapping-bsm.index', compact('title', 'nav', 'data'));
    }

    public function getBsmDetail(Request $request)
    {
        $companycode = session('companycode');
        $rkhno = $request->get('rkhno');
        
        if (!$rkhno) {
            return response()->json([
                'success' => false,
                'message' => 'RKH number is required'
            ], 400);
        }
        
        try {
            $Rkhhdr = new Rkhhdr();
            $data = $Rkhhdr->getBsmDetailByRkh($companycode, $rkhno);
            $orphanData = $Rkhhdr->getOrphanBsmByRkh($companycode, $rkhno);

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => count($data),
                'orphan_data' => $orphanData,
                'orphan_total' => count($orphanData),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching BSM detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBsm(Request $request)
    {
        try {
            $request->validate([
                'suratjalanno' => 'required|string',
                'rkhno' => 'required|string',
                'updates' => 'required|array',
                'updates.nilaibersih' => 'nullable|numeric|min:0|max:9999999',
                'updates.nilaisegar' => 'nullable|numeric|min:0|max:9999999',
                'updates.nilaimanis' => 'nullable|numeric|min:0|max:9999999',
            ]);

            $companycode = session('companycode');
            $suratjalanno = $request->suratjalanno;
            $rkhno = $request->rkhno;
            $updates = $request->updates;

            // Calculate average score
            $nilaibersih = isset($updates['nilaibersih']) ? (float)$updates['nilaibersih'] : 0;
            $nilaisegar = isset($updates['nilaisegar']) ? (float)$updates['nilaisegar'] : 0;
            $nilaimanis = isset($updates['nilaimanis']) ? (float)$updates['nilaimanis'] : 0;
            
            $averagescore = ($nilaibersih + $nilaisegar + $nilaimanis) / 3;
            
            // Determine grade based on BSM grading system
            $grade = '';
            if ($averagescore < 1200) {
                $grade = 'A';
            } elseif ($averagescore < 1999) {
                $grade = 'B';
            } elseif ($averagescore >= 2000) {
                $grade = 'C';
            }

            // Get the LKH number and plot information
            $lkhInfo = DB::selectOne("
                SELECT b.lkhno, b.companycode 
                FROM lkhhdr b 
                WHERE b.companycode = ? AND b.rkhno = ?
            ", [$companycode, $rkhno]);

            if (!$lkhInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'LKH tidak ditemukan untuk RKH ini'
                ], 404);
            }

            // Get plot information from suratjalanpos
            $plotInfo = DB::selectOne("
                SELECT plot 
                FROM suratjalanpos 
                WHERE companycode = ? AND suratjalanno = ?
            ", [$companycode, $suratjalanno]);

            if (!$plotInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data surat jalan tidak ditemukan'
                ], 404);
            }

            // Check if record exists in lkhdetailbsm
            $existingRecord = DB::selectOne("
                SELECT id FROM lkhdetailbsm 
                WHERE companycode = ? AND lkhno = ? AND suratjalanno = ?
            ", [$companycode, $lkhInfo->lkhno, $suratjalanno]);

            $updateData = [
                'nilaibersih' => $nilaibersih,
                'nilaisegar' => $nilaisegar,
                'nilaimanis' => $nilaimanis,
                'averagescore' => $averagescore,
                'grade' => $grade,
                'updatedat' => now(),
                'updateby' => auth()->user()->userid, // ADD UPDATEBY
            ];

            if ($existingRecord) {
                // Update existing record
                $updated = DB::table('lkhdetailbsm')
                    ->where('companycode', $companycode)
                    ->where('lkhno', $lkhInfo->lkhno)
                    ->where('suratjalanno', $suratjalanno)
                    ->update($updateData);
            } else {
                // Insert new record with plot
                $insertData = array_merge($updateData, [
                    'companycode' => $companycode,
                    'lkhno' => $lkhInfo->lkhno,
                    'suratjalanno' => $suratjalanno,
                    'plot' => $plotInfo->plot, // Add plot field
                    'createdat' => now(),
                    'inputby' => auth()->user()->userid, // ADD INPUTBY FOR NEW RECORDS
                ]);
                
                $updated = DB::table('lkhdetailbsm')->insert($insertData);
            }

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data BSM berhasil diperbarui',
                    'data' => [
                        'suratjalanno' => $suratjalanno,
                        'nilaibersih' => $nilaibersih,
                        'nilaisegar' => $nilaisegar,
                        'nilaimanis' => $nilaimanis,
                        'averagescore' => $averagescore,
                        'grade' => $grade,
                        'plot' => $plotInfo->plot,
                        'updateby' => auth()->user()->userid // ADD TO RESPONSE
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data BSM'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating BSM:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update multiple BSM records in bulk - WITH PLOT FIELD AND UPDATEBY
     */
    public function updateBsmBulk(Request $request)
    {
        try {
            $request->validate([
                'rkhno' => 'required|string',
                'bulk_updates' => 'required|array',
                'bulk_updates.*.suratjalanno' => 'required|string',
                'bulk_updates.*.updates' => 'required|array',
                'bulk_updates.*.updates.nilaibersih' => 'nullable|numeric|min:0|max:9999999',
                'bulk_updates.*.updates.nilaisegar' => 'nullable|numeric|min:0|max:9999999',
                'bulk_updates.*.updates.nilaimanis' => 'nullable|numeric|min:0|max:9999999',
            ]);

            $companycode = session('companycode');
            $rkhno = $request->rkhno;
            $bulkUpdates = $request->bulk_updates;

            // Get the LKH number for this RKH
            $lkhInfo = DB::selectOne("
                SELECT lkhno, companycode 
                FROM lkhhdr 
                WHERE companycode = ? AND rkhno = ?
            ", [$companycode, $rkhno]);

            if (!$lkhInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'LKH tidak ditemukan untuk RKH ini'
                ], 404);
            }

            $updatedCount = 0;
            $errors = [];

            DB::beginTransaction();

            try {
                foreach ($bulkUpdates as $update) {
                    $suratjalanno = $update['suratjalanno'];
                    $updates = $update['updates'];

                    // Calculate values
                    $nilaibersih = isset($updates['nilaibersih']) ? (float)$updates['nilaibersih'] : 0;
                    $nilaisegar = isset($updates['nilaisegar']) ? (float)$updates['nilaisegar'] : 0;
                    $nilaimanis = isset($updates['nilaimanis']) ? (float)$updates['nilaimanis'] : 0;
                    
                    $averagescore = ($nilaibersih + $nilaisegar + $nilaimanis) / 3;
                    
                    // Determine grade using BSM grading system
                    $grade = '';
                    if ($averagescore < 1200) {
                        $grade = 'A';
                    } elseif ($averagescore < 1999) {
                        $grade = 'B';
                    } elseif ($averagescore >= 2000) {
                        $grade = 'C';
                    }

                    // Get plot information for this surat jalan
                    $plotInfo = DB::selectOne("
                        SELECT plot 
                        FROM suratjalanpos 
                        WHERE companycode = ? AND suratjalanno = ?
                    ", [$companycode, $suratjalanno]);

                    if (!$plotInfo) {
                        $errors[] = "Data surat jalan tidak ditemukan untuk: {$suratjalanno}";
                        continue;
                    }

                    // Check if record exists
                    $existingRecord = DB::selectOne("
                        SELECT id FROM lkhdetailbsm 
                        WHERE companycode = ? AND lkhno = ? AND suratjalanno = ?
                    ", [$companycode, $lkhInfo->lkhno, $suratjalanno]);

                    $updateData = [
                        'nilaibersih' => $nilaibersih,
                        'nilaisegar' => $nilaisegar,
                        'nilaimanis' => $nilaimanis,
                        'averagescore' => $averagescore,
                        'grade' => $grade,
                        'updatedat' => now(),
                        'updateby' => auth()->user()->userid, // ADD UPDATEBY
                    ];

                    if ($existingRecord) {
                        // Update existing record
                        $result = DB::table('lkhdetailbsm')
                            ->where('companycode', $companycode)
                            ->where('lkhno', $lkhInfo->lkhno)
                            ->where('suratjalanno', $suratjalanno)
                            ->update($updateData);
                    } else {
                        // Insert new record with plot
                        $insertData = array_merge($updateData, [
                            'companycode' => $companycode,
                            'lkhno' => $lkhInfo->lkhno,
                            'suratjalanno' => $suratjalanno,
                            'plot' => $plotInfo->plot, // Add plot field
                            'createdat' => now(),
                            'inputby' => auth()->user()->userid, // ADD INPUTBY FOR NEW RECORDS
                        ]);
                        
                        $result = DB::table('lkhdetailbsm')->insert($insertData);
                    }

                    if ($result) {
                        $updatedCount++;
                    } else {
                        $errors[] = "Gagal memperbarui data untuk surat jalan: {$suratjalanno}";
                    }
                }

                if (count($errors) > 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Beberapa data gagal diperbarui',
                        'errors' => $errors
                    ], 500);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil memperbarui {$updatedCount} data BSM",
                    'updated_count' => $updatedCount,
                    'updateby' => auth()->user()->userid // ADD TO RESPONSE
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error bulk updating BSM:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBsmForCopy(Request $request)
    {
        try {
            $request->validate([
                'rkhno' => 'required|string',
                'plot' => 'required|string',
                'target_suratjalanno' => 'required|string'
            ]);
            
            $companycode = session('companycode');
            if (!$companycode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session company code tidak ditemukan'
                ], 401);
            }
            
            $rkhno = $request->rkhno;
            $plot = $request->plot;
            $targetSuratjalanno = $request->target_suratjalanno;
            
            // 1. Verify target BSM is empty (kosong)
            $targetBsm = DB::selectOne("
                SELECT c.id, c.nilaibersih, c.nilaisegar, c.nilaimanis
                FROM suratjalanpos a
                LEFT JOIN lkhhdr b ON a.companycode = b.companycode AND b.rkhno = ?
                LEFT JOIN lkhdetailbsm c ON b.companycode = c.companycode 
                    AND b.lkhno = c.lkhno 
                    AND a.suratjalanno = c.suratjalanno
                WHERE a.companycode = ? 
                    AND a.suratjalanno = ?
                    AND LEFT(a.tanggalangkut, 10) = b.lkhdate
            ", [$rkhno, $companycode, $targetSuratjalanno]);
            
            // Check if target has values (not empty)
            if ($targetBsm && 
                ($targetBsm->nilaibersih > 0 || 
                $targetBsm->nilaisegar > 0 || 
                $targetBsm->nilaimanis > 0)) {
                return response()->json([
                    'success' => false,
                    'message' => 'BSM sudah memiliki nilai. Hanya BSM kosong yang bisa melakukan copy.'
                ], 400);
            }
            
            // 2. Get available BSM sources (yang sudah ada nilai) from same plot  
            $bsmSources = app(Rkhhdr::class)->getBsmDataForCopy($companycode, $rkhno, $plot);
            
            if ($bsmSources->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data BSM dengan nilai lengkap di plot ini untuk dicopy',
                    'data' => []
                ]);
            }
            
            \Log::info('BSM Copy Sources Found', [
                'target_sj' => $targetSuratjalanno,
                'plot' => $plot,
                'sources_count' => $bsmSources->count()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Data BSM sumber berhasil dimuat',
                'data' => $bsmSources->toArray(),
                'target_info' => [
                    'suratjalanno' => $targetSuratjalanno,
                    'plot' => $plot,
                    'is_empty' => true
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in getBsmForCopy', [
                'message' => $e->getMessage(),
                'target_sj' => $request->target_suratjalanno ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data untuk copy'
            ], 500);
        }
    }

    /**
     * Copy BSM from source to empty target - WITH UPDATEBY
     */
    public function copyBsm(Request $request)
    {
        try {
            $request->validate([
                'source_bsm_id' => 'required|integer',
                'target_suratjalanno' => 'required|string',
                'rkhno' => 'required|string'
            ]);
            
            $companycode = session('companycode');
            if (!$companycode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session company code tidak ditemukan'
                ], 401);
            }
            
            $sourceBsmId = $request->source_bsm_id;
            $targetSuratjalanno = $request->target_suratjalanno;
            $rkhno = $request->rkhno;
            
            \Log::info('Executing BSM Copy', [
                'source_bsm_id' => $sourceBsmId,
                'target_sj' => $targetSuratjalanno,
                'rkhno' => $rkhno
            ]);
            
            // 1. Verify source BSM exists and has values
            $sourceBsm = DB::selectOne("
                SELECT c.*, a.plot as source_plot
                FROM suratjalanpos a
                LEFT JOIN lkhhdr b ON a.companycode = b.companycode 
                LEFT JOIN lkhdetailbsm c ON b.companycode = c.companycode 
                    AND b.lkhno = c.lkhno 
                    AND a.suratjalanno = c.suratjalanno
                WHERE c.id = ? AND c.companycode = ?
            ", [$sourceBsmId, $companycode]);
            
            if (!$sourceBsm || !$sourceBsm->nilaibersih || !$sourceBsm->nilaisegar || !$sourceBsm->nilaimanis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data BSM sumber tidak valid atau tidak memiliki nilai lengkap'
                ], 404);
            }
            
            // 2. Verify target BSM is in same plot
            $targetBsm = DB::selectOne("
                SELECT a.plot as target_plot, c.nilaibersih, c.nilaisegar, c.nilaimanis
                FROM suratjalanpos a
                LEFT JOIN lkhhdr b ON a.companycode = b.companycode AND b.rkhno = ?
                LEFT JOIN lkhdetailbsm c ON b.companycode = c.companycode 
                    AND b.lkhno = c.lkhno 
                    AND a.suratjalanno = c.suratjalanno
                WHERE a.companycode = ? 
                    AND a.suratjalanno = ?
                    AND LEFT(a.tanggalangkut, 10) = b.lkhdate
            ", [$rkhno, $companycode, $targetSuratjalanno]);
            
            if (!$targetBsm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data target tidak ditemukan'
                ], 404);
            }
            
            // 3. Verify same plot
            if ($sourceBsm->source_plot !== $targetBsm->target_plot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Copy hanya bisa dilakukan dalam plot yang sama'
                ], 400);
            }
            
            // 4. Verify target is empty
            if ($targetBsm->nilaibersih > 0 || $targetBsm->nilaisegar > 0 || $targetBsm->nilaimanis > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target BSM sudah memiliki nilai. Hanya BSM kosong yang bisa melakukan copy.'
                ], 400);
            }
            
            // 5. Execute copy operation - ADD UPDATEBY
            $updateby = auth()->user()->userid;
            $result = app(Rkhhdr::class)->copyBsmToEmpty($companycode, $targetSuratjalanno, $sourceBsmId, $rkhno, $updateby);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in copyBsm', [
                'message' => $e->getMessage(),
                'source_bsm_id' => $request->source_bsm_id ?? null,
                'target_suratjalanno' => $request->target_suratjalanno ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat copy data BSM: ' . $e->getMessage()
            ], 500);
        }
    }

    public function remapBsm(Request $request)
    {
        try {
            $request->validate([
                'bsm_id'              => 'required|integer',
                'target_suratjalanno' => 'required|string',
                'rkhno'               => 'required|string',
            ]);

            $companycode = session('companycode');
            $bsmId       = $request->bsm_id;
            $targetSj    = $request->target_suratjalanno;
            $rkhno       = $request->rkhno;

            // Pastikan BSM ini memang milik RKH ini
            $bsm = DB::selectOne("
                SELECT c.id, c.suratjalanno
                FROM lkhdetailbsm c
                INNER JOIN lkhhdr b ON c.lkhno = b.lkhno AND b.companycode = c.companycode
                WHERE c.id = ? AND c.companycode = ? AND b.rkhno = ?
            ", [$bsmId, $companycode, $rkhno]);

            if (!$bsm) {
                return response()->json(['success' => false, 'message' => 'Data BSM tidak ditemukan'], 404);
            }

            // Pastikan SJ tujuan ada di suratjalanpos
            $targetInfo = DB::selectOne("
                SELECT plot FROM suratjalanpos WHERE companycode = ? AND suratjalanno = ?
            ", [$companycode, $targetSj]);

            if (!$targetInfo) {
                return response()->json(['success' => false, 'message' => 'Surat jalan tujuan tidak ditemukan'], 404);
            }

            // Pastikan SJ tujuan belum punya BSM lain
            $existing = DB::selectOne("
                SELECT id FROM lkhdetailbsm WHERE companycode = ? AND suratjalanno = ? AND id != ?
            ", [$companycode, $targetSj, $bsmId]);

            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Surat jalan tujuan sudah memiliki data BSM'], 400);
            }

            DB::table('lkhdetailbsm')
                ->where('id', $bsmId)
                ->where('companycode', $companycode)
                ->update([
                    'suratjalanno' => $targetSj,
                    'plot'         => $targetInfo->plot,
                    'updateby'     => auth()->user()->userid,
                    'updatedat'    => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "BSM berhasil dihubungkan ke surat jalan {$targetSj}",
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error in remapBsm', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
