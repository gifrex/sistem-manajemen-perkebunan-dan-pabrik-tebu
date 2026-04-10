<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Repositories\Approval\PanenApprovalRepository;
use App\Services\Approval\PanenApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * PanenApprovalController
 *
 * Handles approval group Panen: Koreksi SJ Panen, dsb.
 */
class PanenApprovalController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(
        PanenApprovalService $service,
        PanenApprovalRepository $repository
    ) {
        $this->service    = $service;
        $this->repository = $repository;
    }

    /**
     * Process panen approval (approve / decline)
     * POST /approval/panen/process
     */
    public function process(Request $request)
    {
        $request->validate([
            'approvalno' => 'required|string',
            'action'     => 'required|in:approve,decline',
            'level'      => 'required|integer|between:1,3',
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();

        $result = $this->service->processApproval(
            $request->approvalno,
            $companycode,
            $request->level,
            $request->action,
            [
                'userid'     => $currentUser->userid,
                'idjabatan'  => $currentUser->idjabatan,
            ]
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Get approval history (JSON)
     * GET /approval/panen/{approvalno}/history
     */
    public function history(string $approvalno)
    {
        $companycode = Session::get('companycode');
        $history     = $this->repository->getApprovalHistory($companycode, $approvalno);

        if (!$history) {
            return response()->json(['success' => false, 'message' => 'Approval tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $history]);
    }
}
