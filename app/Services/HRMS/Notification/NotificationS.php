<?php

namespace App\Services\HRMS\Notification;

use App\Jobs\SendPermanentActivationNotifications;
use App\Mail\HolidayWorkRequestMail;
use App\Mail\HrWorkflowAlertMail;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Services\Notification\FcmNotificationS;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class NotificationS
{
    private string $notificationsTable = 'notifications';

    /** Idempotent delivery for a committed permanent-activation lifecycle event. */
    public function notifyPermanentActivation(int $employeeId, int $employeeUserId, string $effectiveDate): void
    {
        $eventKey = "permanent_activated:{$employeeId}:{$effectiveDate}";
        $this->ensurePermanentActivationEvent($employeeId, $employeeUserId, $effectiveDate);
        $this->deliverPermanentActivationEvent($eventKey);
    }

    /** Create durable, indexed notification/event rows inside the activation transaction. */
    public function ensurePermanentActivationEvent(int $employeeId, int $employeeUserId, string $effectiveDate): void
    {
        if (! Schema::hasTable($this->notificationsTable)) {
            throw new RuntimeException('Permanent activation notifications table is unavailable.');
        }

        $eventKey = "permanent_activated:{$employeeId}:{$effectiveDate}";
        DB::transaction(function () use ($employeeId, $employeeUserId, $effectiveDate, $eventKey) {
            $employee = DB::table('employees_new')->where('id', $employeeId)->lockForUpdate()->first();
            if (! $employee) {
                return;
            }

            $employeeName = DB::table('users')->where('id', $employeeUserId)->value('name') ?: 'Employee';
            $payloadData = [
                'employee_id' => $employeeId,
                'target_date' => $effectiveDate,
                'lifecycle_event_key' => $eventKey,
                'activation_delivery' => ['fcm' => false, 'email' => false],
            ];
            $this->createActivationNotificationIfMissing(
                $employeeUserId,
                null,
                'Your permanent confirmation has been activated successfully.',
                $eventKey,
                $payloadData
            );

            $formattedDate = Carbon::parse($effectiveDate, 'Asia/Kolkata')->format('d M Y');
            foreach ($this->hrAndSuperAdminUsers() as $user) {
                $this->createActivationNotificationIfMissing(
                    (int) $user->id,
                    $user->system_role_id ?? null,
                    "Permanent confirmation has been activated for {$employeeName} effective from {$formattedDate}.",
                    $eventKey,
                    $payloadData
                );
            }
        });
    }

    private function createActivationNotificationIfMissing(int $userId, ?int $roleId, string $message, string $eventKey, array $data): void
    {
        if (DB::table($this->notificationsTable)->where('user_id', $userId)->where('lifecycle_event_key', $eventKey)->exists()) {
            return;
        }

        $title = 'Permanent Confirmation Activated';
        $payload = $this->standardPayload('permanent_activated', $title, $message, null, [], $data);
        DB::table($this->notificationsTable)->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'title' => $title,
            'message' => $message,
            'type' => 'permanent_activated',
            'route_name' => null,
            'route_params' => json_encode([]),
            'data' => json_encode($payload),
            'is_read' => 0,
            'lifecycle_event_key' => $eventKey,
            'activation_delivery_status' => 'pending',
            'activation_delivery_attempts' => 0,
            'created_at' => now('Asia/Kolkata'),
            'updated_at' => now('Asia/Kolkata'),
        ]);
    }

    /** Deliver all recipient rows for a durable activation event. */
    public function deliverPermanentActivationEvent(string $eventKey): void
    {
        DB::table($this->notificationsTable)
            ->where('lifecycle_event_key', $eventKey)
            ->where('activation_delivery_status', '!=', 'completed')
            ->orderBy('id')
            ->pluck('id')
            ->each(fn ($id) => $this->deliverPermanentActivationNotification((int) $id));
    }

    /** Claim the event before dispatch so the minute sweep does not enqueue it repeatedly. */
    public function dispatchPermanentActivationEvent(string $eventKey): bool
    {
        $now = now('Asia/Kolkata');
        $staleBefore = $now->copy()->subMinutes(10);
        $notificationIds = DB::transaction(function () use ($eventKey, $staleBefore, $now) {
            $rows = DB::table($this->notificationsTable)
                ->where('lifecycle_event_key', $eventKey)
                ->where(function ($query) use ($staleBefore, $now) {
                    $query->where(function ($pending) use ($now) {
                        $pending->where('activation_delivery_status', 'pending')
                            ->where(function ($due) use ($now) {
                                $due->whereNull('activation_delivery_claimed_at')
                                    ->orWhere('activation_delivery_claimed_at', '<=', $now);
                            });
                    })
                        ->orWhere(function ($stale) use ($staleBefore) {
                            $stale->whereIn('activation_delivery_status', ['queued', 'processing'])
                                ->where('activation_delivery_claimed_at', '<=', $staleBefore);
                        });
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');

            if ($rows->isEmpty()) {
                return [];
            }

            DB::table($this->notificationsTable)->whereIn('id', $rows)->update([
                'activation_delivery_status' => 'queued',
                'activation_delivery_claimed_at' => now('Asia/Kolkata'),
                'activation_delivery_error' => null,
                'updated_at' => now('Asia/Kolkata'),
            ]);

            return $rows->all();
        });

        if (! $notificationIds) {
            return false;
        }

        try {
            dispatch(new SendPermanentActivationNotifications($eventKey));
            return true;
        } catch (\Throwable $e) {
            DB::transaction(function () use ($eventKey, $e) {
                DB::table($this->notificationsTable)
                    ->where('lifecycle_event_key', $eventKey)
                    ->where('activation_delivery_status', 'queued')
                    ->update([
                        'activation_delivery_status' => 'pending',
                        'activation_delivery_claimed_at' => null,
                        'activation_delivery_error' => mb_substr($e->getMessage(), 0, 4000),
                        'updated_at' => now('Asia/Kolkata'),
                    ]);
            });
            throw $e;
        }
    }

    /** Claim briefly, deliver without locks, then persist channel progress briefly. */
    private function deliverPermanentActivationNotification(int $notificationId): void
    {
        $notification = DB::transaction(function () use ($notificationId) {
            $row = DB::table($this->notificationsTable)->where('id', $notificationId)->lockForUpdate()->first();
            if (! $row || $row->activation_delivery_status === 'completed') {
                return null;
            }

            $claimedAt = $row->activation_delivery_claimed_at
                ? Carbon::parse($row->activation_delivery_claimed_at, 'Asia/Kolkata')
                : null;
            if ($row->activation_delivery_status === 'processing'
                && $claimedAt
                && $claimedAt->gt(now('Asia/Kolkata')->subMinutes(10))) {
                return null;
            }

            DB::table($this->notificationsTable)->where('id', $notificationId)->update([
                'activation_delivery_status' => 'processing',
                'activation_delivery_claimed_at' => now('Asia/Kolkata'),
                'activation_delivery_attempts' => DB::raw('activation_delivery_attempts + 1'),
                'updated_at' => now('Asia/Kolkata'),
            ]);
            return DB::table($this->notificationsTable)->where('id', $notificationId)->first();
        });

        if (! $notification) {
            return;
        }

        $payload = json_decode((string) ($notification->data ?? ''), true) ?: [];
        $eventData = (array) ($payload['data'] ?? []);
        $delivery = (array) ($eventData['activation_delivery'] ?? []);
        $type = (string) ($payload['type'] ?? $notification->type ?? 'permanent_activated');
        $title = (string) ($payload['title'] ?? $notification->title ?? 'Permanent Confirmation Activated');
        $message = (string) ($payload['message'] ?? $notification->message ?? 'Permanent confirmation has been activated successfully.');
        $routeName = $payload['route_name'] ?? $notification->route_name ?? null;
        $routeParams = (array) ($payload['route_params'] ?? []);
        $policy = app(NotificationPolicyS::class);

        try {
            foreach (['fcm', 'email'] as $channel) {
                if (! $policy->{'shouldSend' . ucfirst($channel)}($type, $eventData)) {
                    $delivery[$channel] = true;
                }
                if (! empty($delivery[$channel])) {
                    continue;
                }

                if ($channel === 'fcm') {
                    $this->sendFcmPush(
                        (int) $notification->id,
                        $notification->user_id ? (int) $notification->user_id : null,
                        ($notification->role_id ?? null) ? (int) $notification->role_id : null,
                        $title,
                        $message,
                        $type,
                        $routeName,
                        $routeParams,
                        $payload,
                        true
                    );
                } else {
                    $this->sendEmailNotification(
                        $notification->user_id ? (int) $notification->user_id : null,
                        $title,
                        $message,
                        $payload,
                        true
                    );
                }

                $delivery[$channel] = true;
                $eventData['activation_delivery'] = $delivery;
                $payload['data'] = $eventData;
                $this->persistActivationNotificationProgress($notificationId, $payload, false);
            }

            $eventData['activation_delivery'] = $delivery;
            $payload['data'] = $eventData;
            $this->persistActivationNotificationProgress($notificationId, $payload, true);
        } catch (\Throwable $e) {
            DB::transaction(function () use ($notificationId, $e) {
                DB::table($this->notificationsTable)->where('id', $notificationId)->update([
                    'activation_delivery_status' => 'pending',
                    'activation_delivery_claimed_at' => now('Asia/Kolkata')->addMinutes(10),
                    'activation_delivery_error' => mb_substr($e->getMessage(), 0, 4000),
                    'updated_at' => now('Asia/Kolkata'),
                ]);
            });
            throw $e;
        }
    }

    private function persistActivationNotificationProgress(int $notificationId, array $payload, bool $completed): void
    {
        DB::transaction(function () use ($notificationId, $payload, $completed) {
            DB::table($this->notificationsTable)->where('id', $notificationId)->update([
                'data' => json_encode($payload),
                'activation_delivery_status' => $completed ? 'completed' : 'processing',
                'activation_delivery_claimed_at' => $completed ? null : now('Asia/Kolkata'),
                'activation_delivery_error' => null,
                'updated_at' => now('Asia/Kolkata'),
            ]);
        });
    }

    public function notifyHrAndSuperAdmin(
        string $title,
        string $message,
        string $type,
        ?string $routeName = null,
        array $routeParams = [],
        array $data = []
    ): void {
        if (! Schema::hasTable($this->notificationsTable)) {
            return;
        }

        $users = $this->hrAndSuperAdminUsers();

        foreach ($users as $user) {
            $employeeId = $data['employee_id'] ?? null;
            $targetDate = $data['target_date'] ?? null;
            $reminderDate = $data['reminder_date'] ?? null;

            if (
                $employeeId
                && $reminderDate
                && $this->alreadySentByReminderDate($type, (int) $employeeId, (string) $reminderDate, (int) $user->id)
            ) {
                continue;
            }

            if (! $reminderDate && $employeeId && $targetDate) {
                if ($this->unresolvedReminderExists($type, (int) $employeeId, (string) $targetDate, (int) $user->id)) {
                    continue;
                }
            }

            $this->createNotification(
                userId: $user->id,
                roleId: $user->system_role_id ?? null,
                title: $title,
                message: $message,
                type: $type,
                routeName: $routeName,
                routeParams: $routeParams,
                data: $data
            );
        }
    }

    /**
     * Notify a specific employee/user.
     */
    public function notifyEmployee(
        string $title,
        string $message,
        string $type,
        ?string $routeName = null,
        array $routeParams = [],
        array $data = [],
        ?int $userId = null
    ): void {
        if (!$userId) {
            return;
        }

        $this->createNotification(
            userId: $userId,
            roleId: null,
            title: $title,
            message: $message,
            type: $type,
            routeName: $routeName,
            routeParams: $routeParams,
            data: $data
        );
    }

    public function notifyUser(int $userId, string $title, string $message, array $data = []): void
    {
        $this->notifyEmployee(
            title: $title,
            message: $message,
            type: $data['type'] ?? 'general',
            routeName: null,
            routeParams: [],
            data: $data,
            userId: $userId
        );
    }

    public function createNotification(
        ?int $userId,
        ?int $roleId,
        string $title,
        string $message,
        string $type,
        ?string $routeName = null,
        array $routeParams = [],
        array $data = [],
        bool $deferExternal = false
    ): ?int {

        if (! Schema::hasTable($this->notificationsTable)) {
            return null;
        }

        $payload = $this->standardPayload($type, $title, $message, $routeName, $routeParams, $data);

        $insert = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($this->notificationsTable, 'is_read')) {
            $insert['is_read'] = 0;
        }

        if (Schema::hasColumn($this->notificationsTable, 'role_id')) {
            $insert['role_id'] = $roleId;
        }

        if (Schema::hasColumn($this->notificationsTable, 'type')) {
            $insert['type'] = $type;
        }

        if (Schema::hasColumn($this->notificationsTable, 'route_name')) {
            $insert['route_name'] = $routeName;
        }

        if (Schema::hasColumn($this->notificationsTable, 'route_params')) {
            $insert['route_params'] = json_encode($routeParams);
        }

        if (Schema::hasColumn($this->notificationsTable, 'data')) {
            $insert['data'] = json_encode($payload);
        }

        $notificationId = DB::table($this->notificationsTable)->insertGetId($insert);

        if ($deferExternal) {
            return $notificationId;
        }

        $policy = app(NotificationPolicyS::class);

        if ($policy->shouldSendFcm($type, $payload)) {
            $this->sendFcmPush(
                $notificationId,
                $userId,
                $roleId,
                $title,
                $message,
                $type,
                $routeName,
                $routeParams,
                $payload
            );
        }

        if ($policy->shouldSendEmail($type, $payload)) {
            $this->sendEmailNotification($userId, $title, $message, $payload);
        }

        return $notificationId;
    }

    /**
     * Send FCM push notification to target user or role.
     */
    private function sendFcmPush(
        $notificationId,
        ?int $userId,
        ?int $roleId,
        string $title,
        string $message,
        string $type,
        ?string $routeName = null,
        array $routeParams = [],
        array $data = [],
        bool $throwOnFailure = false
    ): void {
        try {
            $fcmService = app(FcmNotificationS::class);

            $payload = array_merge($this->standardPayload($type, $title, $message, $routeName, $routeParams, $data), [
                'notification_id' => (string) $notificationId,
            ]);

            // If notification is for a specific user
            if ($userId) {
                $user = DB::table('users')->where('id', $userId)->first();
                if ($user) {
                    $tokens = [];
                    if (! empty($user->fcm_token)) {
                        $tokens = array_merge($tokens, preg_split('/[\s,]+/', trim($user->fcm_token)));
                    }
                    if (Schema::hasColumn('users', 'device_token') && ! empty($user->device_token)) {
                        $tokens = array_merge($tokens, preg_split('/[\s,]+/', trim($user->device_token)));
                    }
                    
                    $uniqueTokens = array_filter(array_unique($tokens));
                    
                    if (! empty($uniqueTokens)) {
                        foreach ($uniqueTokens as $token) {
                            if (! $fcmService->sendPush($token, $title, $message, $payload) && $throwOnFailure) {
                                throw new RuntimeException('FCM permanent activation delivery failed: ' . json_encode($fcmService->lastResponse()));
                            }
                        }
                    } else {
                        Log::warning('Notification FCM skipped: token missing', [
                            'notification_id' => $notificationId,
                            'user_id' => $userId,
                        ]);
                    }
                }
            }
            // If notification is for a role (e.g. HR/Admin)
            elseif ($roleId) {
                $users = DB::table('users')
                    ->where('system_role_id', $roleId)
                    ->where('is_active', 1)
                    ->whereNotNull('fcm_token')
                    ->where('fcm_token', '!=', '')
                    ->get();

                foreach ($users as $user) {
                    $fcmService->sendPush($user->fcm_token, $title, $message, $payload);
                }
            }
        } catch (\Throwable $e) {
            Log::error('FCM Push Hook Error: ' . $e->getMessage());
            if ($throwOnFailure) {
                throw $e;
            }
        }
    }

    private function sendEmailNotification(?int $userId, string $title, string $message, array $payload, bool $throwOnFailure = false): void
    {
        if (! $userId || ! Schema::hasTable('users')) {
            return;
        }

        $user = DB::table('users')->select('id', 'name', 'email')->where('id', $userId)->first();

        if (! $user || empty($user->email)) {
            Log::info('Notification email skipped: email missing', [
                'user_id' => $userId,
                'type' => $payload['type'] ?? null,
            ]);
            return;
        }

        try {
            $payload = is_array($payload) ? $payload : (array) $payload;
            $payloadData = data_get($payload, 'data', []);
            $payloadData = is_array($payloadData) ? $payloadData : (array) $payloadData;

            $type = $payload['type'] ?? null;

            // 1. Centralized HR workflow emails sent to the collective HR inbox (hr@orbosis.com)
            if (in_array($type, ['profile_submitted', 'employee_exit_initiated'], true)) {
                $employeeId = data_get($payload, 'employee_id') ?? data_get($payloadData, 'employee_id');
                if ($employeeId) {
                    $hrMailKey = 'hr_collective_mail:' . $type . ':' . $employeeId;
                    
                    if (Cache::add($hrMailKey, 1, now()->addHour())) {
                        $hrEmail = config('hrms.emails.hr');
                        if ($hrEmail) {
                            $actionUrl = null;
                            if (! empty($payload['route_name'])) {
                                try {
                                    $actionUrl = route($payload['route_name'], (array) ($payload['route_params'] ?? []));
                                } catch (\Throwable $e) {}
                            }
                            if (empty($actionUrl) && !empty($payload['action_url'])) {
                                $actionUrl = $payload['action_url'];
                            }

                            Mail::to($hrEmail)->queue(
                                new HrWorkflowAlertMail(
                                    subjectText: $title,
                                    workflowTitle: $title,
                                    details: [
                                        'Event' => $title,
                                        'Message' => $message,
                                        'Employee Code' => data_get($payload, 'employee_code') ?? data_get($payloadData, 'employee_code') ?? 'N/A',
                                        'Date' => now()->toDateTimeString(),
                                    ],
                                    actionUrl: $actionUrl
                                )
                            );
                            
                            Log::info('Collective HR email queued successfully', [
                                'type' => $type,
                                'employee_id' => $employeeId,
                                'hr_email' => $hrEmail,
                            ]);
                        }
                    }
                }
                return; 
            }

            // 2. Custom Mailable for Holiday Work Requests
            if (in_array($type, ['holiday_work_request_submitted', 'holiday_work_request_approved', 'holiday_work_request_rejected'], true)) {
                $requestId = data_get($payload, 'request_id') ?? data_get($payloadData, 'request_id');
                if ($requestId) {
                    $holidayRequest = HolidayWorkRequestM::with(['employee.department'])->find($requestId);
                    if ($holidayRequest) {
                        $mailKey = 'holiday_work_mail:' . $type . ':' . $requestId . ':' . $userId;
                        if (! Cache::add($mailKey, 1, now()->addMinutes(5))) {
                            Log::info('Holiday work request email skipped (duplicate in cooldown window)', [
                                'user_id' => $userId,
                                'type' => $type,
                                'request_id' => $requestId,
                            ]);
                            return;
                        }

                        $rejectionReason = data_get($payload, 'rejection_reason') ?? data_get($payloadData, 'rejection_reason');
                        $reviewerName = data_get($payload, 'reviewer_name') ?? data_get($payloadData, 'reviewer_name');
                        $actionUrl = data_get($payload, 'action_url') ?? data_get($payloadData, 'action_url');
                        if (empty($actionUrl)) {
                            try {
                                $actionUrl = route('hrms.attendance.holiday_work.index');
                            } catch (\Throwable $e) {
                                $actionUrl = url('/hrms/attendance/holiday-work');
                            }
                        }
                        
                        Mail::to($user->email)->queue(
                            (new HolidayWorkRequestMail(
                                $holidayRequest,
                                $type === 'holiday_work_request_submitted' ? 'submitted' : ($type === 'holiday_work_request_approved' ? 'approved' : 'rejected'),
                                $actionUrl,
                                $rejectionReason,
                                $reviewerName
                            ))->onQueue('default')
                        );
                        
                        Log::info('Holiday work request email queued', [
                            'user_id' => $userId,
                            'type' => $type,
                            'request_id' => $requestId,
                            'queue_connection' => config('queue.default'),
                            'queue_name' => 'default',
                        ]);
                        return;
                    }
                }
            }

            
            $details = [];
            if (! empty($payload['attachment_url'])) {
                $details['Attachment'] = $payload['attachment_url'];
            }
            
            $actionUrl = null;
            if (! empty($payload['route_name'])) {
                try {
                    $actionUrl = route($payload['route_name'], (array) ($payload['route_params'] ?? []));
                } catch (\Throwable $e) {}
            }
            if (empty($actionUrl) && !empty($payload['action_url'])) {
                $actionUrl = $payload['action_url'];
            }

            Mail::to($user->email)->queue(
                new HrWorkflowAlertMail(
                    subjectText: $title,
                    workflowTitle: $title,
                    details: array_merge(['Message' => $message], $details),
                    actionUrl: $actionUrl
                )
            );

            Log::info('Styled notification email queued', [
                'user_id' => $userId,
                'email' => $user->email,
                'type' => $type,
            ]);
        } catch (\Throwable $e) {
            Log::error('Notification email failed', [
                'user_id' => $userId,
                'email' => $user->email,
                'type' => $payload['type'] ?? null,
                'error' => $e->getMessage(),
            ]);
            if ($throwOnFailure) {
                throw $e;
            }
        }
    }

    private function standardPayload(
        string $type,
        string $title,
        string $message,
        ?string $routeName,
        array $routeParams,
        array $data
    ): array {
        // Resolve attachment details from input data
        $attachmentUrl = $data['attachment_url']
            ?? $data['web_attachment_url']
            ?? $data['file_url']
            ?? $data['document_url']
            ?? $data['apk_url']
            ?? $data['image_url']
            ?? '';

        $attachmentApiUrl = $data['attachment_api_url']
            ?? $data['api_attachment_url']
            ?? $data['file_api_url']
            ?? $data['image_api_url']
            ?? '';

        $attachmentName = $data['attachment_name']
            ?? $data['file_name']
            ?? $data['document_name']
            ?? ($attachmentUrl ? basename(parse_url((string) $attachmentUrl, PHP_URL_PATH) ?: (string) $attachmentUrl) : '');

        $attachmentType = $data['attachment_type']
            ?? $data['file_mime_type']
            ?? $data['mime_type']
            ?? $this->attachmentTypeFromName((string) $attachmentName, (string) $attachmentUrl);

        // Resolve reference ID and Module
        $module = $data['module'] ?? '';
        if (empty($module)) {
            if (str_contains($type, 'announcement') || str_contains($routeName, 'announcements')) {
                $module = 'announcement';
            } elseif (str_contains($type, 'document') || str_contains($routeName, 'documents')) {
                $module = 'document';
            } elseif (str_contains($type, 'leave') || str_contains($routeName, 'leave')) {
                $module = 'leave';
            } elseif (str_contains($type, 'payroll') || str_contains($routeName, 'payroll') || str_contains($type, 'payslip')) {
                $module = 'payroll';
            }
        }

        $referenceId = $data['reference_id']
            ?? $data['announcement_id']
            ?? $data['id']
            ?? $routeParams['id']
            ?? null;

        // If it is an announcement, we can build URLs dynamically if missing
        if ($module === 'announcement' && $referenceId) {
            if (empty($attachmentUrl)) {
                try {
                    $attachmentUrl = route('hrms.announcements.attachment', $referenceId);
                } catch (\Throwable $e) {
                    $attachmentUrl = url("/hrms/announcements/attachment/{$referenceId}");
                }
            }
            if (empty($attachmentApiUrl)) {
                $attachmentApiUrl = url("/api/v1/announcements/{$referenceId}/attachment");
            }
        }

        $hasAttachment = !empty($attachmentUrl) || !empty($attachmentApiUrl);
        $isImage = $attachmentType === 'image';

        // Mime Type fallback
        $mimeType = $data['mime_type'] ?? $data['file_mime_type'] ?? '';
        if (empty($mimeType)) {
            if ($isImage) {
                $mimeType = 'image/png';
            } elseif ($attachmentType === 'pdf') {
                $mimeType = 'application/pdf';
            }
        }

        // Construct standardized attachment object
        $attachmentObj = null;
        if ($hasAttachment) {
            $attachmentObj = [
                'name' => (string) $attachmentName,
                'type' => (string) $attachmentType,
                'mime_type' => (string) $mimeType,
                'is_image' => $isImage,
                'web_url' => (string) $attachmentUrl,
                'api_url' => (string) $attachmentApiUrl,
            ];
        }

        $payload = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'route_name' => $routeName ?: '',
            'route_params' => (object) $routeParams,
            'data' => (object) $data,
            
            // Structured attachment payload
            'module' => $module,
            'reference_id' => $referenceId,
            'has_attachment' => $hasAttachment,
            'attachment' => $attachmentObj,

            // Legacy/fallback flat fields
            'attachment_url' => (string) ($attachmentUrl ?: ''),
            'attachment_api_url' => (string) ($attachmentApiUrl ?: ''),
            'api_attachment_url' => (string) ($attachmentApiUrl ?: ''),
            'web_attachment_url' => (string) ($attachmentUrl ?: ''),
            'file_url' => (string) ($attachmentUrl ?: ''),
            'file_api_url' => (string) ($attachmentApiUrl ?: ''),
            'image_url' => $isImage ? (string) $attachmentUrl : '',
            'attachment_type' => (string) ($attachmentType ?: ''),
            'attachment_name' => (string) ($attachmentName ?: ''),
        ];

        foreach ($data as $key => $value) {
            if (! array_key_exists($key, $payload)) {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }

    private function attachmentTypeFromName(string $name, string $url): string
    {
        $extension = strtolower(pathinfo($name ?: parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return 'image';
        }

        if ($extension === 'pdf') {
            return 'pdf';
        }

        if (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'], true)) {
            return 'document';
        }

        if ($extension === 'apk') {
            return 'apk';
        }

        return '';
    }

    public function unresolvedReminderExists(
        string $type,
        int $employeeId,
        string $targetDate,
        int $userId
    ): bool {
        if (! Schema::hasTable($this->notificationsTable)) {
            return true;
        }

        $query = DB::table($this->notificationsTable)
            ->where('user_id', $userId);

        if (Schema::hasColumn($this->notificationsTable, 'type')) {
            $query->where('type', $type);
        }

        if (Schema::hasColumn($this->notificationsTable, 'is_read')) {
            $query->where('is_read', 0);
        }

        if (Schema::hasColumn($this->notificationsTable, 'data')) {
            $query->where('data', 'like', '%"employee_id":' . $employeeId . '%')
                ->where('data', 'like', '%"target_date":"' . $targetDate . '"%');
        }

        return $query->exists();
    }

    public function alreadySentByReminderDate(
        string $type,
        int $employeeId,
        string $reminderDate,
        int $userId
    ): bool {
        if (! Schema::hasTable($this->notificationsTable)) {
            return true;
        }

        $query = DB::table($this->notificationsTable)->where('user_id', $userId);

        if (Schema::hasColumn($this->notificationsTable, 'type')) {
            $query->where('type', $type);
        }

        if (Schema::hasColumn($this->notificationsTable, 'data')) {
            $query->where('data', 'like', '%"employee_id":' . $employeeId . '%')
                ->where('data', 'like', '%"reminder_date":"' . $reminderDate . '"%');
        }

        return $query->exists();
    }

    public function alreadySent(
        string $type,
        int $employeeId,
        string $targetDate,
        ?int $userId = null
    ): bool {
        if (! Schema::hasTable($this->notificationsTable)) {
            return true;
        }

        $query = DB::table($this->notificationsTable);

        if (Schema::hasColumn($this->notificationsTable, 'type')) {
            $query->where('type', $type);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if (Schema::hasColumn($this->notificationsTable, 'data')) {
            $query->where('data', 'like', '%"employee_id":' . $employeeId . '%')
                ->where('data', 'like', '%"target_date":"' . $targetDate . '"%');
        }

        return $query->exists();
    }

    public function markEmployeeLifecycleNotificationsResolved(int $employeeId, array $types): void
    {
        if (! Schema::hasTable($this->notificationsTable)) {
            return;
        }

        $update = [
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($this->notificationsTable, 'is_read')) {
            $update['is_read'] = 1;
        }

        if (Schema::hasColumn($this->notificationsTable, 'read_at')) {
            $update['read_at'] = now();
        }

        $query = DB::table($this->notificationsTable);

        if (Schema::hasColumn($this->notificationsTable, 'type')) {
            $query->whereIn('type', $types);
        }

        if (Schema::hasColumn($this->notificationsTable, 'data')) {
            $query->where('data', 'like', '%"employee_id":' . $employeeId . '%');
        }

        $query->update($update);
    }

    private function hrAndSuperAdminUsers()
    {
        $query = DB::table('users')->select('users.id', 'users.system_role_id');

        if (Schema::hasTable('roles')) {
            $query->leftJoin('roles', 'roles.id', '=', 'users.system_role_id');
        }

        if (Schema::hasTable('system_roles')) {
            $query->leftJoin('system_roles', 'system_roles.id', '=', 'users.system_role_id');
        }

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('users.is_active', 1);
        }

        $query->where(function ($q) {
            if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'name')) {
                $q->orWhereIn('roles.name', [
                    'super_admin',
                    'Super Admin',
                    'hr_admin',
                    'HR Admin',
                    'admin',
                    'Admin',
                ]);
            }

            if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'slug')) {
                $q->orWhereIn('roles.slug', [
                    'super_admin',
                    'hr_admin',
                    'admin',
                ]);
            }

            if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'title')) {
                $q->orWhereIn('roles.title', [
                    'super_admin',
                    'Super Admin',
                    'hr_admin',
                    'HR Admin',
                    'admin',
                    'Admin',
                ]);
            }

            if (Schema::hasTable('system_roles')) {
                $q->orWhereIn('system_roles.slug', [
                    'super_admin',
                    'hr_admin',
                    'admin',
                ])->orWhereIn('system_roles.name', [
                    'Super Admin',
                    'HR Admin',
                    'Admin',
                ]);
            }
        });

        return $query->distinct()->get();
    }
}
