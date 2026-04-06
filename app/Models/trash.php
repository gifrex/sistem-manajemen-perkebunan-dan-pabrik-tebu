<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trash extends Model
{ 
    protected $table = 'trash';
 
    // Composite primary key
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'suratjalanno',
        'companycode',
        'jenis',
        'pucuk',
        'daungulma',
        'sogolan',
        'siwilan',
        'tebumati',
        'tanahetc',
        'total',
        'toleransi',
        'nettotrash',
        'createdby',
        'createddate'
        
    ];

    /**
     * Relasi ke User
     */
}
