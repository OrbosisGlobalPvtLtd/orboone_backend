<?php

use App\Http\Controllers\Web\HRMS\Attendance\AttendancesC;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRMS Attendance Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.access', 'check.profile.complete'])
    ->prefix('attendances')
    ->name('attendances.')
    ->group(function () {
        Route::get('/', [AttendancesC::class, 'index'])->name('index');

        Route::get('/daily', [AttendancesC::class, 'daily'])->name('daily');
        Route::get('/pending-approval', [AttendancesC::class, 'pendingApproval'])->name('pending-approval');
        Route::get('/monthly-report', [AttendancesC::class, 'monthlyReport'])->name('monthly-report');

        Route::get('/print', [AttendancesC::class, 'print'])->name('print');
        Route::get('/export-pdf', [AttendancesC::class, 'exportPdf'])->name('export-pdf');

        Route::post('/', [AttendancesC::class, 'store'])->name('store');
        Route::put('/', [AttendancesC::class, 'update'])->name('update');

        Route::post('/unlock', [AttendancesC::class, 'unlock'])->name('unlock');
        Route::post('/admin/punch-in', [AttendancesC::class, 'adminPunchIn'])->name('admin.punch-in');
        Route::post('/admin/punch-out', [AttendancesC::class, 'adminPunchOut'])->name('admin.punch-out');
    });

Route::middleware(['auth', 'check.access'])
    ->prefix('attendance-settings')
    ->name('attendance.')
    ->group(function () {
        Route::get('/rules', [AttendancesC::class, 'rules'])->name('rules.index');
        Route::put('/rules/{attendanceTime}', [AttendancesC::class, 'updateRule'])->name('rules.update');

        Route::get('/types', [AttendancesC::class, 'types'])->name('types.index');
        Route::post('/types', [AttendancesC::class, 'storeType'])->name('types.store');
        Route::put('/types/{attendanceType}', [AttendancesC::class, 'updateType'])->name('types.update');
        Route::delete('/types/{attendanceType}', [AttendancesC::class, 'destroyType'])->name('types.destroy');
    });
