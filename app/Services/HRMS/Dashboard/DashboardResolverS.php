<?php

namespace App\Services\HRMS\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DashboardResolverS
{
    private const ROLE_PRIORITY = [
        'super_admin' => [
            'title' => 'Super Admin Dashboard',
            'route' => 'dashboard.super_admin',
            'view' => 'dashboard.super-admin',
        ],
        'hr_admin' => [
            'title' => 'HR Admin Dashboard',
            'route' => 'dashboard.hr_admin',
            'view' => 'dashboard.hr-admin',
        ],
        'admin' => [
            'title' => 'Admin Dashboard',
            'route' => 'dashboard.admin',
            'view' => 'dashboard.admin',
        ],
        'finance_admin' => [
            'title' => 'Finance Admin Dashboard',
            'route' => 'dashboard.finance_admin',
            'view' => 'dashboard.finance-admin',
        ],
        'project_admin' => [
            'title' => 'Project Admin Dashboard',
            'route' => 'dashboard.project_admin',
            'view' => 'dashboard.project-admin',
        ],
        'operations_admin' => [
            'title' => 'Operations Admin Dashboard',
            'route' => 'dashboard.operations_admin',
            'view' => 'dashboard.operations-admin',
        ],
        'custom_admin' => [
            'title' => 'Custom Admin Dashboard',
            'route' => 'dashboard.custom_admin',
            'view' => 'dashboard.custom-admin',
        ],
        'manager' => [
            'title' => 'Manager Dashboard',
            'route' => 'dashboard.manager',
            'view' => 'dashboard.manager',
        ],
        'employee' => [
            'title' => 'Employee Dashboard',
            'route' => 'dashboard.employee',
            'view' => 'dashboard.employee',
        ],
    ];

    public function resolveRole($user): string
    {
        $slugs = $this->roleSlugs($user);

        foreach (array_keys(self::ROLE_PRIORITY) as $role) {
            if (in_array($role, $slugs, true)) {
                return $role;
            }
        }

        return 'employee';
    }

    public function routeNameFor(string $role): string
    {
        return self::ROLE_PRIORITY[$role]['route'] ?? self::ROLE_PRIORITY['employee']['route'];
    }

    public function viewFor(string $role): string
    {
        return self::ROLE_PRIORITY[$role]['view'] ?? self::ROLE_PRIORITY['employee']['view'];
    }

    public function canViewRole($user, string $role): bool
    {
        if ($role === 'employee') {
            return true;
        }

        $slugs = $this->roleSlugs($user);

        return in_array('super_admin', $slugs, true)
            || in_array($role, $slugs, true);
    }

    public function roleSlugs($user): array
    {
        if (! $user) {
            return [];
        }

        $slugs = [];

        if (method_exists($user, 'roles') && $this->tableExists('user_roles')) {
            try {
                $slugs = array_merge($slugs, $user->roles()->pluck('slug')->filter()->all());
            } catch (\Throwable $e) {
                // Keep dashboard resilient if a relationship is temporarily unavailable.
            }
        }

        foreach (['system_role_id', 'role_id'] as $column) {
            if (! empty($user->{$column}) && $this->tableExists('roles')) {
                $slug = DB::table('roles')->where('id', $user->{$column})->value('slug');

                if ($slug) {
                    $slugs[] = $slug;
                }
            }
        }

        return array_values(array_unique(array_filter($slugs)));
    }

    private function baseDashboardPayload(string $title, string $subtitle = ''): array
    {
        return [
            'meta' => [
                'title' => $title,
                'subtitle' => $subtitle,
                'current_date' => now(config('app.timezone', 'Asia/Kolkata'))->format('l, d M Y h:i A'),
                'user_name' => '', // Will be overridden
            ],
            'cards' => [],
            'quick_actions' => [],
            'charts' => [],
            'recent_activities' => [],
        ];
    }

    public function dashboardData(string $role, $user): array
    {
        $role = isset(self::ROLE_PRIORITY[$role]) ? $role : 'employee';

        $rolePayloadMethods = [
            'admin' => 'adminDashboardData',
            'finance_admin' => 'financeAdminDashboardData',
            'project_admin' => 'projectAdminDashboardData',
            'operations_admin' => 'operationsAdminDashboardData',
            'custom_admin' => 'customAdminDashboardData',
            'manager' => 'managerDashboardData',
        ];

        if (isset($rolePayloadMethods[$role])) {
            return $this->{$rolePayloadMethods[$role]}($user);
        }
        
        $base = $this->baseDashboardPayload(self::ROLE_PRIORITY[$role]['title'], 'HRMS Dashboard');
        $base['meta']['user_name'] = $user->name ?? 'User';

        $data = array_merge($base, [
            'role' => $role,
            'role_title' => self::ROLE_PRIORITY[$role]['title'],
            'user_name' => $user->name ?? 'User',
            'today_label' => now()->format('d M Y'),
            'charts' => [
                'daily' => [],
                'monthly' => $this->monthlyAttendanceTrend(),
                'departments' => $this->departmentEmployeesChart(),
            ],
            'recent_activities' => $this->recentActivities(),
            'empty_message' => null,
        ]);

        if ($role === 'employee') {
            return array_merge($data, $this->employeeData($user));
        }

        if ($role === 'finance_admin') {
            return array_merge($data, $this->financeData());
        }

        if ($role === 'project_admin') {
            return array_merge($data, $this->projectData($user));
        }

        if ($role === 'operations_admin') {
            return array_merge($data, $this->operationsData());
        }

        if ($role === 'custom_admin') {
            return array_merge($data, $this->customAdminData($user));
        }

        if ($role === 'super_admin') {
            $finalData = array_merge($data, $this->superAdminData());
        } else {
            $finalData = array_merge($data, $this->hrmsAdminData($role));
        }

        Log::info('Super Admin Dashboard Payload', [
            'view' => 'super-admin',
            'cards' => array_keys($finalData['cards'] ?? []),
            'quick_actions' => count($finalData['quick_actions'] ?? []),
            'live_attendance' => count($finalData['live_attendance'] ?? []),
            'recent_activities' => count($finalData['recent_activities'] ?? []),
        ]);

        return $finalData;
    }

    private function hrmsAdminData(string $role): array
    {
        $employee = $this->employeeStats();
        $attendance = $this->attendanceStatsForDate(today()->toDateString(), null, $employee['active'] ?? 0);
        $leave = $this->leaveStats();
        $payroll = $this->payrollStats();
        $documents = $this->documentStats();
        $announcements = $this->announcementStats();

        $cards = [
            'present_today' => 0,
            'absent_today' => 0,
            'late_today' => 0,
            'early_logout' => 0,
            'half_day' => 0,
            'lwp_count' => 0,
            'punch_blocked' => 0,
            'pending_hr' => 0,
            
            'total_employees' => 0,
            'active_employees' => 0,
            'pending_profiles' => 0,
            'rejected_profiles' => 0,
            'interns' => 0,
            'probation' => 0,
            'permanent' => 0,
            'exit_process' => 0,
        ];
        
        // Employee logic
        if (Schema::hasTable('employees_new')) {
            $cards['total_employees'] = DB::table('employees_new')->count();
            
            if (Schema::hasColumn('employees_new', 'status')) {
                $cards['active_employees'] = DB::table('employees_new')->where('status', 'active')->count();
                $cards['exit_process'] = DB::table('employees_new')->whereIn('status', ['exit_process', 'exited', 'terminated'])->count();
            } else {
                $cards['active_employees'] = $cards['total_employees'];
            }
            
            if (Schema::hasColumn('employees_new', 'employee_stage')) {
                $cards['interns'] = DB::table('employees_new')->where('employee_stage', 'internship')->count();
                $cards['probation'] = DB::table('employees_new')->where('employee_stage', 'probation')->count();
                $cards['permanent'] = DB::table('employees_new')->where('employee_stage', 'permanent')->count();
            } elseif (Schema::hasColumn('employees_new', 'employment_type')) {
                $cards['interns'] = DB::table('employees_new')->whereIn('employment_type', ['internship', 'intern'])->count();
                $cards['probation'] = DB::table('employees_new')->where('employment_type', 'probation')->count();
                $cards['permanent'] = DB::table('employees_new')->where('employment_type', 'permanent')->count();
            }
        }
        
        if (Schema::hasTable('employee_profiles') && Schema::hasColumn('employee_profiles', 'profile_status')) {
            $cards['pending_profiles'] = DB::table('employee_profiles')->whereIn('profile_status', ['pending', 'submitted'])->count();
            $cards['rejected_profiles'] = DB::table('employee_profiles')->where('profile_status', 'rejected')->count();
        } elseif (Schema::hasTable('employee_profiles') && Schema::hasColumn('employee_profiles', 'is_profile_completed')) {
            $cards['pending_profiles'] = DB::table('employee_profiles')->where('is_profile_completed', 0)->count();
        }
        
        // Attendance logic
        if (Schema::hasTable('attendances') && Schema::hasTable('attendance_types')) {
            $today = Carbon::today(config('app.timezone', 'Asia/Kolkata'))->toDateString();
            $attRows = DB::table('attendances as a')->join('attendance_types as t', 't.id', '=', 'a.attendance_type_id')->whereDate('a.attendance_date', $today)->select('t.code', 'a.is_late', 'a.is_early_out', 'a.is_half_day', 'a.is_blocked', 'a.is_lwp')->get();
            
            $cards['present_today'] = $attRows->whereIn('code', ['present', 'late', 'early_leave'])->count();
            $cards['absent_today'] = $attRows->where('code', 'absent')->count();
            
            $cards['late_today'] = $attRows->filter(function($r) { return $r->code === 'late' || (isset($r->is_late) && $r->is_late == 1); })->count();
            $cards['early_logout'] = $attRows->filter(function($r) { return $r->code === 'early_leave' || (isset($r->is_early_out) && $r->is_early_out == 1); })->count();
            $cards['half_day'] = $attRows->filter(function($r) { return $r->code === 'half_day' || (isset($r->is_half_day) && $r->is_half_day == 1); })->count();
            $cards['lwp_count'] = $attRows->filter(function($r) { return $r->code === 'lwp' || (isset($r->is_lwp) && $r->is_lwp == 1); })->count();
            $cards['punch_blocked'] = $attRows->filter(function($r) { return $r->code === 'punch_blocked' || (isset($r->is_blocked) && $r->is_blocked == 1); })->count();
            $cards['pending_hr'] = $attRows->where('code', 'pending_hr')->count();
        }

        if ($role === 'hr_admin') {
            $cards = [
                $this->card('Employee Total', $employee['total'] ?? 0, 'fas fa-users', 'HRMS employee base'),
                $this->card('Active Employees', $employee['active'] ?? 0, 'fas fa-user-check', 'Active workforce'),
                $this->card('Pending Onboarding', $cards['pending_profiles'], 'fas fa-id-badge', 'Profiles needing review'),
                $this->card('Probation Ending Soon', $this->probationEndingSoonCount(), 'fas fa-hourglass-half', 'Next 30 days'),
                $this->card('Leave Pending', $leave['pending'] ?? 0, 'fas fa-calendar-alt', 'Approvals waiting'),
                $this->card('Documents Pending', $documents['pending'] ?? 0, 'fas fa-folder-open', 'Document approval queue'),
                $this->card('Announcements', $announcements['total'] ?? 0, 'fas fa-bullhorn', 'Communication stats'),
            ];
            array_splice($cards, 4, 0, $this->attendanceAdminCards($attendance));
        }

        $liveAttendance = $this->buildLiveAttendance();
        $actionRequired = $this->buildActionRequired();
        $charts = $this->buildCharts();

        return [
            'cards' => $cards,
            'attendance_today' => $attendance,
            'employee_lifecycle' => $employee['lifecycle'] ?? [],
            'quick_actions' => $this->quickActionsFor($role),
            'payroll' => $payroll,
            'documents' => $documents,
            'leave' => $leave,
            'action_required' => $actionRequired,
            'live_attendance' => $liveAttendance,
            'system_health' => [],
            'announcements' => $announcements,
            'charts' => $charts,
        ];
    }

    private function financeData(): array
    {
        $payroll = $this->payrollStats();
        $attendance = $this->attendanceStatsForDate(today()->toDateString());

        return [
            'cards' => [
                $this->card('Payroll Month Status', $payroll['current_status'], 'fas fa-money-check-alt', 'Current month payroll state'),
                $this->card('Salary Estimate', $this->money($payroll['salary_estimate']), 'fas fa-rupee-sign', 'Active employee salary estimate'),
                $this->card('Payslips Generated', $payroll['payslips_generated'], 'fas fa-file-pdf', 'Current month payslips'),
                $this->card('FNF Pending', $payroll['fnf_pending'], 'fas fa-user-minus', 'Exit settlement estimate'),
                $this->card('Claims Pending', $payroll['claims_pending'], 'fas fa-receipt', 'Bonus/incentive or claims pending'),
                $this->card('Salary Structures', $payroll['salary_structures'], 'fas fa-layer-group', 'Configured structures'),
                $this->card('Attendance Ready', $attendance['present'] + $attendance['half_day'], 'fas fa-calendar-check', 'Today payable presence'),
                $this->card('Pending HR Attendance', $attendance['pending_hr'], 'fas fa-exclamation-circle', 'May affect payroll readiness'),
            ],
            'payroll' => $payroll,
            'attendance_today' => $attendance,
            'quick_actions' => $this->quickActionsFor('finance_admin'),
        ];
    }

    public function adminDashboardData($user = null): array
    {
        $employee = $this->employeeStats();
        $attendance = $this->attendanceStatsForEmployees(today()->toDateString());
        $leave = $this->leaveStats();
        $documents = $this->documentStats();
        $announcements = $this->announcementStats();
        $pendingProfiles = (int) data_get($employee, 'lifecycle.pending_profiles', 0);

        return $this->roleDashboardPayload('admin', $user, [
            'subtitle' => 'General HR monitoring and limited HR operations.',
            'cards' => [
                $this->card('Total Employees', $employee['total'] ?? 0, 'fas fa-users', 'All employee records'),
                $this->card('Present Today', $attendance['present'], 'fas fa-user-check', 'Marked present today'),
                $this->card('Pending Leave Requests', $leave['pending'] ?? 0, 'fas fa-calendar-times', 'Awaiting approval'),
                $this->card('Pending Documents', $documents['pending'] ?? 0, 'fas fa-file-signature', 'Verification required'),
                $this->card('Active Announcements', $announcements['active'] ?? 0, 'fas fa-bullhorn', 'Visible communications'),
                $this->card('Profiles Pending', $pendingProfiles, 'fas fa-id-card', 'Profile completion or review'),
            ],
            'quick_actions' => $this->dashboardQuickActions([
                ['Employee Directory', 'fas fa-address-book', ['hrms.employees.index']],
                ['Attendance Overview', 'fas fa-calendar-check', ['attendances.index', 'attendances.monthly-report']],
                ['Leave Overview', 'fas fa-calendar-alt', ['leave-approvals.index', 'hrms.leave.dashboard']],
                ['Documents', 'fas fa-folder-open', ['documents.hr.index', 'documents.dashboard']],
                ['Announcements', 'fas fa-bullhorn', ['announcements.index']],
                ['Reports', 'fas fa-chart-bar', ['attendances.monthly-report', 'hrms.attendance.monthly_summary.index']],
            ]),
            'action_required' => $this->dashboardActionItems([
                ['Pending Leave Requests', 'Employee leave requests waiting for review', $leave['pending'] ?? 0, 'fas fa-calendar-times', 'leave-approvals.index'],
                ['Pending Documents', 'Employee documents waiting for verification', $documents['pending'] ?? 0, 'fas fa-file-signature', 'documents.hr.index'],
                ['Profiles Pending', 'Profiles needing completion or approval', $pendingProfiles, 'fas fa-id-card', 'hrms.employees.pending_profiles'],
            ]),
            'recent_activities' => $this->buildRecentActivities(),
            'tables' => [
                'attendance_snapshot' => $this->dashboardTable('Today Attendance Snapshot', 'Latest attendance records for today', 'fas fa-clock', $this->todayAttendanceRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'code', 'label' => 'Code'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'punch_in', 'label' => 'Punch In'],
                    ['key' => 'punch_out', 'label' => 'Punch Out'],
                ], 'No attendance records for today.'),
                'pending_leaves' => $this->dashboardTable('Pending Leave Requests', 'Requests awaiting action', 'fas fa-calendar-times', $this->pendingLeaveRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'period', 'label' => 'Period'],
                    ['key' => 'days', 'label' => 'Days'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No pending leave requests.'),
                'pending_documents' => $this->dashboardTable('Pending Documents', 'Documents awaiting verification', 'fas fa-file-signature', $this->pendingDocumentRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'document', 'label' => 'Document'],
                    ['key' => 'uploaded', 'label' => 'Uploaded'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No pending documents.'),
                'recent_announcements' => $this->dashboardTable('Recent Announcements', 'Latest published communications', 'fas fa-bullhorn', $this->recentAnnouncementRows(), [
                    ['key' => 'title', 'label' => 'Title'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'published', 'label' => 'Published'],
                ], 'No announcements found.'),
            ],
            'charts' => [
                'monthly_attendance' => $this->monthlyAttendanceTrend(),
                'departments' => $this->departmentEmployeesChart(),
            ],
        ]);
    }

