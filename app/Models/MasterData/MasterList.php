<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class MasterList extends Model
{
    protected $table = 'masterlist';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'plot',
        'blok',
        'activebatchno',
        'isactive',
    ];

    protected $casts = [
        'isactive' => 'boolean',
    ];

    // Relationship to active batch
    public function activeBatch()
    {
        return $this->belongsTo(Batch::class, 'activebatchno', 'batchno');
    }

    // Relationship to company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    public function scopeByBlok($query, $blok)
    {
        return $query->where('blok', $blok);
    }

    // Helper methods
    public function getCurrentLifecycleAttribute(): ?string
    {
        return $this->activeBatch?->lifecyclestatus;
    }

    public function hasActiveBatch(): bool
    {
        return !is_null($this->activebatchno);
    }
}