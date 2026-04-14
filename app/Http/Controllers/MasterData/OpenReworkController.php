<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\MasterData\OpenRework\OpenReworkService;
use App\Repositories\MasterData\OpenRework\OpenReworkRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * OpenReworkController
 * 
 * Manages rework approval requests for plots based on LKH
 */
class OpenReworkController extends Controller
{
    protected $service;
    protected $repo;

    public function __construct(OpenReworkService $service, OpenReworkRepository $repo)
    {
        $this->service = $service;
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $perPage = (int) $request->input('perPage', 10);
        
        $filters = [
            'search' => $request->input('search'),
        ];
        
        $data = $this->service->getIndexPageData($filters, $perPage, $companycode);
        
        return view('masterdata.open-rework.index', [
            'title' => 'Open Rework',
            'navbar' => 'Master Data',
            'nav' => 'Open Rework',
            'requests' => $data['requests'],
            'activities' => $data['activities'],
            'perPage' => $perPage,
            'search' => $filters['search'],
        ]);
    }

    /**
     * Get LKH list by activity and date range (AJAX)
     */
    public function getLkhList(Request $request)
    {
        $request->validate([
            'activitycode' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        try {
            $companycode = Session::get('companycode');
            
            $lkhList = $this->service->getLkhByActivityAndDateRange(
                $companycode,
                $request->activitycode,
                $request->start_date,
                $request->end_date
            );
            
            return response()->json([
                'success' => true,
                'data' => $lkhList
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting LKH list: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get LKH detail plots (AJAX)
     */
    public function getLkhDetailPlots($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $plots = $this->service->getLkhDetailPlots($companycode, $lkhno);
            
            return response()->json([
                'success' => true,
                'data' => $plots
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting LKH detail plots: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail plot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store rework request
     */
    public function store(Request $request)
    {
        $request->validate([
            'lkhno' => 'required|string|exists:lkhhdr,lkhno',
            'plots' => 'required|array|min:1',
            'plots.*' => 'required|string',
            'activities' => 'required|array|min:1',
            'activities.*' => 'required|string',
            'reason' => 'required|string|max:500'
        ]);
        
        try {
            $companycode = Session::get('companycode');
            $userid = Auth::user()->userid;
            
            $dto = [
                'lkhno' => $request->lkhno,
                'plots' => $request->plots,
                'activities' => $request->activities,
                'reason' => $request->reason
            ];
            
            $result = $this->service->createReworkRequest($dto, $companycode, $userid);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Rework request failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat rework request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval detail (AJAX)
     */
    public function getApprovalDetail($approvalno)
    {
        try {
            $companycode = Session::get('companycode');
            $approval = $this->repo->getApprovalDetail($companycode, $approvalno);
            
            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval tidak ditemukan'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $approval
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval'
            ], 500);
        }
    }
}