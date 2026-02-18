<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Lkh;

use App\Repositories\Transaction\RencanaKerjaHarian\LkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\RkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterlistBatchRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * LkhService
 * 
 * Orchestrates LKH management business logic.
 * RULE: No DB queries. Only orchestration + business rules.
 */
class LkhService
{
    protected $lkhRepo;
    protected $rkhRepo;
    protected $masterDataRepo;
    protected $batchRepo;

    public function __construct(
        LkhRepository $lkhRepo,
        RkhRepository $rkhRepo,
        MasterDataRepository $masterDataRepo,
        MasterlistBatchRepository $batchRepo
    ) {
        $this->lkhRepo = $lkhRepo;
        $this->rkhRepo = $rkhRepo;
        $this->masterDataRepo = $masterDataRepo;
        $this->batchRepo = $batchRepo;
    }

    /**
     * Get LKH list for specific RKH
     * 
     * @param string $rkhno
     * @param string $companycode
     * @return array
     */
    public function getLkhListForRkh($rkhno, $companycode)
    {
        // Get LKH list
        $lkhList = $this->lkhRepo->listByRkhNo($companycode, $rkhno);

        // Batch-load related data (avoid N+1)
        $lkhNos = $lkhList->pluck('lkhno')->toArray();
        
        $plotsByLkh = $this->lkhRepo->getPlotsByLkhNos($companycode, $lkhNos);
        $workersByLkh = $this->lkhRepo->getWorkersCountByLkhNos($companycode, $lkhNos);
        $materialsByLkh = $this->lkhRepo->getMaterialsCountByLkhNos($companycode, $lkhNos);

        // Format data
        $formattedData = $this->formatLkhData($lkhList, $plotsByLkh, $workersByLkh, $materialsByLkh);

        // Get generate info
        $generateInfo = $this->getLkhGenerateInfo($companycode, $rkhno, $lkhList);

        return [
            'success' => true,
            'lkh_data' => $formattedData->values()->toArray(),
            'rkhno' => $rkhno,
            'can_generate_lkh' => $generateInfo['can_generate'],
            'generate_message' => $generateInfo['message'],
            'total_lkh' => $lkhList->count()
        ];
    }

    
    /**
     * Get show LKH page data (detects activity type)
     */
    public function getShowLkhPageData($lkhno, $companycode)
    {
        $lkhData = $this->lkhRepo->getHeaderForShow($companycode, $lkhno);

        if (!$lkhData) {
            return null;
        }

        // Authorization check
        $this->authorizeActivityGroup($lkhno, $companycode);

        $panenActivities = ['4.3.3', '4.4.3', '4.5.2'];
        $bsmActivity     = '4.7';

        $isPanenActivity = in_array($lkhData->activitycode, $panenActivities);
        $isBsmActivity   = ($lkhData->activitycode === $bsmActivity);

        $approvals = $this->getLkhApprovalsData($lkhData);

        if ($isBsmActivity) {
            return [
                'activity_type'    => 'bsm',
                'lkhData'          => $lkhData,
                'lkhBsmDetails'    => $this->lkhRepo->getBsmDetailsForShow($companycode, $lkhno),
                'lkhWorkerDetails' => $this->lkhRepo->getWorkerDetailsForShow($companycode, $lkhno),
                'approvals'        => $approvals,
            ];
        }

        if ($isPanenActivity) {
            return [
                'activity_type'       => 'panen',
                'lkhData'             => $lkhData,
                'lkhPanenDetails'     => $this->lkhRepo->getPanenDetailsForShow($companycode, $lkhno),
                'approvals'           => $approvals,
                'kontraktorSummary'   => $this->lkhRepo->getKontraktorSummaryForLkh($companycode, $lkhno),
                'subkontraktorDetail' => $this->lkhRepo->getSubkontraktorDetailForLkh($companycode, $lkhno),
                'ongoingPlots'        => $this->lkhRepo->getOngoingPlotsForMandor($companycode, $lkhno, $lkhData->mandorid),
            ];
        }

        return [
            'activity_type'      => 'normal',
            'lkhData'            => $lkhData,
            'lkhPlotDetails'     => $this->lkhRepo->getPlotDetailsForShow($companycode, $lkhno),
            'lkhWorkerDetails'   => $this->lkhRepo->getWorkerDetailsForShow($companycode, $lkhno),
            'lkhMaterialDetails' => $this->lkhRepo->getMaterialDetailsForShow($companycode, $lkhno),
            'approvals'          => $approvals,
        ];
    }

