<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Shared;

use Illuminate\Support\Facades\DB;

/**
 * MasterlistBatchRepository
 * 
 * Centralized repository for masterlist + batch business logic.
 * RULE: Complex batch queries, progress calculations, and batch writes ONLY here.
 * Other repos CAN join batch for simple display purposes (avoid N+1).
 */
class MasterlistBatchRepository
{
    /**
     * Get active batch for specific plot
     * Returns batch with surrogate ID + full metadata
     * 
     * @param string $companycode
     * @param string $plot
     * @return object|null
     */
    public function getActiveBatchForPlot($companycode, $plot)
    {
        return DB::table('masterlist')
            ->join('batch', function($join) use ($companycode) {
                $join->on('masterlist.activebatchno', '=', 'batch.batchno')
                    ->where('batch.companycode', '=', $companycode)
                    ->where('batch.isactive', '=', 1);
            })
            ->where('masterlist.companycode', $companycode)
            ->where('masterlist.plot', $plot)
            ->where('masterlist.isactive', 1)
            ->select([
                'batch.id',
                'batch.batchno',
                'batch.lifecyclestatus',
                'batch.batcharea',
                'batch.tanggalpanen',
                'batch.batchdate',
                'masterlist.blok',
                'masterlist.plot'
            ])
            ->first();
    }

