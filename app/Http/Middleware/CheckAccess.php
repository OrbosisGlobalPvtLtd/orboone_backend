<?php

namespace App\Http\Middleware;

use App\Models\Core\AccessM as Access;
use App\Models\Core\MenuM as Menu;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CheckAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;

        // If route is unnamed (null), skip access check and allow request.
        if (!$routeName) {
            return $next($request);
        }

        if ($routeName === 'enterprise-payroll.payslips.download' || $routeName === 'enterprise-payroll.payslips.view' || $routeName === 'enterprise-payroll.payslips.preview') {
            if ($user->hasPermission('enterprise_payslip.download') || $user->hasPermission('enterprise_payslip.view') || $user->hasPermission('enterprise_payroll.my_payslips.view') || $user->hasPermission('enterprise_payslip.generate')) {
                return $next($request);
            }
        }

        $parts     = explode('.', $routeName);
        $main      = $parts[1] ?? null;

        /**
         * ------------------------------------------
         * MAP MODULE → MENU
         * ------------------------------------------
         */

        if ($main === 'payroll') {
            $menuName = 'payroll';
        }
        else if (strpos($routeName, 'hrms.departments.') === 0 || strpos($routeName, 'hrms.designations.') === 0) {
            $menuName = 'data';
        }
        else if (strpos($routeName, 'hrms.employees.performance_scores.') === 0) {
            $menuName = 'performance';
        }
        else if (strpos($routeName, 'hrms.employees.') === 0) {
            $menuName = 'employees-data';
        }
        else if (strpos($routeName, 'hrms.assets.') === 0) {
            $menuName = 'asset-allocations';
        }
        else if (strpos($routeName, 'hrms.documents.employee.') === 0) {
            $menuName = 'employee-documents';
        }
        else if (strpos($routeName, 'hrms.documents.self.') === 0) {
            $menuName = 'employee';
        }
        else if (strpos($routeName, 'documents.hr.') === 0 || strpos($routeName, 'documents.policies.') === 0) {
            $menuName = 'hr';
        }
        else if ($routeName === 'hrms.organization.index') {
            $menuName = 'organization';
        }
        else if (in_array($parts[0], ['users', 'roles'])) {
            $menuName = 'accounts';
        }
        else if ($parts[0] === 'profile') {
            $menuName = 'user';
        }
        else if ($parts[0] === 'employees-leave-request') {
            $menuName = 'leave-request';
        }
        else if (strpos($routeName, 'project_management.tasks.') === 0) {
            $menuName = 'data';
        }
        else if ($parts[0] === 'score-categories') {
            $menuName = 'score-category';
        }
        else {
            $menuName = $parts[0];
        }

        /**
         * ------------------------------------------
         * CHECK MENU EXISTS
         * ------------------------------------------
         */

        // 1. Try to find menu by exact route name
        $menu = Menu::where('route', $routeName)->where('is_active', 1)->first();

        // 2. If not found, try to find menu by route prefix (e.g. leave-approvals.*)
        if (!$menu) {
            $routePrefix = count($parts) > 1 ? implode('.', array_slice($parts, 0, -1)) : $routeName;
            $menu = Menu::where(function($q) use ($routeName, $routePrefix) {
                $q->where('route', 'like', $routePrefix . '.%')
                  ->orWhere('route', 'like', $routePrefix . '%');
            })->where('is_active', 1)->first();
        }

        // 3. Fallback to name-based lookup
        if (!$menu) {
            $menu = Menu::where('name', $menuName)->first();
        }

        if (!$menu) {
            Log::warning('Menu not found. Access denied.', [
                'menu' => $menuName,
                'route' => $routeName,
                'user_id' => $user->id,
                'path' => $request->path(),
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Access denied.');
        }


        /**
         * ------------------------------------------
         * CHECK ROLE ACCESS
         * ------------------------------------------
         */

        $roleIds = [];

        if (! empty($user->role_id)) {
            $roleIds[] = (int) $user->role_id;
        }

        if (! empty($user->system_role_id)) {
            $roleIds[] = (int) $user->system_role_id;
        }

        if (method_exists($user, 'roles')) {
            $roleIds = array_merge($roleIds, $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->all());
        }

        $roleIds = array_unique(array_filter($roleIds));

        if (empty($roleIds)) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied.');
        }

        $access = \Illuminate\Support\Facades\DB::table('role_menu_access')
            ->where('menu_id', $menu->id)
            ->whereIn('role_id', $roleIds)
            ->first();

        if (!$access) {
            $access = Access::where('menu_id', $menu->id)
                ->whereIn('role_id', $roleIds)
                ->where('status', '>', 0)
                ->first();
        }

        if (!$access) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
