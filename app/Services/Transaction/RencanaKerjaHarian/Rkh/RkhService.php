<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Rkh;

use App\Repositories\Transaction\RencanaKerjaHarian\RkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Domain\WorkerRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Domain\KendaraanRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterlistBatchRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\AbsenRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * RkhService
 * 
 * Orchestrates RKH CRUD business logic.
 * RULE: NO DB queries. Only orchestration + business rules + transformations.
 */
class RkhService
{
    protected $rkhRepo;
    protected $workerRepo;
    protected $kendaraanRepo;
    protected $masterDataRepo;
    protected $batchRepo;
    protected $absenRepo;
    protected $numberGenerator;

    public function __construct(
        RkhRepository $rkhRepo,
        WorkerRepository $workerRepo,
        KendaraanRepository $kendaraanRepo,
        MasterDataRepository $masterDataRepo,
        MasterlistBatchRepository $batchRepo,
        AbsenRepository $absenRepo,
        RkhNumberGeneratorService $numberGenerator
    ) {
        $this->rkhRepo = $rkhRepo;
        $this->workerRepo = $workerRepo;
        $this->kendaraanRepo = $kendaraanRepo;
        $this->masterDataRepo = $masterDataRepo;
        $this->batchRepo = $batchRepo;
        $this->absenRepo = $absenRepo;
        $this->numberGenerator = $numberGenerator;
    }

    /**
     * Get data for index page
     */
    public function getIndexPageData($filters, $perPage, $companycode)
    {
        $rkhData = $this->rkhRepo->paginateIndex($companycode, $filters, $perPage);
        
        $rkhNos = $rkhData->pluck('rkhno')->toArray();
        $lkhProgress = [];
        if (!empty($rkhNos)) {
            $lkhProgress = $this->rkhRepo->getLkhProgressForRkhNos($companycode, $rkhNos);
        }
        
        // ✅ FIX: Changed from lkh_status to lkh_progress_status to match blade view
        $rkhData->getCollection()->transform(function($rkh) use ($lkhProgress) {
            $rkh->lkh_progress_status = $lkhProgress[$rkh->rkhno] ?? [
                'status' => 'no_lkh',
                'progress' => 'No LKH Created',
                'can_complete' => false,
                'color' => 'gray'
            ];
            return $rkh;
        });
        
        $filterDate = $filters['filterDate'] ?? date('Y-m-d');
        $mandorId = null; // index tidak filter by mandor
        
        $absenData = $this->absenRepo->getAttendanceData($companycode, $filterDate, $mandorId);
        $mandors = $this->masterDataRepo->getMandorsByCompany($companycode);
        
        return [
            'rkhData' => $rkhData,
            'absentenagakerja' => $absenData,
            'mandors' => $mandors
        ];
    }

    /**
     * Get data for create page
     */
    public function getCreatePageData($date, $mandorId, $companycode)
    {
        $rkhno = $this->numberGenerator->generatePreviewRkhNo($date, $companycode);

        $selectedMandor = DB::table('user')
        ->where('userid', $mandorId)
        ->first();
        
        // Get all master data from respective repositories
        $activities = $this->masterDataRepo->getActivitiesActive();
        $herbisidaData = $this->masterDataRepo->getFullHerbisidaGroupData($companycode);
        $blokData = $this->masterDataRepo->getBlokData($companycode);
        $masterlistData = $this->batchRepo->getAllActivePlotsWithBatch($companycode);
        $absenData = $this->absenRepo->getDataAbsenFull($companycode, $date, $mandorId);
        $vehicles = $this->kendaraanRepo->getVehiclesWithOperators($companycode);
        $helpersData = $this->workerRepo->getHelpersByCompany($companycode);

        return [
            'rkhno' => $rkhno,
            'selectedDate' => $date,
            'selectedMandor' => $selectedMandor,
            'activities' => $activities,
            'bloks' => $blokData,
            'masterlist' => $masterlistData,
            'herbisida' => $herbisidaData,
            'herbisidagroups' => $herbisidaData,
            'kendaraan' => $vehicles,
            'vehiclesData' => $vehicles,
            'helpersData' => $helpersData,
            'absentenagakerja' => $absenData,
        ];
    }

