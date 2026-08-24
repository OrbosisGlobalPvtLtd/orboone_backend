<?php

use App\Http\Controllers\Api\V1\Reporting\ReportingApiC;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1/reporting')->group(function () {
    Route::get('/dashboard', [ReportingApiC::class, 'dashboard']);
    Route::get('/my-employees', [ReportingApiC::class, 'myEmployees']);
    Route::get('/attendance', [ReportingApiC::class, 'attendance']);
    Route::get('/leave', [ReportingApiC::class, 'leave']);
    Route::get('/work-reports', [ReportingApiC::class, 'workReports']);
    Route::get('/projects', [ReportingApiC::class, 'projects']);
});
