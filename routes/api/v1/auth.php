<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/check-token', [AuthController::class, 'checkToken']);
Route::post('/forgot-password/send-otp', [PasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [PasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [PasswordController::class, 'reset']);
Route::post('/change-password', [PasswordController::class, 'changePassword'])->middleware('auth:sanctum');
