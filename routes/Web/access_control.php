<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AccessControl\RoleController;
use App\Http\Controllers\Web\AccessControl\PermissionController;
use App\Http\Controllers\Web\AccessControl\AdminUserController;

Route::middleware(['auth', 'web.admin.access'])->prefix('access-control')->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.manage')
        ->name('roles.index');

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.manage')
        ->name('permissions.index');

    Route::get('/admins', [AdminUserController::class, 'index'])
        ->middleware('permission:admins.manage')
        ->name('admins.index');
});