    /**
     * Get edit LKH page data
     */
    public function getEditLkhPageData($lkhno, $companycode)
    {
        $lkhData = $this->lkhRepo->getHeaderForEdit($companycode, $lkhno);

        if (!$lkhData) {
            return null;
        }

        // Authorization check
        $this->authorizeActivityGroup($lkhno, $companycode);

        if ($lkhData->issubmit) {
            throw new \Exception('LKH sudah disubmit dan tidak dapat diedit');
        }

        $lkhPlotDetails    = $this->lkhRepo->getPlotDetailsForEdit($companycode, $lkhno);
        $lkhWorkerDetails  = $this->lkhRepo->getWorkerDetailsForEdit($companycode, $lkhno);
        $lkhMaterialDetails= $this->lkhRepo->getMaterialDetailsForEdit($companycode, $lkhno);

        $formData = $this->loadLkhEditFormData($companycode);

        $boronganRate = 0;
        if ($lkhData->jenistenagakerja == 2) {
            $boronganRate = $this->masterDataRepo->getBoronganRate(
                $companycode,
                $lkhData->activitycode,
                $lkhData->lkhdate
            ) ?? 0;
        }

        return array_merge([
            'lkhData'            => $lkhData,
            'lkhPlotDetails'     => $lkhPlotDetails,
            'lkhWorkerDetails'   => $lkhWorkerDetails,
            'lkhMaterialDetails' => $lkhMaterialDetails,
            'boronganRate'       => $boronganRate,
        ], $formData);
    }

    /**
     * Update LKH with transaction
     * 
     * @param string $lkhno
     * @param array $dto
     * @param string $companycode
     * @return void
     * @throws \Exception
     */
    public function updateLkh($lkhno, array $dto, $companycode)
    {
        DB::transaction(function () use ($lkhno, $dto, $companycode) {
            $currentUser = Auth::user();
            
            // Security check
            $lkhData = $this->lkhRepo->getForValidation($companycode, $lkhno);
            
            if (!$lkhData) {
                throw new \Exception('LKH tidak ditemukan');
            }

            if ($lkhData->issubmit) {
                throw new \Exception('LKH sudah disubmit dan tidak dapat diedit');
            }

            // Get RKH number for usemateriallst update
            $rkhno = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->value('rkhno');

            // Prepend "Alasan Edit: " to keterangan
            $keterangan = $dto['keterangan'] ?? null;
            if ($keterangan) {
                $keterangan = "Alasan Edit: " . trim($keterangan);
            }

            // Calculate totals
            $totalWorkers = count($dto['workers'] ?? []);
            $totalHasil = collect($dto['plots'] ?? [])->sum('luashasil');
            $totalSisa = collect($dto['plots'] ?? [])->sum('luassisa');
            
            // Get full LKH data for wage calculation
            $fullLkhData = $this->lkhRepo->getHeaderForEdit($companycode, $lkhno);
            
            if ($fullLkhData->jenistenagakerja == 2) {
                $totalUpah = $this->calculateBoronganFromPlots(
                    $dto['plots'] ?? [], 
                    $fullLkhData->activitycode, 
                    $companycode, 
                    $fullLkhData->lkhdate
                );
            } else {
                $totalUpah = $this->calculateTotalUpah($dto['workers'] ?? [], $fullLkhData);
            }

            // Update header with edit tracking
            $headerData = [
                'totalworkers' => $totalWorkers,
                'totalhasil' => $totalHasil,
                'totalsisa' => $totalSisa,
                'totalupahall' => $totalUpah,
                'keterangan' => $keterangan,
                'isedit' => 1,
                'editedby' => $currentUser->userid,
                'editedat' => now(),
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ];
            
            $this->lkhRepo->updateHeader($companycode, $lkhno, $headerData);

            // Update plot details
            if (!empty($dto['plots'])) {
                $plotDetails = $this->buildLkhPlotDetails($dto['plots'], $lkhno, $companycode);
                $this->lkhRepo->replacePlotDetails($companycode, $lkhno, $plotDetails);
            }

            // Update worker details
            if (!empty($dto['workers'])) {
                $workerDetails = $this->buildLkhWorkerDetails($dto['workers'], $lkhno, $companycode);
                $this->lkhRepo->replaceWorkerDetails($companycode, $lkhno, $workerDetails);
            }

            // ✅ NEW: Update material details (SYNC 2 TABLES)
            if (!empty($dto['materials'])) {
                $this->updateMaterialUsage($dto['materials'], $lkhno, $rkhno, $companycode, $currentUser->userid);
            }
        });
    }

