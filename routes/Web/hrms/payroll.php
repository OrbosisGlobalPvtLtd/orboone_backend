<?php

use App\Http\Controllers\PayrollAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payroll Routes
|--------------------------------------------------------------------------
*/

Route::prefix('payroll')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PayrollAdminController::class, 'dashboard'])->name('pages.payroll.dashboard');

    // Salary Structure
    Route::get('/', [PayrollAdminController::class, 'structuresIndex'])->name('pages.payroll.index');
    Route::get('/create', [PayrollAdminController::class, 'structuresCreate'])->name('pages.payroll.create');
    Route::post('/store', [PayrollAdminController::class, 'salary_structure'])->name('pages.payroll.salary_structure');
    Route::get('/edit/{id}', [PayrollAdminController::class, 'structuresEdit'])->name('pages.payroll.edit');
    Route::post('/update/{id}', [PayrollAdminController::class, 'structuresUpdate'])->name('pages.payroll.update');

    // Assign Structures
    Route::get('/assign', [PayrollAdminController::class, 'structuresAssignForm'])->name('pages.payroll.assign');
    Route::post('/assign', [PayrollAdminController::class, 'structuresAssign'])->name('pages.payroll.assign.save');

    // Employee Salary Structure View
    Route::get('/salary-structure', [PayrollAdminController::class, 'salaryStructure'])->name('pages.payroll.salary.structure');

    // Payroll Run + Preview
    Route::get('/run', [PayrollAdminController::class, 'payrollRunForm'])->name('pages.payroll.payrollrun');
    Route::post('/run', [PayrollAdminController::class, 'payrollRun'])->name('pages.payroll.payrollrun.run');
    Route::get('/preview/{month}', [PayrollAdminController::class, 'payrollPreview'])->name('pages.payroll.preview');
    Route::post('/lock/{month}', [PayrollAdminController::class, 'payrollLock'])->name('pages.payroll.lock');

    // Monthly Salary View
    Route::get('/monthly', [PayrollAdminController::class, 'monthlyList'])->name('pages.payroll.monthlylist');
    Route::get('/monthly/{month}', [PayrollAdminController::class, 'monthlyDetail'])->name('pages.payroll.monthlydetail');

    // Payslips
    Route::get('/payslip-index/{month}', [PayrollAdminController::class, 'payslipsByMonth'])->name('pages.payroll.payslipindex');
    Route::post('/payslip-generate/{month}', [PayrollAdminController::class, 'payslipsGenerate'])->name('pages.payroll.payslipgenerate');
    Route::get('/payslips', [PayrollAdminController::class, 'payslips'])->name('pages.payroll.payslips');

    Route::get('/payslips/{id}/download', [PayrollAdminController::class, 'download'])
        ->name('pages.payroll.payslip.download');

    Route::get('/payslip/{employee_id}/{month}/download', [PayrollAdminController::class, 'downloadByEmployeeMonth'])
        ->name('pages.payroll.payslip.download.employee');

    Route::get('/salary-slip', [PayrollAdminController::class, 'salarySlipForm'])
        ->name('pages.payroll.salaryslip.form');

    Route::post('/salary-slip', [PayrollAdminController::class, 'salarySlipDownload'])
        ->name('pages.payroll.salaryslip.download');

    Route::get('/payslip-download-all/{month}', [PayrollAdminController::class, 'downloadAllPayslips'])
        ->name('pages.payroll.payslip.downloadall');

    // Statutory
    Route::get('/statutory-settings', [PayrollAdminController::class, 'statutorySettingsForm'])
        ->name('pages.payroll.statutorysettings');

    Route::post('/statutory-settings', [PayrollAdminController::class, 'statutorySettingsSave'])
        ->name('pages.payroll.statutorysettings.save');

    Route::get('/statutory-report', [PayrollAdminController::class, 'statutoryReportForm'])
        ->name('pages.payroll.statutoryreport_form');

    Route::get('/statutory-report/view', [PayrollAdminController::class, 'statutoryReportView'])
        ->name('pages.payroll.statutoryreport_view');

    // Deductions
    Route::get('/deductions', [PayrollAdminController::class, 'deductions'])->name('pages.payroll.deductions');

    // Full & Final
    Route::get('/fnf-pending', [PayrollAdminController::class, 'fnfPendingEmployees'])->name('pages.payroll.fnfpending');
    Route::get('/fnf/{employee}', [PayrollAdminController::class, 'fnfCalculateForm'])->name('pages.payroll.fnfcalculate');
    Route::post('/fnf/{employee}', [PayrollAdminController::class, 'fnfProcess'])->name('pages.payroll.fnfprocess');
    Route::get('/fnf-view', [PayrollAdminController::class, 'fnfView'])->name('pages.payroll.fnf');

    // Claims
    Route::get('/claims', [PayrollAdminController::class, 'claimsIndex'])->name('pages.payroll.claims.index');
    Route::get('/claims/create', [PayrollAdminController::class, 'claimsCreate'])->name('pages.payroll.claims.create');
    Route::post('/claims/store', [PayrollAdminController::class, 'claimsStore'])->name('pages.payroll.claims.store');
});