<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PermissionService;

/**
 * Navigation Composer (OPTIMIZED)
 * 
 * BEST PRACTICE: View composer for sidebar navigation
 * - Reads menu from config/menu.php (not database)
 * - Filters menu items based on user permissions
 * - Uses static caching to prevent duplicate queries per request
 * 
 * OPTIMIZATION CHANGES:
 * - Added static cache to prevent multiple queries in same request
 * - Combined company name & period into single query
 * - Use Auth::user() directly instead of re-querying user table
 * - Removed duplicate queries in catch block
 */
class NavigationComposer
{
    /**
     * Static cache untuk mencegah query berulang dalam satu request
     * @var array|null
     */
    private static ?array $cachedData = null;

    /**
     * Static cache untuk user companies
     * @var array|null
     */
    private static ?array $cachedUserCompanies = null;

    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        if (!Auth::check()) {
            return;
        }

        // Gunakan cached data jika sudah ada (dalam request yang sama)
        if (self::$cachedData !== null) {
            $view->with(self::$cachedData);
            return;
        }

        $user = Auth::user();

        try {
            // Get company code dari session
            $companyCode = session('companycode');
            
            // Single query untuk company data (gabung name dan period)
            $companyData = $this->getCompanyDataOnce($companyCode);

            // Get menu structure from config file
            $menuConfig = config('menu', []);

            // Filter menu based on permissions
            $navigationMenus = $this->filterMenuByPermissions($menuConfig, $user);

            // Build cached data
            self::$cachedData = [
                'navigationMenus' => collect($navigationMenus),
                'companyName' => $this->resolveCompanyName($companyData, $companyCode),
                'user' => $user->name ?? $user->userid ?? 'Guest',
                'company' => $this->getUserCompaniesOnce($user->userid),
                'period' => $this->formatPeriod($companyData?->companyperiod),
            ];

            // Update session jika company name tersedia
            if ($companyData?->name) {
                session(['companyname' => $companyData->name]);
            }

            $view->with(self::$cachedData);

        } catch (\Exception $e) {
            Log::error('Navigation composer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => $user->userid ?? 'unknown'
            ]);

            // Fallback: gunakan data minimal TANPA query tambahan
            self::$cachedData = [
                'navigationMenus' => collect([]),
                'companyName' => session('companyname', session('companycode', 'Default Company')),
                'user' => $user->name ?? $user->userid ?? 'Guest',
                'company' => [],
                'period' => null,
            ];

            $view->with(self::$cachedData);
        }
    }

    /**
     * Get company data (name & period) - dengan static cache
     * 
     * @param string|null $companyCode
     * @return object|null
     */
    private function getCompanyDataOnce(?string $companyCode): ?object
    {
        static $companyData = null;
        static $lastCompanyCode = null;

        // Return cached jika company code sama
        if ($companyData !== null && $lastCompanyCode === $companyCode) {
            return $companyData;
        }

        if (!$companyCode) {
            return null;
        }

        $lastCompanyCode = $companyCode;
        $companyData = DB::table('company')
            ->where('companycode', $companyCode)
            ->select('name', 'companyperiod')
            ->first();

        return $companyData;
    }

    /**
     * Get user companies - dengan static cache
     * 
     * @param string $userId
     * @return array
     */
    private function getUserCompaniesOnce(string $userId): array
    {
        // Return cached jika sudah ada
        if (self::$cachedUserCompanies !== null) {
            return self::$cachedUserCompanies;
        }

        try {
            self::$cachedUserCompanies = DB::table('usercompany')
                ->where('userid', $userId)
                ->where('isactive', 1)
                ->pluck('companycode')
                ->sort()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting user companies: ' . $e->getMessage());
            self::$cachedUserCompanies = [];
        }

        return self::$cachedUserCompanies;
    }

    /**
     * Resolve company name dengan fallback
     * 
     * @param object|null $companyData
     * @param string|null $companyCode
     * @return string
     */
    private function resolveCompanyName(?object $companyData, ?string $companyCode): string
    {
        if ($companyData?->name) {
            return $companyData->name;
        }

        if ($companyCode) {
            return $companyCode;
        }

        return session('companyname', 'Default Company');
    }

    /**
     * Format period untuk display
     * 
     * @param string|null $period
     * @return string|null
     */
    private function formatPeriod(?string $period): ?string
    {
        if (!$period) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($period)->format('F Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Filter menu items based on user permissions (recursive)
     * 
     * @param array $menuItems
     * @param \App\Models\User $user
     * @return array
     */
    private function filterMenuByPermissions(array $menuItems, $user): array
    {
        $filtered = [];

        foreach ($menuItems as $item) {
            // Check if user has permission for this menu item
            if (isset($item['permission'])) {
                if (!PermissionService::check($user, $item['permission'])) {
                    continue; // Skip this item
                }
            }

            // Process children recursively
            if (isset($item['children']) && is_array($item['children'])) {
                $filteredChildren = $this->filterMenuByPermissions($item['children'], $user);
                
                // Only include parent if it has visible children OR has a route itself
                if (!empty($filteredChildren)) {
                    $item['children'] = $filteredChildren;
                    $filtered[] = $item;
                } elseif (isset($item['route'])) {
                    // Parent has route and user has permission
                    unset($item['children']); // Remove empty children
                    $filtered[] = $item;
                }
            } else {
                // Leaf node (no children)
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    /**
     * Check if user has permission (for backward compatibility)
     * 
     * @param string $permissionKey
     * @return bool
     */
    public function hasPermission(string $permissionKey): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return PermissionService::check(Auth::user(), $permissionKey);
    }

    /**
     * Reset static cache
     * Panggil method ini jika data berubah mid-request (jarang diperlukan)
     * 
     * @return void
     */
    public static function resetCache(): void
    {
        self::$cachedData = null;
        self::$cachedUserCompanies = null;
    }

    /**
     * Clear navigation cache for a user
     * (Now just clears permission cache since menu is config-based)
     * 
     * @param \App\Models\User $user
     * @return void
     */
    public static function clearNavigationCache($user): void
    {
        self::resetCache();
        PermissionService::clearUserCache($user);
    }

    /**
     * Clear all navigation caches
     * (Now just clears all permission caches)
     * 
     * @return void
     */
    public static function clearAllNavigationCaches(): void
    {
        self::resetCache();
        
        try {
            // Clear all permission caches
            if (config('cache.default') === 'redis') {
                $redis = \Illuminate\Support\Facades\Cache::getRedis();
                
                $permKeys = $redis->keys('*user_permissions_*');
                foreach ($permKeys as $key) {
                    \Illuminate\Support\Facades\Cache::forget(
                        str_replace(config('cache.prefix') . ':', '', $key)
                    );
                }
                
                Log::info('All permission caches cleared via Redis');
            } else {
                // Fallback: clear all cache
                \Illuminate\Support\Facades\Cache::flush();
                Log::info('All caches cleared (including permissions)');
            }

        } catch (\Exception $e) {
            Log::error('Error clearing all caches: ' . $e->getMessage());
        }
    }
}