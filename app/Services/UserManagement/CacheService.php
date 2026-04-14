<?php

namespace App\Services\UserManagement;

use App\Models\User;
use App\Models\Permission;
use App\Models\JabatanPermission;
use App\Models\UserPermission;
use Illuminate\Support\Facades\{Cache, Log};

class CacheService
{
    /**
     * Clear all permission cache for a user
     *
     * @param User $user
     * @param string $reason
     * @return void
     */
    public function clearUserCache(User $user, string $reason = 'Manual clear'): void
    {
        // Clear permission cache
        \App\Http\Middleware\CheckPermission::clearUserCache($user);

        // Clear navigation cache
        \App\View\Composers\NavigationComposer::clearNavigationCache($user);

        Log::info('Cache cleared for user', [
            'userid' => $user->userid,
            'jabatan' => $user->idjabatan,
            'reason' => $reason
        ]);
    }

    /**
     * Clear user + company cache
     *
     * @param User $user
     * @param string $reason
     * @return void
     */
    public function clearUserAndCompanyCache(User $user, string $reason = 'Company access changed'): void
    {
        // Clear permission cache
        \App\Http\Middleware\CheckPermission::clearUserCache($user);

        // Clear company cache
        Cache::forget("user_companies_{$user->userid}");

        // Clear navigation cache
        \App\View\Composers\NavigationComposer::clearNavigationCache($user);

        Log::info('User and company cache cleared', [
            'userid' => $user->userid,
            'reason' => $reason
        ]);
    }

    /**
     * Clear cache for all users in a jabatan
     *
     * @param int $idjabatan
     * @return int Number of affected users
     */
    public function clearCacheForJabatan(int $idjabatan): int
    {
        $users = User::where('idjabatan', $idjabatan)
            ->where('isactive', 1)
            ->get();

        foreach ($users as $user) {
            \App\Http\Middleware\CheckPermission::clearUserCache($user);
            \App\View\Composers\NavigationComposer::clearNavigationCache($user);
        }

        Log::info('Bulk cache clear for jabatan', [
            'idjabatan' => $idjabatan,
            'affected_users' => $users->count()
        ]);

        return $users->count();
    }

    /**
     * Clear cache for multiple users
     *
     * @param array $userIds
     * @return int Number of affected users
     */
    public function clearCacheForUsers(array $userIds): int
    {
        $users = User::whereIn('userid', $userIds)
            ->where('isactive', 1)
            ->get();

        foreach ($users as $user) {
            \App\Http\Middleware\CheckPermission::clearUserCache($user);
        }

        Log::info('Bulk cache clear for users', [
            'count' => $users->count(),
            'userids' => $userIds
        ]);

        return $users->count();
    }

    /**
     * Clear cache for permission changes
     *
     * @param string $displayname
     * @return int Number of affected users
     */
    public function clearCacheForPermission(string $displayname): int
    {
        try {
            $permission = Permission::where('displayname', $displayname)->first();
            
            if (!$permission) {
                Log::warning('Permission not found for cache clear', ['permission' => $displayname]);
                return 0;
            }

            // Find jabatan using this permission
            $jabatanIds = JabatanPermission::where('permissionid', $permission->id)
                ->where('isactive', 1)
                ->pluck('idjabatan')
                ->unique();

            // Clear cache for affected jabatan
            $affectedCount = 0;
            foreach ($jabatanIds as $idjabatan) {
                $affectedCount += $this->clearCacheForJabatan($idjabatan);
            }

            // Clear for users with permission overrides
            $userIds = UserPermission::where('permissionid', $permission->id)
                ->where('isactive', 1)
                ->pluck('userid')
                ->unique()
                ->toArray();

            if (!empty($userIds)) {
                $affectedCount += $this->clearCacheForUsers($userIds);
            }

            // Clear menu cache
            Cache::forget('navigationMenus');
            Cache::forget('allSubmenus');

            Log::info('Bulk cache clear for permission', [
                'permission' => $displayname,
                'affected_users' => $affectedCount
            ]);

            return $affectedCount;
        } catch (\Exception $e) {
            Log::error('Error clearing cache for permission', [
                'permission' => $displayname,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Clear all menu caches
     *
     * @return void
     */
    public function clearMenuCache(): void
    {
        Cache::forget('navigationMenus');
        Cache::forget('allSubmenus');

        Log::info('Menu cache cleared');
    }
}