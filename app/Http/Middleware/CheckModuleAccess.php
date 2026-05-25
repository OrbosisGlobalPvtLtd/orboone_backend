<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, $module)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return $next($request);
        }

        $module = strtolower(trim((string) $module));

        // HRMS is the primary authenticated app surface (employee + admin).
        // Keep existing behavior for active self-service/admin flows.
        if ($module === 'hrms') {
            return $next($request);
        }

        if (! $this->hasModuleAccess($user, $module)) {
            Log::warning('Module access denied.', [
                'user_id' => $user->id,
                'module' => $module,
                'route' => optional($request->route())->getName(),
                'path' => $request->path(),
            ]);

            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        return $next($request);
    }

    private function hasModuleAccess($user, string $module): bool
    {
        if (! Schema::hasTable('menus')) {
            return false;
        }

        $menu = DB::table('menus')->where('name', $module)->first();
        if (! $menu) {
            return false;
        }

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

        $roleIds = array_unique(array_filter($roleIds));
        if (empty($roleIds)) {
            return false;
        }

        if (! Schema::hasTable('accesses')) {
            return false;
        }

        return DB::table('accesses')
            ->where('menu_id', $menu->id)
            ->whereIn('role_id', $roleIds)
            ->where('status', '>', 0)
            ->exists();
    }
}
