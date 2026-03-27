<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RekapPremiHistory extends Model
{
    protected $table      = 'rekappremikontraktorhistory';
    protected $primaryKey = 'nodoc';
    public    $keyType    = 'string';
    public    $incrementing = false;
    public    $timestamps = false;

    protected $fillable = [
        'nodoc',
        'companycode',
        'userid',
        'idkontraktor',
        'namakontraktor',
        'bulan',
        'tahun',
        'target_per_hari',
        'grandtotal',
        'dataresult',
        'hargasnapshot',
        'alasanlist',
        'nodoc_references',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'dataresult'       => 'array',
        'hargasnapshot'    => 'array',
        'alasanlist'       => 'array',
        'nodoc_references' => 'array',
        'target_per_hari'  => 'decimal:2',
        'grandtotal'       => 'decimal:2',
        'createdat'        => 'datetime',
        'updatedat'        => 'datetime',
    ];

    // -------------------------------------------------------
    // Auto-generate nodoc before creating
    // Format: RPK-{companycode}-{idkontraktor}-{MM}-{seq 3 digit}
    // Contoh: RPK-TBL1-K001-11-001
    // -------------------------------------------------------
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nodoc)) {
                $model->nodoc = static::generateNodoc(
                    $model->companycode,
                    $model->idkontraktor,
                    $model->bulan
                );
            }
            if (empty($model->createdat)) {
                $model->createdat = Carbon::now();
            }
            $model->updatedat = Carbon::now();
        });

        static::updating(function ($model) {
            $model->updatedat = Carbon::now();
        });
    }

    /**
     * Generate nodoc dengan format:
     * RPK-{companycode}-{idkontraktor}-{MM}-{seq 3 digit}
     * Contoh: RPK-TBL1-K001-11-001
     *
     * Sequence di-reset per kombinasi companycode + idkontraktor + bulan
     */
    protected static function generateNodoc(string $companycode, string $idkontraktor, string $bulan): string
    {
        // Bulan diformat 2 digit: "01", "11", dst
        $bulanFormatted = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        $prefix = "RPK-{$companycode}-{$idkontraktor}-{$bulanFormatted}-";

        $last = DB::table('rekappremikontraktorhistory')
            ->where('nodoc', 'like', $prefix . '%')
            ->orderBy('nodoc', 'desc')
            ->value('nodoc');

        // Ambil 3 digit terakhir sebagai sequence
        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}