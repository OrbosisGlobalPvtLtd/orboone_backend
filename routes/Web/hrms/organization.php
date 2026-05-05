<?php

use App\Http\Controllers\Web\HRMS\Employee\DepartmentC;
use App\Http\Controllers\Web\HRMS\Employee\DepartmentsC;
use App\Http\Controllers\Web\HRMS\Employee\DesignationC;
use App\Http\Controllers\Web\HRMS\Employee\OrganizationC;
use App\Http\Controllers\Web\HRMS\Employee\PositionsC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->name('hrms.')
    ->group(function () {
        Route::get('/organization', [OrganizationC::class, 'index'])
            ->middleware('permission:departments.manage')
            ->name('organization.index');

        Route::post('/organization/departments', [DepartmentC::class, 'store'])
            ->middleware('permission:departments.manage')
            ->name('organization.departments.store');

        Route::put('/organization/departments/{id}', [DepartmentC::class, 'update'])
            ->middleware('permission:departments.manage')
            ->name('organization.departments.update');

        Route::delete('/organization/departments/{id}', [DepartmentC::class, 'destroy'])
            ->middleware('permission:departments.manage')
            ->name('organization.departments.destroy');

        Route::post('/organization/designations', [DesignationC::class, 'store'])
            ->middleware('permission:designations.manage')
            ->name('organization.designations.store');

        Route::put('/organization/designations/{id}', [DesignationC::class, 'update'])
            ->middleware('permission:designations.manage')
            ->name('organization.designations.update');

        Route::delete('/organization/designations/{id}', [DesignationC::class, 'destroy'])
            ->middleware('permission:designations.manage')
            ->name('organization.designations.destroy');

        Route::get('/designations/by-department/{id}', [DesignationC::class, 'getByDepartment'])
            ->middleware('permission:employees.view')
            ->name('designations.by_department');

        Route::prefix('departments')->name('departments.')->group(function () {
            Route::get('/', [DepartmentsC::class, 'index'])
                ->middleware('permission:departments.manage')
                ->name('index');

            Route::get('/create', [DepartmentsC::class, 'create'])
                ->middleware('permission:departments.manage')
                ->name('create');

            Route::post('/', [DepartmentsC::class, 'store'])
                ->middleware('permission:departments.manage')
                ->name('store');

            Route::get('/export', [DepartmentsC::class, 'print'])
                ->middleware('permission:departments.manage')
                ->name('export');

            Route::get('/{department}', [DepartmentsC::class, 'show'])
                ->middleware('permission:departments.manage')
                ->name('show');

            Route::get('/{department}/edit', [DepartmentsC::class, 'edit'])
                ->middleware('permission:departments.manage')
                ->name('edit');

            Route::put('/{department}', [DepartmentsC::class, 'update'])
                ->middleware('permission:departments.manage')
                ->name('update');

            Route::delete('/{department}', [DepartmentsC::class, 'destroy'])
                ->middleware('permission:departments.manage')
                ->name('destroy');
        });

        Route::prefix('designations')->name('designations.')->group(function () {
            Route::get('/', [PositionsC::class, 'index'])
                ->middleware('permission:designations.manage')
                ->name('index');

            Route::get('/create', [PositionsC::class, 'create'])
                ->middleware('permission:designations.manage')
                ->name('create');

            Route::post('/', [PositionsC::class, 'store'])
                ->middleware('permission:designations.manage')
                ->name('store');

            Route::get('/export', [PositionsC::class, 'print'])
                ->middleware('permission:designations.manage')
                ->name('export');

            Route::get('/{position}', [PositionsC::class, 'show'])
                ->middleware('permission:designations.manage')
                ->name('show');

            Route::get('/{position}/edit', [PositionsC::class, 'edit'])
                ->middleware('permission:designations.manage')
                ->name('edit');

            Route::put('/{position}', [PositionsC::class, 'update'])
                ->middleware('permission:designations.manage')
                ->name('update');

            Route::delete('/{position}', [PositionsC::class, 'destroy'])
                ->middleware('permission:designations.manage')
                ->name('destroy');
        });
    });
