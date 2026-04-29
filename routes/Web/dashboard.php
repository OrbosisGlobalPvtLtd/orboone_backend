<?php

use App\Http\Controllers\Web\Dashboard\DashboardC;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Common Dashboard Redirect
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardC::class, 'redirectDashboard'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */
    Route::middleware(['web.admin.access'])->group(function () {
        Route::get('/admin/dashboard', [DashboardC::class, 'adminIndex'])->name('admin.dashboard');
        Route::get('/generate-storage-link', [DashboardC::class, 'generateStorageLink']);

        /*
        |--------------------------------------------------------------------------
        | Coming Soon Module Pages
        |--------------------------------------------------------------------------
        */
        Route::get('/module/crm', function () {
            return view('pages.coming-soon')->with('module', 'crm');
        })->name('module.crm');

        Route::get('/module/project-mgmt', function () {
            return view('pages.coming-soon')->with('module', 'project-mgmt');
        })->name('module.project-mgmt');

        Route::get('/module/finance', function () {
            return view('pages.coming-soon')->with('module', 'finance');
        })->name('module.finance');
    });

    /*
    |--------------------------------------------------------------------------
    | Employee Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/employee/dashboard', [DashboardC::class, 'employeeIndex'])->name('employee.dashboard');
});