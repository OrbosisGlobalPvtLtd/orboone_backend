<?php

namespace App\Services\HRMS\Notification;

use Illuminate\Support\Facades\DB;
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
            $lines = [
                $message,
                '',
                'Notification Type: ' . ($payload['type'] ?? 'general'),
            ];

            if (! empty($payload['attachment_url'])) {
                $lines[] = 'Attachment: ' . $payload['attachment_url'];
            }

            Mail::raw(implode(PHP_EOL, $lines), function ($mail) use ($user, $title) {
                $mail->to($user->email, $user->name ?: null)->subject($title);
            });

            Log::info('Notification email sent', [
                'user_id' => $userId,
                'email' => $user->email,
                'type' => $payload['type'] ?? null,
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
        $attachmentUrl = $data['attachment_url']
            ?? $data['file_url']
            ?? $data['document_url']
            ?? $data['apk_url']
            ?? '';

        $attachmentName = $data['attachment_name']
            ?? $data['file_name']
            ?? $data['document_name']
            ?? ($attachmentUrl ? basename(parse_url((string) $attachmentUrl, PHP_URL_PATH) ?: (string) $attachmentUrl) : '');

        $attachmentType = $data['attachment_type']
            ?? $data['file_mime_type']
            ?? $data['mime_type']
            ?? $this->attachmentTypeFromName((string) $attachmentName, (string) $attachmentUrl);

        $payload = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'route_name' => $routeName ?: '',
            'route_params' => (object) $routeParams,
            'data' => (object) $data,
            'attachment_url' => (string) ($attachmentUrl ?: ''),
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
