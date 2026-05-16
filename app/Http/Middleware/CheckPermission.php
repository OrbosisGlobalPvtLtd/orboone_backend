<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        $user = auth()->user();
        $permissions = array_filter(array_map('trim', explode('|', $permission)));

        foreach ($permissions as $permissionKey) {
            if ($user && $user->hasPermission($permissionKey)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
