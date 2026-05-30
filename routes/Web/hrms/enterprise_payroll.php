<?php

use App\Http\Controllers\Web\HRMS\EnterprisePayroll\DashboardC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\SalaryStructureC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\PayrollRunC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\PayslipC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\BonusIncentiveC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\ReimbursementC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\ReportC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\FnfSettlementC;
use App\Http\Controllers\Web\HRMS\EnterprisePayroll\PolicyC;
use Illuminate\Support\Facades\Route;

Route::prefix('enterprise-payroll')->middleware(['auth', 'check.access'])->name('enterprise-payroll.')->group(function () {
    Route::get('/dashboard', [DashboardC::class, 'index'])
        ->middleware('permission:enterprise_payroll.dashboard.view')
        ->name('dashboard');

    Route::get('/salary-structures', [SalaryStructureC::class, 'index'])
        ->middleware('permission:enterprise_salary_structure.view|enterprise_salary_structure.manage')
        ->name('salary-structures.index');
    Route::post('/salary-structures', [SalaryStructureC::class, 'store'])
        ->middleware('permission:enterprise_salary_structure.manage')
        ->name('salary-structures.store');
    Route::match(['put', 'patch'], '/salary-structures/{salaryStructure}', [SalaryStructureC::class, 'update'])
        ->middleware('permission:enterprise_salary_structure.manage')
        ->name('salary-structures.update');

    Route::get('/runs', [PayrollRunC::class, 'index'])
        ->middleware('permission:enterprise_payroll_run.view')
        ->name('runs.index');
    Route::post('/runs/preview', [PayrollRunC::class, 'preview'])
        ->middleware('permission:enterprise_payroll_run.generate')
        ->name('runs.preview');
    Route::post('/runs/generate', [PayrollRunC::class, 'generate'])
        ->middleware('permission:enterprise_payroll_run.generate')
        ->name('runs.generate');
    Route::get('/runs/{run}', [PayrollRunC::class, 'show'])
        ->middleware('permission:enterprise_payroll_run.view')
        ->name('runs.show');
    Route::post('/runs/{run}/approve', [PayrollRunC::class, 'approve'])
        ->middleware('permission:enterprise_payroll_run.approve')
        ->name('runs.approve');
    Route::post('/runs/{run}/lock', [PayrollRunC::class, 'lock'])
        ->middleware('permission:enterprise_payroll_run.lock')
        ->name('runs.lock');
    Route::post('/runs/{run}/reopen', [PayrollRunC::class, 'reopen'])
        ->middleware('permission:enterprise_payroll_run.reopen')
        ->name('runs.reopen');
    Route::post('/runs/{run}/payslips', [PayslipC::class, 'generateForRun'])
        ->middleware('permission:enterprise_payslip.generate')
        ->name('runs.payslips.generate');
    Route::get('/runs/{run}/report', [PayrollRunC::class, 'downloadReport'])
        ->middleware('permission:enterprise_payroll_reports.view')
        ->name('runs.report.download');

    Route::get('/payslips', [PayslipC::class, 'index'])
        ->middleware('permission:enterprise_payslip.view')
        ->name('payslips.index');
    Route::get('/payslips/{payslip}/download', [PayslipC::class, 'download'])
        ->middleware('permission:enterprise_payslip.download|enterprise_payroll.my_payslips.view')
        ->name('payslips.download');

    Route::get('/bonus-incentives', [BonusIncentiveC::class, 'index'])
        ->middleware('permission:enterprise_bonus_incentive.view|enterprise_bonus_incentive.manage')
        ->name('bonus-incentives.index');
    Route::post('/bonus-incentives', [BonusIncentiveC::class, 'store'])
        ->middleware('permission:enterprise_bonus_incentive.manage')
        ->name('bonus-incentives.store');
    Route::match(['put', 'patch'], '/bonus-incentives/{bonusIncentive}', [BonusIncentiveC::class, 'update'])
        ->middleware('permission:enterprise_bonus_incentive.manage')
        ->name('bonus-incentives.update');
    Route::post('/bonus-incentives/{bonusIncentive}/approve', [BonusIncentiveC::class, 'approve'])
        ->middleware('permission:enterprise_bonus_incentive.manage')
        ->name('bonus-incentives.approve');
    Route::post('/bonus-incentives/{bonusIncentive}/reject', [BonusIncentiveC::class, 'reject'])
        ->middleware('permission:enterprise_bonus_incentive.manage')
        ->name('bonus-incentives.reject');

    Route::get('/reimbursements', [ReimbursementC::class, 'index'])
        ->middleware('permission:enterprise_reimbursement.view|enterprise_reimbursement.manage')
        ->name('reimbursements.index');
    Route::post('/reimbursements', [ReimbursementC::class, 'store'])
        ->middleware('permission:enterprise_reimbursement.view|enterprise_reimbursement.manage|enterprise_payroll.my_reimbursements.create')
        ->name('reimbursements.store');
    Route::match(['put', 'patch'], '/reimbursements/{reimbursement}', [ReimbursementC::class, 'update'])
        ->middleware('permission:enterprise_reimbursement.manage')
        ->name('reimbursements.update');
    Route::post('/reimbursements/{reimbursement}/approve', [ReimbursementC::class, 'approve'])
        ->middleware('permission:enterprise_reimbursement.manage')
        ->name('reimbursements.approve');
    Route::post('/reimbursements/{reimbursement}/reject', [ReimbursementC::class, 'reject'])
        ->middleware('permission:enterprise_reimbursement.manage')
        ->name('reimbursements.reject');

    Route::get('/fnf', [FnfSettlementC::class, 'index'])
        ->middleware('permission:enterprise_fnf.view')
        ->name('fnf.index');
    Route::post('/fnf', [FnfSettlementC::class, 'store'])
        ->middleware('permission:enterprise_fnf.manage')
        ->name('fnf.store');
    Route::post('/fnf/{settlement}/approve', [FnfSettlementC::class, 'approve'])
        ->middleware('permission:enterprise_fnf.manage')
        ->name('fnf.approve');

    Route::get('/reports', [ReportC::class, 'index'])
        ->middleware('permission:enterprise_payroll_reports.view')
        ->name('reports.index');
    Route::get('/reports/{type}', [ReportC::class, 'show'])
        ->middleware('permission:enterprise_payroll_reports.view')
        ->name('reports.show');

    Route::get('/my-payslips', [PayslipC::class, 'self'])
        ->middleware('permission:enterprise_payroll.my_payslips.view')
        ->name('self.payslips');
    Route::get('/my-reimbursements', [ReimbursementC::class, 'self'])
        ->middleware('permission:enterprise_payroll.my_reimbursements.view')
        ->name('self.reimbursements');

    Route::get('/policies', [PolicyC::class, 'index'])
        ->middleware('permission:enterprise_payroll.policy.view')
        ->name('policies.index');
    Route::post('/policies', [PolicyC::class, 'update'])
        ->middleware('permission:enterprise_payroll.policy.update')
        ->name('policies.update');
});
