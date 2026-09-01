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

        $allPermissionIds = DB::table('permissions')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $permissionIdsByKey = DB::table('permissions')->pluck('id', 'key')->toArray();
        $roleIdsBySlug = DB::table('roles')->pluck('id', 'slug')->toArray();

        $rolePermissionKeys = [
            'super_admin' => array_keys($permissionIdsByKey),
            'admin' => [
                'dashboard.view',
                'employees.view','employees.create','employees.edit','employees.delete','employees.pending_profiles.view','employees.pending_profiles.approve','employees.probation_internship.view','employees.probation_internship.manage','employees.exit.view','employees.exit.manage','employees.organization.manage','employees.reporting_structure.manage','departments.manage','designations.manage','employee.shift.assign.manage',
                'attendance.dashboard.view','attendance.records.view_all','attendance.monthly_report.view_all','attendance.monthly_summary.view','attendance.export','attendance.work_reports.view_all','attendance.rules.manage','attendance.blocked.view',
                'attendance.wfh.view','attendance.wfh.approve','attendance.wfh.reject','attendance.wfh.mark_lwp','attendance.wfh.assign',
                'leave.dashboard.view','leave.approvals.view_all','leave.approvals.approve','leave.approvals.reject','leave.balance.view_all','leave.allocation.view_all','leave.allocation.manage','leave.balance_logs.view',
                'enterprise_payroll.dashboard.view','enterprise_salary_structure.view','enterprise_salary_structure.manage','enterprise_payroll_run.view','enterprise_payroll_run.generate','enterprise_payroll_run.approve','enterprise_payroll_run.lock','enterprise_payroll_run.reopen','enterprise_payslip.view','enterprise_payslip.generate','enterprise_payslip.download','enterprise_bonus_incentive.view','enterprise_bonus_incentive.manage','enterprise_reimbursement.view','enterprise_reimbursement.manage','enterprise_fnf.view','enterprise_fnf.manage','enterprise_payroll_reports.view','enterprise_payroll.my_reimbursements.view','enterprise_payroll.my_reimbursements.create','enterprise_payroll.policy.view',
                'documents.upload.self','documents.company.view','documents.types.manage',
                'announcements.view','announcements.create','announcements.edit','announcements.delete','announcements.publish','announcements.print',
                'settings.profile.view','settings.profile.update','settings.policy_change_logs.view','settings.employee_policy_assignments.view','settings.employee_policy_assignments.manage','settings.notification_retention.manage',
                'mobile_app_versions.view','mobile_app_versions.manage','mobile_app_versions.upload','mobile_app_versions.delete',
                'document_generation.view','document_generation.template_create','document_generation.template_edit','document_generation.generate','document_generation.preview','document_generation.download','document_generation.email','document_generation.review','document_generation.delete',
            ],
            'hr_admin' => [
                'dashboard.view',
                // Employee Management
                'employees.view','employees.create','employees.edit','employees.delete','employees.directory.view','employees.pending_profiles.view','employees.pending_profiles.approve','employees.probation_internship.view','employees.probation_internship.manage','employees.exit.view','employees.exit.manage','employees.organization.manage','employees.reporting_structure.manage','departments.manage','designations.manage','organization_hierarchy.manage','employee.shift.assign.manage',
                'attendance.dashboard.view','attendance.records.view_all','attendance.my.view','attendance.blocked.view','attendance.blocked.unlock','attendance.regularization.view_own','attendance.regularization.view_team','attendance.regularization.view_all','attendance.regularization.create','attendance.regularization.approve','attendance.regularization.reject','attendance.holiday_work.view','attendance.holiday_work.manage','attendance.holiday_work.approve','attendance.holiday_work.reject','attendance.monthly_report.view_own','attendance.monthly_report.view_team','attendance.monthly_report.view_all','attendance.monthly_summary.view','attendance.violations.view','attendance.rules.manage','attendance.types.manage','attendance.policy_overrides.manage','attendance.weekoff_rules.manage','attendance.holidays.manage','attendance.export','attendance.work_reports.view_all',
                'attendance.wfh.view','attendance.wfh.approve','attendance.wfh.reject','attendance.wfh.mark_lwp','attendance.wfh.assign',
                'leave.dashboard.view','leave.my_requests.view','leave.my_requests.create','leave.my_requests.cancel','leave.approvals.view_team','leave.approvals.view_all','leave.approvals.approve','leave.approvals.reject','leave.team_calendar.view','leave.balance.view_own','leave.balance.view_team','leave.balance.view_all','leave.allocation.view_own','leave.allocation.view_all','leave.allocation.manage','leave.types.manage','leave.policies.manage','leave.holidays.manage','leave.weekoff_rules.manage','leave.comp_off.view_own','leave.comp_off.view_all','leave.comp_off.manage','leave.policy_overrides.manage','leave.balance_logs.view',
                'documents.compliance.view','documents.upload.self','documents.company.view','documents.types.manage','documents.verification.view','documents.verification.approve','documents.verification.reject',
                'announcements.view','announcements.create','announcements.edit','announcements.delete','announcements.publish','announcements.print',
                'settings.profile.view','settings.profile.update','settings.policy_change_logs.view','settings.employee_policy_assignments.view','settings.employee_policy_assignments.manage','settings.notification_retention.manage',
                'mobile_app_versions.view','mobile_app_versions.manage','mobile_app_versions.upload','mobile_app_versions.delete',
                'employee_documents.view','company_documents.manage','documents_self.view','documents_self.upload','employee.view',
                'enterprise_payroll.dashboard.view','enterprise_salary_structure.view','enterprise_salary_structure.manage','enterprise_payroll_run.view','enterprise_payroll_run.generate','enterprise_payroll_run.approve','enterprise_payroll_run.lock','enterprise_payroll_run.reopen','enterprise_payslip.view','enterprise_payslip.generate','enterprise_payslip.download','enterprise_bonus_incentive.view','enterprise_bonus_incentive.manage','enterprise_reimbursement.view','enterprise_reimbursement.manage','enterprise_fnf.view','enterprise_fnf.manage','enterprise_payroll_reports.view','enterprise_payroll.my_reimbursements.view','enterprise_payroll.my_reimbursements.create','enterprise_payroll.policy.view','enterprise_payroll.policy.update',
                'document_generation.view','document_generation.template_create','document_generation.template_edit','document_generation.generate','document_generation.preview','document_generation.download','document_generation.email','document_generation.review','document_generation.delete',
            ],
            'manager' => [
                'dashboard.view','employees.view','attendance.dashboard.view','attendance.my.view','attendance.records.view_all','attendance.regularization.view_own','attendance.regularization.view_team','attendance.regularization.create','attendance.regularization.approve','attendance.regularization.reject','attendance.monthly_report.view_own','attendance.monthly_report.view_team','attendance.work_reports.view_team','attendance.wfh.view','attendance.wfh.approve','attendance.wfh.mark_lwp','attendance.wfh.assign',
                'leave.dashboard.view','leave.my_requests.view','leave.my_requests.create','leave.my_requests.cancel','leave.approvals.view_team','leave.approvals.approve','leave.approvals.reject','leave.team_calendar.view','leave.balance.view_own','leave.balance.view_team','leave.comp_off.view_own',
                'documents.upload.self','documents.company.view','employee.announcements.view','employee.announcements.detail','settings.profile.view','settings.profile.update','documents_self.view',
            ],
            'finance_admin' => [
                'dashboard.view','employees.view','attendance.monthly_report.view_all','attendance.monthly_summary.view','attendance.export','leave.balance.view_all','leave.balance_logs.view',
                'enterprise_payroll.dashboard.view','enterprise_salary_structure.view','enterprise_salary_structure.manage','enterprise_payroll_run.view','enterprise_payroll_run.generate','enterprise_payroll_run.approve','enterprise_payroll_run.lock','enterprise_payroll_run.reopen','enterprise_payslip.view','enterprise_payslip.generate','enterprise_payslip.download','enterprise_bonus_incentive.view','enterprise_bonus_incentive.manage','enterprise_reimbursement.view','enterprise_reimbursement.manage','enterprise_fnf.view','enterprise_fnf.manage','enterprise_payroll_reports.view','enterprise_payroll.my_reimbursements.view','enterprise_payroll.my_reimbursements.create','enterprise_payroll.policy.view','enterprise_payroll.policy.update',
                'documents.company.view','employee.announcements.view','employee.announcements.detail','settings.profile.view','settings.profile.update',
            ],
            'project_admin' => [
                'dashboard.view',
                'employees.view',
                'attendance.monthly_report.view_all',
                'attendance.work_reports.view_all',
                'documents.company.view',
                'employee.announcements.view',
                'employee.announcements.detail',
                'settings.profile.view',
                'settings.profile.update',
            ],
            'operations_admin' => [
                'dashboard.view',
                'employees.view',
                'attendance.dashboard.view',
                'attendance.records.view_all',
                'attendance.monthly_report.view_all',
                'attendance.monthly_summary.view',
                'attendance.export',
                'attendance.work_reports.view_all',
                'attendance.wfh.view',
                'leave.dashboard.view',
                'leave.approvals.view_all',
                'leave.balance.view_all',
                'documents.company.view',
                'employee.announcements.view',
                'employee.announcements.detail',
                'settings.profile.view',
                'settings.profile.update',
            ],
            'employee' => [
                'dashboard.view','attendance.my.view','attendance.regularization.view_own','attendance.regularization.create','attendance.monthly_report.view_own','leave.my_requests.view','leave.my_requests.create','leave.my_requests.cancel','leave.balance.view_own','leave.comp_off.view_own','documents.upload.self','documents.company.view','employee.announcements.view','employee.announcements.detail','settings.profile.view','settings.profile.update','documents_self.view','documents_self.upload','attendance.work_reports.view_own','attendance.wfh.own',
                'enterprise_payroll.my_payslips.view','enterprise_payroll.my_reimbursements.view','enterprise_payroll.my_reimbursements.create',
            ],
            'custom_admin' => [
                'dashboard.view',
                'employees.view',
                'attendance.dashboard.view',
                'attendance.records.view_all',
                'attendance.monthly_report.view_all',
                'attendance.wfh.view',
                'leave.dashboard.view',
                'leave.approvals.view_all',
                'leave.balance.view_all',
                'documents.company.view',
                'employee.announcements.view',
                'employee.announcements.detail',
                'settings.profile.view',
                'settings.profile.update',
            ],
        ];

        DB::table('role_permissions')->truncate();

        $insertRows = [];
        foreach ($rolePermissionKeys as $slug => $keys) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if (! $roleId) {
                continue;
            }

            $permissionIds = $slug === 'super_admin'
                ? $allPermissionIds
                : collect($keys)->map(fn ($key) => $permissionIdsByKey[$key] ?? null)->filter()->unique()->values()->all();

            foreach ($permissionIds as $permissionId) {
                $insertRows[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($insertRows, 250) as $chunk) {
            DB::table('role_permissions')->insert($chunk);
        }
    }
}
