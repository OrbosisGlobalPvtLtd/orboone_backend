<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
                'errors'  => null,
                'data'    => null,
            ], 401);
        }

        /** @var \App\Models\Core\UserM $user */
        $user = Auth::guard('web')->user()->load('role');

        if (!(bool) $user->is_active) {
            Auth::guard('web')->logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact admin.',
                'errors'  => null,
                'data'    => null,
            ], 403);
        }

        $employee = EmployeeM::with([
            'department',
            'designation',
            'systemRole',
            'reportingManager.user',
            'profile',
            'salaryHistories',
        ])->where('user_id', $user->id)->first();

        $isProfileCompleted = (bool) ($employee?->profile?->is_profile_completed ?? false);
        $isEmployee = method_exists($user, 'isEmployee') ? (bool) $user->isEmployee() : false;
        $isAdmin = method_exists($user, 'isAdmin') ? (bool) $user->isAdmin() : false;
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') ? (bool) $user->isSuperAdmin() : false;

        $mustCompleteProfile = $isEmployee ? !$isProfileCompleted : false;
        $mustChangePassword = Schema::hasColumn('users', 'must_change_password')
            ? (bool) ($user->must_change_password ?? false)
            : false;

        $nextRoute = $mustChangePassword
            ? 'change_password'
            : ($mustCompleteProfile ? 'profile_completion' : 'dashboard');

        // HRMS mobile rule:
        // One user = one active mobile token.
        // Old mobile/api tokens are deleted before creating a new one.
        $user->tokens()
            ->whereIn('name', ['api-token', 'mobile-app'])
            ->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Success',
            'errors'  => null,
            'data'    => [
                'token' => $token,

                'user'  => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'is_active' => (bool) $user->is_active,

                    'role'      => [
                        'id'             => $user->role?->id,
                        'name'           => $user->role?->name,
                        'code'           => $user->role?->code,
                        'is_system'      => (bool) ($user->role?->is_system ?? false),
                        'is_active'      => (bool) ($user->role?->is_active ?? false),
                        'is_super_admin' => (bool) ($user->role?->is_super_admin ?? false),
                    ],

                    'permissions' => [
                        'is_admin'       => $isAdmin,
                        'is_employee'    => $isEmployee,
                        'is_super_admin' => $isSuperAdmin,
                    ],
                ],

                'employee_context' => [
                    'employee_id'            => $employee?->id,
                    'employee_code'          => $employee?->employee_code,
                    'employment_type'        => $employee?->employment_type,
                    'employee_stage'         => $employee?->employee_stage,
                    'work_mode'              => $employee?->work_mode,
                    'work_schedule_type'     => $employee?->work_schedule_type,
                    'employment_status'      => $employee?->employment_status,
                    'probation_status'       => $employee?->probation_status,
                    'internship_start_date'  => $employee?->internship_start_date,
                    'internship_end_date'    => $employee?->internship_end_date,
                    'internship_extended_to' => $employee?->internship_extended_to,
                    'actual_salary'          => $employee?->actual_salary,

                    'salary_history' => $employee?->salaryHistories?->map(function ($history) {
                        return [
                            'id'             => $history->id,
                            'stage'          => $history->stage,
                            'salary_amount'  => $history->salary_amount,
                            'effective_from' => optional($history->effective_from)->toDateString(),
                            'effective_to'   => optional($history->effective_to)->toDateString(),
                            'reason'         => $history->reason,
                        ];
                    })->values() ?? [],

                    'department' => [
                        'id'   => $employee?->department?->id,
                        'name' => $employee?->department?->name,
                    ],

                    'designation' => [
                        'id'   => $employee?->designation?->id,
                        'name' => $employee?->designation?->name,
                    ],

                    'position' => [
                        'id'   => $employee?->position?->id,
                        'name' => $employee?->position?->name,
                    ],

                    'role' => [
                        'id'   => $employee?->systemRole?->id,
                        'name' => $employee?->systemRole?->name,
                    ],

                    'reporting_manager' => [
                        'id'   => $employee?->reportingManager?->id,
                        'name' => $employee?->reportingManager?->user?->name,
                    ],

                    'is_profile_completed' => $isProfileCompleted,
                    'must_change_password' => $mustChangePassword,
                    'must_complete_profile' => $mustCompleteProfile,
                    'next_route' => $nextRoute,
                ],
            ],
        ]);
    }

    public function checkToken(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors'  => null,
                'data'    => null,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token is valid.',
            'errors'  => null,
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout Success',
            'errors'  => null,
            'data'    => null,
        ]);
    }
}