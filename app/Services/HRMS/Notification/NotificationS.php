<?php

namespace App\Services\HRMS\Notification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class NotificationS
{
    private string $notificationsTable = 'notifications';

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

    public function createNotification(
        ?int $userId,
        ?int $roleId,
        string $title,
        string $message,
        string $type,
        ?string $routeName = null,
        array $routeParams = [],
        array $data = []
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

        $policy = app(\App\Services\HRMS\Notification\NotificationPolicyS::class);

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
        array $data = []
    ): void {
        try {
            $fcmService = app(\App\Services\Notification\FcmNotificationS::class);

            $payload = array_merge($this->standardPayload($type, $title, $message, $routeName, $routeParams, $data), [
                'notification_id' => (string) $notificationId,
            ]);

            // If notification is for a specific user
            if ($userId) {
                $user = DB::table('users')->where('id', $userId)->first();
                if ($user && ! empty($user->fcm_token)) {
                    $fcmService->sendPush($user->fcm_token, $title, $message, $payload);
                } else {
                    Log::warning('Notification FCM skipped: token missing', [
                        'notification_id' => $notificationId,
                        'user_id' => $userId,
                    ]);
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
        }
    }

    private function sendEmailNotification(?int $userId, string $title, string $message, array $payload): void
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
                    // Cache lock for 1 hour to prevent duplicate triggers
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
                                new \App\Mail\HrWorkflowAlertMail(
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
                return; // Prevent fall-through to individual admin emails
            }

            // 2. Custom Mailable for Holiday Work Requests
            if (in_array($type, ['holiday_work_request_submitted', 'holiday_work_request_approved', 'holiday_work_request_rejected'], true)) {
                $requestId = data_get($payload, 'request_id') ?? data_get($payloadData, 'request_id');
                if ($requestId) {
                    $holidayRequest = \App\Models\HRMS\Attendance\HolidayWorkRequestM::with(['employee.department'])->find($requestId);
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
                            (new \App\Mail\HolidayWorkRequestMail(
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

            // 3. Fallback beautifully-styled HTML email that uses our enterprise layout!
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
                new \App\Mail\HrWorkflowAlertMail(
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
