<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            // Dashboard
            [
                'id' => 1,
                'module' => 'core',
                'submodule' => 'dashboard',
                'action' => 'view',
                'key' => 'dashboard.view',
                'description' => 'View dashboard',
            ],

            // Employee Management
            [
                'id' => 2,
                'module' => 'hrms',
                'submodule' => 'employees',
                'action' => 'view',
                'key' => 'employees.view',
                'description' => 'View employee list',
            ],
            [
                'id' => 3,
                'module' => 'hrms',
                'submodule' => 'employees',
                'action' => 'create',
                'key' => 'employees.create',
                'description' => 'Create employee',
            ],
            [
                'id' => 4,
                'module' => 'hrms',
                'submodule' => 'employees',
                'action' => 'edit',
                'key' => 'employees.edit',
                'description' => 'Edit employee',
            ],
            [
                'id' => 5,
                'module' => 'hrms',
                'submodule' => 'employees',
                'action' => 'delete',
                'key' => 'employees.delete',
                'description' => 'Delete employee',
            ],
            [
                'id' => 6,
                'module' => 'hrms',
                'submodule' => 'employees',
                'action' => 'performance_view',
                'key' => 'employees.performance.view',
                'description' => 'View employee performance score',
            ],
            [
                'id' => 7,
                'module' => 'hrms',
                'submodule' => 'employee_directory',
                'action' => 'view',
                'key' => 'employees.directory.view',
                'description' => 'View employee directory',
            ],
            [
                'id' => 8,
                'module' => 'hrms',
                'submodule' => 'departments',
                'action' => 'manage',
                'key' => 'departments.manage',
                'description' => 'Manage departments',
            ],
            [
                'id' => 9,
                'module' => 'hrms',
                'submodule' => 'designations',
                'action' => 'manage',
                'key' => 'designations.manage',
                'description' => 'Manage designations',
            ],
            [
                'id' => 10,
                'module' => 'hrms',
                'submodule' => 'organization_hierarchy',
                'action' => 'manage',
                'key' => 'organization_hierarchy.manage',
                'description' => 'Manage organization hierarchy',
            ],
            [
                'id' => 11,
                'module' => 'hrms',
                'submodule' => 'probation',
                'action' => 'manage',
                'key' => 'probation.manage',
                'description' => 'Manage probation and confirmation',
            ],
            [
                'id' => 12,
                'module' => 'hrms',
                'submodule' => 'asset_allocations',
                'action' => 'manage',
                'key' => 'asset_allocations.manage',
                'description' => 'Manage asset allocations',
            ],

            // Self Profile
            [
                'id' => 13,
                'module' => 'hrms',
                'submodule' => 'employee_profile_self',
                'action' => 'view',
                'key' => 'employee_profile_self.view',
                'description' => 'Employee can view own profile',
            ],
            [
                'id' => 14,
                'module' => 'hrms',
                'submodule' => 'employee_profile_self',
                'action' => 'edit_limited',
                'key' => 'employee_profile_self.edit_limited',
                'description' => 'Employee can edit allowed self profile fields',
            ],

            // Attendance
            [
                'id' => 15,
                'module' => 'hrms',
                'submodule' => 'attendance',
                'action' => 'mark',
                'key' => 'attendance.mark',
                'description' => 'Mark attendance',
            ],
            [
                'id' => 16,
                'module' => 'hrms',
                'submodule' => 'attendance',
                'action' => 'view',
                'key' => 'attendance.view',
                'description' => 'View attendance module',
            ],
            [
                'id' => 17,
                'module' => 'hrms',
                'submodule' => 'attendance_records',
                'action' => 'view',
                'key' => 'attendance_records.view',
                'description' => 'View attendance records',
            ],
            [
                'id' => 18,
                'module' => 'hrms',
                'submodule' => 'attendance_rules',
                'action' => 'manage',
                'key' => 'attendance_rules.manage',
                'description' => 'Manage attendance rules',
            ],
            [
                'id' => 19,
                'module' => 'hrms',
                'submodule' => 'attendance_reports',
                'action' => 'generate',
                'key' => 'attendance.report.generate',
                'description' => 'Generate attendance reports',
            ],
            [
                'id' => 20,
                'module' => 'hrms',
                'submodule' => 'attendance_self',
                'action' => 'view',
                'key' => 'attendance_self.view',
                'description' => 'Employee can view own attendance',
            ],
            [
                'id' => 21,
                'module' => 'hrms',
                'submodule' => 'tasks',
                'action' => 'view',
                'key' => 'task.view',
                'description' => 'View task tracking',
            ],

            // Leave
            [
                'id' => 22,
                'module' => 'hrms',
                'submodule' => 'leave',
                'action' => 'allocation_manage',
                'key' => 'leave.allocation.manage',
                'description' => 'Manage leave allocation',
            ],
            [
                'id' => 23,
                'module' => 'hrms',
                'submodule' => 'leave',
                'action' => 'apply',
                'key' => 'leave.apply',
                'description' => 'Apply for leave',
            ],
            [
                'id' => 24,
                'module' => 'hrms',
                'submodule' => 'leave',
                'action' => 'approve',
                'key' => 'leave.approve',
                'description' => 'Approve leave requests',
            ],
            [
                'id' => 25,
                'module' => 'hrms',
                'submodule' => 'leave',
                'action' => 'balance_view',
                'key' => 'leave.balance.view',
                'description' => 'View leave balances',
            ],
            [
                'id' => 26,
                'module' => 'hrms',
                'submodule' => 'holiday',
                'action' => 'manage',
                'key' => 'holiday.manage',
                'description' => 'Manage holiday list',
            ],
            [
                'id' => 27,
                'module' => 'hrms',
                'submodule' => 'leave_self',
                'action' => 'apply',
                'key' => 'leave_self.apply',
                'description' => 'Employee can apply leave',
            ],
            [
                'id' => 28,
                'module' => 'hrms',
                'submodule' => 'leave_self',
                'action' => 'view_balance',
                'key' => 'leave_self.view_balance',
                'description' => 'Employee can view own leave balance',
            ],

            // Payroll
            [
                'id' => 29,
                'module' => 'hrms',
                'submodule' => 'payroll',
                'action' => 'view',
                'key' => 'payroll.view',
                'description' => 'View payroll module',
            ],
            [
                'id' => 30,
                'module' => 'hrms',
                'submodule' => 'payroll',
                'action' => 'structure_manage',
                'key' => 'payroll.structure.manage',
                'description' => 'Manage salary structure',
            ],
            [
                'id' => 31,
                'module' => 'hrms',
                'submodule' => 'payroll',
                'action' => 'dashboard_view',
                'key' => 'payroll.dashboard.view',
                'description' => 'View payroll dashboard',
            ],
            [
                'id' => 32,
                'module' => 'hrms',
                'submodule' => 'payroll',
                'action' => 'payslip_view',
                'key' => 'payroll.payslip.view',
                'description' => 'View payslips',
            ],
            [
                'id' => 33,
                'module' => 'hrms',
                'submodule' => 'payroll',
                'action' => 'fnf_manage',
                'key' => 'payroll.fnf.manage',
                'description' => 'Manage FNF',
            ],
            [
                'id' => 34,
                'module' => 'hrms',
                'submodule' => 'payroll',
                'action' => 'bonus_manage',
                'key' => 'payroll.bonus.manage',
                'description' => 'Manage bonus and incentives',
            ],
            [
                'id' => 35,
                'module' => 'hrms',
                'submodule' => 'payroll_self',
                'action' => 'view_payslip',
                'key' => 'payroll_self.view_payslip',
                'description' => 'Employee can view own payslip',
            ],

            // Documents
            [
                'id' => 36,
                'module' => 'hrms',
                'submodule' => 'documents',
                'action' => 'view',
                'key' => 'documents.view',
                'description' => 'View documents module',
            ],
            [
                'id' => 37,
                'module' => 'hrms',
                'submodule' => 'documents',
                'action' => 'compliance_manage',
                'key' => 'documents.compliance.manage',
                'description' => 'Manage compliance documents',
            ],
            [
                'id' => 38,
                'module' => 'hrms',
                'submodule' => 'documents',
                'action' => 'upload',
                'key' => 'documents.upload',
                'description' => 'Upload documents',
            ],
            [
                'id' => 39,
                'module' => 'hrms',
                'submodule' => 'documents',
                'action' => 'company_view',
                'key' => 'documents.company.view',
                'description' => 'View company documents',
            ],
            [
                'id' => 40,
                'module' => 'hrms',
                'submodule' => 'documents_self',
                'action' => 'upload',
                'key' => 'documents_self.upload',
                'description' => 'Employee can upload own documents',
            ],
            [
                'id' => 41,
                'module' => 'hrms',
                'submodule' => 'documents_self',
                'action' => 'view',
                'key' => 'documents_self.view',
                'description' => 'Employee can view own documents',
            ],

            // Announcements
            [
                'id' => 42,
                'module' => 'hrms',
                'submodule' => 'announcements',
                'action' => 'view',
                'key' => 'announcements.view',
                'description' => 'View announcements',
            ],
            [
                'id' => 43,
                'module' => 'hrms',
                'submodule' => 'announcements',
                'action' => 'create',
                'key' => 'announcements.create',
                'description' => 'Create announcements',
            ],
            [
                'id' => 44,
                'module' => 'hrms',
                'submodule' => 'announcements',
                'action' => 'manage',
                'key' => 'announcements.manage',
                'description' => 'Manage announcements',
            ],
            [
                'id' => 45,
                'module' => 'hrms',
                'submodule' => 'announcements_self',
                'action' => 'view',
                'key' => 'announcements_self.view',
                'description' => 'Employee can view announcements',
            ],

            // Access Control
            [
                'id' => 46,
                'module' => 'access_control',
                'submodule' => 'roles',
                'action' => 'manage',
                'key' => 'roles.manage',
                'description' => 'Manage roles',
            ],
            [
                'id' => 47,
                'module' => 'access_control',
                'submodule' => 'permissions',
                'action' => 'manage',
                'key' => 'permissions.manage',
                'description' => 'Manage permissions',
            ],
            [
                'id' => 48,
                'module' => 'access_control',
                'submodule' => 'admins',
                'action' => 'manage',
                'key' => 'admins.manage',
                'description' => 'Manage admin users',
            ],
            [
                'id' => 49,
                'module' => 'access_control',
                'submodule' => 'module_access',
                'action' => 'manage',
                'key' => 'module_access.manage',
                'description' => 'Manage module access mapping',
            ],

            // Future Modules
            [
                'id' => 50,
                'module' => 'crm',
                'submodule' => 'crm',
                'action' => 'view',
                'key' => 'crm.view',
                'description' => 'View CRM module',
            ],
            [
                'id' => 51,
                'module' => 'project_management',
                'submodule' => 'project_management',
                'action' => 'view',
                'key' => 'project_management.view',
                'description' => 'View Project Management module',
            ],
            [
                'id' => 52,
                'module' => 'finance',
                'submodule' => 'finance',
                'action' => 'view',
                'key' => 'finance.view',
                'description' => 'View Finance module',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['id' => $permission['id']],
                [
                    'module' => $permission['module'],
                    'submodule' => $permission['submodule'],
                    'action' => $permission['action'],
                    'key' => $permission['key'],
                    'description' => $permission['description'],
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}