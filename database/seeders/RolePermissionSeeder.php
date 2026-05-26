<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | Role IDs
        |--------------------------------------------------------------------------
        | 1 = super_admin
        | 2 = admin
        | 3 = hr_admin
        | 4 = finance_admin
        | 5 = project_admin
        | 6 = operations_admin
        | 7 = employee
        | 8 = custom_admin
        */

        /*
        |--------------------------------------------------------------------------
        | Permission IDs from PermissionSeeder
        |--------------------------------------------------------------------------
        */

        $mapping = [

            // ============================================================
            // SUPER ADMIN -> ALL PERMISSIONS
            // ============================================================
            1 => array_merge(range(1, 52), range(100, 112)),

            // ============================================================
            // ADMIN -> Broad HRMS admin access
            // ============================================================
            2 => [
                // dashboard
                1,

                // employee management
                2, 3, 4, 5, 6, 7, 8, 12, 13, 10, 11, 12,

                // self profile (optional if admin is also employee)
                13, 14,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,
                100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112,

                // payroll
                29, 30, 31, 32, 33, 34, 35,

                // documents
                36, 37, 38, 39, 40, 41,

                // announcements
                42, 43, 44, 45,
            ],

            // ============================================================
            // HR ADMIN -> HR focused access
            // ============================================================
            3 => [
                // dashboard
                1,

                // employee management
                2, 3, 4, 6, 7, 8, 9, 10, 11, 12,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,
                100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112,

                // payroll (limited)
                29, 31, 32, 35,

                // documents
                36, 37, 38, 39, 40, 41,

                // announcements
                42, 43, 44, 45,
            ],

            // ============================================================
            // FINANCE ADMIN -> payroll heavy, limited HR
            // ============================================================
            4 => [
                // dashboard
                1,

                // payroll
                29, 30, 31, 32, 33, 34, 35,

                // limited employee/payroll reference
                2, 7,

                // announcements view
                42, 45,
            ],

            // ============================================================
            // PROJECT ADMIN -> future module base
            // ============================================================
            5 => [
                1,     // dashboard
                51,    // project_management.view
                42, 45 // announcements
            ],

            // ============================================================
            // OPERATIONS ADMIN -> attendance + leave heavy
            // ============================================================
            6 => [
                1,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,
                100, 101, 102, 103, 104, 105, 106, 107, 108, 111, 112,

                // employee directory/basic
                2, 7, 11,

                // announcements
                42, 45,
            ],

            // ============================================================
            // EMPLOYEE -> self-service only
            // ============================================================
            7 => [
                1,      // dashboard.view

                // self profile
                13, 14,

                // directory
                7,

                // attendance self
                15, 20,

                // leave self
                23, 28,
                101, 102, 103, 107,

                // payroll self
                35,

                // documents self
                40, 41,

                // announcements self
                45,
            ],

            // ============================================================
            // CUSTOM ADMIN -> start with HR-admin like base
            // later super admin can customize through UI
            // ============================================================
            8 => [
                1,

                // employee
                2, 3, 4, 6, 7, 8, 9, 10, 11, 12,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,
                100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112,

                // payroll limited
                29, 31, 32, 35,

                // documents
                36, 37, 38, 39, 40, 41,

                // announcements
                42, 43, 44, 45,
            ],
        ];

        $rows = [];
        $id = 1;

        foreach ($mapping as $roleId => $permissionIds) {
            foreach (array_unique($permissionIds) as $permissionId) {
                $rows[] = [
                    'id' => $id++,
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach ($rows as $row) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role_id' => $row['role_id'],
                    'permission_id' => $row['permission_id'],
                ],
                [
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        $allPermissionIds = DB::table('permissions')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $permissionIdsByKey = DB::table('permissions')->pluck('id', 'key')->toArray();
        $roleIdsBySlug = DB::table('roles')->pluck('id', 'slug')->toArray();

        $rolePermissionKeys = [
            'super_admin' => array_keys($permissionIdsByKey),
            'admin' => [
                'dashboard.view',
                'employees.view','employees.create','employees.edit','employees.delete','employees.pending_profiles.view','employees.pending_profiles.approve','employees.probation_internship.view','employees.probation_internship.manage','employees.exit.view','employees.exit.manage','employees.organization.manage','employees.reporting_structure.manage',
                'attendance.dashboard.view','attendance.records.view_all','attendance.monthly_report.view_all','attendance.monthly_summary.view','attendance.export',
                'leave.dashboard.view','leave.approvals.view_all','leave.approvals.approve','leave.approvals.reject','leave.balance.view_all','leave.allocation.view_all','leave.allocation.manage','leave.balance_logs.view',
                'payroll.dashboard.view','payroll.salary_structure.view','payroll.salary_structure.manage','payroll.attendance_impacts.view','payroll.generate.view','payroll.generate.process','payroll.approve','payroll.payslips.view_all','payroll.claims.view_all','payroll.claims.manage','payroll.adjustments.manage','payroll.fnf.view','payroll.fnf.manage','payroll.bonus.view','payroll.bonus.manage','payroll.monthly_summary.view',
                'documents.upload.self','documents.company.view','documents.types.manage',
                'announcements.view','announcements.create','announcements.edit','announcements.delete','announcements.publish','announcements.print',
                'settings.profile.view','settings.profile.update','settings.policy_change_logs.view','settings.employee_policy_assignments.view','settings.employee_policy_assignments.manage','settings.notification_retention.manage',
                'mobile_app_versions.view','mobile_app_versions.manage','mobile_app_versions.upload','mobile_app_versions.delete',
                'document_generation.view','document_generation.template_create','document_generation.template_edit','document_generation.generate','document_generation.preview','document_generation.download','document_generation.email','document_generation.review','document_generation.delete',
            ],
            'hr_admin' => [
                'dashboard.view',
                'employees.view','employees.create','employees.edit','employees.delete','employees.pending_profiles.view','employees.pending_profiles.approve','employees.probation_internship.view','employees.probation_internship.manage','employees.exit.view','employees.exit.manage','employees.organization.manage','employees.reporting_structure.manage',
                'attendance.dashboard.view','attendance.records.view_all','attendance.my.view','attendance.blocked.view','attendance.blocked.unlock','attendance.regularization.view_own','attendance.regularization.view_team','attendance.regularization.view_all','attendance.regularization.create','attendance.regularization.approve','attendance.regularization.reject','attendance.holiday_work.view','attendance.holiday_work.manage','attendance.holiday_work.approve','attendance.holiday_work.reject','attendance.monthly_report.view_own','attendance.monthly_report.view_team','attendance.monthly_report.view_all','attendance.monthly_summary.view','attendance.violations.view','attendance.rules.manage','attendance.types.manage','attendance.policy_overrides.manage','attendance.weekoff_rules.manage','attendance.holidays.manage','attendance.export',
                'leave.dashboard.view','leave.my_requests.view','leave.my_requests.create','leave.my_requests.cancel','leave.approvals.view_team','leave.approvals.view_all','leave.approvals.approve','leave.approvals.reject','leave.team_calendar.view','leave.balance.view_own','leave.balance.view_team','leave.balance.view_all','leave.allocation.view_own','leave.allocation.view_all','leave.allocation.manage','leave.types.manage','leave.policies.manage','leave.holidays.manage','leave.weekoff_rules.manage','leave.comp_off.view_own','leave.comp_off.view_all','leave.comp_off.manage','leave.policy_overrides.manage','leave.balance_logs.view',
                'documents.compliance.view','documents.upload.self','documents.company.view','documents.types.manage','documents.verification.view','documents.verification.approve','documents.verification.reject',
                'announcements.view','announcements.create','announcements.edit','announcements.delete','announcements.publish','announcements.print',
                'settings.profile.view','settings.profile.update','settings.policy_change_logs.view','settings.employee_policy_assignments.view','settings.employee_policy_assignments.manage','settings.notification_retention.manage',
                'mobile_app_versions.view','mobile_app_versions.manage','mobile_app_versions.upload','mobile_app_versions.delete',
                'employee_documents.view','company_documents.manage','documents_self.view','documents_self.upload','employee.view',
                'document_generation.view','document_generation.template_create','document_generation.template_edit','document_generation.generate','document_generation.preview','document_generation.download','document_generation.email','document_generation.review','document_generation.delete',
            ],
            'manager' => [
                'dashboard.view','employees.view','attendance.dashboard.view','attendance.my.view','attendance.regularization.view_own','attendance.regularization.view_team','attendance.regularization.create','attendance.regularization.approve','attendance.regularization.reject','attendance.monthly_report.view_own','attendance.monthly_report.view_team',
                'leave.dashboard.view','leave.my_requests.view','leave.my_requests.create','leave.my_requests.cancel','leave.approvals.view_team','leave.approvals.approve','leave.approvals.reject','leave.team_calendar.view','leave.balance.view_own','leave.balance.view_team','leave.comp_off.view_own',
                'documents.upload.self','documents.company.view','employee.announcements.view','employee.announcements.detail','settings.profile.view','settings.profile.update','documents_self.view',
            ],
            'finance_admin' => [
                'dashboard.view','employees.view','attendance.monthly_report.view_all','attendance.monthly_summary.view','attendance.export','leave.balance.view_all','leave.balance_logs.view',
                'payroll.dashboard.view','payroll.salary_structure.view','payroll.salary_structure.manage','payroll.attendance_impacts.view','payroll.generate.view','payroll.generate.process','payroll.approve','payroll.payslips.view_all','payroll.claims.view_all','payroll.claims.manage','payroll.adjustments.manage','payroll.fnf.view','payroll.fnf.manage','payroll.bonus.view','payroll.bonus.manage','payroll.monthly_summary.view',
                'documents.company.view','employee.announcements.view','employee.announcements.detail','settings.profile.view','settings.profile.update',
            ],
            'employee' => [
                'dashboard.view','attendance.my.view','attendance.regularization.view_own','attendance.regularization.create','attendance.monthly_report.view_own','leave.my_requests.view','leave.my_requests.create','leave.my_requests.cancel','leave.balance.view_own','leave.comp_off.view_own','payroll.payslips.view_own','documents.upload.self','documents.company.view','employee.announcements.view','employee.announcements.detail','settings.profile.view','settings.profile.update','documents_self.view','documents_self.upload',
                'enterprise_payroll.my_payslips.view','enterprise_payroll.my_reimbursements.view','enterprise_payroll.my_reimbursements.create',
            ],
        ];

        foreach (array_keys($rolePermissionKeys) as $slug) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if ($roleId) {
                DB::table('role_permissions')->where('role_id', $roleId)->delete();
            }
        }

        foreach ($rolePermissionKeys as $slug => $keys) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if (! $roleId) {
                continue;
            }

            $permissionIds = $slug === 'super_admin'
                ? $allPermissionIds
                : collect($keys)->map(fn ($key) => $permissionIdsByKey[$key] ?? null)->filter()->unique()->values()->all();

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
        }
    }
}
