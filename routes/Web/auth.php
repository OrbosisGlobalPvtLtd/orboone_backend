<?php

use App\Http\Controllers\Web\HRMS\Employee\RecruitmentCandidatesController;
use App\Http\Controllers\Web\Auth\EmployeePasswordSetupController;
use App\Http\Controllers\Web\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Auth\WelcomeController;
use App\Http\Controllers\Web\Auth\LoginC;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::middleware('guest')->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

    Route::get('/login', [LoginC::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginC::class, 'login'])->name('login.submit');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.otp.send');
    Route::get('/forgot-password/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.otp.form');
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset.update');

    Route::get('/welcome/announcements', [WelcomeController::class, 'announcements'])->name('welcome.announcements');
    Route::get('/welcome/announcements/{announcement}', [WelcomeController::class, 'announcementShow'])->name('welcome.announcements.show');
    Route::get('/welcome/recruitments', [WelcomeController::class, 'recruitments'])->name('welcome.recruitments');
    Route::get('/welcome/recruitments/{recruitment}', [WelcomeController::class, 'recruitmentShow'])->name('welcome.recruitments.show');
});

Route::get('/employee-password/setup/{token}', [EmployeePasswordSetupController::class, 'show'])
    ->middleware('guest')
    ->name('employee.password.setup');
Route::post('/employee-password/setup/{token}', [EmployeePasswordSetupController::class, 'update'])
    ->middleware('guest')
    ->name('employee.password.setup.update');

Route::post('/logout', [LoginC::class, 'logout'])->middleware('auth')->name('logout');

Route::post('/recruitment-candidates', [RecruitmentCandidatesController::class, 'store'])
    ->name('recruitment-candidates.store');

Route::get('/debug-prod-db-9824', function () {
    $results = [];

    // 1. Check admin users role slugs
    $results['admin_users'] = DB::table('users as u')
        ->leftJoin('roles as r', 'r.id', '=', 'u.system_role_id')
        ->whereIn('u.email', [
            'superadmin@orbosis.com',
            'admin@orbosis.com',
            'hradmin@orbosis.com',
            'financeadmin@orbosis.com',
            'projectadmin@orbosis.com',
            'operationsadmin@orbosis.com'
        ])
        ->select('u.id', 'u.name', 'u.email', 'u.system_role_id', 'r.name as role_name', 'r.slug as role_slug')
        ->get()
        ->toArray();

    // 2. Check if admin users are wrongly linked to employees_new
    $results['linked_employees'] = DB::table('employees_new')
        ->whereIn('user_id', [1, 2, 3, 9, 10, 11, 12, 13])
        ->select('id', 'user_id', 'employee_code', 'employee_stage', 'employment_status', 'is_active')
        ->get()
        ->toArray();

    // 3. Check employee profiles for admin users
    $results['employee_profiles'] = DB::table('employee_profiles as ep')
        ->join('employees_new as e', 'e.id', '=', 'ep.employee_id')
        ->whereIn('e.user_id', [1, 2, 3, 9, 10, 11, 12, 13])
        ->select('ep.*')
        ->get()
        ->toArray();

    // 4. Compare production role slugs
    $results['roles'] = DB::table('roles')
        ->select('id', 'name', 'slug')
        ->orderBy('id')
        ->get()
        ->toArray();

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});

