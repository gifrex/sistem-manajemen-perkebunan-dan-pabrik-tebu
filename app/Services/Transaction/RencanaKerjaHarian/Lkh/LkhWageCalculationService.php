<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Lkh;

use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * LkhWageCalculationService
 * 
 * Centralized wage calculation logic for both Web and Mobile
 * Reuses the same logic as mobile API (PerhitunganUpahApiMobile)
 */
class LkhWageCalculationService
{
    protected $masterDataRepo;

    public function __construct(MasterDataRepository $masterDataRepo)
    {
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Calculate wages for HARIAN workers
     * 
     * @param array $workersData
     * @param string $companycode
     * @param string $activitycode
     * @param string $lkhdate
     * @return array
     */
    public function calculateHarianWages($workersData, $companycode, $activitycode, $lkhdate)
    {
        $results = [];
        
        // Get activity group for wage lookup
        $activityGroup = $this->getActivityGroup($activitycode);
        
        foreach ($workersData as $worker) {
            $tenagakerjaid = $worker['tenagakerjaid'];
            
            // Calculate total work hours
            $totalJamKerja = $this->calculateWorkHours(
                $worker['jammasuk'], 
                $worker['jamselesai']
            );
            
            // Calculate wage breakdown
            $wageData = $this->calculateWorkerWage(
                $companycode,
                $activityGroup,
                $lkhdate,
                $totalJamKerja,
                $worker['overtimehours'] ?? 0
            );
            
            // If error (missing wage data)
            if (isset($wageData['error'])) {
                $results['error'] = $wageData['error'];
                return $results;
            }
            
            $results[$tenagakerjaid] = array_merge($wageData, [
                'totaljamkerja' => $totalJamKerja
            ]);
        }
        
        return $results;
    }

    /**
     * Calculate total BORONGAN wage
     * 
     * @param array $plotsData
     * @param string $companycode
     * @param string $activitycode
     * @param string $lkhdate
     * @return array
     */
    public function calculateBoronganWage($plotsData, $companycode, $activitycode, $lkhdate)
    {
        $totalLuas = collect($plotsData)->sum('luashasil');
        
        $rate = $this->masterDataRepo->getBoronganRate($companycode, $activitycode, $lkhdate);
        
        if (!$rate) {
            return [
                'error' => "Tidak ditemukan upah borongan aktif untuk Activity: {$activitycode} pada tanggal " . Carbon::parse($lkhdate)->format('d/m/Y')
            ];
        }
        
        $totalUpah = round($totalLuas * $rate, 2);

        return [
            'total_luas' => round($totalLuas, 2),
            'rate_per_ha' => round($rate, 2),
            'total_upah' => $totalUpah,
        ];
    }

    /**
     * Calculate wage for a single worker
     * 
     * @param string $companycode
     * @param string $activityGroup
     * @param string $lkhdate
     * @param float $totalJamKerja
     * @param float $overtimeHours
     * @return array
     */
    private function calculateWorkerWage($companycode, $activityGroup, $lkhdate, $totalJamKerja, $overtimeHours)
    {
        $dayType = $this->getDayType($lkhdate);
        
        $wageData = [
            'premi' => 0,
            'upahharian' => 0,
            'upahperjam' => 0,
            'upahlembur' => 0,
            'totalupah' => 0
        ];
        
        // Full day (>= 7 hours) - use daily rate
        if ($totalJamKerja >= 7) {
            $dailyRate = $this->getWageRate($companycode, $activityGroup, $dayType, $lkhdate);
            
            if ($dailyRate === null) {
                return [
                    'error' => "Data upah '{$dayType}' untuk Activity Group '{$activityGroup}' pada Company '{$companycode}' belum tersedia. Silakan tambahkan data upah melalui Website terlebih dahulu."
                ];
            }
            
            $wageData['upahharian'] = round($dailyRate, 2);
            
        } else {
            // Part day (< 7 hours) - use hourly rate
            $hourlyRate = $this->getWageRate($companycode, $activityGroup, 'HOURLY', $lkhdate);
            
            if ($hourlyRate === null) {
                return [
                    'error' => "Data upah 'HOURLY' untuk Activity Group '{$activityGroup}' pada Company '{$companycode}' belum tersedia. Silakan tambahkan data upah melalui Website terlebih dahulu."
                ];
            }
            
            $wageData['upahperjam'] = round($hourlyRate, 2);
            $wageData['upahharian'] = round($totalJamKerja * $wageData['upahperjam'], 2);
        }
        
        // Calculate overtime if applicable
        if ($overtimeHours > 0) {
            $overtimeRate = $this->getWageRate($companycode, $activityGroup, 'OVERTIME', $lkhdate);
            
            if ($overtimeRate === null) {
                return [
                    'error' => "Data upah 'OVERTIME' untuk Activity Group '{$activityGroup}' pada Company '{$companycode}' belum tersedia. Silakan tambahkan data upah melalui Website terlebih dahulu."
                ];
            }
            
            $wageData['upahlembur'] = round($overtimeHours * $overtimeRate, 2);
        }
        
        $wageData['totalupah'] = round($wageData['upahharian'] + $wageData['upahlembur'] + $wageData['premi'], 2);
        
        return $wageData;
    }

    /**
     * Get wage rate from database
     */
    private function getWageRate($companycode, $activitygroup, $wagetype, $workDate)
    {
        $workDate = Carbon::parse($workDate)->format('Y-m-d');
        
        $rate = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->where('wagetype', $wagetype)
            ->where('effectivedate', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', $workDate);
            })
            ->orderBy('effectivedate', 'DESC')
            ->value('amount');
        
        return $rate;
    }

