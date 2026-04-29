<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Notification\NotificationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
});