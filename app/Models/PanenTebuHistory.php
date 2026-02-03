<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PanenTebuHistory extends Model
{
    protected $table = 'panentebuhistory';
    
    // Disable auto-increment since we're using custom nodoc
    public $incrementing = false;
    
    // Set primary key to nodoc
    protected $primaryKey = 'nodoc';
    
    // Set key type to string
    protected $keyType = 'string';
    
    // Define custom timestamp columns
    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';
    
    protected $fillable = [
        'nodoc',
        'companycode',
        'userid',
        'idkontraktor',
        'namakontraktor',
        'kodeharga',
        'startdate',
        'enddate',
        'grandtotal',
        'dataresult',
        'hargasnapshot',
        'hargaperplot'
    ];
    
    protected $casts = [
        'dataresult' => 'array',
        'hargasnapshot' => 'array',
        'hargaperplot' => 'array',
        'startdate' => 'date',
        'enddate' => 'date',
        'grandtotal' => 'decimal:2'
    ];
    
    /**
     * Generate nodoc with format: DOC-{companycode}-{ddmmyy}{sequence}
     * Example: DOC-TBL1-030226001
     */
    public static function generateNodoc($companycode)
    {
        // Get current date in ddmmyy format
        $datePrefix = date('dmy'); // 030226
        
        // Build the nodoc prefix
        $prefix = "DOC-{$companycode}-{$datePrefix}";
        
        // Get the last nodoc for today with this company
        $lastRecord = self::where('companycode', $companycode)
                          ->where('nodoc', 'LIKE', "{$prefix}%")
                          ->orderBy('nodoc', 'desc')
                          ->first();
        
        if ($lastRecord) {
            // Extract the sequence number (last 3 digits)
            $lastSequence = intval(substr($lastRecord->nodoc, -3));
            $newSequence = $lastSequence + 1;
        } else {
            // First record for today
            $newSequence = 1;
        }
        
        // Format sequence to 3 digits with leading zeros
        $sequenceFormatted = str_pad($newSequence, 3, '0', STR_PAD_LEFT);
        
        // Return complete nodoc
        return $prefix . $sequenceFormatted;
    }
    
    /**
     * Boot method to auto-generate nodoc before creating
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->nodoc)) {
                $model->nodoc = self::generateNodoc($model->companycode);
            }
        });
    }
    
    // Accessor untuk format tanggal range
    public function getRangeTanggalAttribute()
    {
        return $this->startdate->format('d M Y') . ' s/d ' . $this->enddate->format('d M Y');
    }
    
    // Accessor untuk format grand total
    public function getFormattedGrandTotalAttribute()
    {
        return number_format($this->grandtotal, 0, ',', '.');
    }
    
    // Accessor untuk tanggal dibuat
    public function getFormattedCreatedDateAttribute()
    {
        return $this->createdat->format('d/m/Y H:i');
    }
    
    // Scope untuk filter by company
    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }
    
    // Scope untuk filter by user
    public function scopeByUser($query, $userid)
    {
        return $query->where('userid', $userid);
    }
}