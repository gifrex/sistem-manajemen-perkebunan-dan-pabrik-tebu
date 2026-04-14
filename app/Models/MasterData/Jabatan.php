<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use App\Models\JabatanPermission;

class Jabatan extends Model
{
    protected $table = 'jabatan';
    protected $primaryKey = 'idjabatan';
    public $timestamps = false;

    protected $fillable = [
        'namajabatan',
        'inputby',
        'updateby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'idjabatan' => 'integer',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'idjabatan', 'idjabatan');
    }

    public function jabatanPermissions()
    {
        return $this->hasMany(JabatanPermission::class, 'idjabatan', 'idjabatan');
    }
}