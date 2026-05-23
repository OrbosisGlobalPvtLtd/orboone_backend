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

        if (in_array('admin', $slugs, true)) {
            return 'custom_admin';
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
            || in_array($role, $slugs, true)
            || ($role === 'custom_admin' && in_array('admin', $slugs, true));
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
                $this->card('Probation Ending Soon', $cards['probation'], 'fas fa-hourglass-half', 'Next 30 days'),
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
        if (! $this->columnExists('employees_new', 'probation_end_date')) {
            return 0;
        }

        return DB::table('employees_new')
            ->whereBetween('probation_end_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
            ->where(function ($q) {
                if ($this->columnExists('employees_new', 'probation_status')) {
                    $q->whereIn('probation_status', ['pending', 'ongoing']);
                }
            })
            ->count();
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
                ['title' => 'Documents', 'icon' => 'fas fa-file-alt', 'routes' => ['hrms.documents.hr.index', 'hrms.employee-documents.index', 'hrms.documents.index']],
            ],
            'hr_admin' => [
                ['title' => 'Add Employee', 'icon' => 'fas fa-user-plus', 'routes' => ['hrms.employees.create', 'employees.create']],
                ['title' => 'Pending Profiles', 'icon' => 'fas fa-id-card', 'routes' => ['hrms.employees.pending_profiles']],
                ['title' => 'Attendance', 'icon' => 'fas fa-clock', 'routes' => ['attendances.index']],
                ['title' => 'Leave Approval', 'icon' => 'fas fa-plane', 'routes' => ['leave-approvals.index']],
            ],
            'finance_admin' => [
                ['title' => 'Payroll Dashboard', 'icon' => 'fas fa-chart-line', 'routes' => ['pages.payroll.dashboard']],
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
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function columnExists($table, $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
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
                    'url' => $this->routeUrl('hrms.documents.hr.index'),
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