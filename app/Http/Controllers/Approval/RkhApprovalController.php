<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Services\Approval\RkhApprovalService;
use App\Repositories\Approval\RkhApprovalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * RkhApprovalController
 * 
 * Thin controller - only handles HTTP layer
 * All business logic in RkhApprovalService
 */
class RkhApprovalController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(
        RkhApprovalService $service,
        RkhApprovalRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Process RKH approval (approve/decline)
     * POST /approval/rkh/process
     */
    public function process(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        $result = $this->service->processApproval(
            $request->rkhno,
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
     * Get RKH approval detail
     * GET /approval/rkh/{rkhno}/detail
     */
    public function detail(string $rkhno)
    {
        $companycode = Session::get('companycode');
        
        $result = $this->service->getApprovalDetail($rkhno, $companycode);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return view('approval.rkh.detail', [
            'title' => 'RKH Approval Detail',
            'navbar' => 'Approval',
            'nav' => 'RKH Approval',
            'data' => $result['data']
        ]);
    }

    /**
     * Get RKH approval history
     * GET /approval/rkh/{rkhno}/history
     */
    public function history(string $rkhno)
    {
        $companycode = Session::get('companycode');
        
        $history = $this->repository->getApprovalHistory($companycode, $rkhno);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'RKH tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}