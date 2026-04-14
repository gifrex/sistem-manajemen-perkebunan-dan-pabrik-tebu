<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Services\Approval\AbsenApprovalService;
use App\Repositories\Approval\AbsenApprovalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * AbsenApprovalController
 * 
 * Thin controller - only handles HTTP layer
 * All business logic in AbsenApprovalService
 */
class AbsenApprovalController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(
        AbsenApprovalService $service,
        AbsenApprovalRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Process header approval (approve/decline entire absen)
     * POST /approval/absen/process
     */
    public function process(Request $request)
    {
        $request->validate([
            'absenno' => 'required|string',
            'action' => 'required|in:approve,decline'
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        $result = $this->service->processHeaderApproval(
            $request->absenno,
            $companycode,
            $request->action,
            [
                'userid' => $currentUser->userid,
                'idjabatan' => $currentUser->idjabatan
            ]
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Process individual foto approval
     * POST /approval/absen/foto/process
     */
    public function processFoto(Request $request)
    {
        $request->validate([
            'absenno' => 'required|string',
            'tenagakerjaid' => 'required|string',
            'action' => 'required|in:approve,decline',
            'reason' => 'required_if:action,decline|nullable|string|max:500'
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        $result = $this->service->processFotoApproval(
            $request->absenno,
            $companycode,
            $request->tenagakerjaid,
            $request->action,
            $request->reason,
            [
                'userid' => $currentUser->userid,
                'idjabatan' => $currentUser->idjabatan
            ]
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Get absen approval detail
     * GET /approval/absen/{absenno}/detail
     */
    public function detail(string $absenno)
    {
        $companycode = Session::get('companycode');
        
        $result = $this->service->getApprovalDetail($absenno, $companycode);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return view('approval.absen.detail', [
            'title' => 'Absen Approval Detail',
            'navbar' => 'Approval',
            'nav' => 'Absen Approval',
            'data' => $result['data']
        ]);
    }

    /**
     * Get absen approval history
     * GET /approval/absen/{absenno}/history
     */
    public function history(string $absenno)
    {
        $companycode = Session::get('companycode');
        
        $history = $this->repository->getApprovalHistory($companycode, $absenno);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'Absen tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}