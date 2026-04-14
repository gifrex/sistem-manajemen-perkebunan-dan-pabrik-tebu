<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Services\Approval\LkhApprovalService;
use App\Repositories\Approval\LkhApprovalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * LkhApprovalController
 * 
 * Thin controller - only handles HTTP layer
 * All business logic in LkhApprovalService
 */
class LkhApprovalController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(
        LkhApprovalService $service,
        LkhApprovalRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Process LKH approval (approve/decline)
     * POST /approval/lkh/process
     */
    public function process(Request $request)
    {
        $request->validate([
            'lkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        $result = $this->service->processApproval(
            $request->lkhno,
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
     * Get LKH approval detail
     * GET /approval/lkh/{lkhno}/detail
     */
    public function detail(string $lkhno)
    {
        $companycode = Session::get('companycode');
        
        $result = $this->service->getApprovalDetail($lkhno, $companycode);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return view('approval.lkh.detail', [
            'title' => 'LKH Approval Detail',
            'navbar' => 'Approval',
            'nav' => 'LKH Approval',
            'data' => $result['data']
        ]);
    }

    /**
     * Get LKH approval history
     * GET /approval/lkh/{lkhno}/history
     */
    public function history(string $lkhno)
    {
        $companycode = Session::get('companycode');
        
        $history = $this->repository->getApprovalHistory($companycode, $lkhno);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'LKH tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}