    /**
     * Create new RKH (transaction)
     */
    public function createRkh($dto, $companycode, $userid)
    {
        return DB::transaction(function() use ($dto, $companycode, $userid) {
            $rkhno = $this->numberGenerator->generateUniqueRkhNo($dto['rkhdate'], $companycode);

            // Build components
            $activityGroup = $this->getPrimaryActivityGroup($dto['rows']);
            $workers = $this->groupWorkersByActivity($dto['workers'] ?? []);
            $kendaraan = $this->groupKendaraanByActivity($dto['kendaraan'] ?? []);
            $approvalData = $this->getApprovalData($dto['rows'], $companycode);
            
            // ✅ FIX: Calculate totals BEFORE insert
            $totalLuas = collect($dto['rows'])->sum('luas');
            $totalManpower = collect($workers)->sum('jumlahtenagakerja');
            
            $headerData = array_merge([
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'rkhdate' => $dto['rkhdate'],
                'totalluas' => $totalLuas,        // ✅ ADDED
                'manpower' => $totalManpower,     // ✅ ADDED
                'mandorid' => $dto['mandorid'],
                'activitygroup' => $activityGroup,
                'status' => 'In Progress',
                'createdat' => now(),
                'inputby' => $userid,
            ], $approvalData);
            
            $rkhhdrid = $this->rkhRepo->insertHeaderReturnId($headerData);
            
            // Build details AFTER getting rkhhdrid
            $details = $this->buildRkhDetails($dto['rows'], $companycode, $rkhno, $dto['rkhdate'], $rkhhdrid);
            
            if (!empty($details)) {
                $this->rkhRepo->insertDetails($details);
            }
            
            if (!empty($workers)) {
                $this->workerRepo->replaceWorkersForRkh($companycode, $rkhno, $rkhhdrid, $workers);
            }
            
            if (!empty($kendaraan)) {
                $this->kendaraanRepo->replaceKendaraanForRkh($companycode, $rkhno, $rkhhdrid, $kendaraan);
            }
            
            return [
                'success' => true,
                'rkhno' => $rkhno,
                'message' => "RKH berhasil dibuat: {$rkhno}"
            ];
        });
    }

    /**
     * Get data for show page
     */
    public function getShowPageData($rkhno, $companycode)
    {
        $header = $this->rkhRepo->getHeader($companycode, $rkhno);
        if (!$header) {
            throw new \Exception('RKH not found');
        }
        
        $details = $this->rkhRepo->getDetails($companycode, $rkhno);
        $workers = $this->workerRepo->getWorkersByActivityForRkh($companycode, $rkhno);
        $kendaraan = $this->kendaraanRepo->getKendaraanByActivity($companycode, $rkhno);
        
        $absenData = $this->absenRepo->getAttendanceData($companycode, $header->rkhdate, $header->mandorid);
        $herbisidaData = $this->masterDataRepo->getFullHerbisidaGroupData($companycode);
        
        return [
            // ✅ FIX: Match God Controller variable names
            'rkhHeader' => $header,                      // header → rkhHeader
            'rkhDetails' => $details,                    // details → rkhDetails
            'workersByActivity' => $workers,             // workers → workersByActivity
            'kendaraanByActivity' => $kendaraan,         // kendaraan → kendaraanByActivity
            'absentenagakerja' => $absenData,
            'herbisidagroups' => $herbisidaData,
        ];
    }

    /**
     * Get data for edit page
     */
    public function getEditPageData($rkhno, $companycode)
    {
        $header = $this->rkhRepo->getHeaderForEdit($companycode, $rkhno);
        if (!$header) {
            throw new \Exception('RKH not found');
        }
        
        $details = $this->rkhRepo->getDetailsForEdit($companycode, $rkhno);
        $workers = $this->workerRepo->getWorkersByActivityForRkh($companycode, $rkhno);
        $kendaraan = $this->kendaraanRepo->getKendaraanByActivity($companycode, $rkhno);
        
        // Get all master data from respective repositories
        $activities = $this->masterDataRepo->getActivitiesActive();
        $herbisidaData = $this->masterDataRepo->getFullHerbisidaGroupData($companycode);
        $blokData = $this->masterDataRepo->getBlokData($companycode);
        $masterlistData = $this->batchRepo->getAllActivePlotsWithBatch($companycode);
        $absenData = $this->absenRepo->getDataAbsenFull($companycode, $header->rkhdate, $header->mandorid);
        $vehicles = $this->kendaraanRepo->getVehiclesWithOperators($companycode);
        $helpersData = $this->workerRepo->getHelpersByCompany($companycode);
        
        return [
            // ✅ FIX: Match God Controller variable names
            'rkhHeader' => $header,              // header → rkhHeader
            'rkhDetails' => $details,            // details → rkhDetails
            'existingWorkers' => $workers,       // workers → existingWorkers
            'existingKendaraan' => $kendaraan,   // kendaraan → existingKendaraan
            
            // Master data (sudah benar)
            'activities' => $activities,
            'bloks' => $blokData,
            'masterlist' => $masterlistData,
            'herbisida' => $herbisidaData,
            'herbisidagroups' => $herbisidaData,
            'vehiclesData' => $vehicles,
            'helpersData' => $helpersData,
            'absentenagakerja' => $absenData,
            
            // ✅ TAMBAH: oldInput (dipake di view)
            'oldInput' => old(),
        ];
    }

