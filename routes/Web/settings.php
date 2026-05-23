<?php

use App\Http\Controllers\Web\HRMS\Announcement\AnnouncementsC;
use App\Http\Controllers\Web\Settings\CompanySettingsController;
use App\Http\Controllers\Web\Settings\LogsController;
use App\Http\Controllers\Web\Settings\ProfilesController;
use App\Http\Controllers\Web\Settings\PolicyChangeLogC;
use App\Http\Controllers\Web\Settings\EmployeePolicyAssignmentC;
use App\Http\Controllers\Web\Settings\NotificationRetentionC;
use App\Http\Controllers\Web\Settings\ScoreCategoriesController;
use App\Http\Controllers\Web\Settings\SystemSettingsController;
use App\Http\Controllers\Web\Settings\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Settings / Admin / Utility Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.access'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Score Categories
    |--------------------------------------------------------------------------
    */
    Route::get('/score-categories', [ScoreCategoriesController::class, 'index'])->name('score-categories');
    Route::get('/score-categories/create', [ScoreCategoriesController::class, 'create'])->name('score-categories.create');
    Route::get('/score-categories/print', [ScoreCategoriesController::class, 'print'])->name('score-categories.print');
    Route::get('/score-categories/{scoreCategory}/edit', [ScoreCategoriesController::class, 'edit'])->name('score-categories.edit');
    Route::post('/score-categories', [ScoreCategoriesController::class, 'store'])->name('score-categories.store');
    Route::put('/score-categories/{scoreCategory}', [ScoreCategoriesController::class, 'update'])->name('score-categories.update');
    Route::delete('/score-categories/{scoreCategory}', [ScoreCategoriesController::class, 'destroy'])->name('score-categories.destroy');

    /*
    |--------------------------------------------------------------------------
    | Logs
    |--------------------------------------------------------------------------
    */
    Route::get('/logs', [LogsController::class, 'index'])->name('logs');
    Route::get('/logs/print', [LogsController::class, 'print'])->name('logs.print');

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/print', [UsersController::class, 'print'])->name('users.print');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile/view', function () {
        return redirect()->route('profile.index');
    })->name('profile');

    Route::get('/profile', [ProfilesController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfilesController::class, 'update'])->name('profile.update');
    Route::post('/profile/submit', [ProfilesController::class, 'submitForVerification'])->name('profile.submit');
    Route::put('/profile/password', [ProfilesController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/hrms/employee/profile-image/{employee}', [ProfilesController::class, 'profileImage'])->name('employee.profile-image');

    Route::get('/settings/policy-change-logs', [PolicyChangeLogC::class, 'index'])->name('hrms.policy_change_logs.index');
    Route::get('/settings/employee-policy-assignments', [EmployeePolicyAssignmentC::class, 'index'])
        ->middleware('permission:settings.employee_policy_assignments.view|settings.employee_policy_assignments.manage')
        ->name('hrms.employee_policy_assignments.index');
    Route::post('/settings/employee-policy-assignments', [EmployeePolicyAssignmentC::class, 'store'])
        ->middleware('permission:settings.employee_policy_assignments.manage')
        ->name('hrms.employee_policy_assignments.store');
    Route::put('/settings/employee-policy-assignments/{id}', [EmployeePolicyAssignmentC::class, 'update'])
        ->middleware('permission:settings.employee_policy_assignments.manage')
        ->name('hrms.employee_policy_assignments.update');

    Route::get('/settings/notification-retention', [NotificationRetentionC::class, 'index'])->name('settings.notification_retention.index');
    Route::get('/settings/notification-retention-alias', [NotificationRetentionC::class, 'index'])->name('settings.notification-retention.index');
    Route::post('/settings/notification-retention', [NotificationRetentionC::class, 'update'])->name('settings.notification-retention.update');
});

Route::middleware(['auth', 'web.admin.access'])->group(function () {
    Route::get('/settings/system', [SystemSettingsController::class, 'index'])->name('settings.system.index');
    Route::put('/settings/system', [SystemSettingsController::class, 'update'])->name('settings.system.update');

    Route::get('/settings/company', [CompanySettingsController::class, 'index'])->name('settings.company.index');
    Route::put('/settings/company', [CompanySettingsController::class, 'update'])->name('settings.company.update');
});
