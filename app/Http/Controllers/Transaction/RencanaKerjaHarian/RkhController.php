<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Rkh\RkhService;
use App\Services\Transaction\RencanaKerjaHarian\Rkh\RkhValidationService;
use App\Services\Transaction\RencanaKerjaHarian\Rkh\RkhNumberGeneratorService;
use App\Services\Transaction\RencanaKerjaHarian\Utility\RkhUtilityService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class RkhController extends Controller
{
    protected $rkhService;
    protected $validationService;
    protected $numberGenerator;
    protected $utilityService;

    public function __construct(
        RkhService $rkhService,
        RkhValidationService $validationService,
        RkhNumberGeneratorService $numberGenerator,
        RkhUtilityService $utilityService
    ) {
        $this->rkhService = $rkhService;
        $this->validationService = $validationService;
        $this->numberGenerator = $numberGenerator;
        $this->utilityService = $utilityService;
    }

    /**
     * Display listing of RKH with filters
     */
    public function index(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Extract filters from request
            $filters = [
                'search' => $request->input('search', ''),
                'filterApproval' => $request->input('filter_approval', ''),
                'filterStatus' => $request->input('filter_status', ''),
                'filterDate' => $request->input('filter_date', date('Y-m-d')),
                'allDate' => $request->boolean('all_date'),
            ];

            $perPage = 50;

            // Get paginated data from service
            $serviceData = $this->rkhService->getIndexPageData($filters, $perPage, $companycode);

            // Return view with all required variables
            return view('transaction.rencanakerjaharian.rkh-index', [
                'title' => 'Rencana Kerja Harian',
                'navbar' => 'Transaction',
                'nav' => 'Rencana Kerja Harian',
                
                // Data
                'rkhData' => $serviceData['rkhData'],
                
                // Filters (for form state)
                'search' => $filters['search'],
                'filterApproval' => $filters['filterApproval'],
                'filterStatus' => $filters['filterStatus'],
                'filterDate' => $filters['filterDate'],
                'allDate' => $filters['allDate'],
                
                // Optional data
                'absentenagakerja' => $serviceData['absentenagakerja'] ?? [],
                'mandors' => $serviceData['mandors'] ?? []
            ]);

        } catch (\Exception $e) {
            \Log::error('RKH Index Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memuat data RKH: ' . $e->getMessage());
        }
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $date = $request->input('date', date('Y-m-d'));
            $mandorId = $request->input('mandor_id');

            // VALIDATION 1: Date Range (Today to +7 days)
            $today = date('Y-m-d');
            $maxDate = date('Y-m-d', strtotime('+7 days'));
            
            if ($date < $today) {
                return redirect()
                    ->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Tidak dapat membuat RKH untuk tanggal yang sudah lewat.');
            }
            
            if ($date > $maxDate) {
                return redirect()
                    ->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Tidak dapat membuat RKH lebih dari 7 hari ke depan.');
            }

            // VALIDATION 2: Check Outstanding RKH
            if ($mandorId) {
                $outstandingCheck = $this->utilityService->checkOutstandingRkh($companycode, $mandorId);
                
                if ($outstandingCheck['hasOutstanding']) {
                    return redirect()
                        ->route('transaction.rencanakerjaharian.index')
                        ->with('error', 'Tidak dapat membuat RKH baru. Mandor ' . $mandorId . ' masih memiliki RKH outstanding: ' . $outstandingCheck['details']['rkhno'])
                        ->with('outstanding_details', $outstandingCheck['details']);
                }
            }

            // VALIDATION 3: Check Duplicate RKH for Same Date & Mandor
            if ($mandorId && $date) {
                $duplicateCheck = $this->utilityService->checkDuplicateRkh($companycode, $mandorId, $date);
                
                if ($duplicateCheck['exists']) {
                    return redirect()
                        ->route('transaction.rencanakerjaharian.index')
                        ->with('error', 'RKH untuk Mandor ' . $mandorId . ' pada tanggal ' . date('d/m/Y', strtotime($date)) . ' sudah ada (No: ' . $duplicateCheck['rkhno'] . ')')
                        ->with('duplicate_rkhno', $duplicateCheck['rkhno']);
                }
            }

            // Load page data
            $data = $this->rkhService->getCreatePageData($date, $mandorId, $companycode);

            return view('transaction.rencanakerjaharian.rkh-create', array_merge($data, [
                'title' => 'Create Rencana Kerja Harian',
                'navbar' => 'Transaction',
                'nav' => 'Rencana Kerja Harian',
            ]));

        } catch (\Exception $e) {
            \Log::error('RKH Create Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function createV2(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $date = $request->input('date', date('Y-m-d'));
            $mandorId = $request->input('mandor_id');

            // VALIDATION 1: Date Range (Today to +7 days)
            $today = date('Y-m-d');
            $maxDate = date('Y-m-d', strtotime('+7 days'));
            
            if ($date < $today) {
                return redirect()
                    ->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Tidak dapat membuat RKH untuk tanggal yang sudah lewat.');
            }
            
            if ($date > $maxDate) {
                return redirect()
                    ->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Tidak dapat membuat RKH lebih dari 7 hari ke depan.');
            }

            // VALIDATION 2: Check Outstanding RKH
            if ($mandorId) {
                $outstandingCheck = $this->utilityService->checkOutstandingRkh($companycode, $mandorId);
                
                if ($outstandingCheck['hasOutstanding']) {
                    return redirect()
                        ->route('transaction.rencanakerjaharian.index')
                        ->with('error', 'Tidak dapat membuat RKH baru. Mandor ' . $mandorId . ' masih memiliki RKH outstanding: ' . $outstandingCheck['details']['rkhno'])
                        ->with('outstanding_details', $outstandingCheck['details']);
                }
            }

            // VALIDATION 3: Check Duplicate RKH for Same Date & Mandor
            if ($mandorId && $date) {
                $duplicateCheck = $this->utilityService->checkDuplicateRkh($companycode, $mandorId, $date);
                
                if ($duplicateCheck['exists']) {
                    return redirect()
                        ->route('transaction.rencanakerjaharian.index')
                        ->with('error', 'RKH untuk Mandor ' . $mandorId . ' pada tanggal ' . date('d/m/Y', strtotime($date)) . ' sudah ada (No: ' . $duplicateCheck['rkhno'] . ')')
                        ->with('duplicate_rkhno', $duplicateCheck['rkhno']);
                }
            }

            // Load page data
            $data = $this->rkhService->getCreatePageData($date, $mandorId, $companycode);

            \Log::info('RKH Create V2 Data Check', [
                'activities_count' => count($data['activities'] ?? []),
                'mandor_id' => $mandorId,
                'date' => $date
            ]);

            return view('transaction.rencanakerjaharian.rkh-create-v2', array_merge($data, [
                'title' => 'Create RKH (Modern Wizard)',
                'navbar' => 'Transaction',
                'nav' => 'Rencana Kerja Harian',
            ]));

        } catch (\Exception $e) {
            \Log::error('RKH Create V2 Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function editV2($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $data = $this->rkhService->getEditPageData($rkhno, $companycode);

            return view('transaction.rencanakerjaharian.rkh-edit-v2', array_merge($data, [
                'title' => 'Edit RKH (Modern Wizard)',
                'navbar' => 'Transaction',
                'nav' => 'Rencana Kerja Harian',
            ]));

        } catch (\Exception $e) {
            \Log::error('RKH Edit V2 Error', [
                'rkhno' => $rkhno,
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store new RKH
     */
    public function store(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $userid = Auth::user()->userid;
            $mandorId = $request->input('mandor_id');
            $tanggal = $request->input('tanggal');

            // PROTECTION 1: Cache-based submission lock
            $cacheKey = "rkh_submit_{$companycode}_{$mandorId}_{$tanggal}";
            
            if (Cache::has($cacheKey)) {
                \Log::warning('RKH Store - Duplicate Submission Blocked', [
                    'mandor_id' => $mandorId,
                    'tanggal' => $tanggal
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Sedang diproses, mohon tunggu...'
                ], 429);
            }

            // Lock for 10 seconds
            Cache::put($cacheKey, true, 10);

            try {
                \Log::info('RKH Store - Incoming Request', [
                    'tanggal' => $tanggal,
                    'mandor_id' => $mandorId,
                    'rows_count' => count($request->input('rows', [])),
                ]);

                // PROTECTION 2: Database duplicate check
                $duplicateCheck = $this->utilityService->checkDuplicateRkh($companycode, $mandorId, $tanggal);
                
                if ($duplicateCheck['exists']) {
                    Cache::forget($cacheKey);
                    
                    \Log::warning('RKH Store - Duplicate Found', [
                        'existing_rkhno' => $duplicateCheck['rkhno']
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'RKH untuk tanggal ini sudah ada (No: ' . $duplicateCheck['rkhno'] . ')'
                    ], 409);
                }

                // Validate request
                $this->validationService->validateRkhRequest($request);

                \Log::info('RKH Store - Validation Passed');

                // Prepare DTO
                $dto = [
                    'companycode' => $companycode,
                    'userid' => $userid,
                    'rkhdate' => $tanggal,
                    'mandorid' => $mandorId,
                    'rows' => $request->input('rows', []),
                    'workers' => $request->input('workers', []),
                    'kendaraan' => $request->input('kendaraan', []),
                ];

                // Create RKH via service
                $result = $this->rkhService->createRkh($dto, $companycode, $userid);

                // Clear lock after success
                Cache::forget($cacheKey);

                \Log::info('RKH Store - Service Result', [
                    'success' => $result['success'],
                    'rkhno' => $result['rkhno'] ?? 'N/A'
                ]);

                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message'],
                        'rkhno' => $result['rkhno'],
                        'redirect_url' => route('transaction.rencanakerjaharian.show', $result['rkhno'])
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);

            } catch (\Exception $e) {
                // Clear lock on error
                Cache::forget($cacheKey);
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('RKH Store - Validation Failed', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Form Belum Lengkap',
                'errors' => $e->validator->errors()->all()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('RKH Store Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function show($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $data = $this->rkhService->getShowPageData($rkhno, $companycode);

            return view('transaction.rencanakerjaharian.rkh-show', array_merge($data, [
                'title'  => 'Detail Rencana Kerja Harian',
                'navbar' => 'Transaction',
                'nav'    => 'Rencana Kerja Harian',
            ]));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        } catch (\Exception $e) {
            \Log::error('RKH Show Error', ['rkhno' => $rkhno, 'message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $data = $this->rkhService->getEditPageData($rkhno, $companycode);

            return view('transaction.rencanakerjaharian.rkh-edit', array_merge($data, [
                'title'  => 'Edit Rencana Kerja Harian',
                'navbar' => 'Transaction',
                'nav'    => 'Rencana Kerja Harian',
            ]));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        } catch (\Exception $e) {
            \Log::error('RKH Edit Error', ['rkhno' => $rkhno, 'message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update existing RKH
     */
    public function update(Request $request, $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $userid = Auth::user()->userid;

            \Log::info('RKH Update - Incoming Request', [
                'rkhno' => $rkhno,
                'tanggal' => $request->input('tanggal'),
                'mandor_id' => $request->input('mandor_id'),
                'rows_count' => count($request->input('rows', [])),
            ]);

            // Validate request
            $this->validationService->validateRkhRequest($request);

            \Log::info('RKH Update - Validation Passed');

            $dto = [
                'companycode' => $companycode,
                'userid' => $userid,
                'rkhdate' => $request->input('tanggal'),
                'mandorid' => $request->input('mandor_id'),
                'keterangan' => $request->input('keterangan'),
                'rows' => $request->input('rows', []),
                'workers' => $request->input('workers', []),
                'kendaraan' => $request->input('kendaraan', []),
            ];

            $result = $this->rkhService->updateRkh($rkhno, $dto, $companycode, $userid);

            \Log::info('RKH Update - Service Result', ['success' => $result]);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'RKH berhasil diupdate',
                    'redirect_url' => route('transaction.rencanakerjaharian.show', $rkhno)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate RKH'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('RKH Update - Validation Failed', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->validator->errors()->all()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('RKH Update Error', [
                'rkhno' => $rkhno,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate RKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete RKH
     */
    public function destroy($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Correct parameter order (rkhno first, then companycode)
            $result = $this->rkhService->deleteRkh($rkhno, $companycode);

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            \Log::error('RKH Delete Error', [
                'rkhno' => $rkhno,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus RKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel RKH
     * 
     * @param Request $request
     * @param string $rkhno
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $rkhno)
    {
        try {
            $request->validate([
                'alasan' => 'required|string|min:10|max:500'
            ], [
                'alasan.required' => 'Alasan pembatalan wajib diisi',
                'alasan.min' => 'Alasan pembatalan minimal 10 karakter',
                'alasan.max' => 'Alasan pembatalan maksimal 500 karakter'
            ]);
            
            $companycode = Session::get('companycode');
            $userid = Auth::user()->userid;
            $alasan = $request->input('alasan');
            
            $result = $this->rkhService->cancelRkh($rkhno, $alasan, $companycode, $userid);
            
            return response()->json($result, $result['success'] ? 200 : 400);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('RKH Cancel Error', [
                'rkhno' => $rkhno,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membatalkan RKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cancellation detail
     * 
     * @param string $rkhno
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBatalDetail($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $batalDetail = $this->rkhService->getBatalDetail($rkhno, $companycode);
            
            if (!$batalDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembatalan tidak ditemukan'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $batalDetail
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}