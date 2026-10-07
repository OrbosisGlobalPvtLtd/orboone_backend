<?php

namespace App\Services\AccessControl;

class PermissionMapS
{
    public function roles(): array
    {
        return config('hrms_access.roles.system', []);
    }

    public function modules(): array
    {
        return config('hrms_access.modules', []);
    }

    public function permissionsByModule(): array
    {
        return config('hrms_access.permissions', []);
    }

    public function rolePermissionTemplates(): array
    {
        return config('hrms_access.role_permission_templates', []);
    }

    /**
     * Canonical Route -> Granular Permissions mapping for PermissionSyncService.
     */
    public static function getRoutePermissionMap(): array
    {
        return [
            // Dashboard
            'dashboard' => ['dashboard.view'],

            // Employee Management
            'hrms.employees.index' => ['employees.view', 'employees.directory.view'],
            'hrms.employees.create' => ['employees.create'],
            'hrms.employees.pending_profiles' => ['employees.pending_profiles.view', 'employees.pending_profiles.approve'],
            'hrms.employees.probation_internship' => ['employees.probation_internship.view', 'employees.probation_internship.manage', 'probation.manage'],
            'hrms.employees.exit' => ['employees.exit.view', 'employees.exit.manage', 'employee_exit.view', 'employee_exit.initiate', 'employee_exit.update', 'employee_exit.complete', 'employee_exit.cancel', 'employee_exit.asset_clearance', 'employee_exit.fnf_process', 'employee_exit.document_generate'],
            'hrms.organization.index' => ['departments.manage', 'designations.manage', 'employees.organization.manage', 'organization_hierarchy.manage'],
            'hrms.employees.reporting_structure' => ['employees.reporting_structure.manage'],
            'employee.shift-assignment.index' => ['employee.shift.assign.manage'],

            // Attendance Management
            'attendances.today' => ['attendance.my.view'],
            'hrms.attendance.my' => ['attendance.my.view', 'attendance_self.view'],
            'hrms.attendance.my-wfh.index' => ['attendance.wfh.own'],
            'hrms.attendance.my-holiday-work.index' => ['attendance.holiday_work.view'],
            'hrms.attendance.my-work-reports' => ['attendance.work_reports.view_own'],
            'hrms.attendance.regularizations.index' => ['attendance.regularization.view', 'attendance.regularization.view_own', 'attendance.regularization.create'],
            'attendances.index' => ['attendance.dashboard.view', 'attendance.view'],
            'attendances.record' => ['attendance.records.view_all', 'attendance_records.view', 'attendance.records.view', 'attendance.mark'],
            'attendances.team' => ['attendance.records.view_all', 'attendance.records.view_team', 'team.attendance.view'],
            'attendances.pending-approval' => ['attendance.blocked.view', 'attendance.blocked.unlock'],
            'hrms.attendance.holiday_work.index' => ['attendance.holiday_work.view', 'attendance.holiday_work.manage', 'attendance.holiday_work.approve', 'attendance.holiday_work.reject'],
            'hrms.attendance.holiday-work.index' => ['attendance.holiday_work.view', 'attendance.holiday_work.manage', 'attendance.holiday_work.approve', 'attendance.holiday_work.reject'],
            'hrms.attendance.wfh.index' => ['attendance.wfh.view', 'attendance.wfh.approve', 'attendance.wfh.reject', 'attendance.wfh.assign', 'attendance.wfh.mark_lwp', 'attendance.wfh.own'],
            'hrms.attendance.work-reports' => ['attendance.work_reports.view_all', 'attendance.work_reports.view_team'],
            'attendances.monthly-report' => ['attendance.monthly_report.view', 'attendance.monthly_report.view_all', 'attendance.report.generate', 'attendance.monthly_report.view_team', 'attendance.monthly_report.view_own'],
            'hrms.attendance.monthly_summary.index' => ['attendance.monthly_summary.view'],
            'hrms.attendance.violations.index' => ['attendance.violations.view'],
            'attendance.policies.index' => ['attendance.rules.manage', 'attendance_rules.manage'],
            'attendance.rules.index' => ['attendance.rules.manage', 'attendance_rules.manage'],
            'attendances.access-control' => ['attendance.access_control.manage', 'attendance.blocked.view'],
            'attendance.types.index' => ['attendance.types.manage'],
            'hrms.attendance.policy_overrides.index' => ['attendance.policy_overrides.manage'],
            'attendances.export-pdf' => ['attendance.export'],

            // Leave Management
            'leave-requests.create' => ['leave.my_requests.create', 'leave.apply', 'leave_self.apply'],
            'hrms.leave.balances.index' => ['leave.balance.view', 'leave.balance.view_own', 'leave_self.view_balance', 'hrms.leave.balance.view'],
            'employees-leave-request.summary' => ['leave.balance.view', 'leave.balance.view_own', 'leave_self.view_balance', 'hrms.leave.balance.view'],
            'leave-requests.index' => ['leave.my_requests.view', 'leave.my_requests.cancel', 'hrms.leave.request.view', 'hrms.leave.request.cancel', 'hrms.leave.request.create'],
            'hrms.comp_offs.index' => ['leave.comp_off.view', 'leave.comp_off.view_own', 'leave.comp_off.manage', 'leave.comp_off.view_all', 'hrms.comp_off.manage'],
            'hrms.leave.dashboard' => ['leave.dashboard.view', 'hrms.leave.dashboard.view'],
            'leave-approvals.index' => ['leave.approvals.view', 'leave.approvals.view_all', 'leave.approvals.view_team', 'leave.approvals.approve', 'leave.approvals.reject', 'leave.approve', 'hrms.leave.approval.view', 'hrms.leave.approval.approve', 'hrms.leave.approval.reject'],
            'hrms.leave.team_calendar.index' => ['leave.team_calendar.view', 'leave.balance.view_team'],
            'leave-allocations.index' => ['leave.allocation.manage', 'leave.allocation.view', 'leave.allocation.view_all', 'hrms.leave.allocation.manage'],
            'hrms.leave.types.index' => ['leave.types.manage', 'hrms.leave.type.manage'],
            'hrms.leave.policies.index' => ['leave.policies.manage', 'hrms.leave.policy.manage'],
            'hrms.holidays.index' => ['leave.holidays.manage', 'holiday.manage', 'hrms.holiday.manage', 'attendance.holidays.manage'],
            'hrms.weekoff_rules.index' => ['leave.weekoff_rules.manage', 'attendance.weekoff_rules.manage'],
            'hrms.leave.policy_overrides.index' => ['leave.policy_overrides.manage'],
            'hrms.leave.balance_logs.index' => ['leave.balance_logs.view'],
            'hrms.leave.history' => ['leave.my_requests.view', 'hrms.leave.request.view', 'leave.history.view', 'leave.approvals.view_all', 'leave.approvals.view_team'],

            // Documents
            'hrms.documents.self.index' => ['documents.upload.self', 'documents_self.view', 'documents_self.upload', 'employee_documents.view'],
            'hrms.document-generation.self.index' => ['document_generation.view', 'documents.upload.self', 'documents_self.view', 'employee_documents.view'],
            'documents.policies.index' => ['documents.company.view', 'company_documents.manage'],
            'documents.compliance.index' => ['documents.compliance.view', 'documents.compliance.manage'],
            'documents.types.index' => ['documents.types.manage'],
            'documents.verification.index' => ['documents.verification.view', 'documents.verification.approve', 'documents.verification.reject'],
            'hrms.document-generation.dashboard' => ['document_generation.view', 'document_generation.template_create', 'document_generation.template_edit', 'document_generation.generate', 'document_generation.preview', 'document_generation.download', 'document_generation.email', 'document_generation.review', 'document_generation.delete'],

            // Access Control
            'roles.index' => ['roles.manage', 'access.roles.manage'],
            'permissions.index' => ['permissions.manage', 'access.permissions.manage'],
            'admins.index' => ['admins.manage', 'access.admins.manage'],
            'role_permissions.index' => ['roles.manage', 'permissions.manage', 'module_access.manage', 'access.roles.manage'],
            'role_menus.index' => ['roles.manage', 'access.roles.manage'],
            'access_control.visualizer.index' => ['roles.manage'],

            // Settings
            'profile.index' => ['employee_profile_self.view', 'employee_profile_self.edit_limited', 'settings.profile.view', 'settings.profile.update'],
            'settings.system.index' => ['settings.system.manage'],
            'settings.company.index' => ['settings.company.manage'],
            'settings.branding.index' => ['settings.branding.view', 'settings.branding.update'],
            'hrms.policy_change_logs.index' => ['settings.policy_change_logs.view'],
            'hrms.employee_policy_assignments.index' => ['settings.employee_policy_assignments.view', 'settings.employee_policy_assignments.manage'],
            'settings.notification_retention.index' => ['settings.notification_retention.manage'],
            'hrms.mobile-app-versions.index' => ['mobile_app_versions.view', 'mobile_app_versions.manage', 'mobile_app_versions.upload', 'mobile_app_versions.delete'],
            'settings.hrms_exit_policies.index' => ['hrms_exit_policy.view', 'hrms_exit_policy.manage', 'hrms_exit_policy.update'],
            'log-viewer.index' => ['settings.system.manage'],

            // Enterprise Payroll
            'enterprise-payroll.self.payslips' => ['enterprise_payroll.my_payslips.view', 'enterprise_payslip.download'],
            'enterprise-payroll.self.reimbursements' => ['enterprise_payroll.my_reimbursements.view', 'enterprise_payroll.my_reimbursements.create'],
            'enterprise-payroll.dashboard' => ['enterprise_payroll.dashboard.view'],
            'enterprise-payroll.salary-structures.index' => ['enterprise_salary_structure.view', 'enterprise_salary_structure.manage'],
            'enterprise-payroll.runs.index' => ['enterprise_payroll_run.view', 'enterprise_payroll_run.generate', 'enterprise_payroll_run.approve', 'enterprise_payroll_run.lock', 'enterprise_payroll_run.reopen'],
            'enterprise-payroll.payslips.index' => ['enterprise_payslip.view', 'enterprise_payslip.generate', 'enterprise_payslip.download'],
            'enterprise-payroll.bonus-incentives.index' => ['enterprise_bonus_incentive.view', 'enterprise_bonus_incentive.manage'],
            'enterprise-payroll.reimbursements.index' => ['enterprise_reimbursement.view', 'enterprise_reimbursement.manage'],
            'enterprise-payroll.fnf.index' => ['enterprise_fnf.view', 'enterprise_fnf.manage'],
            'enterprise-payroll.reports.index' => ['enterprise_payroll_reports.view'],
            'enterprise-payroll.policies.index' => ['enterprise_payroll.policy.view', 'enterprise_payroll.policy.update'],

            // Project Management
            'projects.index' => ['projects.view_all', 'projects.manage', 'project_management.view', 'projects.my_projects.view', 'projects.delivery_head.view', 'projects.team_lead.view'],
            'projects.my' => ['projects.my_projects.view', 'project_management.view'],
            'projects.tasks.index' => ['projects.tasks.view', 'task.view', 'projects.tasks.manage', 'projects.view_all'],
            'projects.team.attendance' => ['projects.team.attendance.view', 'team.attendance.view', 'projects.team_attendance.view', 'attendance.records.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.team.work_reports' => ['projects.team.work_reports.view', 'team.work_report.view', 'projects.team_work_reports.view', 'attendance.work_reports.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.team.leave' => ['projects.team.leave.view', 'team.leave.view', 'projects.team_leave.view', 'leave.approvals.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.templates.index' => ['projects.templates.manage', 'projects.work_report.templates.manage', 'projects.manage'],

            // Assets
            'hrms.assets.index' => ['asset_allocations.manage', 'asset_allocation.manage', 'asset.view'],
            'hrms.employee.assets.index' => ['asset_allocations.manage', 'asset_allocation.manage', 'asset.view', 'employee_profile_self.view'],

            // Announcements
            'employee.announcements.index' => ['employee.announcements.view', 'employee.announcements.detail', 'announcements.view'],
            'announcements.index' => ['announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete', 'announcements.publish', 'announcements.print', 'employee.announcements.view'],

            // Reporting / Team Management
            'reporting.structure' => ['reporting.structure.view', 'reporting.structure.manage', 'reporting.view'],
            'reporting.supervisors' => ['reporting.supervisor.assign', 'reporting.view'],
            'reporting.assignments' => ['reporting.employee.assign', 'reporting.view'],
            'reporting.history' => ['reporting.history.view', 'reporting.view'],
            'reporting.dashboard' => ['reporting.view', 'team.dashboard.view', 'team.view'],
            'reporting.my_employees' => ['reporting.my_employees.view', 'team.employee.view', 'team.view'],
            'reporting.attendance' => ['reporting.attendance.view', 'team.attendance.view', 'team.view', 'attendance.records.view_all', 'attendance.monthly_report.view_team', 'attendance.regularization.view_team', 'attendance.dashboard.view'],
            'reporting.leave' => ['reporting.leave.view', 'team.leave.view', 'team.view'],
            'reporting.work_reports' => ['reporting.work_reports.view', 'team.work_report.view', 'team.view'],
            'reporting.projects' => ['reporting.projects.view', 'team.project.view', 'team.view'],
        ];
    }

    /**
     * Exact Route -> Granular Permission Keys Mapping for Sidebar runtime resolution.
     */
    public static function getSidebarRoutePermissionMap(): array
    {
        return [
            'employee.shift-assignment.index' => ['employee.shift.assign.manage'],
            'attendances.today' => ['attendance.my.view', 'attendance.records.view_all', 'attendance.dashboard.view'],
            'attendances.record' => ['attendance.records.view_all', 'attendance.dashboard.view'],
            'attendances.team' => ['attendance.records.view_all', 'attendance.monthly_report.view_team', 'attendance.regularization.view_team', 'attendance.dashboard.view'],
            'reporting.attendance' => ['attendance.records.view_all', 'attendance.monthly_report.view_team', 'attendance.regularization.view_team', 'attendance.dashboard.view'],
            'attendance.policies.index' => ['attendance.rules.manage'],
            'attendance.rules.index' => ['attendance.rules.manage'],
            'attendances.access-control' => ['attendance.blocked.view', 'attendance.access_control.manage', 'attendance.records.view_all', 'attendance.dashboard.view'],
            'documents.compliance.index' => ['documents.compliance.view'],
            'documents.verification.index' => ['documents.verification.view'],
            'documents.types.index' => ['documents.types.manage'],
            'documents.policies.index' => ['documents.company.view'],
            'hrms.documents.self.index' => ['documents.upload.self', 'documents_self.view'],
            'hrms.document-generation.dashboard' => ['document_generation.view'],
            'hrms.document-generation.self.index' => ['document_generation.view', 'employee_documents.view', 'documents.upload.self', 'documents_self.view'],
            'settings.hrms_exit_policies.index' => ['hrms_exit_policy.view', 'hrms_exit_policy.manage', 'hrms_exit_policy.update'],
            'settings.system.index' => ['settings.system.manage'],
            'settings.company.index' => ['settings.company.manage'],
            'settings.branding.index' => ['settings.branding.view', 'settings.branding.update'],
            'hrms.mobile-app-versions.index' => ['mobile_app_versions.view', 'mobile_app_versions.manage'],
            'roles.index' => ['roles.manage', 'access.roles.manage'],
            'role_permissions.index' => ['roles.manage', 'access.roles.manage'],
            'role_menus.index' => ['roles.manage', 'access.roles.manage'],
            'permissions.index' => ['permissions.manage', 'access.permissions.manage'],
            'admins.index' => ['admins.manage', 'access.admins.manage'],
            'hrms.attendance.work-reports' => ['attendance.work_reports.view_all', 'attendance.work_reports.view_team'],
            'hrms.attendance.my-work-reports' => ['attendance.work_reports.view_own'],
            'enterprise-payroll.policies.index' => ['enterprise_payroll.policy.view'],
            'hrms.organization.index' => ['departments.manage', 'designations.manage', 'employees.organization.manage'],
            'hrms.attendance.regularizations.index' => ['attendance.regularization.view_all', 'attendance.regularization.view_team', 'attendance.regularization.view_own'],
            'hrms.attendance.holiday-work.index' => ['attendance.holiday_work.view', 'attendance.holiday_work.manage', 'attendance.holiday_work.approve'],
            'hrms.attendance.wfh.index' => ['attendance.wfh.view', 'attendance.wfh.own'],
            'hrms.attendance.my-wfh.index' => ['attendance.wfh.own'],
            'hrms.leave.dashboard' => ['leave.dashboard.view', 'leave.my_requests.view'],
            'leave-requests.index' => ['leave.my_requests.view', 'leave.approvals.view_all', 'leave.history.view', 'leave.dashboard.view'],
            'leave-approvals.index' => ['leave.approvals.view_all', 'leave.approvals.view_team', 'leave.approvals.view', 'leave.approve'],
            'hrms.leave.history' => ['leave.history.view', 'leave.my_requests.view', 'leave.approvals.view_all', 'leave.approvals.view_team'],
            'leave-requests.create' => ['leave.my_requests.create', 'leave.my_requests.view', 'leave.apply', 'leave_self.apply', 'leave.approvals.view_all'],
            'leave-allocations.index' => ['leave.allocation.manage', 'leave.allocation.view_all', 'leave.allocation.view'],
            'hrms.leave.balances.index' => ['leave.balance.view_all', 'leave.balance.view_team', 'leave.balance.view_own', 'leave.balance.view', 'leave_self.view_balance'],
            'employees-leave-request.summary' => ['leave.balance.view_all', 'leave.balance.view_team', 'leave.balance.view_own', 'leave.balance.view', 'leave_self.view_balance'],
            'hrms.holidays.index' => ['leave.holidays.manage', 'leave.team_calendar.view'],
            'projects.index' => ['projects.view_all', 'projects.my_projects.view', 'projects.delivery_head.view', 'projects.team_lead.view'],
            'projects.my' => ['projects.my_projects.view'],
            'projects.tasks.index' => ['projects.tasks.view', 'projects.view_all'],
            'projects.team.attendance' => ['projects.team_attendance.view', 'attendance.records.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.team.work_reports' => ['projects.team_work_reports.view', 'attendance.work_reports.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.team.leave' => ['projects.team_leave.view', 'leave.approvals.view_all', 'projects.team_lead.view', 'projects.delivery_head.view'],
            'projects.templates.index' => ['projects.work_report.templates.manage', 'projects.manage'],
            'employee.announcements.index' => ['employee.announcements.view', 'announcements.view'],
            'announcements.index' => ['announcements.view', 'employee.announcements.view'],
            'hrms.assets.index' => ['asset_allocations.manage', 'asset_allocation.manage', 'asset.view'],
            'hrms.employee.assets.index' => ['asset_allocations.manage', 'asset_allocation.manage', 'asset.view', 'employee_profile_self.view'],
        ];
    }

    /**
     * Canonical HR Admin permission prefix rules.
     */
    public static function getHrAdminPrefixes(): array
    {
        return [
            'employees.',
            'employee.',
            'employee_',
            'attendance.',
            'attendance_',
            'leave.',
            'leave_',
            'departments.',
            'designations.',
            'organization_hierarchy.',
            'probation.',
            'internship.',
            'hrms_exit_policy.',
            'reporting.',
            'reporting_structure.',
            'document_generation.',
            'documents.',
            'company_documents.',
            'asset.',
            'assets.',
            'asset_allocation.',
            'asset_allocations.',
        ];
    }

    /**
     * Exact routes classified as Employee Self-Service (ESS).
     */
    public static function getEmployeeSelfServiceRoutes(): array
    {
        return [
            'profile.index',
            'attendances.today',
            'hrms.attendance.my',
            'hrms.attendance.my-work-reports',
            'hrms.attendance.my-wfh.index',
            'hrms.attendance.my-holiday-work.index',
            'leave-requests.index',
            'leave-requests.create',
            'hrms.leave.balances.index',
            'enterprise-payroll.self.payslips',
            'enterprise-payroll.self.reimbursements',
            'enterprise_payroll.my_payslips.view',
            'enterprise_payroll.my_reimbursements.view',
            'hrms.documents.self.index',
            'hrms.document-generation.self.index',
            'projects.my',
            'employee.announcements.index',
            'hrms.employee.assets.index',
        ];
    }

    /**
     * Route prefixes classified as Employee Self-Service (ESS).
     */
    public static function getEmployeeSelfServiceRoutePrefixes(): array
    {
        return [
            'hrms.attendance.my-',
            'hrms.attendance.my',
            'hrms.documents.self.',
            'employee.announcements.',
            'enterprise-payroll.self.',
            'enterprise-payroll.my_',
            'enterprise_payroll.my_',
            'hrms.employee.',
            'profile.',
        ];
    }

    /**
     * Menu names/keywords classified as Employee Self-Service (ESS).
     */
    public static function getEmployeeSelfServiceNames(): array
    {
        return [
            "today's attendance",
            'my attendance',
            'my work reports',
            'my wfh requests',
            'my holiday work',
            'my leave requests',
            'my leaves',
            'apply leave',
            'leave balance',
            'my payslips',
            'my salary slips',
            'my reimbursements',
            'upload documents',
            'my documents',
            'my projects',
            'my announcements',
            'my assets',
            'my profile',
            'complete profile',
            'my tasks',
        ];
    }

    /**
     * Module key prefixes classified as Employee Self-Service (ESS).
     */
    public static function getEmployeeSelfServiceModulePrefixes(): array
    {
        return [
            'my.',
            'my_',
            'employee.documents',
            'employee.attendance',
            'employee.leave',
            'employee.salary',
            'employee.payroll',
            'employee.announcements',
            'employee.assets',
        ];
    }

    /**
     * Single canonical detector for Employee Self-Service menus.
     *
     * @param object|array $menu
     */
    public static function isEmployeeSelfServiceMenu(object|array $menu): bool
    {
        $obj = (object) $menu;
        $id = (int) ($obj->id ?? 0);
        $route = strtolower(trim((string) ($obj->route ?? '')));
        $name = strtolower(trim((string) ($obj->name ?? '')));
        $moduleKey = strtolower(trim((string) ($obj->module_key ?? '')));

        // Explicit non-ESS exceptions (e.g. Shift Assignment is an administrative operation)
        if ($id === 19 || $route === 'employee.shift-assignment.index' || str_contains($name, 'shift assignment')) {
            return false;
        }

        if (in_array($route, static::getEmployeeSelfServiceRoutes(), true)) {
            return true;
        }

        foreach (static::getEmployeeSelfServiceRoutePrefixes() as $prefix) {
            if ($prefix !== '' && str_starts_with($route, $prefix)) {
                return true;
            }
        }

        if (in_array($name, static::getEmployeeSelfServiceNames(), true) || str_starts_with($name, 'my ')) {
            return true;
        }

        foreach (static::getEmployeeSelfServiceModulePrefixes() as $prefix) {
            if ($prefix !== '' && str_starts_with($moduleKey, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Single permission alias mapping (target -> canonical replacement).
     */
    public static function getPermissionAliases(): array
    {
        return [
            'employees.update' => 'employees.edit',
        ];
    }

    /**
     * Compound / expand-to-any permission alias mappings.
     */
    public static function getAttendanceAliasExpansions(): array
    {
        return [
            'attendance.regularization.view' => [
                'attendance.regularization.view_all',
                'attendance.regularization.view_team',
                'attendance.regularization.view_own',
            ],
            'attendance.monthly_report.view' => [
                'attendance.monthly_report.view_all',
                'attendance.monthly_report.view_team',
                'attendance.monthly_report.view_own',
            ],
            'attendance.work_reports.view' => [
                'attendance.work_reports.view_all',
                'attendance.work_reports.view_team',
                'attendance.work_reports.view_own',
            ],
        ];
    }
}
