<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Repositories\Approval\UpahMingguanApprovalRepository;
use App\Services\Approval\UpahMingguanApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * UpahMingguanApprovalController
 *
 * Thin controller - only handles HTTP layer
 * All business logic in UpahMingguanApprovalService
 */
class UpahMingguanApprovalController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(
        UpahMingguanApprovalService $service,
        UpahMingguanApprovalRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Process approve / decline
     * POST /approval/upah-mingguan/process
     */
    public function process(Request $request)
    {
        $request->validate([
            'transno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,5',
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();

        $result = $this->service->processApproval(
            $request->transno,
            $companycode,
            (int) $request->level,
            $request->action,
            [
                'userid' => $currentUser->userid,
                'idjabatan' => $currentUser->idjabatan,
            ]
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Process batch approve / decline (multiple transno, same mandor)
     * POST /approval/upah-mingguan/process-group
     */
    public function processGroup(Request $request)
    {
        $request->validate([
            'transno_list' => 'required|array|min:1',
            'transno_list.*' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,5',
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();

        $result = $this->service->processApprovalBatch(
            $request->transno_list,
            $companycode,
            (int) $request->level,
            $request->action,
            [
                'userid' => $currentUser->userid,
                'idjabatan' => $currentUser->idjabatan,
            ]
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Get grouped approval detail (multiple transno per mandor)
     * GET /approval/upah-mingguan/detail-group?transno_list=TRX1,TRX2
     * Returns JSON for modal
     */
    public function detailGroup(Request $request)
    {
        $request->validate(['transno_list' => 'required|string']);

        $companycode = Session::get('companycode');
        $transnoList = array_filter(explode(',', $request->transno_list));

        $result = $this->service->getApprovalDetailGroup(array_values($transnoList), $companycode);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 404);
        }

        return response()->json($result);
    }

    /**
     * Get approval detail
     * GET /approval/upah-mingguan/{transno}/detail
     * Supports JSON (for modal) when Accept: application/json
     */
    public function detail(string $transno)
    {
        $companycode = Session::get('companycode');

        $result = $this->service->getApprovalDetail($transno, $companycode);

        if (!$result['success']) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 404);
            }
            return back()->with('error', $result['message']);
        }

        if (request()->wantsJson()) {
            $data = $result['data'];
            $header = $data['header'];
            $history = $data['history'];
            $workers = $data['workers'];
            $status = $data['status'];

            // Format workers for JSON
            $workersFormatted = $workers->map(function ($w) {
                return [
                    'tenagakerjaid' => $w->tenagakerjaid,
                    'worker_name' => $w->worker_name ?? '-',
                    'totalupah' => $w->totalupah ?? 0,
                    'totalupah_fmt' => \Illuminate\Support\Number::currency($w->totalupah ?? 0, 'IDR', 'id'),
                ];
            });

            // Format history for JSON
            $historyData = null;
            if ($history) {
                $levels = [];
                for ($i = 1; $i <= ($history->jumlahapproval ?? 0); $i++) {
                    $levels[] = [
                        'level' => $i,
                        'jabatan' => $history->{"jabatan{$i}_name"} ?? '-',
                        'user' => $history->{"approval{$i}_user_name"} ?? null,
                        'flag' => $history->{"approval{$i}flag"} ?? null,
                        'date' => $history->{"approval{$i}date"} ?? null,
                    ];
                }
                $historyData = [
                    'jumlahapproval' => $history->jumlahapproval,
                    'levels' => $levels,
                ];
            }

            return response()->json([
                'success' => true,
                'trx' => $data['trx'],
                'header' => $header,
                'history' => $historyData,
                'workers' => $workersFormatted,
                'status' => $status,
            ]);
        }

        return view('approval.upahmingguan.detail', [
            'title' => 'Upah Mingguan Approval Detail',
            'navbar' => 'Approval',
            'nav' => 'Upah Mingguan Approval',
            'data' => $result['data'],
        ]);
    }

    /**
     * Get approval history (JSON)
     * GET /approval/upah-mingguan/{transno}/history
     */
    public function history(string $transno)
    {
        $companycode = Session::get('companycode');

        $history = $this->repository->getApprovalHistory($companycode, $transno);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'Data approval tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
