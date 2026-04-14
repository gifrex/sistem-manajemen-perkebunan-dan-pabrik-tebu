<?php

namespace App\Repositories\Approval;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UpahMingguanApprovalRepository
 *
 * Handles Upah Mingguan approval queries (approvaltransaction + pembayaranupahhdr)
 * RULE: All Upah Mingguan approval-related queries here
 */
class UpahMingguanApprovalRepository
{
    /**
     * Get pending Upah Mingguan approvals for specific user jabatan
     *
     * @param string $companycode
     * @param int    $idjabatan
     * @param array  $filters ['date' => 'Y-m-d', 'all_date' => bool]
     * @return Collection
     */
    public function getPendingApprovals(string $companycode, int $idjabatan, array $filters = []): Collection
    {
        $query = DB::table('approvaltransaction as at')
            ->join('pembayaranupahhdr as p', function ($j) {
                $j->on('p.transno', '=', 'at.transactionnumber')
                    ->on('p.companycode', '=', 'at.companycode');
            })
            ->leftJoin('user as u', function ($j) {
                $j->on('u.userid', '=', 'p.mandoruserid')
                    ->on('u.companycode', '=', 'p.companycode');
            })
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->whereIn('at.approvalcategoryid', function ($q) use ($companycode) {
                $q->select('id')
                    ->from('approval')
                    ->where('companycode', $companycode)
                    ->where('category', 'Approval Pembayaran Upah Mingguan');
            })
            ->where('at.companycode', $companycode)
            ->whereNull('at.approvalstatus')
            ->where(function ($q) use ($idjabatan) {
                $q->where(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval1idjabatan', $idjabatan)
                        ->whereNull('at.approval1flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval2idjabatan', $idjabatan)
                        ->where('at.approval1flag', '1')
                        ->whereNull('at.approval2flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval3idjabatan', $idjabatan)
                        ->where('at.approval2flag', '1')
                        ->whereNull('at.approval3flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval4idjabatan', $idjabatan)
                        ->where('at.approval3flag', '1')
                        ->whereNull('at.approval4flag');
                })->orWhere(function ($q2) use ($idjabatan) {
                    $q2->where('at.approval5idjabatan', $idjabatan)
                        ->where('at.approval4flag', '1')
                        ->whereNull('at.approval5flag');
                });
            })
            ->when(!($filters['all_date'] ?? true) && !empty($filters['date']), function ($q) use ($filters) {
                $q->whereDate('p.generatedate', $filters['date']);
            })
            ->select(
                'at.approvalno',
                'at.transactionnumber as transno',
                'at.jumlahapproval',
                'at.approvalstatus',
                'at.approval1idjabatan',
                'at.approval1flag',
                'at.approval2idjabatan',
                'at.approval2flag',
                'at.approval3idjabatan',
                'at.approval3flag',
                'at.approval4idjabatan',
                'at.approval4flag',
                'at.approval5idjabatan',
                'at.approval5flag',
                'p.startdate',
                'p.enddate',
                'p.grandtotal',
                'p.jenistenagakerja',
                'p.generatedate',
                'p.mandoruserid',
                'p.activitycode',
                'ac.activityname',
                'u.name as mandorname',
                DB::raw("
                    CASE
                        WHEN at.approval1idjabatan = {$idjabatan} AND at.approval1flag IS NULL THEN 1
                        WHEN at.approval2idjabatan = {$idjabatan} AND at.approval1flag = '1' AND at.approval2flag IS NULL THEN 2
                        WHEN at.approval3idjabatan = {$idjabatan} AND at.approval2flag = '1' AND at.approval3flag IS NULL THEN 3
                        WHEN at.approval4idjabatan = {$idjabatan} AND at.approval3flag = '1' AND at.approval4flag IS NULL THEN 4
                        WHEN at.approval5idjabatan = {$idjabatan} AND at.approval4flag = '1' AND at.approval5flag IS NULL THEN 5
                        ELSE NULL
                    END as approval_level
                ")
            )
            ->orderByDesc('p.generatedate')
            ->get();

        if ($query->isNotEmpty()) {
            $transNos = $query->pluck('transno')->toArray();
            $workerCounts = DB::table('pembayaranupahlst')
                ->whereIn('transno', $transNos)
                ->where('companycode', $companycode)
                ->groupBy('transno')
                ->select('transno', DB::raw('COUNT(DISTINCT tenagakerjaid) as totalworkers'))
                ->pluck('totalworkers', 'transno');

            foreach ($query as $item) {
                $item->totalworkers = $workerCounts[$item->transno] ?? 0;
                $item->jenis_label = $item->jenistenagakerja == 1 ? 'Harian' : 'Borongan';
            }
        }

        return $query;
    }

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
            ->select('*', DB::raw('transactionnumber as transno'))
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

