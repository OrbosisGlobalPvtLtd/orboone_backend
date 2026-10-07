<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $menus = [
            // 1. Dashboard
            ['id' => 1, 'name' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fas fa-house', 'module_key' => 'dashboard', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 1, 'is_active' => 1],

            // 2. Employee Management
            ['id' => 10, 'name' => 'Employee Management', 'route' => null, 'icon' => 'fas fa-users-cog', 'module_key' => 'employees', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 10, 'is_active' => 1],
            ['id' => 11, 'name' => 'Employee Directory', 'route' => 'hrms.employees.index', 'icon' => 'fas fa-address-book', 'module_key' => 'employees', 'permission_key' => 'employees.view', 'parent_id' => 10, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 12, 'name' => 'Onboard Employee', 'route' => 'hrms.employees.create', 'icon' => 'fas fa-user-plus', 'module_key' => 'employees', 'permission_key' => 'employees.create', 'parent_id' => 10, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 13, 'name' => 'Pending Profiles', 'route' => 'hrms.employees.pending_profiles', 'icon' => 'fas fa-user-clock', 'module_key' => 'employees', 'permission_key' => 'employees.pending_profiles.view', 'parent_id' => 10, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 15, 'name' => 'Probation / Internship', 'route' => 'hrms.employees.probation_internship', 'icon' => 'fas fa-hourglass-half', 'module_key' => 'employees', 'permission_key' => 'employees.probation_internship.view', 'parent_id' => 10, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 16, 'name' => 'Exit Employees', 'route' => 'hrms.employees.exit', 'icon' => 'fas fa-user-times', 'module_key' => 'employees', 'permission_key' => 'employees.exit.view', 'parent_id' => 10, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 17, 'name' => 'Dept & Designation', 'route' => 'hrms.organization.index', 'icon' => 'fas fa-building', 'module_key' => 'employees', 'permission_key' => 'departments.manage', 'parent_id' => 10, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 18, 'name' => 'Reporting Structure', 'route' => 'hrms.employees.reporting_structure', 'icon' => 'fas fa-sitemap', 'module_key' => 'employees', 'permission_key' => 'employees.reporting_structure.manage', 'parent_id' => 10, 'sort_order' => 7, 'is_active' => 1],
            ['id' => 19, 'name' => 'Shift Assignment', 'route' => 'employee.shift-assignment.index', 'icon' => 'fas fa-business-time', 'module_key' => 'employees', 'permission_key' => 'employee.shift.assign.manage', 'parent_id' => 10, 'sort_order' => 8, 'is_active' => 1],

            // 3. Attendance & Time Tracking
            ['id' => 20, 'name' => 'Attendance & Time Tracking', 'route' => null, 'icon' => 'fas fa-calendar-check', 'module_key' => 'attendance', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 20, 'is_active' => 1],
            // Self-Service Workflow
            ['id' => 349, 'name' => "Today's Attendance", 'route' => 'attendances.today', 'icon' => 'fas fa-fingerprint', 'module_key' => 'employee.attendance', 'permission_key' => 'attendance.my.view', 'parent_id' => 20, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 145, 'name' => 'My Attendance', 'route' => 'hrms.attendance.my', 'icon' => 'fas fa-user-clock', 'module_key' => 'my.attendance', 'permission_key' => null, 'parent_id' => 20, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 181, 'name' => 'My Work Reports', 'route' => 'hrms.attendance.my-work-reports', 'icon' => 'fas fa-user-edit', 'module_key' => 'my.attendance', 'permission_key' => 'attendance.work_reports.view_own', 'parent_id' => 20, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 163, 'name' => 'My WFH Requests', 'route' => 'hrms.attendance.my-wfh.index', 'icon' => 'fas fa-laptop-house', 'module_key' => 'my.attendance', 'permission_key' => 'attendance.wfh.own', 'parent_id' => 20, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 332, 'name' => 'My Holiday Work', 'route' => 'hrms.attendance.my-holiday-work.index', 'icon' => 'fas fa-calendar-plus', 'module_key' => 'my.attendance', 'permission_key' => null, 'parent_id' => 20, 'sort_order' => 5, 'is_active' => 1],
            // Attendance Operations
            ['id' => 21, 'name' => 'Attendance Dashboard', 'route' => 'attendances.index', 'icon' => 'fas fa-chart-line', 'module_key' => 'attendance', 'permission_key' => 'attendance.dashboard.view', 'parent_id' => 20, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 22, 'name' => 'Attendance Records', 'route' => 'attendances.record', 'icon' => 'fas fa-fingerprint', 'module_key' => 'attendance', 'permission_key' => 'attendance.records.view_all', 'parent_id' => 20, 'sort_order' => 7, 'is_active' => 1],
            ['id' => 380, 'name' => 'Team Attendance', 'route' => 'attendances.team', 'icon' => 'fas fa-users-cog', 'module_key' => 'attendance', 'permission_key' => null, 'parent_id' => 20, 'sort_order' => 8, 'is_active' => 1],
            // Exceptions / HR Actions
            ['id' => 23, 'name' => 'Blocked / HR Approval', 'route' => 'attendances.pending-approval', 'icon' => 'fas fa-user-lock', 'module_key' => 'attendance', 'permission_key' => 'attendance.blocked.view', 'parent_id' => 20, 'sort_order' => 9, 'is_active' => 1],
            ['id' => 28, 'name' => 'Regularization Requests', 'route' => 'hrms.attendance.regularizations.index', 'icon' => 'fas fa-user-check', 'module_key' => 'attendance', 'permission_key' => 'attendance.regularization.view', 'parent_id' => 20, 'sort_order' => 10, 'is_active' => 1],
            ['id' => 156, 'name' => 'WFH Requests', 'route' => 'hrms.attendance.wfh.index', 'icon' => 'fas fa-home', 'module_key' => 'attendance', 'permission_key' => 'attendance.wfh.view', 'parent_id' => 20, 'sort_order' => 11, 'is_active' => 1],
            ['id' => 29, 'name' => 'Holiday Work Requests', 'route' => 'hrms.attendance.holiday_work.index', 'icon' => 'fas fa-calendar-plus', 'module_key' => 'attendance', 'permission_key' => 'attendance.holiday_work.view', 'parent_id' => 20, 'sort_order' => 12, 'is_active' => 1],
            ['id' => 135, 'name' => 'Attendance Violations', 'route' => 'hrms.attendance.violations.index', 'icon' => 'fas fa-exclamation-triangle', 'module_key' => 'attendance', 'permission_key' => null, 'parent_id' => 20, 'sort_order' => 13, 'is_active' => 1],
            // Reports
            ['id' => 180, 'name' => 'Work Reports', 'route' => 'hrms.attendance.work-reports', 'icon' => 'fas fa-clipboard-list', 'module_key' => 'attendance', 'permission_key' => 'attendance.work_reports.view_all', 'parent_id' => 20, 'sort_order' => 14, 'is_active' => 1],
            ['id' => 26, 'name' => 'Monthly Attendance Report', 'route' => 'attendances.monthly-report', 'icon' => 'fas fa-calendar-alt', 'module_key' => 'attendance', 'permission_key' => 'attendance.monthly_report.view', 'parent_id' => 20, 'sort_order' => 15, 'is_active' => 1],
            ['id' => 134, 'name' => 'Monthly Attendance Summary', 'route' => 'hrms.attendance.monthly_summary.index', 'icon' => 'fas fa-table', 'module_key' => 'attendance', 'permission_key' => null, 'parent_id' => 20, 'sort_order' => 16, 'is_active' => 1],
            ['id' => 27, 'name' => 'Export Attendance Report', 'route' => 'attendances.export-pdf', 'icon' => 'fas fa-file-pdf', 'module_key' => 'attendance', 'permission_key' => 'attendance.export.view', 'parent_id' => 20, 'sort_order' => 17, 'is_active' => 1],
            // Configuration
            ['id' => 164, 'name' => 'Attendance Policies', 'route' => 'attendance.policies.index', 'icon' => 'fas fa-shield-alt', 'module_key' => 'attendance', 'permission_key' => 'attendance.rules.manage', 'parent_id' => 20, 'sort_order' => 18, 'is_active' => 1],
            ['id' => 24, 'name' => 'Shift Timings', 'route' => 'attendance.rules.index', 'icon' => 'fas fa-clock', 'module_key' => 'attendance', 'permission_key' => 'attendance.rules.manage', 'parent_id' => 20, 'sort_order' => 19, 'is_active' => 1],
            ['id' => 25, 'name' => 'Attendance Status Types', 'route' => 'attendance.types.index', 'icon' => 'fas fa-tags', 'module_key' => 'attendance', 'permission_key' => 'attendance.types.manage', 'parent_id' => 20, 'sort_order' => 20, 'is_active' => 1],
            ['id' => 136, 'name' => 'Attendance Policy Overrides', 'route' => 'hrms.attendance.policy_overrides.index', 'icon' => 'fas fa-sliders-h', 'module_key' => 'attendance', 'permission_key' => null, 'parent_id' => 20, 'sort_order' => 21, 'is_active' => 1],
            ['id' => 351, 'name' => 'Access Control', 'route' => 'attendances.access-control', 'icon' => 'fas fa-user-lock', 'module_key' => 'attendance', 'permission_key' => 'attendance.access_control.manage', 'parent_id' => 20, 'sort_order' => 22, 'is_active' => 1],

            // 4. Leave Management
            ['id' => 30, 'name' => 'Leave Management', 'route' => null, 'icon' => 'fas fa-calendar-alt', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 30, 'is_active' => 1],
            // Primary Operations & Approvals
            ['id' => 31, 'name' => 'Leave Dashboard', 'route' => 'hrms.leave.dashboard', 'icon' => 'fas fa-chart-pie', 'module_key' => 'leave', 'permission_key' => 'leave.dashboard.view', 'parent_id' => 30, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 33, 'name' => 'Leave Approvals', 'route' => 'leave-approvals.index', 'icon' => 'fas fa-check-double', 'module_key' => 'leave', 'permission_key' => 'leave.approvals.view_all', 'parent_id' => 30, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 146, 'name' => 'Team Leave Calendar', 'route' => 'hrms.leave.team_calendar.index', 'icon' => 'fas fa-calendar-week', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 3, 'is_active' => 1],
            // Requests & Balances
            ['id' => 32, 'name' => 'My Leave Requests', 'route' => 'leave-requests.index', 'icon' => 'fas fa-paper-plane', 'module_key' => 'my.leave', 'permission_key' => 'leave.my_requests.view', 'parent_id' => 30, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 137, 'name' => 'Apply Leave', 'route' => 'leave-requests.create', 'icon' => 'fas fa-plus-circle', 'module_key' => 'employee.leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 34, 'name' => 'Leave Balance', 'route' => 'hrms.leave.balances.index', 'icon' => 'fas fa-wallet', 'module_key' => 'employee.leave', 'permission_key' => 'leave.balance.view_all', 'parent_id' => 30, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 35, 'name' => 'Leave Allocation', 'route' => 'leave-allocations.index', 'icon' => 'fas fa-coins', 'module_key' => 'leave', 'permission_key' => 'leave.allocation.manage', 'parent_id' => 30, 'sort_order' => 7, 'is_active' => 1],
            ['id' => 133, 'name' => 'Compensatory Off', 'route' => 'hrms.comp_offs.index', 'icon' => 'fas fa-calendar-plus', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 8, 'is_active' => 1],
            ['id' => 36, 'name' => 'Leave History', 'route' => 'hrms.leave.history', 'icon' => 'fas fa-history', 'module_key' => 'leave', 'permission_key' => 'leave.history.view', 'parent_id' => 30, 'sort_order' => 9, 'is_active' => 1],
            ['id' => 140, 'name' => 'Leave Balance Logs', 'route' => 'hrms.leave.balance_logs.index', 'icon' => 'fas fa-history', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 10, 'is_active' => 1],
            // Configuration & Policy
            ['id' => 130, 'name' => 'Leave Types', 'route' => 'hrms.leave.types.index', 'icon' => 'fas fa-tags', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 11, 'is_active' => 1],
            ['id' => 131, 'name' => 'Leave Policies', 'route' => 'hrms.leave.policies.index', 'icon' => 'fas fa-sliders-h', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 12, 'is_active' => 1],
            ['id' => 132, 'name' => 'Holidays', 'route' => 'hrms.holidays.index', 'icon' => 'fas fa-glass-cheers', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 13, 'is_active' => 1],
            ['id' => 138, 'name' => 'Weekoff Rules', 'route' => 'hrms.weekoff_rules.index', 'icon' => 'fas fa-calendar-day', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 14, 'is_active' => 1],
            ['id' => 139, 'name' => 'Leave Policy Overrides', 'route' => 'hrms.leave.policy_overrides.index', 'icon' => 'fas fa-user-cog', 'module_key' => 'leave', 'permission_key' => null, 'parent_id' => 30, 'sort_order' => 15, 'is_active' => 1],

            // 5. Enterprise Payroll (Active)
            ['id' => 300, 'name' => 'Enterprise Payroll', 'route' => null, 'icon' => 'fas fa-wallet', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 40, 'is_active' => 1],
            // Employee Self-Service
            ['id' => 309, 'name' => 'My Payslips', 'route' => 'enterprise-payroll.self.payslips', 'icon' => 'fas fa-file-invoice-dollar', 'module_key' => 'employee.salary', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 310, 'name' => 'My Reimbursements', 'route' => 'enterprise-payroll.self.reimbursements', 'icon' => 'fas fa-receipt', 'module_key' => 'employee.salary', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 2, 'is_active' => 1],
            // Payroll Operations
            ['id' => 301, 'name' => 'Dashboard', 'route' => 'enterprise-payroll.dashboard', 'icon' => 'fas fa-chart-pie', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 302, 'name' => 'Salary Structures', 'route' => 'enterprise-payroll.salary-structures.index', 'icon' => 'fas fa-layer-group', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 303, 'name' => 'Payroll Runs', 'route' => 'enterprise-payroll.runs.index', 'icon' => 'fas fa-play-circle', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 304, 'name' => 'Payslips', 'route' => 'enterprise-payroll.payslips.index', 'icon' => 'fas fa-file-invoice-dollar', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 305, 'name' => 'Bonus & Incentives', 'route' => 'enterprise-payroll.bonus-incentives.index', 'icon' => 'fas fa-gift', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 7, 'is_active' => 1],
            ['id' => 306, 'name' => 'Reimbursements', 'route' => 'enterprise-payroll.reimbursements.index', 'icon' => 'fas fa-receipt', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 8, 'is_active' => 1],
            ['id' => 307, 'name' => 'FNF Settlements', 'route' => 'enterprise-payroll.fnf.index', 'icon' => 'fas fa-hand-holding-usd', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 9, 'is_active' => 1],
            // Reports
            ['id' => 308, 'name' => 'Reports', 'route' => 'enterprise-payroll.reports.index', 'icon' => 'fas fa-chart-bar', 'module_key' => 'enterprise_payroll', 'permission_key' => null, 'parent_id' => 300, 'sort_order' => 10, 'is_active' => 1],
            // Configuration
            ['id' => 311, 'name' => 'Payroll Policy Settings', 'route' => 'enterprise-payroll.policies.index', 'icon' => 'fas fa-cogs', 'module_key' => 'enterprise_payroll', 'permission_key' => 'enterprise_payroll.policy.view', 'parent_id' => 300, 'sort_order' => 11, 'is_active' => 1],

            // 6. Document Management
            ['id' => 50, 'name' => 'Document Management', 'route' => null, 'icon' => 'fas fa-folder-open', 'module_key' => 'documents', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 50, 'is_active' => 1],
            // Employee Self-Service
            ['id' => 52, 'name' => 'Upload Documents', 'route' => 'hrms.documents.self.index', 'icon' => 'fas fa-file-upload', 'module_key' => 'employee.documents', 'permission_key' => 'documents.upload.self', 'parent_id' => 50, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 161, 'name' => 'My Documents', 'route' => 'hrms.document-generation.self.index', 'icon' => 'fas fa-folder', 'module_key' => 'employee.documents', 'permission_key' => 'document_generation.view', 'parent_id' => 50, 'sort_order' => 2, 'is_active' => 1],
            // Document Administration
            ['id' => 53, 'name' => 'Company Documents & Policies', 'route' => 'documents.policies.index', 'icon' => 'fas fa-folder-open', 'module_key' => 'documents', 'permission_key' => 'documents.company.view', 'parent_id' => 50, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 51, 'name' => 'Compliance Management', 'route' => 'documents.compliance.index', 'icon' => 'fas fa-shield-alt', 'module_key' => 'documents', 'permission_key' => 'documents.compliance.view', 'parent_id' => 50, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 148, 'name' => 'Document Types', 'route' => 'documents.types.index', 'icon' => 'fas fa-file-alt', 'module_key' => 'documents', 'permission_key' => 'documents.types.manage', 'parent_id' => 50, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 149, 'name' => 'Document Verification', 'route' => 'documents.verification.index', 'icon' => 'fas fa-clipboard-check', 'module_key' => 'documents', 'permission_key' => 'documents.verification.view', 'parent_id' => 50, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 160, 'name' => 'Document Generation', 'route' => 'hrms.document-generation.dashboard', 'icon' => 'fas fa-file-signature', 'module_key' => 'document_generation', 'permission_key' => 'document_generation.view', 'parent_id' => 50, 'sort_order' => 7, 'is_active' => 1],

            // 7. Project Management
            ['id' => 320, 'name' => 'Project Management', 'route' => null, 'icon' => 'fas fa-bars-progress', 'module_key' => 'project_management', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 60, 'is_active' => 1],
            // Self / Project Work
            ['id' => 324, 'name' => 'My Projects', 'route' => 'projects.my', 'icon' => 'fas fa-folder', 'module_key' => 'project_management', 'permission_key' => 'projects.my_projects.view', 'parent_id' => 320, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 321, 'name' => 'Projects', 'route' => 'projects.index', 'icon' => 'fas fa-diagram-project', 'module_key' => 'project_management', 'permission_key' => 'projects.view_all', 'parent_id' => 320, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 322, 'name' => 'Tasks', 'route' => 'projects.tasks.index', 'icon' => 'fas fa-tasks', 'module_key' => 'project_management', 'permission_key' => 'projects.tasks.view', 'parent_id' => 320, 'sort_order' => 3, 'is_active' => 1],
            // Team Operations
            ['id' => 325, 'name' => 'Team Attendance', 'route' => 'projects.team.attendance', 'icon' => 'fas fa-user-clock', 'module_key' => 'project_management', 'permission_key' => null, 'parent_id' => 320, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 326, 'name' => 'Team Work Reports', 'route' => 'projects.team.work_reports', 'icon' => 'fas fa-file-invoice', 'module_key' => 'project_management', 'permission_key' => null, 'parent_id' => 320, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 327, 'name' => 'Team Leave', 'route' => 'projects.team.leave', 'icon' => 'fas fa-calendar-minus', 'module_key' => 'project_management', 'permission_key' => null, 'parent_id' => 320, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 323, 'name' => 'Team Work Logs', 'route' => 'reporting.work_reports', 'icon' => 'fas fa-clipboard-list', 'module_key' => 'project_management', 'permission_key' => null, 'parent_id' => 320, 'sort_order' => 7, 'is_active' => 1],
            // Project Configuration
            ['id' => 328, 'name' => 'Work Report Templates', 'route' => 'projects.templates.index', 'icon' => 'fas fa-file-signature', 'module_key' => 'project_management', 'permission_key' => null, 'parent_id' => 320, 'sort_order' => 8, 'is_active' => 1],

            // 8. Reporting Management (Admin)
            ['id' => 350, 'name' => 'Reporting Management', 'route' => '', 'icon' => 'fas fa-sitemap', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 65, 'is_active' => 1],
            ['id' => 352, 'name' => 'Reporting Structure', 'route' => 'reporting.structure', 'icon' => 'fas fa-network-wired', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 350, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 353, 'name' => 'Reporting Managers', 'route' => 'reporting.supervisors', 'icon' => 'fas fa-user-tie', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 350, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 354, 'name' => 'Employee Assignments', 'route' => 'reporting.assignments', 'icon' => 'fas fa-user-check', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 350, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 360, 'name' => 'Reporting History', 'route' => 'reporting.history', 'icon' => 'fas fa-history', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 350, 'sort_order' => 4, 'is_active' => 1],

            // 9. Team Management (Operational)
            ['id' => 370, 'name' => 'Team Management', 'route' => '', 'icon' => 'fas fa-users', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 66, 'is_active' => 1],
            ['id' => 371, 'name' => 'Dashboard', 'route' => 'reporting.dashboard', 'icon' => 'fas fa-tachometer-alt', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 370, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 372, 'name' => 'My Team', 'route' => 'reporting.my_employees', 'icon' => 'fas fa-users', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 370, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 373, 'name' => 'Attendance', 'route' => 'reporting.attendance', 'icon' => 'fas fa-user-clock', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 370, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 374, 'name' => 'Leave', 'route' => 'reporting.leave', 'icon' => 'fas fa-calendar-check', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 370, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 375, 'name' => 'Daily Work Reports', 'route' => 'reporting.work_reports', 'icon' => 'fas fa-file-alt', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 370, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 376, 'name' => 'Projects & Tasks', 'route' => 'reporting.projects', 'icon' => 'fas fa-project-diagram', 'module_key' => 'reporting', 'permission_key' => null, 'parent_id' => 370, 'sort_order' => 6, 'is_active' => 1],

            // 10. Notice / Announcement
            ['id' => 60, 'name' => 'Notice / Announcement', 'route' => 'announcements.index', 'icon' => 'fas fa-bullhorn', 'module_key' => 'announcements', 'permission_key' => 'announcements.view', 'parent_id' => null, 'sort_order' => 70, 'is_active' => 1],
            ['id' => 154, 'name' => 'My Announcements', 'route' => 'employee.announcements.index', 'icon' => 'fas fa-bullhorn', 'module_key' => 'employee.announcements', 'permission_key' => 'announcements.view_own', 'parent_id' => null, 'sort_order' => 71, 'is_active' => 1],

            // 11. Assets
            ['id' => 330, 'name' => 'Assets', 'route' => 'hrms.assets.index', 'icon' => 'fas fa-laptop', 'module_key' => 'assets', 'permission_key' => 'asset_allocations.manage', 'parent_id' => null, 'sort_order' => 75, 'is_active' => 1],
            ['id' => 331, 'name' => 'My Assets', 'route' => 'hrms.employee.assets.index', 'icon' => 'fas fa-laptop-code', 'module_key' => 'assets', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 76, 'is_active' => 1],

            // 12. Access Control
            ['id' => 70, 'name' => 'Access Control', 'route' => null, 'icon' => 'fas fa-user-shield', 'module_key' => 'access_control', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 80, 'is_active' => 1],
            ['id' => 71, 'name' => 'Roles', 'route' => 'roles.index', 'icon' => 'fas fa-user-tag', 'module_key' => 'access_control', 'permission_key' => 'roles.manage', 'parent_id' => 70, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 72, 'name' => 'Permissions', 'route' => 'permissions.index', 'icon' => 'fas fa-key', 'module_key' => 'access_control', 'permission_key' => 'permissions.manage', 'parent_id' => 70, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 73, 'name' => 'Admin Users', 'route' => 'admins.index', 'icon' => 'fas fa-user-cog', 'module_key' => 'access_control', 'permission_key' => 'admins.manage', 'parent_id' => 70, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 74, 'name' => 'Role Permission Mapping', 'route' => 'role_permissions.index', 'icon' => 'fas fa-shield-alt', 'module_key' => 'access_control', 'permission_key' => 'roles.manage', 'parent_id' => 70, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 75, 'name' => 'Role Menu Access', 'route' => 'role_menus.index', 'icon' => 'fas fa-list-check', 'module_key' => 'access_control', 'permission_key' => 'roles.manage', 'parent_id' => 70, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 378, 'name' => 'RBAC Visualizer', 'route' => 'access_control.visualizer.index', 'icon' => 'fas fa-diagram-project', 'module_key' => 'access_control', 'permission_key' => 'roles.manage', 'parent_id' => 70, 'sort_order' => 6, 'is_active' => 1],

            // 13. Settings
            ['id' => 80, 'name' => 'Settings', 'route' => null, 'icon' => 'fas fa-cogs', 'module_key' => 'settings', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 90, 'is_active' => 1],
            ['id' => 83, 'name' => 'My Profile', 'route' => 'profile.index', 'icon' => 'fas fa-user-circle', 'module_key' => 'my.profile', 'permission_key' => null, 'parent_id' => 80, 'sort_order' => 1, 'is_active' => 1],
            ['id' => 81, 'name' => 'System Settings', 'route' => 'settings.system.index', 'icon' => 'fas fa-sliders-h', 'module_key' => 'settings', 'permission_key' => 'settings.system.manage', 'parent_id' => 80, 'sort_order' => 2, 'is_active' => 1],
            ['id' => 82, 'name' => 'Company Settings', 'route' => 'settings.company.index', 'icon' => 'fas fa-building', 'module_key' => 'settings', 'permission_key' => 'settings.company.manage', 'parent_id' => 80, 'sort_order' => 3, 'is_active' => 1],
            ['id' => 84, 'name' => 'Company Branding', 'route' => 'settings.branding.index', 'icon' => 'fas fa-palette', 'module_key' => 'settings', 'permission_key' => 'settings.branding.view', 'parent_id' => 80, 'sort_order' => 4, 'is_active' => 1],
            ['id' => 143, 'name' => 'Policy Change Logs', 'route' => 'hrms.policy_change_logs.index', 'icon' => 'fas fa-history', 'module_key' => 'settings', 'permission_key' => null, 'parent_id' => 80, 'sort_order' => 5, 'is_active' => 1],
            ['id' => 144, 'name' => 'Employee Policy Assignments', 'route' => 'hrms.employee_policy_assignments.index', 'icon' => 'fas fa-user-shield', 'module_key' => 'settings', 'permission_key' => null, 'parent_id' => 80, 'sort_order' => 6, 'is_active' => 1],
            ['id' => 150, 'name' => 'Notification Retention', 'route' => 'settings.notification_retention.index', 'icon' => 'fas fa-bell-slash', 'module_key' => 'settings', 'permission_key' => null, 'parent_id' => 80, 'sort_order' => 7, 'is_active' => 1],
            ['id' => 151, 'name' => 'Mobile App Updates', 'route' => 'hrms.mobile-app-versions.index', 'icon' => 'fas fa-mobile-alt', 'module_key' => 'settings', 'permission_key' => 'mobile_app_versions.view', 'parent_id' => 80, 'sort_order' => 8, 'is_active' => 1],
            ['id' => 162, 'name' => 'Exit Policy', 'route' => 'settings.hrms_exit_policies.index', 'icon' => 'fas fa-user-clock', 'module_key' => 'settings', 'permission_key' => 'hrms_exit_policy.view', 'parent_id' => 80, 'sort_order' => 9, 'is_active' => 1],
            ['id' => 165, 'name' => 'Laravel Log Viewer', 'route' => 'log-viewer.index', 'icon' => 'fas fa-terminal', 'module_key' => 'settings', 'permission_key' => null, 'parent_id' => 80, 'sort_order' => 10, 'is_active' => 1],

            // 14. Standalone Modules
            ['id' => 90, 'name' => 'CRM', 'route' => 'module.crm', 'icon' => 'fas fa-handshake', 'module_key' => 'crm', 'permission_key' => null, 'parent_id' => null, 'sort_order' => 100, 'is_active' => 1],
        ];

        $hasPermCol = Schema::hasColumn('menus', 'permission_key');

        foreach ($menus as $menu) {
            $data = [
                'name' => $menu['name'],
                'route' => $menu['route'],
                'icon' => $menu['icon'],
                'module_key' => $menu['module_key'],
                'parent_id' => $menu['parent_id'],
                'sort_order' => $menu['sort_order'],
                'is_active' => isset($menu['is_active']) ? $menu['is_active'] : 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ];

            if ($hasPermCol) {
                $data['permission_key'] = $menu['permission_key'] ?? null;
            }

            DB::table('menus')->updateOrInsert(
                ['id' => $menu['id']],
                $data
            );
        }
    }
}
