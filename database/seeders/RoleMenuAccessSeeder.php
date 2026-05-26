<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleMenuAccessSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Role IDs
        |--------------------------------------------------------------------------
        | 1 = Super Admin
        | 2 = Admin / HR Admin
        | 3 = HR / Manager
        | 7 = Employee
        */

        $adminRoles = [1, 2, 3];

        // Admin / HR full access
        $adminMenuIds = [
            1,
            10,11,12,13,15,16,17,18,
            20,21,22,23,24,
            30,31,32,33,34,35,130,131,132,133,
            40,41,42,43,44,45,
            50,51,52,53,
            60,
            70,71,72,73,74,75,76,
            80,81,82,
            90,91,92,
        ];

        // Employee limited access
        $employeeRoleId = 7;

        $employeeMenuIds = [
            1,      // Dashboard

            20,     // Attendance & Tracking
            21,     // Attendance Marking
            22,     // Task Tracking

            30,     // Leave Management
            32,     // Apply for Leave
            34,     // Balance Tracker
            132,    // Holiday List

            40,     // Payroll Management
            43,     // My Salary Slips

            50,     // Document Management
            52,     // Upload Documents
            53,     // Company Documents

            154,    // My Announcements
        ];

        foreach ($adminRoles as $roleId) {
            foreach ($adminMenuIds as $menuId) {
                DB::table('role_menu_access')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'menu_id' => $menuId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        foreach ($employeeMenuIds as $menuId) {
            DB::table('role_menu_access')->updateOrInsert(
                [
                    'role_id' => $employeeRoleId,
                    'menu_id' => $menuId,
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $allMenuIds = DB::table('menus')->where('is_active', 1)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $roleIdsBySlug = DB::table('roles')->pluck('id', 'slug')->toArray();

        $roleMenus = [
            'super_admin' => $allMenuIds,
            'admin' => [
                1,
                10,11,
                20,21,26,134,
                30,31,33,
                50,
                60,
            ],
            'hr_admin' => [
                1,
                10,11,12,13,15,16,17,18,
                20,21,22,145,23,28,29,26,134,135,24,25,136,152,153,27,
                30,31,32,137,33,146,34,35,130,131,132,138,133,139,140,
                50,51,52,53,148,149,160,
                60,
                80,83,143,144,150,151,
            ],
            'manager' => [
                1,
                10,11,
                20,21,26,134,
                30,33,146,
                50,
                60,
            ],
            'finance_admin' => [
                1,
                300,301,302,303,304,305,306,308,
            ],
            'project_admin' => [
                1,
                320,321,322,323,
                10,11,
                20,26,134,
            ],
            'operations_admin' => [
                1,
                20,21,23,26,134,
                30,132,138,
                330,
                10,11,
                60,
            ],
            'custom_admin' => [
                1,
            ],
            'employee' => [
                1,
                20,145,28,26,
                30,32,137,34,133,
                40,43,
                50,52,53,161,
                154,
                80,83,
                309,310,
            ],
        ];

        foreach (array_keys($roleMenus) as $slug) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if ($roleId) {
                DB::table('role_menu_access')->where('role_id', $roleId)->delete();
            }
        }

        foreach ($roleMenus as $slug => $menuIds) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach (array_unique($menuIds) as $menuId) {
                DB::table('role_menu_access')->updateOrInsert(
                    ['role_id' => $roleId, 'menu_id' => $menuId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
        }

        DB::table('permissions')->updateOrInsert(
            ['key' => 'asset_allocation.manage'],
            [
                'module' => 'hrms',
                'submodule' => 'asset_allocations',
                'action' => 'manage',
                'description' => 'Manage asset allocations',
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        $permissionKeysByRole = [
            'admin' => [
                'dashboard.view',
                'employees.view',
                'attendance.dashboard.view',
                'attendance.records.view_all',
                'attendance.monthly_report.view_all',
                'attendance.monthly_summary.view',
                'leave.dashboard.view',
                'leave.approvals.view',
                'leave.approvals.view_all',
                'documents.compliance.view',
                'documents.verification.view',
                'employee_documents.view',
                'announcements.view',
            ],
            'finance_admin' => [
                'dashboard.view',
                'enterprise_payroll.dashboard.view',
                'enterprise_salary_structure.view',
                'enterprise_salary_structure.manage',
                'enterprise_payroll_run.view',
                'enterprise_payroll_run.generate',
                'enterprise_payroll_run.approve',
                'enterprise_payroll_run.lock',
                'enterprise_payslip.view',
                'enterprise_payslip.generate',
                'enterprise_payslip.download',
                'enterprise_bonus_incentive.view',
                'enterprise_bonus_incentive.manage',
                'enterprise_reimbursement.view',
                'enterprise_reimbursement.manage',
                'enterprise_payroll_reports.view',
            ],
            'project_admin' => [
                'dashboard.view',
                'project_management.view',
                'employees.view',
                'attendance.records.view_all',
                'attendance.monthly_report.view_all',
                'attendance.monthly_summary.view',
            ],
            'operations_admin' => [
                'dashboard.view',
                'employees.view',
                'attendance.dashboard.view',
                'attendance.records.view_all',
                'attendance.blocked.view',
                'attendance.monthly_report.view_all',
                'attendance.monthly_summary.view',
                'leave.holidays.manage',
                'leave.weekoff_rules.manage',
                'asset_allocation.manage',
                'announcements.view',
            ],
            'custom_admin' => [
                'dashboard.view',
            ],
            'manager' => [
                'dashboard.view',
                'employees.view',
                'attendance.dashboard.view',
                'attendance.monthly_report.view_team',
                'attendance.monthly_summary.view',
                'leave.approvals.view',
                'leave.approvals.view_team',
                'leave.team_calendar.view',
                'documents.verification.view',
                'employee_documents.view',
                'announcements.view',
            ],
        ];

        $permissionIds = DB::table('permissions')->pluck('id', 'key')->toArray();

        foreach ($permissionKeysByRole as $slug => $permissionKeys) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach ($permissionKeys as $key) {
                $permissionId = $permissionIds[$key] ?? null;
                if (! $permissionId) {
                    continue;
                }

                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
        }
    }
}
