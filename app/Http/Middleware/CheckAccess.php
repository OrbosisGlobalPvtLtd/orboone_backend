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
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;

        // If route is unnamed (null), skip access check and allow request.
        if (!$routeName) {
            return $next($request);
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
        else if (strpos($routeName, 'hrms.documents.hr.') === 0 || strpos($routeName, 'hrms.documents.policies.') === 0) {
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

        $menu = Menu::where('name', $menuName)->first();

        if (!$menu) {
            // Menu record missing in DB. Log and allow the request
            // so pages still open. Admins should still be able to access.
            Log::warning("Menu not found (allowing access): {$menuName}");
            return $next($request);
        }


        /**
         * ------------------------------------------
         * CHECK ROLE ACCESS
         * ------------------------------------------
         */

        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return $next($request);
        }

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

        $access = Access::where('menu_id', $menu->id)
            ->whereIn('role_id', $roleIds)
            ->where('status', '>', 0)
            ->first();

        if (!$access) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
