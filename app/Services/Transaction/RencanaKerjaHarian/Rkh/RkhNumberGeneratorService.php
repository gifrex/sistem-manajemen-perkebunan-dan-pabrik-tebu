<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Rkh;

use App\Repositories\Transaction\RencanaKerjaHarian\RkhRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * RkhNumberGeneratorService
 * 
 * Handles RKH number generation logic.
 * Format: RKH[DD][MM][SEQ][YY]
 * Example: RKH140101425 = 14 Dec, sequence 01, year 25
 * 
 * RULE: No DB queries directly, use repository.
 */
class RkhNumberGeneratorService
{
    protected $rkhRepo;

    public function __construct(RkhRepository $rkhRepo)
    {
        $this->rkhRepo = $rkhRepo;
    }

    /**
     * Generate preview RKH number (for create form)
     * No database lock - just preview
     * 
     * @param string $date
     * @param string $companycode
     * @return string
     */
    public function generatePreviewRkhNo($date, $companycode)
    {
        $targetDate = Carbon::parse($date);
        $day = $targetDate->format('d');
        $month = $targetDate->format('m');
        $year = $targetDate->format('y');

        $lastRkh = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', 'like', "RKH{$day}{$month}%{$year}")
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();

        $newNumber = $lastRkh 
            ? str_pad(((int)substr($lastRkh->rkhno, 7, 2)) + 1, 2, '0', STR_PAD_LEFT)
            : '01';
            
        return "RKH{$day}{$month}{$newNumber}{$year}";
    }

    /**
     * Generate unique RKH number with database lock
     * Format: RKH[DD][MM][SEQ][YY]
     * Sequence resets per date
     * 
     * @param string $date
     * @param string $companycode
     * @return string
     * @throws \Exception
     */
    public function generateUniqueRkhNo($date, $companycode)
    {
        return DB::transaction(function () use ($date, $companycode) {
            $carbonDate = Carbon::parse($date);
            $day = $carbonDate->format('d');
            $month = $carbonDate->format('m');
            $year = $carbonDate->format('y');

            // Get last RKH with lock
            $lastRkh = $this->rkhRepo->getLastRkhSequenceForDateWithLock($companycode, $date);

            if ($lastRkh) {
                // Extract sequence from position 8-9 (0-indexed: position 7, length 2)
                $lastNumber = (int)substr($lastRkh->rkhno, 7, 2);
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
                
                \Log::info("RKH sequence increment", [
                    'date' => $carbonDate->format('Y-m-d'),
                    'last_rkhno' => $lastRkh->rkhno,
                    'last_sequence' => $lastNumber,
                    'new_sequence' => $newNumber
                ]);
            } else {
                $newNumber = '01';
                
                \Log::info("RKH first sequence for date", [
                    'date' => $carbonDate->format('Y-m-d'),
                    'new_sequence' => $newNumber
                ]);
            }

            $rkhno = "RKH{$day}{$month}{$newNumber}{$year}";
            
            // Extra safety check
            if ($this->rkhRepo->existsRkhNo($companycode, $rkhno)) {
                \Log::error("DUPLICATE RKH NUMBER DETECTED!", [
                    'rkhno' => $rkhno,
                    'companycode' => $companycode,
                    'date' => $carbonDate->format('Y-m-d')
                ]);
                
                throw new \Exception("Duplicate RKH number: {$rkhno}. Please refresh and try again.");
            }
            
            return $rkhno;
        });
    }
}