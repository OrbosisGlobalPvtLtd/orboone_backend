<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Notification\NotificationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
});