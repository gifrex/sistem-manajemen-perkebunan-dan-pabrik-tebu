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

    // ── Get approval detail for a transno ────────────────────────────────────
    public function getApprovalDetail(string $transno, string $companycode): array
    {
        $trx = $this->repository->findByTransno($companycode, $transno);
        if (!$trx) {
            return ['success' => false, 'message' => 'Data approval tidak ditemukan'];
        }

        $header = $this->repository->findHeader($companycode, $transno);
        $history = $this->repository->getApprovalHistory($companycode, $transno);
        $status = $this->buildApprovalStatus($trx);

        return [
            'success' => true,
            'data' => compact('trx', 'header', 'history', 'status'),
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
