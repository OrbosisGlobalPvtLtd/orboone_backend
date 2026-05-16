<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\NotificationM as Notification;
use App\Models\HRMS\Notification\NotificationRetentionSettingM;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old notifications based on dynamic retention settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification cleanup...');
        Log::info('Notification cleanup started.');

        $settings = NotificationRetentionSettingM::where('is_active', true)->get();
        $totalDeleted = 0;

        foreach ($settings as $setting) {
            $type = $setting->notification_type;
            $days = max($setting->retention_days, 7); // Safety: Never delete notifications from last 7 days
            $cutoffDate = Carbon::now()->subDays($days);
            
            $query = Notification::where('created_at', '<', $cutoffDate);

            // Dynamic type matching logic
            if ($type === 'announcement') {
                $query->where('type', 'announcement');
            } elseif (in_array($type, ['attendance', 'document', 'leave'])) {
                $query->where('type', 'LIKE', $type . '_%');
            } elseif ($type === 'payroll') {
                $query->where('type', 'LIKE', 'payroll%');
            } elseif ($type === 'general') {
                // For general, we delete everything that doesn't match other active settings
                $otherTypes = $settings->pluck('notification_type')->reject(fn($t) => $t === 'general')->toArray();
                foreach ($otherTypes as $ot) {
                    if ($ot === 'announcement') {
                        $query->where('type', '!=', 'announcement');
                    } elseif (in_array($ot, ['attendance', 'document', 'leave'])) {
                        $query->where('type', 'NOT LIKE', $ot . '_%');
                    } elseif ($ot === 'payroll') {
                        $query->where('type', 'NOT LIKE', 'payroll%');
                    }
                }
            }

            // Only delete read notifications if setting is enabled
            if ($setting->delete_only_read) {
                $query->where('is_read', 1);
            }

            // Perform deletion in chunks for safety
            $count = 0;
            $query->chunkById(1000, function ($notifications) use (&$count) {
                foreach ($notifications as $notification) {
                    $notification->delete();
                    $count++;
                }
            });

            if ($count > 0) {
                $this->line("Deleted $count notifications for category: $setting->display_name");
                Log::info("Cleanup: Deleted $count notifications for category: $setting->display_name");
                $totalDeleted += $count;
            }
        }

        $this->info("Cleanup complete. Total notifications deleted: $totalDeleted");
        Log::info("Notification cleanup finished. Total deleted: $totalDeleted");
    }
}
