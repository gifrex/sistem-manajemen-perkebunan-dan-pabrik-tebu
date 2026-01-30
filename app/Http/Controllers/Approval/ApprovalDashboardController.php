<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Repositories\Approval\RkhApprovalRepository;
use App\Repositories\Approval\LkhApprovalRepository;
use App\Repositories\Approval\OtherApprovalRepository;
use App\Repositories\Approval\AbsenApprovalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

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

    public function __construct(
        RkhApprovalRepository $rkhRepository,
        LkhApprovalRepository $lkhRepository,
        OtherApprovalRepository $otherRepository,
        AbsenApprovalRepository $absenRepository
    ) {
        $this->rkhRepository = $rkhRepository;
        $this->lkhRepository = $lkhRepository;
        $this->otherRepository = $otherRepository;
        $this->absenRepository = $absenRepository;
    }

    /**
     * Show approval dashboard with date filter
     * GET /approval
     *
     * COPIED FROM: ApprovalController::index()
     * Logic 100% sama
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $currentUser = Auth::user();

        // Validate user for approval
        if (!$this->validateUserForApproval($currentUser)) {
            return redirect()->route('home')
                ->with('error', 'Anda tidak memiliki akses untuk approval');
        }

        // Get filter parameters
        $filterDate = $request->input('filter_date');
        $allDate = $request->input('all_date', false);

        // Build filters array
        $filters = [
            'date' => $filterDate,
            'all_date' => $allDate
        ];

        // Get pending approvals from all repositories
        $pendingRKH = $this->getPendingRKHWithDetails($companycode, $currentUser, $filters);
        $pendingLKH = $this->getPendingLKHWithDetails($companycode, $currentUser, $filters);
        $pendingAbsen = $this->getPendingAbsenWithDetails($companycode, $currentUser, $filters);

        $pendingOther = $this->getPendingOtherWithDetails($companycode, $currentUser, $filters);

        $othersDetail = $this->setOtherDetail($pendingOther);


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
            'otherDetail'=>$othersDetail
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
            $filters
        );

        // Enrich with additional details (activities, material, kendaraan)
        return $pendingRKH->map(function($rkh) use ($companycode) {
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
            $filters
        );

        // Enrich with additional details
        return $pendingLKH->map(function($lkh) use ($companycode) {
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
        return $pendingOther->map(function($approval) use ($companycode) {
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

    private function setOtherDetail( $otherDetail )
    {
      $detail = array();
      if( count($otherDetail) > 0 ){
          foreach( $otherDetail as $item ){

              if ( $item->category == "Use Material" ){
                  $detail[$item->approvalno] = $this->otherRepository->getApprovalUseMaterialDetail($item->companycode, $item->approvalno);
              }

          }
      }
      return $detail;
    }
}
