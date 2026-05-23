<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Services\Notification\FcmNotificationS;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::post('/test-fcm', function (FcmNotificationS $fcm) {
        $user = auth()->user();
        $token = (string) ($user->fcm_token ?? '');
        $hasToken = trim($token) !== '';
        $success = false;

        if ($hasToken) {
            $success = $fcm->sendPush(
                $token,
                'OrboOne HRMS Test',
                'Test push notification from OrboOne HRMS.',
                [
                    'type' => 'test_fcm',
                    'title' => 'OrboOne HRMS Test',
                    'message' => 'Test push notification from OrboOne HRMS.',
                    'route_name' => 'notifications',
                    'route_params' => '{}',
                    'data' => '{}',
                    'attachment_url' => '',
                    'attachment_type' => '',
                    'attachment_name' => '',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'has_token' => $hasToken,
            'token_start' => $hasToken ? substr($token, 0, 18) : null,
            'fcm_success' => $success,
            'fcm_response' => $fcm->lastResponse(),
        ]);
    });
});
