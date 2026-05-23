<?php

use App\Http\Controllers\Web\Dashboard\DashboardC;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardC::class, 'redirectDashboard'])->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/super-admin', [DashboardC::class, 'superAdmin'])->name('super_admin');
        Route::get('/hr-admin', [DashboardC::class, 'hrAdmin'])->name('hr_admin');
        Route::get('/finance-admin', [DashboardC::class, 'financeAdmin'])->name('finance_admin');
        Route::get('/project-admin', [DashboardC::class, 'projectAdmin'])->name('project_admin');
        Route::get('/operations-admin', [DashboardC::class, 'operationsAdmin'])->name('operations_admin');
        Route::get('/custom-admin', [DashboardC::class, 'customAdmin'])->name('custom_admin');
        Route::get('/employee', [DashboardC::class, 'employee'])->middleware(['employee.user', 'check.profile.complete'])->name('employee');
    });

    Route::get('/admin/dashboard', [DashboardC::class, 'adminIndex'])->name('admin.dashboard');
    Route::get('/employee/dashboard', [DashboardC::class, 'employeeIndex'])->middleware(['employee.user', 'check.profile.complete'])->name('employee.dashboard');
    Route::get('/generate-storage-link', [DashboardC::class, 'generateStorageLink']);

    Route::middleware(['web.admin.access'])->group(function () {
        Route::get('/module/crm', function () {
            return view('settings.coming-soon')->with('module', 'crm');
        })->name('module.crm');

        Route::get('/module/project-mgmt', function () {
            return view('settings.coming-soon')->with('module', 'project-mgmt');
        })->name('module.project-mgmt');

        Route::get('/module/finance', function () {
            return view('settings.coming-soon')->with('module', 'finance');
        })->name('module.finance');
    });
});
