<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Services\HRMS\Employee\EmployeeProfileCompletionS;

class CheckEmployeeProfileCompletion
{
    protected EmployeeProfileCompletionS $completionService;

    public function __construct(EmployeeProfileCompletionS $completionService)
    {
        $this->completionService = $completionService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Don't apply to API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        // Bypass profile completion if user must change password to avoid redirect loops
        if (session('must_change_password') || (isset($user->must_change_password) && $user->must_change_password)) {
            return $next($request);
        }

        // 1. Check if user exists in employees_new
        $employee = ($user->relationLoaded('employee') && $user->employee)
            ? $user->employee
            : EmployeeM::with(['profile'])->where('user_id', $user->id)->first();

        if ($employee && !$user->relationLoaded('employee')) {
            $user->setRelation('employee', $employee);
        }

        // Agar user ka record employees_new me nahi hai, to bypass karein
        if (!$employee) {
            return $next($request);
        }

        $eligibilityService = app(\App\Services\HRMS\Employee\EmployeeEligibilityS::class);
        if ($eligibilityService->isExitCompleted($employee) || $eligibilityService->isTerminated($employee)) {
            Auth::logout();
            return redirect('/login')->with('fail', 'Your employment has ended. Please contact HR.');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        // 2. Check employee_profiles record
        $profile = $employee->profile;
        if (!$profile) {
            $profile = EmployeeProfileM::firstOrCreate(['employee_id' => $employee->id]);
            $employee->setRelation('profile', $profile);
        }

        $status = $this->completionService->buildCompletionStatus($employee, $profile);

        $isCompletionRoute = $request->routeIs('hrms.employee.complete_profile') || 
                             $request->routeIs('hrms.employee.store_profile') || 
                             $request->routeIs('hrms.employee.documents.upload') || 
                             $request->routeIs('hrms.employee.documents.replace') || 
                             $request->routeIs('hrms.employee.documents.destroy') ||
                             $request->routeIs('hrms.employee.documents.file') ||
                             $request->routeIs('hrms.employee.submit_verification') ||
                             $request->routeIs('hrms.documents.file') ||
                             $request->routeIs('hrms.documents.self.*');

        if ($status['must_complete_profile']) {
            if (!$isCompletionRoute && !$request->routeIs('logout')) {
                return redirect()->route('hrms.employee.complete_profile');
            }
        } else {
            // Profile is completed (submitted or approved)
            // Prevent access to complete_profile page
            if ($isCompletionRoute && $request->routeIs('hrms.employee.complete_profile')) {
                return redirect()->route('hrms.employee.my_profile');
            }
        }

        return $next($request);
    }
}
