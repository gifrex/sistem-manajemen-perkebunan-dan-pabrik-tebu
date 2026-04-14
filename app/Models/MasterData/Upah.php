<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Upah extends Model
{
    protected $table = 'upah';
    protected $primaryKey = 'id';
    public $timestamps = false; // Karena menggunakan createdat/updatedat custom

    protected $fillable = [
        'companycode',
        'activitygroup', 
        'wagetype',
        'amount',
        'effectivedate',
        'enddate',
        'parameter',
        'inputby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effectivedate' => 'date',
        'enddate' => 'date',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];


    // Scopes
    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    public function scopeByActivityGroup($query, $activitygroup)
    {
        return $query->where('activitygroup', $activitygroup);
    }

    public function scopeByWageType($query, $wagetype)
    {
        return $query->where('wagetype', $wagetype);
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effectivedate', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('enddate')
                          ->orWhere('enddate', '>=', $date);
                    });
    }
}