    private function updateMaterialUsage($materials, $lkhno, $rkhno, $companycode, $userid)
    {
        \Log::info("🔵 updateMaterialUsage STARTED", [
            'lkhno' => $lkhno,
            'rkhno' => $rkhno,
            'companycode' => $companycode,
            'total_materials' => count($materials)
        ]);

        foreach ($materials as $index => $material) {
            $plot = $material['plot'];
            $itemcode = $material['itemcode'];
            $qtydigunakan = (float)($material['qtydigunakan'] ?? 0);
            $qtyditerima = (float)($material['qtyditerima'] ?? 0);
            
            \Log::info("🔹 Processing material #{$index}", [
                'id' => $material['id'] ?? 'N/A',
                'plot' => $plot,
                'itemcode' => $itemcode,
                'qtydigunakan' => $qtydigunakan,
                'qtyditerima' => $qtyditerima
            ]);
            
            // Validation
            if ($qtydigunakan > $qtyditerima) {
                throw new \Exception("Qty Used ({$qtydigunakan}) cannot exceed Qty Received ({$qtyditerima}) for item {$itemcode} on plot {$plot}");
            }
            
            // Calculate qtysisa (remaining = return)
            $qtysisa = $qtyditerima - $qtydigunakan;
            
            // 1️⃣ Update lkhdetailmaterial (LKH detail)
            $affectedRows1 = DB::table('lkhdetailmaterial')
                ->where('id', $material['id'])
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->update([
                    'qtydigunakan' => $qtydigunakan,
                    'qtysisa' => $qtysisa,
                    'updatedat' => now()
                ]);

            \Log::info("✅ lkhdetailmaterial updated", [
                'affected_rows' => $affectedRows1,
                'qtydigunakan' => $qtydigunakan,
                'qtysisa' => $qtysisa
            ]);
            
            // 2️⃣ Sync to usemateriallst (Material tracking)
            $existingRecord = DB::table('usemateriallst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->where('lkhno', $lkhno)
                ->where('plot', $plot)
                ->where('itemcode', $itemcode)
                ->first();

            if (!$existingRecord) {
                \Log::error("❌ Record NOT FOUND in usemateriallst", [
                    'companycode' => $companycode,
                    'rkhno' => $rkhno,
                    'lkhno' => $lkhno,
                    'plot' => $plot,
                    'itemcode' => $itemcode
                ]);
                continue;
            }

            $affectedRows2 = DB::table('usemateriallst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->where('lkhno', $lkhno)
                ->where('plot', $plot)
                ->where('itemcode', $itemcode)
                ->update([
                    'qtydigunakan' => $qtydigunakan,
                    'qtyretur' => $qtysisa,
                ]);
            
            \Log::info("✅ usemateriallst updated", [
                'affected_rows' => $affectedRows2,
                'qtydigunakan' => $qtydigunakan,
                'qtyretur' => $qtysisa // ✅ same as qtysisa
            ]);
        }

        \Log::info("🟢 updateMaterialUsage COMPLETED");
    }

    /**
     * Submit LKH for approval
     * 
     * @param string $lkhno
     * @param string $companycode
     * @return array
     * @throws \Exception
     */
    public function submitLkh($lkhno, $companycode)
    {
        return DB::transaction(function () use ($lkhno, $companycode) {
            $currentUser = Auth::user();
            
            // Get LKH with activity group
            $lkh = DB::table('lkhhdr as h')
                ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
                ->where('h.companycode', $companycode)
                ->where('h.lkhno', $lkhno)
                ->select(['h.*', 'a.activitygroup'])
                ->first();

            if (!$lkh) {
                return ['success' => false, 'message' => 'LKH tidak ditemukan'];
            }

            if ($lkh->issubmit) {
                return ['success' => false, 'message' => 'LKH sudah disubmit sebelumnya'];
            }

            if ($lkh->status !== 'DRAFT') {
                return ['success' => false, 'message' => 'LKH harus berstatus DRAFT untuk bisa disubmit'];
            }

            // Get approval setting
            $approvalSetting = null;
            if ($lkh->activitygroup) {
                $approvalSetting = $this->masterDataRepo->getApprovalSettingByActivityGroup($companycode, $lkh->activitygroup);
            }

            // Build update data
            $updateData = [
                'issubmit' => 1,
                'submitby' => $currentUser->userid,
                'submitat' => now(),
                'status' => 'SUBMITTED',
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ];

            if ($approvalSetting) {
                $updateData = array_merge($updateData, [
                    'jumlahapproval' => $approvalSetting->jumlahapproval,
                    'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                    'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                    'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
                ]);
            }

            $this->lkhRepo->submitLkh($companycode, $lkhno, $updateData);

            return [
                'success' => true,
                'message' => 'LKH berhasil disubmit dan masuk ke proses approval'
            ];
        });
    }

    /**
     * Get LKH approval detail (for info modal)
     * 
     * @param string $lkhno
     * @param string $companycode
     * @return array|null
     */
    public function getLkhApprovalDetail($lkhno, $companycode)
    {
        $lkh = $this->lkhRepo->getLkhApprovalDetail($companycode, $lkhno);

        if (!$lkh) {
            return null;
        }

        // Format levels
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $lkh->{"approval{$i}idjabatan"};
            if (!$jabatanId) continue;

            $flagField = "approval{$i}flag";
            $dateField = "approval{$i}date";
            $userField = "approval{$i}_user_name";
            $jabatanField = "jabatan{$i}_name";

            $flag = $lkh->$flagField;
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
                'jabatan_name' => $lkh->$jabatanField ?? 'Unknown',
                'status' => $status,
                'status_text' => $statusText,
                'user_name' => $lkh->$userField ?? null,
                'date_formatted' => $lkh->$dateField ? \Carbon\Carbon::parse($lkh->$dateField)->format('d/m/Y H:i') : null
            ];
        }

        return [
            'lkhno' => $lkh->lkhno,
            'rkhno' => $lkh->rkhno,
            'lkhdate' => $lkh->lkhdate,
            'lkhdate_formatted' => \Carbon\Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
            'mandor_nama' => $lkh->mandor_nama,
            'activityname' => $lkh->activityname ?? 'Unknown Activity',
            'location' => $lkh->location ?? '-',
            'jumlah_approval' => $lkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    private function formatLkhData($lkhList, $plotsByLkh, $workersByLkh, $materialsByLkh)
    {
        return $lkhList->map(function($lkh) use ($plotsByLkh, $workersByLkh, $materialsByLkh) {
            $approvalStatus = $this->calculateLKHApprovalStatus($lkh);
            
            $canEdit = !$lkh->issubmit;
            $canSubmit = !$lkh->issubmit && $lkh->status === 'DRAFT';

            $plots = ($plotsByLkh[$lkh->lkhno] ?? collect())
                ->pluck('plot')
                ->unique()
                ->join(', ');

            $workersAssigned = $workersByLkh[$lkh->lkhno] ?? 0;
            $materialCount = $materialsByLkh[$lkh->lkhno] ?? 0;

            return [
                'lkhno' => $lkh->lkhno,
                'activitycode' => $lkh->activitycode,
                'activityname' => $lkh->activityname ?? 'Unknown Activity',
                'plots' => $plots ?: 'No plots assigned',
                'jenistenagakerja' => $lkh->jenistenagakerja,
                'jenis_tenaga' => $lkh->jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                'status' => $lkh->status ?? 'EMPTY',
                'approval_status' => $approvalStatus,
                'workers_assigned' => $workersAssigned,
                'material_count' => $materialCount,
                'totalhasil' => $lkh->totalhasil,
                'totalsisa' => $lkh->totalsisa,
                'totalupah' => $lkh->totalupahall ?? 0,
                'issubmit' => (bool) $lkh->issubmit,
                'date_formatted' => $lkh->lkhdate ? Carbon::parse($lkh->lkhdate)->format('d/m/Y') : '-',
                'created_at' => $lkh->createdat ? Carbon::parse($lkh->createdat)->format('d/m/Y H:i') : '-',
                'submit_info' => $lkh->submitat ? 'Submitted at ' . Carbon::parse($lkh->submitat)->format('d/m/Y H:i') : null,
                'can_edit' => $canEdit,
                'can_submit' => $canSubmit,
                'view_url' => route('transaction.rencanakerjaharian.showLKH', $lkh->lkhno),
                'edit_url' => route('transaction.rencanakerjaharian.editLKH', $lkh->lkhno),
            ];
        });
    }

    private function calculateLKHApprovalStatus($lkh)
    {
        if (!$lkh->issubmit) {
            return 'Not Yet Submitted';
        }

        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return 'No Approval Required';
        }

        if ($this->isLKHFullyApproved($lkh)) {
            return 'Approved';
        }

        if ($lkh->approval1flag === '0' || $lkh->approval2flag === '0' || $lkh->approval3flag === '0') {
            return 'Declined';
        }

        $completed = 0;
        if ($lkh->approval1flag === '1') $completed++;
        if ($lkh->approval2flag === '1') $completed++;
        if ($lkh->approval3flag === '1') $completed++;

        return "Waiting ({$completed} / {$lkh->jumlahapproval})";
    }

    private function isLKHFullyApproved($lkh)
    {
        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return true;
        }

        switch ($lkh->jumlahapproval) {
            case 1:
                return $lkh->approval1flag === '1';
            case 2:
                return $lkh->approval1flag === '1' && $lkh->approval2flag === '1';
            case 3:
                return $lkh->approval1flag === '1' && 
                       $lkh->approval2flag === '1' && 
                       $lkh->approval3flag === '1';
            default:
                return false;
        }
    }

