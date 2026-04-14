<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Shared;

use Illuminate\Support\Facades\DB;

/**
 * MasterDataRepository
 * 
 * Centralized repository for shared master data queries.
 * RULE: This repo only reads master/reference tables.
 */
class MasterDataRepository
{
    /**
     * Get company info by companycode
     * 
     * @param string $companycode
     * @return object|null
     */
    public function getCompanyInfo($companycode)
    {
        return DB::table('company')
            ->where('companycode', $companycode)
            ->select('companycode', 'name')
            ->first();
    }

    /**
     * Get jabatan name by ID
     * 
     * @param int $idjabatan
     * @return string|null
     */
    public function getJabatanName($idjabatan)
    {
        $jabatan = DB::table('jabatan')
            ->where('idjabatan', $idjabatan)
            ->first();
        
        return $jabatan ? $jabatan->namajabatan : null;
    }

    /**
     * Get multiple jabatan names by IDs
     * 
     * @param array $ids
     * @return \Illuminate\Support\Collection
     */
    public function getJabatanNamesByIds(array $ids)
    {
        return DB::table('jabatan')
            ->whereIn('idjabatan', array_filter($ids))
            ->pluck('namajabatan', 'idjabatan');
    }

    public function getMandorsByCompany($companycode)
    {
        return DB::table('user')
            ->where('companycode', $companycode)
            ->where('idjabatan', 5)
            ->select('userid', 'name', 'companycode')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get approval setting by activity group
     * 
     * @param string $companycode
     * @param string $activitygroup
     * @return object|null
     */
    public function getApprovalSettingByActivityGroup($companycode, $activitygroup)
    {
        return DB::table('approval')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->first();
    }

    /**
     * Get all active activities
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getActivitiesActive()
    {
        return DB::table('activity as a')
            ->leftJoin('activitygroup as ag', 'a.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('jenistenagakerja as j', 'a.jenistenagakerja', '=', 'j.idjenistenagakerja')
            ->where('a.active', 1)
            ->select([
                'a.*',
                'ag.groupname',
                'j.nama as jenistenagakerja_nama'
            ])
            ->orderBy('a.activitycode')
            ->get();
    }

    /**
     * Get single activity by code
     * 
     * @param string $activitycode
     * @return object|null
     */
    public function getActivityByCode($activitycode)
    {
        return DB::table('activity')
            ->where('activitycode', $activitycode)
            ->first();
    }

    /**
     * Get all activity groups
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getActivityGroups()
    {
        return DB::table('activitygroup')
            ->orderBy('activitygroup')
            ->get();
    }

    /**
     * Get blok data for companycode
     * 
     * @param string $companycode
     * @return \Illuminate\Support\Collection
     */
    public function getBlokData($companycode)
    {
        return DB::table('blok')
            ->where('companycode', $companycode)
            ->orderBy('blok')
            ->get();
    }

    /**
     * Get active plots from masterlist
     * 
     * @param string $companycode
     * @return \Illuminate\Support\Collection
     */
    public function getActivePlotsFromMasterlist($companycode)
    {
        return DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->select('plot', 'blok', 'activebatchno')
            ->orderBy('blok')
            ->orderBy('plot')
            ->get();
    }

    /**
     * Get full masterlist data with batch info
     * 
     * @param string $companycode
     * @return \Illuminate\Support\Collection
     */
    public function getMasterlistData($companycode)
    {
        return DB::table('masterlist as m')
            ->leftJoin('batch as b', function($join) use ($companycode) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                    ->where('b.companycode', '=', $companycode);
            })
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->select([
                'm.companycode',
                'm.plot',
                'm.blok',
                'm.activebatchno',
                'm.isactive',
                'b.lifecyclestatus',
                'b.batcharea'
            ])
            ->orderBy('m.blok')
            ->orderBy('m.plot')
            ->get();
    }

    /**
     * Get full herbisida group data with dosage
     * 
     * @param string $companycode
     * @return array
     */
    public function getFullHerbisidaGroupData($companycode)
    {
        return DB::table('herbisidadosage as a')
            ->join('herbisida as b', function($join) use ($companycode) {
                $join->on('a.itemcode', '=', 'b.itemcode')
                    ->where('b.companycode', '=', $companycode);
            })
            ->join('herbisidagroup as c', 'a.herbisidagroupid', '=', 'c.herbisidagroupid')
            ->where('a.companycode', $companycode)
            ->select([
                'a.companycode',
                'a.herbisidagroupid',
                'c.herbisidagroupname',
                'c.activitycode',
                'c.rounddosage',
                'a.itemcode',
                'a.dosageperha',
                'b.itemname',
                'b.measure',
                'c.description'
            ])
            ->get()
            ->toArray();
    }

    public function getBoronganRate($companycode, $activitycode, $workDate)
    {
        return DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $activitycode)
            ->where('effectivedate', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', $workDate);
            })
            ->orderBy('effectivedate', 'DESC')
            ->value('amount');
    }

    /**
     * Check if user has permission to access given activitygroup.
     *
     * @param string $userid
     * @param string $companycode
     * @param string $activitygroup
     * @return bool
     */
    public function hasActivityGroupPermission($userid, $companycode, $activitygroup): bool
    {
        return DB::table('useractivity')
            ->where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->where('isactive', 1)
            ->exists();
    }

    /**
     * Resolve activitygroup from lkhno via rkhhdr.
     *
     * @param string $companycode
     * @param string $lkhno
     * @return string|null
     */
    public function getActivityGroupByLkhNo($companycode, $lkhno): ?string
    {
        return DB::table('lkhhdr as l')
            ->join('rkhhdr as r', function ($join) {
                $join->on('l.rkhno', '=', 'r.rkhno')
                     ->on('l.companycode', '=', 'r.companycode');
            })
            ->where('l.companycode', $companycode)
            ->where('l.lkhno', $lkhno)
            ->value('r.activitygroup');
    }
}