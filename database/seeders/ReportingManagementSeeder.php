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

        // 1. Seed Permissions
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

        // 2. Seed Role Menu Access
        if (Schema::hasTable('role_menu_access')) {
            $roles = DB::table('roles')->pluck('id')->toArray();
            if (empty($roles)) {
                $roles = [1, 2, 3, 7];
            }

            // Keep 323 and 375 separate until their product/access decision is made; 377 is retired in favor of 360.
            $menuIds = [350, 352, 353, 354, 360, 370, 371, 372, 373, 374, 375, 376];

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
