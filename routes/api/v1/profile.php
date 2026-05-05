<?php

use App\Http\Controllers\Api\V1\Profile\ProfileController;
use App\Http\Controllers\Core\FileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::get('/holidays', [ProfileController::class, 'listHolidays']);

    Route::get('/file', [FileController::class, 'view']);
});