    /**
     * Update existing RKH (transaction)
     */
    public function updateRkh($rkhno, $dto, $companycode, $userid)
    {
        return DB::transaction(function() use ($rkhno, $dto, $companycode, $userid) {
            
            // ✅ FIX: Get header dulu untuk ambil rkhhdrid
            $existingHeader = $this->rkhRepo->getHeaderForEdit($companycode, $rkhno);
            
            if (!$existingHeader) {
                throw new \Exception("RKH {$rkhno} tidak ditemukan");
            }
            
            $rkhhdrid = $existingHeader->id;
            
            // ✅ FIX: Build components sama kayak create
            $activityGroup = $this->getPrimaryActivityGroup($dto['rows']);
            $workers = $this->groupWorkersByActivity($dto['workers'] ?? []);
            $kendaraan = $this->groupKendaraanByActivity($dto['kendaraan'] ?? []);
            $approvalData = $this->getApprovalDataForUpdate($dto['rows'], $companycode);
            
            // ✅ FIX: Calculate totals
            $totalLuas = collect($dto['rows'])->sum('luas');
            $totalManpower = collect($workers)->sum('jumlahtenagakerja');
            
            // ✅ Update header dengan approval reset
            $headerData = array_merge([
                'rkhdate' => $dto['rkhdate'],
                'totalluas' => $totalLuas,
                'manpower' => $totalManpower,
                'mandorid' => $dto['mandorid'],
                'activitygroup' => $activityGroup,
                'keterangan' => $dto['keterangan'] ?? null,
                'updatedat' => now(),
                'updateby' => $userid,
            ], $approvalData);
            
            $this->rkhRepo->updateHeader($companycode, $rkhno, $headerData);
            
            // ✅ Delete old details
            $this->rkhRepo->deleteDetails($companycode, $rkhno);
            
            // ✅ Build & insert new details
            $details = $this->buildRkhDetails($dto['rows'], $companycode, $rkhno, $dto['rkhdate'], $rkhhdrid);
            
            if (!empty($details)) {
                $this->rkhRepo->insertDetails($details);
            }
            
            // ✅ Replace workers
            if (!empty($workers)) {
                $this->workerRepo->replaceWorkersForRkh($companycode, $rkhno, $rkhhdrid, $workers);
            }
            
            // ✅ Replace kendaraan
            if (!empty($kendaraan)) {
                $this->kendaraanRepo->replaceKendaraanForRkh($companycode, $rkhno, $rkhhdrid, $kendaraan);
            }
            
            \Log::info('RKH Update Success', [
                'rkhno' => $rkhno,
                'details_count' => count($details),
                'workers_count' => count($workers),
                'kendaraan_count' => count($kendaraan)
            ]);
            
            return true;
        });
    }

    /**
     * Delete RKH (transaction)
     */
    public function deleteRkh($rkhno, $companycode)
    {
        return DB::transaction(function() use ($rkhno, $companycode) {
            $this->kendaraanRepo->deleteByRkhNo($companycode, $rkhno);
            $this->workerRepo->deleteByRkhNo($companycode, $rkhno);
            $this->rkhRepo->deleteDetails($companycode, $rkhno);
            $this->rkhRepo->deleteHeader($companycode, $rkhno);
            
            return [
                'success' => true,
                'message' => 'RKH berhasil dihapus'
            ];
        });
    }





    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Group workers by activity
     */
    private function groupWorkersByActivity($workersData)
    {
        $grouped = [];
        
        foreach ($workersData as $activityCode => $data) {
            $totalWorkers = ($data['laki'] ?? 0) + ($data['perempuan'] ?? 0);
            
            if ($totalWorkers > 0) {
                $grouped[] = [
                    'activitycode' => $activityCode,
                    'jumlahlaki' => $data['laki'] ?? 0,
                    'jumlahperempuan' => $data['perempuan'] ?? 0,
                    'jumlahtenagakerja' => $totalWorkers,
                ];
            }
        }
        
        return $grouped;
    }

