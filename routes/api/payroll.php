<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Payroll\PayrollController;
use App\Http\Controllers\Api\Payroll\ClaimController;
use App\Http\Controllers\Api\Payroll\PayslipController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/payroll/dashboard', [PayrollController::class, 'getPayrollDashboard']);
    Route::get('/payroll/salary-structure', [PayrollController::class, 'getSalaryStructure']);
    Route::get('/payroll/monthly-summary', [PayrollController::class, 'getMonthlySalary']);

    Route::post('/payroll/claims', [ClaimController::class, 'submitClaim']);
    Route::get('/payroll/claims-history', [ClaimController::class, 'getClaimsHistory']);

    Route::get('/payroll/payslip', [PayslipController::class, 'getPayslip']);
    Route::get('/payroll/payslip-history', [PayslipController::class, 'getPayslipHistory']);
});