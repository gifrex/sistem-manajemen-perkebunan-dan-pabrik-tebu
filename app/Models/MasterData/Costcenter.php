<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $table = 'costcenter';

    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'herbisidagroupid',
        'costcenter',
        'description',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    /**
     * karena composite key, kita disable increment
     */
    public $incrementing = false;
    protected $keyType = 'string';
}