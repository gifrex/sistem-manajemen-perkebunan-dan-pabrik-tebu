<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;

/**
 * RkhRepository
 * 
 * Handles RKH (rkhhdr + rkhlst) database operations.
 * RULE: All RKH queries here. Can join batch for display (avoid N+1).
 */
class RkhRepository
{
    /**
     * Get RKH header with approval metadata + mandor name
     * Used in: show page
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function getHeader($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                DB::raw('
                CASE 
                    WHEN r.approval1flag = "0" THEN "Declined by Level 1"
                    WHEN r.approval2flag = "0" THEN "Declined by Level 2"
                    WHEN r.approval3flag = "0" THEN "Declined by Level 3"
                    WHEN r.approvalstatus = "1" THEN "Approved"
                    WHEN app.jumlahapproval IS NULL OR app.jumlahapproval = 0 THEN "No Approval Required"
                    WHEN r.approval1flag IS NULL AND app.idjabatanapproval1 IS NOT NULL THEN "Waiting for Approval"
                    WHEN r.approval1flag = "1" AND app.idjabatanapproval2 IS NOT NULL AND r.approval2flag IS NULL THEN "Waiting for Approval"
                    WHEN r.approval2flag = "1" AND app.idjabatanapproval3 IS NOT NULL AND r.approval3flag IS NULL THEN "Waiting for Approval"
                    ELSE "Waiting for Approval"
                END as approval_status'),
                DB::raw('CASE 
                    WHEN r.status = "Completed" THEN "Completed"
                    ELSE "In Progress"
                END as current_status')
            ])
            ->first();
    }

    /**
     * Get RKH header for edit (minimal fields)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function getHeaderForEdit($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select(['r.*', 'm.name as mandor_nama'])
            ->first();
    }

    /**
     * Get RKH details (rkhlst) with workers + batch info
     * Used in: show page
     * ALLOWED: Join batch directly (avoid N+1)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param string|null $rkhDate Optional for progress subquery
     * @return \Illuminate\Support\Collection
     */
    public function getDetails($companycode, $rkhno, $rkhDate = null)
    {
        $query = DB::table('rkhlst as r')
            ->leftJoin('rkhlstworker as w', function($join) {
                $join->on('r.companycode', '=', 'w.companycode')
                    ->on('r.rkhno', '=', 'w.rkhno')
                    ->on('r.activitycode', '=', 'w.activitycode');
            })
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->leftJoin('activitygroup as ag', 'a.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('jenistenagakerja as jtk', 'a.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->leftJoin('batch as b', 'r.batchid', '=', 'b.id')
            ->leftJoin('masterlist as m', function($join) use ($companycode) {
                $join->on('r.plot', '=', 'm.plot')
                    ->where('m.companycode', '=', $companycode);
            })
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno);

        $selectFields = [
            'r.*',
            'w.jumlahlaki',
            'w.jumlahperempuan',
            'w.jumlahtenagakerja',
            'hg.herbisidagroupname',
            'a.activityname',
            'ag.groupname as activity_group_name',
            'jtk.nama as jenistenagakerja_nama',
            'a.jenistenagakerja',
            'a.isblokactivity',
            'b.batchno as batch_number',
            'b.lifecyclestatus as batch_lifecycle',
            'b.batcharea',
            'b.tanggalpanen',
            'm.blok as masterlist_blok',
            DB::raw("CASE 
                WHEN r.blok = 'ALL' THEN 'Semua Blok'
                WHEN r.plot IS NULL THEN CONCAT('Blok: ', r.blok)
                ELSE CONCAT(COALESCE(m.blok, r.blok), '-', r.plot)
            END as location_display")
        ];

        // Add progress subquery if rkhDate provided
        if ($rkhDate) {
            $selectFields[] = DB::raw("(
                SELECT COALESCE(SUM(ldp.luashasil), 0)
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno 
                            AND ldp.companycode = lh.companycode
                WHERE ldp.plot = r.plot
                AND lh.activitycode = r.activitycode
                AND lh.approvalstatus = '1'
                AND lh.lkhdate < '{$rkhDate}'
            ) as total_sudah_dikerjakan");
        }

        return $query->select($selectFields)->get();
    }

    /**
     * Get RKH details for edit (simpler join, no subquery)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return \Illuminate\Support\Collection
     */
    public function getDetailsForEdit($companycode, $rkhno)
    {
        return DB::table('rkhlst as r')
            ->leftJoin('rkhlstworker as w', function($join) {
                $join->on('r.companycode', '=', 'w.companycode')
                    ->on('r.rkhno', '=', 'w.rkhno')
                    ->on('r.activitycode', '=', 'w.activitycode');
            })
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->leftJoin('activitygroup as ag', 'a.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('jenistenagakerja as jtk', 'a.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->leftJoin('batch as b', 'r.batchid', '=', 'b.id')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'w.jumlahlaki',
                'w.jumlahperempuan',
                'w.jumlahtenagakerja',
                'hg.herbisidagroupname',
                'a.activityname',
                'a.usingvehicle',
                'a.usingmaterial',
                'a.isblokactivity',
                'ag.groupname as activity_group_name',
                'jtk.nama as jenistenagakerja_nama',
                'a.jenistenagakerja',
                'b.batchno as batch_number',
                'b.lifecyclestatus as batch_lifecycle',
                'b.batcharea'
            ])
            ->get();
    }