    /**
     * Get plot with active batch + lifecycle data
     * Used in forms (create/edit RKH) with panen history
     * 
     * @param string $companycode
     * @param string $plot
     * @return object|null
     */
    public function getPlotWithActiveBatch($companycode, $plot)
    {
        return DB::table('masterlist as m')
            ->leftJoin('batch as b', function($join) use ($companycode) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                    ->where('b.companycode', '=', $companycode);
            })
            ->leftJoin(DB::raw('(
                SELECT batchno, COALESCE(SUM(luashasil), 0) as total_panen
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno AND ldp.companycode = lh.companycode
                WHERE lh.companycode = "' . $companycode . '" 
                AND lh.approvalstatus = "1"
                GROUP BY batchno
            ) as panen_summary'), 'b.batchno', '=', 'panen_summary.batchno')
            ->where('m.companycode', $companycode)
            ->where('m.plot', $plot)
            ->where('m.isactive', 1)
            ->select([
                'm.companycode',
                'm.plot',
                'm.blok',
                'm.activebatchno',
                'm.isactive',
                'b.id as batch_id',
                'b.lifecyclestatus',
                'b.batcharea',
                'b.tanggalpanen',
                'b.isactive as batch_isactive',
                DB::raw('COALESCE(panen_summary.total_panen, 0) as total_panen'),
                DB::raw("CASE 
                    WHEN b.tanggalpanen IS NOT NULL 
                        AND b.isactive = 1
                        AND b.batcharea > COALESCE(panen_summary.total_panen, 0)
                    THEN 1
                    ELSE 0
                END as is_on_panen")
            ])
            ->first();
    }

    /**
     * Get all active plots with batch metadata
     * Used in masterlist dropdown for RKH create/edit
     * 
     * @param string $companycode
     * @return \Illuminate\Support\Collection
     */
    public function getAllActivePlotsWithBatch($companycode)
    {
        return DB::table('masterlist as m')
            ->leftJoin('batch as b', function($join) use ($companycode) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                    ->where('b.companycode', '=', $companycode);
            })
            ->leftJoin(DB::raw('(
                SELECT batchno, COALESCE(SUM(luashasil), 0) as total_panen
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno AND ldp.companycode = lh.companycode
                WHERE lh.companycode = "' . $companycode . '" 
                AND lh.approvalstatus = "1"
                GROUP BY batchno
            ) as panen_summary'), 'b.batchno', '=', 'panen_summary.batchno')
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->select([
                'm.companycode',
                'm.plot',
                'm.blok',
                'm.activebatchno',
                'm.isactive',
                'b.id as batch_id',
                'b.lifecyclestatus',
                'b.batcharea',
                'b.tanggalpanen',
                'b.isactive as batch_isactive',
                DB::raw('COALESCE(panen_summary.total_panen, 0) as total_panen'),
                DB::raw("CASE 
                    WHEN b.tanggalpanen IS NOT NULL 
                        AND b.isactive = 1
                        AND b.batcharea > COALESCE(panen_summary.total_panen, 0)
                    THEN 1
                    ELSE 0
                END as is_on_panen"),
                DB::raw('(
                    SELECT activitycode
                    FROM lkhdetailplot ldp2
                    JOIN lkhhdr lh2 ON ldp2.lkhno = lh2.lkhno AND ldp2.companycode = lh2.companycode
                    WHERE ldp2.companycode = "' . $companycode . '"
                    AND ldp2.plot = m.plot
                    AND ldp2.batchno = m.activebatchno
                    AND lh2.approvalstatus = "1"
                    ORDER BY lh2.lkhdate DESC
                    LIMIT 1
                ) as last_activitycode'),
                DB::raw('(
                    SELECT a2.activityname
                    FROM lkhdetailplot ldp2
                    JOIN lkhhdr lh2 ON ldp2.lkhno = lh2.lkhno AND ldp2.companycode = lh2.companycode
                    JOIN activity a2 ON lh2.activitycode = a2.activitycode
                    WHERE ldp2.companycode = "' . $companycode . '"
                    AND ldp2.plot = m.plot
                    AND ldp2.batchno = m.activebatchno
                    AND lh2.approvalstatus = "1"
                    ORDER BY lh2.lkhdate DESC
                    LIMIT 1
                ) as last_activityname'),
                DB::raw('(
                    SELECT lh2.lkhdate
                    FROM lkhdetailplot ldp2
                    JOIN lkhhdr lh2 ON ldp2.lkhno = lh2.lkhno AND ldp2.companycode = lh2.companycode
                    WHERE ldp2.companycode = "' . $companycode . '"
                    AND ldp2.plot = m.plot
                    AND ldp2.batchno = m.activebatchno
                    AND lh2.approvalstatus = "1"
                    ORDER BY lh2.lkhdate DESC
                    LIMIT 1
                ) as last_activity_date')
            ])
            ->orderBy('m.blok')
            ->orderBy('m.plot')
            ->get();
    }

    /**
     * Calculate total approved work (luashasil) 
     * for plot+activity BEFORE specific date
     * filter by active BATCH
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $activitycode
     * @param string $beforeDate
     * @return float
     */
    public function getTotalApprovedWorkByPlotActivityBeforeDate(
        $companycode, 
        $plot, 
        $activitycode, 
        $beforeDate,
        $batchno = null
    ) {
        $query = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.plot', $plot)
            ->when(is_array($activitycode),
                fn($q) => $q->whereIn('lh.activitycode', $activitycode),
                fn($q) => $q->where('lh.activitycode', $activitycode)
            )
            ->where('lh.approvalstatus', '1')
            ->where('ldp.rework', 0)
            ->whereDate('lh.lkhdate', '<', $beforeDate);

        if ($batchno) {
            $query->where('ldp.batchno', $batchno);
        }
        
        return (float) $query->sum('ldp.luashasil');
    }

    /**
     * Calculate total approved luashasil for a plot, excluding a specific LKH.
     * Used for over-luas validation at submit/approval time.
     */
    public function getTotalApprovedWorkByPlotExcludingLkh(
        $companycode,
        $plot,
        $activitycodes,
        $batchno,
        $excludeLkhno
    ) {
        return (float) DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function ($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.plot', $plot)
            ->where('ldp.batchno', $batchno)
            ->when(is_array($activitycodes),
                fn($q) => $q->whereIn('lh.activitycode', $activitycodes),
                fn($q) => $q->where('lh.activitycode', $activitycodes)
            )
            ->where('lh.approvalstatus', '1')
            ->where('lh.lkhno', '!=', $excludeLkhno)
            ->sum('ldp.luashasil');
    }

    /**
     * Calculate total harvest (luashasil)
     * for batch UNTIL specific date (STC calculation)
     * 
     * @param string $companycode
     * @param string $batchno
     * @param string $untilDate
     * @return float
     */
    public function getTotalApprovedHarvestByBatchUntilDate(
        $companycode, 
        $batchno, 
        $untilDate
    ) {
        return (float) DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.batchno', $batchno)
            ->where('lh.approvalstatus', '1')
            ->whereDate('lh.lkhdate', '<=', $untilDate)
            ->sum('ldp.luashasil');
    }

    /**
     * Get last approved activity code for plot
     * 
     * @param string $companycode
     * @param string $plot
     * @return string|null
     */
    public function getLastApprovedActivityForPlot($companycode, $plot)
    {
        return DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.plot', $plot)
            ->where('lh.approvalstatus', '1')
            ->orderBy('lh.lkhdate', 'desc')
            ->value('lh.activitycode');
    }

    /**
     * Get last approved activity info for plot
     * 
     * @param string $companycode
     * @param string $plot
     * @return string|null
     */
    public function getLastApprovedActivityInfoForPlot($companycode, $plot, $batchno = null)
    {
        $query = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->join('activity as ma', 'lh.activitycode', '=', 'ma.activitycode')
            ->where('ldp.companycode', $companycode)
            ->where('ldp.plot', $plot)
            ->where('lh.approvalstatus', '1');

        if ($batchno) {
            $query->where('ldp.batchno', $batchno);
        }

        return $query->orderBy('lh.lkhdate', 'desc')
            ->select([
                'lh.activitycode as last_activitycode',
                'ma.activityname as last_activityname',
                'lh.lkhdate as last_activity_date'
            ])
            ->first();
    }

    /**
     * Get last ZPK (4.2.2) date for plot
     * Used in panen validation (25-35 days gap)
     * 
     * @param string $companycode
     * @param string $plot
     * @return string|null
     */
    public function getLastApprovedZpkDateForPlot($companycode, $plot)
    {
        return DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.plot', $plot)
            ->where('lh.activitycode', '4.2.2')
            ->where('lh.approvalstatus', '1')
            ->orderBy('lh.lkhdate', 'desc')
            ->value('lh.lkhdate');
    }

    /**
     * Get previous inactive batch for lifecycle transition
     * Used to determine PC→RC1→RC2→RC3→PC
     * 
     * @param string $companycode
     * @param string $plot
     * @return object|null
     */
    public function getPreviousInactiveBatch($companycode, $plot)
    {
        return DB::table('batch')
            ->where('companycode', $companycode)
            ->where('plot', $plot)
            ->where('isactive', 0)
            ->orderBy('createdat', 'desc')
            ->first();
    }

    /**
     * Insert new batch and return surrogate ID
     * 
     * @param array $data
     * @return int
     */
    public function insertBatchReturnId(array $data)
    {
        return DB::table('batch')->insertGetId($data);
    }

    /**
     * Update/insert masterlist.activebatchno
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $batchno
     * @return int
     */
    public function upsertMasterlistActiveBatch($companycode, $plot, $batchno)
    {
        return DB::table('masterlist')->updateOrInsert(
            ['companycode' => $companycode, 'plot' => $plot],
            ['activebatchno' => $batchno, 'isactive' => 1]
        );
    }

    /**
     * Count batches created on specific batchdate
     * Used for sequence generation (BATCH240801001)
     * 
     * @param string $companycode
     * @param string $batchdate
     * @return int
     */
    public function countBatchesByBatchDate($companycode, $batchdate)
    {
        return DB::table('batch')
            ->where('companycode', $companycode)
            ->whereDate('batchdate', $batchdate)
            ->count();
    }
}