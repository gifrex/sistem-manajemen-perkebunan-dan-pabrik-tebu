<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Repositories\Approval\AbsenApprovalRepository;
use App\Repositories\Approval\LkhApprovalRepository;
use App\Repositories\Approval\OtherApprovalRepository;
use App\Repositories\Approval\RkhApprovalRepository;
use App\Repositories\Approval\UpahMingguanApprovalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * ApprovalDashboardController
 *
 * Unified dashboard untuk semua jenis approval (RKH, LKH, Others)
 * COPIED FROM: ApprovalController::index()
 */
class ApprovalDashboardController extends Controller
{
    protected $rkhRepository;
    protected $lkhRepository;
    protected $otherRepository;
    protected $absenRepository;
    protected $upahRepository;

    public function __construct(
        RkhApprovalRepository $rkhRepository,
        LkhApprovalRepository $lkhRepository,
        OtherApprovalRepository $otherRepository,
        AbsenApprovalRepository $absenRepository,
        UpahMingguanApprovalRepository $upahRepository
    ) {
        $this->rkhRepository = $rkhRepository;
        $this->lkhRepository = $lkhRepository;
        $this->otherRepository = $otherRepository;
        $this->absenRepository = $absenRepository;
        $this->upahRepository = $upahRepository;
    }

    /**
     * Show approval dashboard with date filter
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $currentUser = Auth::user();

        if (!$this->validateUserForApproval($currentUser)) {
            return redirect()->route('home')
                ->with('error', 'Anda tidak memiliki akses untuk approval');
        }

        // Default: all_date = true
        $filterDate = $request->input('filter_date');
        $allDate = $request->input('all_date', true); // <-- default true

        $filters = [
            'date' => $filterDate,
            'all_date' => $allDate
        ];

        $pendingRKH = $this->getPendingRKHWithDetails($companycode, $currentUser, $filters);
        $pendingLKH = $this->getPendingLKHWithDetails($companycode, $currentUser, $filters);
        $pendingAbsen = $this->getPendingAbsenWithDetails($companycode, $currentUser, $filters);
        $pendingOther = $this->getPendingOtherWithDetails($companycode, $currentUser, $filters);
        $othersDetail = $this->setOtherDetail($pendingOther);
        $pendingUpah = $this->getPendingUpahWithDetails($companycode, $currentUser, $filters);

        // Load activity groups user punya akses
        $userActivityGroups = DB::table('useractivity as ua')
            ->join('activitygroup as ag', 'ua.activitygroup', '=', 'ag.activitygroup')
            ->where('ua.userid', $currentUser->userid)
            ->where('ua.companycode', $companycode)
            ->where('ua.isactive', 1)
            ->select('ag.activitygroup', 'ag.groupname')
            ->orderBy('ag.activitygroup')
            ->get();

        return view('approval.index', [
            'title' => 'Approval Center',
            'navbar' => 'Input',
            'nav' => 'Approval',
            'pendingRKH' => $pendingRKH,
            'pendingLKH' => $pendingLKH,
            'pendingOther' => $pendingOther,
            'pendingAbsen' => $pendingAbsen,
            'userInfo' => $this->getUserInfo($currentUser),
            'filterDate' => $filterDate,
            'allDate' => $allDate,
            'otherDetail' => $othersDetail,
            'userActivityGroups' => $userActivityGroups, // <-- tambah ini
        ]);
    }

    /**
     * Get pending RKH approvals with additional details
     *
     * @param string $companycode
     * @param object $currentUser
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private function getPendingRKHWithDetails($companycode, $currentUser, array $filters)
    {
        $pendingRKH = $this->rkhRepository->getPendingApprovals(
            $companycode,
            $currentUser->idjabatan,
            $currentUser->userid,  // tambah userid
            $filters
        );

        return $pendingRKH->map(function ($rkh) use ($companycode) {
            $rkh->activities_list = $this->rkhRepository->getActivitiesSummary($companycode, $rkh->rkhno);
            $rkh->has_material = $this->rkhRepository->hasMaterial($companycode, $rkh->rkhno);
            $rkh->has_kendaraan = $this->rkhRepository->hasKendaraan($companycode, $rkh->rkhno);
            return $rkh;
        });
    }

    /**
     * Get pending LKH approvals with additional details
     *
     * @param string $companycode
     * @param object $currentUser
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private function getPendingLKHWithDetails($companycode, $currentUser, array $filters)
    {
        $pendingLKH = $this->lkhRepository->getPendingApprovals(
            $companycode,
            $currentUser->idjabatan,
            $currentUser->userid,  // tambah userid
            $filters
        );

        return $pendingLKH->map(function ($lkh) use ($companycode) {
            $lkh->has_material = $this->lkhRepository->hasMaterial($companycode, $lkh->lkhno);
            $lkh->has_kendaraan = $this->lkhRepository->hasKendaraan($companycode, $lkh->lkhno);
            return $lkh;
        });
    }

    /**
     * Get pending other approvals with additional details
     *
     * @param string $companycode
     * @param object $currentUser
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private function getPendingOtherWithDetails($companycode, $currentUser, array $filters)
    {
        $pendingOther = $this->otherRepository->getPendingApprovals(
            $companycode,
            $currentUser->idjabatan,
            $filters
        );

        // Enrich with decoded JSON and real batch areas
        return $pendingOther->map(function ($approval) use ($companycode) {
            // Decode JSON fields for Split/Merge
            if ($approval->sourceplots) {
                $approval->sourceplots_array = json_decode($approval->sourceplots, true);
            }
            if ($approval->resultplots) {
                $approval->resultplots_array = json_decode($approval->resultplots, true);
            }
            if ($approval->sourcebatches) {
                $approval->sourcebatches_array = json_decode($approval->sourcebatches, true);

                // Fetch REAL batch area dari database
                $batchAreas = [];
                foreach ($approval->sourcebatches_array as $batchno) {
                    $batch = DB::table('batch')
                        ->where('companycode', $companycode)
                        ->where('batchno', $batchno)
                        ->select('plot', 'batcharea')
                        ->first();

                    if ($batch) {
                        $batchAreas[$batch->plot] = $batch->batcharea;
                    }
                }
                $approval->real_batch_areas = $batchAreas;
            }
            if ($approval->resultbatches) {
                $approval->resultbatches_array = json_decode($approval->resultbatches, true);
            }
            if ($approval->areamap) {
                $approval->areamap_array = json_decode($approval->areamap, true);
            }

            // Decode JSON for Open Rework
            if ($approval->rework_plots) {
                $approval->plots_array = json_decode($approval->rework_plots, true);
            }
            if ($approval->rework_activities) {
                $approval->activities_array = json_decode($approval->rework_activities, true);
            }

            return $approval;
        });
    }

    /**
     * Validate user for approval
     *
     * @param object $currentUser
     * @return bool
     */
    private function validateUserForApproval($currentUser): bool
    {
        return $currentUser && $currentUser->idjabatan;
    }

