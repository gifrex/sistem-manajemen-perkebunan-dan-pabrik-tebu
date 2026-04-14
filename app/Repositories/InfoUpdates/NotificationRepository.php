<?php

namespace App\Repositories\InfoUpdates;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class NotificationRepository
{
    /**
     * Get notifications for specific user with filters
     */
    public function getForUser(string|int $userId, int $limit = 10, bool $unreadOnly = false): Collection
    {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return collect();
        }

        // Get user companies
        $userCompanies = $user->userCompanies;
        
        if ($userCompanies->isEmpty()) {
            $companyCodes = $user->companycode ? explode(',', $user->companycode) : [];
        } else {
            $companyCodes = $userCompanies->pluck('companycode')->toArray();
        }

        $companyCodes = array_filter($companyCodes);

        if (empty($companyCodes)) {
            return collect();
        }

        $idjabatan = $user->idjabatan;

        // Build query
        $query = Notification::query()
            ->where('status', 'active')
            ->where(function($q) use ($companyCodes) {
                foreach ($companyCodes as $code) {
                    $q->orWhere('companycode', 'like', "%{$code}%");
                }
            });

        // Filter by jabatan
        if ($idjabatan) {
            $query->where(function($q) use ($idjabatan) {
                $q->whereNull('target_jabatan')
                  ->orWhere('target_jabatan', '')
                  ->orWhere('target_jabatan', 'like', "%{$idjabatan}%");
            });
        }

        // Filter unread only
        if ($unreadOnly) {
            $query->where(function($q) use ($userId) {
                $q->whereNull('readby')
                  ->orWhereRaw("NOT JSON_CONTAINS(readby, ?)", [json_encode($userId)]);
            });
        }

        $notifications = $query->orderBy('priority', 'desc')
            ->orderBy('createdat', 'desc')
            ->limit($limit)
            ->get();

        // Attach is_read flag
        return $notifications->map(function ($notif) use ($userId) {
            $readBy = [];
            if (!empty($notif->readby)) {
                $decoded = json_decode($notif->readby, true);
                $readBy = is_array($decoded) ? $decoded : [];
            }
            $notif->is_read = in_array($userId, $readBy);
            return $notif;
        });
    }

    /**
     * Get dropdown data for user (10 recent notifications)
     */
    public function getDropdownData(string|int $userId): array
    {
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return [
                'notifications' => collect(),
                'unread_count' => 0
            ];
        }

        // Get user companies
        $userCompanies = $user->userCompanies;
        if ($userCompanies->isEmpty()) {
            $userCompanyArray = $user->companycode ? explode(',', $user->companycode) : [];
        } else {
            $userCompanyArray = $userCompanies->pluck('companycode')->toArray();
        }
        $userCompanyArray = array_filter($userCompanyArray);
        
        $idjabatan = $user->idjabatan;
        
        // Query notifications
        $notifications = DB::table('notification')
            ->where('status', 'active')
            ->where(function($query) use ($userCompanyArray) {
                foreach ($userCompanyArray as $companycode) {
                    $query->orWhere('companycode', 'like', "%{$companycode}%");
                }
            })
            ->where(function($query) use ($idjabatan) {
                $query->whereNull('target_jabatan')
                    ->orWhere('target_jabatan', '')
                    ->orWhere('target_jabatan', 'like', "%{$idjabatan}%");
            })
            ->orderBy('createdat', 'desc')
            ->limit(10)
            ->get()
            ->map(function($notif) use ($userId) {
                $readBy = [];
                if (!empty($notif->readby)) {
                    $decoded = json_decode($notif->readby, true);
                    $readBy = is_array($decoded) ? $decoded : [];
                }
                $notif->is_read = in_array($userId, $readBy);
                return $notif;
            });
        
        $unreadCount = $notifications->where('is_read', false)->count();
        
        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ];
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(string|int $userId): int
    {
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return 0;
        }

        // Get user companies
        $userCompanies = $user->userCompanies;
        if ($userCompanies->isEmpty()) {
            $userCompanyArray = $user->companycode ? explode(',', $user->companycode) : [];
        } else {
            $userCompanyArray = $userCompanies->pluck('companycode')->toArray();
        }
        $userCompanyArray = array_filter($userCompanyArray);
        
        $idjabatan = $user->idjabatan;
        
        // Query unread notifications
        $notifications = DB::table('notification')
            ->where('status', 'active')
            ->where(function($query) use ($userCompanyArray) {
                foreach ($userCompanyArray as $companycode) {
                    $query->orWhere('companycode', 'like', "%{$companycode}%");
                }
            })
            ->where(function($query) use ($idjabatan) {
                $query->whereNull('target_jabatan')
                    ->orWhere('target_jabatan', '')
                    ->orWhere('target_jabatan', 'like', "%{$idjabatan}%");
            })
            ->get()
            ->map(function($notif) use ($userId) {
                $readBy = [];
                if (!empty($notif->readby)) {
                    $decoded = json_decode($notif->readby, true);
                    $readBy = is_array($decoded) ? $decoded : [];
                }
                $notif->is_read = in_array($userId, $readBy);
                return $notif;
            });
        
        return $notifications->where('is_read', false)->count();
    }

    /**
     * Find notification by ID
     */
    public function findById(int $notificationId): ?Notification
    {
        return Notification::find($notificationId);
    }

    /**
     * Get all notifications for admin (with pagination)
     */
    public function getAllForAdmin(array $filters = [], int $perPage = 15)
    {
        $query = Notification::with('supportTicket');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhere('companycode', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('createdat', 'desc')->paginate($perPage);
    }

    /**
     * Get target users based on companies and jabatan
     */
    public function getTargetUsers(array $companyCodes, $targetJabatan = null): Collection
    {
        $query = DB::table('user')
            ->select('userid', 'idjabatan', 'companycode')
            ->where('isactive', true);

        // Filter by company
        if (!empty($companyCodes)) {
            $query->where(function($q) use ($companyCodes) {
                foreach ($companyCodes as $code) {
                    $q->orWhere('companycode', 'like', "%{$code}%");
                }
            });
        }

        // Filter by jabatan
        if (!empty($targetJabatan)) {
            $jabatanArray = is_array($targetJabatan) ? $targetJabatan : explode(',', $targetJabatan);
            $query->where(function($q) use ($jabatanArray) {
                foreach ($jabatanArray as $jabatan) {
                    $q->orWhere('idjabatan', $jabatan);
                }
            });
        }

        return $query->get();
    }

    /**
     * Create notification
     */
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    /**
     * Update notification
     */
    public function update(Notification $notification, array $data): bool
    {
        return $notification->update($data);
    }

    /**
     * Soft delete notification (set status to deleted)
     */
    public function softDelete(Notification $notification): bool
    {
        return $notification->update([
            'status' => 'deleted',
            'updatedat' => now()
        ]);
    }
}