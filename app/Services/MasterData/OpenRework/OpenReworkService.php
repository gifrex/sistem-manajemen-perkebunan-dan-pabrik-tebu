<?php

namespace App\Services\MasterData\OpenRework;

use App\Repositories\MasterData\OpenRework\OpenReworkRepository;
use Carbon\Carbon;

/**
 * OpenReworkService
 * 
 * Business logic for rework approval requests
 */
class OpenReworkService
{
    protected $repo;

    public function __construct(OpenReworkRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get index page data
     */
    public function getIndexPageData($filters, $perPage, $companycode)
    {
        $requests = $this->repo->paginateReworkRequests($companycode, $filters, $perPage);
        
        // Transform JSON columns
        $requests->getCollection()->transform(function($request) {
            $request->plots_array = json_decode($request->plots, true);
            $request->activities_array = json_decode($request->activities, true);
            return $request;
        });
        
        $activities = $this->repo->getActiveActivities($companycode);
        
        return [
            'requests' => $requests,
            'activities' => $activities
        ];
    }

    /**
     * Get LKH list by activity and date range
     */
    public function getLkhByActivityAndDateRange($companycode, $activitycode, $startDate, $endDate)
    {
        return $this->repo->getLkhByActivityAndDateRange($companycode, $activitycode, $startDate, $endDate);
    }

    /**
     * Get LKH detail plots
     */
    public function getLkhDetailPlots($companycode, $lkhno)
    {
        $plots = $this->repo->getLkhDetailPlots($companycode, $lkhno);
        
        return $plots->map(function($plot) {
            return [
                'plot' => $plot->plot,
                'blok' => $plot->blok,
                'rework' => $plot->rework,
                'luas_rencana' => number_format($plot->luas_rencana ?? 0, 2),
                'luas_hasil' => number_format($plot->luas_hasil ?? 0, 2),
                'luas_sisa' => number_format($plot->luas_sisa ?? 0, 2),
                'can_select' => $plot->rework == 0 // Can only select if not yet reworked
            ];
        });
    }

    /**
     * Create rework request
     */
    public function createReworkRequest($dto, $companycode, $userid)
    {
        $transactionNumber = $this->generateTransactionNumber($companycode, now());
        $approvalMaster = $this->repo->getApprovalMaster($companycode);
        
        if (!$approvalMaster) {
            throw new \Exception('Approval master untuk "Approval Open Rework" belum di-setup');
        }
        
        // Insert rework request (sesuai struktur DB yang ada)
        $this->repo->createReworkRequest([
            'transactionnumber' => $transactionNumber,
            'companycode' => $companycode,
            'requestdate' => now()->format('Y-m-d'),
            'plots' => json_encode($dto['plots']),
            'activities' => json_encode($dto['activities']), // Sesuai struktur DB
            'reason' => $dto['reason'],
            'inputby' => $userid,
            'createdat' => now()
        ]);
        
        // Create approval transaction
        $approvalNo = $this->generateApprovalNo($companycode, now());
        
        $this->repo->createApprovalTransaction([
            'approvalno' => $approvalNo,
            'companycode' => $companycode,
            'approvalcategoryid' => $approvalMaster->id,
            'transactionnumber' => $transactionNumber,
            'jumlahapproval' => $approvalMaster->jumlahapproval,
            'approval1idjabatan' => $approvalMaster->idjabatanapproval1,
            'approval2idjabatan' => $approvalMaster->idjabatanapproval2,
            'approval3idjabatan' => $approvalMaster->idjabatanapproval3,
            'approvalstatus' => null,
            'inputby' => $userid,
            'createdat' => now()
        ]);
        
        return [
            'success' => true,
            'message' => "Rework request berhasil dibuat [{$transactionNumber}]",
            'data' => [
                'transaction_number' => $transactionNumber,
                'approval_number' => $approvalNo
            ]
        ];
    }

    /**
     * Generate transaction number: RWK[YYMMDD][SEQ]
     */
    private function generateTransactionNumber($companycode, $date)
    {
        $dateStr = $date->format('ymd');
        $count = \DB::table('openrework')
            ->where('companycode', $companycode)
            ->whereDate('requestdate', $date)
            ->count();
        $sequence = $count + 1;
        
        return "RWK{$dateStr}" . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Generate approval number: APV[YYMMDD][SEQ]
     */
    private function generateApprovalNo($companycode, $date)
    {
        $dateStr = $date->format('ymd');
        $count = \DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->whereDate('createdat', $date)
            ->count();
        $sequence = $count + 1;
        
        return "APV{$dateStr}" . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }
}