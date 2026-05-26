<?php

namespace App\Http\Controllers\Web\HRMS\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class NotificationC extends Controller
{
    public function open($notification)
    {
        $notificationData = DB::table('notifications')
            ->where('id', $notification)
            ->where('user_id', auth()->id())
            ->first();

        abort_if(! $notificationData, 404);

        $updateData = [
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('notifications', 'is_read')) {
            $updateData['is_read'] = 1;
        }

        if (Schema::hasColumn('notifications', 'read_at')) {
            $updateData['read_at'] = now();
        }

        DB::table('notifications')
            ->where('id', $notificationData->id)
            ->update($updateData);

        $routeName = $notificationData->route_name ?? null;
        $routeParams = [];

        if (! empty($notificationData->route_params)) {
            $decoded = json_decode($notificationData->route_params, true);
            $routeParams = is_array($decoded) ? $decoded : [];
        }

        $data = ! empty($notificationData->data)
            ? json_decode($notificationData->data, true)
            : [];

        if (! empty($routeName) && Route::has($routeName)) {
            $url = route($routeName, $routeParams);
            $queryParts = [];

            if (! empty($data['employee_id'])) {
                $queryParts['highlight_employee'] = $data['employee_id'];
                $queryParts['highlight'] = $data['employee_id'];
            }

            if (! empty($data['highlight_employee_id'])) {
                $queryParts['highlight_employee'] = $data['highlight_employee_id'];
                $queryParts['highlight'] = $data['highlight_employee_id'];
            }

            if (! empty($data['stage'])) {
                $queryParts['stage'] = $data['stage'];
            } elseif (! empty($data['lifecycle_type'])) {
                $queryParts['stage'] = $data['lifecycle_type'];
            }

            if (! empty($queryParts)) {
                $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($queryParts);
            }

            return redirect($url);
        }

        return redirect()->back();
    }
}
