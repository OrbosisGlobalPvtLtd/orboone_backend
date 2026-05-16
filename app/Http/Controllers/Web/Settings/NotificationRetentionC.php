<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Notification\NotificationRetentionSettingM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationRetentionC extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermission('settings.notification_retention.manage')) {
            abort(403);
        }

        $settings = NotificationRetentionSettingM::orderBy('id')->get();
        return view('settings.notification_retention.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!Auth::user()->hasPermission('settings.notification_retention.manage')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'id' => ['required', 'exists:notification_retention_settings,id'],
            'retention_days' => ['required', 'integer', 'min:7', 'max:3650'],
            'delete_only_read' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $setting = NotificationRetentionSettingM::findOrFail($request->id);
        
        $setting->update([
            'retention_days' => $request->retention_days,
            'delete_only_read' => $request->boolean('delete_only_read'),
            'is_active' => $request->boolean('is_active'),
            'updated_by_user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully for ' . $setting->display_name
        ]);
    }
}
