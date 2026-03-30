<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Utility;

use App\Repositories\Transaction\RencanaKerjaHarian\Shared\AbsenRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterlistBatchRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\RkhRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * RkhUtilityService
 * 
 * Orchestrates utility operations.
 * RULE: No DB queries. Only orchestration.
 */
class RkhUtilityService
{
    protected $absenRepo;
    protected $batchRepo;
    protected $rkhRepo;

    public function __construct(
        AbsenRepository $absenRepo,
        MasterlistBatchRepository $batchRepo,
        RkhRepository $rkhRepo
    ) {
        $this->absenRepo = $absenRepo;
        $this->batchRepo = $batchRepo;
        $this->rkhRepo = $rkhRepo;
    }

    /**
     * Get attendance data by date
     * 
     * @param string $companycode
     * @param string $date
     * @param string|null $mandorId
     * @return array
     */
    public function getAttendancePayload($companycode, $date, $mandorId = null)
    {
        $absenData = $this->absenRepo->getAttendanceData($companycode, $date, $mandorId);
        $mandorList = $this->absenRepo->getMandorListByDate($companycode, $date);

        return [
            'success' => true,
            'data' => $absenData,
            'mandor_list' => $mandorList
        ];
    }

    /**
     * Get plot info with batch and progress data
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $activitycode
     * @return array
     */
    public function getPlotInfo($companycode, $plot, $activitycode, $rkhdate = null)
    {
        // Get plot with active batch
        $plotData = $this->batchRepo->getPlotWithActiveBatch($companycode, $plot);
        if (!$plotData) {
            return [
                'success' => false,
                'message' => 'Plot tidak ditemukan di masterlist / tidak aktif'
            ];
        }
        
        // Calculate work progress
        $luasPlot = (float) ($plotData->batcharea ?? 0);

        // Detect panen activities
        $panenActivities = ['4.3.3', '4.4.3', '4.5.2', '2.2.2a', '2.2.2b'];
        $isPanenActivity = in_array($activitycode, $panenActivities);

        // Panen activities share a single saldo — sum across all panen activity codes
        $queryActivitycode = $isPanenActivity ? $panenActivities : $activitycode;

        $beforeDate = $rkhdate ?? now()->format('Y-m-d');

        $totalSudahDikerjakan = $this->batchRepo->getTotalApprovedWorkByPlotActivityBeforeDate(
            $companycode,
            $plot,
            $queryActivitycode,
            $beforeDate,
            $plotData->activebatchno
        );
        $luasSisa = max(0, $luasPlot - $totalSudahDikerjakan);
        $batchInfo = null;
        
        // If panen, calculate batch progress (STC)
        if ($isPanenActivity && $plotData->activebatchno) {
            $batchInfo = $this->buildPanenBatchInfo($companycode, $plotData);
        }
        
        // Get last activity info
        $tanggalActivity = null;
        $lastActivityInfo = $this->batchRepo->getLastApprovedActivityInfoForPlot($companycode, $plot);
        if ($lastActivityInfo) {
            $tanggalActivity = Carbon::parse($lastActivityInfo->last_activity_date)->format('d/m/Y');
        }
        
        return [
            'success' => true,
            'plot' => $plot,
            'activitycode' => $activitycode,
            'luasplot' => number_format($luasPlot, 2),
            'totalsudahdikerjakan' => number_format($totalSudahDikerjakan, 2),
            'luassisa' => number_format($luasSisa, 2),
            'tanggal' => $isPanenActivity
                ? ($batchInfo['tanggalpanen'] ?? null)
                : $tanggalActivity,
            'ispanen' => $isPanenActivity,
            'batchinfo' => $batchInfo,
            'blok' => $plotData->blok ?? null,
            'activebatchno' => $plotData->activebatchno,
            'last_activitycode' => $lastActivityInfo->last_activitycode ?? null,
            'last_activityname' => $lastActivityInfo->last_activityname ?? null,
            'last_activity_date' => $lastActivityInfo->last_activity_date ?? null,
        ];
    }

    /**
     * Check outstanding RKH for mandor
     * 
     * @param string $companycode
     * @param string $mandorId
     * @return array
     */
    public function checkOutstandingRkh($companycode, $mandorId)
    {
        $outstandingRKH = $this->rkhRepo->getLatestOutstandingByMandor($companycode, $mandorId);

        if ($outstandingRKH) {
            // Get mandor name
            $mandor = DB::table('user')
                ->where('userid', $mandorId)
                ->first();

            return [
                'success' => false,
                'hasOutstanding' => true,
                'message' => 'Mandor masih memiliki RKH yang belum diselesaikan',
                'details' => [
                    'rkhno' => $outstandingRKH->rkhno,
                    'rkhdate' => Carbon::parse($outstandingRKH->rkhdate)->format('d/m/Y'),
                    'status' => $outstandingRKH->status,
                    'mandor_name' => $mandor->name ?? 'Unknown',
                    'mandor_id' => $mandorId
                ]
            ];
        }

        return [
            'success' => true,
            'hasOutstanding' => false,
            'message' => 'Mandor tidak memiliki RKH outstanding'
        ];
    }

