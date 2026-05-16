<?php

use App\Http\Controllers\Web\HRMS\MobileApp\MobileAppVersionC;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('hrms/mobile-app')
    ->name('hrms.mobile-app-versions.')
    ->group(function () {
        Route::get('/', [MobileAppVersionC::class, 'index'])
            ->middleware('permission:mobile_app_versions.view|mobile_app_versions.manage')
            ->name('index');

        Route::post('/store', [MobileAppVersionC::class, 'store'])
            ->middleware('permission:mobile_app_versions.upload|mobile_app_versions.manage')
            ->name('store');

        Route::post('/{id}/toggle-active', [MobileAppVersionC::class, 'toggleActive'])
            ->middleware('permission:mobile_app_versions.manage')
            ->name('toggle-active');

        Route::delete('/{id}', [MobileAppVersionC::class, 'destroy'])
            ->middleware('permission:mobile_app_versions.delete|mobile_app_versions.manage')
            ->name('destroy');

        Route::get('/{id}/download', [MobileAppVersionC::class, 'download'])
            ->middleware('permission:mobile_app_versions.view|mobile_app_versions.manage')
            ->name('download');
    });

Route::get('/mobile-app/download-latest', [MobileAppVersionC::class, 'downloadLatest'])
    ->name('mobile-app.download-latest');
