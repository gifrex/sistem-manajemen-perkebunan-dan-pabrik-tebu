<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class LkhHdr extends Model
{
    protected $table = 'lkhhdr';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $keyType = 'int';

    protected $fillable = [
        'lkhno',
        'rkhno',
        'rkhhdrid',
        'companycode',
        'activitycode',
        'mandorid',
        'lkhdate',
        'jenistenagakerja',
        'totalworkers',
        'totalluasactual',
        'totalhasil',
        'totalsisa',
        'totalupahall',
        'status',
        'keterangan',
        'jumlahapproval',
        'approval1idjabatan',
        'approval1userid',
        'approval1flag',
        'approval1date',
        'approval2idjabatan',
        'approval2userid',
        'approval2flag',
        'approval2date',
        'approval3idjabatan',
        'approval3userid',
        'approval3flag',
        'approval3date',
        'approvalstatus',
        'issubmit',
        'submitby',
        'submitat',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
        'mobileupdatedat',
        'mobile_status',
    ];

    protected $casts = [
        'lkhdate' => 'date',
        'jenistenagakerja' => 'integer',
        'totalworkers' => 'integer',
        'totalluasactual' => 'decimal:2',
        'totalhasil' => 'decimal:2',
        'totalsisa' => 'decimal:2',
        'totalupahall' => 'decimal:2',
        'jumlahapproval' => 'integer',
        'approval1idjabatan' => 'integer',
        'approval1date' => 'datetime',
        'approval2idjabatan' => 'integer',
        'approval2date' => 'datetime',
        'approval3idjabatan' => 'integer',
        'approval3date' => 'datetime',
        'issubmit' => 'boolean',
        'submitat' => 'datetime',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'mobileupdatedat' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function rkhHeader()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhhdrid', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }

    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    public function plotDetails()
    {
        return $this->hasMany(LkhDetailPlot::class, 'lkhhdrid', 'id');
    }

    public function workerDetails()
    {
        return $this->hasMany(LkhDetailWorker::class, 'lkhhdrid', 'id');
    }

    public function materialDetails()
    {
        return $this->hasMany(LkhDetailMaterial::class, 'lkhhdrid', 'id');
    }

    public function vehicleDetails()
    {
        return $this->hasMany(LkhDetailKendaraan::class, 'lkhhdrid', 'id');
    }

    public function bsmDetails()
    {
        return $this->hasMany(LkhDetailBsm::class, 'lkhhdrid', 'id');
    }
}