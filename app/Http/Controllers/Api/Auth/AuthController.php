<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
                'errors'  => null,
                'data'    => null,
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user()->load('role');

        if (!(bool) $user->is_active) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact admin.',
                'errors'  => null,
                'data'    => null,
            ], 403);
        }

        $employee = EmployeeM::with([
            'department',
            'position',
            'systemRole',
            'reportingManager.user',
            'profile',
        ])->where('user_id', $user->id)->first();

        $isProfileCompleted = (bool) ($employee?->profile?->is_profile_completed ?? false);
        $isEmployee = method_exists($user, 'isEmployee') ? (bool) $user->isEmployee() : false;
        $isAdmin = method_exists($user, 'isAdmin') ? (bool) $user->isAdmin() : false;
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') ? (bool) $user->isSuperAdmin() : false;

        $mustCompleteProfile = $isEmployee ? !$isProfileCompleted : false;
        $nextRoute = $mustCompleteProfile ? 'profile_completion' : 'dashboard';

        $token = $user->createToken('api-token')->plainTextToken;

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
                    'employee_id'       => $employee?->id,
                    'employee_code'     => $employee?->employee_code,
                    'employment_type'   => $employee?->employment_type,
                    'work_mode'         => $employee?->work_mode,
                    'employment_status' => $employee?->employment_status,

                    'department' => [
                        'id'   => $employee?->department?->id,
                        'name' => $employee?->department?->name,
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

                    'is_profile_completed'  => $isProfileCompleted,
                    'must_complete_profile' => $mustCompleteProfile,
                    'next_route'            => $nextRoute,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout Success',
            'errors'  => null,
            'data'    => null,
        ]);
    }
}