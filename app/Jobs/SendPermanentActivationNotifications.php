<?php

namespace App\Jobs;

use App\Services\HRMS\Notification\NotificationS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPermanentActivationNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 120];

    public ?string $eventKey = null;
    // Retained for already-serialized jobs from the previous job contract.
    public int $employeeId = 0;
    public int $userId = 0;
    public string $effectiveDate = '';

    public function __construct(
        string|int $eventOrEmployeeId,
        ?int $userId = null,
        ?string $effectiveDate = null
    ) {
        if (is_string($eventOrEmployeeId) && $userId === null && $effectiveDate === null) {
            $this->eventKey = $eventOrEmployeeId;
            return;
        }

        $this->employeeId = (int) $eventOrEmployeeId;
        $this->userId = (int) $userId;
        $this->effectiveDate = (string) $effectiveDate;
    }

    public function handle(NotificationS $notifications): void
    {
        if ($this->eventKey !== null) {
            $notifications->deliverPermanentActivationEvent($this->eventKey);
            return;
        }

        $notifications->notifyPermanentActivation($this->employeeId, $this->userId, $this->effectiveDate);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Permanent activation notification job exhausted retries', [
            'event_key' => $this->eventKey ?: "permanent_activated:{$this->employeeId}:{$this->effectiveDate}",
            'error' => $exception?->getMessage(),
        ]);
    }
}
