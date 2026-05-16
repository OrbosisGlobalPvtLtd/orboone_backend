<?php

namespace App\Services\HRMS\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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

    public function dashboardData(string $role, $user): array
    {
        $role = isset(self::ROLE_PRIORITY[$role]) ? $role : 'employee';

        $data = [
            'role' => $role,
            'role_title' => self::ROLE_PRIORITY[$role]['title'],
            'user_name' => $user->name ?? 'User',
            'today_label' => now()->format('d M Y'),
            'cards' => [],
            'quick_actions' => [],
            'charts' => [
                'daily' => [],
                'monthly' => $this->monthlyAttendanceTrend(),
                'departments' => $this->departmentEmployeesChart(),
            ],
            'recent_activities' => $this->recentActivities(),
            'empty_message' => null,
        ];

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

        return array_merge($data, $this->hrmsAdminData($role));
    }

    private function hrmsAdminData(string $role): array
    {
        $employee = $this->employeeStats();
        $attendance = $this->attendanceStatsForDate(today()->toDateString(), null, $employee['active']);
        $leave = $this->leaveStats();
        $payroll = $this->payrollStats();
        $documents = $this->documentStats();
        $announcements = $this->announcementStats();

        $cards = [
            $this->card('Total Employees', $employee['total'], 'fas fa-users', 'All employees in HRMS'),
            $this->card('Active Employees', $employee['active'], 'fas fa-user-check', 'Currently active workforce'),
            $this->card('Pending Profiles', $employee['pending_profiles'], 'fas fa-id-card', 'Profile completion pending'),
            $this->card('Probation / Internship', $employee['probation_internship'], 'fas fa-user-clock', 'Lifecycle follow-up'),
            $this->card('Exit Employees', $employee['exit'], 'fas fa-user-slash', 'Resigned, terminated, or inactive'),
            $this->card('Pending Leave', $leave['pending'], 'fas fa-plane-departure', 'Leave approvals waiting'),
            $this->card('Payroll Status', $payroll['current_status'], 'fas fa-file-invoice-dollar', 'Current month payroll'),
            $this->card('Documents Pending', $documents['pending'], 'fas fa-file-signature', 'Verification queue'),
            $this->card('Announcements', $announcements['total'], 'fas fa-bullhorn', 'Published announcements'),
        ];
        array_splice($cards, 5, 0, $this->attendanceAdminCards($attendance));

        if ($role === 'hr_admin') {
            $cards = [
                $this->card('Employee Total', $employee['total'], 'fas fa-users', 'HRMS employee base'),
                $this->card('Active Employees', $employee['active'], 'fas fa-user-check', 'Active workforce'),
                $this->card('Pending Onboarding', $employee['pending_profiles'], 'fas fa-id-badge', 'Profiles needing review'),
                $this->card('Probation Ending Soon', $employee['probation_ending_soon'], 'fas fa-hourglass-half', 'Next 30 days'),
                $this->card('Leave Pending', $leave['pending'], 'fas fa-calendar-alt', 'Approvals waiting'),
                $this->card('Documents Pending', $documents['pending'], 'fas fa-folder-open', 'Document approval queue'),
                $this->card('Announcements', $announcements['total'], 'fas fa-bullhorn', 'Communication stats'),
            ];
            array_splice($cards, 4, 0, $this->attendanceAdminCards($attendance));
        }

        return [
            'cards' => $cards,
            'attendance_today' => $attendance,
            'employee_lifecycle' => $employee['lifecycle'],
            'quick_actions' => $this->quickActionsFor($role),
            'payroll' => $payroll,
            'documents' => $documents,
            'leave' => $leave,
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
            return [
                'total' => 0,
                'active' => 0,
                'pending_profiles' => 0,
                'probation_internship' => 0,
                'probation_ending_soon' => 0,
                'exit' => 0,
                'lifecycle' => [],
            ];
        }

        $total = DB::table('employees_new')->count();
        $active = $this->activeEmployeesQuery()->count();
        $pendingProfiles = $this->pendingProfilesCount();
        $probationInternship = $this->probationInternshipCount();
        $probationEndingSoon = $this->probationEndingSoonCount();
        $exit = $this->exitEmployeesCount();
        $lifecycle = $this->lifecycleCounts();

        return compact(
            'total',
            'active',
            'pendingProfiles',
            'probationInternship',
            'probationEndingSoon',
            'exit'
        ) + [
            'pending_profiles' => $pendingProfiles,
            'probation_internship' => $probationInternship,
            'probation_ending_soon' => $probationEndingSoon,
            'lifecycle' => $lifecycle,
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

        if ($this->tableExists('leave_applications') && $this->columnExists('leave_applications', 'status')) {
            $pending += DB::table('leave_applications')->whereRaw('LOWER(status) = ?', ['pending'])->count();
        }

        if ($this->tableExists('leave_requests') && $this->columnExists('leave_requests', 'status')) {
            $pending += DB::table('leave_requests')->whereRaw('LOWER(status) = ?', ['pending'])->count();
        }

        return ['pending' => $pending];
    }

    private function payrollStats(): array
    {
        $month = (int) now()->month;
        $year = (int) now()->year;
        $statusRows = collect();
        $status = 'Not Run';

        if ($this->tableExists('payrolls')) {
            $statusRows = DB::table('payrolls')
                ->where('month', $month)
                ->where('year', $year)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->get();

            $status = optional($statusRows->sortByDesc('total')->first())->status ?: 'Not Run';
        }

        return [
            'current_status' => $status,
            'status_breakdown' => $statusRows,
            'salary_estimate' => $this->activeSalaryEstimate(),
            'payslips_generated' => $this->tableExists('payslips')
                ? DB::table('payslips')->where('month', $month)->where('year', $year)->count()
                : 0,
            'fnf_pending' => $this->fnfPendingCount(),
            'claims_pending' => $this->tableExists('claims') && $this->columnExists('claims', 'status')
                ? DB::table('claims')->whereRaw('LOWER(status) = ?', ['pending'])->count()
                : 0,
            'salary_structures' => $this->tableExists('salary_structures') ? DB::table('salary_structures')->count() : 0,
        ];
    }

    private function documentStats(): array
    {
        $pending = 0;

        if ($this->tableExists('employee_documents_new') && $this->columnExists('employee_documents_new', 'verification_status')) {
            $pending += DB::table('employee_documents_new')->where('verification_status', 'pending')->count();
        }

        if ($this->tableExists('employee_documents') && $this->columnExists('employee_documents', 'status')) {
            $pending += DB::table('employee_documents')->whereRaw('LOWER(status) = ?', ['pending'])->count();
        }

        return ['pending' => $pending];
    }

    private function announcementStats(): array
    {
        return ['total' => $this->tableExists('announcements') ? DB::table('announcements')->count() : 0];
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
                ['label' => 'Add Employee', 'icon' => 'fas fa-user-plus', 'route' => 'hrms.employees.create'],
                ['label' => 'Pending Profiles', 'icon' => 'fas fa-id-card', 'route' => 'hrms.employees.pending_profiles'],
                ['label' => 'Attendance', 'icon' => 'fas fa-clock', 'route' => 'attendances.index'],
                ['label' => 'Leave Approval', 'icon' => 'fas fa-plane', 'route' => 'leave-approvals.index'],
                ['label' => 'Documents', 'icon' => 'fas fa-folder-open', 'route' => 'hrms.documents.employee.index'],
            ],
            'hr_admin' => [
                ['label' => 'Add Employee', 'icon' => 'fas fa-user-plus', 'route' => 'hrms.employees.create'],
                ['label' => 'Pending Profiles', 'icon' => 'fas fa-id-card', 'route' => 'hrms.employees.pending_profiles'],
                ['label' => 'Attendance', 'icon' => 'fas fa-clock', 'route' => 'attendances.index'],
                ['label' => 'Leave Approval', 'icon' => 'fas fa-plane', 'route' => 'leave-approvals.index'],
                ['label' => 'Documents', 'icon' => 'fas fa-folder-open', 'route' => 'hrms.documents.employee.index'],
            ],
            'finance_admin' => [
                ['label' => 'Payroll Dashboard', 'icon' => 'fas fa-chart-line', 'route' => 'pages.payroll.dashboard'],
                ['label' => 'Run Payroll', 'icon' => 'fas fa-play-circle', 'route' => 'pages.payroll.payrollrun'],
                ['label' => 'Payslips', 'icon' => 'fas fa-file-pdf', 'route' => 'pages.payroll.payslips'],
                ['label' => 'FNF Pending', 'icon' => 'fas fa-user-minus', 'route' => 'pages.payroll.fnfpending'],
            ],
            'project_admin' => [
                ['label' => 'Task Tracking', 'icon' => 'fas fa-tasks', 'route' => 'project_management.tasks.index'],
                ['label' => 'Create Task', 'icon' => 'fas fa-plus-circle', 'route' => 'project_management.tasks.create'],
                ['label' => 'My Tasks', 'icon' => 'fas fa-user-check', 'route' => 'project_management.tasks.my'],
            ],
            'operations_admin' => [
                ['label' => 'Daily Attendance', 'icon' => 'fas fa-calendar-day', 'route' => 'attendances.daily'],
                ['label' => 'Pending Approval', 'icon' => 'fas fa-user-shield', 'route' => 'attendances.pending-approval'],
                ['label' => 'Monthly Report', 'icon' => 'fas fa-chart-bar', 'route' => 'attendances.monthly-report'],
                ['label' => 'Export Report', 'icon' => 'fas fa-file-export', 'route' => 'attendances.export-pdf'],
            ],
            'custom_admin' => [
                ['label' => 'Dashboard', 'icon' => 'fas fa-th-large', 'route' => 'dashboard'],
            ],
            'employee' => [
                ['label' => 'Complete Profile', 'icon' => 'fas fa-id-card', 'route' => 'profile.index'],
                ['label' => 'Punch In/Out', 'icon' => 'fas fa-fingerprint', 'route' => 'attendances.index'],
                ['label' => 'Apply Leave', 'icon' => 'fas fa-plane-departure', 'route' => 'leave-requests.create'],
                ['label' => 'Upload Document', 'icon' => 'fas fa-upload', 'route' => 'hrms.documents.self.index'],
                ['label' => 'View Payslip', 'icon' => 'fas fa-file-invoice', 'route' => 'pages.payroll.salaryslip.form'],
            ],
        ];

        return collect($actions[$role] ?? [])->map(function ($action) {
            $action['url'] = $this->routeUrl($action['route']);

            return $action;
        })->all();
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
}
