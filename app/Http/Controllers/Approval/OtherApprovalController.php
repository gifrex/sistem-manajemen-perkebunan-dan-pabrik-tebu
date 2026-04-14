<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Services\Approval\OtherApprovalService;
use App\Repositories\Approval\OtherApprovalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * OtherApprovalController
 * 
 * Thin controller - only handles HTTP layer
 * Handles generic approvals: Split/Merge, Purchase Request, Open Rework, etc.
 * All business logic in OtherApprovalService
 */
class OtherApprovalController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(
        OtherApprovalService $service,
        OtherApprovalRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Process other approval (approve/decline)
     * POST /approval/other/process
     */
    public function process(Request $request)
    {
        $request->validate([
            'approvalno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        $result = $this->service->processApproval(
            $request->approvalno,
            $companycode,
            $request->level,
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
     * Get approval detail
     * GET /approval/other/{approvalno}/detail
     */
    public function detail(string $approvalno)
    {
        $companycode = Session::get('companycode');
        
        $result = $this->service->getApprovalDetail($approvalno, $companycode);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return view('approval.other.detail', [
            'title' => 'Approval Detail',
            'navbar' => 'Approval',
            'nav' => 'Other Approval',
            'data' => $result['data']
        ]);
    }

    /**
     * Get approval history
     * GET /approval/other/{approvalno}/history
     */
    public function history(string $approvalno)
    {
        $companycode = Session::get('companycode');
        
        $history = $this->repository->getApprovalHistory($companycode, $approvalno);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'Approval tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}