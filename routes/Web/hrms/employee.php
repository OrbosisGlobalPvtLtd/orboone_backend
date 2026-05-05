<?php

use App\Http\Controllers\Web\HRMS\Employee\EmployeeC;
use App\Http\Controllers\Web\HRMS\Employee\EmployeeScoresC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->name('hrms.')
    ->group(function () {
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeC::class, 'index'])
                ->middleware('permission:employees.view')
                ->name('index');

            Route::get('/create', [EmployeeC::class, 'create'])
                ->middleware('permission:employees.create')
                ->name('create');

            Route::post('/', [EmployeeC::class, 'store'])
                ->middleware('permission:employees.create')
                ->name('store');

            Route::get('/pending-profiles', [EmployeeC::class, 'pendingProfiles'])
                ->middleware('permission:employees.view')
                ->name('pending_profiles');

            Route::get('/probation-internship', [EmployeeC::class, 'probationInternship'])
                ->middleware('permission:employees.view')
                ->name('probation_internship');

            Route::get('/exit', [EmployeeC::class, 'exitEmployees'])
                ->middleware('permission:employees.view')
                ->name('exit');

            Route::get('/reporting-structure', [EmployeeC::class, 'reportingStructure'])
                ->middleware('permission:employees.view')
                ->name('reporting_structure');

            Route::post('/{employee}/mark-permanent', [EmployeeC::class, 'markPermanent'])
                ->middleware('permission:employees.update')
                ->name('probation.mark_permanent');

            Route::post('/{employee}/extend-internship', [EmployeeC::class, 'extendInternship'])
                ->middleware('permission:employees.update')
                ->name('internship.extend');

            Route::post('/{employee}/complete-internship', [EmployeeC::class, 'completeInternship'])
                ->middleware('permission:employees.update')
                ->name('internship.complete');

            Route::post('/{employee}/mark-exit', [EmployeeC::class, 'markExit'])
                ->middleware('permission:employees.update')
                ->name('exit.mark');

            Route::get('/export', [EmployeeC::class, 'print'])
                ->middleware('permission:employees.view')
                ->name('export');

            Route::get('/get-designations/{department}', [EmployeeC::class, 'getDesignationsByDepartment'])
                ->middleware('permission:employees.view')
                ->name('get_designations');

            Route::get('/{employee}/manage', [EmployeeC::class, 'manage'])
                ->middleware('permission:employees.view')
                ->name('manage');

            Route::put('/{employee}/manage', [EmployeeC::class, 'manageUpdate'])
                ->middleware('permission:employees.update')
                ->name('manage.update');

            Route::get('/{employee}/edit', [EmployeeC::class, 'edit'])
                ->middleware('permission:employees.update')
                ->name('edit');

            Route::put('/{employee}', [EmployeeC::class, 'update'])
                ->middleware('permission:employees.update')
                ->name('update');

            Route::delete('/{employee}', [EmployeeC::class, 'destroy'])
                ->middleware('permission:employees.update')
                ->name('destroy');

            Route::get('/{employee}/complete-profile', [EmployeeC::class, 'completeProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.complete');

            Route::post('/{employee}/complete-profile', [EmployeeC::class, 'storeProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.store');

            Route::get('/{employee}/profile-view', [EmployeeC::class, 'viewProfile'])
                ->middleware('permission:employees.view')
                ->name('profile.view');

            Route::get('/{employee}/profile-edit', [EmployeeC::class, 'editProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.edit');

            Route::post('/{employee}/profile-update', [EmployeeC::class, 'updateProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.update');

            Route::post('/{employee}/profile-approve', [EmployeeC::class, 'approveProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.approve');

            Route::post('/{employee}/profile-reject', [EmployeeC::class, 'rejectProfile'])
                ->middleware('permission:employees.update')
                ->name('profile.reject');

            Route::get('/{employee}', [EmployeeC::class, 'show'])
                ->middleware('permission:employees.view')
                ->name('show');
        });

        Route::prefix('employees-performance-score')
            ->name('employees.performance_scores.')
            ->group(function () {
                Route::get('/', [EmployeeScoresC::class, 'index'])
                    ->middleware('permission:employees.view')
                    ->name('index');

                Route::get('/create', [EmployeeScoresC::class, 'create'])
                    ->middleware('permission:employees.update')
                    ->name('create');

                Route::post('/', [EmployeeScoresC::class, 'store'])
                    ->middleware('permission:employees.update')
                    ->name('store');

                Route::get('/export', [EmployeeScoresC::class, 'print'])
                    ->middleware('permission:employees.view')
                    ->name('export');

                Route::get('/{employeeScore}', [EmployeeScoresC::class, 'show'])
                    ->middleware('permission:employees.view')
                    ->name('show');

                Route::get('/{employeeScore}/edit', [EmployeeScoresC::class, 'edit'])
                    ->middleware('permission:employees.update')
                    ->name('edit');

                Route::put('/{employeeScore}', [EmployeeScoresC::class, 'update'])
                    ->middleware('permission:employees.update')
                    ->name('update');

                Route::delete('/{employeeScore}', [EmployeeScoresC::class, 'destroy'])
                    ->middleware('permission:employees.update')
                    ->name('destroy');
            });
    });
