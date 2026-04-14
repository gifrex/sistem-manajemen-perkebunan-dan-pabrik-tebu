<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'notification_id';
    public $timestamps = false;

    protected $fillable = [
        'notification_type',
        'reference_type',
        'reference_id',
        'companycode',
        'target_jabatan',
        'title',
        'body',
        'action_url',
        'icon',
        'priority',
        'status',
        'readby',
        'inputby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'readby' => 'array', // ✅ Ini ada, tapi kadang gagal decode
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    protected $attributes = [
        'notification_type' => 'manual',
        'status' => 'active',
        'priority' => 'medium',
        'icon' => 'bell'
    ];

    // ============================================================================
    // RELATIONSHIPS
    // ============================================================================

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class, 'reference_id', 'ticket_id');
    }

    // ============================================================================
    // ACCESSORS & HELPERS
    // ============================================================================

    /**
     * Check if notification is read by specific user
     */
    public function isReadBy(string|int $userId): bool
    {
        if (!$this->readby) {
            return false;
        }

        // ✅ FIX: Ensure readby is array
        $readBy = is_array($this->readby) ? $this->readby : [];

        return in_array($userId, $readBy);
    }

    /**
     * Get is_read attribute
     */
    public function getIsReadAttribute(): bool
    {
        $userId = auth()->id();

        if (!$userId) {
            return false;
        }

        return $this->isReadBy($userId);
    }

    // ============================================================================
    // STATIC METHODS UNTUK KOLEGA (HPT & AGRONOMI) - JANGAN DIHAPUS
    // ============================================================================

    /**
     * Create notification for Agronomi alerts
     */
    public static function createForAgronomi(array $data)
    {
        $condition = $data['condition'];
        $avgGerminasi = $condition['germinasi'];
        $avgGulma = $condition['gulma'];
        $umurTanam = $condition['umur'];
        $plot = $data['plot'];

        $priority = 'medium';
        $alerts = [];

        if ($avgGerminasi < 0.9 && $umurTanam == 1.0) {
            $alerts[] = "Germinasi rendah (" . round($avgGerminasi * 100, 1) . "%) pada plot " . $plot . ".";
            $priority = 'high';
        }

        if ($avgGulma > 0.25) {
            $alerts[] = "Persentase penutupan gulma tinggi (" . round($avgGulma * 100, 1) . "%) pada plot " . $plot . ".";
            $priority = 'high';
        }

        return self::create([
            'notification_type' => 'system',
            'reference_type' => 'agronomi',
            'reference_id' => $data['plot'],
            'companycode' => $data['companycode'],
            'target_jabatan' => '7,10',
            'title' => "Agronomi Alert - Plot #{$data['plot']}",
            'body' => "Perhatian: " . implode(', ', $alerts) . ". Mohon segera ditindaklanjuti.",
            'action_url' => route('transaction.agronomi.index'),
            'icon' => 'bell',
            'priority' => $priority,
            'inputby' => auth()->user()->userid ?? 'system',
            'createdat' => now(),
            'updatedat' => now()
        ]);
    }

    /**
     * Create notification for HPT alerts
     */
    public static function createForHPT(array $data)
    {
        $condition = $data['condition'];
        $avgPPT = $condition['ppt'];
        $avgPBT = $condition['pbt'];
        $umurTanam = $condition['umur'];
        $plot = $data['plot'];

        $priority = 'medium';
        $alerts = [];

        $notifications = [
            ['per' => $avgPBT, 'threshold' => 0.03, 'min_age' => 1, 'max_age' => 3, 'title' => 'HPT - Persentase PBT > 3%', 'type' => 'penggerek batang tebu'],
            ['per' => $avgPPT, 'threshold' => 0.03, 'min_age' => 1, 'max_age' => 3, 'title' => 'HPT - Persentase PPT > 3%', 'type' => 'penggerek pucuk tebu'],
            ['per' => $avgPBT, 'threshold' => 0.05, 'min_age' => 4, 'max_age' => null, 'title' => 'HPT - Persentase PBT > 5%', 'type' => 'penggerek batang tebu'],
            ['per' => $avgPPT, 'threshold' => 0.05, 'min_age' => 4, 'max_age' => null, 'title' => 'HPT - Persentase PPT > 5%', 'type' => 'penggerek pucuk tebu']
        ];

        foreach ($notifications as $notif) {
            if (
                $notif['per'] > $notif['threshold'] &&
                $umurTanam >= $notif['min_age'] &&
                ($notif['max_age'] === null || $umurTanam <= $notif['max_age'])
            ) {
                $alerts[] = "Persentase {$notif['type']} lebih dari " . ($notif['threshold'] * 100) . " (" . round($notif['per'] * 100, 1) . "%) pada plot " . $plot . ".";
                $priority = 'high';
            }
        }

        return self::create([
            'notification_type' => 'system',
            'reference_type' => 'hpt',
            'reference_id' => $data['plot'],
            'companycode' => $data['companycode'],
            'target_jabatan' => '7,10',
            'title' => "HPT Alert - Plot #{$data['plot']}",
            'body' => "Perhatian: " . implode(', ', $alerts) . ". Mohon segera ditindaklanjuti.",
            'action_url' => route('transaction.hpt.index'),
            'icon' => 'bell',
            'priority' => $priority,
            'inputby' => auth()->user()->userid ?? 'system',
            'createdat' => now(),
            'updatedat' => now()
        ]);
    }
}