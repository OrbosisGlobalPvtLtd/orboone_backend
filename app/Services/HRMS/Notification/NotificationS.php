<?php

namespace App\Services\HRMS\Notification;

use Illuminate\Support\Facades\DB;
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

            if (
                $employeeId
                && $targetDate
                && $this->unresolvedReminderExists($type, (int) $employeeId, (string) $targetDate, (int) $user->id)
            ) {
                continue;
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
    ): void {
        if (! Schema::hasTable($this->notificationsTable)) {
            return;
        }

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
            $insert['data'] = json_encode($data);
        }

        $notificationId = DB::table($this->notificationsTable)->insertGetId($insert);

        // Task: Send FCM Push
        $this->sendFcmPush(
            $notificationId,
            $userId,
            $roleId,
            $title,
            $message,
            $type,
            $routeName,
            $routeParams,
            $data
        );
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

            $payload = array_merge($data, [
                'notification_id' => (string) $notificationId,
                'type' => (string) $type,
                'route_name' => (string) $routeName,
                'route_params' => json_encode($routeParams),
            ]);

            // If notification is for a specific user
            if ($userId) {
                $user = DB::table('users')->where('id', $userId)->first();
                if ($user && ! empty($user->fcm_token)) {
                    $fcmService->sendPush($user->fcm_token, $title, $message, $payload);
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Push Hook Error: ' . $e->getMessage());
        }
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
        $query = DB::table('users')
            ->leftJoin('roles', 'roles.id', '=', 'users.system_role_id')
            ->select('users.id', 'users.system_role_id');

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('users.is_active', 1);
        }

        $query->where(function ($q) {
            if (Schema::hasColumn('roles', 'name')) {
                $q->orWhereIn('roles.name', [
                    'super_admin',
                    'Super Admin',
                    'hr_admin',
                    'HR Admin',
                    'admin',
                    'Admin',
                ]);
            }

            if (Schema::hasColumn('roles', 'title')) {
                $q->orWhereIn('roles.title', [
                    'super_admin',
                    'Super Admin',
                    'hr_admin',
                    'HR Admin',
                    'admin',
                    'Admin',
                ]);
            }
        });

        return $query->get();
    }
}
