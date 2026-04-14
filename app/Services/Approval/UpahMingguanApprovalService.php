<?php

namespace App\Services\Approval;

use App\Repositories\Approval\UpahMingguanApprovalRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpahMingguanApprovalService
{
    protected UpahMingguanApprovalRepository $repository;

    public function __construct(UpahMingguanApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createApprovalAfterGenerate(
        string $companycode,
        string $transno,
        string $userid,
        int $idjabatan
    ): array {
        $config = $this->repository->findApprovalConfig($companycode);

        if (!$config) {
            Log::warning("Approval config not found for Upah Mingguan", ['companycode' => $companycode]);
            // Non-blocking: generate still succeeds
            return ['success' => false, 'message' => 'Konfigurasi approval tidak ditemukan'];
        }

        $ok = $this->repository->createApprovalTransaction(
            $companycode,
            $transno,
            $config,
            $userid,
            $idjabatan
        );

        if (!$ok) {
            return ['success' => false, 'message' => 'Gagal membuat record approval'];
        }

        // If jumlahapproval == 1, auto fully approved
        if (intval($config->jumlahapproval) === 1) {
            $this->repository->updateHeaderStatus($companycode, $transno, 'APPROVED');
        }

        return ['success' => true];
    }

    // ── Main approval process (approve / decline) ────────────────────────────
    public function processApproval(
        string $transno,
        string $companycode,
        int $level,
        string $action,
        array $userData
    ): array {
        DB::beginTransaction();
        try {
            $trx = $this->repository->findByTransno($companycode, $transno);
            if (!$trx) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Data approval tidak ditemukan'];
            }

            // Validate authority
            $validation = $this->repository->validateApprovalAuthority($trx, $userData['idjabatan'], $level);
            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            // Process
            $processed = $this->repository->processApproval(
                $companycode,
                $transno,
                $level,
                $action,
                $userData
            );
            if (!$processed) {
                throw new \Exception('Gagal update approval di database');
            }

            $label = $action === 'approve' ? 'disetujui' : 'ditolak';
            $message = "Transaksi {$transno} berhasil {$label} (Level {$level})";

            if ($action === 'approve') {
                // Refresh
                $updatedTrx = $this->repository->findByTransno($companycode, $transno);
                if ($this->repository->isFullyApproved($updatedTrx)) {
                    $this->repository->markFullyApproved($companycode, $transno, $userData['userid']);
                    $this->repository->updateHeaderStatus($companycode, $transno, 'APPROVED');
                    $message .= '. Transaksi telah fully approved.';
                }
            } else {
                // Declined — update header status
                $this->repository->updateHeaderStatus($companycode, $transno, 'DECLINED');
            }

            DB::commit();
            return ['success' => true, 'message' => $message];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Upah approval failed", ['transno' => $transno, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal memproses approval: ' . $e->getMessage()];
        }
    }

    // ── Batch approve / decline (multiple transno, same mandor) ─────────────
    public function processApprovalBatch(
        array $transnoList,
        string $companycode,
        int $level,
        string $action,
        array $userData
    ): array {
        DB::beginTransaction();
        try {
            foreach ($transnoList as $transno) {
                $trx = $this->repository->findByTransno($companycode, $transno);
                if (!$trx) {
                    DB::rollBack();
                    return ['success' => false, 'message' => "Data approval tidak ditemukan: {$transno}"];
                }

                $validation = $this->repository->validateApprovalAuthority($trx, $userData['idjabatan'], $level);
                if (!$validation['success']) {
                    DB::rollBack();
                    return $validation;
                }

                $ok = $this->repository->processApproval($companycode, $transno, $level, $action, $userData);
                if (!$ok) {
                    throw new \Exception("Gagal update approval {$transno}");
                }

                if ($action === 'approve') {
                    $updatedTrx = $this->repository->findByTransno($companycode, $transno);
                    if ($this->repository->isFullyApproved($updatedTrx)) {
                        $this->repository->markFullyApproved($companycode, $transno, $userData['userid']);
                        $this->repository->updateHeaderStatus($companycode, $transno, 'APPROVED');
                    }
                } else {
                    $this->repository->updateHeaderStatus($companycode, $transno, 'DECLINED');
                }
            }

            DB::commit();
            $label = $action === 'approve' ? 'disetujui' : 'ditolak';
            $count = count($transnoList);
            return ['success' => true, 'message' => "{$count} transaksi berhasil {$label}"];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Upah approval batch failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal memproses approval: ' . $e->getMessage()];
        }
    }

    // ── Get grouped approval detail (multiple transno per mandor) ────────────
    public function getApprovalDetailGroup(array $transnoList, string $companycode): array
    {
        $transactions = [];
        $history = null;
        $firstHeader = null;
        $firstTrx = null;

        foreach ($transnoList as $transno) {
            $trx = $this->repository->findByTransno($companycode, $transno);
            if (!$trx)
                continue;

            $hdr = $this->repository->findHeaderWithActivity($companycode, $transno);
            $workers = $this->repository->getWorkers($companycode, $transno);

            if (!$history) {
                $history = $this->repository->getApprovalHistory($companycode, $transno);
                $firstTrx = $trx;
                $firstHeader = $hdr;
            }

            $transactions[] = [
                'transno' => $transno,
                'activityname' => $hdr->activityname ?? '-',
                'startdate' => $hdr->startdate ?? null,
                'enddate' => $hdr->enddate ?? null,
                'generatedate' => $hdr->generatedate ?? null,
                'grandtotal' => $hdr->grandtotal ?? 0,
                'jenistenagakerja' => $hdr->jenistenagakerja ?? null,
                'workers' => $workers->map(fn($w) => [
                    'tenagakerjaid' => $w->tenagakerjaid,
                    'worker_name' => $w->worker_name ?? '-',
                    'totalupah' => $w->totalupah ?? 0,
                    'totalupah_fmt' => \Illuminate\Support\Number::currency($w->totalupah ?? 0, 'IDR', 'id'),
                ])->values()->toArray(),
            ];
        }

        if (!$firstTrx) {
            return ['success' => false, 'message' => 'Data approval tidak ditemukan'];
        }

        $status = $this->buildApprovalStatus($firstTrx);

        // Format history
        $historyData = null;
        if ($history) {
            $levels = [];
            for ($i = 1; $i <= ($history->jumlahapproval ?? 0); $i++) {
                $levels[] = [
                    'level' => $i,
                    'jabatan' => $history->{"jabatan{$i}_name"} ?? '-',
                    'user' => $history->{"approval{$i}_user_name"} ?? null,
                    'flag' => $history->{"approval{$i}flag"} ?? null,
                    'date' => $history->{"approval{$i}date"} ?? null,
                ];
            }
            $historyData = [
                'jumlahapproval' => $history->jumlahapproval,
                'levels' => $levels,
            ];
        }

        return [
            'success' => true,
            'header' => $firstHeader,
            'history' => $historyData,
            'transactions' => $transactions,
            'status' => $status,
        ];
    }

    // ── Get approval detail for a transno ────────────────────────────────────
    public function getApprovalDetail(string $transno, string $companycode): array
    {
        $trx = $this->repository->findByTransno($companycode, $transno);
        if (!$trx) {
            return ['success' => false, 'message' => 'Data approval tidak ditemukan'];
        }

        $header = $this->repository->findHeader($companycode, $transno);
        $history = $this->repository->getApprovalHistory($companycode, $transno);
        $workers = $this->repository->getWorkers($companycode, $transno);
        $status = $this->buildApprovalStatus($trx);

        return [
            'success' => true,
            'data' => compact('trx', 'header', 'history', 'workers', 'status'),
        ];
    }

    // ── Build display status ──────────────────────────────────────────────────
    private function buildApprovalStatus(object $trx): array
    {
        if (!$trx->jumlahapproval || $trx->jumlahapproval == 0) {
            return ['status' => 'no_approval', 'message' => 'No Approval Required', 'color' => 'gray'];
        }

        for ($i = 1; $i <= $trx->jumlahapproval; $i++) {
            $col = "approval{$i}flag";
            if (($trx->$col ?? null) === '0') {
                return ['status' => 'declined', 'message' => 'Declined', 'color' => 'red'];
            }
        }

        if ($this->repository->isFullyApproved($trx)) {
            return ['status' => 'approved', 'message' => 'Approved', 'color' => 'green'];
        }

        $waitingLevel = 1;
        for ($i = 1; $i < $trx->jumlahapproval; $i++) {
            $col = "approval{$i}flag";
            if (($trx->$col ?? null) === '1')
                $waitingLevel = $i + 1;
        }

        return ['status' => 'waiting', 'message' => "Waiting Level {$waitingLevel}", 'color' => 'yellow'];
    }
}
