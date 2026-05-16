<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Models\Core\NotificationM as Notification;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())->get();

        return response()->json($notifications);
    }

    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->where('is_read', 0)
                  ->orWhereNull('read_at');
            })
            ->count();

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Unread notification count fetched successfully.',
            'data' => [
                'unread_count' => $count
            ],
            'errors' => null
        ]);
    }
}
