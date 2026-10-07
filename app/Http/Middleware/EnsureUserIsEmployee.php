<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\HRMS\Employee\EmployeeM;

class EnsureUserIsEmployee
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
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userId = $user->id;
        $employee = null;

        try {
            if (class_exists(EmployeeM::class)) {
                $employee = EmployeeM::where('user_id', $userId)->first();
            } else {
                $employee = DB::table('employees_new')->where('user_id', $userId)->first();
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // If no employee profile exists, automatically link/create for Super Admin and Admin accounts
        if (!$employee) {
            $isAdminUser = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
                || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin', 'finance_admin', 'project_admin', 'operations_admin', 'custom_admin']))
                || in_array((int)($user->system_role_id ?? 0), [1, 2, 3, 4, 5, 6, 8], true);

            if ($isAdminUser && Schema::hasTable('employees_new')) {
                try {
                    $codePrefix = in_array((int)($user->system_role_id ?? 0), [1, 2], true) ? 'ADMIN-' : 'EMP-';
                    $existingCode = DB::table('employees_new')->where('employee_code', $codePrefix . str_pad($userId, 4, '0', STR_PAD_LEFT))->exists();
                    $empCode = $existingCode ? $codePrefix . $userId . '-' . time() : $codePrefix . str_pad($userId, 4, '0', STR_PAD_LEFT);

                    DB::table('employees_new')->insert([
                        'user_id' => $userId,
                        'employee_code' => $empCode,
                        'system_role_id' => $user->system_role_id ?? 1,
                        'employment_status' => 'confirmed',
                        'is_active' => 1,
                        'allow_web_attendance' => 1,
                        'allow_mobile_attendance' => 1,
                        'joining_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (class_exists(EmployeeM::class)) {
                        $employee = EmployeeM::where('user_id', $userId)->first();
                    } else {
                        $employee = DB::table('employees_new')->where('user_id', $userId)->first();
                    }
                } catch (\Throwable $e) {
                    // Ignore insert failure
                }
            }
        }

        if (!$employee) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Employee profile is not linked with this account.', 'status' => false], 403);
            }
            abort(403, 'Employee profile is not linked with this account.');
        }

        return $next($request);
    }
}