    /**
     * Get user info with jabatan
     *
     * @param object $currentUser
     * @return array
     */
    private function getUserInfo($currentUser): array
    {
        $jabatan = DB::table('jabatan')
            ->where('idjabatan', $currentUser->idjabatan)
            ->first();

        return [
            'userid' => $currentUser->userid,
            'name' => $currentUser->name,
            'idjabatan' => $currentUser->idjabatan,
            'jabatan_name' => $jabatan ? $jabatan->namajabatan : 'Unknown'
        ];
    }

    /**
     * Get pending absen approvals with additional details
     *
     * @param string $companycode
     * @param object $currentUser
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private function getPendingAbsenWithDetails($companycode, $currentUser, array $filters)
    {
        return $this->absenRepository->getPendingApprovals(
            $companycode,
            $currentUser->idjabatan,
            $filters
        );
    }

    private function getPendingUpahWithDetails(string $companycode, object $currentUser, array $filters)
    {
        $idjabatan = $currentUser->idjabatan;

        $query = DB::table('approvaltransaction as at')
            ->join('pembayaranupahhdr as p', function ($j) {
                $j->on('p.transno', '=', 'at.transactionnumber')
                    ->on('p.companycode', '=', 'at.companycode');
            })
            ->leftJoin('user as u', function ($j) {
                $j->on('u.userid', '=', 'p.mandoruserid')
                    ->on('u.companycode', '=', 'p.companycode');
            })
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->whereIn('at.approvalcategoryid', function ($q) use ($companycode) {
                $q->select('id')
                    ->from('approval')
                    ->where('companycode', $companycode)
                    ->where('category', 'Approval Pembayaran Upah Mingguan');
            })
            ->where('at.companycode', $companycode)
            ->whereNull('at.approvalstatus')
            ->where(function ($q) use ($idjabatan) {
                $q->where(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval1idjabatan', $idjabatan)
                        ->whereNull('at.approval1flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval2idjabatan', $idjabatan)
                        ->where('at.approval1flag', '1')
                        ->whereNull('at.approval2flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval3idjabatan', $idjabatan)
                        ->where('at.approval2flag', '1')
                        ->whereNull('at.approval3flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval4idjabatan', $idjabatan)
                        ->where('at.approval3flag', '1')
                        ->whereNull('at.approval4flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval5idjabatan', $idjabatan)
                        ->where('at.approval4flag', '1')
                        ->whereNull('at.approval5flag');
                });
            })
            ->when(!($filters['all_date'] ?? true) && !empty($filters['date']), function ($q) use ($filters) {
                $q->whereDate('p.generatedate', $filters['date']);
            })
            ->select(
                'at.approvalno',
                'at.transactionnumber as transno',
                'at.jumlahapproval',
                'at.approvalstatus',
                'at.approval1idjabatan',
                'at.approval1flag',
                'at.approval2idjabatan',
                'at.approval2flag',
                'at.approval3idjabatan',
                'at.approval3flag',
                'at.approval4idjabatan',
                'at.approval4flag',
                'at.approval5idjabatan',
                'at.approval5flag',
                'p.startdate',
                'p.enddate',
                'p.grandtotal',
                'p.jenistenagakerja',
                'p.generatedate',
                'ac.activityname',
                'u.name as mandorname',
                DB::raw("
                    CASE
                        WHEN at.approval1idjabatan = {$idjabatan} AND at.approval1flag IS NULL THEN 1
                        WHEN at.approval2idjabatan = {$idjabatan} AND at.approval1flag = '1' AND at.approval2flag IS NULL THEN 2
                        WHEN at.approval3idjabatan = {$idjabatan} AND at.approval2flag = '1' AND at.approval3flag IS NULL THEN 3
                        WHEN at.approval4idjabatan = {$idjabatan} AND at.approval3flag = '1' AND at.approval4flag IS NULL THEN 4
                        WHEN at.approval5idjabatan = {$idjabatan} AND at.approval4flag = '1' AND at.approval5flag IS NULL THEN 5
                        ELSE NULL
                    END as approval_level
                ")
            )
            ->orderByDesc('p.generatedate')
            ->get();

        if ($query->isNotEmpty()) {
            $transNos = $query->pluck('transno')->toArray();
            $workerCounts = DB::table('pembayaranupahlst')
                ->whereIn('transno', $transNos)
                ->where('companycode', $companycode)
                ->groupBy('transno')
                ->select('transno', DB::raw('COUNT(DISTINCT tenagakerjaid) as totalworkers'))
                ->pluck('totalworkers', 'transno');

            foreach ($query as $item) {
                $item->totalworkers = $workerCounts[$item->transno] ?? 0;
                $item->jenis_label = $item->jenistenagakerja == 1 ? 'Harian' : 'Borongan';
            }
        }

        return $query;
    }

    private function setOtherDetail($otherDetail)
    {
        $detail = array();
        if (count($otherDetail) > 0) {
            foreach ($otherDetail as $item) {
                if ($item->category == "Use Material" OR $item->category == "Use Koreksi" OR $item->category == "Retur Koreksi") {
                    $materialDetail = $this->otherRepository->getApprovalUseMaterialDetail(
                        $item->companycode,
                        $item->approvalno
                    );

                    // HANYA SIMPAN JIKA ADA ISI
                    if (!empty($materialDetail)) {
                        $detail[$item->approvalno] = $materialDetail;
                    }
                }
            }
        }
        return $detail;
    }
}
