<?php

use App\Http\Controllers\Web\HRMS\Leave\EmployeeLeaveRequestsC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveAllocationC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveApprovalC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveRequestC;
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
    Route::get('/employees-leave-request/summary', [EmployeeLeaveRequestsC::class, 'summary'])
        ->name('employees-leave-request.summary');

    Route::get('/employees-leave-request', [EmployeeLeaveRequestsC::class, 'index'])
        ->name('employees-leave-request');

    Route::get('/employees-leave-request/create', [EmployeeLeaveRequestsC::class, 'create'])
        ->name('employees-leave-request.create');

    Route::get('/employees-leave-request/print', [EmployeeLeaveRequestsC::class, 'print'])
        ->name('employees-leave-request.print');

    Route::get('/employees-leave-request/{employeeLeaveRequest}', [EmployeeLeaveRequestsC::class, 'show'])
        ->name('employees-leave-request.show');

    Route::get('/employees-leave-request/{employeeLeaveRequest}/edit', [EmployeeLeaveRequestsC::class, 'edit'])
        ->name('employees-leave-request.edit');

    Route::post('/employees-leave-request', [EmployeeLeaveRequestsC::class, 'store'])
        ->name('employees-leave-request.store');

    Route::put('/employees-leave-request/{employeeLeaveRequest}', [EmployeeLeaveRequestsC::class, 'update'])
        ->name('employees-leave-request.update');

    Route::delete('/employees-leave-request/{employeeLeaveRequest}', [EmployeeLeaveRequestsC::class, 'destroy'])
        ->name('employees-leave-request.destroy');

    /*
    |--------------------------------------------------------------------------
    | New Leave Management
    |--------------------------------------------------------------------------
    */
    Route::get('/leave-allocations', [LeaveAllocationC::class, 'index'])
        ->name('leave-allocations.index');

    Route::post('/leave-allocations/process', [LeaveAllocationC::class, 'processAllocations'])
        ->name('leave-allocations.process');

    Route::post('/leave-allocations/single', [LeaveAllocationC::class, 'allocateSingle'])
        ->name('leave-allocations.single');

    Route::get('/leave-allocations/balance', [LeaveAllocationC::class, 'getBalance'])
        ->name('leave-allocations.balance');

    Route::get('/leave-requests', [LeaveRequestC::class, 'index'])
        ->name('leave-requests.index');

    Route::get('/leave-requests/create', [LeaveRequestC::class, 'create'])
        ->name('leave-requests.create');

    Route::post('/leave-requests', [LeaveRequestC::class, 'store'])
        ->name('leave-requests.store');

    Route::get('/leave-approvals', [LeaveApprovalC::class, 'index'])
        ->name('leave-approvals.index');

    Route::post('/leave-approvals/{id}/approve', [LeaveApprovalC::class, 'approve'])
        ->name('leave-approvals.approve');

    Route::post('/leave-approvals/{id}/reject', [LeaveApprovalC::class, 'reject'])
        ->name('leave-approvals.reject');
});
