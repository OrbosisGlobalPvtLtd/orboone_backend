<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure the HR Admin role has full Employee Management menu visibility
     * and the backing permissions, then flush relevant caches.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('menus')) {
            return;
        }

        $role = DB::table('roles')->where('slug', 'hr_admin')->first(['id']);
        if (! $role) {
            return;
        }

        $roleId = (int) $role->id;
        $now = now();

        // Employee Management parent + child menu IDs
        $menuIds = [10, 11, 12, 13, 15, 16, 17, 18, 19];

        $validMenuIds = DB::table('menus')
            ->whereIn('id', $menuIds)
            ->where('is_active', 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($validMenuIds as $menuId) {
            DB::table('role_menu_access')->updateOrInsert(
                ['role_id' => $roleId, 'menu_id' => $menuId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        // Backing permissions for the Employee Management cards/actions
        $permissionKeys = [
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'employees.update',
            'employees.directory.view',
            'employees.performance.view',
            'employees.pending_profiles.view',
            'employees.pending_profiles.approve',
            'employees.probation_internship.view',
            'employees.probation_internship.manage',
            'employees.exit.view',
            'employees.exit.manage',
            'employees.organization.manage',
            'employees.reporting_structure.manage',
            'employee.shift.assign.manage',
            'departments.manage',
            'designations.manage',
            'organization_hierarchy.manage',
            'probation.manage',
        ];

        if (Schema::hasTable('permissions') && Schema::hasTable('role_permissions')) {
            $permissionIds = DB::table('permissions')
                ->whereIn('key', $permissionKeys)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        // Flush caches for users holding this role
        if (class_exists(\App\Services\AccessControl\PermissionSyncService::class)) {
            app(\App\Services\AccessControl\PermissionSyncService::class)->clearRoleAndUserCaches($roleId);
        }
    }

    /**
     * Reverse the migration by removing the Employee Management menu and
     * permission grants added above.
     */
    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $role = DB::table('roles')->where('slug', 'hr_admin')->first(['id']);
        if (! $role) {
            return;
        }

        $roleId = (int) $role->id;
        $menuIds = [10, 11, 12, 13, 15, 16, 17, 18, 19];

        if (Schema::hasTable('role_menu_access')) {
            DB::table('role_menu_access')
                ->where('role_id', $roleId)
                ->whereIn('menu_id', $menuIds)
                ->delete();
        }

        $permissionKeys = [
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'employees.update',
            'employees.directory.view',
            'employees.performance.view',
            'employees.pending_profiles.view',
            'employees.pending_profiles.approve',
            'employees.probation_internship.view',
            'employees.probation_internship.manage',
            'employees.exit.view',
            'employees.exit.manage',
            'employees.organization.manage',
            'employees.reporting_structure.manage',
            'employee.shift.assign.manage',
            'departments.manage',
            'designations.manage',
            'organization_hierarchy.manage',
            'probation.manage',
        ];

        if (Schema::hasTable('permissions') && Schema::hasTable('role_permissions')) {
            $permissionIds = DB::table('permissions')
                ->whereIn('key', $permissionKeys)
                ->pluck('id')
                ->all();

            DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->whereIn('permission_id', $permissionIds)
                ->delete();
        }
    }
};
