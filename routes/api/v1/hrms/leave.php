<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HRMS\Leave\LeaveApiC;
use App\Http\Controllers\Api\V1\HRMS\Leave\LeaveApprovalApiC;

Route::middleware('auth:sanctum')
    ->prefix('hrms/leave')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Employee Leave APIs
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [LeaveApiC::class, 'dashboard']);
        Route::get('/types', [LeaveApiC::class, 'types']);
        Route::get('/balance', [LeaveApiC::class, 'balance']);
        Route::get('/history', [LeaveApiC::class, 'myRequests']);
        Route::get('/holidays', [LeaveApiC::class, 'holidays']);
        Route::get('/comp-offs', [LeaveApiC::class, 'compOffs']);
        Route::get('/team-calendar', [LeaveApiC::class, 'teamCalendar']);

        Route::post('/calculate', [LeaveApiC::class, 'calculate']);
        Route::post('/apply', [LeaveApiC::class, 'apply']);
        Route::post('/cancel/{id}', [LeaveApiC::class, 'cancel']);

        /*
        |--------------------------------------------------------------------------
        | Manager / HR Approval APIs
        |--------------------------------------------------------------------------
        */

        Route::get('/pending', [LeaveApprovalApiC::class, 'pending']);
        Route::get('/approvals', [LeaveApprovalApiC::class, 'pending']);

        Route::post('/approve/{id}', [LeaveApprovalApiC::class, 'approve']);
        Route::post('/reject/{id}', [LeaveApprovalApiC::class, 'reject']);

        /*
        |--------------------------------------------------------------------------
        | Leave Details
        |--------------------------------------------------------------------------
        | Keep dynamic route LAST
        |--------------------------------------------------------------------------
        */

        Route::get('/{id}', [LeaveApiC::class, 'show']);
    });
    
