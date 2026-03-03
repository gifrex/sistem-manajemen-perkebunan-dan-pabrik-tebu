<?php
namespace App\Repositories\Approval;

use Illuminate\Support\Facades\DB;

class UpahMingguanApprovalRepository
{
    // ── Lookup approval config ───────────────────────────────────────────────
    public function findApprovalConfig(string $companycode): ?object
    {
        return DB::table('approval')
            ->where('companycode', $companycode)
            ->where('category', 'Approval Pembayaran Upah Mingguan')
            ->first();
    }

    // ── Find approvaltransaction by transno ──────────────────────────────────
    public function findByTransno(string $companycode, string $transno): ?object
    {
        return DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transno)
            ->first();
    }

    // ── Find pembayaranupahhdr ────────────────────────────────────────────────
    public function findHeader(string $companycode, string $transno): ?object
    {
        return DB::table('pembayaranupahhdr')
            ->where('companycode', $companycode)
            ->where('transno', $transno)
            ->first();
    }

    // ── Create approval transaction row + auto-approve level 1 ───────────────
    public function createApprovalTransaction(
        string $companycode,
        string $transno,
        object $config,
        string $level1UserId,
        int $level1Jabatan
    ): bool {
        $now = now();

        // Generate approvalno: APV[YYMMDD][SEQ]
        $approvalno = $this->generateApprovalno($now, $companycode);

        return DB::table('approvaltransaction')->insert([
            'approvalno' => $approvalno,
            'companycode' => $companycode,
            'approvalcategoryid' => $config->id,
            'transactionnumber' => $transno,
            'jumlahapproval' => $config->jumlahapproval,
            // Level 1 — auto-approved by the generator (admin perawatan)
            'approval1idjabatan' => $level1Jabatan,
            'approval1userid' => $level1UserId,
            'approval1flag' => '1',
            'approval1date' => $now,
            // Level 2 onward — prefill jabatan from config, userid/flag/date NULL
            'approval2idjabatan' => $config->idjabatanapproval2 ?? null,
            'approval2userid' => null,
            'approval2flag' => null,
            'approval2date' => null,
            'approval3idjabatan' => $config->idjabatanapproval3 ?? null,
            'approval3userid' => null,
            'approval3flag' => null,
            'approval3date' => null,
            'approval4idjabatan' => $config->idjabatanapproval4 ?? null,
            'approval4userid' => null,
            'approval4flag' => null,
            'approval4date' => null,
            'approval5idjabatan' => $config->idjabatanapproval5 ?? null,
            'approval5userid' => null,
            'approval5flag' => null,
            'approval5date' => null,
            // Overall status: NULL = pending (waiting next level)
            'approvalstatus' => $config->jumlahapproval == 1 ? '1' : null,
            'inputby' => $level1UserId,
            'createdat' => $now,
            'updatedat' => $now,
        ]);
    }

    // ── Process approve / decline for a level ────────────────────────────────
    public function processApproval(
        string $companycode,
        string $transno,
        int $level,
        string $action,
        array $userData
    ): bool {
        $flag = $action === 'approve' ? '1' : '0';
        $now = now();
        $col = "approval{$level}";
        $status = null;

        if ($action === 'decline') {
            $status = '0'; // declined
        }

        $updates = [
            "{$col}userid" => $userData['userid'],
            "{$col}flag" => $flag,
            "{$col}date" => $now,
            'updatedat' => $now,
            'updatedby' => $userData['userid'],
        ];
        if ($status !== null) {
            $updates['approvalstatus'] = $status;
        }

        return (bool) DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transno)
            ->update($updates);
    }

    // ── Mark approvaltransaction as fully approved ────────────────────────────
    public function markFullyApproved(string $companycode, string $transno, string $userid): bool
    {
        return (bool) DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $transno)
            ->update([
                'approvalstatus' => '1',
                'updatedat' => now(),
                'updatedby' => $userid,
            ]);
    }

    // ── Update pembayaranupahhdr status ──────────────────────────────────────
    public function updateHeaderStatus(string $companycode, string $transno, string $status): bool
    {
        return (bool) DB::table('pembayaranupahhdr')
            ->where('companycode', $companycode)
            ->where('transno', $transno)
            ->update(['approvalstatus' => $status, 'updatedat' => now()]);
    }

    // ── Validate approver authority ──────────────────────────────────────────
    public function validateApprovalAuthority(object $trx, int $idjabatan, int $level): array
    {
        $jabatanCol = "approval{$level}idjabatan";
        $flagCol = "approval{$level}flag";

        if (!isset($trx->$jabatanCol) || $trx->$jabatanCol != $idjabatan) {
            return ['success' => false, 'message' => "Anda tidak memiliki wewenang untuk approval level {$level}"];
        }
        if ($trx->$flagCol !== null) {
            return ['success' => false, 'message' => "Level {$level} sudah diproses sebelumnya"];
        }
        // Check previous levels are approved
        for ($i = 1; $i < $level; $i++) {
            $prevFlag = "approval{$i}flag";
            if ($trx->$prevFlag !== '1') {
                return ['success' => false, 'message' => "Level sebelumnya belum disetujui"];
            }
        }
        return ['success' => true];
    }

    // ── Check if fully approved ──────────────────────────────────────────────
    public function isFullyApproved(object $trx): bool
    {
        $total = intval($trx->jumlahapproval ?? 0);
        if ($total === 0)
            return true;
        for ($i = 1; $i <= $total; $i++) {
            $col = "approval{$i}flag";
            if (($trx->$col ?? null) !== '1')
                return false;
        }
        return true;
    }

    // ── Approval history (all levels summary) ────────────────────────────────
    public function getApprovalHistory(string $companycode, string $transno): ?object
    {
        return DB::table('approvaltransaction as at')
            ->leftJoin('approval as a', 'a.id', '=', 'at.approvalcategoryid')
            ->where('at.companycode', $companycode)
            ->where('at.transactionnumber', $transno)
            ->select('at.*', 'a.category', 'a.jumlahapproval as config_jumlah')
            ->first();
    }

    // ── Generate approvalno APV[YYMMDD][SEQ] ─────────────────────────────────
    private function generateApprovalno(\Carbon\Carbon $now, string $companycode): string
    {
        $yymmdd = $now->format('ymd');
        $like = "APV{$yymmdd}%";

        $last = DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('approvalno', 'like', $like)
            ->orderByDesc(DB::raw('CAST(SUBSTRING(approvalno, 10) AS UNSIGNED)'))
            ->value('approvalno');

        $seq = $last ? (int) substr($last, 9) : 0;
        $seq++;

        return "APV{$yymmdd}" . str_pad($seq, 2, '0', STR_PAD_LEFT);
    }
}