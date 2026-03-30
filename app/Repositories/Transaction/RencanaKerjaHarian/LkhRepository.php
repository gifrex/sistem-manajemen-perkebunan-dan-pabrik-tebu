<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;

/**
 * LkhRepository
 * 
 * Handles LKH (lkhhdr + lkhdetail*) database operations.
 * RULE: All LKH queries here. Batch-load methods for N+1 prevention.
 */
class LkhRepository
{
    /**
     * Get LKH header with approval metadata
     * Used in: show page
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getHeaderForShow($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandornama',
                'a.activityname',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * Get LKH header for edit
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getHeaderForEdit($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandornama',
                'a.activityname'
            ])
            ->first();
    }

    /**
     * List all LKH for specific RKH
     * Returns: lkhno, activitycode, status, workers, etc.
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return \Illuminate\Support\Collection
     */
    public function listByRkhNo($companycode, $rkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->where('h.rkhno', $rkhno)
            ->select([
                'h.lkhno',
                'h.activitycode',
                'a.activityname',
                'h.jenistenagakerja',
                'h.lkhdate',
                'h.totalworkers',
                'h.totalhasil',
                'h.totalsisa',
                'h.totalupahall',
                'h.createdat',
                'h.status',
                'h.issubmit',
                'h.submitby',
                'h.submitat',
                'h.jumlahapproval',
                'h.approval1flag',
                'h.approval2flag',
                'h.approval3flag',
                'app.jumlahapproval as required_approvals'
            ])
            ->orderBy('h.lkhno')
            ->get();
    }

    /**
     * Batch-load: Get plots grouped by lkhno
     * Input: array of lkhno
     * Output: Collection grouped by lkhno
     * 
     * @param string $companycode
     * @param array $lkhNos
     * @return \Illuminate\Support\Collection
     */
    public function getPlotsByLkhNos($companycode, array $lkhNos)
    {
        return DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->whereIn('lkhno', $lkhNos)
            ->select('lkhno', 'blok', 'plot', 'luasrkh')
            ->get()
            ->groupBy('lkhno');
    }

    /**
     * Batch-load: Get workers count grouped by lkhno
     * 
     * @param string $companycode
     * @param array $lkhNos
     * @return \Illuminate\Support\Collection
     */
    public function getWorkersCountByLkhNos($companycode, array $lkhNos)
    {
        return DB::table('lkhdetailworker')
            ->where('companycode', $companycode)
            ->whereIn('lkhno', $lkhNos)
            ->select('lkhno', DB::raw('COUNT(*) as count'))
            ->groupBy('lkhno')
            ->pluck('count', 'lkhno');
    }

    /**
     * Batch-load: Get materials count grouped by lkhno
     * 
     * @param string $companycode
     * @param array $lkhNos
     * @return \Illuminate\Support\Collection
     */
    public function getMaterialsCountByLkhNos($companycode, array $lkhNos)
    {
        return DB::table('lkhdetailmaterial')
            ->where('companycode', $companycode)
            ->whereIn('lkhno', $lkhNos)
            ->select('lkhno', DB::raw('COUNT(*) as count'))
            ->groupBy('lkhno')
            ->pluck('count', 'lkhno');
    }

