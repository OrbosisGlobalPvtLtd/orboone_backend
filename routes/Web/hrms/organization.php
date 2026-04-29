<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HRMS\Employee\OrganizationC;
use App\Http\Controllers\Web\HRMS\Employee\DepartmentC;
use App\Http\Controllers\Web\HRMS\Employee\DesignationC;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->group(function () {

        Route::get('/organization', [OrganizationC::class, 'index'])
            ->middleware('permission:departments.manage')
            ->name('organization.index');

        Route::post('/departments', [DepartmentC::class, 'store'])
            ->middleware('permission:departments.manage')
            ->name('departments.store');

        Route::put('/departments/{id}', [DepartmentC::class, 'update'])
            ->middleware('permission:departments.manage')
            ->name('departments.update');

        Route::delete('/departments/{id}', [DepartmentC::class, 'destroy'])
            ->middleware('permission:departments.manage')
            ->name('departments.destroy');

        Route::post('/designations', [DesignationC::class, 'store'])
            ->middleware('permission:designations.manage')
            ->name('designations.store');

        Route::put('/designations/{id}', [DesignationC::class, 'update'])
            ->middleware('permission:designations.manage')
            ->name('designations.update');

        Route::delete('/designations/{id}', [DesignationC::class, 'destroy'])
            ->middleware('permission:designations.manage')
            ->name('designations.destroy');

        Route::get('/designations/by-department/{id}', [DesignationC::class, 'getByDepartment'])
            ->middleware('permission:employees.view')
            ->name('designations.by-department');
    });