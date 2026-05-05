<?php

namespace App\Legacy\Http\Controllers\Api\Flat;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())->get();

        return response()->json($notifications);
    }
}
