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
}
