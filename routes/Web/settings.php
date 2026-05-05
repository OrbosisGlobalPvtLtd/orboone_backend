<?php

use App\Http\Controllers\Web\HRMS\Announcement\AnnouncementsC;
use App\Http\Controllers\Web\Settings\CompanySettingsController;
use App\Http\Controllers\Web\Settings\LogsController;
use App\Http\Controllers\Web\Settings\ProfilesController;
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
    | Announcements
    |--------------------------------------------------------------------------
    */
    Route::get('/announcements', [AnnouncementsC::class, 'index'])->name('announcements');
    Route::get('/announcements/create', [AnnouncementsC::class, 'create'])->name('announcements.create');
    Route::get('/announcements/print', [AnnouncementsC::class, 'print'])->name('announcements.print');
    Route::get('/announcements/{announcement}', [AnnouncementsC::class, 'show'])->name('announcements.show');
    Route::get('/announcements/{announcement}/edit', [AnnouncementsC::class, 'edit'])->name('announcements.edit');
    Route::post('/announcements', [AnnouncementsC::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AnnouncementsC::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementsC::class, 'destroy'])->name('announcements.destroy');

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
    Route::put('/profile/password', [ProfilesController::class, 'updatePassword'])->name('profile.password.update');
});

Route::middleware(['auth', 'web.admin.access'])->group(function () {
    Route::get('/settings/system', [SystemSettingsController::class, 'index'])->name('settings.system.index');
    Route::put('/settings/system', [SystemSettingsController::class, 'update'])->name('settings.system.update');

    Route::get('/settings/company', [CompanySettingsController::class, 'index'])->name('settings.company.index');
    Route::put('/settings/company', [CompanySettingsController::class, 'update'])->name('settings.company.update');
});
