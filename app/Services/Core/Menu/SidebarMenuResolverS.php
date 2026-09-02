<?php

namespace App\Services\Core\Menu;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SidebarMenuResolverS
{
    public function resolveForUser(?Authenticatable $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return Cache::remember($this->cacheKey((int) $user->id), 3600, function () use ($user) {
            $menus = $this->loadBaseMenus();
            if ($menus->isEmpty()) {
                return collect();
            }

            $roleIds = $this->resolveRoleIds($user);
            $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
                || (method_exists($user, 'hasRole') && $user->hasRole('super_admin'));

            $userRoles = !empty($roleIds) ? DB::table('roles')->whereIn('id', $roleIds)->get(['id', 'slug', 'is_system']) : collect();
            $isOnlyEmployee = $userRoles->isNotEmpty() && $userRoles->every(fn ($r) => $r->slug === 'employee');
            $hasAdminRole = $isSuperAdmin || !$isOnlyEmployee;
            $hasEmployeeRole = $userRoles->isEmpty() || $userRoles->contains(fn ($r) => $r->slug === 'employee' || $r->slug === 'super_admin') || $hasAdminRole;

            $employeeMenus = collect();
            $adminMenus = collect();

            if ($hasEmployeeRole) {
                $employeeMenus = $this->resolveForContext($menus, $user, $roleIds, $isSuperAdmin, true);
            }

            if ($hasAdminRole) {
                $adminMenus = $this->resolveForContext($menus, $user, $roleIds, $isSuperAdmin, false);
            }

            $merged = $employeeMenus->concat($adminMenus)
                ->reject(function ($m) {
                    // Filter out legacy standalone top-level Projects menu (ID 91) when Project Management container exists
                    return (int) ($m->id ?? 0) === 91;
                })
                ->unique('id');
            $merged = $this->repairParentVisibility($merged);
            $merged = $this->deduplicateMenus($merged);
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

    private function resolveForContext(Collection $menus, Authenticatable $user, array $roleIds, bool $isSuperAdmin, bool $isEmployeeContext): Collection
    {
        $filtered = $this->filterByRoleMenuAccess($menus, $user, $roleIds, $isSuperAdmin);
        $filtered = $this->filterByPermission($filtered, $user, $isSuperAdmin);
        $filtered = $this->filterReportingManagementVisibility($filtered, $user, $roleIds, $isSuperAdmin);
        $isProjectManager = $this->checkIsProjectManager($user, $roleIds, $isSuperAdmin);
        $filtered = $this->filterByEmployeeOnlyVisibility($filtered, $isEmployeeContext, $isProjectManager);
        $filtered = $this->filterByWebAttendancePermission($filtered, $user, $isSuperAdmin);
        $filtered = $this->filterRetiredLegacyPayrollMenus($filtered);
        $filtered = $this->filterByPermanentWfhVisibility($filtered, $user);
        $filtered = $this->filterByRouteValidity($filtered);

        return $filtered;
    }

    private function filterByPermanentWfhVisibility(Collection $menus, Authenticatable $user): Collection
    {
        $emp = DB::table('employees_new')->where('user_id', $user->id)->first(['id', 'work_mode']);
        if (!$emp) {
            return $menus;
        }

        $workMode = strtolower((string)($emp->work_mode ?? 'wfo'));
        $isPermanentWfh = in_array($workMode, ['wfh', 'permanent_wfh', 'permanent wfh'], true);

        if (!$isPermanentWfh) {
            return $menus;
        }

        return $menus->reject(function ($menu) {
            $r = strtolower((string)($menu->route ?? ''));
            $n = strtolower((string)($menu->name ?? ''));
            return in_array($r, ['hrms.attendance.my-wfh.index', 'attendances.my-wfh', 'attendance.my-wfh'], true)
                || str_contains($n, 'my wfh');
        });
    }

    private function filterReportingManagementVisibility(Collection $menus, Authenticatable $user, array $roleIds, bool $isSuperAdmin): Collection
    {
        $empId = null;
        $userEmp = DB::table('employees_new')->where('user_id', $user->id)->first(['id']);
        if ($userEmp) {
            $empId = (int)$userEmp->id;
        }

        $isTeamManager = false;
        if ($empId) {
            $teamScope = app(\App\Services\HRMS\Team\TeamManagementScopeS::class);
            $teamIds = $teamScope->getTeamEmployeeIds($empId);
            $isTeamManager = !empty($teamIds);
        }

        $isProjectManager = $this->checkIsProjectManager($user, $roleIds, $isSuperAdmin);
        $hasAdminAccess = $isSuperAdmin || (method_exists($user, 'hasRole') && $user->hasRole(['admin', 'hr_admin', 'manager'])) || (method_exists($user, 'hasPermission') && $user->hasPermission('reporting.structure.manage'));

        return $menus->map(function ($m) use ($isProjectManager) {
            $id = (int)($m->id ?? 0);

            // Remap Projects menu (321) to real projects.index route instead of generic coming-soon module.project-mgmt route
            if (($id === 321 || ($m->route ?? '') === 'module.project-mgmt') && $isProjectManager) {
                $m = clone $m;
                $m->route = 'projects.index';
            }

            return $m;
        })->reject(function ($m) use ($isTeamManager, $isProjectManager, $hasAdminAccess) {
            $id = (int)($m->id ?? 0);
            $parentId = (int)($m->parent_id ?? 0);

            // 1. Legacy operational submenus under 350 are deprecated
            if (in_array($id, [355, 356, 357, 358, 359], true)) {
                return true;
            }

            // 2. Team Management container (370) and operational submenus (371..377):
            // Show ONLY if employee is an active Reporting Manager (has team members assigned under them)
            if (($id === 370 || $parentId === 370 || in_array($id, [371, 372, 373, 374, 375, 376, 377], true)) && !$isTeamManager) {
                return true;
            }

            // 3. Project Management lead/management menus (321, 322, 9901, 9903):
            // If user is NOT a project manager/lead, reject project management lead menus
            if (in_array($id, [321, 322, 9901, 9903], true) && !$isProjectManager) {
                return true;
            }

            // 4. Reporting Management container (350) and configuration submenus (352, 353, 354, 360):
            // Show ONLY if user has Admin access
            if (($id === 350 || $parentId === 350) && !$hasAdminAccess) {
                return true;
            }

            return false;
        })->values();
    }

    public function clearCache(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    private function cacheKey(int $userId): string
    {
        return 'sidebar_resolved_user_' . $userId;
    }

    private function loadBaseMenus(): Collection
    {
        if (! Schema::hasTable('menus')) {
            return collect();
        }

        $base = DB::table('menus')
            ->where('is_active', 1)
            ->select('id', 'name', 'route', 'icon', 'module_key', 'permission_key', 'parent_id', 'sort_order', 'is_active')
            ->get();

        $menus = collect($base);

        // Dynamically inject "My Tasks" submenu item for Employees
        $menus->push((object)[
            'id' => 9999,
            'name' => 'My Tasks',
            'route' => 'project_management.tasks.my',
            'icon' => 'fas fa-user-check',
            'module_key' => 'employee.tasks',
            'permission_key' => 'tasks.view',
            'parent_id' => 320,
            'sort_order' => 2,
            'is_active' => 1
        ]);

        $menus->push((object)[
            'id' => 9901,
            'name' => 'Projects Directory',
            'route' => 'projects.index',
            'icon' => 'fas fa-project-diagram',
            'module_key' => 'projects.directory',
            'permission_key' => 'projects.view_all',
            'parent_id' => 320,
            'sort_order' => 1,
            'is_active' => 1
        ]);

        $menus->push((object)[
            'id' => 9902,
            'name' => 'My Projects',
            'route' => 'projects.my',
            'icon' => 'fas fa-tasks',
            'module_key' => 'employee.projects',
            'permission_key' => 'projects.my_projects.view',
            'parent_id' => 320,
            'sort_order' => 3,
            'is_active' => 1
        ]);

        $menus->push((object)[
            'id' => 9903,
            'name' => 'Project Tasks',
            'route' => 'projects.tasks.index',
            'icon' => 'fas fa-list-check',
            'module_key' => 'projects.tasks',
            'permission_key' => 'projects.tasks.view',
            'parent_id' => 320,
            'sort_order' => 4,
            'is_active' => 1
        ]);

        return $menus;
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
        if ($isSuperAdmin) {
            return $menus;
        }

        if (empty($roleIds) || ! Schema::hasTable('role_menu_access')) {
            return collect();
        }

        $allowedIds = DB::table('role_menu_access')
            ->whereIn('role_id', $roleIds)
            ->pluck('menu_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // Always allow Project Management container, Projects menu, Tasks menu, My Tasks, Projects Directory, etc.
        $allowedIds[] = 320;
        $allowedIds[] = 321;
        $allowedIds[] = 322;
        $allowedIds[] = 9999;
        $allowedIds[] = 9901;
        $allowedIds[] = 9902;
        $allowedIds[] = 9903;

        // Always allow Reporting Management (350..360) and Team Management (370..377) containers to pass role filtering
        for ($i = 350; $i <= 377; $i++) {
            $allowedIds[] = $i;
        }

        // Always allow Today's Attendance menu (349 / attendances.today)
        $todayMenu = $menus->firstWhere('route', 'attendances.today');
        if ($todayMenu) {
            $allowedIds[] = (int) $todayMenu->id;
        } else {
            $allowedIds[] = 349;
        }

        // Always allow Leave Requests (32) and Apply Leave (137)
        $allowedIds[] = 32;
        $allowedIds[] = 137;

        if (empty($allowedIds)) {
            return collect();
        }

        return $menus->whereIn('id', $allowedIds)->values();
    }

    private function filterByPermission(Collection $menus, Authenticatable $user, bool $isSuperAdmin): Collection
    {
        if ($isSuperAdmin || ! method_exists($user, 'hasPermission')) {
            return $menus;
        }

        $menuPermissionMap = $this->menuPermissionMap();

        return $menus->filter(function ($menu) use ($user, $menuPermissionMap) {
            $permKey = (string) ($menu->permission_key ?? '');
            if ($permKey !== '') {
                if ($user->hasPermission($permKey)) {
                    return true;
                }
            }

            $route = (string) ($menu->route ?? '');
            if ($route === '' || ! isset($menuPermissionMap[$route])) {
                return $permKey === '';
            }

            if ($route === 'projects.my' || $route === 'projects.tasks.index' || $route === 'projects.index') {
                return true;
            }

            foreach ($menuPermissionMap[$route] as $permissionKey) {
                if ($user->hasPermission($permissionKey)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    private function checkIsProjectManager(Authenticatable $user, array $roleIds, bool $isSuperAdmin): bool
    {
        $empId = null;
        $userEmp = DB::table('employees_new')->where('user_id', $user->id)->first(['id']);
        if ($userEmp) {
            $empId = (int)$userEmp->id;
        }

        $isTeamManager = false;
        $isProjectManager = false;

        if ($empId) {
            $teamScope = app(\App\Services\HRMS\Team\TeamManagementScopeS::class);
            $teamIds = $teamScope->getTeamEmployeeIds($empId);
            $isTeamManager = !empty($teamIds);

            $isTeamLead = DB::table('project_teams')->where('team_lead_employee_id', $empId)->where('is_active', 1)->exists();
            $isDeliveryHead = DB::table('projects')->where('delivery_head_employee_id', $empId)->exists();
            $isProjectLead = DB::table('project_assignments')
                ->where('employee_id', $empId)
                ->where('is_active', 1)
                ->where(function($q) {
                    $q->whereIn(DB::raw('LOWER(project_role)'), [
                        'team_lead', 'team lead',
                        'project_lead', 'project lead',
                        'project_manager', 'project manager',
                        'lead', 'manager',
                        'delivery_head', 'delivery head'
                    ]);
                })->exists();

            $isProjectManager = $isTeamLead || $isDeliveryHead || $isProjectLead || $isTeamManager;
        }

        $hasRoleMenuAccess = false;
        if (!empty($roleIds) && Schema::hasTable('role_menu_access')) {
            $hasRoleMenuAccess = DB::table('role_menu_access')
                ->whereIn('role_id', $roleIds)
                ->whereIn('menu_id', [320, 321, 322, 9901, 9903])
                ->exists();
        }

        if ($isSuperAdmin || $hasRoleMenuAccess || (method_exists($user, 'hasRole') && $user->hasRole(['admin', 'hr_admin', 'project_admin', 'operations_admin', 'custom_admin'])) || in_array(($user->system_role_id ?? $user->role_id ?? 0), [1, 2, 3, 5], true) || (method_exists($user, 'hasPermission') && ($user->hasPermission('projects.view_all') || $user->hasPermission('projects.manage')))) {
            $isProjectManager = true;
        }

        return $isProjectManager;
    }

    private function filterByEmployeeOnlyVisibility(Collection $menus, bool $isEmployeeContext, bool $isProjectManager = false): Collection
    {
        return $menus->filter(function ($menu) use ($isEmployeeContext, $isProjectManager) {
            // Dashboard is always visible to everyone
            if ($menu->id === 1 || ($menu->route ?? '') === 'dashboard') {
                return true;
            }

            // Exclude My Tasks for non-employee contexts
            if (! $isEmployeeContext && $menu->id == 9999) {
                return false;
            }

            // Always allow Reporting Management (350) and its submenus (351..360) in employee context
            $id = (int)($menu->id ?? 0);
            $parentId = (int)($menu->parent_id ?? 0);
            $route = strtolower(trim((string) ($menu->route ?? '')));
            if ($id === 350 || $parentId === 350 || $id === 370 || $parentId === 370 || str_starts_with($route, 'reporting.') || str_starts_with($route, 'team.')) {
                return true;
            }

            $isEmployeeOnly = $this->isEmployeeOnlyMenu($menu);

            if ($isEmployeeContext) {
                $route = strtolower(trim((string) ($menu->route ?? '')));

                if (in_array($id, [321, 9901], true)) {
                    return $isProjectManager;
                }

                if ($id === 9903 || $route === 'projects.tasks.index') {
                    return true;
                }

                // Exclude admin-only attendance & HR management routes from Employee Self Service panel
                $adminOnlyRoutes = [
                    'projects.index',
                    'module.project-mgmt',
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
        $validIds = [];

        foreach ($menus as $menu) {
            $route = (string) ($menu->route ?? '');
            if ($route === '' || $route === '#' || $this->resolveRouteName($route) !== null) {
                $validIds[] = (int) $menu->id;
            }
        }

        return $menus->whereIn('id', $validIds)->values();
    }

    private function filterRetiredLegacyPayrollMenus(Collection $menus): Collection
    {
        // Legacy Payroll retired. Enterprise Payroll is the only active payroll engine.
        return $menus->filter(function ($menu) {
            $route = strtolower(trim((string) ($menu->route ?? '')));
            if ($route === '') {
                return true;
            }

            return ! str_starts_with($route, 'pages.payroll.')
                && ! str_starts_with($route, 'hrms.payroll.');
        })->values();
    }

    private function repairParentVisibility(Collection $menus): Collection
    {
        $indexed = $menus->keyBy('id');

        foreach ($menus as $menu) {
            $parentId = (int) ($menu->parent_id ?? 0);
            if ($parentId > 0 && ! $indexed->has($parentId)) {
                $parent = DB::table('menus')
                    ->where('id', $parentId)
                    ->where('is_active', 1)
                    ->first(['id', 'name', 'route', 'icon', 'module_key', 'parent_id', 'sort_order', 'is_active']);

                if ($parent) {
                    $indexed->put((int) $parent->id, $parent);
                }
            }
        }

        return $indexed->values();
    }

    private function deduplicateMenus(Collection $menus): Collection
    {
        $seenIds = [];
        $seenSignatures = [];
        $seenRoutes = [];
        $deduped = collect();

        // Sort so that child menus (parent_id > 0) are evaluated before standalone top-level duplicate routes,
        // and lower IDs (e.g. DB menu ID 321) are evaluated before dynamic menu IDs (e.g. 9901)
        $sortedForDedup = $menus->sort(function ($a, $b) {
            $aParent = !empty($a->parent_id) ? 1 : 0;
            $bParent = !empty($b->parent_id) ? 1 : 0;
            if ($aParent !== $bParent) {
                return $bParent <=> $aParent;
            }
            return ((int)($a->id ?? 0)) <=> ((int)($b->id ?? 0));
        });

        foreach ($sortedForDedup as $menu) {
            $id = (int) ($menu->id ?? 0);
            if ($id > 0 && isset($seenIds[$id])) {
                continue;
            }

            $parentId = (int) ($menu->parent_id ?? 0);
            $route = strtolower(trim((string) ($menu->route ?? '')));
            $name = strtolower(trim((string) ($menu->name ?? '')));

            if ($route !== '' && isset($seenRoutes[$route])) {
                continue;
            }

            $signature = $parentId . '|' . $route . '|' . $name;

            if (isset($seenSignatures[$signature])) {
                continue;
            }

            if ($id > 0) {
                $seenIds[$id] = true;
            }
            if ($route !== '') {
                $seenRoutes[$route] = true;
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
            $hasRoute = ! empty((string) ($menu->route ?? ''));
            if ($hasRoute) {
                return true;
            }

            return in_array((int) $menu->id, $idsWithChildren, true);
        })->values();
    }

    private function resolveRouteName(string $routeName): ?string
    {
        if ($routeName === '') {
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

    private function filterByWebAttendancePermission(Collection $menus, Authenticatable $user, bool $isSuperAdmin): Collection
    {
        return $menus;
    }

    private function menuPermissionMap(): array
    {
        return [
            'employee.shift-assignment.index' => ['employee.shift.assign.manage'],
            'attendances.today' => ['attendance.my.view', 'attendance.records.view_all', 'attendance.dashboard.view'],
            'attendances.team' => ['attendance.records.view_all', 'attendance.monthly_report.view_team', 'attendance.regularization.view_team', 'attendance.dashboard.view'],
            'reporting.attendance' => ['attendance.records.view_all', 'attendance.monthly_report.view_team', 'attendance.regularization.view_team', 'attendance.dashboard.view'],
            'attendance.policies.index' => ['attendance.rules.manage'],
            'attendance.rules.index' => ['attendance.rules.manage'],
            'attendances.access-control' => ['attendance.blocked.view', 'attendance.access_control.manage', 'attendance.records.view_all', 'attendance.dashboard.view'],
            'documents.compliance.index' => ['documents.compliance.view'],
            'documents.verification.index' => ['documents.verification.view'],
            'documents.types.index' => ['documents.types.manage'],
            'documents.policies.index' => ['documents.company.view'],
            'hrms.documents.self.index' => ['documents.upload.self', 'documents_self.view'],
            'hrms.document-generation.dashboard' => ['document_generation.view'],
            'hrms.document-generation.self.index' => ['document_generation.view', 'employee_documents.view', 'documents.upload.self', 'documents_self.view'],
            'settings.hrms_exit_policies.index' => ['hrms_exit_policy.view', 'hrms_exit_policy.manage', 'hrms_exit_policy.update'],
            'settings.system.index' => ['settings.system.manage'],
            'settings.company.index' => ['settings.company.manage'],
            'settings.branding.index' => ['settings.branding.view', 'settings.branding.update'],
            'hrms.mobile-app-versions.index' => ['mobile_app_versions.view', 'mobile_app_versions.manage'],
            'roles.index' => ['roles.manage', 'access.roles.manage'],
            'permissions.index' => ['permissions.manage', 'access.permissions.manage'],
            'admins.index' => ['admins.manage', 'access.admins.manage'],
            'hrms.attendance.work-reports' => ['attendance.work_reports.view_all', 'attendance.work_reports.view_team'],
            'hrms.attendance.my-work-reports' => ['attendance.work_reports.view_own'],
            'enterprise-payroll.policies.index' => ['enterprise_payroll.policy.view'],
            'hrms.organization.index' => ['departments.manage', 'designations.manage', 'employees.organization.manage'],
            'hrms.attendance.regularizations.index' => ['attendance.regularization.view_all', 'attendance.regularization.view_team', 'attendance.regularization.view_own'],
            'hrms.attendance.holiday-work.index' => ['attendance.holiday_work.view', 'attendance.holiday_work.manage', 'attendance.holiday_work.approve'],
            'hrms.attendance.wfh.index' => ['attendance.wfh.view', 'attendance.wfh.own'],
            'hrms.attendance.my-wfh.index' => ['attendance.wfh.own'],
            'hrms.leave.dashboard' => ['leave.dashboard.view', 'leave.my_requests.view'],
            'leave-requests.index' => ['leave.my_requests.view', 'leave.approvals.view_all', 'leave.history.view', 'leave.dashboard.view'],
            'leave-approvals.index' => ['leave.approvals.view_all', 'leave.approvals.view_team', 'leave.approvals.view', 'leave.approve'],
            'hrms.leave.history' => ['leave.history.view', 'leave.my_requests.view', 'leave.approvals.view_all', 'leave.approvals.view_team'],
            'leave-requests.create' => ['leave.my_requests.create', 'leave.my_requests.view', 'leave.apply', 'leave_self.apply', 'leave.approvals.view_all'],
            'leave-allocations.index' => ['leave.allocation.manage', 'leave.allocation.view_all', 'leave.allocation.view'],
            'hrms.leave.balances.index' => ['leave.balance.view_all', 'leave.balance.view_team', 'leave.balance.view_own', 'leave.balance.view', 'leave_self.view_balance'],
            'employees-leave-request.summary' => ['leave.balance.view_all', 'leave.balance.view_team', 'leave.balance.view_own', 'leave.balance.view', 'leave_self.view_balance'],
            'hrms.holidays.index' => ['leave.holidays.manage', 'leave.team_calendar.view'],
            'projects.index' => ['projects.view_all', 'projects.my_projects.view', 'projects.delivery_head.view', 'projects.team_lead.view'],
            'projects.my' => ['projects.my_projects.view'],
            'projects.tasks.index' => ['projects.tasks.view', 'projects.view_all'],
            'projects.team.attendance' => ['projects.team_attendance.view', 'attendance.records.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.team.work_reports' => ['projects.team_work_reports.view', 'attendance.work_reports.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.team.leave' => ['projects.team_leave.view', 'leave.approvals.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.templates.index' => ['projects.work_report.templates.manage', 'projects.manage'],
        ];
    }

    private function isEmployeeContext(Authenticatable $user): bool
    {
        $hasEmployeeRole = method_exists($user, 'hasRole') && $user->hasRole('employee');
        if (! $hasEmployeeRole) {
            return false;
        }

        $hasAdminRole = method_exists($user, 'hasRole') && $user->hasRole([
            'super_admin',
            'admin',
            'hr_admin',
            'finance_admin',
            'project_admin',
            'operations_admin',
            'custom_admin',
            'manager',
        ]);

        return ! $hasAdminRole;
    }

    private function isEmployeeOnlyMenu(object $menu): bool
    {
        $route = strtolower(trim((string) ($menu->route ?? '')));
        $name = strtolower(trim((string) ($menu->name ?? '')));
        $moduleKey = strtolower(trim((string) ($menu->module_key ?? '')));

        $employeeRoutePrefixes = [
            'hrms.attendance.my-wfh.',
            'hrms.attendance.my-holiday-work.',
            'hrms.documents.self.',
            'employee.announcements.',
            'enterprise-payroll.self.',
            'enterprise-payroll.my_',
            'enterprise_payroll.my_',
            'hrms.attendance.my',
            'hrms.employee.',
            'profile.',
        ];

        $employeeRouteExact = [
            'profile.index',
            'attendances.today',
            'hrms.document-generation.self.index',
            'hrms.attendance.my',
            'hrms.attendance.my-holiday-work.index',
            'employee.announcements.index',
            'enterprise-payroll.self.payslips',
            'enterprise-payroll.self.reimbursements',
            'enterprise_payroll.my_payslips.view',
            'enterprise_payroll.my_reimbursements.view',
        ];

        $employeeNames = [
            'my attendance',
            'my holiday work',
            'my work requests',
            'my documents',
            'upload documents',
            'my payslips',
            'my salary slips',
            'my reimbursements',
            'my announcements',
            'my profile',
            'complete profile',
        ];

        $employeeModulePrefixes = [
            'employee.',
            'my.',
            'my_',
            'employee_',
        ];

        if (in_array($route, $employeeRouteExact, true) || in_array($name, $employeeNames, true) || $route === 'project_management.tasks.my' || $name === 'my tasks') {
            return true;
        }

        foreach ($employeeRoutePrefixes as $prefix) {
            if ($prefix !== '' && str_starts_with($route, $prefix)) {
                return true;
            }
        }

        foreach ($employeeModulePrefixes as $prefix) {
            if ($prefix !== '' && str_starts_with($moduleKey, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isEmployeeParentContainer(object $menu): bool
    {

        $moduleKey = strtolower(trim((string) ($menu->module_key ?? '')));
        $name = strtolower(trim((string) ($menu->name ?? '')));

        if ($moduleKey === 'my.profile' || $name === 'settings') {

            return true;
        }

        return in_array($moduleKey, ['documents', 'attendance', 'leave', 'enterprise_payroll', 'assets', 'project_management'], true)
            || $menu->id == 320;
    }
}