    /**
     * Get plot details for show (normal activity)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getPlotDetailsForShow($companycode, $lkhno)
    {
        return DB::table('lkhdetailplot as ldp')
            ->leftJoin('batch as b', 'ldp.batchid', '=', 'b.id')
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->select([
                'ldp.*',
                'b.lifecyclestatus',
                'b.batcharea',
                'b.tanggalpanen'
            ])
            ->orderBy('ldp.blok')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Get worker details for show
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getWorkerDetailsForShow($companycode, $lkhno)
    {
        return DB::table('lkhdetailworker as ldw')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('ldw.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode);
            })
            ->where('ldw.companycode', $companycode)
            ->where('ldw.lkhno', $lkhno)
            ->select([
                'ldw.*',
                'tk.nama',
                'tk.nik',
                'tk.jenistenagakerja'
            ])
            ->orderBy('ldw.tenagakerjaurutan')
            ->get();
    }

    /**
     * Get material details for show
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getMaterialDetailsForShow($companycode, $lkhno)
    {
        return DB::table('lkhdetailmaterial as ldm')
            ->leftJoin('herbisida as h', function($join) use ($companycode) {
                $join->on('ldm.itemcode', '=', 'h.itemcode')
                    ->where('h.companycode', '=', $companycode);
            })
            ->where('ldm.companycode', $companycode)
            ->where('ldm.lkhno', $lkhno)
            ->select([
                'ldm.id',
                'ldm.plot',
                'ldm.itemcode',
                'ldm.qtyditerima',
                'ldm.qtysisa', 
                'ldm.qtydigunakan',
                'ldm.keterangan',
                'ldm.inputby',
                'ldm.createdat',
                'ldm.updatedat',
                'h.itemname',
                'h.measure as satuan'
            ])
            ->orderBy('ldm.plot')
            ->orderBy('ldm.itemcode')
            ->get()
            ->map(function($material) {
                return (object)[
                    'id' => $material->id,
                    'plot' => (string)($material->plot ?? '-'),
                    'itemcode' => (string)($material->itemcode ?? ''),
                    'itemname' => (string)($material->itemname ?? 'Unknown Item'),
                    'qtyditerima' => floatval($material->qtyditerima ?? 0),
                    'qtysisa' => floatval($material->qtysisa ?? 0),
                    'qtydigunakan' => floatval($material->qtydigunakan ?? 0),
                    'satuan' => (string)($material->satuan ?? '-'),
                    'keterangan' => (string)($material->keterangan ?? ''),
                    'inputby' => (string)($material->inputby ?? ''),
                    'createdat' => $material->createdat,
                    'updatedat' => $material->updatedat,
                ];
            });
    }

    /**
     * Get BSM details for show
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getBsmDetailsForShow($companycode, $lkhno)
    {
        return DB::table('lkhdetailbsm as bsm')
            ->leftJoin('batch as b', function($join) use ($companycode) {
                $join->on('bsm.batchno', '=', 'b.batchno')
                    ->where('b.companycode', '=', $companycode);
            })
            ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                $join->on('bsm.companycode', '=', 'ldp.companycode')
                    ->on('bsm.lkhno', '=', 'ldp.lkhno')
                    ->on('bsm.plot', '=', 'ldp.plot');
            })
            ->where('bsm.companycode', $companycode)
            ->where('bsm.lkhno', $lkhno)
            ->select([
                'bsm.id',
                'bsm.suratjalanno',
                'ldp.blok',
                'bsm.plot',
                'bsm.kodetebang',
                'bsm.batchno',
                'bsm.nilaibersih',
                'bsm.nilaisegar',
                'bsm.nilaimanis',
                'bsm.averagescore',
                'bsm.grade',
                'bsm.keterangan',
                'bsm.inputby',
                'bsm.createdat',
                'bsm.updateby',
                'bsm.updatedat',
                'b.lifecyclestatus as kodestatus',
                'b.batcharea',
                'ldp.luasrkh'
            ])
            ->orderBy('ldp.blok')
            ->orderBy('bsm.plot')
            ->orderBy('bsm.suratjalanno')
            ->get()
            ->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'suratjalanno' => $item->suratjalanno,
                    'blok' => $item->blok ?? '-',
                    'plot' => $item->plot,
                    'plot_display' => $item->plot,
                    'kodetebang' => $item->kodetebang ?? '-',
                    'kodetebang_label' => stripos($item->kodetebang ?? '', 'premium') !== false ? 'Premium' : 'Non-Premium',
                    'batchno' => $item->batchno ?? '-',
                    'kodestatus' => $item->kodestatus ?? '-',
                    'batcharea' => number_format((float)($item->batcharea ?? 0), 2),
                    'luasrkh' => number_format((float)($item->luasrkh ?? 0), 2),
                    'nilaibersih' => $item->nilaibersih ? number_format((float)$item->nilaibersih, 2) : null,
                    'nilaisegar' => $item->nilaisegar ? number_format((float)$item->nilaisegar, 2) : null,
                    'nilaimanis' => $item->nilaimanis ? number_format((float)$item->nilaimanis, 2) : null,
                    'averagescore' => $item->averagescore ? number_format((float)$item->averagescore, 2) : null,
                    'grade' => $item->grade ?? null,
                    'status' => $item->averagescore ? 'COMPLETED' : 'PENDING',
                    'keterangan' => $item->keterangan ?? '-',
                    'inputby' => $item->inputby ?? '-',
                    'createdat' => $item->createdat ? \Carbon\Carbon::parse($item->createdat)->format('d/m/Y H:i') : '-',
                    'updateby' => $item->updateby ?? '-',
                    'updatedat' => $item->updatedat ? \Carbon\Carbon::parse($item->updatedat)->format('d/m/Y H:i') : '-',
                ];
            });
    }

    /**
     * Get panen details for show (with STC/BC calculations)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getPanenDetailsForShow($companycode, $lkhno)
    {
        $lkhDate = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->value('lkhdate');

        $panenActivities = "'4.3.3', '4.4.3', '4.5.2', '2.2.2a', '2.2.2b'";

        return DB::table('lkhdetailplot as ldp')
            ->leftJoin('batch as b', 'ldp.batchid', '=', 'b.id')
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->select([
                'ldp.plot',
                'ldp.blok',
                'ldp.batchno',
                'ldp.luasrkh',
                'ldp.luashasil',
                'ldp.createdat',
                'ldp.fieldbalancerit',
                'ldp.fieldbalanceton',
                'b.batcharea',
                'b.tanggalpanen',
                'b.lifecyclestatus as kodestatus',

                // STC calculation — only panen activities share the same saldo
                DB::raw("(
                    COALESCE(b.batcharea, 0) -
                    COALESCE((
                        SELECT SUM(ldp2.luashasil)
                        FROM lkhdetailplot ldp2
                        JOIN lkhhdr lh2 ON ldp2.lkhno = lh2.lkhno
                                        AND ldp2.companycode = lh2.companycode
                        WHERE ldp2.companycode = ldp.companycode
                        AND ldp2.batchno = ldp.batchno
                        AND ldp2.batchno IS NOT NULL
                        AND lh2.activitycode IN ({$panenActivities})
                        AND lh2.approvalstatus = '1'
                        AND lh2.lkhdate < '{$lkhDate}'
                    ), 0)
                ) as stc"),

                DB::raw('COALESCE(ldp.luashasil, 0) as hc'),

                // BC calculation — same panen-only filter
                DB::raw("(
                    (
                        COALESCE(b.batcharea, 0) -
                        COALESCE((
                            SELECT SUM(ldp2.luashasil)
                            FROM lkhdetailplot ldp2
                            JOIN lkhhdr lh2 ON ldp2.lkhno = lh2.lkhno
                                            AND ldp2.companycode = lh2.companycode
                            WHERE ldp2.companycode = ldp.companycode
                            AND ldp2.batchno = ldp.batchno
                            AND ldp2.batchno IS NOT NULL
                            AND lh2.activitycode IN ({$panenActivities})
                            AND lh2.approvalstatus = '1'
                            AND lh2.lkhdate < '{$lkhDate}'
                        ), 0)
                    ) - COALESCE(ldp.luashasil, 0)
                ) as bc"),
                
                // Hari Tebang dengan fallback
                DB::raw("CASE 
                    WHEN b.tanggalpanen IS NULL THEN 1
                    ELSE DATEDIFF('{$lkhDate}', b.tanggalpanen) + 1
                END as haritebang")
            ])
            ->orderBy('ldp.blok')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Get kontraktor summary for LKH panen
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getKontraktorSummaryForLkh($companycode, $lkhno)
    {
        return DB::table('suratjalanpos as sjp')
            ->leftJoin('kontraktor as k', function($join) use ($companycode) {
                $join->on('sjp.namakontraktor', '=', 'k.id')
                    ->where('k.companycode', '=', $companycode);
            })
            ->where('sjp.companycode', $companycode)
            ->where('sjp.suratjalanno', 'LIKE', "%-{$lkhno}-%")
            ->select([
                'sjp.namakontraktor as kontraktor_id',
                'k.namakontraktor as kontraktor_nama',
                DB::raw('COUNT(DISTINCT sjp.namasubkontraktor) as total_subkontraktor'),
                DB::raw('COUNT(DISTINCT sjp.plot) as total_plot'),
                DB::raw("GROUP_CONCAT(DISTINCT sjp.plot ORDER BY sjp.plot SEPARATOR ', ') as list_plot")
            ])
            ->groupBy('sjp.namakontraktor', 'k.namakontraktor')
            ->orderBy('k.namakontraktor')
            ->get();
    }

    /**
     * Get subkontraktor detail for LKH panen
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getSubkontraktorDetailForLkh($companycode, $lkhno)
    {
        return DB::table('suratjalanpos as sjp')
            ->join('lkhdetailplot as ldp', function($join) use ($companycode, $lkhno) {
                $join->on('sjp.plot', '=', 'ldp.plot')
                    ->where('ldp.companycode', '=', $companycode)
                    ->where('ldp.lkhno', '=', $lkhno);
            })
            ->leftJoin('kontraktor as k', function($join) use ($companycode) {
                $join->on('sjp.namakontraktor', '=', 'k.id')
                    ->where('k.companycode', '=', $companycode);
            })
            ->leftJoin('subkontraktor as sk', function($join) use ($companycode) {
                $join->on('sjp.namasubkontraktor', '=', 'sk.id')
                    ->where('sk.companycode', '=', $companycode);
            })
            ->where('sjp.companycode', $companycode)
            ->where('sjp.suratjalanno', 'LIKE', "%-{$lkhno}-%")
            ->select([
                'ldp.blok',
                'sjp.plot',
                'sjp.namakontraktor as kontraktor_id',
                'k.namakontraktor as kontraktor_nama',
                'sjp.namasubkontraktor as subkontraktor_id',
                'sk.namasubkontraktor as subkontraktor_nama',
                DB::raw('COUNT(sjp.suratjalanno) as jumlah_sj')
            ])
            ->groupBy(
                'ldp.blok',
                'sjp.plot',
                'sjp.namakontraktor',
                'k.namakontraktor',
                'sjp.namasubkontraktor',
                'sk.namasubkontraktor'
            )
            ->orderBy('ldp.blok')
            ->orderBy('sjp.plot')
            ->get();
    }

    /**
     * Get ongoing plots for mandor (not in current LKH)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param string $mandorid
     * @return \Illuminate\Support\Collection
     */
    public function getOngoingPlotsForMandor($companycode, $lkhno, $mandorid)
    {
        // Get LKH date for reference
        $lkhDate = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->value('lkhdate');
        
        // Get plots in current LKH (to exclude)
        $currentLkhPlots = DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->pluck('plot')
            ->toArray();
        
        // Query ongoing plots for this mandor
        $ongoingPlots = DB::table('masterlist as m')
            ->join('batch as b', function($join) use ($companycode) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                    ->where('b.companycode', '=', $companycode);
            })
            ->leftJoin(DB::raw('(
                SELECT 
                    ldp.plot,
                    ldp.batchno,
                    SUM(ldp.luashasil) as total_dipanen,
                    MAX(lh.lkhdate) as last_harvest_date
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno AND ldp.companycode = lh.companycode
                WHERE ldp.companycode = "' . $companycode . '"
                    AND lh.mandorid = "' . $mandorid . '"
                    AND lh.approvalstatus = "1"
                GROUP BY ldp.plot, ldp.batchno
            ) as harvest_summary'), function($join) {
                $join->on('m.plot', '=', 'harvest_summary.plot')
                    ->on('b.batchno', '=', 'harvest_summary.batchno');
            })
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->where('b.isactive', 1)
            ->whereNotNull('b.tanggalpanen')
            ->whereNotNull('harvest_summary.total_dipanen')
            ->whereNotIn('m.plot', $currentLkhPlots)
            ->select([
                'm.plot',
                'm.blok',
                'b.batchno',
                'b.batcharea',
                'b.tanggalpanen',
                'b.lifecyclestatus as kodestatus',
                DB::raw('COALESCE(harvest_summary.total_dipanen, 0) as total_dipanen'),
                DB::raw('(b.batcharea - COALESCE(harvest_summary.total_dipanen, 0)) as sisa'),
                'harvest_summary.last_harvest_date',
                DB::raw('DATEDIFF("' . $lkhDate . '", harvest_summary.last_harvest_date) as days_since_harvest')
            ])
            ->havingRaw('sisa > 0')
            ->orderBy('m.blok')
            ->orderBy('m.plot')
            ->get();
        
        return $ongoingPlots->map(function($plot) {
            return (object)[
                'plot' => $plot->plot,
                'blok' => $plot->blok,
                'batchno' => $plot->batchno,
                'batcharea' => number_format((float)$plot->batcharea, 2),
                'tanggalpanen' => $plot->tanggalpanen ? \Carbon\Carbon::parse($plot->tanggalpanen)->format('d/m/Y') : '-',
                'kodestatus' => $plot->kodestatus,
                'total_dipanen' => number_format((float)$plot->total_dipanen, 2),
                'sisa' => number_format((float)$plot->sisa, 2),
                'last_harvest_date' => $plot->last_harvest_date ? \Carbon\Carbon::parse($plot->last_harvest_date)->format('d/m/Y') : '-',
                'days_since_harvest' => (int)$plot->days_since_harvest
            ];
        });
    }

    /**
     * Get plot details for edit
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getPlotDetailsForEdit($companycode, $lkhno)
    {
        return DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->orderBy('blok')
            ->orderBy('plot')
            ->get();
    }

    /**
     * Get plot rows with batcharea for luas over-limit validation.
     */
    public function getPlotsWithBatchForValidation($companycode, $lkhno)
    {
        return DB::table('lkhdetailplot as ldp')
            ->leftJoin('batch as b', 'ldp.batchid', '=', 'b.id')
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->whereNotNull('ldp.plot')
            ->select(['ldp.plot', 'ldp.batchno', 'ldp.luashasil', 'b.batcharea'])
            ->get();
    }

    /**
     * Get worker details for edit
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getWorkerDetailsForEdit($companycode, $lkhno)
    {
        return DB::table('lkhdetailworker as ldw')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('ldw.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode);
            })
            ->where('ldw.companycode', $companycode)
            ->where('ldw.lkhno', $lkhno)
            ->select(['ldw.*', 'tk.nama', 'tk.nik'])
            ->orderBy('ldw.tenagakerjaurutan')
            ->get();
    }

    /**
     * Get material details for edit
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    public function getMaterialDetailsForEdit($companycode, $lkhno)
    {
        return DB::table('lkhdetailmaterial as ldm')
            ->leftJoin('herbisida as h', function($join) use ($companycode) {
                $join->on('ldm.itemcode', '=', 'h.itemcode')
                    ->where('h.companycode', '=', $companycode);
            })
            ->where('ldm.companycode', $companycode)
            ->where('ldm.lkhno', $lkhno)
            ->select([
                'ldm.id',
                'ldm.plot',
                'ldm.itemcode',
                'ldm.qtyditerima',
                'ldm.qtysisa', 
                'ldm.qtydigunakan',
                'ldm.keterangan',
                'ldm.inputby',
                'ldm.createdat',
                'ldm.updatedat',
                'h.itemname',
                'h.measure as satuan'
            ])
            ->orderBy('ldm.plot')
            ->orderBy('ldm.itemcode')
            ->get();
    }

    /**
     * Update LKH header
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param array $data
     * @return int
     */
    public function updateHeader($companycode, $lkhno, array $data)
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update($data);
    }

    /**
     * Replace plot details (delete + insert)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param array $rows
     * @return void
     */
    public function replacePlotDetails($companycode, $lkhno, array $rows)
    {
        // Delete existing
        DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
        
        if (empty($rows)) {
            return;
        }
        
        // Insert new
        DB::table('lkhdetailplot')->insert($rows);
    }

    /**
     * Replace worker details (delete + insert)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param array $rows
     * @return void
     */
    public function replaceWorkerDetails($companycode, $lkhno, array $rows)
    {
        // Delete existing
        DB::table('lkhdetailworker')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
        
        if (empty($rows)) {
            return;
        }
        
        // Insert new
        DB::table('lkhdetailworker')->insert($rows);
    }

    /**
     * Replace material details (delete + insert)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param array $rows
     * @return void
     */
    public function replaceMaterialDetails($companycode, $lkhno, array $rows)
    {
        // Delete existing
        DB::table('lkhdetailmaterial')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->delete();
        
        if (empty($rows)) {
            return;
        }
        
        // Insert new
        DB::table('lkhdetailmaterial')->insert($rows);
    }

    /**
     * Submit LKH (update submission flags)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param array $data
     * @return int
     */
    public function submitLkh($companycode, $lkhno, array $data)
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update($data);
    }

    /**
     * Get LKH for validation (check issubmit)
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getForValidation($companycode, $lkhno)
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->select(['lkhno', 'issubmit', 'status', 'activitycode'])
            ->first();
    }

    /**
     * Get LKH approval detail with all approval
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getLkhApprovalDetail($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('user as u1', 'h.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'h.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'h.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'h.approval1idjabatan', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'h.approval2idjabatan', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'h.approval3idjabatan', '=', 'j3.idjabatan')
            ->leftJoin(DB::raw('(
                SELECT 
                    lkhno,
                    companycode,
                    GROUP_CONCAT(DISTINCT CONCAT(blok, "-", plot) ORDER BY blok, plot SEPARATOR ", ") as location
                FROM lkhdetailplot
                WHERE companycode = "' . $companycode . '"
                GROUP BY lkhno, companycode
            ) as plots'), function($join) {
                $join->on('h.lkhno', '=', 'plots.lkhno')
                    ->on('h.companycode', '=', 'plots.companycode');
            })
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                'h.jumlahapproval',
                'h.approval1idjabatan',
                'h.approval2idjabatan', 
                'h.approval3idjabatan',
                'h.approval1flag',
                'h.approval2flag',
                'h.approval3flag',
                'h.approval1date',
                'h.approval2date',
                'h.approval3date',
                'u1.name as approval1_user_name',
                'u2.name as approval2_user_name',
                'u3.name as approval3_user_name',
                'j1.namajabatan as jabatan1_name',
                'j2.namajabatan as jabatan2_name',
                'j3.namajabatan as jabatan3_name',
                'plots.location'
            ])
            ->first();
    }
}