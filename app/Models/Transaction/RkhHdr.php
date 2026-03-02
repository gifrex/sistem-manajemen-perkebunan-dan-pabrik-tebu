<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class RkhHdr extends Model
{
    protected $table = 'rkhhdr';
    
    // UPDATED: Use surrogate ID after migration
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $keyType = 'int';

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhdate',
        'manpower',
        'totalluas',
        'mandorid',
        'activitygroup',
        'keterangan',
        
        // Approval fields
        'jumlahapproval',
        'approval1idjabatan',
        'approval1userid',
        'approval1date',
        'approval1flag',
        'approval2idjabatan',
        'approval2userid',
        'approval2date',
        'approval2flag',
        'approval3idjabatan',
        'approval3userid',
        'approval3date',
        'approval3flag',
        'approvalstatus',
        
        'status',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'rkhdate'            => 'date',
        'manpower'           => 'integer',
        'totalluas'          => 'float',
        'jumlahapproval'     => 'integer',
        'approval1idjabatan' => 'integer',
        'approval1date'      => 'datetime',
        'approval2idjabatan' => 'integer',
        'approval2date'      => 'datetime',
        'approval3idjabatan' => 'integer',
        'approval3date'      => 'datetime',
        'createdat'          => 'datetime',
        'updatedat'          => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function activityGroup()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }

    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    public function details()
    {
        return $this->hasMany(Rkhlst::class, 'rkhhdrid', 'id');
    }

    public function workers()
    {
        return $this->hasMany(RkhLstWorker::class, 'rkhhdrid', 'id');
    }

    public function vehicles()
    {
        return $this->hasMany(RkhLstKendaraan::class, 'rkhhdrid', 'id');
    }

    public function lkhRecords()
    {
        return $this->hasMany(Lkhhdr::class, 'rkhhdrid', 'id');
    }


    public function getDataBsmSJ($companycode, $tanggalawal, $tanggalakhir)
    {
        $query = "
            SELECT 
                r.companycode,
                r.rkhno,
                r.rkhdate,
                r.mandorid,
                r.activitygroup,
                r.totalluas,
                r.manpower,
                
                -- Count jumlah SURAT JALAN yang terkait dengan RKH ini
                COUNT(DISTINCT s.suratjalanno) AS jumlah_sj,
                
                -- Count total BSM records yang terkait dengan RKH ini
                COUNT(DISTINCT b.id) AS total_bsm,
                
                -- Additional info untuk keperluan display
                GROUP_CONCAT(DISTINCT l.lkhno ORDER BY l.lkhdate SEPARATOR ', ') AS list_lkhno,
                MIN(l.lkhdate) AS lkh_date_start,
                MAX(l.lkhdate) AS lkh_date_end,
                
                -- BSM averages (only for filled BSM)
                AVG(CASE WHEN b.nilaibersih > 0 THEN b.nilaibersih ELSE NULL END) AS avg_bersih,
                AVG(CASE WHEN b.nilaisegar > 0 THEN b.nilaisegar ELSE NULL END) AS avg_segar,
                AVG(CASE WHEN b.nilaimanis > 0 THEN b.nilaimanis ELSE NULL END) AS avg_manis,
                AVG(CASE WHEN b.averagescore > 0 THEN b.averagescore ELSE NULL END) AS avg_bsm_score,
                
                -- Grade counts (only for filled BSM)
                SUM(CASE WHEN b.grade = 'A' THEN 1 ELSE 0 END) AS bsm_grade_a,
                SUM(CASE WHEN b.grade = 'B' THEN 1 ELSE 0 END) AS bsm_grade_b,
                SUM(CASE WHEN b.grade = 'C' THEN 1 ELSE 0 END) AS bsm_grade_c,
                
                -- Metadata
                r.inputby AS created_by,
                r.createdat AS created_at

            FROM rkhhdr r
            LEFT JOIN lkhhdr l ON r.companycode = l.companycode AND r.rkhno = l.rkhno
            LEFT JOIN suratjalanpos s ON r.companycode = s.companycode 
                AND LEFT(s.tanggalangkut, 10) = l.lkhdate
            LEFT JOIN lkhdetailbsm b ON l.companycode = b.companycode 
                AND l.lkhno = b.lkhno 
                AND s.suratjalanno = b.suratjalanno

            WHERE 
                r.companycode = ?
                AND r.rkhdate BETWEEN ? AND ?
                AND l.activitycode = '4.7'

            GROUP BY 
                r.companycode,
                r.rkhno,
                r.rkhdate,
                r.mandorid,
                r.activitygroup,
                r.totalluas,
                r.manpower,
                r.inputby,
                r.createdat

            HAVING 
                -- Filter hanya RKH yang memiliki Surat Jalan atau BSM data
                (COUNT(DISTINCT s.suratjalanno) > 0 OR COUNT(DISTINCT b.id) > 0)

            ORDER BY 
                r.rkhdate DESC, 
                r.rkhno ASC
        ";

        try {
            $results = DB::select($query, [$companycode, $tanggalawal, $tanggalakhir]);
            
            // Convert to collection and transform data
            return collect($results)->map(function ($item) {
                return (object) [
                    // Main display data - UPDATED FIELDS
                    'rkhno' => $item->rkhno,
                    'rkhdate' => $item->rkhdate,
                    'tanggal' => $item->rkhdate, // alias
                    'jumlah_sj' => (int) $item->jumlah_sj, // CHANGED from jumlah_lkh
                    'total_bsm' => (int) $item->total_bsm, // Total BSM
                    
                    // Legacy compatibility - keep old field name for view compatibility
                    'jumlah_lkh' => (int) $item->jumlah_sj, // For backward compatibility (shows SJ count)
                    'jumlah_bsm' => (int) $item->total_bsm, // For backward compatibility
                    
                    // RKH details
                    'companycode' => $item->companycode,
                    'mandorid' => $item->mandorid,
                    'activitygroup' => $item->activitygroup,
                    'totalluas' => (float) $item->totalluas,
                    'manpower' => (int) $item->manpower,
                    
                    // LKH details
                    'list_lkhno' => $item->list_lkhno,
                    'lkh_date_start' => $item->lkh_date_start,
                    'lkh_date_end' => $item->lkh_date_end,
                    
                    // BSM details (only from filled BSM)
                    'avg_bersih' => $item->avg_bersih ? round((float) $item->avg_bersih, 2) : 0,
                    'avg_segar' => $item->avg_segar ? round((float) $item->avg_segar, 2) : 0,
                    'avg_manis' => $item->avg_manis ? round((float) $item->avg_manis, 2) : 0,
                    'avg_bsm_score' => $item->avg_bsm_score ? round((float) $item->avg_bsm_score, 2) : 0,
                    'bsm_grade_a' => (int) $item->bsm_grade_a,
                    'bsm_grade_b' => (int) $item->bsm_grade_b,
                    'bsm_grade_c' => (int) $item->bsm_grade_c,
                    
                    // Metadata
                    'created_by' => $item->created_by,
                    'created_at' => $item->created_at,
                    'keterangan' => "RKH dengan {$item->jumlah_sj} Surat Jalan dan {$item->total_bsm} BSM total"
                ];
            });

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error in getDataBsmSJ (RKH Summary): ' . $e->getMessage());
            
            // Return empty collection on error
            return collect([]);
        }
    }

    public function getBsmDetailByRkh($companycode, $rkhno)
    {
        // First, let's use the exact query that works in your database
        $query = "
            SELECT 
                a.companycode, 
                a.suratjalanno, 
                c.createdat,
                a.plot, 
                c.id, 
                c.grade,
                c.nilaibersih, 
                c.nilaisegar, 
                c.nilaimanis, 
                c.averagescore,
                b.lkhno,
                b.lkhdate,
                COALESCE(c.keterangan, '') as keterangan
            FROM suratjalanpos a
            LEFT JOIN lkhhdr b ON a.companycode = b.companycode AND b.rkhno = ?
            LEFT JOIN lkhdetailbsm c ON b.companycode = c.companycode 
                AND b.lkhno = c.lkhno 
                AND a.suratjalanno = c.suratjalanno
            WHERE LEFT(a.tanggalangkut, 10) = b.lkhdate 
                AND a.companycode = ?
            ORDER BY a.plot
        ";
        
        try {
            // Debug: Log the query and parameters
            \Log::info('BSM Detail Query', [
                'query' => $query,
                'rkhno' => $rkhno,
                'companycode' => $companycode
            ]);
            
            $results = DB::select($query, [$rkhno, $companycode]);
            
            // Debug: Log the raw results
            \Log::info('BSM Detail Raw Results', ['count' => count($results), 'results' => $results]);
            
            // Convert to collection and transform data
            return collect($results)->map(function ($item) {
                return (object) [
                    'suratjalanno' => $item->suratjalanno ?? '',
                    'nilaibersih' => (float) ($item->nilaibersih ?? 0),
                    'nilaisegar' => (float) ($item->nilaisegar ?? 0),
                    'nilaimanis' => (float) ($item->nilaimanis ?? 0),
                    'averagescore' => (float) ($item->averagescore ?? 0),
                    'grade' => $item->grade ?? '',
                    'lkhno' => $item->lkhno ?? '',
                    'lkhdate' => $item->lkhdate ?? '',
                    'plot' => $item->plot ?? '',
                    'keterangan' => $item->keterangan ?? '',
                    'createdat' => $item->createdat ?? '',
                    'id' => $item->id ?? '',
                    'companycode' => $item->companycode ?? ''
                ];
            });
        } catch (\Exception $e) {
            // Log detailed error for debugging
            \Log::error('Error in getBsmDetailByRkh', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'rkhno' => $rkhno,
                'companycode' => $companycode
            ]);
            
            // Return empty collection on error
            return collect([]);
        }
    }

    public function getBsmDataForCopy($companycode, $rkhno, $plot)
    {
        try {
            // Get BSM data from same plot that has complete values
            $query = "
                SELECT DISTINCT
                    c.id,
                    c.suratjalanno,
                    c.nilaibersih,
                    c.nilaisegar,
                    c.nilaimanis,
                    c.averagescore,
                    c.grade,
                    CONCAT('SJ: ', c.suratjalanno, ' | Grade ', c.grade, ' | Score: ', FORMAT(c.averagescore, 2)) as display_text
                FROM suratjalanpos a
                LEFT JOIN lkhhdr b ON a.companycode = b.companycode 
                    AND b.rkhno = ?
                LEFT JOIN lkhdetailbsm c ON b.companycode = c.companycode 
                    AND b.lkhno = c.lkhno 
                    AND a.suratjalanno = c.suratjalanno
                WHERE LEFT(a.tanggalangkut, 10) = b.lkhdate 
                    AND a.companycode = ?
                    AND a.plot = ?
                    AND c.suratjalanno IS NOT NULL
                    AND c.nilaibersih IS NOT NULL
                    AND c.nilaisegar IS NOT NULL  
                    AND c.nilaimanis IS NOT NULL
                    AND c.averagescore IS NOT NULL
                    AND c.grade IS NOT NULL
                    AND c.grade != ''
                    AND c.nilaibersih > 0
                    AND c.nilaisegar > 0
                    AND c.nilaimanis > 0
                ORDER BY 
                    CASE c.grade 
                        WHEN 'A' THEN 1 
                        WHEN 'B' THEN 2 
                        WHEN 'C' THEN 3 
                        ELSE 4 
                    END ASC,
                    c.averagescore ASC,
                    c.createdat DESC
            ";
            
            $results = DB::select($query, [$rkhno, $companycode, $plot]);
            
            \Log::info('BSM Copy Source Data', [
                'rkhno' => $rkhno, 
                'plot' => $plot,
                'found' => count($results)
            ]);
            
            return collect($results);
            
        } catch (\Exception $e) {
            \Log::error('Error getting BSM copy sources', [
                'message' => $e->getMessage(),
                'rkhno' => $rkhno,
                'plot' => $plot
            ]);
            return collect([]);
        }
    }

    /**
     * Copy BSM values from source to target (empty BSM) - WITH UPDATEBY
     * Updates: nilaibersih, nilaisegar, nilaimanis, averagescore, grade, parentbsm
     */
    public function copyBsmToEmpty($companycode, $targetSuratjalanno, $sourceBsmId, $rkhno, $updateby)
    {
        try {
            DB::beginTransaction();
            
            // 1. Get source BSM data (yang sudah ada nilai)
            $sourceData = DB::selectOne("
                SELECT id, suratjalanno, nilaibersih, nilaisegar, nilaimanis, averagescore, grade
                FROM lkhdetailbsm 
                WHERE id = ? AND companycode = ?
            ", [$sourceBsmId, $companycode]);
            
            if (!$sourceData) {
                throw new \Exception('Data BSM sumber tidak ditemukan');
            }
            
            // 2. Get target record info using proper JOIN to get lkhno
            $targetRecord = DB::selectOne("
                SELECT 
                    c.id as bsm_id,
                    b.lkhno,
                    a.plot,
                    a.suratjalanno,
                    b.rkhno
                FROM suratjalanpos a
                LEFT JOIN lkhhdr b ON a.companycode = b.companycode 
                    AND b.rkhno = ?
                    AND LEFT(a.tanggalangkut, 10) = b.lkhdate
                LEFT JOIN lkhdetailbsm c ON b.companycode = c.companycode 
                    AND b.lkhno = c.lkhno 
                    AND a.suratjalanno = c.suratjalanno
                WHERE a.companycode = ? 
                    AND a.suratjalanno = ?
            ", [$rkhno, $companycode, $targetSuratjalanno]);
            
            if (!$targetRecord || !$targetRecord->lkhno) {
                throw new \Exception('Data target tidak ditemukan atau lkhno tidak valid. RKH: ' . $rkhno . ', SJ: ' . $targetSuratjalanno);
            }
            
            \Log::info('Target Record Found', [
                'bsm_id' => $targetRecord->bsm_id,
                'lkhno' => $targetRecord->lkhno, 
                'plot' => $targetRecord->plot,
                'suratjalanno' => $targetRecord->suratjalanno,
                'rkhno' => $targetRecord->rkhno
            ]);
            
            // 3. Verify target BSM is empty (kosong)
            if ($targetRecord->bsm_id) {
                $existingValues = DB::selectOne("
                    SELECT nilaibersih, nilaisegar, nilaimanis 
                    FROM lkhdetailbsm 
                    WHERE id = ?
                ", [$targetRecord->bsm_id]);
                
                // Check if already has values (not empty)
                if ($existingValues && 
                    ($existingValues->nilaibersih > 0 || 
                    $existingValues->nilaisegar > 0 || 
                    $existingValues->nilaimanis > 0)) {
                    throw new \Exception('Target BSM sudah memiliki nilai, hanya BSM kosong yang bisa dicopy');
                }
            }
            
            // 4. Copy values from source to target - WITH UPDATEBY
            $copyData = [
                'nilaibersih' => $sourceData->nilaibersih,
                'nilaisegar' => $sourceData->nilaisegar, 
                'nilaimanis' => $sourceData->nilaimanis,
                'averagescore' => $sourceData->averagescore,
                'grade' => $sourceData->grade,
                'parentbsm' => $sourceBsmId, // Track source BSM ID
                'updatedat' => now(),
                'updateby' => $updateby // ADD UPDATEBY
            ];
            
            if ($targetRecord->bsm_id) {
                // Update existing empty record
                DB::table('lkhdetailbsm')
                    ->where('id', $targetRecord->bsm_id)
                    ->update($copyData);
                    
                $resultId = $targetRecord->bsm_id;
            } else {
                // Create new record if doesn't exist - ENSURE LKHNO IS NOT NULL
                $insertData = array_merge($copyData, [
                    'companycode' => $companycode,
                    'lkhno' => $targetRecord->lkhno, // This should not be null now
                    'suratjalanno' => $targetSuratjalanno,
                    'plot' => $targetRecord->plot,
                    'createdat' => now(),
                    'inputby' => $updateby // ADD INPUTBY FOR NEW RECORDS
                ]);
                
                \Log::info('Inserting BSM data', $insertData);
                
                $resultId = DB::table('lkhdetailbsm')->insertGetId($insertData);
            }
            
            DB::commit();
            
            \Log::info('BSM Copy Success', [
                'source_id' => $sourceBsmId,
                'target_sj' => $targetSuratjalanno,
                'target_id' => $resultId,
                'values_copied' => [
                    'bersih' => $sourceData->nilaibersih,
                    'segar' => $sourceData->nilaisegar,
                    'manis' => $sourceData->nilaimanis
                ],
                'updateby' => $updateby // ADD TO LOG
            ]);
            
            return [
                'success' => true,
                'message' => 'Data BSM berhasil dicopy dari ' . $sourceData->suratjalanno,
                'data' => [
                    'id' => $resultId,
                    'nilaibersih' => $sourceData->nilaibersih,
                    'nilaisegar' => $sourceData->nilaisegar,
                    'nilaimanis' => $sourceData->nilaimanis,
                    'averagescore' => $sourceData->averagescore,
                    'grade' => $sourceData->grade,
                    'parentbsm' => $sourceBsmId,
                    'updateby' => $updateby // ADD TO RESPONSE
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error copying BSM to empty', [
                'message' => $e->getMessage(),
                'source_bsm_id' => $sourceBsmId,
                'target_suratjalanno' => $targetSuratjalanno,
                'updateby' => $updateby
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}