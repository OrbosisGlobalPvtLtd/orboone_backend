<?php

use App\Http\Controllers\Web\HRMS\Employee\RecruitmentsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->name('hrms.recruitments.')
    ->group(function () {
        Route::get('/recruitments', [RecruitmentsController::class, 'index'])
            ->name('index');

        Route::get('/recruitments/create', [RecruitmentsController::class, 'create'])
            ->name('create');

        Route::post('/recruitments', [RecruitmentsController::class, 'store'])
            ->name('store');

        Route::get('/recruitments/export', [RecruitmentsController::class, 'print'])
            ->name('export');

        Route::get('/recruitments/{recruitment}', [RecruitmentsController::class, 'show'])
            ->name('show');

        Route::get('/recruitments/{recruitment}/edit', [RecruitmentsController::class, 'edit'])
            ->name('edit');

        Route::put('/recruitments/{recruitment}', [RecruitmentsController::class, 'update'])
            ->name('update');

        Route::delete('/recruitments/{recruitment}', [RecruitmentsController::class, 'destroy'])
            ->name('destroy');
    });
