<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Create Permission
        $permissionId = DB::table('permissions')->insertGetId([
            'module' => 'Settings',
            'submodule' => 'Notification Retention',
            'action' => 'Manage',
            'key' => 'settings.notification_retention.manage',
            'description' => 'Can manage notification retention settings',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Assign to Super Admin (Role ID 1) if role exists
        if (DB::table('roles')->where('id', 1)->exists()) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => 1, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3. Add Menu Item under Settings (Parent ID 80) if route column exists
        if (Schema::hasColumn('menus', 'route')) {
            DB::table('menus')->updateOrInsert(
                ['route' => 'settings.notification-retention.index'],
                [
                    'name' => 'Notification Retention',
                    'icon' => 'fas fa-history',
                    'module_key' => 'settings',
                    'parent_id' => 80,
                    'sort_order' => 10,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $menuId = DB::table('menus')->where('route', 'settings.notification-retention.index')->value('id');

            // 4. Map Menu to Role (role_menu_access) if table and role exist
            if ($menuId && Schema::hasTable('role_menu_access') && DB::table('roles')->where('id', 1)->exists()) {
                DB::table('role_menu_access')->updateOrInsert(
                    ['role_id' => 1, 'menu_id' => $menuId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down()
    {
        $permission = DB::table('permissions')->where('key', 'settings.notification_retention.manage')->first();
        if ($permission) {
            DB::table('role_permissions')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }

        DB::table('menus')->where('route', 'settings.notification-retention.index')->delete();
    }
};