    /**
     * Calculate work hours (with break deduction)
     */
    private function calculateWorkHours($jamMasuk, $jamSelesai)
    {
        $start = Carbon::createFromFormat('H:i:s', $jamMasuk);
        $end = Carbon::createFromFormat('H:i:s', $jamSelesai);
        
        // Handle overnight shifts
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        $totalHours = $start->diffInHours($end, false);
        
        // Deduct 1 hour break if work spans 12:00-13:00
        $breakStart = Carbon::createFromFormat('H:i:s', '12:00:00');
        $breakEnd = Carbon::createFromFormat('H:i:s', '13:00:00');
        
        $breakDeduction = 0;
        
        if ($start->lt($breakEnd) && $end->gt($breakStart)) {
            $overlapStart = $start->gt($breakStart) ? $start : $breakStart;
            $overlapEnd = $end->lt($breakEnd) ? $end : $breakEnd;
            $breakDeduction = $overlapStart->diffInHours($overlapEnd, false);
        }
        
        return max(0, $totalHours - $breakDeduction);
    }

    /**
     * Get day type (DAILY, WEEKEND_SATURDAY, WEEKEND_SUNDAY)
     */
    private function getDayType($workDate)
    {
        $dayOfWeek = Carbon::parse($workDate)->dayOfWeek;
        
        if ($dayOfWeek === Carbon::SATURDAY) {
            return 'WEEKEND_SATURDAY';
        } elseif ($dayOfWeek === Carbon::SUNDAY) {
            return 'WEEKEND_SUNDAY';
        }
        
        return 'DAILY';
    }

    /**
     * Get activity group from activity code
     */
    private function getActivityGroup($activitycode)
    {
        $activitygroup = DB::table('activity')
            ->where('activitycode', $activitycode)
            ->value('activitygroup');
        
        if ($activitygroup) {
            return $activitygroup;
        }
        
        // Fallback: extract from code (e.g., "4.1.1" -> "IV")
        if (preg_match('/^([IVX]+)/', $activitycode, $matches)) {
            return $matches[1];
        }
        
        return 'V'; // Default fallback
    }
}