<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WageCalculationService
{
    public function calculateWorkerWage($companycode, $activitycode, $jenistenagakerja, $workDate, $workerData, $plotData = [])
    {
        try {
            $activitygroup = $this->getActivityGroup($activitycode);
            
            if (!$activitygroup) {
                throw new \Exception("Activity group not found for activity: {$activitycode}");
            }

            if ($jenistenagakerja == 1 || $jenistenagakerja == 3) {
                return $this->calculateHarianWage($companycode, $activitygroup, $workDate, $workerData);
            }

            throw new \Exception("Invalid jenistenagakerja: {$jenistenagakerja}. Use calculateBoronganWageTotal for borongan");

        } catch (\Exception $e) {
            Log::error("Wage calculation error", [
                'companycode' => $companycode,
                'activitycode' => $activitycode,
                'jenistenagakerja' => $jenistenagakerja,
                'error' => $e->getMessage()
            ]);

            return $this->getErrorWageResult($e->getMessage());
        }
    }

    public function calculateBoronganWageTotal($companycode, $activitycode, $workDate, $plotsData)
    {
        try {
            $totalArea = collect($plotsData)->sum('luashasil');
            $boronganRate = $this->getBoronganRate($companycode, $activitycode, $workDate);
            
            if ($boronganRate === 0) {
                Log::warning("No borongan rate found for activity", [
                    'companycode' => $companycode,
                    'activitycode' => $activitycode,
                    'workDate' => $workDate
                ]);
            }
            
            $totalBorongan = $totalArea * $boronganRate;
            
            return [
                'success' => true,
                'calculation_method' => 'borongan_total',
                'total_area' => $totalArea,
                'rate_per_ha' => $boronganRate,
                'upahharian' => 0,
                'upahperjam' => 0,
                'upahlembur' => 0,
                'premi' => 0,
                'upahborongan' => $totalBorongan,
                'totalupah' => $totalBorongan,
                'notes' => "Borongan: {$totalArea} ha × Rp " . number_format($boronganRate, 0, ',', '.') . " = Rp " . number_format($totalBorongan, 0, ',', '.')
            ];
            
        } catch (\Exception $e) {
            Log::error("Borongan wage calculation error", [
                'companycode' => $companycode,
                'activitycode' => $activitycode,
                'error' => $e->getMessage()
            ]);
            
            return $this->getErrorWageResult($e->getMessage());
        }
    }

    private function calculateHarianWage($companycode, $activitygroup, $workDate, $workerData)
    {
        $dayType = $this->getDayType($workDate);
        $wageRates = $this->getHarianWageRates($companycode, $activitygroup, $workDate);
        
        $totalHours = $workerData['totaljamkerja'] ?? 0;
        $overtimeHours = $workerData['overtimehours'] ?? 0;
        $premi = $workerData['premi'] ?? 0;
        
        if ($this->isFullDay($totalHours, $dayType)) {
            $baseWage = $this->getFullDayRate($wageRates, $dayType);
            $calculationMethod = 'full_day';
        } else {
            $hourlyRate = $wageRates['HOURLY'] ?? 0;
            $baseWage = $hourlyRate * $totalHours;
            $calculationMethod = 'hourly';
        }
        
        $overtimeRate = $wageRates['OVERTIME'] ?? 0;
        $overtimeWage = $overtimeHours * $overtimeRate;
        
        $totalUpah = $baseWage + $overtimeWage + $premi;
        
        return [
            'success' => true,
            'calculation_method' => $calculationMethod,
            'day_type' => $dayType,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'upahharian' => $baseWage,
            'upahperjam' => $calculationMethod === 'hourly' ? ($wageRates['HOURLY'] ?? 0) : 0,
            'upahlembur' => $overtimeWage,
            'premi' => $premi,
            'upahborongan' => 0,
            'totalupah' => $totalUpah,
            'rates_used' => $wageRates,
            'notes' => $this->generateHarianNotes($calculationMethod, $dayType, $totalHours, $overtimeHours)
        ];
    }

    private function getHarianWageRates($companycode, $activitygroup, $workDate)
    {
        $workDate = Carbon::parse($workDate)->format('Y-m-d');
        
        $rates = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->where('effectivedate', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', $workDate);
            })
            ->orderBy('effectivedate', 'DESC')
            ->get();
        
        $wageRates = [];
        
        foreach ($rates as $rate) {
            if (!isset($wageRates[$rate->wagetype])) {
                $wageRates[$rate->wagetype] = $rate->amount;
            }
        }
        
        return $wageRates;
    }

    private function getBoronganRate($companycode, $activitycode, $workDate)
    {
        $workDate = Carbon::parse($workDate)->format('Y-m-d');
        
        $rate = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $activitycode)
            ->where('effectivedate', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', $workDate);
            })
            ->orderBy('effectivedate', 'DESC')
            ->value('amount');
        
        return $rate ?? 0;
    }

    public function calculateBulkWages($companycode, $activitycode, $jenistenagakerja, $workDate, $workersData, $plotsData = [])
    {
        if ($jenistenagakerja == 2) {
            $totalWage = $this->calculateBoronganWageTotal($companycode, $activitycode, $workDate, $plotsData);
            
            return [
                'success' => $totalWage['success'],
                'workers' => [],
                'summary' => [
                    'total_workers' => count($workersData),
                    'success_count' => $totalWage['success'] ? 1 : 0,
                    'error_count' => $totalWage['success'] ? 0 : 1,
                    'total_wages' => $totalWage['totalupah']
                ],
                'borongan_detail' => $totalWage
            ];
        }
        
        $results = [];
        $totalWages = 0;
        $successCount = 0;
        $errorCount = 0;

        foreach ($workersData as $workerData) {
            $result = $this->calculateWorkerWage(
                $companycode, 
                $activitycode, 
                $jenistenagakerja, 
                $workDate, 
                $workerData, 
                $plotsData
            );
            
            $results[] = array_merge($workerData, $result);
            
            if ($result['success']) {
                $totalWages += $result['totalupah'];
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'success' => $errorCount === 0,
            'workers' => $results,
            'summary' => [
                'total_workers' => count($workersData),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_wages' => $totalWages
            ]
        ];
    }

    private function getActivityGroup($activitycode)
    {
        return DB::table('activity')
            ->where('activitycode', $activitycode)
            ->value('activitygroup');
    }

    private function getDayType($workDate)
    {
        $date = Carbon::parse($workDate);
        
        if ($date->isSaturday()) {
            return 'WEEKEND_SATURDAY';
        } elseif ($date->isSunday()) {
            return 'WEEKEND_SUNDAY';
        }
        
        return 'DAILY';
    }

    private function isFullDay($totalHours, $dayType)
    {
        if (in_array($dayType, ['WEEKEND_SATURDAY', 'WEEKEND_SUNDAY'])) {
            return $totalHours >= 8;
        }
        
        return $totalHours >= 8;
    }

    private function getFullDayRate($wageRates, $dayType)
    {
        switch ($dayType) {
            case 'WEEKEND_SATURDAY':
                return $wageRates['WEEKEND_SATURDAY'] ?? $wageRates['DAILY'] ?? 0;
            case 'WEEKEND_SUNDAY':
                return $wageRates['WEEKEND_SUNDAY'] ?? $wageRates['DAILY'] ?? 0;
            default:
                return $wageRates['DAILY'] ?? 0;
        }
    }

    private function generateHarianNotes($calculationMethod, $dayType, $totalHours, $overtimeHours)
    {
        $notes = [];
        
        if ($calculationMethod === 'full_day') {
            $notes[] = "Full day rate ({$dayType})";
        } else {
            $notes[] = "Hourly rate: {$totalHours} hours";
        }
        
        if ($overtimeHours > 0) {
            $notes[] = "Overtime: {$overtimeHours} hours";
        }
        
        return implode(', ', $notes);
    }

    private function getErrorWageResult($errorMessage)
    {
        return [
            'success' => false,
            'error' => $errorMessage,
            'upahharian' => 0,
            'upahperjam' => 0,
            'upahlembur' => 0,
            'premi' => 0,
            'upahborongan' => 0,
            'totalupah' => 0,
            'notes' => 'Error in wage calculation'
        ];
    }

    public function validateWageParameters($params)
    {
        $errors = [];
        
        if (empty($params['companycode'])) {
            $errors[] = 'Company code is required';
        }
        
        if (empty($params['activitycode'])) {
            $errors[] = 'Activity code is required';
        }
        
        if (!in_array($params['jenistenagakerja'], [1, 2, 3])) {
            $errors[] = 'Invalid jenis tenaga kerja';
        }
        
        if (empty($params['workDate'])) {
            $errors[] = 'Work date is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}