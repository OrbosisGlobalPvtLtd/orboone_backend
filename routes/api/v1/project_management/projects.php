<?php

use App\Http\Controllers\Api\V1\ProjectManagement\ProjectApiC;
use App\Http\Controllers\Api\V1\ProjectManagement\TechnicalLeadApiC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])
    ->prefix('projects')
    ->group(function () {
        Route::get('/my-projects', [ProjectApiC::class, 'myProjects']);
        Route::get('/{id}', [ProjectApiC::class, 'show']);
        Route::get('/{id}/tasks', [ProjectApiC::class, 'tasks']);
        Route::get('/{id}/work-report-template', [ProjectApiC::class, 'getWorkReportTemplate']);
        Route::post('/work-report', [ProjectApiC::class, 'submitWorkReport']);
        Route::get('/{id}/team-attendance', [ProjectApiC::class, 'teamAttendance']);
    });

Route::middleware(['auth:sanctum'])
    ->prefix('technical-lead')
    ->group(function () {
        Route::get('/dashboard', [TechnicalLeadApiC::class, 'dashboard']);
        Route::get('/developers', [TechnicalLeadApiC::class, 'developers']);
        Route::get('/attendance', [TechnicalLeadApiC::class, 'attendance']);
        Route::get('/leave', [TechnicalLeadApiC::class, 'leave']);
        Route::get('/work-reports', [TechnicalLeadApiC::class, 'workReports']);
        Route::get('/projects', [TechnicalLeadApiC::class, 'projects']);
    });
