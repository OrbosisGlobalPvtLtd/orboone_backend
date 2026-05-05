<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HRMS\Leave\LeaveController;
use App\Http\Controllers\Api\V1\HRMS\Leave\LeaveTypeController;
use App\Http\Controllers\Api\V1\HRMS\Leave\LeaveBalanceController;
use App\Http\Controllers\Api\V1\HRMS\Leave\LeaveApprovalController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/leave/types', [LeaveTypeController::class, 'createLeaveType']);
    Route::get('/leave/types', [LeaveTypeController::class, 'listLeaveTypes']);

    Route::get('/leave/my-balance', [LeaveBalanceController::class, 'getLeaveBalance']);

    Route::post('/leave/apply', [LeaveController::class, 'applyLeave']);
    Route::get('/leave/my-requests', [LeaveController::class, 'myLeaves']);
    Route::get('/leave/my-requests-status', [LeaveController::class, 'myLeaves']);
    Route::post('/leave/requests/{id}/cancel', [LeaveController::class, 'cancelLeaveRequest']);
    Route::get('/leave/calendar/my', [LeaveController::class, 'myLeaveCalendar']);
    Route::get('/leave/calendar/employees/{id}', [LeaveController::class, 'employeeLeaveCalendar']);

    Route::post('/leave/requests/{id}/approve', [LeaveApprovalController::class, 'approveLeave']);
    Route::post('/leave/requests/{id}/reject', [LeaveApprovalController::class, 'rejectLeave']);
});