    // ── Find pembayaranupahhdr with activity name joined ─────────────────────
    public function findHeaderWithActivity(string $companycode, string $transno): ?object
    {
        return DB::table('pembayaranupahhdr as p')
            ->leftJoin('activity as ac', 'ac.activitycode', '=', 'p.activitycode')
            ->where('p.companycode', $companycode)
            ->where('p.transno', $transno)
            ->select('p.*', 'ac.activityname')
            ->first();
    }

    // ── Get pembayaranupahlst (worker detail lines) ──────────────────────────
    public function getWorkers(string $companycode, string $transno): Collection
    {
        return DB::table('pembayaranupahlst as l')
            ->leftJoin('tenagakerja as tk', function ($j) use ($companycode) {
                $j->on('l.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode);
            })
            ->where('l.companycode', $companycode)
            ->where('l.transno', $transno)
            ->groupBy('l.tenagakerjaid', 'tk.nama')
            ->select(
                'l.tenagakerjaid',
                'tk.nama as worker_name',
                DB::raw('SUM(l.total) as totalupah')
            )
            ->orderBy('l.tenagakerjaid')
            ->get();
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

        $updates = [
            "{$col}userid" => $userData['userid'],
            "{$col}flag" => $flag,
            "{$col}date" => $now,
            'updatedat' => $now,
            'updateby' => $userData['userid'],
        ];

        if ($action === 'decline') {
            $updates['approvalstatus'] = '0';
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
                'updateby' => $userid,
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

        for ($i = 1; $i < $level; $i++) {
            $prevFlag = "approval{$i}flag";
            if ($trx->$prevFlag !== '1') {
                return ['success' => false, 'message' => 'Level sebelumnya belum disetujui'];
            }
        }

        return ['success' => true];
    }

    // ── Check if fully approved ──────────────────────────────────────────────
    public function isFullyApproved(object $trx): bool
    {
        $total = intval($trx->jumlahapproval ?? 0);
        if ($total === 0) {
            return true;
        }
        for ($i = 1; $i <= $total; $i++) {
            $col = "approval{$i}flag";
            if (($trx->$col ?? null) !== '1') {
                return false;
            }
        }
        return true;
    }

    // ── Approval history with joined user & jabatan names ────────────────────
    public function getApprovalHistory(string $companycode, string $transno): ?object
    {
        return DB::table('approvaltransaction as at')
            ->leftJoin('approval as a', 'a.id', '=', 'at.approvalcategoryid')
            ->leftJoin('user as u1', 'u1.userid', '=', 'at.approval1userid')
            ->leftJoin('user as u2', 'u2.userid', '=', 'at.approval2userid')
            ->leftJoin('user as u3', 'u3.userid', '=', 'at.approval3userid')
            ->leftJoin('user as u4', 'u4.userid', '=', 'at.approval4userid')
            ->leftJoin('user as u5', 'u5.userid', '=', 'at.approval5userid')
            ->leftJoin('jabatan as j1', 'j1.idjabatan', '=', 'at.approval1idjabatan')
            ->leftJoin('jabatan as j2', 'j2.idjabatan', '=', 'at.approval2idjabatan')
            ->leftJoin('jabatan as j3', 'j3.idjabatan', '=', 'at.approval3idjabatan')
            ->leftJoin('jabatan as j4', 'j4.idjabatan', '=', 'at.approval4idjabatan')
            ->leftJoin('jabatan as j5', 'j5.idjabatan', '=', 'at.approval5idjabatan')
            ->where('at.companycode', $companycode)
            ->where('at.transactionnumber', $transno)
            ->select([
                'at.approvalno',
                'at.transactionnumber as transno',
                'at.jumlahapproval',
                'at.approvalstatus',
                'a.category',
                // Level 1
                'at.approval1idjabatan',
                'at.approval1flag',
                'at.approval1date',
                'at.approval1userid',
                'u1.name as approval1_user_name',
                'j1.namajabatan as jabatan1_name',
                // Level 2
                'at.approval2idjabatan',
                'at.approval2flag',
                'at.approval2date',
                'at.approval2userid',
                'u2.name as approval2_user_name',
                'j2.namajabatan as jabatan2_name',
                // Level 3
                'at.approval3idjabatan',
                'at.approval3flag',
                'at.approval3date',
                'at.approval3userid',
                'u3.name as approval3_user_name',
                'j3.namajabatan as jabatan3_name',
                // Level 4
                'at.approval4idjabatan',
                'at.approval4flag',
                'at.approval4date',
                'at.approval4userid',
                'u4.name as approval4_user_name',
                'j4.namajabatan as jabatan4_name',
                // Level 5
                'at.approval5idjabatan',
                'at.approval5flag',
                'at.approval5date',
                'at.approval5userid',
                'u5.name as approval5_user_name',
                'j5.namajabatan as jabatan5_name',
            ])
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
