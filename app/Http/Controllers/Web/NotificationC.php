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
     * Mark single notification as read.
     */
    public function markAsRead($id)
    {
        $notification = NotificationM::forUser(auth()->id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        NotificationM::forUser(auth()->id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
            ]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete/dismiss a notification.
     */
    public function destroy($id)
    {
        NotificationM::forUser(auth()->id())
            ->where('id', $id)
            ->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification dismissed.',
            ]);
        }

        return redirect()->back()->with('success', 'Notification dismissed.');
    }

    /**
     * Mark notification as read and redirect to target.
     */
    public function open(NotificationM $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        $routeName = $notification->route_name;
        $routeParams = is_array($notification->route_params) ? $notification->route_params : [];
        $data = is_array($notification->data) ? $notification->data : [];

        if (!empty($data['action_url'])) {
            return redirect((string) $data['action_url']);
        }

        if (empty($routeName) && !empty($data['route_name']) && Route::has((string) $data['route_name'])) {
            $routeName = (string) $data['route_name'];
            $routeParams = is_array($data['route_params'] ?? null) ? $data['route_params'] : $routeParams;
        }

        if (empty($routeName) && ! empty($data['notification_type'])) {
            $type = (string) $data['notification_type'];
            if ($type === 'profile_submitted' && ! empty($data['employee_id']) && Route::has('hrms.employees.profile.view')) {
                return redirect()->route('hrms.employees.profile.view', ['employee' => $data['employee_id']]);
            }
            if (in_array($type, ['document_uploaded', 'document_reuploaded'], true) && ! empty($data['employee_id']) && Route::has('documents.employee.show')) {
                return redirect()->route('documents.employee.show', ['employee' => $data['employee_id']]);
            }
        }

        if (!empty($routeName) && Route::has($routeName)) {
            try {
                $url = route($routeName, $routeParams);

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
