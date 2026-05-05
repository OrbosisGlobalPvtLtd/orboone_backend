<?php

use App\Http\Controllers\Web\ProjectManagement\TaskmanagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.admin.access', 'module:hrms'])
    ->prefix('hrms')
    ->name('project_management.tasks.')
    ->group(function () {
        Route::get('/task_management', [TaskmanagementController::class, 'task_management'])
            ->name('index');

        Route::get('/add_task', [TaskmanagementController::class, 'store_task'])
            ->name('create');

        Route::post('/add_task', [TaskmanagementController::class, 'add_task'])
            ->name('store');

        Route::get('/edit_task/{id}', [TaskmanagementController::class, 'edit_task'])
            ->name('edit');

        Route::post('/update_task/{id}', [TaskmanagementController::class, 'update'])
            ->name('update');

        Route::delete('/delete_task/{id}', [TaskmanagementController::class, 'destroy'])
            ->name('destroy');

        Route::get('/task_print', [TaskmanagementController::class, 'task_print'])
            ->name('export');

        Route::get('/my-tasks', [TaskmanagementController::class, 'myTasks'])
            ->name('my');
    });
