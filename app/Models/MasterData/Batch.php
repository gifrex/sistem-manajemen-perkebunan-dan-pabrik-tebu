<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'batch';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'batchno',
        'companycode',
        'plot',
        'batcharea',
        'batchdate',
        'tanggalulangtahun',
        'lifecyclestatus',
        'previousbatchno',
        'plantinglkhno',
        'tanggalpanen',
        'kontraktorid',
        'kodevarietas',
        'pkp',
        'lastactivity',
        'isactive',
        'closedat',
        'inputby',
        'createdat',
        'plottype',
        'splitfrombatchno',
        'mergedtobatchno',
        'splitmergedreason',
    ];

    protected $casts = [
        'batcharea' => 'decimal:2',
        'batchdate' => 'date',
        'tanggalulangtahun' => 'date',
        'tanggalpanen' => 'date',
        'pkp' => 'integer',
        'isactive' => 'boolean',
        'closedat' => 'datetime',
        'createdat' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    public function previousBatch()
    {
        return $this->belongsTo(Batch::class, 'previousbatchno', 'batchno');
    }

    public function nextBatch()
    {
        return $this->hasOne(Batch::class, 'previousbatchno', 'batchno');
    }

    public function plantingLkh()
    {
        return $this->belongsTo(Lkhhdr::class, 'plantinglkhno', 'lkhno');
    }

    public function kontraktor()
    {
        return $this->belongsTo(Kontraktor::class, 'kontraktorid', 'id');
    }

    public function masterlist()
    {
        return $this->belongsTo(MasterList::class, 'plot', 'plot')
                    ->where('companycode', $this->companycode);
    }

    public function lkhDetailPlots()
    {
        return $this->hasMany(LkhDetailPlot::class, 'batchid', 'id');
    }

    public function lkhDetailBsm()
    {
        return $this->hasMany(LkhDetailBsm::class, 'batchid', 'id');
    }

    public function rkhlsts()
    {
        return $this->hasMany(Rkhlst::class, 'batchid', 'id');
    }
}