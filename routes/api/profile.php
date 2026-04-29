<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Profile\ProfileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/generate-storage-link/{token}', [ProfileController::class, 'generateStorageLink']);
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::get('/holidays', [ProfileController::class, 'listHolidays']);
});