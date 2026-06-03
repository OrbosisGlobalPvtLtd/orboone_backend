<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = $this->settings();

        return view('settings.system', [
            'settings' => $settings,
            'mailSettings' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:150'],
            'timezone' => ['required', 'string', 'max:100'],
            'date_format' => ['required', 'string', 'max:50'],
            'attendance_start_time' => ['nullable', 'date_format:H:i'],
            'attendance_end_time' => ['nullable', 'date_format:H:i'],
            'late_mark_after_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'hrms_support_email' => ['nullable', 'email', 'max:150'],
        ]);

        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'system',
                    'type' => is_numeric($value) ? 'number' : 'string',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return redirect()
            ->route('settings.system.index')
            ->with('success', 'System settings updated successfully.');
    }

    private function settings(): array
    {
        $defaults = [
            'app_name' => config('app.name', 'OrboOne HRMS'),
            'timezone' => config('app.timezone', 'Asia/Kolkata'),
            'date_format' => 'd M Y',
            'attendance_start_time' => '09:30',
            'attendance_end_time' => '18:30',
            'late_mark_after_minutes' => '15',
            'hrms_support_email' => config('mail.from.address'),
        ];

        $saved = DB::table('settings')
            ->where('group', 'system')
            ->pluck('value', 'key')
            ->toArray();

        return array_merge($defaults, $saved);
    }
}
