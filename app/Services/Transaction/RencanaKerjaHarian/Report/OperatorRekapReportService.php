<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Report;

use App\Repositories\Transaction\RencanaKerjaHarian\Report\OperatorRekapReportRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Carbon\Carbon;

/**
 * OperatorRekapReportService
 * 
 * Orchestrates Operator Rekap report (UNION LKH + SJS).
 */
class OperatorRekapReportService
{
    protected $operatorRekapRepo;
    protected $masterDataRepo;

    public function __construct(
        OperatorRekapReportRepository $operatorRekapRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->operatorRekapRepo = $operatorRekapRepo;
        $this->masterDataRepo = $masterDataRepo;
    }

    public function buildOperatorRekapReportPayload($companycode, $date)
    {
        $allData = $this->operatorRekapRepo->getAllOperatorsWithActivities($companycode, $date);

        $groupedByOperator = $allData->groupBy('tenagakerjaid');

        $allActivities = [];
        $totalOperators = $groupedByOperator->count();

        // Grand totals
        $totalLuasRencana = 0;
        $totalHasilHa = 0;
        $totalHasilRit = 0;
        $totalSolar = 0;
        $totalDurationMinutes = 0;
        $countLkh = 0;
        $countSjs = 0;

        foreach ($groupedByOperator as $operatorId => $activities) {
            $firstActivity = $activities->first();

            foreach ($activities as $activity) {
                $isLkh = $activity->source_type === 'LKH';
                $isSjs = $activity->source_type === 'SJS';

                if ($isLkh) $countLkh++;
                if ($isSjs) $countSjs++;

                // Jam & Durasi
                $jamMulai = $activity->jammulai && $activity->jammulai !== '00:00:00'
                    ? substr($activity->jammulai, 0, 5)
                    : null;

                $jamSelesai = $activity->jamselesai && $activity->jamselesai !== '00:00:00'
                    ? substr($activity->jamselesai, 0, 5)
                    : null;

                $durasiKerja = $activity->durasi_kerja && $activity->durasi_kerja !== '00:00:00'
                    ? substr($activity->durasi_kerja, 0, 5)
                    : null;

                // Luas Rencana (LKH only)
                $luasRencana = $isLkh && $activity->luas_rencana && (float)$activity->luas_rencana > 0
                    ? number_format((float)$activity->luas_rencana, 2)
                    : null;

                // Hasil: hektar (LKH) atau rit (SJS)
                $hasilValue = $activity->hasil_value && (float)$activity->hasil_value > 0
                    ? (float)$activity->hasil_value
                    : null;

                $hasilFormatted = null;
                if ($hasilValue !== null) {
                    $hasilFormatted = $isLkh
                        ? number_format($hasilValue, 2)
                        : number_format($hasilValue, 0);
                }

                // Solar (prioritas: real > requested)
                $solarReal = $activity->solar_real && (float)$activity->solar_real > 0
                    ? (float)$activity->solar_real
                    : null;

                $solarRequested = $activity->solar_requested && (float)$activity->solar_requested > 0
                    ? (float)$activity->solar_requested
                    : null;

                $solarValue = $solarReal ?? $solarRequested;
                $solarLiter = $solarValue ? number_format($solarValue, 1) : null;

                $allActivities[] = [
                    'operator_name' => $firstActivity->operator_name,
                    'nokendaraan' => $activity->nokendaraan,
                    'vehicle_type' => $activity->vehicle_type,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'durasi_kerja' => $durasiKerja,
                    'kegiatan' => $activity->kegiatan ?: '-',
                    'kegiatan_code' => $activity->kegiatan_code ?: null,
                    'plots_display' => $activity->plots_display ?: '-',
                    'luas_rencana' => $luasRencana,
                    'hasil_value' => $hasilFormatted,
                    'hasil_satuan' => $activity->hasil_satuan,
                    'solar_requested' => $solarRequested ? number_format($solarRequested, 1) : null,
                    'solar_real' => $solarReal ? number_format($solarReal, 1) : null,
                    'solar_liter' => $solarLiter,
                    'source_type' => $activity->source_type,
                    'source_no' => $activity->source_no,
                ];

                // Grand totals
                if ($isLkh && $activity->luas_rencana) {
                    $totalLuasRencana += (float)$activity->luas_rencana;
                }

                if ($hasilValue !== null) {
                    if ($isLkh) {
                        $totalHasilHa += $hasilValue;
                    } else {
                        $totalHasilRit += $hasilValue;
                    }
                }

                if ($solarValue) {
                    $totalSolar += $solarValue;
                }

                if ($durasiKerja) {
                    $parts = explode(':', $durasiKerja);
                    $totalDurationMinutes += ((int)$parts[0] * 60) + (int)$parts[1];
                }
            }
        }

        $totalHours = floor($totalDurationMinutes / 60);
        $totalMinutes = $totalDurationMinutes % 60;

        $companyInfo = $this->masterDataRepo->getCompanyInfo($companycode);

        $standByVehicles = $this->operatorRekapRepo->getStandByVehicles($companycode, $date);
        $standByList = $standByVehicles->map(fn($v) => [
            'nokendaraan'     => $v->nokendaraan,
            'jenis'           => $v->jenis,
            'statuskendaraan' => $v->statuskendaraan,
            'operator_name'   => $v->operator_name,
        ])->values()->all();

        return [
            'success' => true,
            'date' => $date,
            'date_formatted' => Carbon::parse($date)->format('d F Y'),
            'company_info' => $companyInfo ? "{$companyInfo->companycode} - {$companyInfo->name}" : $companycode,
            'all_activities' => $allActivities,
            'standby_vehicles' => $standByList,
            'grand_totals' => [
                'total_operators' => $totalOperators,
                'total_activities' => count($allActivities),
                'count_lkh' => $countLkh,
                'count_sjs' => $countSjs,
                'total_luas_rencana' => $totalLuasRencana,
                'total_luas_rencana_formatted' => $totalLuasRencana > 0 ? number_format($totalLuasRencana, 2) : null,
                'total_hasil_ha' => $totalHasilHa,
                'total_hasil_ha_formatted' => $totalHasilHa > 0 ? number_format($totalHasilHa, 2) : null,
                'total_hasil_rit' => $totalHasilRit,
                'total_hasil_rit_formatted' => $totalHasilRit > 0 ? number_format($totalHasilRit, 0) : null,
                'total_solar' => $totalSolar,
                'total_solar_formatted' => $totalSolar > 0 ? number_format($totalSolar, 1) : null,
                'total_duration_minutes' => $totalDurationMinutes,
                'total_duration_hours' => $totalHours,
                'total_duration_minutes_remainder' => $totalMinutes,
            ],
            'generated_at' => now()->format('d/m/Y H:i:s')
        ];
    }
}