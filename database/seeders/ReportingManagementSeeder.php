<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportingManagementSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Seed Menus for Reporting Management (Admin Configuration) and Team Management (Operational Workspace)
        if (Schema::hasTable('menus')) {
            $allMenus = [
                // Reporting Management (Admin Configuration - Parent 350)
                ['id' => 350, 'name' => 'Reporting Management', 'route' => '', 'icon' => 'fas fa-sitemap', 'module_key' => 'reporting', 'parent_id' => null, 'sort_order' => 68, 'is_active' => 1],
                ['id' => 352, 'name' => 'Reporting Structure', 'route' => 'reporting.structure', 'icon' => 'fas fa-sitemap', 'module_key' => 'reporting.structure', 'parent_id' => 350, 'sort_order' => 1, 'is_active' => 1],
                ['id' => 353, 'name' => 'Reporting Managers', 'route' => 'reporting.supervisors', 'icon' => 'fas fa-user-shield', 'module_key' => 'reporting.supervisors', 'parent_id' => 350, 'sort_order' => 2, 'is_active' => 1],
                ['id' => 354, 'name' => 'Employee Assignments', 'route' => 'reporting.assignments', 'icon' => 'fas fa-users-cog', 'module_key' => 'reporting.assignments', 'parent_id' => 350, 'sort_order' => 3, 'is_active' => 1],
                ['id' => 360, 'name' => 'Reporting History', 'route' => 'reporting.history', 'icon' => 'fas fa-history', 'module_key' => 'reporting.history', 'parent_id' => 350, 'sort_order' => 4, 'is_active' => 1],

                // Team Management (Operational Workspace - Parent 370)
                ['id' => 370, 'name' => 'Team Management', 'route' => '', 'icon' => 'fas fa-users-cog', 'module_key' => 'team', 'parent_id' => null, 'sort_order' => 67, 'is_active' => 1],
                ['id' => 371, 'name' => 'Dashboard', 'route' => 'reporting.dashboard', 'icon' => 'fas fa-tachometer-alt', 'module_key' => 'team.dashboard', 'parent_id' => 370, 'sort_order' => 1, 'is_active' => 1],
                ['id' => 372, 'name' => 'My Team', 'route' => 'reporting.my_employees', 'icon' => 'fas fa-users', 'module_key' => 'team.my_team', 'parent_id' => 370, 'sort_order' => 2, 'is_active' => 1],
                ['id' => 373, 'name' => 'Attendance', 'route' => 'reporting.attendance', 'icon' => 'fas fa-calendar-check', 'module_key' => 'team.attendance', 'parent_id' => 370, 'sort_order' => 3, 'is_active' => 1],
                ['id' => 374, 'name' => 'Leave', 'route' => 'reporting.leave', 'icon' => 'fas fa-plane-departure', 'module_key' => 'team.leave', 'parent_id' => 370, 'sort_order' => 4, 'is_active' => 1],
                ['id' => 375, 'name' => 'Daily Work Reports', 'route' => 'reporting.work_reports', 'icon' => 'fas fa-file-signature', 'module_key' => 'team.work_reports', 'parent_id' => 370, 'sort_order' => 5, 'is_active' => 1],
                ['id' => 376, 'name' => 'Projects & Tasks', 'route' => 'reporting.projects', 'icon' => 'fas fa-project-diagram', 'module_key' => 'team.projects', 'parent_id' => 370, 'sort_order' => 6, 'is_active' => 1],
                ['id' => 377, 'name' => 'Team History', 'route' => 'reporting.history', 'icon' => 'fas fa-history', 'module_key' => 'team.history', 'parent_id' => 370, 'sort_order' => 7, 'is_active' => 1],
            ];

            // Deactivate legacy operational items under 350 if they exist
            DB::table('menus')->whereIn('id', [351, 355, 356, 357, 358, 359])->update(['is_active' => 0]);

            foreach ($allMenus as $m) {
                DB::table('menus')->updateOrInsert(
                    ['id' => $m['id']],
                    array_merge($m, ['updated_at' => $now, 'created_at' => $now])
                );
            }
        }

        // 2. Seed Permissions
        if (Schema::hasTable('permissions')) {
            $permissions = [
                ['key' => 'reporting.view', 'module' => 'hrms', 'submodule' => 'reporting', 'action' => 'view', 'description' => 'View Reporting Management'],
                ['key' => 'reporting.structure.view', 'module' => 'hrms', 'submodule' => 'reporting', 'action' => 'view', 'description' => 'View Reporting Structure'],
                ['key' => 'reporting.structure.manage', 'module' => 'hrms', 'submodule' => 'reporting', 'action' => 'manage', 'description' => 'Manage Reporting Structure'],
                ['key' => 'reporting.supervisor.assign', 'module' => 'hrms', 'submodule' => 'reporting', 'action' => 'assign', 'description' => 'Assign Reporting Managers'],
                ['key' => 'reporting.employee.assign', 'module' => 'hrms', 'submodule' => 'reporting', 'action' => 'assign', 'description' => 'Assign Employees to Manager'],
                ['key' => 'reporting.history.view', 'module' => 'hrms', 'submodule' => 'reporting', 'action' => 'view', 'description' => 'View Reporting History'],

                // Team Management Permissions
                ['key' => 'team.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View Team Management'],
                ['key' => 'team.dashboard.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View Team Dashboard'],
                ['key' => 'team.employee.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View My Team Employees'],
                ['key' => 'team.attendance.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View Team Attendance'],
                ['key' => 'team.leave.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View Team Leave'],
                ['key' => 'team.work_report.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View Team Daily Work Reports'],
                ['key' => 'team.project.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View Team Projects & Tasks'],
                ['key' => 'team.history.view', 'module' => 'hrms', 'submodule' => 'team', 'action' => 'view', 'description' => 'View Team History'],
            ];

            foreach ($permissions as $p) {
                DB::table('permissions')->updateOrInsert(
                    ['key' => $p['key']],
                    array_merge($p, ['updated_at' => $now, 'created_at' => $now])
                );
            }
        }

        // 3. Seed Role Menu Access
        if (Schema::hasTable('role_menu_access')) {
            $roles = DB::table('roles')->pluck('id')->toArray();
            if (empty($roles)) {
                $roles = [1, 2, 3, 7];
            }

            $menuIds = [350, 352, 353, 354, 360, 370, 371, 372, 373, 374, 375, 376, 377];

            foreach ($roles as $roleId) {
                foreach ($menuIds as $menuId) {
                    DB::table('role_menu_access')->updateOrInsert(
                        ['role_id' => $roleId, 'menu_id' => $menuId],
                        ['updated_at' => $now, 'created_at' => $now]
                    );
                }
            }
        }
    }
}
