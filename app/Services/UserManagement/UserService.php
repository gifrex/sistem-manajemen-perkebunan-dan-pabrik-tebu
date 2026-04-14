<?php

namespace App\Services\UserManagement;

use Illuminate\Database\Eloquent\Collection;
use App\Repositories\UserManagement\UserRepository;
use App\Models\UserCompany;
use App\Models\UserActivity;
use Illuminate\Support\Facades\{DB, Hash, Log};
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    protected UserRepository $userRepository;
    protected CacheService $cacheService;

    public function __construct(UserRepository $userRepository, CacheService $cacheService)
    {
        $this->userRepository = $userRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * Get paginated users
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->userRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get user with relations
     */
    public function getUserWithRelations(string $userid, array $relations = [])
    {
        return $this->userRepository->findWithRelations($userid, $relations);
    }

    /**
     * Create new user with company and activity assignments
     */
    public function createUser(array $data, string $createdBy): array
    {
        try {
            DB::beginTransaction();

            // Prepare user data
            $userData = [
                'userid' => $data['userid'],
                'name' => $data['name'],
                'companycode' => $data['companycode'],
                'idjabatan' => $data['idjabatan'],
                'password' => Hash::make($data['password']),
                'mpassword' => md5($data['password']),
                'inputby' => $createdBy,
                'createdat' => now(),
                'isactive' => $data['isactive'] ?? 1
            ];

            // Create user
            $user = $this->userRepository->create($userData);

            // Auto-assign to primary company
            UserCompany::create([
                'userid' => $data['userid'],
                'companycode' => $data['companycode'],
                'isactive' => 1,
                'grantedby' => $createdBy,
                'createdat' => now()
            ]);

            // Assign activity groups if provided (NORMALIZED - one row per activity)
            if (!empty($data['activitygroups'])) {
                foreach ($data['activitygroups'] as $activitygroup) {
                    UserActivity::create([
                        'userid' => $data['userid'],
                        'companycode' => $data['companycode'],
                        'activitygroup' => trim($activitygroup),
                        'isactive' => 1,
                        'grantedby' => $createdBy,
                        'createdat' => now()
                    ]);
                }
            }

            DB::commit();

            Log::info('User created', [
                'userid' => $data['userid'],
                'created_by' => $createdBy
            ]);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'User berhasil ditambahkan'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'user' => null,
                'message' => 'Gagal menambahkan user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update existing user
     */
    public function updateUser(string $userid, array $data, string $updatedBy): array
    {
        try {
            $user = $this->userRepository->findWithRelations($userid);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            $jabatanChanged = $user->idjabatan != $data['idjabatan'];

            // Prepare update data
            $updateData = [
                'name' => $data['name'],
                'companycode' => $data['companycode'],
                'idjabatan' => $data['idjabatan'],
                'isactive' => $data['isactive'] ?? 0,
                'updatedat' => now()
            ];

            // Update password if provided
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
                $updateData['mpassword'] = md5($data['password']);
            }

            // Update user
            $this->userRepository->update($userid, $updateData);

            // Update activity groups if provided (NORMALIZED)
            if (isset($data['activitygroups'])) {
                // Deactivate all existing activities for this company
                UserActivity::where('userid', $userid)
                    ->where('companycode', $data['companycode'])
                    ->update(['isactive' => 0, 'updatedat' => now()]);

                // Insert or reactivate new activities
                foreach ($data['activitygroups'] as $activitygroup) {
                    UserActivity::updateOrCreate(
                        [
                            'userid' => $userid,
                            'companycode' => $data['companycode'],
                            'activitygroup' => trim($activitygroup)
                        ],
                        [
                            'isactive' => 1,
                            'grantedby' => $updatedBy,
                            'createdat' => now(),
                            'updatedat' => now()
                        ]
                    );
                }
            }

            // Clear cache if jabatan changed
            if ($jabatanChanged) {
                $this->cacheService->clearUserCache($user, 'Jabatan changed');
            }

            Log::info('User updated', [
                'userid' => $userid,
                'updated_by' => $updatedBy
            ]);

            return [
                'success' => true,
                'message' => 'User berhasil diperbarui'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'userid' => $userid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Deactivate user (soft delete)
     */
    public function deactivateUser(string $userid, string $deactivatedBy): array
    {
        try {
            $user = $this->userRepository->findWithRelations($userid);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            $this->userRepository->softDelete($userid);
            $this->cacheService->clearUserCache($user, 'User deactivated');

            Log::info('User deactivated', [
                'userid' => $userid,
                'deactivated_by' => $deactivatedBy
            ]);

            return [
                'success' => true,
                'message' => 'User berhasil dinonaktifkan'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to deactivate user', [
                'userid' => $userid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menonaktifkan user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user permissions summary
     */
    public function getUserPermissionsSummary(string $userid): array
    {
        $user = $this->userRepository->findWithRelations($userid, ['jabatan']);

        if (!$user) {
            return ['error' => 'User not found'];
        }

        $result = [
            'role' => null,
            'overrides' => []
        ];

        // Get role information
        if ($user->idjabatan && $user->jabatan) {
            $permissionCount = \App\Models\JabatanPermission::where('idjabatan', $user->idjabatan)
                ->where('isactive', 1)
                ->count();

            $result['role'] = [
                'idjabatan' => $user->idjabatan,
                'namajabatan' => $user->jabatan->namajabatan,
                'count' => $permissionCount
            ];
        }

        // Get permission overrides
        $userPermissions = \App\Models\UserPermission::where('userid', $userid)
            ->where('isactive', 1)
            ->with('permission')
            ->get();

        foreach ($userPermissions as $perm) {
            $hasCompanyAccess = UserCompany::where('userid', $userid)
                ->where('companycode', $perm->companycode)
                ->where('isactive', 1)
                ->exists();

            if ($hasCompanyAccess) {
                $result['overrides'][] = [
                    'permission' => $perm->permission->displayname ?? $perm->permissionid,
                    'companycode' => $perm->companycode,
                    'permissiontype' => $perm->permissiontype,
                    'reason' => $perm->reason,
                    'grantedby' => $perm->grantedby,
                    'createdat' => $perm->createdat ? $perm->createdat->format('Y-m-d H:i:s') : null
                ];
            }
        }

        return $result;
    }

    /**
     * Get user activities for company (NORMALIZED version)
     */
    public function getUserActivities(string $userid, string $companycode): array
    {
        return UserActivity::where('userid', $userid)
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->pluck('activitygroup')
            ->toArray();
    }

    public function getMandorList(string $companyCode): Collection
    {
        return $this->userRepository->getMandorByCompany($companyCode);
    }

    public function getOperatorList(string $companyCode): Collection
    {
        return $this->userRepository->getOperatorsByCompany($companyCode);
    }

    public function getUsersByRole(string $companyCode, int $jabatanId): Collection
    {
        return $this->userRepository->getByJabatan($jabatanId, true);
    }
}