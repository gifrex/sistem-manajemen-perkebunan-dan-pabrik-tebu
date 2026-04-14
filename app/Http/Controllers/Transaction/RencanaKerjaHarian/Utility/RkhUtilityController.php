<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian\Utility;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\Utility\RkhUtilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * RkhUtilityController
 * 
 * Handles utility HTTP requests.
 * RULE: Thin controller - only routing, validation, response.
 */
class RkhUtilityController extends Controller
{
    protected $utilityService;

    public function __construct(RkhUtilityService $utilityService)
    {
        $this->utilityService = $utilityService;
    }

    /**
     * Load attendance by date
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadAbsenByDate(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            $mandorId = $request->query('mandor_id');
            $companycode = Session::get('companycode');
            
            $result = $this->utilityService->getAttendancePayload($companycode, $date, $mandorId);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error loading absen by date: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data absen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get plot info
     * 
     * @param string $plot
     * @param string $activitycode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlotInfo(Request $request, $plot, $activitycode)
    {
        try {
            $companycode = Session::get('companycode');
            $rkhdate = $request->query('rkhdate');

            $result = $this->utilityService->getPlotInfo($companycode, $plot, $activitycode, $rkhdate);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error getting plot info for {$plot}/{$activitycode}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat info plot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check outstanding RKH
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOutstandingRKH(Request $request)
    {
        $request->validate([
            'mandor_id' => 'required|string',
            'date' => 'required|date'
        ]);

        try {
            $companycode = Session::get('companycode');
            $mandorId = $request->mandor_id;
            $date = $request->date;

            // VALIDATION 1: Date Range
            $today = date('Y-m-d');
            $maxDate = date('Y-m-d', strtotime('+7 days'));
            
            if ($date < $today) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat membuat RKH untuk tanggal yang sudah lewat.'
                ], 400);
            }
            
            if ($date > $maxDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat membuat RKH lebih dari 7 hari ke depan.'
                ], 400);
            }

            // VALIDATION 2: Check Outstanding
            $outstandingResult = $this->utilityService->checkOutstandingRkh($companycode, $mandorId);
            
            if ($outstandingResult['hasOutstanding']) {
                return response()->json($outstandingResult);
            }

            // VALIDATION 3: Check Duplicate for Same Date
            $duplicateResult = $this->utilityService->checkDuplicateRkh($companycode, $mandorId, $date);
            
            if ($duplicateResult['exists']) {
                return response()->json([
                    'success' => false,
                    'hasDuplicate' => true,
                    'message' => 'RKH untuk mandor ini pada tanggal tersebut sudah ada',
                    'details' => [
                        'rkhno' => $duplicateResult['rkhno'],
                        'rkhdate' => $duplicateResult['rkhdate'],
                        'status' => $duplicateResult['status']
                    ]
                ]);
            }

            // All validations passed
            return response()->json([
                'success' => true,
                'hasOutstanding' => false,
                'hasDuplicate' => false,
                'message' => 'Validasi berhasil, dapat melanjutkan pembuatan RKH'
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error checking outstanding RKH: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get surat jalan list
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuratJalan(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $plot = $request->query('plot');
            $subkontraktorId = $request->query('subkontraktor_id');
            $lkhno = $request->query('lkhno');
            
            if (!$plot || !$subkontraktorId || !$lkhno) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap'
                ], 400);
            }
            
            $result = $this->utilityService->getSuratJalanPayload($companycode, $plot, $subkontraktorId, $lkhno);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error getting surat jalan list: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data surat jalan: ' . $e->getMessage()
            ], 500);
        }
    }
}