    public function financeAdminDashboardData($user = null): array
    {
        $payroll = $this->enterprisePayrollStats();

        return $this->roleDashboardPayload('finance_admin', $user, [
            'subtitle' => 'Payroll, salary, reimbursement and payslip operations.',
            'cards' => [
                $this->card('Current Month Gross Payroll', $this->money($payroll['gross_payroll']), 'fas fa-coins', 'Enterprise payroll gross total'),
                $this->card('Net Payroll', $this->money($payroll['net_payroll']), 'fas fa-money-check-alt', 'Enterprise payroll net total'),
                $this->card('Pending Payroll Runs', $payroll['pending_runs'], 'fas fa-hourglass-half', 'Draft or pending runs'),
                $this->card('Payslips Generated', $payroll['payslips_generated'], 'fas fa-file-invoice-dollar', 'Current month payslips'),
                $this->card('Pending Reimbursements', $payroll['pending_reimbursements'], 'fas fa-receipt', 'Claims waiting for action'),
                $this->card('Employees Without Salary Structure', $payroll['missing_structure'], 'fas fa-user-slash', 'Active employees missing setup'),
            ],
            'quick_actions' => $this->dashboardQuickActions([
                ['Enterprise Payroll', 'fas fa-chart-pie', ['enterprise-payroll.dashboard']],
                ['Salary Structures', 'fas fa-layer-group', ['enterprise-payroll.salary-structures.index']],
                ['Payroll Runs', 'fas fa-play-circle', ['enterprise-payroll.runs.index']],
                ['Payslips', 'fas fa-file-invoice-dollar', ['enterprise-payroll.payslips.index']],
                ['Reimbursements', 'fas fa-receipt', ['enterprise-payroll.reimbursements.index']],
                ['Payroll Reports', 'fas fa-chart-bar', ['enterprise-payroll.reports.index']],
            ]),
            'action_required' => $this->dashboardActionItems([
                ['Pending Payroll Runs', 'Payroll runs still waiting for processing or approval', $payroll['pending_runs'], 'fas fa-hourglass-half', 'enterprise-payroll.runs.index'],
                ['Pending Reimbursements', 'Employee reimbursement claims awaiting review', $payroll['pending_reimbursements'], 'fas fa-receipt', 'enterprise-payroll.reimbursements.index'],
                ['Missing Salary Structures', 'Employees cannot be processed until salary is configured', $payroll['missing_structure'], 'fas fa-user-slash', 'enterprise-payroll.salary-structures.index'],
            ]),
            'recent_activities' => $this->payrollActivityRows(),
            'tables' => [
                'latest_payroll_runs' => $this->dashboardTable('Latest Payroll Runs', 'Recent enterprise payroll cycles', 'fas fa-play-circle', $this->latestPayrollRunRows(), [
                    ['key' => 'period', 'label' => 'Period'],
                    ['key' => 'employees', 'label' => 'Employees'],
                    ['key' => 'gross', 'label' => 'Gross'],
                    ['key' => 'net', 'label' => 'Net'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No enterprise payroll runs found.'),
                'pending_reimbursements' => $this->dashboardTable('Pending Reimbursements', 'Claims awaiting finance action', 'fas fa-receipt', $this->pendingReimbursementRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'title', 'label' => 'Title'],
                    ['key' => 'amount', 'label' => 'Amount'],
                    ['key' => 'claim_date', 'label' => 'Claim Date'],
                ], 'No pending reimbursements.'),
                'recent_payslips' => $this->dashboardTable('Recent Payslips', 'Latest generated payslips', 'fas fa-file-invoice-dollar', $this->recentPayslipRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'period', 'label' => 'Period'],
                    ['key' => 'payslip_no', 'label' => 'Payslip No.'],
                    ['key' => 'generated', 'label' => 'Generated'],
                ], 'No payslips generated yet.'),
                'missing_salary_structures' => $this->dashboardTable('Salary Structure Missing Employees', 'Active employees without enterprise salary setup', 'fas fa-user-slash', $this->missingSalaryStructureRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'code', 'label' => 'Code'],
                    ['key' => 'department', 'label' => 'Department'],
                ], 'All active employees have salary structures.'),
            ],
            'charts' => [
                'payroll_trend' => $payroll['monthly_trend'],
            ],
        ]);
    }

    public function projectAdminDashboardData($user = null): array
    {
        $project = $this->projectDashboardStats($user);

        return $this->roleDashboardPayload('project_admin', $user, [
            'subtitle' => 'Project, task and team work log monitoring.',
            'empty_message' => $project['has_project_setup'] ? null : 'Project tables or project routes are not configured yet. Available task and attendance data is shown where present.',
            'cards' => [
                $this->card('Active Projects', $project['active_projects'], 'fas fa-project-diagram', 'Currently active projects'),
                $this->card('Open Tasks', $project['open_tasks'], 'fas fa-tasks', 'Pending task workload'),
                $this->card('Completed Tasks', $project['completed_tasks'], 'fas fa-check-double', 'Completed tasks'),
                $this->card('Team Members', $project['team_members'], 'fas fa-users', 'Employees in directory'),
                $this->card('Pending Work Logs', $project['pending_work_logs'], 'fas fa-clipboard-list', 'Work logs awaiting review if configured'),
                $this->card('Today Attendance', $project['today_attendance'], 'fas fa-user-check', 'Present today'),
            ],
            'quick_actions' => $this->dashboardQuickActions([
                ['Projects', 'fas fa-project-diagram', ['project_management.projects.index', 'module.project-mgmt']],
                ['Tasks', 'fas fa-tasks', ['project_management.tasks.index']],
                ['Team Work Logs', 'fas fa-clipboard-list', ['attendances.daily', 'hrms.attendance.monthly_summary.index']],
                ['Employee Directory', 'fas fa-address-book', ['hrms.employees.index']],
                ['Attendance Summary', 'fas fa-calendar-check', ['hrms.attendance.monthly_summary.index', 'attendances.monthly-report']],
                ['Reports', 'fas fa-chart-bar', ['project_management.tasks.export', 'attendances.monthly-report']],
            ]),
            'action_required' => $this->dashboardActionItems([
                ['Open Tasks', 'Tasks that are not completed', $project['open_tasks'], 'fas fa-tasks', 'project_management.tasks.index'],
                ['Pending Work Logs', 'Work logs awaiting review if configured', $project['pending_work_logs'], 'fas fa-clipboard-list', 'attendances.daily'],
            ]),
            'recent_activities' => $this->workLogActivityRows(),
            'tables' => [
                'project_progress' => $this->dashboardTable('Project Progress', 'Progress data from project tables when available', 'fas fa-project-diagram', $this->projectProgressRows(), [
                    ['key' => 'project', 'label' => 'Project'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'progress', 'label' => 'Progress'],
                    ['key' => 'deadline', 'label' => 'Deadline'],
                ], 'Project tables are not configured.'),
                'recent_tasks' => $this->dashboardTable('Recent Tasks', 'Latest task records', 'fas fa-tasks', $this->recentTaskRows(), [
                    ['key' => 'title', 'label' => 'Task'],
                    ['key' => 'employee', 'label' => 'Owner'],
                    ['key' => 'due_date', 'label' => 'Due Date'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No tasks found.'),
                'team_work_logs' => $this->dashboardTable('Team Work Logs', 'Latest submitted work logs', 'fas fa-clipboard-list', $this->workLogRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'summary', 'label' => 'Summary'],
                ], 'No work logs found.'),
                'project_deadlines' => $this->dashboardTable('Project Deadlines', 'Upcoming project or task deadlines', 'fas fa-calendar-day', $this->deadlineRows(), [
                    ['key' => 'item', 'label' => 'Item'],
                    ['key' => 'owner', 'label' => 'Owner'],
                    ['key' => 'deadline', 'label' => 'Deadline'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No upcoming deadlines found.'),
            ],
            'charts' => [],
        ]);
    }

    public function operationsAdminDashboardData($user = null): array
    {
        $attendance = $this->attendanceStatsForEmployees(today()->toDateString());

        return $this->roleDashboardPayload('operations_admin', $user, [
            'subtitle' => 'Daily attendance, punch block, asset and holiday monitoring.',
            'cards' => [
                $this->card('Present Today', $attendance['present'], 'fas fa-user-check', 'Employees present today'),
                $this->card('Punch Blocked', $attendance['punch_blocked'], 'fas fa-user-lock', 'Blocked punch records'),
                $this->card('Late Employees', $attendance['late'], 'fas fa-clock', 'Late arrivals today'),
                $this->card('Early Logout', $attendance['early_out'], 'fas fa-door-open', 'Early exits today'),
                $this->card('Assets Assigned', $this->assetsAssignedCount(), 'fas fa-laptop', 'Active asset allocations'),
                $this->card('Upcoming Holidays', $this->upcomingHolidayCount(), 'fas fa-calendar-day', 'Next 30 days'),
            ],
            'quick_actions' => $this->dashboardQuickActions([
                ['Attendance Live', 'fas fa-broadcast-tower', ['attendances.daily', 'attendances.index']],
                ['Punch Blocked', 'fas fa-user-lock', ['attendances.pending-approval']],
                ['Week Off / Holidays', 'fas fa-calendar-day', ['hrms.weekoff_rules.index', 'hrms.holidays.index']],
                ['Assets', 'fas fa-laptop', ['hrms.assets.index']],
                ['Employee Directory', 'fas fa-address-book', ['hrms.employees.index']],
                ['Announcements', 'fas fa-bullhorn', ['announcements.index']],
                ['Reports', 'fas fa-chart-bar', ['attendances.monthly-report', 'hrms.attendance.monthly_summary.index']],
            ]),
            'action_required' => $this->dashboardActionItems([
                ['Punch Blocked', 'Blocked punches need operations review', $attendance['punch_blocked'], 'fas fa-user-lock', 'attendances.pending-approval'],
                ['Late Employees', 'Employees marked late today', $attendance['late'], 'fas fa-clock', 'attendances.daily'],
                ['Early Logout', 'Employees with early logout today', $attendance['early_out'], 'fas fa-door-open', 'attendances.daily'],
            ]),
            'recent_activities' => $this->buildRecentActivities(),
            'tables' => [
                'live_attendance' => $this->dashboardTable('Live Attendance Table', 'Today attendance activity', 'fas fa-broadcast-tower', $this->todayAttendanceRows(10), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'code', 'label' => 'Code'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'punch_in', 'label' => 'Punch In'],
                    ['key' => 'punch_out', 'label' => 'Punch Out'],
                ], 'No live attendance records found.'),
                'punch_blocked' => $this->dashboardTable('Punch Blocked Employees', 'Employees blocked from punching', 'fas fa-user-lock', $this->punchBlockedRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'code', 'label' => 'Code'],
                    ['key' => 'reason', 'label' => 'Reason'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No punch blocked employees today.'),
                'asset_summary' => $this->dashboardTable('Asset Allocation Summary', 'Latest active asset assignments', 'fas fa-laptop', $this->assetRows(), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'asset', 'label' => 'Asset'],
                    ['key' => 'assigned', 'label' => 'Assigned'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No active asset allocations found.'),
                'holiday_summary' => $this->dashboardTable('Week Off / Holiday Summary', 'Upcoming holidays and configured days off', 'fas fa-calendar-day', $this->holidayRows(), [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'type', 'label' => 'Type'],
                ], 'No upcoming holidays found.'),
            ],
            'charts' => [
                'monthly_attendance' => $this->monthlyAttendanceTrend(),
            ],
        ]);
    }

    public function customAdminDashboardData($user = null): array
    {
        $modules = $this->assignedModules($user);
        $cards = [];
        foreach ($modules as $module) {
            $metric = $this->moduleMetric($module['name'], $module['count']);
            $cards[] = $this->card($metric['label'], $metric['value'], $module['icon'], $metric['subtitle']);
        }

        return $this->roleDashboardPayload('custom_admin', $user, [
            'subtitle' => 'Permission-based administration dashboard.',
            'empty_message' => empty($cards) ? 'No module access assigned.' : null,
            'cards' => $cards,
            'quick_actions' => $this->customAdminQuickActions($user),
            'action_required' => [],
            'recent_activities' => $this->buildRecentActivities(),
            'tables' => [
                'assigned_modules' => $this->dashboardTable('Assigned Modules', 'Modules visible through role menu access', 'fas fa-shield-alt', $modules, [
                    ['key' => 'name', 'label' => 'Module'],
                    ['key' => 'description', 'label' => 'Access'],
                    ['key' => 'count', 'label' => 'Menu Items'],
                ], 'No assigned modules found.'),
            ],
            'charts' => [],
        ]);
    }

    public function managerDashboardData($user = null): array
    {
        $teamIds = $this->managerTeamEmployeeIds($user);
        $attendance = $this->attendanceStatsForEmployees(today()->toDateString(), $teamIds);
        $teamCount = count($teamIds);
        $pendingLeaves = $this->pendingLeaveCount($teamIds);
        $pendingDocuments = $this->pendingDocumentCount($teamIds);

        return $this->roleDashboardPayload('manager', $user, [
            'subtitle' => 'Team-level attendance, leave and document monitoring.',
            'empty_message' => $teamCount === 0 ? 'No team employees are assigned to this manager.' : null,
            'cards' => [
                $this->card('Team Members', $teamCount, 'fas fa-users', 'Direct reporting employees'),
                $this->card('Present Today', $attendance['present'], 'fas fa-user-check', 'Team present today'),
                $this->card('Team On Leave', $attendance['leave'], 'fas fa-plane-departure', 'Team leave today'),
                $this->card('Pending Leave Approvals', $pendingLeaves, 'fas fa-calendar-check', 'Team requests awaiting approval'),
                $this->card('Late Team Members', $attendance['late'], 'fas fa-clock', 'Team late arrivals'),
                $this->card('Pending Team Documents', $pendingDocuments, 'fas fa-folder-open', 'Team document verification'),
            ],
            'quick_actions' => $this->dashboardQuickActions([
                ['My Team', 'fas fa-users', ['hrms.employees.index']],
                ['Team Attendance', 'fas fa-calendar-check', ['attendances.monthly-report', 'attendances.daily']],
                ['Team Leave Calendar', 'fas fa-calendar-week', ['hrms.leave.team_calendar.index']],
                ['Leave Approvals', 'fas fa-check-double', ['leave-approvals.index']],
                ['Team Documents View', 'fas fa-folder-open', ['documents.employee.index', 'documents.hr.index']],
                ['Announcements', 'fas fa-bullhorn', ['announcements.index', 'employee.announcements.index']],
                ['Reports', 'fas fa-chart-bar', ['attendances.monthly-report']],
            ]),
            'action_required' => $this->dashboardActionItems([
                ['Pending Leave Approvals', 'Team leave requests waiting for manager action', $pendingLeaves, 'fas fa-calendar-check', 'leave-approvals.index'],
                ['Pending Team Documents', 'Team documents waiting for verification', $pendingDocuments, 'fas fa-folder-open', 'documents.employee.index'],
                ['Late Team Members', 'Team members marked late today', $attendance['late'], 'fas fa-clock', 'attendances.daily'],
            ]),
            'recent_activities' => $this->teamActivityRows($teamIds),
            'tables' => [
                'team_attendance' => $this->dashboardTable('Team Attendance Today', 'Only assigned team employees are shown', 'fas fa-calendar-check', $this->todayAttendanceRows(10, $teamIds), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'code', 'label' => 'Code'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'punch_in', 'label' => 'Punch In'],
                    ['key' => 'punch_out', 'label' => 'Punch Out'],
                ], 'No team attendance records for today.'),
                'team_leave_calendar' => $this->dashboardTable('Team Leave Calendar Preview', 'Upcoming approved or pending team leaves', 'fas fa-calendar-week', $this->teamLeaveRows($teamIds), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'period', 'label' => 'Period'],
                    ['key' => 'days', 'label' => 'Days'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No team leave records found.'),
                'pending_approvals' => $this->dashboardTable('Pending Approvals', 'Manager action queue', 'fas fa-check-double', $this->pendingLeaveRows(8, $teamIds), [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'period', 'label' => 'Period'],
                    ['key' => 'days', 'label' => 'Days'],
                    ['key' => 'status', 'label' => 'Status'],
                ], 'No pending team approvals.'),
                'recent_team_activities' => $this->dashboardTable('Recent Team Activities', 'Latest team attendance and work log records', 'fas fa-history', $this->teamActivityRows($teamIds), [
                    ['key' => 'title', 'label' => 'Activity'],
                    ['key' => 'description', 'label' => 'Description'],
                    ['key' => 'time', 'label' => 'Time'],
                ], 'No recent team activity.'),
            ],
            'charts' => [],
        ]);
    }

    private function roleDashboardPayload(string $role, $user, array $payload): array
    {
        $title = self::ROLE_PRIORITY[$role]['title'] ?? 'Dashboard';

        return [
            'meta' => [
                'title' => $title,
                'subtitle' => $payload['subtitle'] ?? 'Orbosis HRMS dashboard',
                'current_date' => Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('l, d M Y h:i A'),
                'user_name' => $user->name ?? 'User',
            ],
            'role' => $role,
            'role_title' => $title,
            'user_name' => $user->name ?? 'User',
            'today_label' => Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('d M Y'),
            'cards' => $payload['cards'] ?? [],
            'quick_actions' => $payload['quick_actions'] ?? [],
            'action_required' => $payload['action_required'] ?? [],
            'recent_activities' => $payload['recent_activities'] ?? [],
            'tables' => $payload['tables'] ?? [],
            'charts' => $payload['charts'] ?? [],
            'empty_message' => $payload['empty_message'] ?? null,
        ];
    }

    private function dashboardQuickActions(array $items): array
    {
        return collect($items)->map(function ($item) {
            [$title, $icon, $routes] = $item;
            foreach ($routes as $route) {
                if (Route::has($route)) {
                    return [
                        'title' => $title,
                        'icon' => $icon,
                        'route' => $route,
                        'url' => route($route),
                    ];
                }
            }

            return null;
        })->filter()->values()->all();
    }

    private function dashboardActionItems(array $items): array
    {
        return collect($items)->map(function ($item) {
            [$title, $subtitle, $count, $icon, $route] = $item;
            $count = (int) $count;
            if ($count <= 0) {
                return null;
            }

            return [
                'title' => $title,
                'subtitle' => $subtitle,
                'count' => $count,
                'icon' => $icon,
                'url' => Route::has($route) ? route($route) : null,
            ];
        })->filter()->values()->all();
    }

    private function dashboardTable(string $title, string $subtitle, string $icon, array $rows, array $columns, string $empty): array
    {
        return compact('title', 'subtitle', 'icon', 'rows', 'columns', 'empty');
    }

    private function attendanceStatsForEmployees($date, ?array $employeeIds = null): array
    {
        $empty = [
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
            'leave' => 0,
            'week_off' => 0,
            'pending_hr' => 0,
            'punch_blocked' => 0,
            'missed_punches' => 0,
            'late' => 0,
            'early_out' => 0,
            'wfo' => 0,
            'wfh' => 0,
        ];

        if (! $this->tableExists('attendances')) {
            return $empty;
        }

        if (is_array($employeeIds) && empty($employeeIds)) {
            return $empty;
        }

        $query = DB::table('attendances as a')->whereDate('a.attendance_date', $date);

        if (is_array($employeeIds)) {
            $query->whereIn('a.employee_id', $employeeIds);
        }

        if ($this->tableExists('attendance_types') && $this->columnExists('attendances', 'attendance_type_id')) {
            $rows = (clone $query)
                ->leftJoin('attendance_types as t', 't.id', '=', 'a.attendance_type_id')
                ->select('t.code', DB::raw('COUNT(*) as total'))
                ->groupBy('t.code')
                ->pluck('total', 'code')
                ->toArray();

            foreach (['present', 'absent', 'half_day', 'leave', 'week_off', 'pending_hr', 'punch_blocked'] as $code) {
                $empty[$code] = (int) ($rows[$code] ?? 0);
            }
        } elseif ($this->columnExists('attendances', 'attendance_status')) {
            $rows = (clone $query)
                ->select('a.attendance_status', DB::raw('COUNT(*) as total'))
                ->groupBy('a.attendance_status')
                ->pluck('total', 'attendance_status')
                ->toArray();

            foreach ($rows as $status => $total) {
                $key = strtolower((string) $status);
                if (array_key_exists($key, $empty)) {
                    $empty[$key] = (int) $total;
                }
            }
        }

        foreach (['is_late' => 'late', 'is_early_out' => 'early_out', 'is_blocked' => 'punch_blocked'] as $column => $key) {
            if ($this->columnExists('attendances', $column)) {
                $empty[$key] = max($empty[$key], (int) (clone $query)->where("a.$column", 1)->count());
            }
        }

        if ($this->columnExists('attendances', 'missed_punch')) {
            $empty['missed_punches'] = (int) (clone $query)->where('a.missed_punch', 1)->count();
        } elseif ($this->columnExists('attendances', 'is_missed_punch')) {
            $empty['missed_punches'] = (int) (clone $query)->where('a.is_missed_punch', 1)->count();
        }

        if ($this->columnExists('attendances', 'work_mode')) {
            $empty['wfo'] = (int) (clone $query)->where('a.work_mode', 'wfo')->count();
            $empty['wfh'] = (int) (clone $query)->where('a.work_mode', 'wfh')->count();
        }

        $activeCount = is_array($employeeIds) ? count($employeeIds) : $this->activeEmployeesQuery()->count();
        $marked = (int) (clone $query)->distinct('a.employee_id')->count('a.employee_id');
        $empty['absent'] += max(0, $activeCount - $marked);

        return $empty;
    }

    private function todayAttendanceRows(int $limit = 8, ?array $employeeIds = null): array
    {
        if (! $this->tableExists('attendances') || ! $this->tableExists('employees_new')) {
            return [];
        }

        if (is_array($employeeIds) && empty($employeeIds)) {
            return [];
        }

        $query = DB::table('attendances as a')
            ->join('employees_new as e', 'e.id', '=', 'a.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->whereDate('a.attendance_date', today()->toDateString());

        if (is_array($employeeIds)) {
            $query->whereIn('a.employee_id', $employeeIds);
        }

        if ($this->tableExists('attendance_types') && $this->columnExists('attendances', 'attendance_type_id')) {
            $query->leftJoin('attendance_types as t', 't.id', '=', 'a.attendance_type_id');
        }

        $select = [
            DB::raw("COALESCE(u.name, 'Employee') as employee"),
            DB::raw($this->columnExists('employees_new', 'employee_code') ? "COALESCE(e.employee_code, '-') as code" : "'-' as code"),
            DB::raw($this->tableExists('attendance_types') && $this->columnExists('attendances', 'attendance_type_id') ? "COALESCE(t.name, t.code, 'Marked') as status" : ($this->columnExists('attendances', 'attendance_status') ? "COALESCE(a.attendance_status, 'Marked') as status" : "'Marked' as status")),
            DB::raw($this->columnExists('attendances', 'punch_in_time') ? "COALESCE(a.punch_in_time, '-')" : "'-' as punch_in"),
            DB::raw($this->columnExists('attendances', 'punch_out_time') ? "COALESCE(a.punch_out_time, '-')" : "'-' as punch_out"),
        ];

        return $query->select($select)
            ->orderByDesc($this->columnExists('attendances', 'created_at') ? 'a.created_at' : 'a.id')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->rowToArray($row))
            ->all();
    }

    private function pendingLeaveRows(int $limit = 8, ?array $employeeIds = null): array
    {
        if ($this->tableExists('leave_requests')) {
            if (is_array($employeeIds) && empty($employeeIds)) {
                return [];
            }

            $query = DB::table('leave_requests as lr')
                ->leftJoin('employees_new as e', 'e.id', '=', 'lr.employee_id')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                ->whereRaw('LOWER(lr.status) = ?', ['pending']);

            if (is_array($employeeIds) && $this->columnExists('leave_requests', 'employee_id')) {
                $query->whereIn('lr.employee_id', $employeeIds);
            }

            return $query->select(
                DB::raw("COALESCE(u.name, 'Employee') as employee"),
                DB::raw($this->columnExists('leave_requests', 'start_date') && $this->columnExists('leave_requests', 'end_date') ? "CONCAT(lr.start_date, ' to ', lr.end_date) as period" : "'-' as period"),
                DB::raw($this->columnExists('leave_requests', 'total_days') ? 'lr.total_days as days' : "'-' as days"),
                'lr.status'
            )->orderByDesc('lr.id')->limit($limit)->get()->map(fn ($row) => $this->rowToArray($row))->all();
        }

        return [];
    }

    private function pendingLeaveCount(?array $employeeIds = null): int
    {
        if (! $this->tableExists('leave_requests') || ! $this->columnExists('leave_requests', 'status')) {
            return 0;
        }

        if (is_array($employeeIds) && empty($employeeIds)) {
            return 0;
        }

        $query = DB::table('leave_requests')->whereRaw('LOWER(status) = ?', ['pending']);
        if (is_array($employeeIds) && $this->columnExists('leave_requests', 'employee_id')) {
            $query->whereIn('employee_id', $employeeIds);
        }

        return (int) $query->count();
    }

    private function pendingDocumentRows(int $limit = 8, ?array $employeeIds = null): array
    {
        if (! $this->tableExists('employee_documents_new')) {
            return [];
        }

        if (is_array($employeeIds) && empty($employeeIds)) {
            return [];
        }

        $query = DB::table('employee_documents_new as d')
            ->leftJoin('employees_new as e', 'e.id', '=', 'd.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->where('d.verification_status', 'pending');

        if (is_array($employeeIds)) {
            $query->whereIn('d.employee_id', $employeeIds);
        }

        return $query->select(
            DB::raw("COALESCE(u.name, 'Employee') as employee"),
            DB::raw("COALESCE(d.title, 'Document') as document"),
            DB::raw($this->columnExists('employee_documents_new', 'uploaded_at') ? "COALESCE(DATE_FORMAT(d.uploaded_at, '%d %b %Y'), '-') as uploaded" : "COALESCE(DATE_FORMAT(d.created_at, '%d %b %Y'), '-') as uploaded"),
            'd.verification_status as status'
        )->orderByDesc('d.id')->limit($limit)->get()->map(fn ($row) => $this->rowToArray($row))->all();
    }

    private function pendingDocumentCount(?array $employeeIds = null): int
    {
        if (! $this->tableExists('employee_documents_new') || ! $this->columnExists('employee_documents_new', 'verification_status')) {
            return 0;
        }

        if (is_array($employeeIds) && empty($employeeIds)) {
            return 0;
        }

        $query = DB::table('employee_documents_new')->where('verification_status', 'pending');
        if (is_array($employeeIds)) {
            $query->whereIn('employee_id', $employeeIds);
        }

        return (int) $query->count();
    }

    private function recentAnnouncementRows(int $limit = 6): array
    {
        if (! $this->tableExists('announcements')) {
            return [];
        }

        return DB::table('announcements')
            ->select(
                'title',
                DB::raw($this->columnExists('announcements', 'status') ? "COALESCE(status, 'published') as status" : ($this->columnExists('announcements', 'is_active') ? "CASE WHEN is_active = 1 THEN 'active' ELSE 'inactive' END as status" : "'published' as status")),
                DB::raw("DATE_FORMAT(created_at, '%d %b %Y') as published")
            )
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->rowToArray($row))
            ->all();
    }

    private function enterprisePayrollStats(): array
    {
        $month = (int) now()->month;
        $year = (int) now()->year;
        $stats = [
            'gross_payroll' => 0,
            'net_payroll' => 0,
            'pending_runs' => 0,
            'payslips_generated' => 0,
            'pending_reimbursements' => 0,
            'missing_structure' => 0,
            'monthly_trend' => ['labels' => [], 'gross' => [], 'net' => []],
        ];

        if ($this->tableExists('enterprise_payrolls')) {
            $stats['gross_payroll'] = (float) DB::table('enterprise_payrolls')->where('month', $month)->where('year', $year)->sum('gross_salary');
            $stats['net_payroll'] = (float) DB::table('enterprise_payrolls')->where('month', $month)->where('year', $year)->sum('net_salary');
        }

        if ($this->tableExists('enterprise_payroll_runs')) {
            $stats['pending_runs'] = (int) DB::table('enterprise_payroll_runs')->whereIn('status', ['draft', 'pending', 'processing'])->count();
        }

        if ($this->tableExists('enterprise_payslips')) {
            $stats['payslips_generated'] = (int) DB::table('enterprise_payslips')->where('month', $month)->where('year', $year)->count();
        }

        if ($this->tableExists('enterprise_reimbursements')) {
            $stats['pending_reimbursements'] = (int) DB::table('enterprise_reimbursements')->where('status', 'pending')->count();
        }

        $stats['missing_structure'] = count($this->missingSalaryStructureRows(100000));

        if ($this->tableExists('enterprise_payroll_runs')) {
            $trend = DB::table('enterprise_payroll_runs')
                ->select('month', 'year', 'total_gross', 'total_net')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->limit(6)
                ->get()
                ->reverse();

            foreach ($trend as $row) {
                $stats['monthly_trend']['labels'][] = Carbon::createFromDate((int) $row->year, (int) $row->month, 1)->format('M Y');
                $stats['monthly_trend']['gross'][] = (float) $row->total_gross;
                $stats['monthly_trend']['net'][] = (float) $row->total_net;
            }
        }

        return $stats;
    }

    private function latestPayrollRunRows(int $limit = 6): array
    {
        if (! $this->tableExists('enterprise_payroll_runs')) {
            return [];
        }

        return DB::table('enterprise_payroll_runs')
            ->select('month', 'year', 'total_employees', 'total_gross', 'total_net', 'status')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'period' => Carbon::createFromDate((int) $row->year, (int) $row->month, 1)->format('M Y'),
                    'employees' => (int) $row->total_employees,
                    'gross' => $this->money($row->total_gross),
                    'net' => $this->money($row->total_net),
                    'status' => ucfirst((string) $row->status),
                ];
            })->all();
    }

    private function pendingReimbursementRows(int $limit = 6): array
    {
        if (! $this->tableExists('enterprise_reimbursements')) {
            return [];
        }

        return DB::table('enterprise_reimbursements as r')
            ->leftJoin('employees_new as e', 'e.id', '=', 'r.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->where('r.status', 'pending')
            ->select(DB::raw("COALESCE(u.name, 'Employee') as employee"), 'r.title', 'r.amount', 'r.claim_date')
            ->orderByDesc('r.claim_date')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'employee' => $row->employee,
                'title' => $row->title,
                'amount' => $this->money($row->amount),
                'claim_date' => $row->claim_date ? Carbon::parse($row->claim_date)->format('d M Y') : '-',
            ])->all();
    }

    private function recentPayslipRows(int $limit = 6): array
    {
        if (! $this->tableExists('enterprise_payslips')) {
            return [];
        }

        return DB::table('enterprise_payslips as p')
            ->leftJoin('employees_new as e', 'e.id', '=', 'p.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->select(DB::raw("COALESCE(u.name, 'Employee') as employee"), 'p.month', 'p.year', 'p.payslip_no', 'p.generated_at')
            ->orderByDesc('p.generated_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'employee' => $row->employee,
                'period' => Carbon::createFromDate((int) $row->year, (int) $row->month, 1)->format('M Y'),
                'payslip_no' => $row->payslip_no,
                'generated' => $row->generated_at ? Carbon::parse($row->generated_at)->format('d M Y') : '-',
            ])->all();
    }

    private function missingSalaryStructureRows(int $limit = 8): array
    {
        if (! $this->tableExists('employees_new') || ! $this->tableExists('enterprise_salary_structures')) {
            return [];
        }

        $query = DB::table('employees_new as e')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoin('enterprise_salary_structures as s', function ($join) {
                $join->on('s.employee_id', '=', 'e.id')->where('s.status', '=', 'active');
            })
            ->whereNull('s.id');

        if ($this->columnExists('employees_new', 'employment_status')) {
            $query->where('e.employment_status', 'active');
        }

        if ($this->columnExists('employees_new', 'is_active')) {
            $query->where('e.is_active', 1);
        }

        if ($this->tableExists('departments') && $this->columnExists('employees_new', 'department_id')) {
            $query->leftJoin('departments as d', 'd.id', '=', 'e.department_id');
        }

        return $query->select(
            DB::raw("COALESCE(u.name, 'Employee') as employee"),
            DB::raw($this->columnExists('employees_new', 'employee_code') ? "COALESCE(e.employee_code, '-') as code" : "'-' as code"),
            DB::raw($this->tableExists('departments') && $this->columnExists('employees_new', 'department_id') ? "COALESCE(d.name, '-') as department" : "'-' as department")
        )->limit($limit)->get()->map(fn ($row) => $this->rowToArray($row))->all();
    }

    private function payrollActivityRows(): array
    {
        return collect($this->latestPayrollRunRows(3))->map(fn ($row) => [
            'title' => 'Payroll run',
            'description' => ($row['period'] ?? '-') . ' payroll is ' . strtolower($row['status'] ?? '-'),
            'time' => now(),
            'icon' => 'fas fa-money-check-alt',
        ])->values()->all();
    }

    private function projectDashboardStats($user): array
    {
        $tasks = $this->taskStats($user);
        $attendance = $this->attendanceStatsForEmployees(today()->toDateString());

        return [
            'active_projects' => $this->tableExists('projects') && $this->columnExists('projects', 'status') ? DB::table('projects')->where('status', 'active')->count() : 0,
            'open_tasks' => $tasks['open'] ?? 0,
            'completed_tasks' => $tasks['completed'] ?? 0,
            'team_members' => $this->tableExists('employees_new') ? $this->activeEmployeesQuery()->count() : 0,
            'pending_work_logs' => $this->tableExists('attendance_work_logs') && $this->columnExists('attendance_work_logs', 'status') ? DB::table('attendance_work_logs')->where('status', 'pending')->count() : 0,
            'today_attendance' => $attendance['present'],
            'has_project_setup' => $this->tableExists('projects') || $this->tableExists('taskmanagement'),
        ];
    }

    private function recentTaskRows(int $limit = 8): array
    {
        if (! $this->tableExists('taskmanagement')) {
            return [];
        }

        return DB::table('taskmanagement')
            ->select('title', 'employee_name', 'due_date', 'status')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'title' => $row->title,
                'employee' => $row->employee_name ?: '-',
                'due_date' => $row->due_date ? Carbon::parse($row->due_date)->format('d M Y') : '-',
                'status' => ucfirst((string) $row->status),
            ])->all();
    }

    private function projectProgressRows(): array
    {
        if (! $this->tableExists('projects')) {
            return [];
        }

        $select = [
            DB::raw($this->columnExists('projects', 'name') ? 'name as project' : "'Project' as project"),
            DB::raw($this->columnExists('projects', 'status') ? 'status' : "'active' as status"),
            DB::raw($this->columnExists('projects', 'progress') ? 'progress' : "'-' as progress"),
            DB::raw($this->columnExists('projects', 'deadline') ? 'deadline' : ($this->columnExists('projects', 'end_date') ? 'end_date as deadline' : "'-' as deadline")),
        ];

        return DB::table('projects')->select($select)->limit(8)->get()->map(fn ($row) => $this->rowToArray($row))->all();
    }

    private function workLogRows(int $limit = 8, ?array $employeeIds = null): array
    {
        if (! $this->tableExists('attendance_work_logs')) {
            return [];
        }

        if (is_array($employeeIds) && empty($employeeIds)) {
            return [];
        }

        $query = DB::table('attendance_work_logs as w')
            ->leftJoin('employees_new as e', 'e.id', '=', 'w.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id');

        if (is_array($employeeIds)) {
            $query->whereIn('w.employee_id', $employeeIds);
        }

        return $query->select(
            DB::raw("COALESCE(u.name, 'Employee') as employee"),
            DB::raw($this->columnExists('attendance_work_logs', 'work_date') ? "DATE_FORMAT(w.work_date, '%d %b %Y') as date" : "DATE_FORMAT(w.created_at, '%d %b %Y') as date"),
            DB::raw("COALESCE(w.work_summary, '-') as summary")
        )->orderByDesc('w.id')->limit($limit)->get()->map(fn ($row) => $this->rowToArray($row))->all();
    }

    private function workLogActivityRows(?array $employeeIds = null): array
    {
        return collect($this->workLogRows(5, $employeeIds))->map(fn ($row) => [
            'title' => 'Work log submitted',
            'description' => ($row['employee'] ?? 'Employee') . ': ' . ($row['summary'] ?? '-'),
            'time' => $row['date'] ?? now(),
            'icon' => 'fas fa-clipboard-list',
        ])->all();
    }

    private function deadlineRows(): array
    {
        return collect($this->recentTaskRows(8))->filter(fn ($row) => ! empty($row['due_date']) && $row['due_date'] !== '-')->map(fn ($row) => [
            'item' => $row['title'],
            'owner' => $row['employee'],
            'deadline' => $row['due_date'],
            'status' => $row['status'],
        ])->values()->all();
    }

    private function assetsAssignedCount(): int
    {
        if (! $this->tableExists('asset_allocations')) {
            return 0;
        }

        return (int) DB::table('asset_allocations')
            ->when($this->columnExists('asset_allocations', 'status'), fn ($query) => $query->whereIn('status', ['Active', 'active', 'assigned']))
            ->count();
    }

    private function assetRows(int $limit = 8): array
    {
        if (! $this->tableExists('asset_allocations')) {
            return [];
        }

        $query = DB::table('asset_allocations as a');
        if ($this->tableExists('employees_new')) {
            $query->leftJoin('employees_new as e', 'e.id', '=', 'a.employee_id')->leftJoin('users as u', 'u.id', '=', 'e.user_id');
        }

        return $query->select(
            DB::raw($this->tableExists('employees_new') ? "COALESCE(u.name, 'Employee') as employee" : "'Employee' as employee"),
            DB::raw($this->columnExists('asset_allocations', 'asset_type') ? "COALESCE(a.asset_type, '-') as asset" : "'-' as asset"),
            DB::raw($this->columnExists('asset_allocations', 'assigned_date') ? "COALESCE(DATE_FORMAT(a.assigned_date, '%d %b %Y'), '-') as assigned" : "'-' as assigned"),
            DB::raw($this->columnExists('asset_allocations', 'status') ? "COALESCE(a.status, '-') as status" : "'-' as status")
        )->orderByDesc('a.id')->limit($limit)->get()->map(fn ($row) => $this->rowToArray($row))->all();
    }

    private function upcomingHolidayCount(): int
    {
        if (! $this->tableExists('holidays')) {
            return 0;
        }

        $dateColumn = $this->columnExists('holidays', 'holiday_date') ? 'holiday_date' : ($this->columnExists('holidays', 'date') ? 'date' : null);
        if (! $dateColumn) {
            return 0;
        }

        return (int) DB::table('holidays')->whereBetween($dateColumn, [today()->toDateString(), today()->addDays(30)->toDateString()])->count();
    }

    private function holidayRows(int $limit = 8): array
    {
        if (! $this->tableExists('holidays')) {
            return [];
        }

        $dateColumn = $this->columnExists('holidays', 'holiday_date') ? 'holiday_date' : ($this->columnExists('holidays', 'date') ? 'date' : null);
        if (! $dateColumn) {
            return [];
        }

        return DB::table('holidays')
            ->select(
                DB::raw($this->columnExists('holidays', 'name') ? "COALESCE(name, 'Holiday') as name" : "'Holiday' as name"),
                DB::raw("DATE_FORMAT($dateColumn, '%d %b %Y') as date"),
                DB::raw($this->columnExists('holidays', 'type') ? "COALESCE(type, 'Holiday') as type" : "'Holiday' as type")
            )
            ->where($dateColumn, '>=', today()->toDateString())
            ->orderBy($dateColumn)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->rowToArray($row))
            ->all();
    }

    private function punchBlockedRows(int $limit = 8): array
    {
        if (! $this->tableExists('attendances') || ! $this->tableExists('employees_new')) {
            return [];
        }

        $query = DB::table('attendances as a')
            ->join('employees_new as e', 'e.id', '=', 'a.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->whereDate('a.attendance_date', today()->toDateString());

        if ($this->columnExists('attendances', 'is_blocked')) {
            $query->where('a.is_blocked', 1);
        } elseif ($this->columnExists('attendances', 'attendance_status')) {
            $query->where('a.attendance_status', 'punch_blocked');
        } else {
            return [];
        }

        return $query->select(
            DB::raw("COALESCE(u.name, 'Employee') as employee"),
            DB::raw($this->columnExists('employees_new', 'employee_code') ? "COALESCE(e.employee_code, '-') as code" : "'-' as code"),
            DB::raw($this->columnExists('attendances', 'blocked_reason') ? "COALESCE(a.blocked_reason, '-')" : "'-' as reason"),
            DB::raw($this->columnExists('attendances', 'is_admin_unlocked') ? "CASE WHEN a.is_admin_unlocked = 1 THEN 'Unlocked' ELSE 'Blocked' END as status" : "'Blocked' as status")
        )->limit($limit)->get()->map(fn ($row) => $this->rowToArray($row))->all();
    }

    private function customAdminQuickActions($user): array
    {
        $roleIds = $this->roleIds($user);
        if (empty($roleIds) || ! $this->tableExists('role_menu_access') || ! $this->tableExists('menus')) {
            return [];
        }

        return DB::table('menus as m')
            ->join('role_menu_access as rma', 'rma.menu_id', '=', 'm.id')
            ->whereIn('rma.role_id', $roleIds)
            ->where('m.is_active', 1)
            ->whereNotNull('m.route')
            ->orderBy('m.sort_order')
            ->select('m.name', 'm.icon', 'm.route')
            ->get()
            ->map(function ($menu) {
                if (! Route::has($menu->route)) {
                    return null;
                }

                return [
                    'title' => $menu->name,
                    'icon' => $menu->icon ?: 'fas fa-link',
                    'route' => $menu->route,
                    'url' => route($menu->route),
                ];
            })->filter()->take(6)->values()->all();
    }

    private function moduleMetric(string $name, int $fallback): array
    {
        $key = strtolower($name);
        if (str_contains($key, 'employee')) {
            return ['label' => 'Employees', 'value' => $this->tableExists('employees_new') ? $this->activeEmployeesQuery()->count() : 0, 'subtitle' => 'Accessible employee module'];
        }
        if (str_contains($key, 'attendance')) {
            $attendance = $this->attendanceStatsForEmployees(today()->toDateString());
            return ['label' => 'Attendance', 'value' => $attendance['present'], 'subtitle' => 'Present today'];
        }
        if (str_contains($key, 'leave')) {
            return ['label' => 'Leave', 'value' => $this->pendingLeaveCount(), 'subtitle' => 'Pending leave requests'];
        }
        if (str_contains($key, 'document')) {
            return ['label' => 'Documents', 'value' => $this->pendingDocumentCount(), 'subtitle' => 'Pending document verification'];
        }
        if (str_contains($key, 'payroll')) {
            $payroll = $this->enterprisePayrollStats();
            return ['label' => 'Payroll', 'value' => $payroll['payslips_generated'], 'subtitle' => 'Current month payslips'];
        }
        if (str_contains($key, 'announcement')) {
            $announcements = $this->announcementStats();
            return ['label' => 'Announcements', 'value' => $announcements['active'] ?? 0, 'subtitle' => 'Active announcements'];
        }

        return ['label' => $name, 'value' => $fallback, 'subtitle' => 'Assigned menu access'];
    }

    private function managerTeamEmployeeIds($user): array
    {
        $manager = $this->employeeForUser($user->id ?? null);
        if (! $manager || ! $this->tableExists('employees_new') || ! $this->columnExists('employees_new', 'reporting_manager_employee_id')) {
            return [];
        }

        return DB::table('employees_new')
            ->where('reporting_manager_employee_id', $manager->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function teamLeaveRows(array $employeeIds): array
    {
        if (empty($employeeIds) || ! $this->tableExists('leave_requests')) {
            return [];
        }

        return DB::table('leave_requests as lr')
            ->leftJoin('employees_new as e', 'e.id', '=', 'lr.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->whereIn('lr.employee_id', $employeeIds)
            ->whereIn('lr.status', ['pending', 'approved'])
            ->select(
                DB::raw("COALESCE(u.name, 'Employee') as employee"),
                DB::raw($this->columnExists('leave_requests', 'start_date') && $this->columnExists('leave_requests', 'end_date') ? "CONCAT(lr.start_date, ' to ', lr.end_date) as period" : "'-' as period"),
                DB::raw($this->columnExists('leave_requests', 'total_days') ? 'lr.total_days as days' : "'-' as days"),
                'lr.status'
            )
            ->orderByDesc('lr.id')
            ->limit(8)
            ->get()
            ->map(fn ($row) => $this->rowToArray($row))
            ->all();
    }

    private function teamActivityRows(array $employeeIds): array
    {
        if (empty($employeeIds)) {
            return [];
        }

        return collect($this->workLogActivityRows($employeeIds))
            ->merge(collect($this->todayAttendanceRows(5, $employeeIds))->map(fn ($row) => [
                'title' => 'Attendance marked',
                'description' => ($row['employee'] ?? 'Employee') . ' is ' . strtolower($row['status'] ?? 'marked'),
                'time' => now(),
                'icon' => 'fas fa-calendar-check',
            ]))
            ->take(8)
            ->values()
            ->all();
    }

    private function attendanceAdminCards(array $attendance): array
    {
        return [
            $this->card('Today Present', $attendance['present'], 'fas fa-calendar-check', 'Fresh attendance table'),
            $this->card('Today Absent', $attendance['absent'], 'fas fa-calendar-times', 'Includes auto absent records'),
            $this->card('Punch Blocked Today', $attendance['punch_blocked'], 'fas fa-user-lock', 'Auto-blocked missed punch-ins'),
            $this->card('Missed Punches', $attendance['missed_punches'], 'fas fa-exclamation-circle', 'No punch-out records'),
            $this->card('Today Late', $attendance['late'], 'fas fa-clock', 'Late punch-ins'),
            $this->card('Pending HR Attendance', $attendance['pending_hr'], 'fas fa-user-shield', 'Needs HR approval'),
        ];
    }

    private function projectData($user): array
    {
        $tasks = $this->taskStats($user);

        return [
            'cards' => [
                $this->card('Total Projects', 0, 'fas fa-project-diagram', 'Project Management setup pending'),
                $this->card('Active Projects', 0, 'fas fa-folder-open', 'Project Management setup pending'),
                $this->card('Open Tasks', $tasks['open'], 'fas fa-tasks', 'Pending and in-progress tasks'),
                $this->card('Completed Tasks', $tasks['completed'], 'fas fa-check-double', 'Completed task count'),
                $this->card('Pending Review', $tasks['review'], 'fas fa-search', 'Pending QA/review if configured'),
                $this->card('My Tasks', $tasks['mine'], 'fas fa-user-check', 'Assigned to you'),
            ],
            'project' => $tasks,
            'quick_actions' => $this->quickActionsFor('project_admin'),
            'empty_message' => $tasks['has_project_setup'] ? null : 'Project Management setup pending.',
        ];
    }

    private function operationsData(): array
    {
        $attendance = $this->attendanceStatsForDate(today()->toDateString());

        return [
            'cards' => [
                $this->card('Today Present', $attendance['present'], 'fas fa-user-check', 'Present employees'),
                $this->card('Today Absent', $attendance['absent'], 'fas fa-user-times', 'Absent or no punch'),
                $this->card('WFO', $attendance['wfo'], 'fas fa-building', 'Work from office'),
                $this->card('WFH', $attendance['wfh'], 'fas fa-home', 'Work from home'),
                $this->card('Late Employees', $attendance['late'], 'fas fa-clock', 'Late punch-ins'),
                $this->card('Early Out', $attendance['early_out'], 'fas fa-door-open', 'Early exits'),
                $this->card('Pending HR Approvals', $attendance['pending_hr'], 'fas fa-user-shield', 'Attendance review queue'),
                $this->card('Availability', $attendance['present'] + $attendance['half_day'], 'fas fa-signal', 'Available employees today'),
            ],
            'attendance_today' => $attendance,
            'quick_actions' => $this->quickActionsFor('operations_admin'),
        ];
    }

    private function customAdminData($user): array
    {
        $modules = $this->assignedModules($user);
        $cards = [];

        foreach ($modules as $module) {
            $cards[] = $this->card($module['name'], $module['count'], $module['icon'], $module['description']);
        }

        return [
            'cards' => $cards,
            'assigned_modules' => $modules,
            'quick_actions' => $this->quickActionsFor('custom_admin'),
            'empty_message' => empty($modules) ? 'No module access assigned.' : null,
        ];
    }

    private function employeeData($user): array
    {
        $employee = $this->employeeForUser($user->id ?? null);
        $employeeId = $employee->id ?? null;
        $attendance = $this->employeeAttendanceData($employeeId, $user->id ?? null);
        $leave = $this->employeeLeaveData($employeeId);
        $documents = $this->employeeDocumentData($employeeId);
        $payslip = $this->latestPayslip($employeeId);

        return [
            'employee' => $employee,
            'cards' => [
                $this->card('Profile Completion', ($employee->profile_completion ?? 0) . '%', 'fas fa-id-card', 'Your HRMS profile status'),
                $this->card('Today Attendance', $attendance['today_status'], 'fas fa-clock', $attendance['punch_summary']),
                $this->card('Month Present', $attendance['month']['present'], 'fas fa-calendar-check', 'This month present days'),
                $this->card('Month Late', $attendance['month']['late'], 'fas fa-business-time', 'This month late count'),
                $this->card('Leave Balance', $leave['summary'], 'fas fa-plane-departure', 'Available leave balance'),
                $this->card('Documents Pending', $documents['pending'], 'fas fa-file-upload', 'My document verification'),
                $this->card('Latest Payslip', $payslip['label'], 'fas fa-file-invoice', $payslip['subtitle']),
                $this->card('Reporting Manager', $employee->manager_name ?? '-', 'fas fa-user-tie', 'Your reporting manager'),
            ],
            'attendance_self' => $attendance,
            'leave_self' => $leave,
            'documents_self' => $documents,
            'latest_announcements' => $this->latestAnnouncements(),
            'latest_payslip' => $payslip,
            'quick_actions' => $this->quickActionsFor('employee', $employeeId),
        ];
    }

    private function employeeStats(): array
    {
        if (! $this->tableExists('employees_new')) {
            return ['total'=>0, 'active'=>0, 'lifecycle'=>['total'=>0,'active'=>0,'pending_profiles'=>0,'rejected_profiles'=>0,'interns'=>0,'probation'=>0,'permanent'=>0,'exit'=>0]];
        }

        $total = DB::table('employees_new')->count();
        
        $active = $total;
        if ($this->columnExists('employees_new', 'status')) {
            $active = DB::table('employees_new')->where('status', 'active')->count();
        }

        $pending_profiles = 0;
        $rejected_profiles = 0;
        if ($this->tableExists('employee_profiles') && $this->columnExists('employee_profiles', 'profile_status')) {
            $pending_profiles = DB::table('employee_profiles')->whereIn('profile_status', ['pending', 'submitted'])->count();
            $rejected_profiles = DB::table('employee_profiles')->where('profile_status', 'rejected')->count();
        } elseif ($this->tableExists('employee_profiles') && $this->columnExists('employee_profiles', 'is_profile_completed')) {
            $pending_profiles = DB::table('employee_profiles')->where('is_profile_completed', 0)->count();
        }

        $interns = 0;
        $probation = 0;
        $permanent = 0;
        if ($this->columnExists('employees_new', 'employee_stage')) {
            $interns = DB::table('employees_new')->where('employee_stage', 'internship')->count();
            $probation = DB::table('employees_new')->where('employee_stage', 'probation')->count();
            $permanent = DB::table('employees_new')->where('employee_stage', 'permanent')->count();
        } elseif ($this->columnExists('employees_new', 'employment_type')) {
            $interns = DB::table('employees_new')->where('employment_type', 'internship')->orWhere('employment_type', 'intern')->count();
            $probation = DB::table('employees_new')->where('employment_type', 'probation')->count();
            $permanent = DB::table('employees_new')->where('employment_type', 'permanent')->count();
        }

        $exit_process = 0;
        if ($this->columnExists('employees_new', 'status')) {
            $exit_process = DB::table('employees_new')->whereIn('status', ['exit_process', 'exited', 'terminated'])->count();
        }

        return [
            'total' => $total,
            'active' => $active,
            'lifecycle' => [
                'total' => $total,
                'active' => $active,
                'pending_profiles' => $pending_profiles,
                'rejected_profiles' => $rejected_profiles,
                'interns' => $interns,
                'probation' => $probation,
                'permanent' => $permanent,
                'exit_process' => $exit_process
            ]
        ];
    }

    private function attendanceStatsForDate($date, $employeeId = null, $activeEmployeeCount = null): array
    {
        $empty = [
            'present' => 0,
            'absent' => 0,
            'half_day' => 0,
            'leave' => 0,
            'week_off' => 0,
            'pending_hr' => 0,
            'punch_blocked' => 0,
            'missed_punches' => 0,
            'late' => 0,
            'early_out' => 0,
            'wfo' => 0,
            'wfh' => 0,
        ];

        if (! $this->tableExists('attendances')) {
            return $empty;
        }

        $query = DB::table('attendances as a')
            ->leftJoin('attendance_types as t', 't.id', '=', 'a.attendance_type_id')
            ->whereDate('a.attendance_date', $date);

        if ($employeeId) {
            $query->where('a.employee_id', $employeeId);
        }

        $rows = (clone $query)
            ->select('t.code', DB::raw('COUNT(*) as total'))
            ->groupBy('t.code')
            ->pluck('total', 'code')
            ->toArray();

        $stats = $empty;

        foreach (['present', 'absent', 'half_day', 'leave', 'week_off', 'pending_hr'] as $code) {
            $stats[$code] = (int) ($rows[$code] ?? 0);
        }

        $stats['punch_blocked'] = (int) ($rows['punch_blocked'] ?? 0);
        $stats['late'] = (int) (clone $query)->where('a.is_late', 1)->count();
        $stats['early_out'] = (int) (clone $query)->where('a.is_early_out', 1)->count();
        $stats['missed_punches'] = (int) (clone $query)->where(function ($q) {
            $q->where('a.missed_punch', 1)->orWhere('a.is_missed_punch', 1);
        })->count();
        $stats['wfo'] = (int) (clone $query)->where('a.work_mode', 'wfo')->count();
        $stats['wfh'] = (int) (clone $query)->where('a.work_mode', 'wfh')->count();

        if (! $employeeId) {
            $activeEmployeeCount = $activeEmployeeCount ?? $this->activeEmployeesQuery()->count();
            $markedEmployees = (int) (clone $query)->distinct('a.employee_id')->count('a.employee_id');
            $stats['absent'] += max(0, $activeEmployeeCount - $markedEmployees);
        }

        return $stats;
    }

    private function monthlyAttendanceTrend(): array
    {
        $labels = [];
        $present = [];
        $absent = [];
        $late = [];

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $days = (int) $start->daysInMonth;
        $rows = collect();

        if ($this->tableExists('attendances')) {
            $rows = DB::table('attendances as a')
                ->leftJoin('attendance_types as t', 't.id', '=', 'a.attendance_type_id')
                ->whereBetween('a.attendance_date', [$start->toDateString(), $end->toDateString()])
                ->select(
                    'a.attendance_date',
                    't.code',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN a.is_late = 1 THEN 1 ELSE 0 END) as late_total')
                )
                ->groupBy('a.attendance_date', 't.code')
                ->get()
                ->groupBy('attendance_date');
        }

        for ($day = 1; $day <= $days; $day++) {
            $date = $start->copy()->day($day)->toDateString();
            $labels[] = (string) $day;
            $dayRows = $rows->get($date, collect());

            $present[] = (int) $dayRows->where('code', 'present')->sum('total');
            $absent[] = (int) $dayRows->where('code', 'absent')->sum('total');
            $late[] = (int) $dayRows->sum('late_total');
        }

        return compact('labels', 'present', 'absent', 'late');
    }

    private function departmentEmployeesChart(): array
    {
        if (! $this->tableExists('employees_new')) {
            return ['labels' => [], 'values' => []];
        }

        if (! $this->tableExists('departments')) {
            return [
                'labels' => ['Unassigned'],
                'values' => [$this->activeEmployeesQuery()->count()],
            ];
        }

        $query = DB::table('employees_new as e')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id');

        if ($this->columnExists('employees_new', 'is_active')) {
            $query->where('e.is_active', 1);
        }

        $rows = $query
            ->select(DB::raw("COALESCE(d.name, 'Unassigned') as name"), DB::raw('COUNT(*) as total'))
            ->groupBy('d.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'values' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];
    }

    private function leaveStats(): array
    {
        $pending = 0;
        $on_leave_today = 0;
        $paid_leave = 0;
        $sick_leave = 0;
        $comp_off = 0;
        $lwp = 0;
        $sandwich_leave = 0;

        if ($this->tableExists('leave_applications') && $this->columnExists('leave_applications', 'status')) {
            $pending += DB::table('leave_applications')->whereRaw('LOWER(status) = ?', ['pending'])->count();
        }

        if ($this->tableExists('leave_requests') && $this->columnExists('leave_requests', 'status')) {
            $pending += DB::table('leave_requests')->whereRaw('LOWER(status) = ?', ['pending'])->count();
            
            if ($this->columnExists('leave_requests', 'start_date') && $this->columnExists('leave_requests', 'end_date')) {
                $today = today()->toDateString();
                $on_leave_today += DB::table('leave_requests')->whereRaw('LOWER(status) = ?', ['approved'])
                    ->where('start_date', '<=', $today)->where('end_date', '>=', $today)->count();
            }
        }

        return [
            'pending' => $pending,
            'on_leave_today' => $on_leave_today,
            'paid_leave' => $paid_leave,
            'sick_leave' => $sick_leave,
            'comp_off' => $comp_off,
            'lwp' => $lwp,
            'sandwich_leave' => $sandwich_leave,
        ];
    }

    private function payrollStats(): array
    {
        $month = (int) now()->month;
        $year = (int) now()->year;
        $statusRows = collect();
        $status = 'Not Run';
        
        $gross_payroll = 0;
        $net_payroll = 0;
        $total_deductions = 0;
        $payslips_generated = 0;
        
        if ($this->tableExists('enterprise_payrolls')) {
             $payslips_generated = DB::table('enterprise_payrolls')->where('month', $month)->where('year', $year)->count();
             $gross_payroll = DB::table('enterprise_payrolls')->where('month', $month)->where('year', $year)->sum('gross_salary');
             $net_payroll = DB::table('enterprise_payrolls')->where('month', $month)->where('year', $year)->sum('net_salary');
             $total_deductions = DB::table('enterprise_payrolls')->where('month', $month)->where('year', $year)->sum('total_deductions');
        }

        if ($this->tableExists('payrolls')) {
            $statusRows = DB::table('payrolls')
                ->where('month', $month)
                ->where('year', $year)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->get();

            $status = optional($statusRows->sortByDesc('total')->first())->status ?: 'Not Run';
            
            if ($payslips_generated == 0) {
                 $payslips_generated = DB::table('payrolls')->where('month', $month)->where('year', $year)->count();
                 $net_payroll = DB::table('payrolls')->where('month', $month)->where('year', $year)->sum('net_salary');
            }
        }

        return [
            'current_status' => $status,
            'status_breakdown' => $statusRows,
            'salary_estimate' => $this->activeSalaryEstimate(),
            'payslips_generated' => $payslips_generated,
            'fnf_pending' => $this->fnfPendingCount(),
            'claims_pending' => $this->tableExists('claims') && $this->columnExists('claims', 'status')
                ? DB::table('claims')->whereRaw('LOWER(status) = ?', ['pending'])->count()
                : 0,
            'salary_structures' => $this->tableExists('salary_structures') ? DB::table('salary_structures')->count() : 0,
            'gross_payroll' => $gross_payroll,
            'net_payroll' => $net_payroll,
            'total_deductions' => $total_deductions,
            'pending_approval' => 0,
            'missing_structure' => 0,
        ];
    }

    private function documentStats(): array
    {
        $pending = 0;
        $rejected_documents = 0;
        $missing_documents = 0;
        $expired_documents = 0;
        $recently_uploaded = 0;

        if ($this->tableExists('employee_documents_new') && $this->columnExists('employee_documents_new', 'verification_status')) {
            $pending += DB::table('employee_documents_new')->where('verification_status', 'pending')->count();
            $rejected_documents += DB::table('employee_documents_new')->where('verification_status', 'rejected')->count();
            $recently_uploaded += DB::table('employee_documents_new')->where('created_at', '>=', now()->subDays(7))->count();
        }

        if ($this->tableExists('employee_documents') && $this->columnExists('employee_documents', 'status')) {
            $pending += DB::table('employee_documents')->whereRaw('LOWER(status) = ?', ['pending'])->count();
        }

        return [
            'pending' => $pending,
            'pending_verification' => $pending,
            'rejected_documents' => $rejected_documents,
            'missing_documents' => $missing_documents,
            'expired_documents' => $expired_documents,
            'recently_uploaded' => $recently_uploaded,
        ];
    }

    private function announcementStats(): array
    {
        $total = 0;
        $active = 0;
        $published_today = 0;
        
        if ($this->tableExists('announcements')) {
            $total = DB::table('announcements')->count();
            if ($this->columnExists('announcements', 'status')) {
                $active = DB::table('announcements')->where('status', 'active')->count();
            } else {
                $active = $total;
            }
            $published_today = DB::table('announcements')->whereDate('created_at', today()->toDateString())->count();
        }
        
        return [
            'total' => $total,
            'active' => $active,
            'published_today' => $published_today,
            'notifications_today' => $this->tableExists('notifications') ? DB::table('notifications')->whereDate('created_at', today()->toDateString())->count() : 0,
            'failed_pushes' => 0,
        ];
    }

    private function taskStats($user): array
    {
        if (! $this->tableExists('taskmanagement')) {
            return [
                'open' => 0,
                'completed' => 0,
                'review' => 0,
                'mine' => 0,
                'has_project_setup' => false,
            ];
        }

        return [
            'open' => DB::table('taskmanagement')->whereIn('status', ['pending', 'progress', 'overdue'])->count(),
            'completed' => DB::table('taskmanagement')->where('status', 'completed')->count(),
            'review' => $this->columnExists('taskmanagement', 'review_status')
                ? DB::table('taskmanagement')->whereRaw('LOWER(review_status) = ?', ['pending'])->count()
                : 0,
            'mine' => DB::table('taskmanagement')->where('user_id', $user->id ?? 0)->count(),
            'has_project_setup' => false,
        ];
    }

    private function employeeAttendanceData($employeeId, $userId): array
    {
        $today = null;
        $todayStatus = 'Not Marked';
        $punchSummary = 'No punch recorded today';
        $remaining = null;

        if ($this->tableExists('attendances') && $employeeId) {
            $today = DB::table('attendances as a')
                ->leftJoin('attendance_types as t', 't.id', '=', 'a.attendance_type_id')
                ->where('a.employee_id', $employeeId)
                ->whereDate('a.attendance_date', today()->toDateString())
                ->select('a.*', 't.name as type_name', 't.code as type_code')
                ->first();

            if ($today) {
                $todayStatus = $today->type_name ?: ucfirst(str_replace('_', ' ', $today->type_code ?: 'Marked'));
                $punchSummary = trim(($today->punch_in_time ?: '--') . ' to ' . ($today->punch_out_time ?: '--'));
                $remaining = $this->remainingShiftMinutes($today);
            }
        }

        return [
            'today' => $today,
            'today_status' => $todayStatus,
            'punch_summary' => $punchSummary,
            'remaining_shift_minutes' => $remaining,
            'month' => $this->attendanceStatsForMonth($employeeId),
            'recent' => $this->recentEmployeeAttendance($employeeId),
        ];
    }

    private function attendanceStatsForMonth($employeeId): array
    {
        if (! $this->tableExists('attendances') || ! $employeeId) {
            return ['present' => 0, 'absent' => 0, 'half_day' => 0, 'leave' => 0, 'late' => 0, 'work_minutes' => 0];
        }

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $base = DB::table('attendances as a')
            ->leftJoin('attendance_types as t', 't.id', '=', 'a.attendance_type_id')
            ->where('a.employee_id', $employeeId)
            ->whereBetween('a.attendance_date', [$start, $end]);

        $rows = (clone $base)
            ->select('t.code', DB::raw('COUNT(*) as total'))
            ->groupBy('t.code')
            ->pluck('total', 'code')
            ->toArray();

        return [
            'present' => (int) ($rows['present'] ?? 0),
            'absent' => (int) ($rows['absent'] ?? 0),
            'half_day' => (int) ($rows['half_day'] ?? 0),
            'leave' => (int) ($rows['leave'] ?? 0),
            'late' => (int) (clone $base)->where('a.is_late', 1)->count(),
            'work_minutes' => (int) (clone $base)->sum('a.total_work_minutes'),
        ];
    }

    private function recentEmployeeAttendance($employeeId)
    {
        if (! $this->tableExists('attendances') || ! $employeeId) {
            return collect();
        }

        return DB::table('attendances as a')
            ->leftJoin('attendance_types as t', 't.id', '=', 'a.attendance_type_id')
            ->where('a.employee_id', $employeeId)
            ->orderByDesc('a.attendance_date')
            ->limit(5)
            ->select('a.attendance_date', 'a.punch_in_time', 'a.punch_out_time', 'a.work_mode', 'a.total_work_minutes', 't.name as type_name', 't.code as type_code')
            ->get();
    }

    private function employeeLeaveData($employeeId): array
    {
        $balance = $this->emptyLeaveAllocationBalance();

        if ($this->tableExists('leave_allocations') && $employeeId) {
            $balance = DB::table('leave_allocations')
                ->where('employee_id', $employeeId)
                ->where('year', now()->year)
                ->first() ?: $balance;
        }

        $paidRemaining = max(0, (float) ($balance->paid_remaining ?? 0));
        $sickRemaining = max(0, (float) ($balance->sick_remaining ?? 0));
        $compOffRemaining = max(0, (float) ($balance->comp_off_remaining ?? 0));
        $totalRemaining = max(0, (float) ($balance->total_remaining ?? ($paidRemaining + $sickRemaining + $compOffRemaining)));

        return [
            'balance' => $balance,
            'paid_remaining' => $paidRemaining,
            'sick_remaining' => $sickRemaining,
            'comp_off_remaining' => $compOffRemaining,
            'total_remaining' => $totalRemaining,
            'summary' => $totalRemaining,
            'pending' => $this->employeePendingLeaveCount($employeeId),
        ];
    }

    private function emptyLeaveAllocationBalance(): \stdClass
    {
        return (object) [
            'total_allocated' => 0,
            'paid_allocated' => 0,
            'sick_allocated' => 0,
            'comp_off_allocated' => 0,
            'total_used' => 0,
            'paid_used' => 0,
            'sick_used' => 0,
            'comp_off_used' => 0,
            'lwp_used' => 0,
            'total_remaining' => 0,
            'paid_remaining' => 0,
            'sick_remaining' => 0,
            'comp_off_remaining' => 0,
        ];
    }

    private function employeeDocumentData($employeeId): array
    {
        $counts = ['pending' => 0, 'verified' => 0, 'rejected' => 0];

        if ($this->tableExists('employee_documents_new') && $employeeId) {
            $rows = DB::table('employee_documents_new')
                ->where('employee_id', $employeeId)
                ->select('verification_status', DB::raw('COUNT(*) as total'))
                ->groupBy('verification_status')
                ->pluck('total', 'verification_status')
                ->toArray();

            foreach ($counts as $key => $value) {
                $counts[$key] = (int) ($rows[$key] ?? 0);
            }
        }

        return $counts;
    }

    private function latestPayslip($employeeId): array
    {
        if (! $this->tableExists('payslips') || ! $employeeId) {
            return ['label' => '-', 'subtitle' => 'No payslip available'];
        }

        $payslip = DB::table('payslips')
            ->where('employee_id', $employeeId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (! $payslip) {
            return ['label' => '-', 'subtitle' => 'No payslip available'];
        }

        return [
            'label' => Carbon::createFromDate($payslip->year, $payslip->month, 1)->format('M Y'),
            'subtitle' => 'Latest generated salary slip',
        ];
    }

    private function employeeForUser($userId)
    {
        if (! $this->tableExists('employees_new') || ! $userId) {
            return null;
        }

        $employee = DB::table('employees_new as e')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoin('employee_profiles as p', 'p.employee_id', '=', 'e.id')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->leftJoin('designations as dg', 'dg.id', '=', 'e.designation_id')
            ->leftJoin('employees_new as m', 'm.id', '=', 'e.reporting_manager_employee_id')
            ->leftJoin('users as mu', 'mu.id', '=', 'm.user_id')
            ->where('e.user_id', $userId)
            ->select(
                'e.*',
                'u.name',
                'u.email',
                'p.is_profile_completed',
                'p.profile_image',
                'p.date_of_birth',
                'p.gender',
                'p.address',
                'p.highest_qualification',
                'p.resume_file',
                'p.bank_account_no',
                'd.name as department_name',
                'dg.name as designation_name',
                'mu.name as manager_name'
            )
            ->first();

        if ($employee) {
            $employee->profile_completion = $this->profileCompletion($employee);
        }

        return $employee;
    }

    private function activeEmployeesQuery()
    {
        $query = DB::table('employees_new');

        if ($this->columnExists('employees_new', 'employment_status')) {
            $query->where('employment_status', 'active');
        }

        if ($this->columnExists('employees_new', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query;
    }

    private function pendingProfilesCount(): int
    {
        if (! $this->tableExists('employee_profiles')) {
            return $this->tableExists('employees_new') ? DB::table('employees_new')->count() : 0;
        }

        return DB::table('employees_new as e')
            ->leftJoin('employee_profiles as p', 'p.employee_id', '=', 'e.id')
            ->where(function ($query) {
                $query->whereNull('p.id');

                if ($this->columnExists('employee_profiles', 'is_profile_completed')) {
                    $query->orWhere('p.is_profile_completed', 0);
                }

                if ($this->columnExists('employee_profiles', 'status')) {
                    $query->orWhereIn('p.status', ['pending', 'draft', 'rejected']);
                }
            })
            ->count();
    }

    private function probationInternshipCount(): int
    {
        $query = DB::table('employees_new')->where(function ($q) {
            if ($this->columnExists('employees_new', 'employee_stage')) {
                $q->whereIn('employee_stage', ['internship', 'probation']);
            }

            if ($this->columnExists('employees_new', 'employment_type')) {
                $q->orWhere('employment_type', 'intern');
            }

            if ($this->columnExists('employees_new', 'probation_status')) {
                $q->orWhereIn('probation_status', ['pending', 'ongoing']);
            }
        });

        if ($this->columnExists('employees_new', 'employment_status')) {
            $query->where('employment_status', 'active');
        }

        return $query->count();
    }

    private function probationEndingSoonCount(): int
    {
        if (! $this->tableExists('employees_new') || ! $this->columnExists('employees_new', 'probation_end_date')) {
            return 0;
        }

        $query = DB::table('employees_new')
            ->whereBetween('probation_end_date', [today()->toDateString(), today()->addDays(30)->toDateString()]);

        if ($this->columnExists('employees_new', 'employee_stage')) {
            $query->where('employee_stage', 'probation');
        }

        if ($this->columnExists('employees_new', 'status')) {
            $query->where('status', 'active');
        }

        if ($this->columnExists('employees_new', 'employment_status')) {
            $query->where('employment_status', 'active');
        }

        if ($this->columnExists('employees_new', 'is_active')) {
            $query->where('is_active', 1);
        }

        return (int) $query->count();
    }

    private function exitEmployeesCount(): int
    {
        if (! $this->tableExists('employees_new')) {
            return 0;
        }

        return DB::table('employees_new')
            ->where(function ($query) {
                if ($this->columnExists('employees_new', 'employment_status')) {
                    $query->whereIn('employment_status', ['resigned', 'terminated', 'inactive']);
                }

                if ($this->columnExists('employees_new', 'relieving_date')) {
                    $query->orWhereNotNull('relieving_date');
                }

                if ($this->columnExists('employees_new', 'is_active')) {
                    $query->orWhere('is_active', 0);
                }
            })
            ->count();
    }

    private function lifecycleCounts(): array
    {
        if (! $this->tableExists('employees_new')) {
            return [];
        }

        if ($this->columnExists('employees_new', 'employee_stage')) {
            return DB::table('employees_new')
                ->select('employee_stage as label', DB::raw('COUNT(*) as total'))
                ->groupBy('employee_stage')
                ->pluck('total', 'label')
                ->toArray();
        }

        return DB::table('employees_new')
            ->select('employment_type as label', DB::raw('COUNT(*) as total'))
            ->groupBy('employment_type')
            ->pluck('total', 'label')
            ->toArray();
    }

    private function activeSalaryEstimate(): float
    {
        if (! $this->tableExists('employees_new') || ! $this->columnExists('employees_new', 'actual_salary')) {
            return 0;
        }

        return (float) $this->activeEmployeesQuery()->sum('actual_salary');
    }

    private function fnfPendingCount(): int
    {
        $exitCount = $this->exitEmployeesCount();

        if (! $this->tableExists('fnf_settlements')) {
            return $exitCount;
        }

        $settled = DB::table('fnf_settlements')->distinct('employee_id')->count('employee_id');

        return max(0, $exitCount - $settled);
    }

    private function remainingShiftMinutes($attendance): ?int
    {
        if (! $attendance || ! empty($attendance->punch_out_time) || ! $this->tableExists('attendance_times')) {
            return null;
        }

        $shift = null;

        if (! empty($attendance->attendance_time_id)) {
            $shift = DB::table('attendance_times')->where('id', $attendance->attendance_time_id)->first();
        }

        if (! $shift) {
            $shift = DB::table('attendance_times')->where('is_default', 1)->first();
        }

        if (! $shift || empty($shift->shift_end_time)) {
            return null;
        }

        $end = Carbon::parse(today()->toDateString() . ' ' . $shift->shift_end_time);

        return now()->lessThan($end) ? now()->diffInMinutes($end) : 0;
    }

    private function recentActivities()
    {
        $activities = collect();

        if ($this->tableExists('employees_new')) {
            DB::table('employees_new as e')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                ->orderByDesc('e.created_at')
                ->limit(4)
                ->select('u.name', 'e.employee_code', 'e.created_at')
                ->get()
                ->each(function ($row) use ($activities) {
                    $activities->push([
                        'title' => 'Employee onboarded',
                        'description' => trim(($row->name ?: 'Employee') . ' ' . ($row->employee_code ? '(' . $row->employee_code . ')' : '')),
                        'time' => $row->created_at,
                        'icon' => 'fas fa-user-plus',
                    ]);
                });
        }

        if ($this->tableExists('attendance_work_logs')) {
            DB::table('attendance_work_logs')
                ->orderByDesc('created_at')
                ->limit(4)
                ->select('work_summary', 'created_at')
                ->get()
                ->each(function ($row) use ($activities) {
                    $activities->push([
                        'title' => 'Work log submitted',
                        'description' => $row->work_summary,
                        'time' => $row->created_at,
                        'icon' => 'fas fa-clipboard-list',
                    ]);
                });
        }

        return $activities->sortByDesc('time')->take(6)->values();
    }

    private function latestAnnouncements()
    {
        if (! $this->tableExists('announcements')) {
            return collect();
        }

        return DB::table('announcements')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select('title', 'description', 'created_at')
            ->get();
    }

    private function assignedModules($user): array
    {
        $modules = collect();
        $roleIds = $this->roleIds($user);

        if ($this->tableExists('role_menu_access') && $this->tableExists('menus') && ! empty($roleIds)) {
            $modules = DB::table('role_menu_access as rma')
                ->join('menus as m', 'm.id', '=', 'rma.menu_id')
                ->whereIn('rma.role_id', $roleIds)
                ->whereNotNull('m.module_key')
                ->select('m.module_key', DB::raw('COUNT(DISTINCT m.id) as total'))
                ->groupBy('m.module_key')
                ->get();
        }

        if ($modules->isEmpty() && $this->tableExists('user_module_access')) {
            $modules = DB::table('user_module_access')
                ->where('user_id', $user->id ?? 0)
                ->where('is_enabled', 1)
                ->select('module_key', DB::raw('1 as total'))
                ->get();
        }

        return $modules->map(function ($module) {
            $name = ucwords(str_replace('_', ' ', $module->module_key));

            return [
                'name' => $name,
                'count' => (int) $module->total,
                'icon' => $this->moduleIcon($module->module_key),
                'description' => 'Assigned ' . $name . ' access',
            ];
        })->values()->all();
    }

            private function quickActionsFor(string $role, $employeeId = null): array
    {
        $actions = [
            'super_admin' => [
                ['title' => 'Add Employee', 'icon' => 'fas fa-user-plus', 'routes' => ['hrms.employees.create', 'employees.create']],
                ['title' => 'Run Payroll', 'icon' => 'fas fa-money-check-alt', 'routes' => ['enterprise-payroll.runs.index']],
                ['title' => 'Publish Announcement', 'icon' => 'fas fa-bullhorn', 'routes' => ['announcements', 'announcements.index', 'hrms.announcements.index']],
                ['title' => 'Attendance Report', 'icon' => 'fas fa-calendar-check', 'routes' => ['attendances.index', 'attendances.monthly-report']],
                ['title' => 'Open Approvals', 'icon' => 'fas fa-tasks', 'routes' => ['leave-approvals.index', 'attendances.pending-approval']],
                ['title' => 'Documents', 'icon' => 'fas fa-file-alt', 'routes' => ['documents.hr.index', 'hrms.employee-documents.index', 'hrms.documents.index']],
            ],
            'hr_admin' => [
                ['title' => 'Add Employee', 'icon' => 'fas fa-user-plus', 'routes' => ['hrms.employees.create', 'employees.create']],
                ['title' => 'Pending Profiles', 'icon' => 'fas fa-id-card', 'routes' => ['hrms.employees.pending_profiles']],
                ['title' => 'Attendance', 'icon' => 'fas fa-clock', 'routes' => ['attendances.index']],
                ['title' => 'Leave Approval', 'icon' => 'fas fa-plane', 'routes' => ['leave-approvals.index']],
            ],
            'finance_admin' => [
                ['title' => 'Payroll Dashboard', 'icon' => 'fas fa-chart-line', 'routes' => ['enterprise-payroll.dashboard']],
                ['title' => 'Run Payroll', 'icon' => 'fas fa-play-circle', 'routes' => ['pages.payroll.payrollrun']],
            ],
            'project_admin' => [
                ['title' => 'Task Tracking', 'icon' => 'fas fa-tasks', 'routes' => ['project_management.tasks.index']],
            ],
            'operations_admin' => [
                ['title' => 'Daily Attendance', 'icon' => 'fas fa-calendar-day', 'routes' => ['attendances.daily']],
            ],
            'custom_admin' => [
                ['title' => 'Dashboard', 'icon' => 'fas fa-th-large', 'routes' => ['dashboard']],
            ],
            'employee' => [
                ['title' => 'Complete Profile', 'icon' => 'fas fa-id-card', 'routes' => ['profile.index']],
                ['title' => 'Punch In/Out', 'icon' => 'fas fa-fingerprint', 'routes' => ['attendances.index']],
            ],
        ];

        return collect($actions[$role] ?? [])->map(function ($action) {
            foreach ($action['routes'] as $route) {
                if (\Illuminate\Support\Facades\Route::has($route)) {
                    return [
                        'title' => $action['title'],
                        'icon' => $action['icon'],
                        'route' => $route,
                        'url' => route($route),
                    ];
                }
            }
            return null;
        })->filter()->values()->all();
    }

    private function roleIds($user): array
    {
        $ids = [];

        foreach (['system_role_id', 'role_id'] as $column) {
            if (! empty($user->{$column})) {
                $ids[] = (int) $user->{$column};
            }
        }

        if ($this->tableExists('user_roles')) {
            $ids = array_merge(
                $ids,
                DB::table('user_roles')->where('user_id', $user->id ?? 0)->pluck('role_id')->map(fn ($id) => (int) $id)->all()
            );
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function employeePendingLeaveCount($employeeId): int
    {
        if (! $this->tableExists('leave_applications') || ! $employeeId) {
            return 0;
        }

        return DB::table('leave_applications')
            ->where('employee_id', $employeeId)
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->count();
    }

    private function profileCompletion($employee): int
    {
        if (! empty($employee->is_profile_completed)) {
            return 100;
        }

        $fields = [
            'name',
            'email',
            'employee_code',
            'department_name',
            'designation_name',
            'date_of_birth',
            'gender',
            'address',
            'highest_qualification',
            'resume_file',
            'bank_account_no',
        ];

        $filled = 0;

        foreach ($fields as $field) {
            if (! empty($employee->{$field})) {
                $filled++;
            }
        }

        return (int) round(($filled / count($fields)) * 100);
    }

    private function card($label, $value, $icon, $subtitle): array
    {
        return compact('label', 'value', 'icon', 'subtitle');
    }

    private function moduleIcon($moduleKey): string
    {
        $icons = [
            'hrms' => 'fas fa-users',
            'attendance' => 'fas fa-clock',
            'payroll' => 'fas fa-file-invoice-dollar',
            'documents' => 'fas fa-folder-open',
            'access_control' => 'fas fa-user-shield',
            'settings' => 'fas fa-cog',
            'project_management' => 'fas fa-project-diagram',
            'finance' => 'fas fa-coins',
            'crm' => 'fas fa-handshake',
        ];

        return $icons[$moduleKey] ?? 'fas fa-th-large';
    }

    private function routeUrl($routeName): string
    {
        return Route::has($routeName) ? route($routeName) : '#';
    }

    private function money($amount): string
    {
        return 'Rs ' . number_format((float) $amount, 0);
    }

    private function tableExists($table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }
        try {
            return $cache[$table] = Schema::hasTable($table);
        } catch (\Throwable $e) {
            return $cache[$table] = false;
        }
    }

    private function columnExists($table, $column): bool
    {
        static $cache = [];
        $key = "{$table}.{$column}";
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        try {
            return $cache[$key] = Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return $cache[$key] = false;
        }
    }
    private function getSystemHealth(): array
    {
        $storagePath = storage_path();
        $publicStoragePath = public_path('storage');

        $dbConnected = false;
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $items = [
            [
                'label' => 'Storage Path',
                'value' => \Illuminate\Support\Facades\File::exists($storagePath) ? 'Available' : 'Missing',
                'icon' => 'fas fa-folder-open',
                'status' => \Illuminate\Support\Facades\File::exists($storagePath) ? 'ok' : 'danger',
            ],
            [
                'label' => 'Public Storage Link',
                'value' => \Illuminate\Support\Facades\File::exists($publicStoragePath) ? 'Available' : 'Not linked',
                'icon' => 'fas fa-link',
                'status' => \Illuminate\Support\Facades\File::exists($publicStoragePath) ? 'ok' : 'warning',
            ],
            [
                'label' => 'Database Connection',
                'value' => $dbConnected ? 'Connected' : 'Not connected',
                'icon' => 'fas fa-database',
                'status' => $dbConnected ? 'ok' : 'danger',
            ],
            [
                'label' => 'Server Timezone',
                'value' => date_default_timezone_get(),
                'icon' => 'fas fa-globe',
                'status' => 'neutral',
            ],
        ];

        if ($this->tableExists('failed_jobs')) {
            $failed = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
            $items[] = [
                'label' => 'Failed Jobs',
                'value' => $failed,
                'icon' => 'fas fa-exclamation-triangle',
                'status' => $failed > 0 ? 'danger' : 'ok',
            ];
        }

        return $items;
    }

    private function getLiveAttendanceTable($date): array
    {
        if (! $this->tableExists('attendances') || ! $this->tableExists('employees_new') || ! $this->tableExists('users')) {
            return [];
        }

        $query = \Illuminate\Support\Facades\DB::table('attendances as a')
            ->join('employees_new as e', 'e.id', '=', 'a.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->whereDate('a.attendance_date', $date);

        if ($this->tableExists('departments') && $this->columnExists('employees_new', 'department_id')) {
            $query->leftJoin('departments as d', 'd.id', '=', 'e.department_id');
        }

        if ($this->tableExists('attendance_times') && $this->columnExists('attendances', 'attendance_time_id')) {
            $query->leftJoin('attendance_times as s', 's.id', '=', 'a.attendance_time_id');
        }

        $select = [
            'e.employee_code',
            'u.name as employee_name',
            'a.punch_in_time',
            'a.punch_out_time',
            \Illuminate\Support\Facades\DB::raw($this->tableExists('departments') && $this->columnExists('employees_new', 'department_id') ? "COALESCE(d.name, 'N/A') as department_name" : "'N/A' as department_name"),
            \Illuminate\Support\Facades\DB::raw($this->tableExists('attendance_times') && $this->columnExists('attendances', 'attendance_time_id') ? "COALESCE(s.name, 'N/A') as shift_name" : "'N/A' as shift_name")
        ];

        if ($this->columnExists('attendances', 'work_mode')) {
            $select[] = 'a.work_mode';
        }
        if ($this->columnExists('attendances', 'attendance_status')) {
            $select[] = 'a.attendance_status';
        }
        if ($this->columnExists('attendances', 'is_late')) {
            $select[] = 'a.is_late';
        }
        if ($this->columnExists('attendances', 'is_early_out')) {
            $select[] = 'a.is_early_out';
        }
        if ($this->columnExists('attendances', 'is_missed_punch')) {
            $select[] = 'a.is_missed_punch';
        }
        if ($this->columnExists('attendances', 'missed_punch')) {
            $select[] = 'a.missed_punch';
        }
        if ($this->columnExists('attendances', 'is_admin_unlocked')) {
            $select[] = 'a.is_admin_unlocked';
        }
        if ($this->columnExists('attendances', 'is_half_day')) {
            $select[] = 'a.is_half_day';
        }

        $rows = $query->select($select)->orderByDesc('a.created_at')->get();

        return collect($rows)->map(function ($r) {
            $row = $this->rowToArray($r);
            $flags = [];
            if (!empty($row['is_late'])) $flags[] = 'Late';
            if (!empty($row['is_early_out'])) $flags[] = 'Early Logout';
            if (!empty($row['is_missed_punch']) || !empty($row['missed_punch'])) $flags[] = 'Missed Punch';
            if (!empty($row['is_admin_unlocked'])) $flags[] = 'Unlocked';
            if (!empty($row['is_half_day'])) $flags[] = 'Half Day';
            
            $workMode = $row['work_mode'] ?? 'WFO';
            $flags[] = strtoupper($workMode);

            $row['flags'] = $flags;
            $row['employee_name'] = $row['employee_name'] ?? 'N/A';
            return $row;
        })->values()->all();
    }

    private function getPayrollOverview(): array
    {
        $overview = [
            'gross_payroll' => 0,
            'net_payroll' => 0,
            'total_deductions' => 0,
            'payslips_generated' => 0,
            'pending_approval' => 0,
            'missing_structure' => 0,
            'monthly_trend' => ['labels' => [], 'net' => [], 'gross' => []],
        ];

        if ($this->tableExists('enterprise_payroll_runs')) {
            $overview['pending_approval'] = \Illuminate\Support\Facades\DB::table('enterprise_payroll_runs')->where('status', 'pending')->count();
        }

        if ($this->tableExists('enterprise_payrolls')) {
            $currentMonth = now()->timezone('Asia/Kolkata')->startOfMonth()->toDateString();
            $latestRun = \Illuminate\Support\Facades\DB::table('enterprise_payrolls')
                ->whereDate('for_month', '>=', $currentMonth)
                ->selectRaw('SUM(gross_pay) as gross, SUM(net_pay) as net, SUM(total_deductions) as deductions, COUNT(id) as count')
                ->first();
                
            $overview['gross_payroll'] = $latestRun->gross ?? 0;
            $overview['net_payroll'] = $latestRun->net ?? 0;
            $overview['total_deductions'] = $latestRun->deductions ?? 0;
            $overview['payslips_generated'] = $latestRun->count ?? 0;
        }
        
        if ($this->tableExists('employees_new') && $this->tableExists('enterprise_salary_structures')) {
            $activeCount = $this->countActiveEmployees();
            $withStructure = \Illuminate\Support\Facades\DB::table('enterprise_salary_structures')->distinct('employee_id')->count('employee_id');
            $overview['missing_structure'] = max(0, $activeCount - $withStructure);
        }

        if ($this->tableExists('enterprise_payrolls')) {
            $trend = \Illuminate\Support\Facades\DB::table('enterprise_payrolls')
                ->selectRaw('DATE_FORMAT(for_month, "%b %Y") as month, SUM(net_pay) as net, SUM(gross_pay) as gross')
                ->groupBy('month')
                ->orderBy('for_month', 'desc')
                ->limit(6)
                ->get();
            
            foreach ($trend->reverse() as $t) {
                $overview['monthly_trend']['labels'][] = $t->month;
                $overview['monthly_trend']['net'][] = (float) $t->net;
                $overview['monthly_trend']['gross'][] = (float) $t->gross;
            }
        }

        return $overview;
    }

    private function getLeaveOverview($date): array
    {
        $overview = [
            'on_leave_today' => 0,
            'paid_leave' => 0,
            'sick_leave' => 0,
            'comp_off' => 0,
            'lwp' => 0,
            'sandwich_leave' => 0,
        ];
        
        if ($this->tableExists('leave_requests')) {
            $query = \Illuminate\Support\Facades\DB::table('leave_requests')->whereRaw('LOWER(status) = ?', ['approved']);
            if ($this->columnExists('leave_requests', 'start_date')) {
                $query->whereDate('start_date', '<=', $date);
            }
            if ($this->columnExists('leave_requests', 'end_date')) {
                $query->whereDate('end_date', '>=', $date);
            }
            $overview['on_leave_today'] = $query->count();
        }
        
        return $overview;
    }

    private function getDocumentOverview(): array
    {
        return [
            'pending_verification' => $this->getPendingDocumentsCount(),
            'rejected_documents' => 0,
            'missing_documents' => 0,
            'expired_documents' => 0,
            'recently_uploaded' => $this->tableExists('employee_documents_new') ? \Illuminate\Support\Facades\DB::table('employee_documents_new')->whereDate('created_at', '>=', now()->subDays(7))->count() : 0,
        ];
    }

    private function employeeLifecycleDistributionChart(): array
    {
        if (! $this->tableExists('employees_new') || ! $this->columnExists('employees_new', 'employee_stage')) {
            return ['labels' => [], 'values' => []];
        }

        $rows = \Illuminate\Support\Facades\DB::table('employees_new')
            ->select('employee_stage as label', \Illuminate\Support\Facades\DB::raw('COUNT(id) as total'))
            ->whereNotNull('employee_stage')
            ->groupBy('employee_stage')
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn($l) => ucfirst($l))->values()->all(),
            'values' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
        ];
    }
    
    private function monthlyHiringTrendChart(): array
    {
        if (! $this->tableExists('employees_new') || ! $this->columnExists('employees_new', 'joining_date')) {
            return ['labels' => [], 'values' => []];
        }

        $rows = \Illuminate\Support\Facades\DB::table('employees_new')
            ->select(\Illuminate\Support\Facades\DB::raw('DATE_FORMAT(joining_date, "%b %Y") as month'), \Illuminate\Support\Facades\DB::raw('COUNT(id) as total'))
            ->whereNotNull('joining_date')
            ->groupBy('month')
            ->orderByRaw('MIN(joining_date) DESC')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows->pluck('month')->reverse()->values()->all(),
            'values' => $rows->pluck('total')->reverse()->map(fn($v) => (int) $v)->values()->all(),
        ];
    }
    
    private function monthlyAttendanceChart(): array { return ['labels' => [], 'present' => [], 'late' => [], 'absent' => []]; }
    private function departmentAttendanceChart(): array { return ['labels' => [], 'values' => []]; }
    private function leaveDistributionChart(): array { return ['labels' => [], 'values' => []]; }
    private function getLatestApkVersion() { return '1.0.0'; }
    private function getMobileAppHealth(): array { return []; }
    private function getLatestAnnouncements(): array { return []; }
    private function routeUrlOrNull(array $routes) { return null; }
    private function getShiftOverview($date): array { return []; }
    private function getActionRequiredCards($a, $b, $c, $d, $e): array { return []; }
    private function getLiveActivity(): array { return []; }
    private function getPunchInRunningCount($date) { return 0; }
    private function getYetToPunchInCount($date) { return 0; }
    private function getLifecycleCount($stage) { return 0; }

    public function superAdminData(): array
    {
        try {
            $employee = $this->employeeStats();
            $attendance = $this->attendanceStatsForDate(Carbon::today(config('app.timezone', 'Asia/Kolkata'))->toDateString(), null, $employee['active'] ?? 0);
            $leave = $this->leaveStats();
            $payroll = $this->payrollStats();
            $documents = $this->documentStats();
            $announcements = $this->announcementStats();

            // Cards structure for attendance
            $cards = $this->superAdminCards($attendance, $employee);
            
            // Build live attendance
            $liveAttendance = $this->buildLiveAttendance();
            
            // Action required
            $actionRequired = $this->buildActionRequired();
            
            // Charts
            $charts = $this->buildCharts();

            // Quick actions
            $quickActions = $this->quickActionsFor('super_admin');

            // Recent activities
            $recentActivities = $this->buildRecentActivities();

            // System health
            $systemHealth = $this->getSystemHealth();

            // Tables structure for Blade support
            $tables = [
                'blocked_employees' => $this->getBlockedEmployeesTable(),
                'pending_leaves' => $this->getPendingLeavesTable(),
                'pending_profiles' => $this->getPendingProfilesTable(),
                'pending_documents' => $this->getPendingDocumentsTable(),
                'live_attendance' => $liveAttendance,
            ];

            return [
                'meta' => [
                    'title' => 'Super Admin Dashboard',
                    'subtitle' => 'Monitor HRMS operations, attendance, payroll and approvals.',
                    'current_date' => Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('l, d M Y h:i A'),
                ],
                'cards' => $cards,
                'quick_actions' => $quickActions,
                'attendance_cards' => $this->getAttendanceCardsData($cards),
                'employee_cards' => $this->getEmployeeCardsData($employee),
                'action_required' => $actionRequired,
                'live_attendance' => $liveAttendance,
                'payroll_overview' => $payroll,
                'leave_overview' => $leave,
                'document_overview' => $documents,
                'announcement_overview' => $announcements,
                'system_health' => $systemHealth,
                'charts' => $charts,
                'recent_activities' => $recentActivities,
                // Include other direct keys used in Blade:
                'lifecycle' => $employee['lifecycle'] ?? [],
                'leave' => $leave,
                'payroll' => $payroll,
                'documents' => $documents,
                'announcements' => $announcements,
                'system' => [
                    'apk_version' => $this->getLatestApkVersion(),
                ],
                'tables' => $tables,
            ];
        } catch (\Throwable $e) {
            Log::error('Super Admin Data Resolution failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->basePayload();
        }
    }

    public function basePayload(): array
    {
        return [
            'meta' => [
                'title' => 'Super Admin Dashboard',
                'subtitle' => 'Monitor HRMS operations, attendance, payroll and approvals.',
                'current_date' => Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('l, d M Y h:i A'),
            ],
            'cards' => [],
            'quick_actions' => [],
            'attendance_cards' => [],
            'employee_cards' => [],
            'action_required' => [],
            'live_attendance' => [],
            'payroll_overview' => [],
            'leave_overview' => [],
            'document_overview' => [],
            'announcement_overview' => [],
            'system_health' => [],
            'charts' => [],
            'recent_activities' => [],
            'lifecycle' => [],
            'leave' => [],
            'payroll' => [],
            'documents' => [],
            'announcements' => [],
            'system' => ['apk_version' => '1.0.0'],
            'tables' => [
                'blocked_employees' => [],
                'pending_leaves' => [],
                'pending_profiles' => [],
                'pending_documents' => [],
                'live_attendance' => [],
            ]
        ];
    }

    public function rowsToArrays($rows): array
    {
        try {
            if (empty($rows)) {
                return [];
            }
            return collect($rows)->map(fn($r) => (array) $r)->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function rowToArray($row): array
    {
        try {
            return (array) $row;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function superAdminCards(array $attendance = [], array $employee = []): array
    {
        try {
            return [
                'present_today' => $attendance['present'] ?? 0,
                'absent_today' => $attendance['absent'] ?? 0,
                'late_today' => $attendance['late'] ?? 0,
                'early_logout' => $attendance['early_out'] ?? 0,
                'half_day' => $attendance['half_day'] ?? 0,
                'lwp_count' => $attendance['leave'] ?? 0,
                'punch_blocked' => $attendance['punch_blocked'] ?? 0,
                'pending_hr' => $attendance['pending_hr'] ?? 0,

                'total_employees' => $employee['total'] ?? 0,
                'active_employees' => $employee['active'] ?? 0,
                'pending_profiles' => $employee['lifecycle']['pending_profiles'] ?? 0,
                'rejected_profiles' => $employee['lifecycle']['rejected_profiles'] ?? 0,
                'interns' => $employee['lifecycle']['interns'] ?? 0,
                'probation' => $employee['lifecycle']['probation'] ?? 0,
                'permanent' => $employee['lifecycle']['permanent'] ?? 0,
                'exit_process' => $employee['lifecycle']['exit_process'] ?? 0,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function buildLiveAttendance(): array
    {
        try {
            return $this->getLiveAttendanceTable(Carbon::today(config('app.timezone', 'Asia/Kolkata'))->toDateString());
        } catch (\Throwable $e) {
            Log::error('buildLiveAttendance failed: ' . $e->getMessage());
            return [];
        }
    }

    public function buildActionRequired(): array
    {
        $actions = [];
        try {
            // 1. Pending Leaves
            $pendingLeaves = 0;
            if ($this->tableExists('leave_applications') && $this->columnExists('leave_applications', 'status')) {
                $pendingLeaves = DB::table('leave_applications')->whereRaw('LOWER(status) = ?', ['pending'])->count();
            }
            if ($pendingLeaves > 0) {
                $actions[] = [
                    'title' => 'Pending Leave Requests',
                    'subtitle' => 'Employee leaves waiting for administrative approval',
                    'count' => $pendingLeaves,
                    'icon' => 'fas fa-calendar-times',
                    'tone' => 'warning',
                    'url' => $this->routeUrl('leave-approvals.index'),
                ];
            }

            // 2. Pending Profiles
            $pendingProfiles = 0;
            if ($this->tableExists('employee_profiles') && $this->columnExists('employee_profiles', 'profile_status')) {
                $pendingProfiles = DB::table('employee_profiles')->whereIn('profile_status', ['pending', 'submitted'])->count();
            }
            if ($pendingProfiles > 0) {
                $actions[] = [
                    'title' => 'Profile Verification Pending',
                    'subtitle' => 'New employee profiles submitted for review',
                    'count' => $pendingProfiles,
                    'icon' => 'fas fa-user-clock',
                    'tone' => 'primary',
                    'url' => $this->routeUrl('hrms.employees.pending_profiles'),
                ];
            }

            // 3. Pending Documents
            $pendingDocs = 0;
            if ($this->tableExists('employee_documents_new') && $this->columnExists('employee_documents_new', 'verification_status')) {
                $pendingDocs = DB::table('employee_documents_new')->where('verification_status', 'pending')->count();
            }
            if ($pendingDocs > 0) {
                $actions[] = [
                    'title' => 'Document Verification Required',
                    'subtitle' => 'Uploaded employee documents awaiting KYC check',
                    'count' => $pendingDocs,
                    'icon' => 'fas fa-file-signature',
                    'tone' => 'danger',
                    'url' => $this->routeUrl('documents.hr.index'),
                ];
            }

            // 4. Punch Blocks / Missed Punches
            $blockedPunches = 0;
            if ($this->tableExists('attendances')) {
                $blockedPunches = DB::table('attendances')
                    ->whereDate('attendance_date', Carbon::today(config('app.timezone', 'Asia/Kolkata'))->toDateString())
                    ->where(function ($q) {
                        if ($this->columnExists('attendances', 'is_blocked')) {
                            $q->where('is_blocked', 1);
                        }
                    })->count();
            }
            if ($blockedPunches > 0) {
                $actions[] = [
                    'title' => 'Blocked Attendance Punches',
                    'subtitle' => 'Suspicious or auto-blocked punches today',
                    'count' => $blockedPunches,
                    'icon' => 'fas fa-user-lock',
                    'tone' => 'danger',
                    'url' => $this->routeUrl('attendances.pending-approval'),
                ];
            }

            // 5. Probation Ending Soon Action Required
            $probationSoon = $this->probationEndingSoonCount();
            if ($probationSoon > 0) {
                $actions[] = [
                    'title' => 'Probation Ending Soon',
                    'subtitle' => 'Employees require confirmation or extension decision',
                    'count' => $probationSoon,
                    'icon' => 'fas fa-hourglass-half',
                    'tone' => 'warning',
                    'url' => $this->routeUrl('hrms.employees.probation_internship'),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('buildActionRequired failed: ' . $e->getMessage());
        }
        return $actions;
    }

    public function buildRecentActivities(): array
    {
        try {
            $col = $this->recentActivities();
            return collect($col)->map(function ($act) {
                $act = (array) $act;
                if (!empty($act['time'])) {
                    try {
                        $act['created_at'] = Carbon::parse($act['time'])->toIso8601String();
                    } catch (\Throwable $e) {
                        $act['created_at'] = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->toIso8601String();
                    }
                } else {
                    $act['created_at'] = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->toIso8601String();
                }
                return $act;
            })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function buildCharts(): array
    {
        try {
            return [
                'monthly_attendance' => $this->monthlyAttendanceTrend(),
                'employee_lifecycle' => $this->employeeLifecycleDistributionChart(),
                'leave_distribution' => $this->leaveDistributionChartData(),
            ];
        } catch (\Throwable $e) {
            Log::error('buildCharts failed: ' . $e->getMessage());
            return [
                'monthly_attendance' => ['labels' => [], 'present' => [], 'late' => [], 'absent' => []],
                'employee_lifecycle' => ['labels' => [], 'values' => []],
                'leave_distribution' => ['labels' => [], 'values' => []],
            ];
        }
    }

    private function leaveDistributionChartData(): array
    {
        try {
            if (!$this->tableExists('leave_requests') || !$this->columnExists('leave_requests', 'status')) {
                return ['labels' => [], 'values' => []];
            }
            $rows = DB::table('leave_requests')
                ->select('status', DB::raw('COUNT(id) as total'))
                ->groupBy('status')
                ->get();
            return [
                'labels' => $rows->pluck('status')->map(fn($s) => ucfirst($s))->values()->all(),
                'values' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
            ];
        } catch (\Throwable $e) {
            return ['labels' => [], 'values' => []];
        }
    }

    private function getAttendanceCardsData(array $cards): array
    {
        return [
            ['label'=>'Present Today','value'=>$cards['present_today'] ?? 0,'icon'=>'fa-user-check','tone'=>'success','url'=>$this->routeUrl('attendances.index')],
            ['label'=>'Absent Today','value'=>$cards['absent_today'] ?? 0,'icon'=>'fa-user-times','tone'=>'danger','url'=>$this->routeUrl('attendances.index')],
            ['label'=>'Late Employees','value'=>$cards['late_today'] ?? 0,'icon'=>'fa-clock','tone'=>'warning','url'=>$this->routeUrl('attendances.index')],
            ['label'=>'Early Logout','value'=>$cards['early_logout'] ?? 0,'icon'=>'fa-sign-out-alt','tone'=>'warning','url'=>$this->routeUrl('attendances.index')],
            ['label'=>'Half Day','value'=>$cards['half_day'] ?? 0,'icon'=>'fa-adjust','tone'=>'info','url'=>$this->routeUrl('attendances.index')],
            ['label'=>'LWP','value'=>$cards['lwp_count'] ?? 0,'icon'=>'fa-ban','tone'=>'danger','url'=>$this->routeUrl('attendances.index')],
            ['label'=>'Punch Blocked','value'=>$cards['punch_blocked'] ?? 0,'icon'=>'fa-lock','tone'=>'danger','url'=>$this->routeUrl('attendances.pending-approval')],
            ['label'=>'Pending HR','value'=>$cards['pending_hr'] ?? 0,'icon'=>'fa-user-shield','tone'=>'primary','url'=>$this->routeUrl('attendances.pending-approval')],
        ];
    }

    private function getEmployeeCardsData(array $employee): array
    {
        $lifecycle = $employee['lifecycle'] ?? [];
        return [
            ['label'=>'Total Employees','value'=>$lifecycle['total'] ?? 0,'icon'=>'fa-users','tone'=>'primary'],
            ['label'=>'Active Employees','value'=>$employee['active'] ?? 0,'icon'=>'fa-user-check','tone'=>'success'],
            ['label'=>'Pending Profiles','value'=>$lifecycle['pending_profiles'] ?? 0,'icon'=>'fa-user-clock','tone'=>'warning'],
            ['label'=>'Rejected Profiles','value'=>$lifecycle['rejected_profiles'] ?? 0,'icon'=>'fa-user-times','tone'=>'danger'],
            ['label'=>'Interns','value'=>$lifecycle['interns'] ?? 0,'icon'=>'fa-user-graduate','tone'=>'primary'],
            ['label'=>'Probation','value'=>$lifecycle['probation'] ?? 0,'icon'=>'fa-hourglass-half','tone'=>'warning'],
            ['label'=>'Permanent','value'=>$lifecycle['permanent'] ?? 0,'icon'=>'fa-id-badge','tone'=>'success'],
            ['label'=>'Exit Process','value'=>$lifecycle['exit_process'] ?? 0,'icon'=>'fa-person-walking-arrow-right','tone'=>'danger'],
        ];
    }

    private function getBlockedEmployeesTable(): array
    {
        try {
            if (!$this->tableExists('attendances') || !$this->tableExists('employees_new') || !$this->tableExists('users')) {
                return [];
            }
            $query = DB::table('attendances as a')
                ->join('employees_new as e', 'e.id', '=', 'a.employee_id')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                ->whereDate('a.attendance_date', Carbon::today(config('app.timezone', 'Asia/Kolkata'))->toDateString());
            if ($this->columnExists('attendances', 'is_blocked')) {
                $query->where('a.is_blocked', 1);
            } else {
                return [];
            }
            return $query->select('u.name as employee_name', 'e.employee_code', 'a.punch_in_time', 'a.punch_out_time')->get()->map(fn($r) => (array) $r)->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getPendingLeavesTable(): array
    {
        try {
            if (!$this->tableExists('leave_applications') || !$this->tableExists('employees_new') || !$this->tableExists('users')) {
                return [];
            }
            $query = DB::table('leave_applications as l')
                ->join('employees_new as e', 'e.id', '=', 'l.employee_id')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id');
            if ($this->columnExists('leave_applications', 'status')) {
                $query->whereRaw('LOWER(l.status) = ?', ['pending']);
            } else {
                return [];
            }
            $select = ['u.name as employee_name', 'e.employee_code'];
            if ($this->columnExists('leave_applications', 'start_date')) $select[] = 'l.start_date';
            if ($this->columnExists('leave_applications', 'end_date')) $select[] = 'l.end_date';
            if ($this->columnExists('leave_applications', 'reason')) $select[] = 'l.reason';
            return $query->select($select)->get()->map(fn($r) => (array) $r)->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getPendingProfilesTable(): array
    {
        try {
            if (!$this->tableExists('employee_profiles') || !$this->tableExists('employees_new') || !$this->tableExists('users')) {
                return [];
            }
            $query = DB::table('employee_profiles as p')
                ->join('employees_new as e', 'e.id', '=', 'p.employee_id')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id');
            if ($this->columnExists('employee_profiles', 'profile_status')) {
                $query->whereIn('p.profile_status', ['pending', 'submitted']);
            } else {
                return [];
            }
            return $query->select('u.name as employee_name', 'e.employee_code', 'p.profile_status')->get()->map(fn($r) => (array) $r)->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getPendingDocumentsTable(): array
    {
        try {
            if (!$this->tableExists('employee_documents_new') || !$this->tableExists('employees_new') || !$this->tableExists('users')) {
                return [];
            }
            $query = DB::table('employee_documents_new as d')
                ->join('employees_new as e', 'e.id', '=', 'd.employee_id')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id');
            if ($this->columnExists('employee_documents_new', 'verification_status')) {
                $query->where('d.verification_status', 'pending');
            } else {
                return [];
            }
            return $query->select('u.name as employee_name', 'e.employee_code', 'd.document_name', 'd.verification_status')->get()->map(fn($r) => (array) $r)->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
