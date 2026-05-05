<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProjectManagement\TaskController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'myTasks']);
    Route::get('/tasks/{id}', [TaskController::class, 'taskDetail']);
    Route::put('/tasks/{id}', [TaskController::class, 'adminUpdateTask']);
    Route::get('/my-tasks', [TaskController::class, 'getMyTasks']);
    Route::post('/my-tasks/{id}/update', [TaskController::class, 'updateMyTask']);
    Route::get('/tasks/{id}/details', [TaskController::class, 'getTaskDetails']);
});