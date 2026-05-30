<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkReportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Ensure permissions exist
        $permissions = [
            [
                'module' => 'hrms',
                'submodule' => 'attendance_work_reports',
                'action' => 'view_all',
                'key' => 'attendance.work_reports.view_all',
                'description' => 'View all employee work reports',
            ],
            [
                'module' => 'hrms',
                'submodule' => 'attendance_work_reports',
                'action' => 'view_team',
                'key' => 'attendance.work_reports.view_team',
                'description' => 'View team employee work reports',
            ],
            [
                'module' => 'hrms',
                'submodule' => 'attendance_work_reports',
                'action' => 'view_own',
                'key' => 'attendance.work_reports.view_own',
                'description' => 'View own daily work reports',
            ]
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $perm['key']],
                [
                    'module' => $perm['module'],
                    'submodule' => $perm['submodule'],
                    'action' => $perm['action'],
                    'description' => $perm['description'],
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        // Fetch permission IDs
        $allPermId = DB::table('permissions')->where('key', 'attendance.work_reports.view_all')->value('id');
        $teamPermId = DB::table('permissions')->where('key', 'attendance.work_reports.view_team')->value('id');
        $ownPermId = DB::table('permissions')->where('key', 'attendance.work_reports.view_own')->value('id');

        // 2. Fetch Role IDs dynamically by slug
        $roles = DB::table('roles')->pluck('id', 'slug')->toArray();
        $superAdminId = $roles['super_admin'] ?? 1;
        $adminId = $roles['admin'] ?? 2;
        $hrAdminId = $roles['hr_admin'] ?? 3;
        $managerId = $roles['manager'] ?? null;
        $employeeId = $roles['employee'] ?? 7;

        // 3. Assign Permissions to Roles
        // Super Admin gets all
        if ($superAdminId && $allPermId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $superAdminId, 'permission_id' => $allPermId],
                ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
            if ($teamPermId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $superAdminId, 'permission_id' => $teamPermId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
            if ($ownPermId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $superAdminId, 'permission_id' => $ownPermId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
        }

        // Admin gets view_all
        if ($adminId && $allPermId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $adminId, 'permission_id' => $allPermId],
                ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }

        // HR Admin gets view_all
        if ($hrAdminId && $allPermId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $hrAdminId, 'permission_id' => $allPermId],
                ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }

        // Manager gets view_team
        if ($managerId && $teamPermId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $managerId, 'permission_id' => $teamPermId],
                ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }

        // Employee gets view_own
        if ($employeeId && $ownPermId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $employeeId, 'permission_id' => $ownPermId],
                ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );
        }

        // 4. Ensure Menus exist under parent_id 20 (Attendance & Time Tracking)
        $menus = [
            [
                'id' => 180,
                'name' => 'Work Reports',
                'route' => 'hrms.attendance.work-reports',
                'icon' => 'fas fa-clipboard-list',
                'module_key' => 'attendance',
                'parent_id' => 20,
                'sort_order' => 9,
                'is_active' => 1,
            ],
            [
                'id' => 181,
                'name' => 'My Work Reports',
                'route' => 'hrms.attendance.my-work-reports',
                'icon' => 'fas fa-user-edit',
                'module_key' => 'my.attendance',
                'parent_id' => 20,
                'sort_order' => 3,
                'is_active' => 1,
            ]
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->updateOrInsert(
                ['id' => $menu['id']],
                [
                    'name' => $menu['name'],
                    'route' => $menu['route'],
                    'icon' => $menu['icon'],
                    'module_key' => $menu['module_key'],
                    'parent_id' => $menu['parent_id'],
                    'sort_order' => $menu['sort_order'],
                    'is_active' => $menu['is_active'],
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        // 5. Assign Menus to Roles (role_menu_access)
        $roleMenuAssignments = [
            $superAdminId => [180, 181],
            $adminId => [180],
            $hrAdminId => [180],
            $employeeId => [181],
        ];

        if ($managerId) {
            $roleMenuAssignments[$managerId] = [180];
        }

        foreach ($roleMenuAssignments as $roleId => $menuIds) {
            foreach ($menuIds as $mId) {
                DB::table('role_menu_access')->updateOrInsert(
                    ['role_id' => $roleId, 'menu_id' => $mId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}
