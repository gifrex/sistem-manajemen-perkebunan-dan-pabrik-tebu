<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserManagement\{UserPermissionService, PermissionService};
use App\Models\User;
use App\Models\MasterData\Company;

class UserPermissionController extends Controller
{
    protected UserPermissionService $userPermissionService;
    protected PermissionService $permissionService;

    public function __construct(
        UserPermissionService $userPermissionService,
        PermissionService $permissionService
    ) {
        $this->userPermissionService = $userPermissionService;
        $this->permissionService = $permissionService;
    }

    /**
     * Display user permission overrides listing
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'companycode' => $request->get('companycode')
        ];

        $perPage = $request->get('perPage', 15);
        $result = $this->userPermissionService->getPaginatedPermissionOverrides($filters, $perPage);

        $users = User::with('jabatan')
            ->where('isactive', 1)
            ->orderBy('name')
            ->get();

        $permissions = $this->permissionService->getAllGroupedByModule();
        $companies = Company::orderBy('name')->get();

        return view('usermanagement.user-permission.index', [
            'title' => 'User Permission Overrides',
            'navbar' => 'User Management',
            'nav' => 'Permission Overrides',
            'result' => $result,
            'users' => $users,
            'permissions' => $permissions,
            'companies' => $companies,
            'perPage' => $perPage
        ]);
    }

    // No show() method - detail handled in modal

    /**
     * Create permission override
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'userid' => 'required|string|exists:user,userid',
            'companycode' => 'required|string|exists:company,companycode',
            'permissionid' => 'required|integer|exists:permission,id',
            'permissiontype' => 'required|in:GRANT,DENY',
            'reason' => 'nullable|string|max:255'
        ]);

        $result = $this->userPermissionService->createPermissionOverride(
            $validated,
            auth()->user()->userid
        );

        if ($result['success']) {
            return redirect()->route('usermanagement.user-permission.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Delete permission override
     */
    public function destroy($id)
    {
        $result = $this->userPermissionService->deletePermissionOverride($id);

        return redirect()->route('usermanagement.user-permission.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}