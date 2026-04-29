<?php

namespace App\Http\Middleware;

use App\Models\Access;
use App\Models\Menu;
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
        else if (in_array($parts[0], ['departments-data', 'positions-data'])) {
            $menuName = 'data';
        }
        else if (in_array($parts[0], ['users', 'roles'])) {
            $menuName = 'accounts';
        }
        else if ($parts[0] === 'profile') {
            $menuName = 'user';
        }
        else if ($parts[0] === 'employees-performance-score') {
            $menuName = 'performance';
        }
        else if ($parts[0] === 'employees-leave-request') {
            $menuName = 'leave-request';
        }
        else if (in_array($parts[0], ['task_management', 'add_task']) || ($parts[0] === 'pages' && in_array($main, ['add_task', 'edit_task', 'task_management']))) {
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

        // Allow super-admins (role_id = 1) to bypass Access table
        if ($user->role_id == 1) {
            return $next($request);
        }

        $access = Access::where([
            'menu_id' => $menu->id,
            'role_id' => $user->role_id,
        ])->first();

        if (!$access || $access->status < 1) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
