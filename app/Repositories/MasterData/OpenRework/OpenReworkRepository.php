<?php

namespace App\Repositories\MasterData\OpenRework;

use Illuminate\Support\Facades\DB;

/**
 * OpenReworkRepository
 * 
 * Handles database queries for rework approval requests
 */
class OpenReworkRepository
{
    /**
     * Get all rework requests with approval status
     */
    public function paginateReworkRequests($companycode, $filters, $perPage)
    {
        $query = DB::table('openrework as rr')
            ->leftJoin('approvaltransaction as at', function($join) use ($companycode) {
                $join->on('rr.transactionnumber', '=', 'at.transactionnumber')
                     ->where('at.companycode', '=', $companycode);
            })
            ->leftJoin('user as u', 'rr.inputby', '=', 'u.userid')
            ->where('rr.companycode', $companycode)
            ->select([
                'rr.*',
                'at.approvalno',
                'at.approvalstatus',
                'at.approval1flag',
                'at.approval2flag',
                'at.approval3flag',
                'at.jumlahapproval',
                'u.name as inputby_name',
                DB::raw("DATE_FORMAT(rr.requestdate, '%d/%m/%Y') as formatted_date"),
                DB::raw("DATE_FORMAT(rr.createdat, '%d/%m/%Y %H:%i') as formatted_createdat")
            ])
            ->orderBy('rr.createdat', 'desc');
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('rr.transactionnumber', 'like', "%{$filters['search']}%")
                  ->orWhere('rr.inputby', 'like', "%{$filters['search']}%");
            });
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Get all active activities
     */
    public function getActiveActivities($companycode)
    {
        // Get activities from LKH that have been executed
        return $this->getActivitiesFromLkh($companycode);
    }

    /**
     * Get LKH list by activity and date range
     */
    public function getLkhByActivityAndDateRange($companycode, $activitycode, $startDate, $endDate)
    {
        return DB::table('lkhhdr as lh')
            ->where('lh.companycode', $companycode)
            ->where('lh.activitycode', $activitycode)
            ->where('lh.approvalstatus', '1') // Only approved LKH
            ->whereBetween('lh.lkhdate', [$startDate, $endDate])
            ->select([
                'lh.lkhno',
                'lh.lkhdate',
                'lh.activitycode',
                'lh.totalhasil',
                'lh.totalworkers',
                DB::raw("DATE_FORMAT(lh.lkhdate, '%d/%m/%Y') as formatted_date"),
                DB::raw("GROUP_CONCAT(DISTINCT ldp.plot ORDER BY ldp.plot SEPARATOR ', ') as plots"),
                DB::raw("COUNT(DISTINCT ldp.plot) as total_plots")
            ])
            ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                $join->on('lh.lkhno', '=', 'ldp.lkhno')
                     ->where('ldp.companycode', '=', $companycode);
            })
            ->groupBy('lh.lkhno', 'lh.lkhdate', 'lh.activitycode', 'lh.totalhasil', 'lh.totalworkers')
            ->orderBy('lh.lkhdate', 'desc')
            ->get();
    }

    /**
     * Get activities from LKH that have rework possibility
     */
    public function getActivitiesFromLkh($companycode)
    {
        return DB::table('lkhhdr as lh')
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            ->where('lh.companycode', $companycode)
            ->where('lh.approvalstatus', '1')
            ->select([
                'lh.activitycode',
                'a.activityname',
                DB::raw('COUNT(DISTINCT lh.lkhno) as total_lkh')
            ])
            ->groupBy('lh.activitycode', 'a.activityname')
            ->having('total_lkh', '>', 0)
            ->orderBy('lh.activitycode')
            ->get();
    }

    /**
     * Get LKH detail plots with rework status
     */
    public function getLkhDetailPlots($companycode, $lkhno)
    {
        return DB::table('lkhdetailplot as ldp')
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->select([
                'ldp.plot',
                'ldp.blok',
                'ldp.rework',
                'ldp.luasrkh as luas_rencana',
                'ldp.luashasil as luas_hasil',
                'ldp.luassisa as luas_sisa'
            ])
            ->orderBy('ldp.blok')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Create rework request transaction
     */
    public function createReworkRequest($data)
    {
        // Note: Using insertGetId may not work with composite PK, using insert instead
        DB::table('openrework')->insert($data);
        return $data['transactionnumber'];
    }

    /**
     * Update rework flag in lkhdetailplot
     */
    public function updateReworkFlag($companycode, $plots, $activities)
    {
        // Update semua LKH yang memiliki plot dan activity yang sesuai
        return DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) use ($companycode) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                     ->where('lh.companycode', '=', $companycode);
            })
            ->where('ldp.companycode', $companycode)
            ->whereIn('ldp.plot', $plots)
            ->whereIn('lh.activitycode', $activities)
            ->update(['ldp.rework' => 1]);
    }

    /**
     * Get approval master for rework
     */
    public function getApprovalMaster($companycode)
    {
        return DB::table('approval')
            ->where('companycode', $companycode)
            ->where('category', 'Approval Open Rework')
            ->first();
    }

    /**
     * Create approval transaction
     */
    public function createApprovalTransaction($data)
    {
        return DB::table('approvaltransaction')->insert($data);
    }

    /**
     * Get approval detail
     */
    public function getApprovalDetail($companycode, $approvalno)
    {
        return DB::table('approvaltransaction as at')
            ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
            ->leftJoin('jabatan as j1', 'at.approval1idjabatan', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'at.approval2idjabatan', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'at.approval3idjabatan', '=', 'j3.idjabatan')
            ->leftJoin('user as u1', 'at.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'at.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'at.approval3userid', '=', 'u3.userid')
            ->where('at.companycode', $companycode)
            ->where('at.approvalno', $approvalno)
            ->select([
                'at.*',
                'am.category',
                'j1.namajabatan as jabatan1_name',
                'j2.namajabatan as jabatan2_name',
                'j3.namajabatan as jabatan3_name',
                'u1.name as approval1_username',
                'u2.name as approval2_username',
                'u3.name as approval3_username',
                DB::raw("DATE_FORMAT(at.approval1date, '%d/%m/%Y %H:%i') as approval1date"),
                DB::raw("DATE_FORMAT(at.approval2date, '%d/%m/%Y %H:%i') as approval2date"),
                DB::raw("DATE_FORMAT(at.approval3date, '%d/%m/%Y %H:%i') as approval3date")
            ])
            ->first();
    }

    /**
     * Get rework request by transaction number
     */
    public function getReworkRequestByTransactionNumber($companycode, $transactionNumber)
    {
        return DB::table('openrework')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transactionNumber)
            ->first();
    }
}