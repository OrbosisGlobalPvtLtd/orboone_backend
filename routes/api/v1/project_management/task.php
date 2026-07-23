<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProjectManagement\TaskController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('project_management')->group(function () {
        Route::get('/tasks', [TaskController::class, 'myTasks']);
        Route::post('/tasks', [TaskController::class, 'createTask']);
        Route::get('/tasks/{id}', [TaskController::class, 'taskDetail']);
        Route::put('/tasks/{id}', [TaskController::class, 'adminUpdateTask']);
        Route::get('/my-tasks', [TaskController::class, 'getMyTasks']);
        Route::post('/my-tasks/{id}/update', [TaskController::class, 'updateMyTask']);
        Route::get('/tasks/{id}/details', [TaskController::class, 'getTaskDetails']);
        Route::post('/tasks/{id}/status', [TaskController::class, 'updateTaskStatus']);
        Route::post('/tasks/{id}/comments', [TaskController::class, 'addComment']);
        Route::get('/dashboard-stats', [TaskController::class, 'getDashboardStats']);
        Route::get('/employees', [TaskController::class, 'getAssignableEmployees']);
    });

    // Fallbacks without prefix
    Route::get('/tasks', [TaskController::class, 'myTasks']);
    Route::post('/tasks', [TaskController::class, 'createTask']);
    Route::get('/tasks/{id}', [TaskController::class, 'taskDetail']);
    Route::put('/tasks/{id}', [TaskController::class, 'adminUpdateTask']);
    Route::get('/my-tasks', [TaskController::class, 'getMyTasks']);
    Route::post('/my-tasks/{id}/update', [TaskController::class, 'updateMyTask']);
    Route::get('/tasks/{id}/details', [TaskController::class, 'getTaskDetails']);
    Route::post('/tasks/{id}/status', [TaskController::class, 'updateTaskStatus']);
    Route::post('/tasks/{id}/comments', [TaskController::class, 'addComment']);
    Route::get('/dashboard-stats', [TaskController::class, 'getDashboardStats']);
    Route::get('/employees', [TaskController::class, 'getAssignableEmployees']);
});