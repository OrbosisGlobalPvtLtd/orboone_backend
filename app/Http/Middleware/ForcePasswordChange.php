<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $mustChange = session('must_change_password') || (isset($user->must_change_password) && $user->must_change_password);

            if ($mustChange) {
                if (!session()->has('must_change_password')) {
                    session(['must_change_password' => true]);
                }

                $routeName = optional($request->route())->getName();
                
                // Allow only logout and password change/update routes
                $allowedRoutes = [
                    'logout',
                    'profile.index',
                    'profile.password.update',
                ];

                if ($routeName && in_array($routeName, $allowedRoutes, true)) {
                    return $next($request);
                }

                return redirect()
                    ->route('profile.index')
                    ->with('warning', 'For security reasons, you must change your default password before continuing.');
            }
        }

        return $next($request);
    }
}
