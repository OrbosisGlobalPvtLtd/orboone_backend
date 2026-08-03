<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceRegularizationM;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceViolationService
{
    public const TIMEZONE = 'Asia/Kolkata';

    public function __construct(
        private AttendanceRuleResolverService $ruleResolver,
        private AttendanceS $attendanceService
    ) {}

    /**
     * Real-time dashboard KPI summary cards metrics.
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $today = Carbon::now(self::TIMEZONE)->toDateString();

        // 1. Violations created today
        $todayViolationsQuery = DB::table('attendance_violations')
            ->whereDate('violation_date', $today);

        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $todayViolationsQuery->whereNull('deleted_at');
        }

        $totalToday = (clone $todayViolationsQuery)->count();
        $lateToday = (clone $todayViolationsQuery)->where('type', 'late_login')->count();
        $earlyToday = (clone $todayViolationsQuery)->where('type', 'early_logout')->count();
        $missedToday = (clone $todayViolationsQuery)->where('type', 'missed_punch')->count();

        // 2. Penalties (Half Day & LWP) applied
        $halfDayQuery = DB::table('attendances')
            ->where(function ($q) {
                $q->where('is_half_day', 1)
                  ->orWhere('attendance_status', 'half_day');
            });

        if (Schema::hasColumn('attendances', 'deleted_at')) {
            $halfDayQuery->whereNull('deleted_at');
        }

        $lwpQuery = DB::table('attendances')
            ->where(function ($q) {
                $q->where('is_lwp', 1)
                  ->orWhere('attendance_status', 'lwp');
            });

        if (Schema::hasColumn('attendances', 'deleted_at')) {
            $lwpQuery->whereNull('deleted_at');
        }

        if (! empty($filters['from'])) {
            $halfDayQuery->whereDate('attendance_date', '>=', $filters['from']);
            $lwpQuery->whereDate('attendance_date', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $halfDayQuery->whereDate('attendance_date', '<=', $filters['to']);
            $lwpQuery->whereDate('attendance_date', '<=', $filters['to']);
        }

        return [
            'total_today' => $totalToday,
            'late_today' => $lateToday,
            'early_today' => $earlyToday,
            'missed_today' => $missedToday,
            'half_day_applied' => $halfDayQuery->count(),
            'lwp_applied' => $lwpQuery->count(),
        ];
    }

    /**
     * Compute row-level employee active cycle counter string (e.g. "2 / 3" or "1 / 3").
     */
    public function getActiveCounterString(int $employeeId, string $date, string $violationType): string
    {
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return '0 / 3';
        }

        $policy = $this->ruleResolver->getPolicyForEmployee($employee, $date);
        $asOfDate = Carbon::parse($date, self::TIMEZONE);

        if (in_array($violationType, ['late_login', 'early_logout'], true)) {
            $limit = $policy ? (int) ($policy->combined_violation_limit ?? 3) : 3;

            $query = DB::table('attendance_violations')
                ->where('employee_id', $employeeId)
                ->whereYear('violation_date', $asOfDate->year)
                ->whereMonth('violation_date', $asOfDate->month)
                ->whereIn('type', ['late_login', 'early_logout']);

            if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            if (Schema::hasColumn('attendance_violations', 'is_consumed')) {
                $query->where(function ($q) {
                    $q->where('is_consumed', false)->orWhereNull('is_consumed');
                });
            }
            if (Schema::hasColumn('attendance_violations', 'policy_action')) {
                $query->where(function ($q) {
                    $q->whereNull('policy_action')->orWhere('policy_action', '!=', 'resolved');
                });
            }

            $count = $query->count();
            return "{$count} / {$limit}";
        } else {
            $allowed = $policy ? (int) ($policy->allowed_missed_punches ?? 2) : 2;
            $limit = $allowed + 1;

            $query = DB::table('attendance_violations')
                ->where('employee_id', $employeeId)
                ->whereYear('violation_date', $asOfDate->year)
                ->whereMonth('violation_date', $asOfDate->month)
                ->where('type', 'missed_punch');

            if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            if (Schema::hasColumn('attendance_violations', 'is_consumed')) {
                $query->where(function ($q) {
                    $q->where('is_consumed', false)->orWhereNull('is_consumed');
                });
            }
            if (Schema::hasColumn('attendance_violations', 'policy_action')) {
                $query->where(function ($q) {
                    $q->whereNull('policy_action')->orWhere('policy_action', '!=', 'resolved');
                });
            }

            $count = $query->count();
            return "{$count} / {$limit}";
        }
    }

    /**
     * Map internal keys to HR-friendly human violation label.
     */
    public function resolveHumanViolationLabel(string $type, ?string $policyAction = null): string
    {
        if ($policyAction === 'resolved') {
            return 'Resolved by Admin';
        }

        return match ($type) {
            'late_login', 'late_mark' => 'Late Login Warning',
            'early_logout' => 'Early Logout Warning',
            'missed_punch' => 'Missed Punch Warning',
            'half_day' => 'Half Day Applied',
            'lwp' => 'LWP Applied',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    /**
     * Resolve Penalty Status label and CSS badge.
     */
    public function resolvePenaltyStatus(object $violation, ?object $attendance = null): array
    {
        $source = $attendance?->attendance_source ?? '';
        $isRegularized = $source === 'regularization' || (isset($violation->status) && $violation->status === 'regularized');

        if ($isRegularized) {
            return [
                'label' => 'Regularized',
                'badge' => 'orb-badge-purple',
                'bg_color' => '#8B5CF6',
            ];
        }

        if (! empty($violation->converted_to_lwp) || (isset($violation->policy_action) && $violation->policy_action === 'lwp')) {
            return [
                'label' => 'Converted to LWP',
                'badge' => 'orb-badge-darkred',
                'bg_color' => '#991B1B',
            ];
        }

        if (! empty($violation->converted_to_half_day) || (isset($violation->policy_action) && $violation->policy_action === 'half_day')) {
            return [
                'label' => 'Converted to Half Day',
                'badge' => 'orb-badge-warning',
                'bg_color' => '#F59E0B',
            ];
        }

        if (! empty($violation->is_consumed)) {
            return [
                'label' => 'Consumed',
                'badge' => 'orb-badge-secondary',
                'bg_color' => '#64748B',
            ];
        }

        if (isset($violation->policy_action) && $violation->policy_action === 'resolved') {
            return [
                'label' => 'Resolved',
                'badge' => 'orb-badge-success',
                'bg_color' => '#10B981',
            ];
        }

        return [
            'label' => 'Active',
            'badge' => 'orb-badge-primary',
            'bg_color' => '#3B82F6',
        ];
    }

    /**
     * Read-only payload for Employee Summary Side Drawer.
     */
    public function getEmployeeAuditPayload(int $employeeId): array
    {
        $employee = Employee::with(['department', 'designation', 'profile'])->find($employeeId);
        if (! $employee) {
            return ['error' => 'Employee profile not found.'];
        }

        $today = Carbon::now(self::TIMEZONE);

        // Shift & Policy info
        $policy = $this->ruleResolver->getPolicyForEmployee($employee, $today);

        // Active counters
        $disciplineCounter = $this->getActiveCounterString($employeeId, $today->toDateString(), 'late_login');
        $missedCounter = $this->getActiveCounterString($employeeId, $today->toDateString(), 'missed_punch');

        // Violation history & timeline
        $violationsQuery = DB::table('attendance_violations')
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_violations.attendance_id')
            ->where('attendance_violations.employee_id', $employeeId)
            ->select([
                'attendance_violations.*',
                'attendances.punch_in_time',
                'attendances.punch_out_time',
                'attendances.attendance_status',
            ]);

        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $violationsQuery->whereNull('attendance_violations.deleted_at');
        }

        $violations = $violationsQuery->latest('attendance_violations.violation_date')
            ->limit(30)
            ->get();

        $timeline = [];
        foreach ($violations as $v) {
            $statusData = $this->resolvePenaltyStatus($v);
            $timeline[] = [
                'date' => Carbon::parse($v->violation_date)->format('d M Y'),
                'type' => $this->resolveHumanViolationLabel($v->type, $v->policy_action),
                'raw_type' => $v->type,
                'minutes' => (int) ($v->minutes ?? 0),
                'penalty_status' => $statusData['label'],
                'badge_class' => $statusData['badge'],
                'remarks' => $v->remarks ?? '-',
            ];
        }

        // Recent Attendances
        $recentAttQuery = DB::table('attendances')
            ->where('employee_id', $employeeId);

        if (Schema::hasColumn('attendances', 'deleted_at')) {
            $recentAttQuery->whereNull('deleted_at');
        }

        $recentAttendances = $recentAttQuery->latest('attendance_date')
            ->limit(10)
            ->get()
            ->map(function ($att) {
                return [
                    'date' => Carbon::parse($att->attendance_date)->format('d M Y'),
                    'punch_in' => $att->punch_in_time ? Carbon::parse($att->punch_in_time)->format('h:i A') : '-',
                    'punch_out' => $att->punch_out_time ? Carbon::parse($att->punch_out_time)->format('h:i A') : '-',
                    'work_minutes' => (int) ($att->total_work_minutes ?? 0),
                    'status' => ucfirst(str_replace('_', ' ', $att->attendance_status ?? 'N/A')),
                ];
            });

        // Recent Regularizations
        $recentRegQuery = DB::table('attendance_regularizations')
            ->where('employee_id', $employeeId);

        if (Schema::hasColumn('attendance_regularizations', 'deleted_at')) {
            $recentRegQuery->whereNull('attendance_regularizations.deleted_at');
        }

        $recentRegularizations = $recentRegQuery->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($reg) {
                return [
                    'type' => ucfirst(str_replace('_', ' ', $reg->request_type)),
                    'status' => ucfirst($reg->status),
                    'requested_in' => $reg->requested_punch_in ? Carbon::parse($reg->requested_punch_in)->format('h:i A') : '-',
                    'requested_out' => $reg->requested_punch_out ? Carbon::parse($reg->requested_punch_out)->format('h:i A') : '-',
                    'reason' => $reg->reason,
                    'created_at' => Carbon::parse($reg->created_at)->format('d M Y h:i A'),
                ];
            });

        $photoUrl = resolveEmployeePassportPhoto($employee->id);
        $initials = resolveEmployeeInitials($employee->id);

        return [
            'success' => true,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->display_name ?: $employee->name,
                'code' => $employee->employee_code,
                'department' => $employee->department?->name ?? 'N/A',
                'designation' => $employee->designation?->name ?? 'N/A',
                'photo_url' => $photoUrl,
                'initials' => $initials,
            ],
            'policy' => [
                'name' => $policy->policy_name ?? $policy->name ?? 'Default Shift Policy',
                'shift_type' => ucfirst(str_replace('_', ' ', $policy->shift_type ?? 'fixed')),
                'shift_start' => $this->ruleResolver->timeString($policy->shift_start_time ?? null) ?: '10:00 AM',
                'shift_end' => $this->ruleResolver->timeString($policy->shift_end_time ?? null) ?: '07:00 PM',
                'discipline_limit' => (int) ($policy->combined_violation_limit ?? 3),
                'missed_limit' => ((int) ($policy->allowed_missed_punches ?? 2)) + 1,
            ],
            'counters' => [
                'discipline' => $disciplineCounter,
                'missed_punch' => $missedCounter,
            ],
            'timeline' => $timeline,
            'recent_attendances' => $recentAttendances,
            'recent_regularizations' => $recentRegularizations,
        ];
    }

    /**
     * Read-only payload for Attendance Detail Audit Modal.
     */
    public function getAttendanceAuditDetail(int $attendanceId): array
    {
        $attendance = Attendance::with(['employee.department', 'employee.designation', 'attendanceType'])->find($attendanceId);
        if (! $attendance) {
            return ['success' => false, 'message' => 'Attendance record not found.'];
        }

        $employee = $attendance->employee;
        $dateStr = Carbon::parse($attendance->attendance_date, self::TIMEZONE)->toDateString();
        $policy = $employee ? $this->ruleResolver->getPolicyForEmployee($employee, $dateStr) : null;

        $targetOutStr = $attendance->target_punch_out_time
            ?: ($policy?->shift_end_time ?? '19:00:00');

        return [
            'success' => true,
            'attendance' => [
                'id' => $attendance->id,
                'date' => Carbon::parse($attendance->attendance_date)->format('d M Y'),
                'employee_name' => $employee?->display_name ?: 'Employee',
                'employee_code' => $employee?->employee_code ?: 'N/A',
                'department' => $employee?->department?->name ?? 'N/A',
                'punch_in' => $attendance->punch_in_time ? Carbon::parse($attendance->punch_in_time)->format('h:i A') : 'N/A',
                'punch_out' => $attendance->punch_out_time ? Carbon::parse($attendance->punch_out_time)->format('h:i A') : 'N/A',
                'target_punch_out' => $targetOutStr ? Carbon::parse($targetOutStr)->format('h:i A') : 'N/A',
                'total_work_minutes' => (int) ($attendance->total_work_minutes ?? 0),
                'late_minutes' => (int) ($attendance->late_minutes ?? 0),
                'early_out_minutes' => (int) ($attendance->early_out_minutes ?? 0),
                'status' => ucfirst(str_replace('_', ' ', $attendance->attendance_status ?? 'N/A')),
                'is_half_day' => (bool) $attendance->is_half_day,
                'half_day_reason' => $attendance->half_day_reason,
                'is_lwp' => (bool) $attendance->is_lwp,
                'lwp_reason' => $attendance->lwp_reason,
                'policy_name' => $policy->policy_name ?? $policy->name ?? 'Default Policy',
            ],
        ];
    }
}
