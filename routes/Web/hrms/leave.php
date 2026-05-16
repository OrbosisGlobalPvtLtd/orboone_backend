<?php

use App\Http\Controllers\Web\HRMS\Leave\CompOffC;
use App\Http\Controllers\Web\HRMS\Leave\HolidayC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveAllocationC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveApprovalC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveBalanceC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveDashboardC;
use App\Http\Controllers\Web\HRMS\Leave\LeavePolicyC;
use App\Http\Controllers\Web\HRMS\Leave\LeavePolicyOverrideC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveRequestC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveTypeC;
use App\Http\Controllers\Web\HRMS\Leave\LeaveBalanceLogC;
use App\Http\Controllers\Web\HRMS\Leave\WeekoffRuleC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'check.access'])->group(function () {
    Route::get('/leave-dashboard', [LeaveDashboardC::class, 'index'])->middleware('permission:leave.dashboard.view')->name('hrms.leave.dashboard');
    Route::get('/leave-requests', [LeaveRequestC::class, 'index'])->middleware('permission:leave.my_requests.view')->name('leave-requests.index');
    Route::get('/leave-requests/create', [LeaveRequestC::class, 'create'])->middleware('permission:leave.my_requests.create')->name('leave-requests.create');
    Route::post('/leave-requests', [LeaveRequestC::class, 'store'])->middleware('permission:leave.my_requests.create')->name('leave-requests.store');
    Route::post('/leave-requests/{id}/cancel', [LeaveRequestC::class, 'cancel'])->middleware('permission:leave.my_requests.cancel')->name('leave-requests.cancel');

    Route::get('/leave-approvals', [LeaveApprovalC::class, 'index'])->middleware('permission:leave.approvals.view_all|leave.approvals.view_team|leave.approvals.view')->name('leave-approvals.index');
    Route::post('/leave-approvals/{id}/approve', [LeaveApprovalC::class, 'approve'])->middleware('permission:leave.approvals.approve')->name('leave-approvals.approve');
    Route::post('/leave-approvals/{id}/reject', [LeaveApprovalC::class, 'reject'])->middleware('permission:leave.approvals.reject')->name('leave-approvals.reject');

    Route::get('/leave-balances', [LeaveBalanceC::class, 'index'])->middleware('permission:leave.balance.view_all|leave.balance.view_team|leave.balance.view_own|leave.balance.view')->name('hrms.leave.balances.index');
    Route::get('/leave-allocations', [LeaveAllocationC::class, 'index'])->middleware('permission:leave.allocation.view_all|leave.allocation.view_own|leave.allocation.view|leave.allocation.manage')->name('leave-allocations.index');
    Route::post('/leave-allocations/process', [LeaveAllocationC::class, 'processAllocations'])->middleware('permission:leave.allocation.manage')->name('leave-allocations.process');
    Route::post('/leave-allocations/single', [LeaveAllocationC::class, 'allocateSingle'])->middleware('permission:leave.allocation.manage')->name('leave-allocations.single');
    Route::get('/leave-allocations/balance', [LeaveAllocationC::class, 'getBalance'])->name('leave-allocations.balance');

    Route::get('/leave-types', [LeaveTypeC::class, 'index'])->middleware('permission:leave.types.manage')->name('hrms.leave.types.index');
    Route::post('/leave-types', [LeaveTypeC::class, 'store'])->middleware('permission:leave.types.manage')->name('hrms.leave.types.store');
    Route::put('/leave-types/{id}', [LeaveTypeC::class, 'update'])->middleware('permission:leave.types.manage')->name('hrms.leave.types.update');

    Route::get('/leave-policies', [LeavePolicyC::class, 'index'])->middleware('permission:leave.policies.manage')->name('hrms.leave.policies.index');
    Route::post('/leave-policies', [LeavePolicyC::class, 'store'])->middleware('permission:leave.policies.manage')->name('hrms.leave.policies.store');
    Route::put('/leave-policies/{id}', [LeavePolicyC::class, 'update'])->middleware('permission:leave.policies.manage')->name('hrms.leave.policies.update');

    Route::get('/holidays', [HolidayC::class, 'index'])->middleware('permission:leave.holidays.manage')->name('hrms.holidays.index');
    Route::post('/holidays', [HolidayC::class, 'store'])->middleware('permission:leave.holidays.manage')->name('hrms.holidays.store');
    Route::delete('/holidays/{id}', [HolidayC::class, 'destroy'])->middleware('permission:leave.holidays.manage')->name('hrms.holidays.destroy');

    Route::get('/comp-offs', [CompOffC::class, 'index'])->middleware('permission:leave.comp_off.view_all|leave.comp_off.view_own|leave.comp_off.view|leave.comp_off.manage')->name('hrms.comp_offs.index');
    Route::post('/comp-offs/holiday-work/{id}/approve', [CompOffC::class, 'approveHolidayWork'])->middleware('permission:leave.comp_off.manage')->name('hrms.comp_offs.holiday_work.approve');
    Route::post('/comp-offs/expire', [CompOffC::class, 'expire'])->middleware('permission:leave.comp_off.manage')->name('hrms.comp_offs.expire');

    Route::get('/weekoff-rules', [WeekoffRuleC::class, 'index'])->middleware('permission:leave.weekoff_rules.manage')->name('hrms.weekoff_rules.index');
    Route::post('/weekoff-rules', [WeekoffRuleC::class, 'store'])->middleware('permission:leave.weekoff_rules.manage')->name('hrms.weekoff_rules.store');
    Route::put('/weekoff-rules/{id}', [WeekoffRuleC::class, 'update'])->middleware('permission:leave.weekoff_rules.manage')->name('hrms.weekoff_rules.update');

    Route::get('/leave-policy-overrides', [LeavePolicyOverrideC::class, 'index'])->middleware('permission:leave.policy_overrides.manage')->name('hrms.leave.policy_overrides.index');
    Route::post('/leave-policy-overrides', [LeavePolicyOverrideC::class, 'store'])->middleware('permission:leave.policy_overrides.manage')->name('hrms.leave.policy_overrides.store');
    Route::put('/leave-policy-overrides/{id}', [LeavePolicyOverrideC::class, 'update'])->middleware('permission:leave.policy_overrides.manage')->name('hrms.leave.policy_overrides.update');

    Route::get('/leave-balance-logs', [LeaveBalanceLogC::class, 'index'])->middleware('permission:leave.balance_logs.view')->name('hrms.leave.balance_logs.index');

    Route::get('/team-leave-calendar', [LeaveApprovalC::class, 'index'])->name('hrms.leave.team_calendar.index');

    Route::get('/employees-leave-request/summary', [LeaveBalanceC::class, 'index'])->name('employees-leave-request.summary');
});