    private function getLkhGenerateInfo($companycode, $rkhno, $lkhList)
    {
        $canGenerateLkh = false;
        $generateMessage = '';
        
        $rkhData = $this->rkhRepo->getHeader($companycode, $rkhno);
            
        if ($rkhData) {
            if ($this->isRkhFullyApproved($rkhData)) {
                if ($lkhList->isEmpty()) {
                    $canGenerateLkh = true;
                    $generateMessage = 'RKH sudah approved, LKH bisa di-generate';
                } else {
                    $generateMessage = 'LKH sudah pernah di-generate';
                }
            } else {
                $generateMessage = 'RKH belum fully approved';
            }
        }

        return [
            'can_generate' => $canGenerateLkh,
            'message' => $generateMessage
        ];
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

    private function getLkhApprovalsData($lkhData)
    {
        $approvals = new \stdClass();
        
        if ($lkhData->jumlahapproval > 0) {
            $jabatanIds = array_filter([
                $lkhData->idjabatanapproval1,
                $lkhData->idjabatanapproval2,
                $lkhData->idjabatanapproval3
            ]);
            
            if (!empty($jabatanIds)) {
                $jabatanData = $this->masterDataRepo->getJabatanNamesByIds($jabatanIds);

                $approvals->jabatan1name = $jabatanData[$lkhData->idjabatanapproval1] ?? null;
                $approvals->jabatan2name = $jabatanData[$lkhData->idjabatanapproval2] ?? null;
                $approvals->jabatan3name = $jabatanData[$lkhData->idjabatanapproval3] ?? null;
            }
        }

        return $approvals;
    }

    private function loadLkhEditFormData($companycode)
    {
        return [
            'tenagaKerja' => DB::table('tenagakerja')
                ->where('companycode', $companycode)
                ->where('isactive', 1)
                ->select(['tenagakerjaid', 'nama', 'nik', 'jenistenagakerja'])
                ->orderBy('nama')
                ->get(),
            'bloks' => $this->masterDataRepo->getBlokData($companycode),
            'masterlist' => $this->batchRepo->getAllActivePlotsWithBatch($companycode),
            'plots' => $this->batchRepo->getAllActivePlotsWithBatch($companycode),
        ];
    }

    private function calculateTotalUpah($workers, $lkhData)
    {
        $totalUpah = 0;

        if ($lkhData->jenistenagakerja == 1) {
            foreach ($workers as $worker) {
                $upahHarian = $worker['upahharian'] ?? 0;
                $premi = $worker['premi'] ?? 0;
                $upahlembur = $worker['upahlembur'] ?? 0;
                $totalUpah += $upahHarian + $premi + $upahlembur;
            }
        }

        return $totalUpah;
    }

    private function calculateBoronganFromPlots($plots, $activitycode, $companycode, $lkhdate)
    {
        $totalArea = collect($plots)->sum('luashasil');
        $rate = $this->masterDataRepo->getBoronganRate($companycode, $activitycode, $lkhdate);
        
        return $totalArea * ($rate ?? 0);
    }

    private function buildLkhPlotDetails($plots, $lkhno, $companycode)
    {
        $details = [];
        
        // Get lkhhdrid from header
        $lkhhdrid = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->value('id');
        
        foreach ($plots as $plot) {
            // Lookup batchno & batchid dari masterlist (seperti di Generator)
            $batchid = null;
            $batchno = $plot['batchno'] ?? null;
            
            if (!$batchno && isset($plot['plot'])) {
                // Get from masterlist if not provided
                $masterlist = DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $plot['plot'])
                    ->where('isactive', 1)
                    ->first();
                
                if ($masterlist && $masterlist->activebatchno) {
                    $batchno = $masterlist->activebatchno;
                }
            }
            
            // Get batchid from batch table
            if ($batchno) {
                $batch = DB::table('batch')
                    ->where('batchno', $batchno)
                    ->where('companycode', $companycode)
                    ->first();
                $batchid = $batch ? $batch->id : null;
            }
            
            $details[] = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'lkhhdrid' => $lkhhdrid,
                'blok' => $plot['blok'],
                'plot' => $plot['plot'],
                'luasrkh' => $plot['luasrkh'] ?? 0,
                'luashasil' => $plot['luashasil'] ?? 0,
                'luassisa' => $plot['luassisa'] ?? 0,
                'batchno' => $batchno,
                'batchid' => $batchid,
                'createdat' => now()
            ];
        }
        
