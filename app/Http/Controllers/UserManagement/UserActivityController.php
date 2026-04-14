<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserManagement\UserActivityService;
use App\Models\User;
use App\Models\MasterData\Company;
use App\Models\MasterData\ActivityGroup;

class UserActivityController extends Controller
{
    protected UserActivityService $userActivityService;

    public function __construct(UserActivityService $userActivityService)
    {
        $this->userActivityService = $userActivityService;
    }

    /**
     * Display user activity permissions listing
     */
    public function index(Request $request)
    {
        $companycode = session('companycode');
        
        $filters = [
            'search' => $request->get('search'),
            'companycode' => $companycode
        ];

        $perPage = $request->get('perPage', 15);
        $result = $this->userActivityService->getPaginatedActivities($filters, $perPage);

        // Get users with access to current company
        $users = User::where('isactive', 1)
            ->whereHas('userCompanies', function ($q) use ($companycode) {
                $q->where('companycode', $companycode)
                  ->where('isactive', 1);
            })
            ->orderBy('name')
            ->get();

        // Activity group options: activitygroup => groupname
        $activitygroup = ActivityGroup::orderBy('activitygroup')
            ->pluck('groupname', 'activitygroup');

        return view('usermanagement.user-activity.index', [
            'title' => 'User Activity Permission',
            'navbar' => 'User Management',
            'nav' => 'User Activity Permission',
            'result' => $result,
            'users' => $users,
            'perPage' => $perPage,
            'companycode' => $companycode,
            'activitygroup' => $activitygroup
        ]);
    }

    /**
     * AJAX: Get user activity for specific company
     */
    public function show($userid, $companycode)
    {
        $activities = $this->userActivityService->getUserActivity($userid, $companycode);

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }

    /**
     * Assign activity groups to user
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'userid' => 'required|string|exists:user,userid',
            'companycode' => 'required|string|exists:company,companycode',
            'activitygroups' => 'nullable|array',
            'activitygroups.*' => 'string'
        ]);

        $activitygroups = $validated['activitygroups'] ?? [];

        $result = $this->userActivityService->assignActivityGroups(
            $validated['userid'],
            $validated['companycode'],
            $activitygroups,
            auth()->user()->userid
        );

        return redirect()->route('usermanagement.user-activity.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Delete all user activities for company
     */
    public function destroy($userid, $companycode)
    {
        $result = $this->userActivityService->deleteUserActivity($userid, $companycode);

        return redirect()->route('usermanagement.user-activity.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}