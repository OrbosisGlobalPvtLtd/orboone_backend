<?php

namespace App\Services\AccessControl;

use App\Models\Core\PermissionM;
use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RbacVisualizerS
{
    public const CRUD_ACTIONS = [
        'view' => ['view', 'show', 'list', 'index', 'read', 'details', 'summary', 'own', 'team', 'all'],
        'create' => ['create', 'store', 'add', 'apply', 'submit', 'upload', 'generate'],
        'edit' => ['edit', 'update', 'modify', 'assign', 'manage', 'approve', 'regularize'],
        'delete' => ['delete', 'destroy', 'remove', 'purge', 'cancel'],
    ];

    /**
     * Get complete visualizer data bundle for views and APIs.
     */
    public function getVisualizerData(): array
    {
        $roles = DB::table('roles')
            ->orderByDesc('is_system')
            ->orderBy('id')
            ->get();

        $menus = DB::table('menus')
            ->where('is_active', 1)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $permissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('submodule')
            ->orderBy('action')
            ->orderBy('key')
            ->get();

        // 1. Role-Menu assignments map [role_id => [menu_id => true]]
        $roleMenuAssignments = [];
        $rolePermAssignments = [];

        $roleMenuRows = DB::table('role_menu_access')->get();
        foreach ($roleMenuRows as $row) {
            $roleMenuAssignments[(int) $row->role_id][(int) $row->menu_id] = true;
        }

        $rolePermRows = DB::table('role_permissions')->get();
        foreach ($rolePermRows as $row) {
            $rolePermAssignments[(int) $row->role_id][(int) $row->permission_id] = true;
        }

        // 2. Build structured module hierarchy with attached CRUD permissions
        $modulesTree = $this->buildFullHierarchy($menus, $permissions, $roles, $roleMenuAssignments, $rolePermAssignments);

        // 3. Compute statistics and metrics per role
        $roleMetrics = $this->computeRoleMetrics($roles, $permissions, $menus, $roleMenuAssignments, $rolePermAssignments);

        // 4. Compute system-wide totals
        $systemTotals = [
            'total_roles' => $roles->count(),
            'total_permissions' => $permissions->count(),
            'total_menus' => $menus->count(),
            'total_modules' => count($modulesTree),
            'high_privilege_roles' => $roles->filter(fn ($r) => in_array($r->slug, ['super_admin', 'admin', 'hr_admin']))->count(),
        ];

        // 5. Active users list for testing/simulator
        $users = DB::table('users')
            ->where('is_active', 1)
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name', 'email', 'system_role_id']);

        return [
            'roles' => $roles,
            'modulesTree' => $modulesTree,
            'roleMetrics' => $roleMetrics,
            'systemTotals' => $systemTotals,
            'allPermissions' => $permissions,
            'allMenus' => $menus,
            'users' => $users,
        ];
    }

    /**
     * Build full hierarchy of Modules -> Submodules/Pages -> CRUD Permissions, with role matrix per item.
     */
    private function buildFullHierarchy(Collection $rawMenus, Collection $allPermissions, Collection $roles, array $roleMenuAssignments, array $rolePermAssignments): array
    {
        $parentMenus = $rawMenus->whereNull('parent_id')->sortBy('sort_order');
        $childrenByParent = $rawMenus->whereNotNull('parent_id')->groupBy('parent_id');

        $mappedPermissionIds = [];
        $tree = [];

        foreach ($parentMenus as $parent) {
            $children = $childrenByParent->get($parent->id, collect());
            $submenusList = [];

            foreach ($children as $child) {
                $childCrud = $this->findAndGroupPermissionsForMenu($child, $allPermissions, $roles, $rolePermAssignments);
                foreach ($childCrud['all_matched_ids'] as $pId) {
                    $mappedPermissionIds[] = $pId;
                }

                // Compute role access matrix for this submenu
                $childRoleAccess = [];
                foreach ($roles as $role) {
                    $rId = (int) $role->id;
                    $isSuper = ($role->slug === 'super_admin');
                    $hasMenu = $isSuper || isset($roleMenuAssignments[$rId][(int) $child->id]);

                    $childRoleAccess[$rId] = [
                        'menu_access' => $hasMenu,
                        'is_super_admin' => $isSuper,
                    ];
                }

                $submenusList[] = [
                    'id' => (int) $child->id,
                    'name' => $child->name,
                    'route' => $child->route,
                    'module_key' => $child->module_key,
                    'permission_key' => $child->permission_key,
                    'role_access' => $childRoleAccess,
                    'crud' => $childCrud['grouped'],
                    'permissions_list' => $childCrud['list'],
                ];
            }

            $parentCrud = $this->findAndGroupPermissionsForMenu($parent, $allPermissions, $roles, $rolePermAssignments);
            foreach ($parentCrud['all_matched_ids'] as $pId) {
                $mappedPermissionIds[] = $pId;
            }

            $parentRoleAccess = [];
            foreach ($roles as $role) {
                $rId = (int) $role->id;
                $isSuper = ($role->slug === 'super_admin');
                $hasMenu = $isSuper || isset($roleMenuAssignments[$rId][(int) $parent->id]);

                $parentRoleAccess[$rId] = [
                    'menu_access' => $hasMenu,
                    'is_super_admin' => $isSuper,
                ];
            }

            $tree[] = [
                'id' => (int) $parent->id,
                'name' => $parent->name,
                'module_key' => $parent->module_key,
                'role_access' => $parentRoleAccess,
                'crud' => $parentCrud['grouped'],
                'permissions_list' => $parentCrud['list'],
                'submenus' => $submenusList,
            ];
        }

        // Collect remaining unmapped permissions into additional categories
        $unmapped = $allPermissions->reject(fn ($p) => in_array((int) $p->id, $mappedPermissionIds, true));
        $unmappedGrouped = $unmapped->groupBy('module');

        foreach ($unmappedGrouped as $modName => $perms) {
            $unmappedList = [];
            $unmappedCrud = ['view' => [], 'create' => [], 'edit' => [], 'delete' => [], 'manage' => []];

            foreach ($perms as $perm) {
                $crudType = $this->categorizeAction((string) $perm->action, (string) $perm->key);
                $permRoleAccess = [];

                foreach ($roles as $role) {
                    $rId = (int) $role->id;
                    $isSuper = ($role->slug === 'super_admin');
                    $isGranted = $isSuper || isset($rolePermAssignments[$rId][(int) $perm->id]);

                    $permRoleAccess[$rId] = [
                        'granted' => $isGranted,
                        'is_super_admin' => $isSuper,
                    ];
                }

                $item = [
                    'id' => (int) $perm->id,
                    'key' => $perm->key,
                    'action' => $perm->action,
                    'module' => $perm->module,
                    'submodule' => $perm->submodule,
                    'description' => $perm->description ?: $perm->key,
                    'crud_type' => $crudType,
                    'role_access' => $permRoleAccess,
                ];

                $unmappedList[] = $item;
                $unmappedCrud[$crudType][] = $item;
            }

            $fakeParentRoleAccess = [];
            foreach ($roles as $role) {
                $fakeParentRoleAccess[(int) $role->id] = [
                    'menu_access' => ($role->slug === 'super_admin' || $role->slug === 'admin' || $role->slug === 'hr_admin'),
                    'is_super_admin' => ($role->slug === 'super_admin'),
                ];
            }

            $tree[] = [
                'id' => 90000 + abs(crc32($modName) % 10000),
                'name' => ucwords(str_replace(['_', '-'], ' ', (string) $modName)) . ' (System Permissions)',
                'module_key' => $modName,
                'is_system_group' => true,
                'role_access' => $fakeParentRoleAccess,
                'crud' => $unmappedCrud,
                'permissions_list' => $unmappedList,
                'submenus' => [],
            ];
        }

        return $tree;
    }

    /**
     * Group matched permissions for a menu into View/Create/Edit/Delete/Manage with per-role access flags.
     */
    private function findAndGroupPermissionsForMenu(object $menu, Collection $allPermissions, Collection $roles, array $rolePermAssignments): array
    {
        $relevant = $this->findPermissionsForMenu($menu, $allPermissions);
        $matchedIds = [];
        $list = [];

        $grouped = [
            'view' => [],
            'create' => [],
            'edit' => [],
            'delete' => [],
            'manage' => [],
        ];

        foreach ($relevant as $perm) {
            $pId = (int) $perm->id;
            $matchedIds[] = $pId;

            $action = (string) ($perm->action ?? '');
            $key = (string) ($perm->key ?? '');
            $crudType = $this->categorizeAction($action, $key);

            $permRoleAccess = [];
            foreach ($roles as $role) {
                $rId = (int) $role->id;
                $isSuper = ($role->slug === 'super_admin');
                $isGranted = $isSuper || isset($rolePermAssignments[$rId][$pId]);

                $permRoleAccess[$rId] = [
                    'granted' => $isGranted,
                    'is_super_admin' => $isSuper,
                ];
            }

            $item = [
                'id' => $pId,
                'key' => $perm->key,
                'action' => $perm->action,
                'module' => $perm->module,
                'submodule' => $perm->submodule,
                'description' => $perm->description ?: $perm->key,
                'crud_type' => $crudType,
                'role_access' => $permRoleAccess,
            ];

            $list[] = $item;
            $grouped[$crudType][] = $item;
        }

        return [
            'all_matched_ids' => $matchedIds,
            'list' => $list,
            'grouped' => $grouped,
        ];
    }

    /**
     * Compute statistics, percentage coverage, and CRUD distribution per role.
     */
    private function computeRoleMetrics(Collection $roles, Collection $permissions, Collection $menus, array $roleMenuAssignments, array $rolePermAssignments): array
    {
        $totalPermsCount = $permissions->count();
        $totalMenusCount = $menus->count();
        $metrics = [];

        foreach ($roles as $role) {
            $rId = (int) $role->id;
            $isSuper = ($role->slug === 'super_admin');

            $assignedPermCount = $isSuper ? $totalPermsCount : count($rolePermAssignments[$rId] ?? []);
            $assignedMenuCount = $isSuper ? $totalMenusCount : count($roleMenuAssignments[$rId] ?? []);

            $coveragePct = $totalPermsCount > 0 ? round(($assignedPermCount / $totalPermsCount) * 100, 1) : 0;
            $menuCoveragePct = $totalMenusCount > 0 ? round(($assignedMenuCount / $totalMenusCount) * 100, 1) : 0;

            // Compute CRUD action counts
            $crudCounts = [
                'view' => 0,
                'create' => 0,
                'edit' => 0,
                'delete' => 0,
                'manage' => 0,
            ];

            foreach ($permissions as $p) {
                $isGranted = $isSuper || isset($rolePermAssignments[$rId][(int) $p->id]);
                if ($isGranted) {
                    $type = $this->categorizeAction((string) $p->action, (string) $p->key);
                    $crudCounts[$type]++;
                }
            }

            // User count for this role
            $userCount = DB::table('users')
                ->where('is_active', 1)
                ->where(function ($q) use ($rId) {
                    $q->where('system_role_id', $rId)
                        ->orWhereExists(function ($sub) use ($rId) {
                            $sub->select(DB::raw(1))
                                ->from('user_roles')
                                ->whereColumn('user_roles.user_id', 'users.id')
                                ->where('user_roles.role_id', $rId);
                        });
                })
                ->count();

            $metrics[$rId] = [
                'role_id' => $rId,
                'name' => $role->name,
                'slug' => $role->slug,
                'is_system' => (bool) $role->is_system,
                'is_super_admin' => $isSuper,
                'user_count' => $userCount,
                'assigned_permissions_count' => $assignedPermCount,
                'assigned_menus_count' => $assignedMenuCount,
                'permission_coverage_pct' => $coveragePct,
                'menu_coverage_pct' => $menuCoveragePct,
                'crud_counts' => $crudCounts,
            ];
        }

        return $metrics;
    }

    /**
     * Categorize action string and permission key into standardized CRUD types.
     */
    public function categorizeAction(string $action, string $key): string
    {
        $action = strtolower(trim($action));
        $key = strtolower(trim($key));

        if (str_contains($action, 'manage') || str_ends_with($key, '.manage') || str_contains($key, '.manage.')) {
            return 'manage';
        }

        foreach (self::CRUD_ACTIONS as $crudType => $tokens) {
            foreach ($tokens as $t) {
                if (str_contains($action, $t) || str_ends_with($key, '.' . $t) || str_contains($key, '.' . $t . '.')) {
                    return $crudType;
                }
            }
        }

        return 'view';
    }

    /**
     * Helper to match permission items to a menu item based on permission_key, module_key, or route tokens.
     */
    private function findPermissionsForMenu(object $menu, Collection $allPermissions): Collection
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

    /**
     * Simulate and trace authorization decision for a Role or User against a Permission Key or Route.
     */
    public function simulateAccess(string $targetType, int $targetId, string $resourceType, string $resourceKey): array
    {
        $steps = [];
        $isAllowed = false;
        $decisionReason = '';
        $matchedPermission = null;

        $resourceKey = trim($resourceKey);

        if ($resourceType === 'permission') {
            $matchedPermission = DB::table('permissions')->where('key', $resourceKey)->first();
        }

        if ($targetType === 'role') {
            $role = DB::table('roles')->where('id', $targetId)->first();
            if (! $role) {
                return [
                    'allowed' => false,
                    'verdict' => 'DENIED',
                    'reason' => 'Target role not found.',
                    'steps' => [],
                ];
            }

            $steps[] = [
                'stage' => 'Role Identity Check',
                'description' => "Target evaluated: Role '{$role->name}' (slug: {$role->slug})",
                'status' => 'info',
            ];

            // 1. Super Admin Check
            if ($role->slug === 'super_admin') {
                $steps[] = [
                    'stage' => 'Super Administrator Bypass',
                    'description' => 'Role is Super Admin: Full unconditional system access granted.',
                    'status' => 'granted',
                ];

                return [
                    'allowed' => true,
                    'verdict' => 'ALLOWED',
                    'reason' => 'Super Admin has unconditional full access to all system modules and actions.',
                    'steps' => $steps,
                ];
            }

            // 2. Role Permission / Menu Check
            if ($resourceType === 'permission') {
                if ($matchedPermission) {
                    $hasRolePerm = DB::table('role_permissions')
                        ->where('role_id', $role->id)
                        ->where('permission_id', $matchedPermission->id)
                        ->exists();

                    if ($hasRolePerm) {
                        $steps[] = [
                            'stage' => 'Role Permission Mapping',
                            'description' => "Permission '{$resourceKey}' is explicitly mapped to Role '{$role->name}'.",
                            'status' => 'granted',
                        ];

                        return [
                            'allowed' => true,
                            'verdict' => 'ALLOWED',
                            'reason' => "Role '{$role->name}' has been assigned permission '{$resourceKey}'.",
                            'steps' => $steps,
                        ];
                    } else {
                        $steps[] = [
                            'stage' => 'Role Permission Mapping',
                            'description' => "Permission '{$resourceKey}' is NOT mapped to Role '{$role->name}'.",
                            'status' => 'denied',
                        ];
                    }
                } else {
                    $steps[] = [
                        'stage' => 'Permission Existence',
                        'description' => "Permission key '{$resourceKey}' does not exist in permissions registry.",
                        'status' => 'denied',
                    ];
                }
            } elseif ($resourceType === 'menu') {
                $menu = DB::table('menus')->where('id', (int) $resourceKey)->orWhere('route', $resourceKey)->first();
                if ($menu) {
                    $hasMenu = DB::table('role_menu_access')
                        ->where('role_id', $role->id)
                        ->where('menu_id', $menu->id)
                        ->exists();

                    if ($hasMenu) {
                        $steps[] = [
                            'stage' => 'Role Menu / Page Visibility',
                            'description' => "Menu '{$menu->name}' ({$menu->route}) is visible for Role '{$role->name}'.",
                            'status' => 'granted',
                        ];

                        return [
                            'allowed' => true,
                            'verdict' => 'ALLOWED',
                            'reason' => "Page '{$menu->name}' is enabled in sidebar for Role '{$role->name}'.",
                            'steps' => $steps,
                        ];
                    } else {
                        $steps[] = [
                            'stage' => 'Role Menu / Page Visibility',
                            'description' => "Menu '{$menu->name}' is NOT assigned to Role '{$role->name}'.",
                            'status' => 'denied',
                        ];
                    }
                }
            }

            return [
                'allowed' => false,
                'verdict' => 'DENIED',
                'reason' => "Role '{$role->name}' does not possess required privileges for '{$resourceKey}'.",
                'steps' => $steps,
            ];
        }

        // USER SIMULATION
        $user = UserM::with(['primaryRole', 'roles', 'employee'])->find($targetId);
        if (! $user) {
            return [
                'allowed' => false,
                'verdict' => 'DENIED',
                'reason' => 'Target user not found.',
                'steps' => [],
            ];
        }

        $steps[] = [
            'stage' => 'User Profile & Identity',
            'description' => "User: {$user->name} ({$user->email}) | Primary Role: " . ($user->primaryRole->name ?? 'None') . " | Active: " . ($user->is_active ? 'Yes' : 'No') . " | Web Access: " . ($user->is_web_access ? 'Yes' : 'No'),
            'status' => 'info',
        ];

        // 1. Super Admin Check
        if ($user->isSuperAdmin()) {
            $steps[] = [
                'stage' => 'Super Admin Bypass',
                'description' => 'User holds Super Admin role: Full access granted.',
                'status' => 'granted',
            ];

            return [
                'allowed' => true,
                'verdict' => 'ALLOWED',
                'reason' => 'User is Super Administrator.',
                'steps' => $steps,
            ];
        }

        // 2. Web Access Check
        if (! $user->is_active || ! $user->is_web_access) {
            $steps[] = [
                'stage' => 'Account Status Check',
                'description' => 'User account is either inactive or does not have web access enabled.',
                'status' => 'denied',
            ];

            return [
                'allowed' => false,
                'verdict' => 'DENIED',
                'reason' => 'User account is inactive or disabled for web access.',
                'steps' => $steps,
            ];
        }

        // 3. User Override Check
        if (Schema::hasTable('user_module_access')) {
            $override = DB::table('user_module_access')
                ->where('user_id', $user->id)
                ->where('permission_key', $resourceKey)
                ->first(['is_allowed', 'is_enabled']);

            if ($override) {
                $overrideAllowed = (bool) ($override->is_allowed ?? $override->is_enabled);
                $steps[] = [
                    'stage' => 'Direct User Override (user_module_access)',
                    'description' => $overrideAllowed ? 'Explicit User-Level GRANT rule active.' : 'Explicit User-Level REVOKE rule active.',
                    'status' => $overrideAllowed ? 'granted' : 'denied',
                ];

                return [
                    'allowed' => $overrideAllowed,
                    'verdict' => $overrideAllowed ? 'ALLOWED' : 'DENIED',
                    'reason' => $overrideAllowed ? 'Explicit User-Level GRANT override applied.' : 'Explicit User-Level REVOKE override applied.',
                    'steps' => $steps,
                ];
            }
        }

        // 4. Role Level Permission Check
        $roleIds = [];
        if ($user->system_role_id) {
            $roleIds[] = (int) $user->system_role_id;
        }
        if (Schema::hasTable('user_roles')) {
            $roleIds = array_merge($roleIds, DB::table('user_roles')->where('user_id', $user->id)->pluck('role_id')->map(fn ($id) => (int) $id)->all());
        }
        $roleIds = array_unique(array_filter($roleIds));

        $hasRolePerm = false;
        if (! empty($roleIds) && $matchedPermission) {
            $hasRolePerm = DB::table('role_permissions')
                ->whereIn('role_id', $roleIds)
                ->where('permission_id', $matchedPermission->id)
                ->exists();
        }

        if ($hasRolePerm) {
            $steps[] = [
                'stage' => 'Role-Level Authorization',
                'description' => "Permission '{$resourceKey}' granted via user's assigned roles (" . implode(', ', $roleIds) . ").",
                'status' => 'granted',
            ];

            return [
                'allowed' => true,
                'verdict' => 'ALLOWED',
                'reason' => "Granted via assigned system role(s).",
                'steps' => $steps,
            ];
        } else {
            $steps[] = [
                'stage' => 'Role-Level Authorization',
                'description' => "Permission '{$resourceKey}' is not granted by user's assigned roles.",
                'status' => 'denied',
            ];
        }

        // 5. Position / Designation Check
        $employee = Schema::hasTable('employees_new') ? DB::table('employees_new')->where('user_id', $user->id)->first(['designation_id', 'department_id']) : null;

        if ($employee && ! empty($employee->designation_id) && Schema::hasTable('designation_module_access')) {
            $hasPosPerm = DB::table('designation_module_access')
                ->where('designation_id', $employee->designation_id)
                ->where('permission_key', $resourceKey)
                ->where(function ($q) {
                    $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
                })
                ->exists();

            if ($hasPosPerm) {
                $steps[] = [
                    'stage' => 'Position / Designation Permission',
                    'description' => "Permission '{$resourceKey}' granted by employee's Designation (ID: {$employee->designation_id}).",
                    'status' => 'granted',
                ];

                return [
                    'allowed' => true,
                    'verdict' => 'ALLOWED',
                    'reason' => 'Granted via Position / Designation policy.',
                    'steps' => $steps,
                ];
            }
        }

        // 6. Department / Profile Check
        if ($employee && ! empty($employee->department_id) && Schema::hasTable('department_module_access')) {
            $hasDeptPerm = DB::table('department_module_access')
                ->where('department_id', $employee->department_id)
                ->where('permission_key', $resourceKey)
                ->where(function ($q) {
                    $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
                })
                ->exists();

            if ($hasDeptPerm) {
                $steps[] = [
                    'stage' => 'Department / Profile Baseline',
                    'description' => "Permission '{$resourceKey}' granted by employee's Department (ID: {$employee->department_id}).",
                    'status' => 'granted',
                ];

                return [
                    'allowed' => true,
                    'verdict' => 'ALLOWED',
                    'reason' => 'Granted via Department baseline policy.',
                    'steps' => $steps,
                ];
            }
        }

        $steps[] = [
            'stage' => 'Final Authorization Resolution',
            'description' => 'No role, position, department, or user rule permits access to this resource.',
            'status' => 'denied',
        ];

        return [
            'allowed' => false,
            'verdict' => 'DENIED',
            'reason' => 'No active authorization rule grants access to this action.',
            'steps' => $steps,
        ];
    }

    /**
     * Generate CSV content for exporting the complete RBAC Matrix.
     */
    public function generateCsvData(): string
    {
        $data = $this->getVisualizerData();
        $roles = $data['roles'];
        $modulesTree = $data['modulesTree'];

        $output = fopen('php://temp', 'r+');

        // CSV Header
        $header = ['Module', 'Page / Submenu', 'Route Name', 'CRUD Action', 'Permission Key', 'Description'];
        foreach ($roles as $r) {
            $header[] = $r->name . ' (' . $r->slug . ')';
        }
        fputcsv($output, $header);

        // Process module tree
        foreach ($modulesTree as $module) {
            $modName = $module['name'];

            // Module root permissions
            foreach ($module['permissions_list'] as $p) {
                $row = [
                    $modName,
                    '-- [Module Root] --',
                    '',
                    strtoupper($p['crud_type']),
                    $p['key'],
                    $p['description'],
                ];
                foreach ($roles as $r) {
                    $granted = $p['role_access'][(int) $r->id]['granted'] ?? false;
                    $row[] = $granted ? 'GRANTED' : 'DENIED';
                }
                fputcsv($output, $row);
            }

            // Submenus
            foreach ($module['submenus'] as $sub) {
                $subName = $sub['name'];
                $route = $sub['route'] ?? '';

                // Page visibility row
                $pageRow = [
                    $modName,
                    $subName . ' (Page Access)',
                    $route,
                    'VIEW PAGE',
                    $sub['permission_key'] ?: 'menu.access',
                    'Sidebar & Page Access',
                ];
                foreach ($roles as $r) {
                    $menuAccess = $sub['role_access'][(int) $r->id]['menu_access'] ?? false;
                    $pageRow[] = $menuAccess ? 'VISIBLE' : 'HIDDEN';
                }
                fputcsv($output, $pageRow);

                // CRUD permissions under submenu
                foreach ($sub['permissions_list'] as $p) {
                    $row = [
                        $modName,
                        $subName,
                        $route,
                        strtoupper($p['crud_type']),
                        $p['key'],
                        $p['description'],
                    ];
                    foreach ($roles as $r) {
                        $granted = $p['role_access'][(int) $r->id]['granted'] ?? false;
                        $row[] = $granted ? 'GRANTED' : 'DENIED';
                    }
                    fputcsv($output, $row);
                }
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