    /**
     * Group kendaraan by activity
     */
    private function groupKendaraanByActivity($kendaraanData)
    {
        $grouped = [];
        
        foreach ($kendaraanData as $activityCode => $vehicles) {
            if (!empty($vehicles)) {
                $grouped[$activityCode] = $vehicles;
            }
        }
        
        return $grouped;
    }

    /**
     * Get primary activity group
     */
    private function getPrimaryActivityGroup($rows)
    {
        if (empty($rows)) {
            return null;
        }
        
        $firstRow = $rows[0];
        $activityCode = $firstRow['nama'] ?? null;
        
        if (!$activityCode) {
            return null;
        }
        
        $activity = $this->masterDataRepo->getActivityByCode($activityCode);
        return $activity->activitygroup ?? null;
    }

    /**
     * Get approval data
     */
    private function getApprovalData($rows, $companycode)
    {
        $activityGroup = $this->getPrimaryActivityGroup($rows);
        
        if (!$activityGroup) {
            return [
                'jumlahapproval' => 0,
                'approval1idjabatan' => null,
                'approval2idjabatan' => null,
                'approval3idjabatan' => null,
            ];
        }
        
        $approvalSetting = $this->masterDataRepo->getApprovalSettingByActivityGroup($companycode, $activityGroup);
        
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

    /**
     * Get approval data for update (reset flags)
     */
    private function getApprovalDataForUpdate($rows, $companycode)
    {
        $baseApproval = $this->getApprovalData($rows, $companycode);

        return array_merge($baseApproval, [
            'approval1flag' => null,
            'approval2flag' => null,
            'approval3flag' => null,
            'approval1date' => null,
            'approval2date' => null,
            'approval3date' => null,
            'approval1userid' => null,
            'approval2userid' => null,
            'approval3userid' => null,
            'approvalstatus' => null,
        ]);
    }

    /**
     * Build RKH details rows
     */
    private function buildRkhDetails($rows, $companycode, $rkhno, $date, $rkhhdrid)
    {
        $details = [];
        
        foreach ($rows as $row) {
            $activityCode = $row['nama'] ?? null;
            
            if (!$activityCode) {
                continue;
            }
            
            $activity = $this->masterDataRepo->getActivityByCode($activityCode);
            
            $jenistenagakerja = $activity ? $activity->jenistenagakerja : null;
            $isBlokActivity = $activity ? ($activity->isblokactivity == 1) : false;
            
            $batchno = null;
            $batchid = null;
            
            if (!$isBlokActivity && !empty($row['plot'])) {
                $batchInfo = $this->batchRepo->getActiveBatchForPlot($companycode, $row['plot']);
                if ($batchInfo) {
                    $batchno = $batchInfo->batchno;
                    $batchid = $batchInfo->id;
                }
            }
            
            $details[] = [
                'companycode'      => $companycode,
                'rkhno'            => $rkhno,
                'rkhhdrid'         => $rkhhdrid,
                'rkhdate'          => $date,
                'blok'             => $row['blok'] ?? null,
                'plot'             => $isBlokActivity ? null : ($row['plot'] ?? null),
                'activitycode'     => $activityCode,
                'luasarea'         => $row['luas'] ?? 0,
                'jenistenagakerja' => $jenistenagakerja,
                'usingmaterial'    => !empty($row['material_group_id']) ? 1 : 0,
                'herbisidagroupid' => !empty($row['material_group_id']) ? (int) $row['material_group_id'] : null,
                'batchno'          => $batchno,
                'batchid'          => $batchid,
            ];
        }
        
        return $details;
    }

    // ============================================
    // APPROVAL INFO METHODS (READ-ONLY)
    // ============================================

    /**
     * Get approval detail for specific RKH (for info modal)
     * 
     * @param string $rkhno
     * @param string $companycode
     * @return array|null
     */
    public function getApprovalDetail($rkhno, $companycode)
    {
        $rkh = $this->rkhRepo->getApprovalDetail($companycode, $rkhno);

        if (!$rkh) {
            return null;
        }

        return $this->formatApprovalDetailData($rkh);
    }

    /**
     * Update RKH status (Completed/In Progress)
     * 
     * @param string $rkhno
     * @param string $status
     * @param object $currentUser
     * @param string $companycode
     * @return array
     */
    public function updateRkhStatus($rkhno, $status, $currentUser, $companycode)
    {
        // Validate LKH completion if marking as Completed
        if ($status === 'Completed') {
            $progressStatus = $this->rkhRepo->getProgressStatusFromLkh($companycode, $rkhno);
            
            if (!$progressStatus['can_complete']) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menandai RKH sebagai Completed. ' . 
                                $progressStatus['progress'] . '. Semua LKH harus diapprove terlebih dahulu.'
                ];
            }
        }
        
