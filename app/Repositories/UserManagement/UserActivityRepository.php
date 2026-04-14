<?php

namespace App\Repositories\UserManagement;

use App\Models\UserActivity;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserActivityRepository
{
    /**
     * Get paginated user activities (grouped by user-company)
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('useractivity as ua')
            ->select(
                'ua.userid',
                'ua.companycode',
                DB::raw('GROUP_CONCAT(ua.activitygroup ORDER BY ua.activitygroup SEPARATOR ",") as activitygroups'),
                DB::raw('MAX(ua.grantedby) as grantedby'),
                DB::raw('MAX(ua.createdat) as createdat'),
                DB::raw('MAX(ua.updatedat) as updatedat')
            )
            ->where('ua.isactive', 1)
            ->groupBy('ua.userid', 'ua.companycode');

        // Apply company filter
        if (!empty($filters['companycode'])) {
            $query->where('ua.companycode', $filters['companycode']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ua.userid', 'like', "%{$search}%")
                ->orWhere('ua.activitygroup', 'like', "%{$search}%");
            });
        }

        $results = $query->orderBy('ua.createdat', 'desc')
            ->paginate($perPage);

        // Load relationships (TANPA jabatan karena ga dipake di blade)
        $userIds = $results->pluck('userid')->unique();
        $companyCodes = $results->pluck('companycode')->unique();
        
        $users = \App\Models\User::whereIn('userid', $userIds)->get()->keyBy('userid');
        $companies = \App\Models\MasterData\Company::whereIn('companycode', $companyCodes)->get()->keyBy('companycode');
        
        $results->getCollection()->transform(function ($item) use ($users, $companies) {
            $item->user = $users->get($item->userid);
            $item->company = $companies->get($item->companycode);
            return $item;
        });

        return $results;
    }

    /**
     * Get user activities for specific company
     */
    public function getUserActivities(string $userid, string $companycode): Collection
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->with('activityGroupModel')
            ->get();
    }

    /**
     * Get activity groups as array (for AJAX)
     */
    public function getActivityGroupsArray(string $userid, string $companycode): array
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->pluck('activitygroup')
            ->toArray();
    }

    /**
     * Sync activities for user (replace all)
     */
    public function syncActivities(string $userid, string $companycode, array $activitygroups, string $grantedBy): void
    {
        // Deactivate all existing
        UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->update(['isactive' => 0, 'updatedat' => now()]);

        // Insert or reactivate
        foreach ($activitygroups as $activitygroup) {
            UserActivity::updateOrCreate(
                [
                    'userid' => $userid,
                    'companycode' => $companycode,
                    'activitygroup' => trim($activitygroup)
                ],
                [
                    'isactive' => 1,
                    'grantedby' => $grantedBy,
                    'createdat' => now(),
                    'updatedat' => now()
                ]
            );
        }
    }

    /**
     * Delete all user activities for company
     */
    public function deleteAll(string $userid, string $companycode): bool
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->update(['isactive' => 0, 'updatedat' => now()]);
    }

    /**
     * Delete specific activity
     */
    public function deleteSingle(string $userid, string $companycode, string $activitygroup): bool
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->update(['isactive' => 0, 'updatedat' => now()]);
    }

    /**
     * Check if exists
     */
    public function exists(string $userid, string $companycode): bool
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->exists();
    }

    /**
     * Count activities for user
     */
    public function countForUser(string $userid): int
    {
        return UserActivity::where('userid', $userid)
            ->where('isactive', 1)
            ->distinct()
            ->count(['companycode']);
    }
}