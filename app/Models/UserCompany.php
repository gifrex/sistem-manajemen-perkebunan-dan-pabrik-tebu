<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Company;

class UserCompany extends Model
{
    protected $table = 'usercompany';
    protected $primaryKey = null; // Composite key
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'userid',
        'companycode',
        'isactive',
        'grantedby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }
}