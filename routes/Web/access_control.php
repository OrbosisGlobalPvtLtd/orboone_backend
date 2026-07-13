<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AccessControl\RoleC;
use App\Http\Controllers\Web\AccessControl\PermissionC;
use App\Http\Controllers\Web\AccessControl\AdminUserC;
use App\Http\Controllers\Web\AccessControl\RolePermissionC;
use App\Http\Controllers\Web\AccessControl\RoleMenuC;

Route::middleware(['auth', 'web.admin.access'])->group(function () {
    Route::get('/roles', [RoleC::class, 'index'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.index');
    Route::get('/roles/create', [RoleC::class, 'create'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.create');
    Route::post('/roles', [RoleC::class, 'store'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.store');
    Route::get('/roles/print', [RoleC::class, 'print'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.print');
    Route::get('/roles/{role}', [RoleC::class, 'show'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.show');
    Route::get('/roles/{role}/edit', [RoleC::class, 'edit'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.edit');
    Route::put('/roles/{role}', [RoleC::class, 'update'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.update');
    Route::delete('/roles/{role}', [RoleC::class, 'destroy'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('roles.destroy');

    Route::get('/permissions', [PermissionC::class, 'index'])
        ->middleware('permission:permissions.manage|access.permissions.manage')
        ->name('permissions.index');
    Route::get('/permissions/create', [PermissionC::class, 'create'])
        ->middleware('permission:permissions.manage|access.permissions.manage')
        ->name('permissions.create');
    Route::post('/permissions', [PermissionC::class, 'store'])
        ->middleware('permission:permissions.manage|access.permissions.manage')
        ->name('permissions.store');
    Route::get('/permissions/{permission}/edit', [PermissionC::class, 'edit'])
        ->middleware('permission:permissions.manage|access.permissions.manage')
        ->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionC::class, 'update'])
        ->middleware('permission:permissions.manage|access.permissions.manage')
        ->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionC::class, 'destroy'])
        ->middleware('permission:permissions.manage|access.permissions.manage')
        ->name('permissions.destroy');

    Route::get('/admins', [AdminUserC::class, 'index'])
        ->middleware('permission:admins.manage|access.admins.manage')
        ->name('admins.index');
    Route::get('/admins/create', [AdminUserC::class, 'create'])
        ->middleware('permission:admins.manage|access.admins.manage')
        ->name('admins.create');
    Route::post('/admins', [AdminUserC::class, 'store'])
        ->middleware('permission:admins.manage|access.admins.manage')
        ->name('admins.store');
    Route::get('/admins/{admin}/edit', [AdminUserC::class, 'edit'])
        ->middleware('permission:admins.manage|access.admins.manage')
        ->name('admins.edit');
    Route::put('/admins/{admin}', [AdminUserC::class, 'update'])
        ->middleware('permission:admins.manage|access.admins.manage')
        ->name('admins.update');
    Route::delete('/admins/{admin}', [AdminUserC::class, 'destroy'])
        ->middleware('permission:admins.manage|access.admins.manage')
        ->name('admins.destroy');

    Route::get('/role-permissions', [RolePermissionC::class, 'index'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('role_permissions.index');
    Route::get('/role-permissions/{role}/edit', [RolePermissionC::class, 'edit'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('role_permissions.edit');
    Route::put('/role-permissions/{role}', [RolePermissionC::class, 'update'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('role_permissions.update');

    Route::get('/role-menus', [RoleMenuC::class, 'index'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('role_menus.index');
    Route::get('/role-menus/{role}/edit', [RoleMenuC::class, 'edit'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('role_menus.edit');
    Route::put('/role-menus/{role}', [RoleMenuC::class, 'update'])
        ->middleware('permission:roles.manage|access.roles.manage')
        ->name('role_menus.update');
});
