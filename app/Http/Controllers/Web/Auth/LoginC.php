<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginC extends Controller
{
    use AuthenticatesUsers;

    /**
     * Default redirect path after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Validate login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            $this->username() => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }

    /**
     * Handle post-authentication logic.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function authenticated(Request $request, $user)
    {
        // Inactive user block
        if (!$user->is_active) {
            Auth::logout();

            return redirect('/login')->with('fail', 'Your account is inactive. Please contact admin.');
        }

        // Update last login time if column exists
        if (\Schema::hasColumn('users', 'last_login_at')) {
            $user->update([
                'last_login_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Temporary Web Login Logic
        |--------------------------------------------------------------------------
        | Current phase:
        | - Admin roles allowed
        | - Employee also temporarily allowed
        | Later:
        | - Employee web can be restricted if needed
        |--------------------------------------------------------------------------
        */

        // If explicit web access flag exists and user has no web access
        if (\Schema::hasColumn('users', 'is_web_access') && !$user->is_web_access) {
            Auth::logout();

            return redirect('/login')->with('fail', 'Web access is not enabled for your account.');
        }

        // Allowed roles for temporary web login
        $allowedRoles = [
            'super_admin',
            'admin',
            'hr_admin',
            'finance_admin',
            'project_admin',
            'operations_admin',
            'custom_admin',
            'employee',
        ];

        $hasAllowedRole = false;

        // Primary role check using system_role_id relation
        if (method_exists($user, 'primaryRole') && $user->primaryRole) {
            if (in_array($user->primaryRole->slug, $allowedRoles, true)) {
                $hasAllowedRole = true;
            }
        }

        // Multi-role check using user_roles relation
        if (!$hasAllowedRole && method_exists($user, 'roles')) {
            $roleSlugs = $user->roles()->pluck('slug')->toArray();

            if (!empty(array_intersect($allowedRoles, $roleSlugs))) {
                $hasAllowedRole = true;
            }
        }

        // Fallback: if old system still has system_role_id only and no relations loaded
        if (
            !$hasAllowedRole &&
            isset($user->system_role_id) &&
            !empty($user->system_role_id) &&
            method_exists($user, 'primaryRole')
        ) {
            $user->loadMissing('primaryRole');

            if ($user->primaryRole && in_array($user->primaryRole->slug, $allowedRoles, true)) {
                $hasAllowedRole = true;
            }
        }

        if (!$hasAllowedRole) {
            Auth::logout();

            return redirect('/login')->with('fail', 'Access denied. You do not have permission to login here.');
        }

        // Temporary same dashboard route for both admin and employee
        return redirect()->route('dashboard');
    }
}