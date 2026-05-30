<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $menus = [
            ['id' => 1, 'name' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fas fa-house', 'module_key' => 'dashboard', 'parent_id' => null, 'sort_order' => 1],

            ['id' => 10, 'name' => 'Employee Management', 'route' => null, 'icon' => 'fas fa-users-cog', 'module_key' => 'employees', 'parent_id' => null, 'sort_order' => 10],
            ['id' => 11, 'name' => 'Employee Directory', 'route' => 'hrms.employees.index', 'icon' => 'fas fa-address-book', 'module_key' => 'employees', 'parent_id' => 10, 'sort_order' => 1],
            ['id' => 12, 'name' => 'Onboard Employee', 'route' => 'hrms.employees.create', 'icon' => 'fas fa-user-plus', 'module_key' => 'employees', 'parent_id' => 10, 'sort_order' => 2],
            ['id' => 13, 'name' => 'Pending Profiles', 'route' => 'hrms.employees.pending_profiles', 'icon' => 'fas fa-user-clock', 'module_key' => 'employees', 'parent_id' => 10, 'sort_order' => 3],
            ['id' => 15, 'name' => 'Probation / Internship', 'route' => 'hrms.employees.probation_internship', 'icon' => 'fas fa-hourglass-half', 'module_key' => 'employees', 'parent_id' => 10, 'sort_order' => 5],
            ['id' => 16, 'name' => 'Exit Employees', 'route' => 'hrms.employees.exit', 'icon' => 'fas fa-user-times', 'module_key' => 'employees', 'parent_id' => 10, 'sort_order' => 6],
            ['id' => 17, 'name' => 'Dept & Designation', 'route' => 'hrms.organization.index', 'icon' => 'fas fa-building', 'module_key' => 'employees', 'parent_id' => 10, 'sort_order' => 7],
            ['id' => 18, 'name' => 'Reporting Structure', 'route' => 'hrms.employees.reporting_structure', 'icon' => 'fas fa-sitemap', 'module_key' => 'employees', 'parent_id' => 10, 'sort_order' => 8],

            ['id' => 20, 'name' => 'Attendance & Time Tracking', 'route' => null, 'icon' => 'fas fa-calendar-check', 'module_key' => 'attendance', 'parent_id' => null, 'sort_order' => 20],
            ['id' => 21, 'name' => 'Attendance Dashboard', 'route' => 'attendances.index', 'icon' => 'fas fa-chart-line', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 1],
            ['id' => 22, 'name' => 'Attendance Records', 'route' => 'attendances.record', 'icon' => 'fas fa-fingerprint', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 2],
            ['id' => 145, 'name' => 'My Attendance', 'route' => 'hrms.attendance.my', 'icon' => 'fas fa-user-clock', 'module_key' => 'my.attendance', 'parent_id' => 20, 'sort_order' => 3],
            ['id' => 23, 'name' => 'Blocked / HR Approval', 'route' => 'attendances.pending-approval', 'icon' => 'fas fa-user-lock', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 4],
            ['id' => 28, 'name' => 'Regularization Requests', 'route' => 'hrms.attendance.regularizations.index', 'icon' => 'fas fa-user-check', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 5],
            ['id' => 29, 'name' => 'Holiday Work Requests', 'route' => 'hrms.attendance.holiday_work.index', 'icon' => 'fas fa-calendar-plus', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 6],
            ['id' => 156, 'name' => 'WFH Requests', 'route' => 'hrms.attendance.wfh.index', 'icon' => 'fas fa-home', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 7],
            ['id' => 163, 'name' => 'My WFH Requests', 'route' => 'hrms.attendance.my-wfh.index', 'icon' => 'fas fa-laptop-house', 'module_key' => 'my.attendance', 'parent_id' => 20, 'sort_order' => 8],
            ['id' => 26, 'name' => 'Monthly Attendance Report', 'route' => 'attendances.monthly-report', 'icon' => 'fas fa-calendar-alt', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 8],
            ['id' => 134, 'name' => 'Monthly Attendance Summary', 'route' => 'hrms.attendance.monthly_summary.index', 'icon' => 'fas fa-table', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 9],
            ['id' => 135, 'name' => 'Attendance Violations', 'route' => 'hrms.attendance.violations.index', 'icon' => 'fas fa-exclamation-triangle', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 10],
            ['id' => 24, 'name' => 'Shift & Attendance Rules', 'route' => 'attendance.rules.index', 'icon' => 'fas fa-business-time', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 11],
            ['id' => 25, 'name' => 'Attendance Status Types', 'route' => 'attendance.types.index', 'icon' => 'fas fa-tags', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 12],
            ['id' => 136, 'name' => 'Attendance Policy Overrides', 'route' => 'hrms.attendance.policy_overrides.index', 'icon' => 'fas fa-sliders-h', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 13],
            ['id' => 27, 'name' => 'Export Attendance Report', 'route' => 'attendances.export-pdf', 'icon' => 'fas fa-file-pdf', 'module_key' => 'attendance', 'parent_id' => 20, 'sort_order' => 14],

            ['id' => 30, 'name' => 'Leave Management', 'route' => null, 'icon' => 'fas fa-calendar-alt', 'module_key' => 'leave', 'parent_id' => null, 'sort_order' => 30],
            ['id' => 31, 'name' => 'Leave Dashboard', 'route' => 'hrms.leave.dashboard', 'icon' => 'fas fa-chart-pie', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 1],
            ['id' => 32, 'name' => 'My Leave Requests', 'route' => 'leave-requests.index', 'icon' => 'fas fa-paper-plane', 'module_key' => 'my.leave', 'parent_id' => 30, 'sort_order' => 2],
            ['id' => 137, 'name' => 'Apply Leave', 'route' => 'leave-requests.create', 'icon' => 'fas fa-plus-circle', 'module_key' => 'employee.leave', 'parent_id' => 30, 'sort_order' => 3],
            ['id' => 33, 'name' => 'Leave Approvals', 'route' => 'leave-approvals.index', 'icon' => 'fas fa-check-double', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 4],
            ['id' => 146, 'name' => 'Team Leave Calendar', 'route' => 'hrms.leave.team_calendar.index', 'icon' => 'fas fa-calendar-week', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 5],
            ['id' => 34, 'name' => 'Leave Balance', 'route' => 'hrms.leave.balances.index', 'icon' => 'fas fa-wallet', 'module_key' => 'employee.leave', 'parent_id' => 30, 'sort_order' => 6],
            ['id' => 35, 'name' => 'Leave Allocation', 'route' => 'leave-allocations.index', 'icon' => 'fas fa-coins', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 7],
            ['id' => 130, 'name' => 'Leave Types', 'route' => 'hrms.leave.types.index', 'icon' => 'fas fa-tags', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 8],
            ['id' => 131, 'name' => 'Leave Policies', 'route' => 'hrms.leave.policies.index', 'icon' => 'fas fa-sliders-h', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 9],
            ['id' => 132, 'name' => 'Holidays', 'route' => 'hrms.holidays.index', 'icon' => 'fas fa-glass-cheers', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 10],
            ['id' => 138, 'name' => 'Weekoff Rules', 'route' => 'hrms.weekoff_rules.index', 'icon' => 'fas fa-calendar-day', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 11],
            ['id' => 133, 'name' => 'Comp Off', 'route' => 'hrms.comp_offs.index', 'icon' => 'fas fa-calendar-plus', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 12],
            ['id' => 139, 'name' => 'Leave Policy Overrides', 'route' => 'hrms.leave.policy_overrides.index', 'icon' => 'fas fa-user-cog', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 13],
            ['id' => 140, 'name' => 'Leave Balance Logs', 'route' => 'hrms.leave.balance_logs.index', 'icon' => 'fas fa-history', 'module_key' => 'leave', 'parent_id' => 30, 'sort_order' => 14],

            ['id' => 40, 'name' => 'Payroll Management', 'route' => null, 'icon' => 'fas fa-money-check-alt', 'module_key' => 'payroll', 'parent_id' => null, 'sort_order' => 40],
            ['id' => 42, 'name' => 'Payroll Dashboard', 'route' => 'pages.payroll.dashboard', 'icon' => 'fas fa-chart-pie', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 1],
            ['id' => 41, 'name' => 'Salary Structure', 'route' => 'pages.payroll.index', 'icon' => 'fas fa-layer-group', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 2],
            ['id' => 141, 'name' => 'Attendance Payroll Impact', 'route' => 'hrms.payroll.attendance_impacts.index', 'icon' => 'fas fa-file-invoice', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 3],
            ['id' => 142, 'name' => 'Generate Payroll', 'route' => 'hrms.payroll.generate.index', 'icon' => 'fas fa-play-circle', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 4],
            ['id' => 43, 'name' => 'My Salary Slips', 'route' => 'pages.payroll.payslips', 'icon' => 'fas fa-file-invoice-dollar', 'module_key' => 'employee.salary', 'parent_id' => 40, 'sort_order' => 5],
            ['id' => 44, 'name' => 'Settlement (FNF)', 'route' => 'pages.payroll.fnf', 'icon' => 'fas fa-walking', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 6],
            ['id' => 45, 'name' => 'Bonus Management', 'route' => 'pages.payroll.index', 'icon' => 'fas fa-gift', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 7],
            ['id' => 147, 'name' => 'Monthly Payroll Summary', 'route' => 'hrms.payroll.monthly_summary.index', 'icon' => 'fas fa-table', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 8],
            ['id' => 155, 'name' => 'Payroll Adjustments', 'route' => 'hrms.payroll.adjustments.index', 'icon' => 'fas fa-sliders-h', 'module_key' => 'payroll', 'parent_id' => 40, 'sort_order' => 9],

            ['id' => 50, 'name' => 'Document Management', 'route' => null, 'icon' => 'fas fa-folder-open', 'module_key' => 'documents', 'parent_id' => null, 'sort_order' => 50],
            ['id' => 51, 'name' => 'Compliance Management', 'route' => 'documents.compliance.index', 'icon' => 'fas fa-shield-alt', 'module_key' => 'documents', 'parent_id' => 50, 'sort_order' => 1],

            ['id' => 52, 'name' => 'Upload Documents', 'route' => 'hrms.documents.self.index', 'icon' => 'fas fa-file-upload', 'module_key' => 'employee.documents', 'parent_id' => 50, 'sort_order' => 2],

            ['id' => 53, 'name' => 'Company Documents & Policies', 'route' => 'documents.policies.index', 'icon' => 'fas fa-folder-open', 'module_key' => 'documents', 'parent_id' => 50, 'sort_order' => 3],

            ['id' => 148, 'name' => 'Document Types', 'route' => 'documents.types.index', 'icon' => 'fas fa-file-alt', 'module_key' => 'documents', 'parent_id' => 50, 'sort_order' => 4],

            ['id' => 149, 'name' => 'Document Verification', 'route' => 'documents.verification.index', 'icon' => 'fas fa-clipboard-check', 'module_key' => 'documents', 'parent_id' => 50, 'sort_order' => 5],
            ['id' => 160, 'name' => 'Document Generation', 'route' => 'hrms.document-generation.dashboard', 'icon' => 'fas fa-file-signature', 'module_key' => 'document_generation', 'parent_id' => 50, 'sort_order' => 6],
            ['id' => 161, 'name' => 'My Documents', 'route' => 'hrms.document-generation.self.index', 'icon' => 'fas fa-folder', 'module_key' => 'employee.documents', 'parent_id' => 50, 'sort_order' => 7],

            ['id' => 60, 'name' => 'Notice / Announcement', 'route' => 'announcements.index', 'icon' => 'fas fa-bullhorn', 'module_key' => 'announcements', 'parent_id' => null, 'sort_order' => 60],
            ['id' => 154, 'name' => 'My Announcements', 'route' => 'employee.announcements.index', 'icon' => 'fas fa-bullhorn', 'module_key' => 'employee.announcements', 'parent_id' => null, 'sort_order' => 61],

            ['id' => 70, 'name' => 'Access Control', 'route' => null, 'icon' => 'fas fa-user-shield', 'module_key' => 'access_control', 'parent_id' => null, 'sort_order' => 70],
            ['id' => 71, 'name' => 'Roles', 'route' => 'roles.index', 'icon' => 'fas fa-user-tag', 'module_key' => 'access_control', 'parent_id' => 70, 'sort_order' => 1],
            ['id' => 72, 'name' => 'Permissions', 'route' => 'permissions.index', 'icon' => 'fas fa-key', 'module_key' => 'access_control', 'parent_id' => 70, 'sort_order' => 2],
            ['id' => 73, 'name' => 'Admin Users', 'route' => 'admins.index', 'icon' => 'fas fa-user-cog', 'module_key' => 'access_control', 'parent_id' => 70, 'sort_order' => 3],

            ['id' => 80, 'name' => 'Settings', 'route' => null, 'icon' => 'fas fa-cogs', 'module_key' => 'settings', 'parent_id' => null, 'sort_order' => 80],
            ['id' => 81, 'name' => 'System Settings', 'route' => 'settings.system.index', 'icon' => 'fas fa-sliders-h', 'module_key' => 'settings', 'parent_id' => 80, 'sort_order' => 1],
            ['id' => 82, 'name' => 'Company Settings', 'route' => 'settings.company.index', 'icon' => 'fas fa-building', 'module_key' => 'settings', 'parent_id' => 80, 'sort_order' => 2],
            ['id' => 83, 'name' => 'My Profile', 'route' => 'profile.index', 'icon' => 'fas fa-user-circle', 'module_key' => 'my.profile', 'parent_id' => 80, 'sort_order' => 3],
            ['id' => 143, 'name' => 'Policy Change Logs', 'route' => 'hrms.policy_change_logs.index', 'icon' => 'fas fa-history', 'module_key' => 'settings', 'parent_id' => 80, 'sort_order' => 4],
            ['id' => 144, 'name' => 'Employee Policy Assignments', 'route' => 'hrms.employee_policy_assignments.index', 'icon' => 'fas fa-user-shield', 'module_key' => 'settings', 'parent_id' => 80, 'sort_order' => 5],
            ['id' => 150, 'name' => 'Notification Retention', 'route' => 'settings.notification_retention.index', 'icon' => 'fas fa-bell-slash', 'module_key' => 'settings', 'parent_id' => 80, 'sort_order' => 6],
            ['id' => 151, 'name' => 'Mobile App Updates', 'route' => 'hrms.mobile-app-versions.index', 'icon' => 'fas fa-mobile-alt', 'module_key' => 'settings', 'parent_id' => 80, 'sort_order' => 7],
            ['id' => 162, 'name' => 'Exit Policy', 'route' => 'settings.hrms_exit_policies.index', 'icon' => 'fas fa-user-clock', 'module_key' => 'settings', 'parent_id' => 80, 'sort_order' => 8],

            ['id' => 90, 'name' => 'CRM', 'route' => 'module.crm', 'icon' => 'fas fa-handshake', 'module_key' => 'crm', 'parent_id' => null, 'sort_order' => 90],
            ['id' => 320, 'name' => 'Project Management', 'route' => null, 'icon' => 'fas fa-project-diagram', 'module_key' => 'project_management', 'parent_id' => null, 'sort_order' => 65],
            ['id' => 321, 'name' => 'Projects', 'route' => 'module.project-mgmt', 'icon' => 'fas fa-folder-open', 'module_key' => 'project_management', 'parent_id' => 320, 'sort_order' => 1],
            ['id' => 322, 'name' => 'Tasks', 'route' => 'project_management.tasks.index', 'icon' => 'fas fa-tasks', 'module_key' => 'project_management', 'parent_id' => 320, 'sort_order' => 2],
            ['id' => 323, 'name' => 'Team Work Logs', 'route' => 'attendances.daily', 'icon' => 'fas fa-clipboard-list', 'module_key' => 'project_management', 'parent_id' => 320, 'sort_order' => 3],
            ['id' => 330, 'name' => 'Assets', 'route' => 'hrms.assets.index', 'icon' => 'fas fa-laptop', 'module_key' => 'assets', 'parent_id' => null, 'sort_order' => 66],
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
                    'is_active' => 1,
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        DB::table('menus')->whereIn('id', [40, 41, 42, 43, 44, 45, 141, 142, 147, 155])->update([
            'is_active' => 0,
            'updated_at' => $now,
        ]);

        $enterpriseMenus = [
            [300, 'Enterprise Payroll', null, 'fas fa-money-check-alt', 'enterprise_payroll', null, 40],
            [301, 'Dashboard', 'enterprise-payroll.dashboard', 'fas fa-chart-pie', 'enterprise_payroll', 300, 1],
            [302, 'Salary Structures', 'enterprise-payroll.salary-structures.index', 'fas fa-layer-group', 'enterprise_payroll', 300, 2],
            [303, 'Payroll Runs', 'enterprise-payroll.runs.index', 'fas fa-play-circle', 'enterprise_payroll', 300, 3],
            [304, 'Payslips', 'enterprise-payroll.payslips.index', 'fas fa-file-invoice-dollar', 'enterprise_payroll', 300, 4],
            [305, 'Bonus & Incentives', 'enterprise-payroll.bonus-incentives.index', 'fas fa-gift', 'enterprise_payroll', 300, 5],
            [306, 'Reimbursements', 'enterprise-payroll.reimbursements.index', 'fas fa-receipt', 'enterprise_payroll', 300, 6],
            [307, 'FNF Settlements', 'enterprise-payroll.fnf.index', 'fas fa-hand-holding-usd', 'enterprise_payroll', 300, 7],
            [308, 'Reports', 'enterprise-payroll.reports.index', 'fas fa-chart-bar', 'enterprise_payroll', 300, 8],
            [311, 'Payroll Policy Settings', 'enterprise-payroll.policies.index', 'fas fa-cogs', 'enterprise_payroll', 300, 9],
            [309, 'My Payslips', 'enterprise-payroll.self.payslips', 'fas fa-file-invoice-dollar', 'employee.salary', null, 42],
            [310, 'My Reimbursements', 'enterprise-payroll.self.reimbursements', 'fas fa-receipt', 'employee.salary', null, 43],
        ];

        foreach ($enterpriseMenus as [$id, $name, $route, $icon, $moduleKey, $parentId, $sortOrder]) {
            DB::table('menus')->updateOrInsert(
                ['id' => $id],
                [
                    'name' => $name,
                    'route' => $route,
                    'icon' => $icon,
                    'module_key' => $moduleKey,
                    'parent_id' => $parentId,
                    'sort_order' => $sortOrder,
                    'is_active' => 1,
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        DB::table('menus')->whereIn('id', [152, 153])->update([
            'is_active' => 0,
            'updated_at' => $now,
        ]);

        DB::table('menus')
            ->where('route', 'hrms.weekoff_rules.index')
            ->where('id', '<>', 138)
            ->update(['is_active' => 0, 'updated_at' => $now]);

        DB::table('menus')
            ->where('route', 'hrms.holidays.index')
            ->where('id', '<>', 132)
            ->update(['is_active' => 0, 'updated_at' => $now]);
    }
}