    /**
     * Check if RKH already exists for mandor on specific date
     * 
     * @param string $companycode
     * @param string $mandorId
     * @param string $date
     * @return array
     */
    public function checkDuplicateRkh($companycode, $mandorId, $date)
    {
        try {
            // Use repository to check existing RKH
            $existing = $this->rkhRepo->getRkhByMandorAndDate($companycode, $mandorId, $date);

            if ($existing) {
                return [
                    'exists' => true,
                    'rkhno' => $existing->rkhno,
                    'rkhdate' => Carbon::parse($existing->rkhdate)->format('d/m/Y'),
                    'status' => $existing->status
                ];
            }

            return ['exists' => false];

        } catch (\Exception $e) {
            \Log::error("Error checking duplicate RKH for mandor {$mandorId} on date {$date}: " . $e->getMessage());
            
            return [
                'exists' => false,
                'error' => 'Gagal memeriksa duplikasi RKH'
            ];
        }
    }

    /**
     * Get surat jalan for plot + subkontraktor
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $subkontraktorId
     * @param string $lkhno
     * @return array
     */
    public function getSuratJalanPayload($companycode, $plot, $subkontraktorId, $lkhno)
    {
        // Get LKH date
        $lkhDate = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->value('lkhdate');

        if (!$lkhDate) {
            return [
                'success' => false,
                'message' => 'LKH tidak ditemukan'
            ];
        }

        // Get surat jalan list
        $suratJalan = DB::table('suratjalanpos as sj')
            ->leftJoin('timbanganpayload as tp', function($join) use ($companycode) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                    ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->leftJoin('subkontraktor as sk', function($join) use ($companycode) {
                $join->on('sj.namasubkontraktor', '=', 'sk.id')
                    ->where('sk.companycode', '=', $companycode);
            })
            ->where('sj.companycode', $companycode)
            ->where('sj.plot', $plot)
            ->where('sj.namasubkontraktor', $subkontraktorId)
            ->whereDate('sj.tanggalcetakpossecurity', $lkhDate)
            ->select([
                'sj.suratjalanno',
                'sj.tanggalcetakpossecurity',
                'sk.namasubkontraktor',
                DB::raw('CASE WHEN tp.suratjalanno IS NULL THEN "Pending" ELSE "Sudah Timbang" END as status')
            ])
            ->orderBy('sj.tanggalcetakpossecurity', 'desc')
            ->get();

        return [
            'success' => true,
            'surat_jalan' => $suratJalan,
            'subkontraktor_nama' => $suratJalan->first()->namasubkontraktor ?? null,
            'total' => $suratJalan->count()
        ];
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Build panen batch info
     */
    private function buildPanenBatchInfo($companycode, $plotData)
    {
        $activeBatchNo = $plotData->activebatchno;

        $totalSudahPanen = $this->batchRepo->getTotalApprovedHarvestByBatchUntilDate(
            $companycode,
            $activeBatchNo,
            now()->format('Y-m-d')
        );

        $batchSisa = (float) $plotData->batcharea - $totalSudahPanen;

        $batchInfo = [
            'batchno' => $activeBatchNo,
            'lifecyclestatus' => $plotData->lifecyclestatus ?? '-',
            'tanggalpanen' => $plotData->tanggalpanen
                ? Carbon::parse($plotData->tanggalpanen)->format('d/m/Y')
                : 'Belum Panen',
            'batcharea' => number_format((float) $plotData->batcharea, 2),
            'totalsudahpanen' => number_format($totalSudahPanen, 2),
            'luassisa_batch' => number_format($batchSisa, 2),
        ];

        // Check ZPK date
        $lastZpkDate = $this->batchRepo->getLastApprovedZpkDateForPlot($companycode, $plotData->plot);

        if ($lastZpkDate) {
            $zpkDate = Carbon::parse($lastZpkDate);
            $today = Carbon::now();
            $daysGap = (int) $zpkDate->diffInDays($today);
            
            $batchInfo['zpk_date'] = $zpkDate->format('d/m/Y');
            $batchInfo['zpk_days_gap'] = $daysGap;
            
            if ($daysGap >= 25 && $daysGap <= 35) {
                $batchInfo['zpk_status'] = 'ideal';
            } elseif ($daysGap < 25) {
                $batchInfo['zpk_status'] = 'too_early';
            } else {
                $batchInfo['zpk_status'] = 'too_late';
            }
        }

        return $batchInfo;
    }
}