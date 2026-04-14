<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Generator;

use App\Services\WageCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LkhGeneratorService
{
    protected $wageCalculationService;
    
    const PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2', '2.2.2a', '2.2.2b'];
    const BSM_ACTIVITY = '4.7';
    const JENIS_HARIAN = 1;
    const JENIS_BORONGAN = 2;
    const JENIS_OPERATOR = 3;
    const JENIS_HELPER = 4;

    public function __construct(WageCalculationService $wageCalculationService = null)
    {
        $this->wageCalculationService = $wageCalculationService ?: new WageCalculationService();
    }

    public function generateLkhFromRkh($rkhno, $companycode = null)
{
    try {
        $companycode = $companycode ?? session('companycode');

        if (!$companycode) {
            throw new \Exception("Company code tidak ditemukan");
        }

        $rkh = DB::table('rkhhdr')
            ->where('rkhno', $rkhno)
            ->where('companycode', $companycode)
            ->first();
            
        if (!$rkh) {
            throw new \Exception("RKH {$rkhno} not found for company {$companycode}");
        }

        if (!$this->isRkhFullyApproved($rkh)) {
            throw new \Exception("RKH {$rkhno} belum fully approved");
        }

        $existingLkh = DB::table('lkhhdr')
            ->where('rkhno', $rkhno)
            ->where('companycode', $companycode)
            ->exists();
        
        if ($existingLkh) {
            throw new \Exception("LKH untuk RKH {$rkhno} (company: {$companycode}) sudah pernah di-generate");
        }

        $rkhActivities = DB::table('rkhlst')
            ->where('rkhno', $rkhno)
            ->where('companycode', $companycode)
            ->get();

        if ($rkhActivities->isEmpty()) {
            throw new \Exception("Tidak ada aktivitas ditemukan untuk RKH {$rkhno}");
        }

        $groupedActivities = $this->groupActivitiesForLkh($rkhActivities);

        $generatedLkh = [];
        $lkhIndex = 1;

        foreach ($groupedActivities as $groupKey => $groupActivities) {
            $firstActivity = $groupActivities->first();
            
            [$activitycode, $jenistenagakerja] = explode('|', $groupKey);
            
            $lkhno = $this->generateLkhNumber($rkh->rkhno, $lkhIndex);
            
            $lkhHeaderResult = $this->createLkhHeader(
                $rkh, 
                $lkhno, 
                $activitycode, 
                $jenistenagakerja, 
                $groupActivities
            );
            
            $plotResult = [];
            $isBsm = ($activitycode === self::BSM_ACTIVITY);
            
            if (!$isBsm) {
                $plotResult = $this->createLkhDetailPlots(
                    $lkhno, 
                    $lkhHeaderResult['lkhhdrid'],
                    $groupActivities, 
                    $rkh->companycode,
                    $activitycode
                );
            }
            
            $kendaraanResult = $this->generateLkhKendaraanRecords(
                $rkh->rkhno,
                $lkhno,
                $lkhHeaderResult['lkhhdrid'],
                $activitycode,
                $rkh->companycode,
                $groupActivities
            );
            
            $isPanen = in_array($activitycode, self::PANEN_ACTIVITIES);
            
            $lkhData = [
                'lkhno' => $lkhno,
                'activitycode' => $activitycode,
                'type' => $isPanen ? 'PANEN' : ($isBsm ? 'BSM' : 'NORMAL'),
                'plots' => $lkhHeaderResult['plots_summary'],
                'plots_count' => count($plotResult),
                'jenistenagakerja' => $jenistenagakerja,
                'jenis_label' => $this->getJenisLabel($jenistenagakerja),
                'total_luas' => $lkhHeaderResult['total_luas'],
                'planned_workers' => $lkhHeaderResult['planned_workers'],
                'kendaraan_count' => $kendaraanResult['total_vehicles'],
                'status' => 'DRAFT'
            ];
            
            if ($isBsm) {
                $lkhData['bsm_status'] = 'WAITING_ANDROID_INPUT';
                $lkhData['bsm_note'] = 'Android will insert BSM records per SJ';
            }
            
            $generatedLkh[] = $lkhData;
            $lkhIndex++;
        }

        Log::info("LKH auto-generated for RKH {$rkhno}", [
            'generated_lkh' => $generatedLkh,
            'total_lkh' => count($generatedLkh)
        ]);

        return [
            'success' => true,
            'message' => 'LKH berhasil di-generate otomatis',
            'generated_lkh' => $generatedLkh,
            'total_lkh' => count($generatedLkh)
        ];

    } catch (\Exception $e) {
        Log::error("Failed to generate LKH for RKH {$rkhno}: " . $e->getMessage(), [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        throw $e;
    }
}

    private function groupActivitiesForLkh($activities)
    {
        return $activities->groupBy(function($item) {
            return $item->activitycode . '|' . $item->jenistenagakerja;
        });
    }

    private function createLkhHeader($rkh, $lkhno, $activitycode, $jenistenagakerja, $activities)
    {
        $approvalData = $this->getApprovalRequirements($rkh->companycode, $activitycode);
        
        $totalLuas = $activities->sum(function($activity) {
            return (float) $activity->luasarea;
        });
        
        $totalWorkersPlanned = $activities->sum('jumlahtenagakerja');
        $plotList = $activities->pluck('plot')->unique()->join(', ');
        
        $lkhHeaderData = array_merge([
            'lkhno' => $lkhno,
            'rkhno' => $rkh->rkhno,
            'rkhhdrid' => $rkh->id,
            'companycode' => $rkh->companycode,
            'activitycode' => $activitycode,
            'mandorid' => $rkh->mandorid,
            'lkhdate' => $rkh->rkhdate,
            'jenistenagakerja' => $jenistenagakerja,
            'totalworkers' => null,
            'totalluasactual' => null,
            'totalhasil' => null,
            'totalsisa' => null,
            'totalupahall' => null,
            'status' => 'EMPTY', 
            'issubmit' => 0,
            'keterangan' => null,
            'inputby' => auth()->user()->userid ?? 'SYSTEM',
            'createdat' => now(),
        ], $approvalData);

        $lkhhdrid = DB::table('lkhhdr')->insertGetId($lkhHeaderData);

        return [
            'success' => true,
            'lkhhdrid' => $lkhhdrid,
            'total_luas' => $totalLuas,
            'planned_workers' => $totalWorkersPlanned,
            'plots_summary' => $plotList
        ];
    }

    private function createLkhDetailPlots($lkhno, $lkhhdrid, $activities, $companycode, $activitycode)
    {
        $plotDetails = [];
        $isPanenActivity = in_array($activitycode, self::PANEN_ACTIVITIES);
        $isBsmActivity = ($activitycode === self::BSM_ACTIVITY);
        
        $activity = DB::table('activity')->where('activitycode', $activitycode)->first();
        $isBlokActivity = $activity ? ($activity->isblokactivity == 1) : false;
        
        foreach ($activities as $activity) {
            $luasArea = (float) $activity->luasarea;
            
            $batchid = null;
            if (!$isBlokActivity && $activity->batchno) {
                $batch = DB::table('batch')
                    ->where('batchno', $activity->batchno)
                    ->where('companycode', $companycode)
                    ->first();
                $batchid = $batch ? $batch->id : null;
            }
            
            $plotDetail = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'lkhhdrid' => $lkhhdrid,
                'blok' => $activity->blok,
                'plot' => $isBlokActivity ? null : $activity->plot,
                'luasrkh' => $isBlokActivity ? null : $luasArea,
                'luashasil' => null,
                'luassisa' => null,
                'batchno' => $isBlokActivity ? null : ($activity->batchno ?? null),
                'batchid' => $batchid,
                'createdat' => now()
            ];
            
            DB::table('lkhdetailplot')->insert($plotDetail);
            $plotDetails[] = $plotDetail;
            
            if ($isBlokActivity) {
                Log::info("Blok activity LKH detail created", [
                    'lkhno' => $lkhno,
                    'blok' => $activity->blok,
                    'plot' => 'NULL (blok activity)',
                    'luasrkh' => 'NULL (blok activity)'
                ]);
            } else {
                Log::info("Plot activity LKH detail created", [
                    'lkhno' => $lkhno,
                    'blok' => $activity->blok,
                    'plot' => $activity->plot,
                    'luasrkh' => $luasArea,
                    'batchid' => $batchid
                ]);
            }
        }
        
        return $plotDetails;
    }

    
    private function generateLkhKendaraanRecords($rkhno, $lkhno, $lkhhdrid, $activitycode, $companycode, $activities)
    {
        try {
            $kendaraanAssignments = DB::table('rkhlstkendaraan')
                ->where('rkhno', $rkhno)
                ->where('activitycode', $activitycode)
                ->where('companycode', $companycode)
                ->orderBy('urutan')
                ->get();
            
            if ($kendaraanAssignments->isEmpty()) {
                return [
                    'success' => true,
                    'total_vehicles' => 0,
                    'records' => []
                ];
            }
            
            $records = [];
            
            foreach ($kendaraanAssignments as $assignment) {
                $kendaraanid = null;
                if ($assignment->nokendaraan) {
                    $kendaraan = DB::table('kendaraan')
                        ->where('nokendaraan', $assignment->nokendaraan)
                        ->where('companycode', $companycode)
                        ->first();
                    $kendaraanid = $kendaraan ? $kendaraan->id : null;
                }
                
                // CHANGED: Hapus hourmeterstart, hourmeterend, solar, status
                // Kolom-kolom tersebut sudah pindah ke tabel orderbbmlst
                $record = [
                    'companycode' => $companycode,
                    'lkhno' => $lkhno,
                    'lkhhdrid' => $lkhhdrid,
                    'nokendaraan' => $assignment->nokendaraan,
                    'kendaraanid' => $kendaraanid,
                    'operatorid' => $assignment->operatorid,
                    'helperid' => $assignment->helperid,
                    'jammulai' => null,
                    'jamselesai' => null,
                    'createdat' => now()
                ];
                
                DB::table('lkhdetailkendaraan')->insert($record);
                $records[] = $record;
            }
            
            return [
                'success' => true,
                'total_vehicles' => $kendaraanAssignments->count(),
                'total_records' => count($records),
                'records' => $records
            ];
            
        } catch (\Exception $e) {
            Log::error("Error generating LKH kendaraan records", [
                'lkhno' => $lkhno,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function generateLkhNumber($rkhno, $index)
    {
        $rkhPart = substr($rkhno, 3);
        return "LKH{$rkhPart}-{$index}";
    }

    private function getJenisLabel($jenistenagakerja)
    {
        switch ($jenistenagakerja) {
            case self::JENIS_HARIAN:
                return 'Harian';
            case self::JENIS_BORONGAN:
                return 'Borongan';
            case self::JENIS_OPERATOR:
                return 'Operator';
            case self::JENIS_HELPER:
                return 'Helper';
            default:
                return 'Unknown';
        }
    }

    private function isRkhFullyApproved($rkh)
    {
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

        switch ($rkh->jumlahapproval) {
            case 1:
                return $rkh->approval1flag === '1';
            case 2:
                return $rkh->approval1flag === '1' && $rkh->approval2flag === '1';
            case 3:
                return $rkh->approval1flag === '1' && 
                       $rkh->approval2flag === '1' && 
                       $rkh->approval3flag === '1';
            default:
                return false;
        }
    }

    private function getApprovalRequirements($companycode, $activitycode)
    {
        $activity = DB::table('activity')->where('activitycode', $activitycode)->first();
        
        if (!$activity || !$activity->activitygroup) {
            return [
                'jumlahapproval' => 0,
                'approval1idjabatan' => null,
                'approval2idjabatan' => null,
                'approval3idjabatan' => null,
            ];
        }

        $approvalSetting = DB::table('approval')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activity->activitygroup)
            ->first();

        if (!$approvalSetting) {
            return [
                'jumlahapproval' => 0,
                'approval1idjabatan' => null,
                'approval2idjabatan' => null,
                'approval3idjabatan' => null,
            ];
        }

        return [
            'jumlahapproval' => $approvalSetting->jumlahapproval ?? 0,
            'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
            'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
            'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
        ];
    }

    public function getBsmSummaryForLkh($lkhno)
    {
        $bsmRecords = DB::table('lkhdetailbsm as bsm')
            ->leftJoin('batch as b', 'bsm.batchno', '=', 'b.batchno')
            ->where('bsm.lkhno', $lkhno)
            ->select([
                'bsm.*',
                'b.lifecyclestatus'
            ])
            ->get();
        
        if ($bsmRecords->isEmpty()) {
            return [
                'total_records' => 0,
                'completed' => 0,
                'pending' => 0,
                'message' => 'No BSM records found. Android will insert records per SJ.',
                'details' => []
            ];
        }
        
        $completed = $bsmRecords->filter(function($record) {
            return $record->nilaibersih !== null && 
                   $record->nilaisegar !== null && 
                   $record->nilaimanis !== null;
        });
        
        $gradeDistribution = $completed->groupBy('grade')->map(function($group) {
            return $group->count();
        })->toArray();
        
        $kodetebangDistribution = $bsmRecords->groupBy('kodetebang')->map(function($group) {
            return $group->count();
        })->toArray();
        
        return [
            'total_records' => $bsmRecords->count(),
            'total_sj' => $bsmRecords->pluck('suratjalanno')->unique()->count(),
            'completed' => $completed->count(),
            'pending' => $bsmRecords->count() - $completed->count(),
            'average_score_overall' => $completed->avg('averagescore'),
            'grade_distribution' => $gradeDistribution,
            'kodetebang_distribution' => $kodetebangDistribution,
            'details' => $bsmRecords->map(function($record) {
                return [
                    'suratjalanno' => $record->suratjalanno,
                    'plot' => $record->plot,
                    'kodetebang' => $record->kodetebang,
                    'batchno' => $record->batchno,
                    'lifecyclestatus' => $record->lifecyclestatus ?? 'N/A',
                    'nilaibersih' => $record->nilaibersih,
                    'nilaisegar' => $record->nilaisegar,
                    'nilaimanis' => $record->nilaimanis,
                    'averagescore' => $record->averagescore,
                    'grade' => $record->grade,
                    'status' => $record->averagescore ? 'COMPLETED' : 'PENDING'
                ];
            })->toArray()
        ];
    }

    public function calculateLkhWages($lkhno)
    {
        try {
            DB::beginTransaction();

            $lkh = DB::table('lkhhdr')->where('lkhno', $lkhno)->first();
            if (!$lkh) {
                throw new \Exception("LKH {$lkhno} not found");
            }

            $workers = DB::table('lkhdetailworker')
                ->where('companycode', $lkh->companycode)
                ->where('lkhno', $lkhno)
                ->get();

            if ($workers->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No workers assigned to this LKH'
                ];
            }

            $plots = DB::table('lkhdetailplot')
                ->where('companycode', $lkh->companycode)
                ->where('lkhno', $lkhno)
                ->get();
            
            $plotsData = $plots->map(function($plot) {
                return [
                    'blok' => $plot->blok,
                    'plot' => $plot->plot,
                    'luashasil' => $plot->luashasil,
                    'luasrkh' => $plot->luasrkh
                ];
            })->toArray();

            $totalWages = 0;
            $calculatedWorkers = 0;

            foreach ($workers as $worker) {
                $workerData = [
                    'tenagakerjaid' => $worker->tenagakerjaid,
                    'totaljamkerja' => $worker->totaljamkerja,
                    'overtimehours' => $worker->overtimehours,
                    'premi' => $worker->premi
                ];

                $wageResult = $this->wageCalculationService->calculateWorkerWage(
                    $lkh->companycode,
                    $lkh->activitycode,
                    $lkh->jenistenagakerja,
                    $lkh->lkhdate,
                    $workerData,
                    $plotsData
                );

                if ($wageResult['success']) {
                    DB::table('lkhdetailworker')
                        ->where('id', $worker->id)
                        ->update([
                            'upahharian' => $wageResult['upahharian'],
                            'upahperjam' => $wageResult['upahperjam'],
                            'upahlembur' => $wageResult['upahlembur'],
                            'upahborongan' => $wageResult['upahborongan'],
                            'totalupah' => $wageResult['totalupah'],
                            'keterangan' => $wageResult['notes'] ?? $worker->keterangan,
                            'updatedat' => now()
                        ]);

                    $totalWages += $wageResult['totalupah'];
                    $calculatedWorkers++;
                }
            }

            DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->update([
                    'totalworkers' => $workers->count(),
                    'totalupahall' => $totalWages,
                    'updatedat' => now()
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Wages calculated for {$calculatedWorkers} workers",
                'total_wages' => $totalWages,
                'workers_calculated' => $calculatedWorkers,
                'total_workers' => $workers->count(),
                'lkhno' => $lkhno
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to calculate LKH wages for {$lkhno}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error calculating wages: ' . $e->getMessage()
            ];
        }
    }

    public function regenerateLkh($rkhno, $forceRegenerate = false)
    {
        if (!$forceRegenerate) {
            throw new \Exception("Regenerate LKH hanya bisa dilakukan dengan force flag");
        }

        try {
            DB::beginTransaction();

            $existingLkh = DB::table('lkhhdr')->where('rkhno', $rkhno)->get();
            
            foreach ($existingLkh as $lkh) {
                DB::table('lkhdetailworker')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                DB::table('lkhdetailplot')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                DB::table('lkhdetailmaterial')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                DB::table('lkhdetailkendaraan')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                DB::table('lkhdetailbsm')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                DB::table('lkhhdr')
                    ->where('lkhno', $lkh->lkhno)
                    ->where('companycode', $lkh->companycode)
                    ->delete();
            }

            DB::commit();

            return $this->generateLkhFromRkh($rkhno);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}