<?php

use App\Http\Controllers\Web\ProjectManagement\ProjectC;
use App\Http\Controllers\Web\ProjectManagement\ProjectTeamC;
use App\Http\Controllers\Web\ProjectManagement\ProjectAssignmentC;
use App\Http\Controllers\Web\ProjectManagement\ProjectTaskC;
use App\Http\Controllers\Web\ProjectManagement\WorkReportTemplateC;
use App\Http\Controllers\Web\ProjectManagement\ProjectTeamAttendanceC;
use App\Http\Controllers\Web\ProjectManagement\ProjectTeamWorkReportC;
use App\Http\Controllers\Web\ProjectManagement\ProjectTeamLeaveC;
use App\Http\Controllers\Web\ProjectManagement\TechnicalLeadC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->group(function () {
        // Projects CRUD
        Route::get('/projects', [ProjectC::class, 'index'])->name('projects.index');
        Route::post('/projects', [ProjectC::class, 'store'])->name('projects.store');
        Route::get('/projects/my-projects', [ProjectC::class, 'myProjects'])->name('projects.my');
        Route::get('/projects/{id}', [ProjectC::class, 'show'])->name('projects.show');
        Route::put('/projects/{id}', [ProjectC::class, 'update'])->name('projects.update');
        Route::get('/projects/{id}/hierarchy', [ProjectC::class, 'hierarchy'])->name('projects.hierarchy');

        // Project Teams
        Route::post('/projects/{id}/teams', [ProjectTeamC::class, 'store'])->name('projects.teams.store');
        Route::put('/project-teams/{id}', [ProjectTeamC::class, 'update'])->name('projects.teams.update');

        // Member Assignments
        Route::post('/projects/{id}/assign', [ProjectAssignmentC::class, 'assign'])->name('projects.assign');
        Route::post('/project-assignments/{id}/relieve', [ProjectAssignmentC::class, 'relieve'])->name('projects.relieve');

        // Project Tasks
        Route::get('/project-tasks', [ProjectTaskC::class, 'index'])->name('projects.tasks.index');
        Route::post('/project-tasks', [ProjectTaskC::class, 'store'])->name('projects.tasks.store');
        Route::post('/project-tasks/{id}/status', [ProjectTaskC::class, 'updateStatus'])->name('projects.tasks.update_status');

        // Work Report Templates
        Route::get('/work-report-templates', [WorkReportTemplateC::class, 'index'])->name('projects.templates.index');
        Route::post('/work-report-templates', [WorkReportTemplateC::class, 'store'])->name('projects.templates.store');
        Route::post('/work-report-templates/{id}/fields', [WorkReportTemplateC::class, 'storeField'])->name('projects.templates.fields.store');

        // Team Views for Leads / Delivery Heads
        Route::get('/project-team/attendance', [ProjectTeamAttendanceC::class, 'index'])->name('projects.team.attendance');
        Route::get('/project-team/work-reports', [ProjectTeamWorkReportC::class, 'index'])->name('projects.team.work_reports');
        Route::get('/project-team/leave', [ProjectTeamLeaveC::class, 'index'])->name('projects.team.leave');

        // Technical Lead Supervision Layer
        Route::get('/technical-lead/dashboard', [TechnicalLeadC::class, 'dashboard'])->name('technical_lead.dashboard');
        Route::get('/technical-lead/supervisors', [TechnicalLeadC::class, 'supervisors'])->name('technical_lead.supervisors');
        Route::get('/technical-lead/developers', [TechnicalLeadC::class, 'developers'])->name('technical_lead.developers');
        Route::post('/technical-lead/developers/assign', [TechnicalLeadC::class, 'assignDeveloper'])->name('technical_lead.developers.assign');
        Route::post('/technical-lead/developers/{id}/relieve', [TechnicalLeadC::class, 'relieveDeveloper'])->name('technical_lead.developers.relieve');
        Route::get('/technical-lead/attendance', [TechnicalLeadC::class, 'attendance'])->name('technical_lead.attendance');
        Route::get('/technical-lead/leave', [TechnicalLeadC::class, 'leave'])->name('technical_lead.leave');
        Route::get('/technical-lead/work-reports', [TechnicalLeadC::class, 'workReports'])->name('technical_lead.work_reports');
        Route::get('/technical-lead/projects', [TechnicalLeadC::class, 'projects'])->name('technical_lead.projects');
    });
