<?php

use App\Http\Controllers\Api\V1\HRMS\EnterprisePayroll\EnterprisePayrollApiC;
use App\Http\Controllers\Api\V1\HRMS\EnterprisePayroll\EnterprisePayslipApiC;
use App\Http\Controllers\Api\V1\HRMS\EnterprisePayroll\EnterpriseReimbursementApiC;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('hrms/enterprise-payroll')
    ->group(function () {
        Route::get('/summary', [EnterprisePayrollApiC::class, 'summary']);
        Route::get('/salary-history', [EnterprisePayrollApiC::class, 'salaryHistory']);

        Route::get('/payslips', [EnterprisePayslipApiC::class, 'index']);
        Route::get('/payslips/{id}', [EnterprisePayslipApiC::class, 'show'])->whereNumber('id');
        Route::get('/payslips/{id}/download', [EnterprisePayslipApiC::class, 'download'])->whereNumber('id');

        Route::get('/reimbursements', [EnterpriseReimbursementApiC::class, 'index']);
        Route::post('/reimbursements', [EnterpriseReimbursementApiC::class, 'store']);
        Route::get('/reimbursements/{id}', [EnterpriseReimbursementApiC::class, 'show'])->whereNumber('id');
    });
