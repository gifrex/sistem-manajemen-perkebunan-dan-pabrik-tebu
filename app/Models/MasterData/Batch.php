<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    public $timestamps = false;

    protected $table = 'batch';

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

    /**
     * IMPORTANT: Cast date columns as 'date:Y-m-d' (immutable string format)
     * instead of just 'date'. This prevents timezone shifting when
     * serialized to JSON via @js() or toArray().
     *
     * Without this, Carbon parses '2026-01-01' as '2026-01-01 00:00:00 Asia/Jakarta',
     * then toJSON() converts to UTC → '2025-12-31T17:00:00.000000Z' → geser 1 hari.
     */
    protected $casts = [
        'batchdate'          => 'date:Y-m-d',
        'tanggalulangtahun'  => 'date:Y-m-d',
        'tanggalpanen'       => 'date:Y-m-d',
        'closedat'           => 'datetime',
        'createdat'          => 'datetime',
        'batcharea'          => 'decimal:2',
        'isactive'           => 'boolean',
        'pkp'                => 'integer',
    ];

    /**
     * Override serializeDate to prevent timezone conversion for dates.
     * This ensures that when @js($data) is called in Blade,
     * date fields stay as 'Y-m-d' strings, not UTC ISO-8601.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    // --- Accessors for badge colors ---

    public function getPlottypeBadgeColorAttribute(): string
    {
        return match ($this->plottype) {
            'KBD' => 'bg-purple-100 text-purple-800',
            'KTG' => 'bg-orange-100 text-orange-800',
            'KBI' => 'bg-teal-100 text-teal-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getLifecycleBadgeColorAttribute(): string
    {
        return match ($this->lifecyclestatus) {
            'PC'  => 'bg-blue-100 text-blue-800',
            'RC1' => 'bg-yellow-100 text-yellow-800',
            'RC2' => 'bg-green-100 text-green-800',
            'RC3' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}