        return $details;
    }

    private function buildLkhWorkerDetails($workers, $lkhno, $companycode)
    {
        $details = [];
        
        $lkhhdrid = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->value('id');
        
        foreach ($workers as $index => $worker) {
            $details[] = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'lkhhdrid' => $lkhhdrid,
                'tenagakerjaid' => $worker['tenagakerjaid'],
                'tenagakerjaurutan' => $index + 1,
                'jammasuk' => $worker['jammasuk'] ?? null,
                'jamselesai' => $worker['jamselesai'] ?? null,
                'totaljamkerja' => $worker['totaljamkerja'] ?? 0,
                'overtimehours' => $worker['overtimehours'] ?? 0,
                'premi' => $worker['premi'] ?? 0,
                'upahharian' => $worker['upahharian'] ?? 0,
                'upahperjam' => $worker['upahperjam'] ?? 0,
                'upahlembur' => $worker['upahlembur'] ?? 0,
                'upahborongan' => $worker['upahborongan'] ?? 0,
                'totalupah' => $worker['totalupah'] ?? 0,
                'keterangan' => $worker['keterangan'] ?? null,
                'createdat' => now()
            ];
        }

        return $details;
    }

    private function buildLkhMaterialDetails($materials, $lkhno, $companycode, $inputby)
    {
        $details = [];
        
        foreach ($materials as $material) {
            $details[] = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'plot' => $material['plot'] ?? null,
                'itemcode' => $material['itemcode'],
                'qtyditerima' => $material['qtyditerima'] ?? 0,
                'qtysisa' => $material['qtysisa'] ?? 0,
                'qtydigunakan' => ($material['qtyditerima'] ?? 0) - ($material['qtysisa'] ?? 0),
                'keterangan' => $material['keterangan'] ?? null,
                'inputby' => $inputby,
                'createdat' => now()
            ];
        }
        
        return $details;
    }

    /**
     * Authorize current user for the activitygroup of the given LKH.
     * Resolves activitygroup via lkhhdr → rkhhdr.
     * Throws AuthorizationException if not permitted.
     *
     * @param string $lkhno
     * @param string $companycode
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizeActivityGroup($lkhno, $companycode): void
    {
        $userid = \Illuminate\Support\Facades\Auth::user()->userid;

        $activitygroup = $this->masterDataRepo->getActivityGroupByLkhNo($companycode, $lkhno);

        if (!$activitygroup) {
            \Log::warning('LKH Authorization - activitygroup not found', [
                'lkhno'       => $lkhno,
                'companycode' => $companycode,
            ]);
            throw new \Illuminate\Auth\Access\AuthorizationException(
                "Activity group untuk LKH {$lkhno} tidak ditemukan"
            );
        }

        $allowed = $this->masterDataRepo->hasActivityGroupPermission($userid, $companycode, $activitygroup);

        if (!$allowed) {
            \Log::warning('LKH Authorization Failed', [
                'userid'        => $userid,
                'companycode'   => $companycode,
                'lkhno'         => $lkhno,
                'activitygroup' => $activitygroup,
            ]);
            throw new \Illuminate\Auth\Access\AuthorizationException(
                "User {$userid} tidak memiliki akses ke activity group {$activitygroup}"
            );
        }
    }
}