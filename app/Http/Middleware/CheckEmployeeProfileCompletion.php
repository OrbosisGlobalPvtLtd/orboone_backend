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

        // resolve employee by user_id from employees_new/current Employee model
        $employee = EmployeeM::with(['profile'])->where('user_id', $user->id)->first();

        // if no employee found, skip middleware
        if (!$employee) {
            return $next($request);
        }

        // if employee found, apply existing completion logic
        // Only apply to logged-in employees who are not admins
        if (!method_exists($user, 'isEmployee') || !$user->isEmployee() || $user->isAdmin()) {
            return $next($request);
        }

        $profile = $employee->profile;
        if (!$profile) {
            $profile = EmployeeProfileM::create(['employee_id' => $employee->id]);
            $employee->setRelation('profile', $profile);
        }

        $status = $this->completionService->buildCompletionStatus($employee, $profile);

        $isCompletionRoute = $request->routeIs('hrms.employee.complete_profile') || 
                             $request->routeIs('hrms.employee.store_profile') || 
                             $request->routeIs('hrms.employee.documents.upload') || 
                             $request->routeIs('hrms.employee.documents.replace') || 
                             $request->routeIs('hrms.employee.documents.destroy') ||
                             $request->routeIs('hrms.employee.documents.file') ||
                             $request->routeIs('hrms.employee.submit_verification');

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
