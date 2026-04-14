<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Shared;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AbsenRepository
 * 
 * Handles ALL absen-related queries (APPROVED only).
 * RULE: Only query APPROVED absen (h.approvalstatus = '1').
 * 
 * UPDATED: Changed from l.approval_status to h.approvalstatus
 */
class AbsenRepository
{
    /**
     * Get attendance data (APPROVED only)
     * Returns basic worker info: tenagakerjaid, nama, gender, jenistenagakerja, mandorid
     * 
     * @param string $companycode
     * @param string|Carbon $date
     * @param string|null $mandorId
     * @return \Illuminate\Support\Collection
     */
    public function getAttendanceData($companycode, $date, $mandorId = null)
    {
        $query = DB::table('absenhdr as h')
            ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
            ->join('tenagakerja as t', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                    ->where('t.companycode', '=', $companycode)
                    ->where('t.isactive', '=', 1);
            })
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->where('h.companycode', $companycode)
            ->whereDate('h.uploaddate', Carbon::parse($date));

        if ($mandorId) {
            $query->where('h.mandorid', $mandorId);
        }

        return $query->select([
                'h.absenno',
                'h.mandorid',
                'h.approvalstatus',
                'm.name as mandor_name',
                'l.tenagakerjaid',
                't.nama',
                't.gender',
                't.jenistenagakerja',
                DB::raw('TIME(l.absenmasuk) as jam_masuk'),
            ])
            ->get();
    }

    /**
     * Get list of mandors who have absen on specific date
     * Used for frontend dropdown filter
     * 
     * @param string $companycode
     * @param string|Carbon $date
     * @return \Illuminate\Support\Collection
     */
    public function getMandorListByDate($companycode, $date)
    {
        return DB::table('absenhdr as h')
            ->join('user as u', 'h.mandorid', '=', 'u.userid')
            ->where('h.companycode', $companycode)
            ->whereDate('h.uploaddate', Carbon::parse($date))
            ->select('h.mandorid', 'u.name as mandor_name')
            ->distinct()
            ->get();
    }

    /**
     * Get full absen data with worker details + time
     * Used in forms (includes absenmasuk time, keterangan)
     * 
     * @param string $companycode
     * @param string|Carbon $date
     * @param string|null $mandorId
     * @return \Illuminate\Support\Collection
     */
    public function getDataAbsenFull($companycode, $date, $mandorId = null)
    {
        $query = DB::table('absenhdr as h')
            ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
            ->join('tenagakerja as t', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                    ->where('t.companycode', '=', $companycode)
                    ->where('t.isactive', '=', 1);
            })
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('jenistenagakerja as jtk', 't.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->where('h.companycode', $companycode)
            ->where('h.approvalstatus', '1'); // FIXED: Changed from l.approval_status to h.approvalstatus

        if ($date) {
            $query->whereDate('h.uploaddate', Carbon::parse($date));
        }

        if ($mandorId) {
            $query->where('h.mandorid', $mandorId);
        }

        return $query->select([
                'h.absenno',
                'h.companycode',
                'h.mandorid',
                'h.uploaddate as absentime',
                'l.tenagakerjaid as id',
                'l.absenmasuk',
                'l.keterangan',
                't.nama',
                't.gender',
                't.jenistenagakerja',
                'jtk.nama as jenistenagakerja_nama',
                'm.name as mandor_nama',
                DB::raw('TIME(l.absenmasuk) as jam_absen')
            ])
            ->orderBy('h.uploaddate')
            ->get();
    }
}