    /**
     * Get latest outstanding RKH by mandor
     * (status != Completed AND status != Batal)
     * 
     * @param string $companycode
     * @param string $mandorId
     * @return object|null
     */
    public function getLatestOutstandingByMandor($companycode, $mandorId)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('mandorid', $mandorId)
            ->where(function($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['Completed', 'Batal']);
            })
            ->orderBy('rkhdate', 'desc')
            ->select(['rkhno', 'rkhdate', 'status'])
            ->first();
    }

    /**
     * Get RKH by mandor and date (exclude Batal)
     * 
     * @param string $companycode
     * @param string $mandorId
     * @param string $date
     * @return object|null
     */
    public function getRkhByMandorAndDate($companycode, $mandorId, $date)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('mandorid', $mandorId)
            ->whereDate('rkhdate', $date)
            ->where(function($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'Batal');
            })
            ->first(['rkhno', 'rkhdate', 'status']);
    }

    /**
     * Insert RKH header and return surrogate ID
     * 
     * @param array $data
     * @return int
     */
    public function insertHeaderReturnId(array $data)
    {
        return DB::table('rkhhdr')->insertGetId($data);
    }

    /**
     * Update RKH header
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param array $data
     * @return int
     */
    public function updateHeader($companycode, $rkhno, array $data)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update($data);
    }

    /**
     * Delete RKH header
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return int
     */
    public function deleteHeader($companycode, $rkhno)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * Delete all RKH details (rkhlst)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return int
     */
    public function deleteDetails($companycode, $rkhno)
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
    }

    /**
     * Bulk insert RKH details
     * 
     * @param array $rows
     * @return bool
     */
    public function insertDetails(array $rows)
    {
        if (empty($rows)) {
            return false;
        }

        return DB::table('rkhlst')->insert($rows);
    }

    /**
     * Paginate RKH index with filters
     * Updated: Handle 'Batal' status
     * 
     * @param string $companycode
     * @param array $filters [search, filterApproval, filterStatus, filterDate, allDate, showBatal]
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateIndex($companycode, array $filters, $perPage)
    {
        $query = DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('user as bataluser', 'r.batalby', '=', 'bataluser.userid') // JOIN untuk nama pembatal
            ->where('r.companycode', $companycode)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                'r.approvalstatus',
                'bataluser.name as batal_by_nama',

                DB::raw('(SELECT COUNT(*) FROM lkhhdr 
                      WHERE lkhhdr.companycode = r.companycode 
                      AND lkhhdr.rkhno = r.rkhno 
                      AND lkhhdr.status != "EMPTY") as non_empty_lkh_count'),

                DB::raw('
                CASE 
                    WHEN r.approval1flag = "0" THEN "Declined by Level 1"
                    WHEN r.approval2flag = "0" THEN "Declined by Level 2"
                    WHEN r.approval3flag = "0" THEN "Declined by Level 3"
                    WHEN r.approvalstatus = "1" THEN "Approved"
                    WHEN app.jumlahapproval IS NULL OR app.jumlahapproval = 0 THEN "No Approval Required"
                    WHEN r.approval1flag IS NULL AND app.idjabatanapproval1 IS NOT NULL THEN "Waiting for Approval"
                    WHEN r.approval1flag = "1" AND app.idjabatanapproval2 IS NOT NULL AND r.approval2flag IS NULL THEN "Waiting for Approval"
                    WHEN r.approval2flag = "1" AND app.idjabatanapproval3 IS NOT NULL AND r.approval3flag IS NULL THEN "Waiting for Approval"
                    ELSE "Waiting for Approval"
                END as approval_status'),
                
                DB::raw('CASE 
                    WHEN r.status = "Batal" THEN "Batal"
                    WHEN r.status = "Completed" THEN "Completed"
                    ELSE "In Progress"
                END as current_status')
            ]);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where('r.rkhno', 'like', '%' . $filters['search'] . '%');
        }

        // Apply approval filter
        if (!empty($filters['filterApproval'])) {
            $this->applyApprovalFilter($query, $filters['filterApproval']);
        }

        // Apply status filter (UPDATED: handle Batal)
        if (!empty($filters['filterStatus'])) {
            if ($filters['filterStatus'] === 'Completed') {
                $query->where('r.status', 'Completed');
            } elseif ($filters['filterStatus'] === 'Batal') {
                $query->where('r.status', 'Batal');
            } elseif ($filters['filterStatus'] === 'In Progress') {
                $query->where(function($q) {
                    $q->whereNull('r.status')
                    ->orWhereNotIn('r.status', ['Completed', 'Batal']);
                });
            }
        }

        if (!empty($filters['filterStatus'])) {
            if ($filters['filterStatus'] === 'Completed') {
                $query->where('r.status', 'Completed');
            } elseif ($filters['filterStatus'] === 'Batal') {
                $query->where('r.status', 'Batal');
            } elseif ($filters['filterStatus'] === 'In Progress') {
                $query->where(function($q) {
                    $q->whereNull('r.status')
                    ->orWhereNotIn('r.status', ['Completed', 'Batal']);
                });
            }
        }

        // Apply date filter (ONLY if allDate is false)
        if (empty($filters['allDate'])) {
            $dateToFilter = $filters['filterDate'] ?? date('Y-m-d');
            $query->whereDate('r.rkhdate', $dateToFilter);
        }

        $query->orderBy('r.rkhdate', 'desc')
            ->orderBy('r.rkhno', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Apply approval filter to query
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $filterApproval
     * @return void
     */
    private function applyApprovalFilter($query, $filterApproval)
    {
        switch ($filterApproval) {
            case 'Approved':
                $query->where(function($q) {
                    $q->where(function($subq) {
                        $subq->where('app.jumlahapproval', 1)->where('r.approval1flag', '1');
                    })->orWhere(function($subq) {
                        $subq->where('app.jumlahapproval', 2)->where('r.approval1flag', '1')->where('r.approval2flag', '1');
                    })->orWhere(function($subq) {
                        $subq->where('app.jumlahapproval', 3)->where('r.approval1flag', '1')->where('r.approval2flag', '1')->where('r.approval3flag', '1');
                    })->orWhere(function($subq) {
                        $subq->whereNull('app.jumlahapproval')->orWhere('app.jumlahapproval', 0);
                    });
                });
                break;
            case 'Waiting':
                $query->where(function($q) {
                    $q->where(function($subq) {
                        $subq->whereNotNull('app.idjabatanapproval1')->whereNull('r.approval1flag');
                    })->orWhere(function($subq) {
                        $subq->whereNotNull('app.idjabatanapproval2')->where('r.approval1flag', '1')->whereNull('r.approval2flag');
                    })->orWhere(function($subq) {
                        $subq->whereNotNull('app.idjabatanapproval3')->where('r.approval1flag', '1')->where('r.approval2flag', '1')->whereNull('r.approval3flag');
                    });
                });
                break;
            case 'Decline':
                $query->where(function($q) {
                    $q->where('r.approval1flag', '0')->orWhere('r.approval2flag', '0')->orWhere('r.approval3flag', '0');
                });
                break;
        }
    }

    /**
     * Get LKH progress for multiple RKH numbers (batch load)
     * Returns collection grouped by rkhno
     * 
     * @param string $companycode
     * @param array $rkhNos
     * @return \Illuminate\Support\Collection
     */
    public function getLkhProgressForRkhNos($companycode, array $rkhNos)
    {
        return DB::table('lkhhdr')
            ->whereIn('rkhno', $rkhNos)
            ->where('companycode', $companycode)
            ->select('rkhno', 'status')
            ->get()
            ->groupBy('rkhno')
            ->map(function($lkhs) {
                if ($lkhs->isEmpty()) {
                    return [
                        'status' => 'no_lkh',
                        'progress' => 'No LKH Created',
                        'can_complete' => false,
                        'color' => 'gray'
                    ];
                }
                
                $totalLkh = $lkhs->count();
                $completedLkh = $lkhs->where('status', 'APPROVED')->count();
                
                if ($completedLkh === $totalLkh) {
                    return [
                        'status' => 'complete',
                        'progress' => 'All Complete',
                        'can_complete' => true,
                        'color' => 'green'
                    ];
                } else {
                    return [
                        'status' => 'in_progress',
                        'progress' => "LKH In Progress ({$completedLkh}/{$totalLkh})",
                        'can_complete' => false,
                        'color' => 'yellow'
                    ];
                }
            });
    }

    /**
     * Get RKH numbers by date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getRkhNumbersByDate($companycode, $date)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $date)
            ->pluck('rkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get last RKH sequence for date with lock
     * Used in number generation
     * 
     * @param string $companycode
     * @param string $date
     * @return object|null
     */
    public function getLastRkhSequenceForDateWithLock($companycode, $date)
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        $day = $carbonDate->format('d');
        $month = $carbonDate->format('m');
        $year = $carbonDate->format('y');

        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $carbonDate->format('Y-m-d'))
            ->where('rkhno', 'like', "RKH{$day}{$month}%{$year}")
            ->lockForUpdate()
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();
    }

    /**
     * Check if RKH number exists
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function existsRkhNo($companycode, $rkhno)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->exists();
    }

    /**
     * Get RKH approval detail with all approval metadata
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function getApprovalDetail($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('user as u1', 'r.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'r.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'r.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'app.idjabatanapproval1', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'app.idjabatanapproval2', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'app.idjabatanapproval3', '=', 'j3.idjabatan')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2', 
                'app.idjabatanapproval3',
                'u1.name as approval1_user_name',
                'u2.name as approval2_user_name',
                'u3.name as approval3_user_name',
                'j1.namajabatan as jabatan1_name',
                'j2.namajabatan as jabatan2_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Get RKH progress status from LKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function getProgressStatusFromLkh($companycode, $rkhno)
    {
        $lkhData = DB::table('lkhhdr')
            ->where('rkhno', $rkhno)
            ->where('companycode', $companycode)
            ->get();
        
        if ($lkhData->isEmpty()) {
            return [
                'status' => 'no_lkh',
                'progress' => 'No LKH Created',
                'can_complete' => false,
                'color' => 'gray'
            ];
        }
        
        $totalLkh = $lkhData->count();
        $completedLkh = $lkhData->where('status', 'APPROVED')->count();
        
        if ($completedLkh === $totalLkh) {
            return [
                'status' => 'complete',
                'progress' => 'All Complete',
                'can_complete' => true,
                'color' => 'green'
            ];
        } else {
            return [
                'status' => 'in_progress',
                'progress' => "LKH In Progress ({$completedLkh}/{$totalLkh})",
                'can_complete' => false,
                'color' => 'yellow'
            ];
        }
    }

    /**
     * Update RKH status (Completed/In Progress)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param string $status
     * @param string $userid
     * @param Carbon $now
     * @return int
     */
    public function updateStatus($companycode, $rkhno, $status, $userid, $now)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update([
                'status' => $status,
                'updateby' => $userid,
                'updatedat' => $now
            ]);
    }

    /**
     * Cancel RKH (set status to Batal)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param string $userid
     * @param string $alasan
     * @return int
     */
    public function cancelRkh($companycode, $rkhno, $userid, $alasan)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update([
                'status' => 'Batal',
                'batalat' => now(),
                'batalby' => $userid,
                'batalalasan' => $alasan,
                'updateby' => $userid,
                'updatedat' => now()
            ]);
    }

    /**
     * Check if RKH can be cancelled
     * Rules: 
     * - approvalstatus = '1' (fully approved)
     * - status not Completed/Batal
     * - All LKH must be EMPTY (no work done yet)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function canCancelRkh($companycode, $rkhno)
    {
        $rkh = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->first(['status', 'approvalstatus']);
        
        if (!$rkh) {
            return ['can_cancel' => false, 'reason' => 'RKH tidak ditemukan'];
        }
        
        // Already cancelled
        if ($rkh->status === 'Batal') {
            return ['can_cancel' => false, 'reason' => 'RKH sudah dibatalkan'];
        }
        
        // Already completed
        if ($rkh->status === 'Completed') {
            return ['can_cancel' => false, 'reason' => 'RKH sudah Completed, tidak bisa dibatalkan'];
        }
        
        // Must be fully approved (approvalstatus = '1')
        if ($rkh->approvalstatus !== '1') {
            return ['can_cancel' => false, 'reason' => 'RKH belum fully approved. Gunakan tombol Hapus jika ingin menghapus.'];
        }
        
        // Check if all LKH are still EMPTY
        $nonEmptyLkhCount = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('status', '!=', 'EMPTY')
            ->count();
        
        if ($nonEmptyLkhCount > 0) {
            return ['can_cancel' => false, 'reason' => 'Tidak dapat membatalkan RKH. Ada LKH yang sudah dikerjakan (status bukan EMPTY).'];
        }
        
        return ['can_cancel' => true, 'reason' => null];
    }

    /**
     * Get batal detail
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function getBatalDetail($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as u', 'r.batalby', '=', 'u.userid')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->where('r.status', 'Batal')
            ->first([
                'r.rkhno',
                'r.rkhdate',
                'r.batalat',
                'r.batalby',
                'r.batalalasan',
                'u.name as batal_by_nama'
            ]);
    }
}