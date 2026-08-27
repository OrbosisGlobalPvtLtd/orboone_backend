<?php

namespace App\Services\AccessControl;

use App\Services\AccessControl\SidebarS;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModulePermissionS
{
    /**
     * Standardize CRUD actions into View, Create, Edit, Delete.
     */
    public const CRUD_ACTIONS = [
        'view' => ['view', 'show', 'list', 'index', 'read', 'details', 'summary', 'own', 'team', 'all'],
        'create' => ['create', 'store', 'add', 'apply', 'submit', 'upload', 'generate'],
        'edit' => ['edit', 'update', 'modify', 'assign', 'manage', 'approve', 'regularize'],
        'delete' => ['delete', 'destroy', 'remove', 'purge', 'cancel'],
    ];

    /**
     * Get Module & CRUD matrix for a given Role.
     */
    public function getRoleMatrix(int $roleId): array
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        if (! $role) {
            return [];
        }

        $roles = DB::table('roles')->orderByDesc('is_system')->orderBy('name')->get();

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

        $modulesTree = $this->buildModuleCrudTree($assignedMenuIds, $assignedPermIds);

        return [
            'role' => $role,
            'roles' => $roles,
            'modulesTree' => $modulesTree,
            'assignedMenuIds' => $assignedMenuIds,
            'assignedPermIds' => $assignedPermIds,
        ];
    }

    /**
     * Save Role Matrix.
     */
    public function saveRoleMatrix(int $roleId, array $menuIds, array $permissionIds): void
    {
        $rbacService = app(RbacPolicyMatrixS::class);
        $rbacService->syncRolePolicyMatrix($roleId, $menuIds, $permissionIds);
    }

    /**
     * Get Module & CRUD matrix for a specific User with override status.
     */
    public function getUserMatrix(int $userId): array
    {
        $user = DB::table('users')
            ->leftJoin('roles', 'roles.id', '=', 'users.system_role_id')
            ->where('users.id', $userId)
            ->select('users.*', 'roles.name as primary_role_name', 'roles.slug as primary_role_slug')
            ->first();

        if (! $user) {
            return [];
        }

        $users = DB::table('users')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Collect user's role IDs
        $roleIds = [];
        if (! empty($user->system_role_id)) {
            $roleIds[] = (int) $user->system_role_id;
        }
        if (! empty($user->role_id)) {
            $roleIds[] = (int) $user->role_id;
        }
        if (Schema::hasTable('user_roles')) {
            $roleIds = array_merge(
                $roleIds,
                DB::table('user_roles')->where('user_id', $userId)->pluck('role_id')->map(fn ($id) => (int) $id)->all()
            );
        }
        $roleIds = array_unique(array_filter($roleIds));

        // Role permissions
        $rolePermIds = [];
        if (! empty($roleIds)) {
            if ($user->primary_role_slug === 'super_admin') {
                $rolePermIds = DB::table('permissions')->pluck('id')->map(fn ($id) => (int) $id)->all();
            } else {
                $rolePermIds = DB::table('role_permissions')
                    ->whereIn('role_id', $roleIds)
                    ->pluck('permission_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        // Department/Profile permissions
        $deptPermKeys = [];
        $employee = Schema::hasTable('employees_new') ? DB::table('employees_new')->where('user_id', $userId)->first(['department_id']) : null;
        if ($employee && ! empty($employee->department_id) && Schema::hasTable('department_module_access')) {
            $deptPermKeys = DB::table('department_module_access')
                ->where('department_id', $employee->department_id)
                ->where('is_allowed', 1)
                ->pluck('permission_key')
                ->filter()
                ->all();
        }

        // User overrides
        $userOverrides = [];
        if (Schema::hasTable('user_module_access')) {
            $overrides = DB::table('user_module_access')
                ->where('user_id', $userId)
                ->get();

            foreach ($overrides as $ov) {
                if (! empty($ov->permission_key)) {
                    $userOverrides[$ov->permission_key] = (bool) ($ov->is_allowed ?? $ov->is_enabled);
                }
            }
        }

        $modulesTree = $this->buildModuleCrudTreeWithUserStatus($rolePermIds, $deptPermKeys, $userOverrides, $user->primary_role_slug === 'super_admin');

        return [
            'user' => $user,
            'users' => $users,
            'modulesTree' => $modulesTree,
            'userOverrides' => $userOverrides,
        ];
    }

    /**
     * Save User Overrides.
     */
    public function saveUserMatrix(int $userId, array $grants, array $revokes): void
    {
        if (! Schema::hasTable('user_module_access')) {
            return;
        }

        DB::transaction(function () use ($userId, $grants, $revokes) {
            DB::table('user_module_access')->where('user_id', $userId)->delete();

            $rows = [];
            foreach ($grants as $permKey) {
                $permKey = trim((string) $permKey);
                if ($permKey === '') {
                    continue;
                }
                $moduleKey = explode('.', $permKey)[0] ?? 'general';
                $rows[] = [
                    'user_id' => $userId,
                    'module_key' => $moduleKey,
                    'permission_key' => $permKey,
                    'is_enabled' => 1,
                    'is_allowed' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach ($revokes as $permKey) {
                $permKey = trim((string) $permKey);
                if ($permKey === '') {
                    continue;
                }
                $moduleKey = explode('.', $permKey)[0] ?? 'general';
                $rows[] = [
                    'user_id' => $userId,
                    'module_key' => $moduleKey,
                    'permission_key' => $permKey,
                    'is_enabled' => 0,
                    'is_allowed' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('user_module_access')->insert($chunk);
            }
        });

        // Flush user cache
        $this->flushUserCache($userId);
    }

    /**
     * Get Module & CRUD matrix for a Profile / Department.
     */
    public function getProfileMatrix(int $departmentId): array
    {
        $department = DB::table('departments')->where('id', $departmentId)->first();
        $departments = DB::table('departments')->orderBy('name')->get();

        $assignedKeys = [];
        if ($department && Schema::hasTable('department_module_access')) {
            $assignedKeys = DB::table('department_module_access')
                ->where('department_id', $departmentId)
                ->where('is_allowed', 1)
                ->pluck('permission_key')
                ->filter()
                ->all();
        }

        $modulesTree = $this->buildModuleCrudTreeForProfile($assignedKeys);

        return [
            'department' => $department,
            'departments' => $departments,
            'modulesTree' => $modulesTree,
            'assignedKeys' => $assignedKeys,
        ];
    }

    /**
     * Save Profile / Department Matrix.
     */
    public function saveProfileMatrix(int $departmentId, array $permissionKeys): void
    {
        if (! Schema::hasTable('department_module_access')) {
            return;
        }

        DB::transaction(function () use ($departmentId, $permissionKeys) {
            DB::table('department_module_access')->where('department_id', $departmentId)->delete();

            $rows = [];
            foreach (array_unique($permissionKeys) as $permKey) {
                $permKey = trim((string) $permKey);
                if ($permKey === '') {
                    continue;
                }
                $moduleKey = explode('.', $permKey)[0] ?? 'general';
                $rows[] = [
                    'department_id' => $departmentId,
                    'module_key' => $moduleKey,
                    'permission_key' => $permKey,
                    'is_enabled' => 1,
                    'is_allowed' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('department_module_access')->insert($chunk);
            }
        });

        // Flush cache for all users in this department
        if (Schema::hasTable('employees_new')) {
            $userIds = DB::table('employees_new')
                ->where('department_id', $departmentId)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->all();

            foreach ($userIds as $uId) {
                $this->flushUserCache((int) $uId);
            }
        }
    }

    /**
     * Build unified Module CRUD tree for Role.
     */
    private function buildModuleCrudTree(array $assignedMenuIds, array $assignedPermIds): array
    {
        $rawMenus = DB::table('menus')
            ->where('is_active', 1)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $allPermissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('submodule')
            ->orderBy('key')
            ->get();

        $parentMenus = $rawMenus->whereNull('parent_id')->sortBy('sort_order');
        $childrenByParent = $rawMenus->whereNotNull('parent_id')->groupBy('parent_id');

        $tree = [];
        foreach ($parentMenus as $parent) {
            $children = $childrenByParent->get($parent->id, collect());
            $submenusTree = [];

            foreach ($children as $child) {
                $crud = $this->groupPermissionsByCrud($child, $allPermissions, $assignedPermIds);

                $submenusTree[] = [
                    'id' => (int) $child->id,
                    'name' => $child->name,
                    'route' => $child->route,
                    'module_key' => $child->module_key,
                    'permission_key' => $child->permission_key,
                    'is_assigned' => in_array((int) $child->id, $assignedMenuIds, true),
                    'crud' => $crud,
                ];
            }

            $parentCrud = $this->groupPermissionsByCrud($parent, $allPermissions, $assignedPermIds);

            $tree[] = [
                'id' => (int) $parent->id,
                'name' => $parent->name,
                'module_key' => $parent->module_key,
                'is_assigned' => in_array((int) $parent->id, $assignedMenuIds, true),
                'crud' => $parentCrud,
                'submenus' => $submenusTree,
            ];
        }

        return $tree;
    }

    /**
     * Build unified Module CRUD tree for User with override calculation.
     */
    private function buildModuleCrudTreeWithUserStatus(array $rolePermIds, array $deptPermKeys, array $userOverrides, bool $isSuperAdmin): array
    {
        $rawMenus = DB::table('menus')
            ->where('is_active', 1)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $allPermissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('submodule')
            ->orderBy('key')
            ->get();

        $parentMenus = $rawMenus->whereNull('parent_id')->sortBy('sort_order');
        $childrenByParent = $rawMenus->whereNotNull('parent_id')->groupBy('parent_id');

        $tree = [];
        foreach ($parentMenus as $parent) {
            $children = $childrenByParent->get($parent->id, collect());
            $submenusTree = [];

            foreach ($children as $child) {
                $crud = $this->groupPermissionsByCrudForUser($child, $allPermissions, $rolePermIds, $deptPermKeys, $userOverrides, $isSuperAdmin);

                $submenusTree[] = [
                    'id' => (int) $child->id,
                    'name' => $child->name,
                    'route' => $child->route,
                    'crud' => $crud,
                ];
            }

            $parentCrud = $this->groupPermissionsByCrudForUser($parent, $allPermissions, $rolePermIds, $deptPermKeys, $userOverrides, $isSuperAdmin);

            $tree[] = [
                'id' => (int) $parent->id,
                'name' => $parent->name,
                'crud' => $parentCrud,
                'submenus' => $submenusTree,
            ];
        }

        return $tree;
    }

    /**
     * Build unified Module CRUD tree for Profile.
     */
    private function buildModuleCrudTreeForProfile(array $assignedKeys): array
    {
        $rawMenus = DB::table('menus')
            ->where('is_active', 1)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $allPermissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('submodule')
            ->orderBy('key')
            ->get();

        $parentMenus = $rawMenus->whereNull('parent_id')->sortBy('sort_order');
        $childrenByParent = $rawMenus->whereNotNull('parent_id')->groupBy('parent_id');

        $tree = [];
        foreach ($parentMenus as $parent) {
            $children = $childrenByParent->get($parent->id, collect());
            $submenusTree = [];

            foreach ($children as $child) {
                $crud = $this->groupPermissionsByCrudForProfile($child, $allPermissions, $assignedKeys);

                $submenusTree[] = [
                    'id' => (int) $child->id,
                    'name' => $child->name,
                    'route' => $child->route,
                    'crud' => $crud,
                ];
            }

            $parentCrud = $this->groupPermissionsByCrudForProfile($parent, $allPermissions, $assignedKeys);

            $tree[] = [
                'id' => (int) $parent->id,
                'name' => $parent->name,
                'crud' => $parentCrud,
                'submenus' => $submenusTree,
            ];
        }

        return $tree;
    }

    private function groupPermissionsByCrud(object $menu, $allPermissions, array $assignedPermIds): array
    {
        $matched = app(RbacPolicyMatrixS::class)->getMatrixForRole(0)['modules'] ?? [];
        $relevant = $this->findPermissionsForMenu($menu, $allPermissions);

        $crud = [
            'view' => [],
            'create' => [],
            'edit' => [],
            'delete' => [],
        ];

        foreach ($relevant as $perm) {
            $action = strtolower($perm->action ?? '');
            $key = strtolower($perm->key ?? '');
            $type = $this->categorizeAction($action, $key);

            $crud[$type][] = [
                'id' => (int) $perm->id,
                'key' => $perm->key,
                'action' => $perm->action,
                'description' => $perm->description ?: $perm->key,
                'is_assigned' => in_array((int) $perm->id, $assignedPermIds, true),
            ];
        }

        return $crud;
    }

    private function groupPermissionsByCrudForUser(object $menu, $allPermissions, array $rolePermIds, array $deptPermKeys, array $userOverrides, bool $isSuperAdmin): array
    {
        $relevant = $this->findPermissionsForMenu($menu, $allPermissions);

        $crud = [
            'view' => [],
            'create' => [],
            'edit' => [],
            'delete' => [],
        ];

        foreach ($relevant as $perm) {
            $action = strtolower($perm->action ?? '');
            $key = strtolower($perm->key ?? '');
            $type = $this->categorizeAction($action, $key);

            $isRoleGranted = $isSuperAdmin || in_array((int) $perm->id, $rolePermIds, true);
            $isDeptGranted = in_array($perm->key, $deptPermKeys, true);
            $hasInherited = $isRoleGranted || $isDeptGranted;

            $overrideStatus = $userOverrides[$perm->key] ?? null; // true (grant), false (revoke), null (none)
            $isEffective = $overrideStatus !== null ? $overrideStatus : $hasInherited;

            $crud[$type][] = [
                'id' => (int) $perm->id,
                'key' => $perm->key,
                'action' => $perm->action,
                'description' => $perm->description ?: $perm->key,
                'is_inherited' => $hasInherited,
                'override_status' => $overrideStatus, // true, false, null
                'is_effective' => $isEffective,
            ];
        }

        return $crud;
    }

    private function groupPermissionsByCrudForProfile(object $menu, $allPermissions, array $assignedKeys): array
    {
        $relevant = $this->findPermissionsForMenu($menu, $allPermissions);

        $crud = [
            'view' => [],
            'create' => [],
            'edit' => [],
            'delete' => [],
        ];

        foreach ($relevant as $perm) {
            $action = strtolower($perm->action ?? '');
            $key = strtolower($perm->key ?? '');
            $type = $this->categorizeAction($action, $key);

            $crud[$type][] = [
                'id' => (int) $perm->id,
                'key' => $perm->key,
                'action' => $perm->action,
                'description' => $perm->description ?: $perm->key,
                'is_assigned' => in_array($perm->key, $assignedKeys, true),
            ];
        }

        return $crud;
    }

    private function categorizeAction(string $action, string $key): string
    {
        foreach (self::CRUD_ACTIONS as $crudType => $tokens) {
            foreach ($tokens as $t) {
                if (str_contains($action, $t) || str_ends_with($key, '.' . $t) || str_contains($key, '.' . $t . '.')) {
                    return $crudType;
                }
            }
        }

        return 'view';
    }

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

    private function flushUserCache(int $userId): void
    {
        try {
            if (app()->bound('cache')) {
                app('cache')->forget('spatie.permission.cache');
                app('cache')->forget('app_permissions_cache');
            }
        } catch (\Throwable $e) {
        }

        $sidebarService = app(SidebarS::class);
        try {
            $sidebarService->clearCache($userId);
            Cache::forget('user_permissions_' . $userId);
            Cache::forget('user_menus_' . $userId);
        } catch (\Throwable $e) {
        }
    }
}
