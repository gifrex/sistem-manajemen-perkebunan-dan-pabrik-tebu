<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Repositories\Approval\UpahMingguanApprovalRepository;
use App\Services\Approval\UpahMingguanApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UpahMingguanApprovalController extends Controller
{
    public function __construct(
        protected UpahMingguanApprovalService $service,
        protected UpahMingguanApprovalRepository $repository
    ) {
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
        $user = Auth::user();

        $result = $this->service->processApproval(
            $request->transno,
            $companycode,
            (int) $request->level,
            $request->action,
            ['userid' => $user->userid, 'idjabatan' => $user->idjabatan]
        );

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Get approval detail (JSON)
     * GET /approval/upah-mingguan/{transno}/detail
     */
    public function detail(string $transno)
    {
        $companycode = Session::get('companycode');
        $result = $this->service->getApprovalDetail($transno, $companycode);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * List pending approvals for current user's jabatan
     * GET /approval/upah-mingguan
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $user = Auth::user();
        $idjabatan = $user->idjabatan;

        // Find which level this jabatan handles
        $pending = DB::table('approvaltransaction as at')
            ->join('pembayaranupahhdr as p', fn($j) =>
                $j->on('p.transno', '=', 'at.transactionnumber')
                    ->on('p.companycode', '=', 'at.companycode'))
            ->leftJoin('user as u', fn($j) =>
                $j->on('u.userid', '=', 'p.mandoruserid')
                    ->on('u.companycode', '=', 'p.companycode'))
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->where('at.companycode', $companycode)
            ->where('at.approvalstatus', null) // still pending
            ->where(function ($q) use ($idjabatan) {
                // Level 1
                $q->where(fn($q2) =>
                    $q2->where('at.approval1idjabatan', $idjabatan)
                        ->whereNull('at.approval1flag'))
                    // Level 2
                    ->orWhere(fn($q2) =>
                        $q2->where('at.approval2idjabatan', $idjabatan)
                            ->where('at.approval1flag', '1')
                            ->whereNull('at.approval2flag'))
                    // Level 3
                    ->orWhere(fn($q2) =>
                        $q2->where('at.approval3idjabatan', $idjabatan)
                            ->where('at.approval2flag', '1')
                            ->whereNull('at.approval3flag'))
                    // Level 4
                    ->orWhere(fn($q2) =>
                        $q2->where('at.approval4idjabatan', $idjabatan)
                            ->where('at.approval3flag', '1')
                            ->whereNull('at.approval4flag'))
                    // Level 5
                    ->orWhere(fn($q2) =>
                        $q2->where('at.approval5idjabatan', $idjabatan)
                            ->where('at.approval4flag', '1')
                            ->whereNull('at.approval5flag'));
            })
            ->select(
                'at.approvalno',
                'at.transactionnumber as transno',
                'at.jumlahapproval',
                'at.approvalstatus',
                'at.approval1flag',
                'at.approval2flag',
                'at.approval3flag',
                'at.approval4flag',
                'at.approval5flag',
                'at.approval1idjabatan',
                'at.approval2idjabatan',
                'at.approval3idjabatan',
                'at.approval4idjabatan',
                'at.approval5idjabatan',
                'p.startdate',
                'p.enddate',
                'p.grandtotal',
                'p.jenistenagakerja',
                'ac.activityname',
                'u.name as mandorname'
            )
            ->orderByDesc('at.createdat')
            ->paginate(15);

        // Resolve which level is actionable for current user
        foreach ($pending as $row) {
            $row->actionable_level = $this->resolveActionableLevel($row, $idjabatan);
        }

        return view('approval.upahmingguan.index', [
            'title' => 'Approval Pembayaran Upah Mingguan',
            'navbar' => 'Approval',
            'nav' => 'Upah Mingguan',
            'pending' => $pending,
        ]);
    }

    private function resolveActionableLevel(object $row, int $idjabatan): ?int
    {
        for ($i = 1; $i <= ($row->jumlahapproval ?? 5); $i++) {
            $jabCol = "approval{$i}idjabatan";
            $flagCol = "approval{$i}flag";
            if (($row->$jabCol ?? null) == $idjabatan && ($row->$flagCol ?? null) === null) {
                // Make sure previous level is approved
                if ($i === 1)
                    return 1;
                $prev = "approval" . ($i - 1) . "flag";
                if (($row->$prev ?? null) === '1')
                    return $i;
            }
        }
        return null;
    }
}
