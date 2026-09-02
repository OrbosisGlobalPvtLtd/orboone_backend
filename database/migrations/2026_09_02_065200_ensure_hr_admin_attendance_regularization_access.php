<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure the Regularization Requests menu and all regularization permissions
     * are properly configured and accessible for HR Admin and other roles.
     */
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')
                ->where('route', 'hrms.attendance.regularizations.index')
                ->orWhere('id', 28)
                ->update([
                    'permission_key' => 'attendance.regularization.view_all|attendance.regularization.view_team|attendance.regularization.view_own',
                    'is_active' => 1,
                ]);
        }

        if (Schema::hasTable('roles') && Schema::hasTable('role_menu_access')) {
            $hrRole = DB::table('roles')->where('slug', 'hr_admin')->first(['id']);
            if ($hrRole) {
                $roleId = (int) $hrRole->id;
                $now = now();

                DB::table('role_menu_access')->updateOrInsert(
                    ['role_id' => $roleId, 'menu_id' => 28],
                    ['created_at' => $now, 'updated_at' => $now]
                );

                if (Schema::hasTable('permissions') && Schema::hasTable('role_permissions')) {
                    $permKeys = [
                        'attendance.regularization.view',
                        'attendance.regularization.view_all',
                        'attendance.regularization.view_team',
                        'attendance.regularization.view_own',
                        'attendance.regularization.create',
                        'attendance.regularization.approve',
                        'attendance.regularization.reject',
                    ];

                    $permIds = DB::table('permissions')
                        ->whereIn('key', $permKeys)
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    foreach ($permIds as $permId) {
                        DB::table('role_permissions')->updateOrInsert(
                            ['role_id' => $roleId, 'permission_id' => $permId],
                            ['created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }

                if (class_exists(\App\Services\AccessControl\PermissionSyncService::class)) {
                    app(\App\Services\AccessControl\PermissionSyncService::class)->clearRoleAndUserCaches($roleId);
                }
            }
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // No-op
    }
};
