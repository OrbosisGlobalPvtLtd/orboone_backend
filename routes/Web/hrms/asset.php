<?php

use App\Http\Controllers\Web\HRMS\Employee\AssetAllocationC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->name('hrms.')
    ->group(function () {
        Route::resource('asset-allocations', AssetAllocationC::class)
            ->names('assets')
            ->middleware('permission:asset_allocation.manage');
    });
