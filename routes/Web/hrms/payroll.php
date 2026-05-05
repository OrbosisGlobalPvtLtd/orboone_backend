<?php

use App\Http\Controllers\Web\HRMS\Payroll\PayrollAdminC;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payroll Routes
|--------------------------------------------------------------------------
*/

Route::prefix('payroll')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PayrollAdminC::class, 'dashboard'])->name('pages.payroll.dashboard');

    // Salary Structure
    Route::get('/', [PayrollAdminC::class, 'structuresIndex'])->name('pages.payroll.index');
    Route::get('/create', [PayrollAdminC::class, 'structuresCreate'])->name('pages.payroll.create');
    Route::post('/store', [PayrollAdminC::class, 'salary_structure'])->name('pages.payroll.salary_structure');
    Route::get('/edit/{id}', [PayrollAdminC::class, 'structuresEdit'])->name('pages.payroll.edit');
    Route::post('/update/{id}', [PayrollAdminC::class, 'structuresUpdate'])->name('pages.payroll.update');

    // Assign Structures
    Route::get('/assign', [PayrollAdminC::class, 'structuresAssignForm'])->name('pages.payroll.assign');
    Route::post('/assign', [PayrollAdminC::class, 'structuresAssign'])->name('pages.payroll.assign.save');

    // Employee Salary Structure View
    Route::get('/salary-structure', [PayrollAdminC::class, 'salaryStructure'])->name('pages.payroll.salary.structure');

    // Payroll Run + Preview
    Route::get('/run', [PayrollAdminC::class, 'payrollRunForm'])->name('pages.payroll.payrollrun');
    Route::post('/run', [PayrollAdminC::class, 'payrollRun'])->name('pages.payroll.payrollrun.run');
    Route::get('/preview/{month}', [PayrollAdminC::class, 'payrollPreview'])->name('pages.payroll.preview');
    Route::post('/lock/{month}', [PayrollAdminC::class, 'payrollLock'])->name('pages.payroll.lock');

    // Monthly Salary View
    Route::get('/monthly', [PayrollAdminC::class, 'monthlyList'])->name('pages.payroll.monthlylist');
    Route::get('/monthly/{month}', [PayrollAdminC::class, 'monthlyDetail'])->name('pages.payroll.monthlydetail');

    // Payslips
    Route::get('/payslip-index/{month}', [PayrollAdminC::class, 'payslipsByMonth'])->name('pages.payroll.payslipindex');
    Route::post('/payslip-generate/{month}', [PayrollAdminC::class, 'payslipsGenerate'])->name('pages.payroll.payslipgenerate');
    Route::get('/payslips', [PayrollAdminC::class, 'payslips'])->name('pages.payroll.payslips');

    Route::get('/payslips/{id}/download', [PayrollAdminC::class, 'download'])
        ->name('pages.payroll.payslip.download');

    Route::get('/payslip/{employee_id}/{month}/download', [PayrollAdminC::class, 'downloadByEmployeeMonth'])
        ->name('pages.payroll.payslip.download.employee');

    Route::get('/salary-slip', [PayrollAdminC::class, 'salarySlipForm'])
        ->name('pages.payroll.salaryslip.form');

    Route::post('/salary-slip', [PayrollAdminC::class, 'salarySlipDownload'])
        ->name('pages.payroll.salaryslip.download');

    Route::get('/payslip-download-all/{month}', [PayrollAdminC::class, 'downloadAllPayslips'])
        ->name('pages.payroll.payslip.downloadall');

    // Statutory
    Route::get('/statutory-settings', [PayrollAdminC::class, 'statutorySettingsForm'])
        ->name('pages.payroll.statutorysettings');

    Route::post('/statutory-settings', [PayrollAdminC::class, 'statutorySettingsSave'])
        ->name('pages.payroll.statutorysettings.save');

    Route::get('/statutory-report', [PayrollAdminC::class, 'statutoryReportForm'])
        ->name('pages.payroll.statutoryreport_form');

    Route::get('/statutory-report/view', [PayrollAdminC::class, 'statutoryReportView'])
        ->name('pages.payroll.statutoryreport_view');

    // Deductions
    Route::get('/deductions', [PayrollAdminC::class, 'deductions'])->name('pages.payroll.deductions');

    // Full & Final
    Route::get('/fnf-pending', [PayrollAdminC::class, 'fnfPendingEmployees'])->name('pages.payroll.fnfpending');
    Route::get('/fnf/{employee}', [PayrollAdminC::class, 'fnfCalculateForm'])->name('pages.payroll.fnfcalculate');
    Route::post('/fnf/{employee}', [PayrollAdminC::class, 'fnfProcess'])->name('pages.payroll.fnfprocess');
    Route::get('/fnf-view', [PayrollAdminC::class, 'fnfView'])->name('pages.payroll.fnf');

    // Claims
    Route::get('/claims', [PayrollAdminC::class, 'claimsIndex'])->name('pages.payroll.claims.index');
    Route::get('/claims/create', [PayrollAdminC::class, 'claimsCreate'])->name('pages.payroll.claims.create');
    Route::post('/claims/store', [PayrollAdminC::class, 'claimsStore'])->name('pages.payroll.claims.store');
});
