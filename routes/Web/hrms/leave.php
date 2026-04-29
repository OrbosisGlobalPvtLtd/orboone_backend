<?php

use App\Http\Controllers\EmployeeLeaveRequestsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Leave Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.access'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Employee Leave Request
    |--------------------------------------------------------------------------
    */
    Route::get('/employees-leave-request/summary', [EmployeeLeaveRequestsController::class, 'summary'])
        ->name('employees-leave-request.summary');

    Route::get('/employees-leave-request', [EmployeeLeaveRequestsController::class, 'index'])
        ->name('employees-leave-request');

    Route::get('/employees-leave-request/create', [EmployeeLeaveRequestsController::class, 'create'])
        ->name('employees-leave-request.create');

    Route::get('/employees-leave-request/print', [EmployeeLeaveRequestsController::class, 'print'])
        ->name('employees-leave-request.print');

    Route::get('/employees-leave-request/{employeeLeaveRequest}', [EmployeeLeaveRequestsController::class, 'show'])
        ->name('employees-leave-request.show');

    Route::get('/employees-leave-request/{employeeLeaveRequest}/edit', [EmployeeLeaveRequestsController::class, 'edit'])
        ->name('employees-leave-request.edit');

    Route::post('/employees-leave-request', [EmployeeLeaveRequestsController::class, 'store'])
        ->name('employees-leave-request.store');

    Route::put('/employees-leave-request/{employeeLeaveRequest}', [EmployeeLeaveRequestsController::class, 'update'])
        ->name('employees-leave-request.update');

    Route::delete('/employees-leave-request/{employeeLeaveRequest}', [EmployeeLeaveRequestsController::class, 'destroy'])
        ->name('employees-leave-request.destroy');

    /*
    |--------------------------------------------------------------------------
    | New Leave Management
    |--------------------------------------------------------------------------
    */
    Route::get('/leave-allocations', [\App\Http\Controllers\LeaveAllocationController::class, 'index'])
        ->name('leave-allocations.index');

    Route::post('/leave-allocations/process', [\App\Http\Controllers\LeaveAllocationController::class, 'processAllocations'])
        ->name('leave-allocations.process');

    Route::post('/leave-allocations/single', [\App\Http\Controllers\LeaveAllocationController::class, 'allocateSingle'])
        ->name('leave-allocations.single');

    Route::get('/leave-allocations/balance', [\App\Http\Controllers\LeaveAllocationController::class, 'getBalance'])
        ->name('leave-allocations.balance');

    Route::get('/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'index'])
        ->name('leave-requests.index');

    Route::get('/leave-requests/create', [\App\Http\Controllers\LeaveRequestController::class, 'create'])
        ->name('leave-requests.create');

    Route::post('/leave-requests', [\App\Http\Controllers\LeaveRequestController::class, 'store'])
        ->name('leave-requests.store');

    Route::get('/leave-approvals', [\App\Http\Controllers\LeaveApprovalController::class, 'index'])
        ->name('leave-approvals.index');

    Route::post('/leave-approvals/{id}/approve', [\App\Http\Controllers\LeaveApprovalController::class, 'approve'])
        ->name('leave-approvals.approve');

    Route::post('/leave-approvals/{id}/reject', [\App\Http\Controllers\LeaveApprovalController::class, 'reject'])
        ->name('leave-approvals.reject');
});