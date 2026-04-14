<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class RkhLst extends Model
{
    protected $table = 'rkhlst';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhhdrid',
        'rkhdate',
        'blok',
        'plot',
        'activitycode',
        'luasarea',
        'jenistenagakerja',
        'usingmaterial',
        'herbisidagroupid',
        'batchno',
        'batchid',
    ];

    protected $casts = [
        'rkhdate'             => 'date',
        'luasarea'            => 'float',
        'jenistenagakerja'    => 'integer',
        'usingmaterial'       => 'boolean',
        'herbisidagroupid'    => 'integer',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

    public function rkhHeader()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhhdrid', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchid', 'id');
    }

    public function herbisidaGroup()
    {
        return $this->belongsTo(HerbisidaGroup::class, 'herbisidagroupid', 'herbisidagroupid');
    }
}