<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HRMS\Attendance\AttendanceController;
use App\Http\Controllers\Api\V1\HRMS\Attendance\AttendanceRegularizationController;
use App\Http\Controllers\Api\V1\HRMS\Attendance\AttendanceConfigController;
use App\Http\Controllers\Api\V1\HRMS\Attendance\HolidayWorkRequestController;
use App\Http\Controllers\Api\V1\HRMS\Attendance\WfhController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('hrms/attendance')->group(function () {
        Route::middleware('employee.user')->group(function () {
            Route::post('/punch-in', [AttendanceController::class, 'clockIn']);
            Route::post('/punch-out', [AttendanceController::class, 'clockOut']);
            Route::get('/profile-status', [AttendanceController::class, 'profileStatus']);
            Route::get('/today', [AttendanceController::class, 'today']);
            Route::get('/today-status', [AttendanceController::class, 'todayStatus']);
            Route::get('/history', [AttendanceController::class, 'history']);
            Route::get('/list', [AttendanceController::class, 'getAttendance']);
            Route::get('/monthly', [AttendanceController::class, 'monthly']);
        });
        Route::get('/rules', [AttendanceController::class, 'rules']);
        Route::post('/unlock', [AttendanceController::class, 'unlock']);
    });

    Route::middleware('employee.user')->group(function () {
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
        Route::get('/attendance/list', [AttendanceController::class, 'getAttendance']);
        Route::get('/attendance/calendar', [AttendanceController::class, 'getMyAttendanceCalendar']);
        Route::post('/attendance/manual-update', [AttendanceController::class, 'manualAttendanceUpdate']);
        Route::get('/attendance/reports/late-early', [AttendanceController::class, 'lateEarlyReport']);

        Route::post('/attendance/regularize', [AttendanceRegularizationController::class, 'requestRegularization']);
        Route::get('/attendance/regularize/my-requests', [AttendanceRegularizationController::class, 'myRegularizationRequests']);
        Route::get('/attendance/regularize/{id}', [AttendanceRegularizationController::class, 'showRegularizationRequest']);
        Route::post('/attendance/regularize/{id}/cancel', [AttendanceRegularizationController::class, 'cancelRegularizationRequest']);
        Route::post('/attendance/regularization', [AttendanceRegularizationController::class, 'requestRegularization']);
        Route::get('/attendance/regularization/my-requests', [AttendanceRegularizationController::class, 'myRegularizationRequests']);
        Route::get('/attendance/regularization/{id}', [AttendanceRegularizationController::class, 'showRegularizationRequest']);
        Route::post('/attendance/regularization/{id}/cancel', [AttendanceRegularizationController::class, 'cancelRegularizationRequest']);

        Route::post('/attendance/holiday-work', [HolidayWorkRequestController::class, 'store']);
        Route::get('/attendance/holiday-work/my-requests', [HolidayWorkRequestController::class, 'index']);
        Route::get('/attendance/holiday-work/{id}', [HolidayWorkRequestController::class, 'show']);
    });

    Route::post('/attendance/regularize/{id}/approve', [AttendanceRegularizationController::class, 'approveRegularization']);
    Route::post('/attendance/regularize/{id}/reject', [AttendanceRegularizationController::class, 'rejectRegularization']);
    Route::post('/attendance/regularization/{id}/approve', [AttendanceRegularizationController::class, 'approveRegularization']);
    Route::post('/attendance/regularization/{id}/reject', [AttendanceRegularizationController::class, 'rejectRegularization']);

    Route::post('/attendance/holiday-work/{id}/approve', [HolidayWorkRequestController::class, 'approve']);
    Route::post('/attendance/holiday-work/{id}/reject', [HolidayWorkRequestController::class, 'reject']);

    Route::post('/attendance/config/geofence', [AttendanceConfigController::class, 'configureGeofence']);
    Route::post('/attendance/config/allowed-ips', [AttendanceConfigController::class, 'configureAllowedIps']);

    Route::middleware('employee.user')->group(function () {
        Route::get('/hrms/wfh/policy', [WfhController::class, 'policy']);
        Route::get('/hrms/wfh/balance', [WfhController::class, 'balance']);
        Route::get('/hrms/wfh/calculate-days', [WfhController::class, 'calculateDays']);
        Route::get('/hrms/wfh/history', [WfhController::class, 'history']);
        Route::post('/hrms/wfh/apply', [WfhController::class, 'apply']);
        Route::post('/hrms/wfh/{id}/cancel', [WfhController::class, 'cancel']);
    });

    Route::get('/hrms/wfh/requests', [WfhController::class, 'requests']);
    Route::post('/hrms/wfh/{id}/approve', [WfhController::class, 'approve']);
    Route::post('/hrms/wfh/{id}/reject', [WfhController::class, 'reject']);
    Route::post('/hrms/wfh/{id}/mark-lwp', [WfhController::class, 'markLwp']);
});
