<?php

use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ScoreCategoriesController;
use App\Http\Controllers\UsersController;
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
    Route::get('/announcements', [AnnouncementsController::class, 'index'])->name('announcements');
    Route::get('/announcements/create', [AnnouncementsController::class, 'create'])->name('announcements.create');
    Route::get('/announcements/print', [AnnouncementsController::class, 'print'])->name('announcements.print');
    Route::get('/announcements/{announcement}', [AnnouncementsController::class, 'show'])->name('announcements.show');
    Route::get('/announcements/{announcement}/edit', [AnnouncementsController::class, 'edit'])->name('announcements.edit');
    Route::post('/announcements', [AnnouncementsController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AnnouncementsController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementsController::class, 'destroy'])->name('announcements.destroy');

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
    | Roles
    |--------------------------------------------------------------------------
    */
    Route::get('/roles', [RolesController::class, 'index'])->name('roles');
    Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::get('/roles/print', [RolesController::class, 'print'])->name('roles.print');
    Route::get('/roles/{role}', [RolesController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfilesController::class, 'index'])->name('profile');
    Route::put('/profile/{user}', [ProfilesController::class, 'update'])->name('profile.update');
});