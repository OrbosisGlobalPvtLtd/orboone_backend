<?php

use App\Http\Controllers\AttendancesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Attendance Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.access'])->group(function () {
    Route::get('/attendances', [AttendancesController::class, 'index'])->name('attendances');
    Route::get('/attendances/print', [AttendancesController::class, 'print'])->name('attendances_print');
    Route::get('/attendances/export-pdf', [AttendancesController::class, 'exportPdf'])->name('attendances.export-pdf');

    Route::post('/attendances', [AttendancesController::class, 'store'])->name('attendances.store');
    Route::put('/attendances', [AttendancesController::class, 'update'])->name('attendances.update');
    Route::delete('/attendances/{attendance}', [AttendancesController::class, 'destroy'])->name('attendances.destroy');

    Route::post('/attendances/unlock', [AttendancesController::class, 'unlock'])->name('attendances.unlock');
    Route::post('/attendances/admin/punch-in', [AttendancesController::class, 'adminPunchIn'])->name('attendances.admin.punch-in');
    Route::post('/attendances/admin/punch-out', [AttendancesController::class, 'adminPunchOut'])->name('attendances.admin.punch-out');
});