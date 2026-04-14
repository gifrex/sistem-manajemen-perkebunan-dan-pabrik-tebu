<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Lkh\LkhService;
use App\Services\Transaction\RencanaKerjaHarian\Lkh\LkhValidationService;
use App\Services\Transaction\RencanaKerjaHarian\Lkh\LkhWageCalculationService;
use App\Services\Transaction\RencanaKerjaHarian\Generator\LkhGeneratorService;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * LkhController
 * 
 * Handles LKH management HTTP requests.
 * RULE: Thin controller - only routing, validation, response formatting.
 */
class LkhController extends Controller
{
    protected $lkhService;
    protected $validationService;
    protected $masterDataRepo;

    public function __construct(
        LkhService $lkhService,
        LkhValidationService $validationService,
        MasterDataRepository $masterDataRepo
    ) {
        $this->lkhService = $lkhService;
        $this->validationService = $validationService;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Get LKH data for specific RKH
     */
    public function getLKHData($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->lkhService->getLkhListForRkh($rkhno, $companycode);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH data for RKH {$rkhno}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data LKH: ' . $e->getMessage(),
                'lkh_data' => [],
                'total_lkh' => 0
            ], 500);
        }
    }

    public function showLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $pageData = $this->lkhService->getShowLkhPageData($lkhno, $companycode);

            if (!$pageData) {
                return redirect()->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }

            if ($pageData['lkhData']->jenistenagakerja == 2) {
                $pageData['boronganRate'] = $this->masterDataRepo->getBoronganRate(
                    $companycode,
                    $pageData['lkhData']->activitycode,
                    $pageData['lkhData']->lkhdate
                );
            }

            $activityType = $pageData['activity_type'];

            if ($activityType === 'bsm') {
                return view('transaction.rencanakerjaharian.lkh-report-bsm', array_merge([
                    'title'  => 'Laporan Kegiatan Harian (LKH) - Cek BSM',
                    'navbar' => 'Input',
                    'nav'    => 'Rencana Kerja Harian',
                ], $pageData));
            }

            if ($activityType === 'panen') {
                return view('transaction.rencanakerjaharian.lkh-report-panen', array_merge([
                    'title'  => 'Laporan Kegiatan Harian (LKH) - Panen',
                    'navbar' => 'Input',
                    'nav'    => 'Rencana Kerja Harian',
                ], $pageData));
            }

            return view('transaction.rencanakerjaharian.lkh-report', array_merge([
                'title'  => 'Laporan Kegiatan Harian (LKH)',
                'navbar' => 'Input',
                'nav'    => 'Rencana Kerja Harian',
            ], $pageData));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        } catch (\Exception $e) {
            \Log::error("Error showing LKH: " . $e->getMessage());
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan LKH: ' . $e->getMessage());
        }
    }

    public function editLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $pageData = $this->lkhService->getEditLkhPageData($lkhno, $companycode);

            if (!$pageData) {
                return redirect()->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }

            return view('transaction.rencanakerjaharian.lkh-edit-v2', array_merge([
                'title'  => 'Edit LKH',
                'navbar' => 'Input',
                'nav'    => 'Rencana Kerja Harian',
            ], $pageData));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        } catch (\Exception $e) {
            \Log::error("Error editing LKH: " . $e->getMessage());
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update LKH record
     */
    public function updateLKH(Request $request, $lkhno)
    {
        try {
            // Validate
            $this->validationService->validateLkhUpdateRequest($request);

            $dto = [
                'keterangan'       => $request->input('keterangan'),
                'plots'            => $request->input('plots'),
                'workers'          => $request->input('workers'),
                'materials'        => $request->input('materials'),
                'is_blok_activity' => $request->boolean('is_blok_activity'),
            ];

            // Update LKH
            $companycode = Session::get('companycode');
            $this->lkhService->updateLkh($lkhno, $dto, $companycode);

            return response()->json([
                'success' => true,
                'message' => 'LKH berhasil diupdate',
                'lkhno' => $lkhno
            ]);

        } catch (\Exception $e) {
            \Log::error("Error updating LKH: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit LKH for approval
     */
    public function submitLKH(Request $request)
    {
        $request->validate(['lkhno' => 'required|string']);

        try {
            $companycode = Session::get('companycode');
            $lkhno = $request->lkhno;

            $validation = $this->validationService->validateCanSubmit($lkhno, $companycode);
            
            if (!$validation['success']) {
                return response()->json($validation);
            }

            $result = $this->lkhService->submitLkh($lkhno, $companycode);
            
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error submitting LKH: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate LKH manually
     */
    public function manualGenerateLkh(Request $request, $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $lkhGenerator = new LkhGeneratorService();
            $result = $lkhGenerator->generateLkhFromRkh($rkhno, $companycode);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Manual generate LKH error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate wages for LKH edit
     */
    public function recalculateWages(Request $request)
    {
        try {
            $validated = $request->validate([
                'companycode' => 'required|string|max:4',
                'lkhno' => 'required|string',
                'activitycode' => 'required|string',
                'lkhdate' => 'required|date',
                'jenistenagakerja' => 'required|integer',
                'workers' => 'required_if:jenistenagakerja,1|array',
                'workers.*.tenagakerjaid' => 'required_with:workers|string',
                'workers.*.jammasuk' => 'required_with:workers',
                'workers.*.jamselesai' => 'required_with:workers',
                'workers.*.overtimehours' => 'nullable|numeric',
                'plots' => 'required_if:jenistenagakerja,2|array',
                'plots.*.luashasil' => 'required_with:plots|numeric',
            ]);

            $wageService = new LkhWageCalculationService($this->masterDataRepo);

            // TENAGA HARIAN
            if ($validated['jenistenagakerja'] == 1) {
                $results = $wageService->calculateHarianWages(
                    $validated['workers'],
                    $validated['companycode'],
                    $validated['activitycode'],
                    $validated['lkhdate']
                );

                if (isset($results['error'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $results['error']
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'type' => 'harian',
                    'wages' => $results,
                ]);
            }

            // TENAGA BORONGAN
            if ($validated['jenistenagakerja'] == 2) {
                $result = $wageService->calculateBoronganWage(
                    $validated['plots'],
                    $validated['companycode'],
                    $validated['activitycode'],
                    $validated['lkhdate']
                );

                if (isset($result['error'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['error']
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'type' => 'borongan',
                    'total_luas' => $result['total_luas'],
                    'rate_per_ha' => $result['rate_per_ha'],
                    'total_upah' => $result['total_upah'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid jenistenagakerja'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('LKH recalculate wages error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $pageData = $this->lkhService->getShowLkhPageData($lkhno, $companycode);

            if (!$pageData) {
                return back()->with('error', 'Data LKH tidak ditemukan');
            }

            if ($pageData['lkhData']->jenistenagakerja == 2) {
                $pageData['boronganRate'] = $this->masterDataRepo->getBoronganRate(
                    $companycode,
                    $pageData['lkhData']->activitycode,
                    $pageData['lkhData']->lkhdate
                );
            }

            $activityType = $pageData['activity_type'];

            if ($activityType === 'bsm') {
                return view('transaction.rencanakerjaharian.lkh-print.lkh-print-bsm', array_merge([
                    'title' => 'Print LKH BSM - ' . $lkhno,
                ], $pageData));
            }

            if ($activityType === 'panen') {
                return view('transaction.rencanakerjaharian.lkh-print.lkh-print-panen', array_merge([
                    'title' => 'Print LKH Panen - ' . $lkhno,
                ], $pageData));
            }

            return view('transaction.rencanakerjaharian.lkh-print.lkh-print', array_merge([
                'title' => 'Print LKH - ' . $lkhno,
            ], $pageData));

        } catch (\Exception $e) {
            \Log::error('LKH Print Error', ['lkhno' => $lkhno, 'message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}