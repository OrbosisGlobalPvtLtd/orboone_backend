<?php

namespace App\Services\AccessControl;

use App\Services\AccessControl\SidebarS;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionSyncService
{
    /**
     * Synchronize role_permissions for a given role based on selected menu IDs.
     */
    public function syncRolePermissionsFromMenus(int $roleId, array $menuIds): array
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        if (! $role) {
            return [];
        }

        // If super_admin, grant 100% permissions
        if ($role->slug === 'super_admin') {
            $permissionIds = DB::table('permissions')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            DB::table('role_permissions')->where('role_id', $roleId)->delete();
        } else {
            $permissionIds = $this->derivePermissionsForMenuIds($menuIds);
            $allMenuDerivableIds = $this->getAllMenuDerivablePermissionIds();

            if (! empty($allMenuDerivableIds)) {
                DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->whereIn('permission_id', $allMenuDerivableIds)
                    ->delete();
            }
        }

        if (! empty($permissionIds)) {
            $existingPermIds = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->pluck('permission_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $toInsert = array_values(array_diff($permissionIds, $existingPermIds));

            if (! empty($toInsert)) {
                $rows = collect($toInsert)->map(function ($permissionId) use ($roleId) {
                    return [
                        'role_id' => $roleId,
                        'permission_id' => (int) $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

                $chunkSize = (int) config('authorization.db_chunk_size', 100);
                foreach (array_chunk($rows, $chunkSize) as $chunk) {
                    DB::table('role_permissions')->insert($chunk);
                }
            }
        }

        // Clear all caches for users holding this role after transaction commits
        DB::afterCommit(function () use ($roleId) {
            $this->clearRoleAndUserCaches($roleId);
        });

        return $permissionIds;
    }

    /**
     * Get all permission IDs that can ever be derived from active menus in the system.
     */
    public function getAllMenuDerivablePermissionIds(): array
    {
        if (! Schema::hasTable('menus')) {
            return [];
        }

        $allActiveMenuIds = DB::table('menus')->where('is_active', 1)->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $this->derivePermissionsForMenuIds($allActiveMenuIds);
    }

    /**
     * Dynamically derive permission IDs for selected menu IDs deterministically.
     */
    public function derivePermissionsForMenuIds(array $menuIds): array
    {
        if (empty($menuIds) || ! Schema::hasTable('menus') || ! Schema::hasTable('permissions')) {
            return [];
        }

        $menus = DB::table('menus')->whereIn('id', $menuIds)->get();
        if ($menus->isEmpty()) {
            return [];
        }

        $matchedPermKeys = [];

        // Canonical Route -> Granular Permissions mapping from PermissionMapS
        $menuPermissionMap = PermissionMapS::getRoutePermissionMap();

        foreach ($menus as $m) {
            $route = (string) ($m->route ?? '');
            if ($route !== '' && isset($menuPermissionMap[$route])) {
                foreach ($menuPermissionMap[$route] as $permKey) {
                    $matchedPermKeys[] = $permKey;
                }
            }

            if (! empty($m->permission_key)) {
                $matchedPermKeys[] = $m->permission_key;
            }
        }

        $uniqueKeys = array_values(array_unique(array_filter($matchedPermKeys)));
        if (empty($uniqueKeys)) {
            return [];
        }

        return DB::table('permissions')
            ->whereIn('key', $uniqueKeys)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Clear all authorization, role, and navigation caches for users with the role.
     */
    public function clearRoleAndUserCaches(int $roleId): void
    {
        app(SidebarS::class)->clearRoleCaches($roleId);
    }
}
