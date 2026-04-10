<?php

namespace App\Services\Approval;

use App\Repositories\Approval\PanenApprovalRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PanenApprovalService
 *
 * Business logic untuk approval group Panen.
 * Saat ini handles: Koreksi SJ Panen
 */
class PanenApprovalService
{
    protected $repository;

    public function __construct(PanenApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function processApproval(
        string $approvalno,
        string $companycode,
        int $level,
        string $action,
        array $userData
    ): array {
        DB::beginTransaction();

        try {
            $approval = $this->repository->findByApprovalno($companycode, $approvalno);

            if (!$approval) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Approval tidak ditemukan'];
            }

            $validation = $this->repository->validateApprovalAuthority($approval, $userData['idjabatan'], $level);

            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            $processed = $this->repository->processApproval($companycode, $approvalno, $level, $action, $userData);

            if (!$processed) {
                throw new \Exception('Gagal update approval di database');
            }

            $message = "{$approval->category} [{$approval->transactionnumber}] berhasil " .
                ($action === 'approve' ? 'disetujui' : 'ditolak');

            if ($action === 'approve') {
                $updatedApproval = $this->repository->findByApprovalno($companycode, $approvalno);

                if ($this->repository->isFullyApproved($updatedApproval)) {
                    Log::info('PanenApproval fully approved, executing post-approval', [
                        'approvalno'        => $approvalno,
                        'category'          => $approval->category,
                        'transactionnumber' => $approval->transactionnumber,
                    ]);

                    $postActionResult = $this->executePostApprovalActions($updatedApproval, $companycode);

                    if ($postActionResult['success']) {
                        $message .= $postActionResult['message'];
                    } else {
                        throw new \Exception($postActionResult['message']);
                    }
                }
            }

            DB::commit();
            return ['success' => true, 'message' => $message];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PanenApproval process failed', [
                'approvalno' => $approvalno,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Gagal memproses approval: ' . $e->getMessage()];
        }
    }

    private function executePostApprovalActions(object $approval, string $companycode): array
    {
        try {
            $category = strtoupper(trim($approval->category ?? ''));

            if ($category === 'APPROVAL KOREKSI SURAT JALAN PANEN') {
                return $this->executeKoreksiSJPanen($approval, $companycode);
            }

            return ['success' => true, 'message' => ''];

        } catch (\Exception $e) {
            Log::error('PanenApproval post-action failed', [
                'approvalno'        => $approval->approvalno,
                'transactionnumber' => $approval->transactionnumber,
                'error'             => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function executeKoreksiSJPanen(object $approval, string $companycode): array
    {
        $koreksi = $this->repository->getKoreksiSJDetail($companycode, $approval->transactionnumber);

        if (!$koreksi) {
            throw new \Exception('Data koreksi SJ tidak ditemukan untuk transaksi ' . $approval->transactionnumber);
        }

        $perubahan = json_decode($koreksi->perubahan, true);

        if (empty($perubahan)) {
            throw new \Exception('Data perubahan kosong pada koreksi ' . $approval->transactionnumber);
        }

        // Build update data dari JSON perubahan
        $updateData = ['koreksi' => 1];
        foreach ($perubahan as $field => $values) {
            $updateData[$field] = $values['baru'];
        }

        $affected = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->where('suratjalanno', $koreksi->suratjalanno)
            ->update($updateData);

        if (!$affected) {
            throw new \Exception("Gagal mengupdate data pada Surat Jalan {$koreksi->suratjalanno}");
        }

        $fieldCount = count($perubahan);
        $fieldNames = implode(', ', array_keys($perubahan));

        Log::info('KoreksiSJPanen executed successfully', [
            'transactionnumber' => $approval->transactionnumber,
            'suratjalanno'      => $koreksi->suratjalanno,
            'fields_updated'    => array_keys($perubahan),
        ]);

        return [
            'success' => true,
            'message' => ". SJ {$koreksi->suratjalanno} berhasil dikoreksi ({$fieldCount} field: {$fieldNames})",
        ];
    }
}
