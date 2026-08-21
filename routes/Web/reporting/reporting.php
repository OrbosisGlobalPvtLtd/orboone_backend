<?php

use App\Http\Controllers\Web\Reporting\ReportingC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])->prefix('hrms/reporting')->group(function () {
    Route::get('/dashboard', [ReportingC::class, 'dashboard'])->name('reporting.dashboard');
    Route::get('/structure', [ReportingC::class, 'structure'])->name('reporting.structure');
    Route::get('/supervisors', [ReportingC::class, 'supervisors'])->name('reporting.supervisors');
    Route::get('/assignments', [ReportingC::class, 'assignments'])->name('reporting.assignments');
    Route::post('/assignments/assign', [ReportingC::class, 'assignSupervisor'])->name('reporting.assignments.assign');
    Route::post('/assignments/{id}/relieve', [ReportingC::class, 'relieveEmployee'])->name('reporting.assignments.relieve');
    Route::get('/my-employees', [ReportingC::class, 'myEmployees'])->name('reporting.my_employees');
    Route::get('/attendance', [ReportingC::class, 'attendance'])->name('reporting.attendance');
    Route::get('/leave', [ReportingC::class, 'leave'])->name('reporting.leave');
    Route::get('/work-reports', [ReportingC::class, 'workReports'])->name('reporting.work_reports');
    Route::get('/projects', [ReportingC::class, 'projects'])->name('reporting.projects');
    Route::get('/history', [ReportingC::class, 'history'])->name('reporting.history');
});
