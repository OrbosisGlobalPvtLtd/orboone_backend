<?php

use App\Http\Controllers\Api\V1\MobileApp\MobileAppVersionApiC;
use App\Http\Controllers\Api\V1\MobileApp\MobileDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/mobile-app/latest-version', [MobileAppVersionApiC::class, 'latest']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mobile/dashboard/bootstrap', [MobileDashboardController::class, 'bootstrap']);
});

