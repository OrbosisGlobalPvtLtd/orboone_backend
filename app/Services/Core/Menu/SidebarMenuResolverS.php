<?php

namespace App\Services\Core\Menu;

use App\Services\AccessControl\PermissionMapS;
use App\Services\HRMS\Team\TeamManagementScopeS;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SidebarMenuResolverS
{
    public function __construct(
        protected ?TeamManagementScopeS $teamScope = null
    ) {
        $this->teamScope = $teamScope ?? app(TeamManagementScopeS::class);
    }
    public function resolveForUser(?Authenticatable $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $ttl = (int) config('authorization.sidebar_cache_ttl', 3600);
        return Cache::remember($this->cacheKey((int) $user->id), $ttl, function () use ($user) {
            $menus = $this->loadBaseMenus();
            if ($menus->isEmpty()) {
                return collect();
            }

            $roleIds = $this->resolveRoleIds($user);
            $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
                || (method_exists($user, 'hasRole') && $user->hasRole('super_admin'));

            $userRoles = ! empty($roleIds) ? DB::table('roles')->whereIn('id', $roleIds)->get(['id', 'slug', 'is_system']) : collect();
            $isOnlyEmployee = $userRoles->isNotEmpty() && $userRoles->every(fn ($r) => $r->slug === 'employee');
            $hasAdminRole = $isSuperAdmin || ! $isOnlyEmployee;

            $userEmp = Schema::hasTable('employees_new')
                ? DB::table('employees_new')->where('user_id', $user->id)->first(['id', 'work_mode', 'department_id', 'designation_id'])
                : null;
            $hasEmployeeRecord = $userEmp !== null;
            $hasEmployeeRole = $hasEmployeeRecord;

            $hrSlugs = config('authorization.hr_admin_slugs', ['super_admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources']);
            $isHrAdmin = $isSuperAdmin || ($userRoles->isNotEmpty() && $userRoles->contains(fn ($r) => in_array(strtolower((string) $r->slug), $hrSlugs, true))) || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin());

            // Pre-compute in-memory permission checker to eliminate N+1 database queries during filtering
            $permissionChecker = $this->buildUserPermissionChecker($user, $userEmp, $roleIds, $isSuperAdmin, $isHrAdmin);

            // Compute user management/work context once per resolution pass
            $userContext = $this->buildUserContext($user, $userEmp, $roleIds, $isSuperAdmin, $userRoles, $menus, $permissionChecker);

            $employeeMenus = collect();
            $adminMenus = collect();

            if ($hasEmployeeRole) {
                $employeeMenus = $this->resolveForContext($menus, $user, $roleIds, $isSuperAdmin, true, $userContext, $permissionChecker, $userRoles);
            }

            if ($hasAdminRole) {
                $adminMenus = $this->resolveForContext($menus, $user, $roleIds, $isSuperAdmin, false, $userContext, $permissionChecker, $userRoles);
            }

            $merged = $employeeMenus->concat($adminMenus)->unique('id');

            // If user has NO corresponding employee record in employees_new, strictly hide all Employee Self-Service menus
            if (! $hasEmployeeRecord) {
                $merged = $merged->reject(fn ($m) => $this->isEmployeeOnlyMenu($m));
            }

            // Post-merge repair and normalization pipeline
            $merged = $this->repairParentVisibility($merged, $user, $roleIds, $isSuperAdmin, $permissionChecker);
            $merged = $this->deduplicateMenus($merged);
            $merged = $this->filterByRouteValidity($merged);
            $merged = $this->removeEmptyParents($merged);

            return $merged
                ->sortBy([
                    ['parent_id', 'asc'],
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values()
                ->groupBy('parent_id');
        });
    }

    private function buildUserContext(Authenticatable $user, ?object $userEmp, array $roleIds, bool $isSuperAdmin, Collection $userRoles, Collection $menus, callable $permissionChecker): array
    {
        $empId = null;
        $workMode = 'wfo';
        if ($userEmp) {
            $empId = (int) $userEmp->id;
            $workMode = strtolower((string) ($userEmp->work_mode ?? 'wfo'));
        }

        $isPermanentWfh = in_array($workMode, ['wfh', 'permanent_wfh', 'permanent wfh'], true);

        $isTeamManager = false;
        $isProjectManager = false;

        if ($empId) {
            $teamIds = $this->teamScope->getTeamEmployeeIds($empId);
            $isTeamManager = ! empty($teamIds);

            if ($isTeamManager) {
                $isProjectManager = true;
            } else {
                $isTeamLead = DB::table('project_teams')->where('team_lead_employee_id', $empId)->where('is_active', 1)->exists();
                $isDeliveryHead = DB::table('projects')->where('delivery_head_employee_id', $empId)->exists();
                $isProjectLead = DB::table('project_assignments')
                    ->where('employee_id', $empId)
                    ->where('is_active', 1)
                    ->where(function ($q) {
                        $q->whereIn(DB::raw('LOWER(project_role)'), [
                            'team_lead', 'team lead',
                            'project_lead', 'project lead',
                            'project_manager', 'project manager',
                            'lead', 'manager',
                            'delivery_head', 'delivery head',
                        ]);
                    })->exists();

                $isProjectManager = $isTeamLead || $isDeliveryHead || $isProjectLead;
            }
        }

        if (! $isProjectManager) {
            $hasRoleMenuAccess = false;
            if (! empty($roleIds) && Schema::hasTable('role_menu_access')) {
                $projectMenuIds = $menus->where('module_key', 'project_management')->pluck('id')->all();

                if (! empty($projectMenuIds)) {
                    $hasRoleMenuAccess = DB::table('role_menu_access')
                        ->whereIn('role_id', $roleIds)
                        ->whereIn('menu_id', $projectMenuIds)
                        ->exists();
                }
            }

            $hasManagerRole = $userRoles->contains(fn ($r) => in_array(strtolower((string) $r->slug), ['super_admin', 'admin', 'hr_admin', 'project_admin', 'operations_admin', 'custom_admin'], true));

            $hasProjectPerm = $permissionChecker('projects.view_all') || $permissionChecker('projects.manage');

            if ($isSuperAdmin || $hasRoleMenuAccess || $hasManagerRole || $hasProjectPerm) {
                $isProjectManager = true;
            }
        }

        return [
            'emp_id' => $empId,
            'is_permanent_wfh' => $isPermanentWfh,
            'is_team_manager' => $isTeamManager,
            'is_project_manager' => $isProjectManager,
        ];
    }

    private function resolveForContext(Collection $menus, Authenticatable $user, array $roleIds, bool $isSuperAdmin, bool $isEmployeeContext, array $userContext, callable $permissionChecker, Collection $userRoles): Collection
    {
        $filtered = $this->filterByRoleMenuAccess($menus, $user, $roleIds, $isSuperAdmin);
        $filtered = $this->filterByPermission($filtered, $user, $isSuperAdmin, $permissionChecker);
        $filtered = $this->filterReportingManagementVisibility($filtered, $user, $roleIds, $isSuperAdmin, $userContext, $userRoles);
        $filtered = $this->filterByEmployeeOnlyVisibility($filtered, $isEmployeeContext, $userContext['is_project_manager']);
        $filtered = $this->filterByPermanentWfhVisibility($filtered, $userContext['is_permanent_wfh']);
        $filtered = $this->filterByRouteValidity($filtered);

        return $filtered;
    }

    private function filterByPermanentWfhVisibility(Collection $menus, bool $isPermanentWfh): Collection
    {
        if (! $isPermanentWfh) {
            return $menus;
        }

        return $menus->reject(function ($menu) {
            $r = strtolower((string) ($menu->route ?? ''));
            $n = strtolower((string) ($menu->name ?? ''));

            return in_array($r, ['hrms.attendance.my-wfh.index', 'attendances.my-wfh', 'attendance.my-wfh'], true)
                || str_contains($n, 'my wfh');
        });
    }

    private function filterReportingManagementVisibility(Collection $menus, Authenticatable $user, array $roleIds, bool $isSuperAdmin, array $userContext, Collection $userRoles): Collection
    {
        $isTeamManager = $userContext['is_team_manager'];
        $isProjectManager = $userContext['is_project_manager'];
        $hasReportingAdminAccess = $isSuperAdmin;
        $hasReportingAdminMenus = $menus->contains(function ($menu) {
            $id = (int) ($menu->id ?? 0);
            $parentId = ! is_null($menu->parent_id) ? (int) $menu->parent_id : null;
            $route = strtolower(trim((string) ($menu->route ?? '')));

            return $id === 350 || $parentId === 350 || in_array($route, [
                'reporting.structure',
                'reporting.supervisors',
                'reporting.assignments',
                'reporting.history',
            ], true);
        });

        if (! $hasReportingAdminAccess && $hasReportingAdminMenus && ! empty($roleIds)) {
            $hasReportingAdminAccess = $userRoles->contains(fn ($r) => in_array(strtolower((string) $r->slug), ['super_admin', 'admin', 'hr_admin'], true));
        }

        return $menus->map(function ($m) use ($isProjectManager) {
            $route = strtolower(trim((string) ($m->route ?? '')));

            // If user is a project lead/manager, point Tasks menu to the comprehensive project tasks view
            if ($isProjectManager && ($route === 'project_management.tasks.index' || $route === 'projects.tasks.index')) {
                $clone = clone $m;
                $clone->route = 'projects.tasks.index';

                return $clone;
            }

            return $m;
        })->reject(function ($menu) use ($isTeamManager, $isProjectManager, $isSuperAdmin, $hasReportingAdminAccess) {
            $route = strtolower(trim((string) ($menu->route ?? '')));
            $moduleKey = strtolower(trim((string) ($menu->module_key ?? '')));
            $id = (int) ($menu->id ?? 0);
            $parentId = ! is_null($menu->parent_id) ? (int) $menu->parent_id : null;

            // 1. Inactive items are hidden
            if (isset($menu->is_active) && (int) $menu->is_active === 0) {
                return true;
            }

            // 2. Team Management container (ID 370) and operational submenus:
            // Strictly visible ONLY if user is an actual reporting manager (manages a team with reportees) or Super Admin
            $name = strtolower(trim((string) ($menu->name ?? '')));
            $isTeamMenu = $id === 370 || $parentId === 370 || str_starts_with($route, 'team.') || in_array($route, [
                'attendances.team',
                'reporting.dashboard',
                'reporting.my_employees',
                'reporting.attendance',
                'reporting.leave',
                'reporting.work_reports',
                'reporting.projects',
            ], true) || (str_contains($name, 'team') && $moduleKey === 'reporting');
            if ($isTeamMenu && ! $isTeamManager && ! $isSuperAdmin) {
                return true;
            }

            // 3. Project Management lead/management menus:
            // If user is NOT a project manager/lead, reject project management lead menus
            $isProjectLeadMenu = $moduleKey === 'project_management' && ! in_array($route, ['projects.my', 'projects.tasks.index'], true);
            if ($isProjectLeadMenu && ! $isProjectManager && ! $isSuperAdmin) {
                return true;
            }

            // 4. Reporting Management admin container (ID 350) and configuration submenus:
            // Restricted to Super Admin, Admin, and HR Admin roles.
            $isReportingAdminMenu = $id === 350 || $parentId === 350 || in_array($route, [
                'reporting.structure',
                'reporting.supervisors',
                'reporting.assignments',
                'reporting.history',
            ], true) || ($moduleKey === 'reporting' && ! $isTeamMenu);
            if ($isReportingAdminMenu && ! $hasReportingAdminAccess) {
                return true;
            }

            return false;
        })->values();
    }

    private function loadBaseMenus(): Collection
    {
        if (! Schema::hasTable('menus')) {
            return collect();
        }

        $base = DB::table('menus')
            ->where('is_active', 1)
            ->select([
                'id',
                'name',
                'route',
                'icon',
                'module_key',
                'permission_key',
                'parent_id',
                'sort_order',
                'is_active',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $mapped = $base->map(function ($menu) {
            $m = clone $menu;
            $m->id = (int) $m->id;
            $m->parent_id = ! is_null($m->parent_id) ? (int) $m->parent_id : null;
            $m->sort_order = (int) $m->sort_order;
            $m->is_active = (bool) $m->is_active;

            return $m;
        });

        return $mapped;
    }

    private function resolveRoleIds(Authenticatable $user): array
    {
        $roleIds = [];

        if (! empty($user->role_id)) {
            $roleIds[] = (int) $user->role_id;
        }

        if (! empty($user->system_role_id)) {
            $roleIds[] = (int) $user->system_role_id;
        }

        if (method_exists($user, 'roles')) {
            $roleIds = array_merge(
                $roleIds,
                $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->all()
            );
        }

        return array_values(array_unique(array_filter($roleIds)));
    }

    private function filterByRoleMenuAccess(Collection $menus, Authenticatable $user, array $roleIds, bool $isSuperAdmin): Collection
    {
        if (empty($roleIds) || ! Schema::hasTable('role_menu_access')) {
            return $isSuperAdmin ? $menus : collect();
        }

        if ($isSuperAdmin) {
            $superAdminRoleId = DB::table('roles')->where('slug', 'super_admin')->value('id');
            $hasExplicitRoleMenus = $superAdminRoleId ? DB::table('role_menu_access')->where('role_id', $superAdminRoleId)->exists() : false;

            if (! $hasExplicitRoleMenus) {
                return $menus;
            }
        }

        $allowedIds = DB::table('role_menu_access')
            ->whereIn('role_id', $roleIds)
            ->pluck('menu_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($allowedIds)) {
            return $isSuperAdmin ? $menus : collect();
        }

        return $menus->whereIn('id', $allowedIds)->values();
    }

    private function buildUserPermissionChecker(Authenticatable $user, ?object $userEmp, array $roleIds, bool $isSuperAdmin, bool $isHrAdmin): callable
    {
        if ($isSuperAdmin) {
            return fn (string $key) => true;
        }

        $userOverrides = [];
        if (Schema::hasTable('user_module_access')) {
            $userOverrides = DB::table('user_module_access')
                ->where('user_id', $user->id)
                ->whereNotNull('permission_key')
                ->get(['permission_key', 'is_allowed', 'is_enabled'])
                ->keyBy('permission_key')
                ->map(fn ($row) => (bool) ($row->is_allowed ?? $row->is_enabled))
                ->all();
        }

        $grantedKeys = [];
        if (! empty($roleIds) && Schema::hasTable('role_permissions') && Schema::hasTable('permissions')) {
            $rolePerms = DB::table('role_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->whereIn('role_permissions.role_id', $roleIds)
                ->pluck('permissions.key')
                ->all();

            foreach ($rolePerms as $k) {
                if ($k) {
                    $grantedKeys[$k] = true;
                }
            }
        }

        if ($userEmp) {
            if (! empty($userEmp->designation_id) && Schema::hasTable('designation_module_access')) {
                $desigPerms = DB::table('designation_module_access')
                    ->where('designation_id', $userEmp->designation_id)
                    ->where(function ($q) {
                        $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
                    })
                    ->pluck('permission_key')
                    ->all();

                foreach ($desigPerms as $k) {
                    if ($k) {
                        $grantedKeys[$k] = true;
                    }
                }
            }

            if (! empty($userEmp->department_id) && Schema::hasTable('department_module_access')) {
                $deptPerms = DB::table('department_module_access')
                    ->where('department_id', $userEmp->department_id)
                    ->where(function ($q) {
                        $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
                    })
                    ->pluck('permission_key')
                    ->all();

                foreach ($deptPerms as $k) {
                    if ($k) {
                        $grantedKeys[$k] = true;
                    }
                }
            }
        }

        $hrAdminPrefixes = PermissionMapS::getHrAdminPrefixes();
        $permissionAliases = PermissionMapS::getPermissionAliases();
        $attendanceExpansions = PermissionMapS::getAttendanceAliasExpansions();

        $checkSingleKey = function (string $key) use ($userOverrides, $grantedKeys, $isHrAdmin, $hrAdminPrefixes, $permissionAliases, $attendanceExpansions): bool {
            if ($key === '') {
                return false;
            }

            if ($isHrAdmin) {
                foreach ($hrAdminPrefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        return true;
                    }
                }
            }

            if (isset($permissionAliases[$key])) {
                $key = $permissionAliases[$key];
            }

            if (array_key_exists($key, $userOverrides)) {
                return $userOverrides[$key];
            }

            if (isset($attendanceExpansions[$key])) {
                foreach ($attendanceExpansions[$key] as $expandedKey) {
                    if (! empty($grantedKeys[$expandedKey])) {
                        return true;
                    }
                }
            }

            return ! empty($grantedKeys[$key]);
        };

        return function (string $permissionKey) use ($checkSingleKey): bool {
            if (str_contains($permissionKey, '|')) {
                $keys = array_filter(array_map('trim', explode('|', $permissionKey)));
                foreach ($keys as $k) {
                    if ($checkSingleKey($k)) {
                        return true;
                    }
                }
                return false;
            }

            return $checkSingleKey($permissionKey);
        };
    }

    private function filterByPermission(Collection $menus, Authenticatable $user, bool $isSuperAdmin, callable $permissionChecker): Collection
    {
        if ($isSuperAdmin) {
            return $menus;
        }

        $menuPermissionMap = $this->menuPermissionMap();

        return $menus->filter(function ($menu) use ($menuPermissionMap, $permissionChecker) {
            $permKey = (string) ($menu->permission_key ?? '');
            if ($permKey !== '') {
                if ($permissionChecker($permKey)) {
                    return true;
                }
            }

            $route = (string) ($menu->route ?? '');
            if ($route === '' || ! isset($menuPermissionMap[$route])) {
                return $permKey === '' || $permissionChecker($permKey);
            }

            if ($route === 'projects.my' || $route === 'projects.tasks.index' || $route === 'projects.index' || $route === 'employee.announcements.index') {
                return true;
            }

            foreach ($menuPermissionMap[$route] as $permissionKey) {
                if ($permissionChecker($permissionKey)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    private function filterByEmployeeOnlyVisibility(Collection $menus, bool $isEmployeeContext, bool $isProjectManager = false): Collection
    {
        return $menus->filter(function ($menu) use ($isEmployeeContext, $isProjectManager) {
            $route = strtolower(trim((string) ($menu->route ?? '')));
            $moduleKey = strtolower(trim((string) ($menu->module_key ?? '')));

            // Dashboard is always visible to everyone
            if ($route === 'dashboard' || strtolower(trim((string) ($menu->name ?? ''))) === 'dashboard') {
                return true;
            }

            // Always allow Reporting Management and Team Management submenus in employee context
            if ($moduleKey === 'reporting' || str_starts_with($route, 'reporting.') || str_starts_with($route, 'team.')) {
                return true;
            }

            $isEmployeeOnly = $this->isEmployeeOnlyMenu($menu);

            if ($isEmployeeContext) {
                $isProjectLeadMenu = $moduleKey === 'project_management' && ! in_array($route, ['projects.my', 'projects.tasks.index'], true);
                if ($isProjectLeadMenu) {
                    return $isProjectManager;
                }

                if ($route === 'projects.tasks.index') {
                    return true;
                }

                // Exclude admin-only attendance & HR management routes from Employee Self Service panel
                $adminOnlyRoutes = [
                    'projects.index',
                    'hrms.attendance.holiday_work.index',
                    'attendances.index',
                    'attendances.team',
                    'reporting.attendance',
                    'attendances.record',
                    'attendances.pending-approval',
                    'attendances.monthly-report',
                    'hrms.attendance.monthly_summary.index',
                    'hrms.attendance.work-reports',
                    'hrms.attendance.violations.index',
                    'attendance.policies.index',
                    'attendance.rules.index',
                    'attendances.access-control',
                    'attendance.types.index',
                    'hrms.attendance.policy_overrides.index',
                    'attendances.export-pdf',
                    'hrms.attendance.wfh.index',
                ];

                if (in_array($route, $adminOnlyRoutes, true)) {
                    return false;
                }

                if (in_array($route, [
                    'hrms.leave.dashboard',
                    'hrms.leave.history',
                    'leave-requests.create',
                    'leave-requests.index',
                    'hrms.leave.balances.index',
                    'employees-leave-request.summary',
                    'hrms.holidays.index',
                ], true)) {
                    return true;
                }

                return $isEmployeeOnly || $this->isEmployeeParentContainer($menu);
            }

            return ! $isEmployeeOnly;
        })->values();
    }

    private function filterByRouteValidity(Collection $menus): Collection
    {
        $valid = collect();

        foreach ($menus as $menu) {
            $route = (string) ($menu->route ?? '');
            if ($route === '' || $route === '#') {
                $valid->push($menu);
                continue;
            }

            $resolved = $this->resolveRouteName($route);

            $cloned = clone $menu;
            $cloned->route = $resolved !== null ? $resolved : '#';
            $valid->push($cloned);
        }

        return $valid->values();
    }


    private function repairParentVisibility(
        Collection $menus,
        Authenticatable $user,
        array $roleIds,
        bool $isSuperAdmin,
        callable $permissionChecker
    ): Collection {
        $indexed = $menus->keyBy('id');

        $allowedRoleMenuIds = [];
        if (! $isSuperAdmin && ! empty($roleIds) && Schema::hasTable('role_menu_access')) {
            $allowedRoleMenuIds = DB::table('role_menu_access')
                ->whereIn('role_id', $roleIds)
                ->pluck('menu_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $menuPermissionMap = $this->menuPermissionMap();

        while (true) {
            $missingParentIds = [];
            foreach ($indexed as $menu) {
                $parentId = (int) ($menu->parent_id ?? 0);
                if ($parentId > 0 && ! $indexed->has($parentId)) {
                    $missingParentIds[$parentId] = $parentId;
                }
            }

            if (empty($missingParentIds)) {
                break;
            }

            $parents = DB::table('menus')
                ->whereIn('id', array_values($missingParentIds))
                ->where('is_active', 1)
                ->get(['id', 'name', 'route', 'icon', 'module_key', 'permission_key', 'parent_id', 'sort_order', 'is_active']);

            if ($parents->isEmpty()) {
                break;
            }

            foreach ($parents as $parent) {
                $parent->id = (int) $parent->id;
                $parent->parent_id = ! is_null($parent->parent_id) ? (int) $parent->parent_id : null;
                $parent->sort_order = (int) $parent->sort_order;
                $parent->is_active = (bool) $parent->is_active;

                $isRoleAuthorized = $isSuperAdmin || in_array($parent->id, $allowedRoleMenuIds, true);

                $isPermAuthorized = $isSuperAdmin;
                if (! $isPermAuthorized) {
                    $permKey = (string) ($parent->permission_key ?? '');
                    if ($permKey !== '') {
                        $isPermAuthorized = $permissionChecker($permKey);
                    } else {
                        $route = (string) ($parent->route ?? '');
                        if ($route === '' || ! isset($menuPermissionMap[$route])) {
                            $isPermAuthorized = true;
                        } else {
                            foreach ($menuPermissionMap[$route] as $pk) {
                                if ($permissionChecker($pk)) {
                                    $isPermAuthorized = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($isRoleAuthorized && $isPermAuthorized) {
                    $parent->route = $this->resolveRouteName((string) ($parent->route ?? '')) ?? '#';
                } else {
                    // Restored strictly as a non-navigable structural container for authorized children.
                    // Strips any unauthorized route so it can NEVER be exposed as a clickable navigation item.
                    $parent->route = '#';
                }

                $indexed->put($parent->id, $parent);
            }
        }

        return $indexed->values();
    }

    private function deduplicateMenus(Collection $menus): Collection
    {
        $seenIds = [];
        $seenSignatures = [];

        // Prefer child entries to top-level duplicates while preserving stable database ordering.
        $sortedForDedup = $menus->sort(function ($a, $b) {
            $aParent = ! empty($a->parent_id) ? 1 : 0;
            $bParent = ! empty($b->parent_id) ? 1 : 0;
            if ($aParent !== $bParent) {
                return $bParent <=> $aParent;
            }

            return ((int) ($a->id ?? 0)) <=> ((int) ($b->id ?? 0));
        });

        $deduped = collect();

        foreach ($sortedForDedup as $menu) {
            $id = (int) ($menu->id ?? 0);
            if ($id > 0 && isset($seenIds[$id])) {
                continue;
            }

            $parentId = (int) ($menu->parent_id ?? 0);
            $route = strtolower(trim((string) ($menu->route ?? '')));
            $name = strtolower(trim((string) ($menu->name ?? '')));

            $signature = $parentId . '|' . $route . '|' . $name;

            if (isset($seenSignatures[$signature])) {
                continue;
            }

            if ($id > 0) {
                $seenIds[$id] = true;
            }
            $seenSignatures[$signature] = true;
            $deduped->push($menu);
        }

        return $deduped->values();
    }

    private function removeEmptyParents(Collection $menus): Collection
    {
        $idsWithChildren = $menus->pluck('parent_id')
            ->filter(fn ($id) => ! is_null($id))
            ->map(fn ($id) => (int) $id)
            ->all();

        return $menus->filter(function ($menu) use ($idsWithChildren) {
            $hasRoute = ! empty((string) ($menu->route ?? '')) && (string) ($menu->route ?? '') !== '#';
            if ($hasRoute) {
                return true;
            }

            return in_array((int) $menu->id, $idsWithChildren, true);
        })->values();
    }

    private function resolveRouteName(string $routeName): ?string
    {
        if ($routeName === '' || $routeName === '#') {
            return null;
        }

        if (Route::has($routeName)) {
            return $routeName;
        }

        $variants = [
            str_replace('-', '_', $routeName),
            str_replace('_', '-', $routeName),
        ];

        foreach ($variants as $variant) {
            if ($variant !== '' && Route::has($variant)) {
                return $variant;
            }
        }

        return null;
    }

    private function menuPermissionMap(): array
    {
        return PermissionMapS::getSidebarRoutePermissionMap();
    }

    private function isEmployeeOnlyMenu(object $menu): bool
    {
        return PermissionMapS::isEmployeeSelfServiceMenu($menu);
    }

    private function isEmployeeParentContainer(object $menu): bool
    {
        $moduleKey = strtolower(trim((string) ($menu->module_key ?? '')));
        $name = strtolower(trim((string) ($menu->name ?? '')));

        if ($moduleKey === 'my.profile' || $name === 'settings') {
            return true;
        }

        return in_array($moduleKey, ['documents', 'attendance', 'leave', 'enterprise_payroll', 'assets', 'project_management', 'announcements', 'notice'], true);
    }

    public function clearCache(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    private function cacheKey(int $userId): string
    {
        return config('authorization.sidebar_cache_prefix', 'user_menus_v2_') . $userId;
    }
}
