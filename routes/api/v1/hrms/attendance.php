<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HRMS\Attendance\AttendanceController;
use App\Http\Controllers\Api\V1\HRMS\Attendance\AttendanceRegularizationController;
use App\Http\Controllers\Api\V1\HRMS\Attendance\AttendanceConfigController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('hrms/attendance')->group(function () {
        Route::post('/punch-in', [AttendanceController::class, 'clockIn']);
        Route::post('/punch-out', [AttendanceController::class, 'clockOut']);
        Route::get('/profile-status', [AttendanceController::class, 'profileStatus']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/today-status', [AttendanceController::class, 'todayStatus']);
        Route::get('/history', [AttendanceController::class, 'history']);
        Route::get('/list', [AttendanceController::class, 'getAttendance']);
        Route::get('/monthly', [AttendanceController::class, 'monthly']);
        Route::get('/rules', [AttendanceController::class, 'rules']);
        Route::post('/unlock', [AttendanceController::class, 'unlock']);
    });

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendance/list', [AttendanceController::class, 'getAttendance']);
    Route::get('/attendance/calendar', [AttendanceController::class, 'getMyAttendanceCalendar']);
    Route::post('/attendance/manual-update', [AttendanceController::class, 'manualAttendanceUpdate']);
    Route::get('/attendance/reports/late-early', [AttendanceController::class, 'lateEarlyReport']);

    Route::post('/attendance/regularize', [AttendanceRegularizationController::class, 'requestRegularization']);
    Route::get('/attendance/regularize/my-requests', [AttendanceRegularizationController::class, 'myRegularizationRequests']);
    Route::post('/attendance/regularize/{id}/approve', [AttendanceRegularizationController::class, 'approveRegularization']);

    Route::post('/attendance/config/geofence', [AttendanceConfigController::class, 'configureGeofence']);
    Route::post('/attendance/config/allowed-ips', [AttendanceConfigController::class, 'configureAllowedIps']);
});
