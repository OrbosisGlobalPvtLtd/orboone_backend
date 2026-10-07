<?php

namespace App\Console\Commands;

use App\Services\HRMS\Notification\NotificationS;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispatchPermanentActivationNotifications extends Command
{
    protected $signature = 'hrms:dispatch-permanent-activation-notifications';
    protected $description = 'Dispatch durable pending permanent activation notification events';

    public function handle(): int
    {
        $now = now('Asia/Kolkata');
        $staleBefore = $now->copy()->subMinutes(10);
        $pending = function ($query) use ($now, $staleBefore) {
            $query->where(function ($due) use ($now) {
                $due->where('activation_delivery_status', 'pending')
                    ->where(function ($dueAt) use ($now) {
                        $dueAt->whereNull('activation_delivery_claimed_at')
                            ->orWhere('activation_delivery_claimed_at', '<=', $now);
                    });
            })
                ->orWhere(function ($stale) use ($staleBefore) {
                    $stale->whereIn('activation_delivery_status', ['queued', 'processing'])
                        ->where('activation_delivery_claimed_at', '<=', $staleBefore);
                });
        };

        $dispatched = 0;
        $failed = 0;
        DB::table('notifications as n')
            ->select('n.id', 'n.lifecycle_event_key')
            ->whereNotNull('n.lifecycle_event_key')
            ->where($pending)
            ->whereNotExists(function ($query) use ($pending) {
                $query->selectRaw('1')
                    ->from('notifications as earlier')
                    ->whereColumn('earlier.lifecycle_event_key', 'n.lifecycle_event_key')
                    ->whereColumn('earlier.id', '<', 'n.id')
                    ->where(function ($eligible) use ($pending) {
                        $pending($eligible);
                    });
            })
            ->orderBy('n.id')
            ->chunkById(250, function ($rows) use (&$dispatched, &$failed) {
                foreach ($rows as $row) {
                    try {
                        if (app(NotificationS::class)->dispatchPermanentActivationEvent((string) $row->lifecycle_event_key)) {
                            $dispatched++;
                        }
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('Durable permanent activation notification dispatch failed', [
                            'event_key' => (string) $row->lifecycle_event_key,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }, 'n.id', 'id');

        $this->info("Durable activation notification dispatch: queued {$dispatched}, failed {$failed}.");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
