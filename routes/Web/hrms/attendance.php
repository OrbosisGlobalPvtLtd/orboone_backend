<?php

use App\Http\Controllers\Web\HRMS\Attendance\AttendancesC;
use App\Http\Controllers\Web\HRMS\Attendance\AttendancePolicyOverrideC;
use App\Http\Controllers\Web\HRMS\Attendance\AttendanceRegularizationC;
use App\Http\Controllers\Web\HRMS\Attendance\AttendanceViolationC;
use App\Http\Controllers\Web\HRMS\Attendance\HolidayWorkRequestC;
use App\Http\Controllers\Web\HRMS\Attendance\MonthlyAttendanceSummaryC;
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
        Route::get('/', [AttendancesC::class, 'index'])->middleware('permission:attendance.dashboard.view')->name('index');

        Route::get('/daily', [AttendancesC::class, 'daily'])->middleware('permission:attendance.records.view_all|attendance.my.view')->name('daily');
        Route::get('/record', [AttendancesC::class, 'attendanceRecord'])->middleware('permission:attendance.records.view_all')->name('record');
        Route::get('/pending-approval', [AttendancesC::class, 'pendingApproval'])->middleware('permission:attendance.blocked.view')->name('pending-approval');
        Route::get('/monthly-report', [AttendancesC::class, 'monthlyReport'])->middleware('permission:attendance.monthly_report.view_all|attendance.monthly_report.view_team|attendance.monthly_report.view_own|attendance.monthly_report.view')->name('monthly-report');

        Route::get('/print', [AttendancesC::class, 'print'])->middleware('permission:attendance.export')->name('print');
        Route::get('/export-pdf', [AttendancesC::class, 'exportPdf'])->middleware('permission:attendance.export')->name('export-pdf');

        Route::post('/', [AttendancesC::class, 'store'])->name('store');
        Route::put('/', [AttendancesC::class, 'update'])->name('update');
        Route::delete('/', [AttendancesC::class, 'destroy'])->name('destroy');

        Route::post('/unlock', [AttendancesC::class, 'unlock'])->middleware('permission:attendance.blocked.unlock')->name('unlock');
        Route::post('/admin/punch-in', [AttendancesC::class, 'adminPunchIn'])->name('admin.punch-in');
        Route::post('/admin/punch-out', [AttendancesC::class, 'adminPunchOut'])->name('admin.punch-out');
    });

Route::middleware(['auth', 'check.access'])
    ->get('/my-attendance', [AttendancesC::class, 'attendanceRecord'])
    ->middleware('permission:attendance.my.view')
    ->name('hrms.attendance.my');

Route::middleware(['auth', 'check.access'])
    ->prefix('attendance-settings')
    ->name('attendance.')
    ->group(function () {
        Route::get('/rules', [AttendancesC::class, 'rules'])->middleware('permission:attendance.rules.manage')->name('rules.index');
        Route::put('/rules/{attendanceTime}', [AttendancesC::class, 'updateRule'])->middleware('permission:attendance.rules.manage')->name('rules.update');
        Route::post('/policy-rules', [AttendancesC::class, 'storePolicyRule'])->middleware('permission:attendance.rules.manage')->name('policy_rules.store');
        Route::put('/policy-rules/{attendancePolicyRule}', [AttendancesC::class, 'updatePolicyRule'])->middleware('permission:attendance.rules.manage')->name('policy_rules.update');

        Route::get('/types', [AttendancesC::class, 'types'])->middleware('permission:attendance.types.manage')->name('types.index');
        Route::post('/types', [AttendancesC::class, 'storeType'])->middleware('permission:attendance.types.manage')->name('types.store');
        Route::put('/types/{attendanceType}', [AttendancesC::class, 'updateType'])->middleware('permission:attendance.types.manage')->name('types.update');
        Route::delete('/types/{attendanceType}', [AttendancesC::class, 'destroyType'])->middleware('permission:attendance.types.manage')->name('types.destroy');
    });

Route::middleware(['auth', 'check.access'])
    ->prefix('hrms/attendance')
    ->name('hrms.attendance.')
    ->group(function () {
        Route::get('/regularizations', [AttendanceRegularizationC::class, 'index'])->middleware('permission:attendance.regularization.view_all|attendance.regularization.view_team|attendance.regularization.view_own|attendance.regularization.view')->name('regularizations.index');
        Route::post('/regularizations', [AttendanceRegularizationC::class, 'store'])->middleware('permission:attendance.regularization.create')->name('regularizations.store');
        Route::put('/regularizations/{id}', [AttendanceRegularizationC::class, 'update'])->middleware('permission:attendance.regularization.create')->name('regularizations.update');
        Route::delete('/regularizations/{id}', [AttendanceRegularizationC::class, 'destroy'])->middleware('permission:attendance.regularization.create')->name('regularizations.destroy');
        Route::post('/regularizations/{id}/approve', [AttendanceRegularizationC::class, 'approve'])->middleware('permission:attendance.regularization.approve')->name('regularizations.approve');
        Route::post('/regularizations/{id}/reject', [AttendanceRegularizationC::class, 'reject'])->middleware('permission:attendance.regularization.reject')->name('regularizations.reject');

        Route::get('/holiday-work', [HolidayWorkRequestC::class, 'index'])->middleware('permission:attendance.holiday_work.view|attendance.holiday_work.manage')->name('holiday_work.index');
        Route::post('/holiday-work', [HolidayWorkRequestC::class, 'store'])->middleware('permission:attendance.holiday_work.manage')->name('holiday_work.store');
        Route::put('/holiday-work/{id}', [HolidayWorkRequestC::class, 'update'])->middleware('permission:attendance.holiday_work.manage')->name('holiday_work.update');
        Route::delete('/holiday-work/{id}', [HolidayWorkRequestC::class, 'destroy'])->middleware('permission:attendance.holiday_work.manage')->name('holiday_work.destroy');
        Route::post('/holiday-work/{id}/approve', [HolidayWorkRequestC::class, 'approve'])->middleware('permission:attendance.holiday_work.manage')->name('holiday_work.approve');
        Route::post('/holiday-work/{id}/reject', [HolidayWorkRequestC::class, 'reject'])->middleware('permission:attendance.holiday_work.manage')->name('holiday_work.reject');

        Route::get('/monthly-summary', [MonthlyAttendanceSummaryC::class, 'index'])->middleware('permission:attendance.monthly_summary.view')->name('monthly_summary.index');
        Route::post('/monthly-summary/{id}/lock', [MonthlyAttendanceSummaryC::class, 'lock'])->middleware('permission:attendance.monthly_summary.view')->name('monthly_summary.lock');
        Route::post('/monthly-summary/{id}/unlock', [MonthlyAttendanceSummaryC::class, 'unlock'])->middleware('permission:attendance.monthly_summary.view')->name('monthly_summary.unlock');

        Route::get('/violations', [AttendanceViolationC::class, 'index'])->name('violations.index');

        Route::get('/policy-overrides', [AttendancePolicyOverrideC::class, 'index'])->middleware('permission:attendance.policy_overrides.manage')->name('policy_overrides.index');
        Route::post('/policy-overrides', [AttendancePolicyOverrideC::class, 'store'])->middleware('permission:attendance.policy_overrides.manage')->name('policy_overrides.store');
        Route::put('/policy-overrides/{id}', [AttendancePolicyOverrideC::class, 'update'])->middleware('permission:attendance.policy_overrides.manage')->name('policy_overrides.update');
    });
