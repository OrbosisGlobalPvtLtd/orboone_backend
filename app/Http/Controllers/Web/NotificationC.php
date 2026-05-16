<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Core\NotificationM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class NotificationC extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifications = NotificationM::forUser(auth()->id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read and redirect to target.
     */
    public function open(NotificationM $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        // Mark as read
        $notification->markAsRead();

        $routeName = $notification->route_name;
        $routeParams = $notification->route_params ?? [];
        $data = $notification->data ?? [];

        // Support for route_name + route_params
        if (!empty($routeName) && Route::has($routeName)) {
            try {
                $url = route($routeName, $routeParams);

                // Existing logic for highlighting employee
                if (!empty($data['employee_id'])) {
                    $url .= (str_contains($url, '?') ? '&' : '?') . 'highlight_employee=' . $data['employee_id'];
                }

                return redirect($url);
            } catch (\Exception $e) {
                // If route generation fails, fall back
            }
        }

        // Support for direct URL in data
        if (!empty($data['url'])) {
            return redirect($data['url']);
        }

        return redirect()->back();
    }
}