        $updated = $this->rkhRepo->updateStatus($companycode, $rkhno, $status, $currentUser->userid, now());

        if ($updated) {
            return [
                'success' => true, 
                'message' => 'Status RKH berhasil diupdate menjadi ' . $status
            ];
        } else {
            return ['success' => false, 'message' => 'RKH tidak ditemukan'];
        }
    }

    /**
     * Format approval detail data (PRIVATE HELPER)
     * 
     * @param object $rkh
     * @return array
     */
    private function formatApprovalDetailData($rkh)
    {
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $rkh->{"idjabatanapproval{$i}"};
            if (!$jabatanId) continue;

            $flagField = "approval{$i}flag";
            $dateField = "approval{$i}date";
            $userField = "approval{$i}_user_name";
            $jabatanField = "jabatan{$i}_name";

            $flag = $rkh->$flagField;
            $status = 'waiting';
            $statusText = 'Waiting';

            if ($flag === '1') {
                $status = 'approved';
                $statusText = 'Approved';
            } elseif ($flag === '0') {
                $status = 'declined';
                $statusText = 'Declined';
            }

            $levels[] = [
                'level' => $i,
                'jabatan_name' => $rkh->$jabatanField ?? 'Unknown',
                'status' => $status,
                'status_text' => $statusText,
                'user_name' => $rkh->$userField ?? null,
                'date_formatted' => $rkh->$dateField ? \Carbon\Carbon::parse($rkh->$dateField)->format('d/m/Y H:i') : null
            ];
        }

        return [
            'rkhno' => $rkh->rkhno,
            'rkhdate' => $rkh->rkhdate,
            'rkhdate_formatted' => \Carbon\Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
            'mandor_nama' => $rkh->mandor_nama,
            'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
            'jumlah_approval' => $rkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }

    /**
     * Cancel RKH
     * 
     * @param string $rkhno
     * @param string $alasan
     * @param string $companycode
     * @param string $userid
     * @return array
     */
    public function cancelRkh($rkhno, $alasan, $companycode, $userid)
    {
        // Validate can cancel
        $canCancel = $this->rkhRepo->canCancelRkh($companycode, $rkhno);
        
        if (!$canCancel['can_cancel']) {
            return [
                'success' => false,
                'message' => $canCancel['reason']
            ];
        }
        
        // Validate alasan
        if (empty(trim($alasan))) {
            return [
                'success' => false,
                'message' => 'Alasan pembatalan wajib diisi'
            ];
        }
        
        if (strlen(trim($alasan)) < 10) {
            return [
                'success' => false,
                'message' => 'Alasan pembatalan minimal 10 karakter'
            ];
        }
        
        // Execute cancel
        try {
            $updated = $this->rkhRepo->cancelRkh($companycode, $rkhno, $userid, trim($alasan));
            
            if ($updated) {
                \Log::info('RKH Cancelled', [
                    'rkhno' => $rkhno,
                    'companycode' => $companycode,
                    'cancelled_by' => $userid,
                    'reason' => $alasan
                ]);
                
                return [
                    'success' => true,
                    'message' => 'RKH berhasil dibatalkan. Material (jika ada dengan status ACTIVE) telah diupdate menjadi CANCEL.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Gagal membatalkan RKH'
            ];
            
        } catch (\Exception $e) {
            \Log::error('Cancel RKH Service Error', [
                'rkhno' => $rkhno,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat membatalkan RKH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get batal detail
     * 
     * @param string $rkhno
     * @param string $companycode
     * @return array|null
     */
    public function getBatalDetail($rkhno, $companycode)
    {
        $rkh = $this->rkhRepo->getBatalDetail($companycode, $rkhno);
        
        if (!$rkh) {
            return null;
        }
        
        return [
            'rkhno' => $rkh->rkhno,
            'rkhdate_formatted' => \Carbon\Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
            'batalat_formatted' => \Carbon\Carbon::parse($rkh->batalat)->format('d/m/Y H:i'),
            'batal_by_nama' => $rkh->batal_by_nama ?? 'Unknown',
            'batalalasan' => $rkh->batalalasan
        ];
    }
}