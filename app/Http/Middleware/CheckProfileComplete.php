<?php

namespace App\Http\Middleware;

use App\Services\HRMS\Employee\EmployeeProfileS;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CheckProfileComplete
{
    private EmployeeProfileS $profileService;

    public function __construct(EmployeeProfileS $profileService)
    {
        $this->profileService = $profileService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        $employeeId = $this->profileService->getIncompleteEmployeeIdForUser((int) $user->id);

        if (! $employeeId) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();

        $allowedRoutes = [
            'logout',
            'profile',
            'profile.update',
            'hrms.employees.profile.complete',
            'hrms.employees.profile.store',
            'hrms.employees.profile.view',
            'hrms.employees.profile.edit',
            'hrms.employees.profile.update',
            'dashboard',
            'employee.dashboard',
        ];

        if ($routeName && in_array($routeName, $allowedRoutes, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Please complete your profile before using this feature.',
            ], 423);
        }

        if (Route::has('profile')) {
            return redirect()
                ->route('profile')
                ->with('warning', 'Please complete your profile first.');
        }

        if (Route::has('hrms.employees.profile.complete')) {
            return redirect()
                ->route('hrms.employees.profile.complete', $employeeId)
                ->with('warning', 'Please complete your profile first.');
        }

        return $next($request);
    }
}
