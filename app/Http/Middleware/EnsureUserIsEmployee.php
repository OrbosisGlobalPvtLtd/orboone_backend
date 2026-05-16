<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $userId = Auth::id();
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

        if (!$employee) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Employee profile is not linked with this account.', 'status' => false], 403);
            }
            abort(403, 'Employee profile is not linked with this account.');
        }

        return $next($request);
    }
}
