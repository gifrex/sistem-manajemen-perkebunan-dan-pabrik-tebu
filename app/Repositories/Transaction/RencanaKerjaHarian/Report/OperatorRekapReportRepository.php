<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Report;

use Illuminate\Support\Facades\DB;

/**
 * OperatorRekapReportRepository
 * 
 * Handles all database queries for Operator Rekap Report (All operators summary).
 * Now UNION: LKH (kendaraan kerja) + SJS (kendaraan supply)
 */
class OperatorRekapReportRepository
{
    public function getAllOperatorsWithActivities($companycode, $date)
    {
        // ---- SOURCE 1: LKH (Kendaraan Kerja) ----
        $lkhQuery = DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function ($join) {
                $join->on('lk.lkhno', '=', 'lh.lkhno')
                    ->on('lk.companycode', '=', 'lh.companycode');
            })
            ->join('tenagakerja as tk', function ($join) use ($companycode) {
                $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3);
            })
            ->join('kendaraan as k', function ($join) use ($companycode) {
                $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', '=', $companycode);
            })
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            ->leftJoin(DB::raw('(
                SELECT 
                    lkhno,
                    companycode,
                    GROUP_CONCAT(DISTINCT CONCAT(blok, "-", plot) ORDER BY blok, plot SEPARATOR ", ") as plots_display,
                    SUM(luasrkh) as total_luas_rencana,
                    SUM(luashasil) as total_luas_hasil
                FROM lkhdetailplot
                GROUP BY lkhno, companycode
            ) as ldp'), function ($join) {
                $join->on('lh.lkhno', '=', 'ldp.lkhno')
                    ->on('lh.companycode', '=', 'ldp.companycode');
            })
            ->leftJoin('orderbbmhdr as obh', function ($join) {
                $join->on('obh.sourceno', '=', 'lh.lkhno')
                    ->on('obh.companycode', '=', 'lh.companycode')
                    ->where('obh.sourcetype', '=', 'LKH');
            })
            ->leftJoin('orderbbmlst as obl', function ($join) {
                $join->on('obl.orderbbmhdrid', '=', 'obh.id')
                    ->on('obl.nokendaraan', '=', 'lk.nokendaraan');
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->select([
                'tk.tenagakerjaid',
                'tk.nama as operator_name',
                'k.nokendaraan',
                'k.jenis as vehicle_type',
                'lk.jammulai',
                'lk.jamselesai',
                DB::raw('TIMEDIFF(lk.jamselesai, lk.jammulai) as durasi_kerja'),
                DB::raw('a.activityname as kegiatan'),
                DB::raw('lh.activitycode as kegiatan_code'),
                DB::raw('ldp.plots_display as plots_display'),
                DB::raw('ldp.total_luas_rencana as luas_rencana'),
                DB::raw('ldp.total_luas_hasil as hasil_value'),
                DB::raw("'ha' as hasil_satuan"),
                'obl.solarrequested as solar_requested',
                'obl.solarreal as solar_real',
                DB::raw("lh.lkhno as source_no"),
                DB::raw("'LKH' as source_type"),
            ]);

        // ---- SOURCE 2: SJS (Kendaraan Supply) ----
        $sjsQuery = DB::table('kendaraansupply as ks')
            ->join('tenagakerja as tk', function ($join) use ($companycode) {
                $join->on('ks.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3);
            })
            ->join('kendaraan as k', function ($join) use ($companycode) {
                $join->on('ks.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', '=', $companycode);
            })
            ->leftJoin('orderbbmhdr as obh', function ($join) {
                $join->on('obh.sourceno', '=', 'ks.sjsno')
                    ->on('obh.companycode', '=', 'ks.companycode')
                    ->where('obh.sourcetype', '=', 'SJS');
            })
            ->leftJoin('orderbbmlst as obl', function ($join) {
                $join->on('obl.orderbbmhdrid', '=', 'obh.id')
                    ->on('obl.nokendaraan', '=', 'ks.nokendaraan');
            })
            ->where('ks.companycode', $companycode)
            ->whereDate('ks.sjsdate', $date)
            ->select([
                'tk.tenagakerjaid',
                'tk.nama as operator_name',
                'k.nokendaraan',
                'k.jenis as vehicle_type',
                'ks.jammulai',
                'ks.jamselesai',
                DB::raw('TIMEDIFF(ks.jamselesai, ks.jammulai) as durasi_kerja'),
                DB::raw('ks.keteranganaktivitas as kegiatan'),
                DB::raw("'' as kegiatan_code"),
                DB::raw('ks.tujuan as plots_display'),
                DB::raw('NULL as luas_rencana'),
                DB::raw('ks.jumlahrit as hasil_value'),
                DB::raw("'rit' as hasil_satuan"),
                'obl.solarrequested as solar_requested',
                'obl.solarreal as solar_real',
                DB::raw("ks.sjsno as source_no"),
                DB::raw("'SJS' as source_type"),
            ]);

        // ---- UNION + ORDER ----
        return $lkhQuery
            ->unionAll($sjsQuery)
            ->orderBy('operator_name')
            ->orderBy('jammulai')
            ->get();
    }

    public function getStandByVehicles($companycode, $date)
    {
        // Kendaraan aktif di company yang tidak ada di LKH maupun SJS pada tanggal tersebut
        $nokendaraanLkh = DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function ($join) {
                $join->on('lk.lkhno', '=', 'lh.lkhno')
                    ->on('lk.companycode', '=', 'lh.companycode');
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->pluck('lk.nokendaraan');

        $nokendaraanSjs = DB::table('kendaraansupply as ks')
            ->where('ks.companycode', $companycode)
            ->whereDate('ks.sjsdate', $date)
            ->pluck('ks.nokendaraan');

        $usedVehicles = $nokendaraanLkh->merge($nokendaraanSjs)->unique()->values();

        return DB::table('kendaraan as k')
            ->leftJoin('tenagakerja as tk', 'k.idtenagakerja', '=', 'tk.tenagakerjaid')
            ->where('k.companycode', $companycode)
            ->where('k.isactive', 1)
            ->when($usedVehicles->isNotEmpty(), function ($q) use ($usedVehicles) {
                $q->whereNotIn('k.nokendaraan', $usedVehicles);
            })
            ->select(['k.nokendaraan', 'k.jenis', 'k.statuskendaraan', 'k.idtenagakerja', 'tk.nama as operator_name'])
            ->orderBy('k.jenis')
            ->orderBy('k.nokendaraan')
            ->get();
    }
}