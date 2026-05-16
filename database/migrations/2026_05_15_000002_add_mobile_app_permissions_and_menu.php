<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['settings', 'mobile_app_versions', 'view', 'mobile_app_versions.view', 'View mobile app versions'],
            ['settings', 'mobile_app_versions', 'manage', 'mobile_app_versions.manage', 'Manage mobile app versions'],
            ['settings', 'mobile_app_versions', 'upload', 'mobile_app_versions.upload', 'Upload mobile APK releases'],
            ['settings', 'mobile_app_versions', 'delete', 'mobile_app_versions.delete', 'Delete mobile APK releases'],
        ];

        foreach ($permissions as [$module, $submodule, $action, $key, $description]) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                [
                    'module' => $module,
                    'submodule' => $submodule,
                    'action' => $action,
                    'description' => $description,
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('key', array_column($permissions, 3))
            ->pluck('id')
            ->all();

        $adminRoleIds = DB::table('roles')
            ->whereIn('slug', ['super_admin', 'hr_admin'])
            ->pluck('id')
            ->all();

        foreach ($adminRoleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
        }

        DB::table('menus')->updateOrInsert(
            ['route' => 'hrms.mobile-app-versions.index'],
            [
                'name' => 'Mobile App Updates',
                'icon' => 'fas fa-mobile-alt',
                'module_key' => 'settings',
                'parent_id' => 80,
                'sort_order' => 7,
                'is_active' => 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        $menuId = DB::table('menus')->where('route', 'hrms.mobile-app-versions.index')->value('id');

        foreach ($adminRoleIds as $roleId) {
            DB::table('role_menu_access')->updateOrInsert(
                ['role_id' => $roleId, 'menu_id' => $menuId],
                ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }

        Cache::flush();
    }

    public function down(): void
    {
        $keys = [
            'mobile_app_versions.view',
            'mobile_app_versions.manage',
            'mobile_app_versions.upload',
            'mobile_app_versions.delete',
        ];

        $permissionIds = DB::table('permissions')->whereIn('key', $keys)->pluck('id')->all();

        DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();

        $menuId = DB::table('menus')->where('route', 'hrms.mobile-app-versions.index')->value('id');
        if ($menuId) {
            DB::table('role_menu_access')->where('menu_id', $menuId)->delete();
            DB::table('menus')->where('id', $menuId)->delete();
        }

        Cache::flush();
    }
};
