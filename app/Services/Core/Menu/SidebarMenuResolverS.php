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
            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super_admin');
            $isEmployeeContext = $this->isEmployeeContext($user);

            $filtered = $this->filterByRoleMenuAccess($menus, $roleIds, $isSuperAdmin);
            $filtered = $this->filterByPermission($filtered, $user, $isSuperAdmin);
            $filtered = $this->filterByEmployeeOnlyVisibility($filtered, $isEmployeeContext);
            $filtered = $this->filterRetiredLegacyPayrollMenus($filtered);
            $filtered = $this->filterByRouteValidity($filtered);
            $filtered = $this->repairParentVisibility($filtered);
            $filtered = $this->deduplicateMenus($filtered);
            $filtered = $this->removeEmptyParents($filtered);

            return $filtered
                ->sortBy([
                    ['parent_id', 'asc'],
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values()
                ->groupBy('parent_id');
        });
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

        return DB::table('menus')
            ->where('is_active', 1)
            ->select('id', 'name', 'route', 'icon', 'module_key', 'parent_id', 'sort_order', 'is_active')
            ->get();
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

    private function filterByRoleMenuAccess(Collection $menus, array $roleIds, bool $isSuperAdmin): Collection
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
            $route = (string) ($menu->route ?? '');
            if ($route === '' || ! isset($menuPermissionMap[$route])) {
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

    private function filterByEmployeeOnlyVisibility(Collection $menus, bool $isEmployeeContext): Collection
    {
        return $menus->filter(function ($menu) use ($isEmployeeContext) {
            // Dashboard is always visible to everyone
            if ($menu->id === 1 || ($menu->route ?? '') === 'dashboard') {
                return true;
            }

            $isEmployeeOnly = $this->isEmployeeOnlyMenu($menu);

            if ($isEmployeeContext) {
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
            if ($route === '' || $this->resolveRouteName($route) !== null) {
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

        foreach ($menus as $menu) {
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

    private function menuPermissionMap(): array
    {
        return [
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
            'hrms.mobile-app-versions.index' => ['mobile_app_versions.view', 'mobile_app_versions.manage'],
            'roles.index' => ['access.roles.manage'],
            'permissions.index' => ['access.permissions.manage'],
            'admins.index' => ['admins.manage'],
            'hrms.attendance.work-reports' => ['attendance.work_reports.view_all', 'attendance.work_reports.view_team'],
            'hrms.attendance.my-work-reports' => ['attendance.work_reports.view_own'],
            'enterprise-payroll.policies.index' => ['enterprise_payroll.policy.view'],
            'hrms.attendance.wfh.index' => ['attendance.wfh.view', 'attendance.wfh.own'],
            'hrms.attendance.my-wfh.index' => ['attendance.wfh.own'],
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
            'hrms.documents.self.',
            'employee.announcements.',
            'enterprise-payroll.self.',
            'enterprise-payroll.my_',
            'enterprise_payroll.my_',
            'hrms.attendance.my',
            'hrms.employee.',
            'profile.',
            'leave-requests.',
        ];

        $employeeRouteExact = [
            'profile.index',
            'hrms.document-generation.self.index',
            'hrms.attendance.my',
            'employee.announcements.index',
            'enterprise-payroll.self.payslips',
            'enterprise-payroll.self.reimbursements',
            'enterprise_payroll.my_payslips.view',
            'enterprise_payroll.my_reimbursements.view',
        ];

        $employeeNames = [
            'my attendance',
            'my leave requests',
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

        if (in_array($route, $employeeRouteExact, true) || in_array($name, $employeeNames, true)) {
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

        return in_array($moduleKey, ['documents', 'attendance', 'leave', 'enterprise_payroll'], true);
    }
}
