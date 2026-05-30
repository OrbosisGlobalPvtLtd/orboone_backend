<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Models\Core\NotificationM as Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $perPage = max(1, min((int) $request->query('per_page', 50), 100));
        $notifications = Notification::where('user_id', auth()->id())
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Notifications fetched successfully.',
            'data' => [
                'notifications' => collect($notifications->items())
                    ->map(fn ($notification) => $this->formatNotification($notification))
                    ->values(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
            'errors' => null,
        ]);
    }

    public function show($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Notification details fetched successfully.',
            'data' => $this->formatNotification($notification),
            'errors' => null,
        ]);
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

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Notification marked as read.',
            'data' => $this->formatNotification($notification->fresh()),
            'errors' => null,
        ]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->where('is_read', 0)->orWhereNull('read_at');
            })
            ->update([
                'is_read' => 1,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'All notifications marked as read.',
            'data' => [
                'unread_count' => 0,
            ],
            'errors' => null,
        ]);
    }

    private function formatNotification(Notification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];
        $routeParams = $notification->route_params;

        if (is_string($routeParams)) {
            $decoded = json_decode($routeParams, true);
            $routeParams = is_array($decoded) ? $decoded : [];
        }

        return [
            'id' => $notification->id,
            'user_id' => $notification->user_id,
            'role_id' => $notification->role_id ?? null,
            'type' => $notification->type ?? ($data['type'] ?? 'general'),
            'title' => $notification->title,
            'message' => $notification->message,
            'route_name' => $notification->route_name ?? ($data['route_name'] ?? ''),
            'route_params' => $routeParams ?: ($data['route_params'] ?? []),
            'data' => $data,
            'attachment' => $data['attachment'] ?? null,
            'attachment_url' => $data['attachment_url'] ?? '',
            'attachment_api_url' => $data['attachment_api_url'] ?? $data['api_attachment_url'] ?? $data['file_api_url'] ?? '',
            'api_attachment_url' => $data['api_attachment_url'] ?? $data['attachment_api_url'] ?? $data['file_api_url'] ?? '',
            'web_attachment_url' => $data['web_attachment_url'] ?? $data['attachment_url'] ?? $data['file_url'] ?? '',
            'file_url' => $data['file_url'] ?? $data['attachment_url'] ?? '',
            'file_api_url' => $data['file_api_url'] ?? $data['attachment_api_url'] ?? $data['api_attachment_url'] ?? '',
            'image_url' => $data['image_url'] ?? '',
            'attachment_type' => $data['attachment_type'] ?? '',
            'attachment_name' => $data['attachment_name'] ?? '',
            'is_read' => (bool) $notification->is_read,
            'read_at' => optional($notification->read_at)->toIso8601String(),
            'created_at' => optional($notification->created_at)->toIso8601String(),
            'updated_at' => optional($notification->updated_at)->toIso8601String(),
        ];
    }
}
