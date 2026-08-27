<?php

namespace App\Services\AccessControl;

use App\Services\AccessControl\SidebarS;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RbacPolicyMatrixS
{
    /**
     * Build full RBAC Policy Matrix tree for a given role ID.
     */
    public function getMatrixForRole(int $roleId): array
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        if (! $role) {
            return [];
        }

        $allRoles = DB::table('roles')->orderByDesc('is_system')->orderBy('name')->get();

        // 1. Assigned Menu IDs & Permission IDs
        $assignedMenuIds = DB::table('role_menu_access')
            ->where('role_id', $roleId)
            ->pluck('menu_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $assignedPermIds = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->pluck('permission_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($role->slug === 'super_admin') {
            $assignedMenuIds = DB::table('menus')->where('is_active', 1)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $assignedPermIds = DB::table('permissions')->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        // 2. Fetch all active menus
        $rawMenus = DB::table('menus')
            ->where('is_active', 1)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // 3. Fetch all permissions
        $allPermissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('submodule')
            ->orderBy('action')
            ->get();

        // Map permissions by module/submodule/key for quick lookup
        $permissionsById = $allPermissions->keyBy('id');
        $mappedPermissionIds = [];

        $parentMenus = $rawMenus->whereNull('parent_id')->sortBy('sort_order');
        $childrenByParent = $rawMenus->whereNotNull('parent_id')->groupBy('parent_id');

        $modulesTree = [];

        foreach ($parentMenus as $parent) {
            $children = $childrenByParent->get($parent->id, collect());
            $submenusTree = [];

            foreach ($children as $child) {
                // Find CRUD permissions relevant to this child menu
                $crudPermissions = $this->findPermissionsForMenu($child, $allPermissions);

                $crudList = [];
                foreach ($crudPermissions as $perm) {
                    $mappedPermissionIds[] = (int) $perm->id;
                    $crudList[] = [
                        'id' => (int) $perm->id,
                        'key' => $perm->key,
                        'action' => $perm->action,
                        'description' => $perm->description ?: $perm->key,
                        'is_assigned' => in_array((int) $perm->id, $assignedPermIds, true),
                    ];
                }

                $submenusTree[] = [
                    'id' => (int) $child->id,
                    'name' => $child->name,
                    'route' => $child->route,
                    'module_key' => $child->module_key,
                    'permission_key' => $child->permission_key,
                    'is_assigned' => in_array((int) $child->id, $assignedMenuIds, true),
                    'crud_permissions' => $crudList,
                ];
            }

            // Also check if parent menu has permissions directly
            $parentCrudPermissions = $this->findPermissionsForMenu($parent, $allPermissions);
            $parentCrudList = [];
            foreach ($parentCrudPermissions as $perm) {
                $mappedPermissionIds[] = (int) $perm->id;
                $parentCrudList[] = [
                    'id' => (int) $perm->id,
                    'key' => $perm->key,
                    'action' => $perm->action,
                    'description' => $perm->description ?: $perm->key,
                    'is_assigned' => in_array((int) $perm->id, $assignedPermIds, true),
                ];
            }

            $modulesTree[] = [
                'id' => (int) $parent->id,
                'name' => $parent->name,
                'module_key' => $parent->module_key,
                'is_assigned' => in_array((int) $parent->id, $assignedMenuIds, true),
                'crud_permissions' => $parentCrudList,
                'submenus' => $submenusTree,
            ];
        }

        // Remaining unmapped permissions
        $unmappedPermissions = $allPermissions->reject(function ($perm) use ($mappedPermissionIds) {
            return in_array((int) $perm->id, $mappedPermissionIds, true);
        })->map(function ($perm) use ($assignedPermIds) {
            return [
                'id' => (int) $perm->id,
                'module' => $perm->module,
                'submodule' => $perm->submodule,
                'key' => $perm->key,
                'action' => $perm->action,
                'description' => $perm->description ?: $perm->key,
                'is_assigned' => in_array((int) $perm->id, $assignedPermIds, true),
            ];
        })->groupBy('module');

        return [
            'role' => $role,
            'all_roles' => $allRoles,
            'modules' => $modulesTree,
            'unmapped_permissions' => $unmappedPermissions,
            'assigned_menu_ids' => $assignedMenuIds,
            'assigned_permission_ids' => $assignedPermIds,
        ];
    }

    /**
     * Atomically save menu & permission assignments for a role and flush user caches.
     */
    public function syncRolePolicyMatrix(int $roleId, array $menuIds, array $permissionIds): void
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        if (! $role) {
            return;
        }

        $menuIds = collect($menuIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $permissionIds = collect($permissionIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        // If submenus selected, ensure parent menus are also included
        if (! $menuIds->isEmpty()) {
            $parentIds = DB::table('menus')
                ->whereIn('id', $menuIds)
                ->whereNotNull('parent_id')
                ->pluck('parent_id')
                ->map(fn ($id) => (int) $id);

            $menuIds = $menuIds->merge($parentIds)->unique()->values();
        }

        if ($role->slug === 'super_admin') {
            $menuIds = DB::table('menus')->where('is_active', 1)->pluck('id')->map(fn ($id) => (int) $id);
            $permissionIds = DB::table('permissions')->pluck('id')->map(fn ($id) => (int) $id);
        }

        DB::transaction(function () use ($roleId, $menuIds, $permissionIds) {
            // 1. Sync role_menu_access
            DB::table('role_menu_access')->where('role_id', $roleId)->delete();
            if ($menuIds->isNotEmpty()) {
                $rows = $menuIds->map(fn ($mId) => [
                    'role_id' => $roleId,
                    'menu_id' => $mId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table('role_menu_access')->insert($chunk);
                }
            }

            // 2. Sync role_permissions
            DB::table('role_permissions')->where('role_id', $roleId)->delete();
            if ($permissionIds->isNotEmpty()) {
                $rows = $permissionIds->map(fn ($pId) => [
                    'role_id' => $roleId,
                    'permission_id' => $pId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table('role_permissions')->insert($chunk);
                }
            }
        });

        // 3. Clear authorization and sidebar caches for affected users
        $this->flushRoleCaches($roleId);
    }

    /**
     * Clear all user caches for users holding the role.
     */
    public function flushRoleCaches(int $roleId): void
    {
        $userIds = DB::table('users')
            ->where('system_role_id', $roleId)
            ->pluck('id')
            ->merge(DB::table('user_roles')->where('role_id', $roleId)->pluck('user_id'))
            ->unique()
            ->all();

        try {
            if (app()->bound('cache')) {
                app('cache')->forget('spatie.permission.cache');
                app('cache')->forget('app_permissions_cache');
            }
        } catch (\Throwable $e) {
        }

        $sidebarService = app(SidebarS::class);
        foreach ($userIds as $userId) {
            try {
                $sidebarService->clearCache((int) $userId);
                Cache::forget('user_permissions_' . $userId);
                Cache::forget('user_menus_' . $userId);
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Helper to match permission items to a menu item based on permission_key, module_key, or submodule tokens.
     */
    private function findPermissionsForMenu(object $menu, $allPermissions)
    {
        $matched = collect();

        $permKey = strtolower(trim((string) ($menu->permission_key ?? '')));
        $modKey = strtolower(trim((string) ($menu->module_key ?? '')));
        $routeName = strtolower(trim((string) ($menu->route ?? '')));

        if ($permKey !== '') {
            $keyPrefix = explode('.', $permKey)[0] ?? '';
            $keyPrefix2 = implode('.', array_slice(explode('.', $permKey), 0, 2));

            foreach ($allPermissions as $p) {
                $pK = strtolower($p->key);
                if ($pK === $permKey || str_starts_with($pK, $keyPrefix . '.') || str_starts_with($pK, $keyPrefix2 . '.')) {
                    $matched->push($p);
                }
            }
        }

        if ($matched->isEmpty() && $modKey !== '') {
            foreach ($allPermissions as $p) {
                $submod = strtolower($p->submodule ?? '');
                $pK = strtolower($p->key);
                if ($submod === $modKey || str_contains($submod, $modKey) || str_starts_with($pK, $modKey . '.')) {
                    $matched->push($p);
                }
            }
        }

        if ($matched->isEmpty() && $routeName !== '') {
            $routeClean = str_replace(['hrms.', 'pages.', '.index', '.dashboard'], '', $routeName);
            $token = str_replace(['-', '.'], '_', $routeClean);
            foreach ($allPermissions as $p) {
                $submod = strtolower($p->submodule ?? '');
                $pK = strtolower($p->key);
                if ($submod === $token || str_contains($submod, $token) || str_starts_with($pK, $token . '.')) {
                    $matched->push($p);
                }
            }
        }

        return $matched->unique('id')->values();
    }
}
