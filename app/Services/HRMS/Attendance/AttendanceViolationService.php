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
        $month = $filters['month'] ?? null;
        $from = $filters['from_date'] ?? ($filters['from'] ?? null);
        $to = $filters['to_date'] ?? ($filters['to'] ?? null);
        $employeeId = $filters['employee_id'] ?? null;

        $violationsQuery = DB::table('attendance_violations');
        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $violationsQuery->whereNull('deleted_at');
        }

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

        if (!empty($employeeId)) {
            $violationsQuery->where('employee_id', $employeeId);
            $halfDayQuery->where('employee_id', $employeeId);
            $lwpQuery->where('employee_id', $employeeId);
        } elseif (!empty($filters['employee_ids']) && is_array($filters['employee_ids'])) {
            $violationsQuery->whereIn('employee_id', $filters['employee_ids']);
            $halfDayQuery->whereIn('employee_id', $filters['employee_ids']);
            $lwpQuery->whereIn('employee_id', $filters['employee_ids']);
        }

        if (!empty($month) && $month !== 'custom') {
            try {
                $mDate = Carbon::parse($month . '-01');
                $mStart = $mDate->copy()->startOfMonth()->toDateString();
                $mEnd = $mDate->copy()->endOfMonth()->toDateString();

                $violationsQuery->whereDate('violation_date', '>=', $mStart)
                                ->whereDate('violation_date', '<=', $mEnd);
                $halfDayQuery->whereDate('attendance_date', '>=', $mStart)
                             ->whereDate('attendance_date', '<=', $mEnd);
                $lwpQuery->whereDate('attendance_date', '>=', $mStart)
                         ->whereDate('attendance_date', '<=', $mEnd);
            } catch (\Throwable $e) {
            }
        } elseif (!empty($from) || !empty($to)) {
            if (!empty($from)) {
                try {
                    $pFrom = Carbon::parse($from)->toDateString();
                    $violationsQuery->whereDate('violation_date', '>=', $pFrom);
                    $halfDayQuery->whereDate('attendance_date', '>=', $pFrom);
                    $lwpQuery->whereDate('attendance_date', '>=', $pFrom);
                } catch (\Throwable $e) {
                    $violationsQuery->whereDate('violation_date', '>=', $from);
                    $halfDayQuery->whereDate('attendance_date', '>=', $from);
                    $lwpQuery->whereDate('attendance_date', '>=', $from);
                }
            }
            if (!empty($to)) {
                try {
                    $pTo = Carbon::parse($to)->toDateString();
                    $violationsQuery->whereDate('violation_date', '<=', $pTo);
                    $halfDayQuery->whereDate('attendance_date', '<=', $pTo);
                    $lwpQuery->whereDate('attendance_date', '<=', $pTo);
                } catch (\Throwable $e) {
                    $violationsQuery->whereDate('violation_date', '<=', $to);
                    $halfDayQuery->whereDate('attendance_date', '<=', $to);
                    $lwpQuery->whereDate('attendance_date', '<=', $to);
                }
            }
        }

        $totalViolations = (clone $violationsQuery)->count();
        $lateCount = (clone $violationsQuery)->whereIn('type', ['late_login', 'late_mark'])->count();
        $earlyCount = (clone $violationsQuery)->whereIn('type', ['early_logout', 'early_out'])->count();
        $missedCount = (clone $violationsQuery)->where('type', 'missed_punch')->count();

        return [
            'total_today' => $totalViolations,
            'late_today' => $lateCount,
            'early_today' => $earlyCount,
            'missed_today' => $missedCount,
            'half_day_applied' => $halfDayQuery->count(),
            'lwp_applied' => $lwpQuery->count(),
        ];
    }

    /**
     * Compute row-level employee active cycle counter string (e.g. "2 / 3" or "1 / 3").
     */
    
    public function getActiveCounterString(int $employeeId, string $date, string $violationType, int $limit = 3): string
    {
        $rawType = strtolower((string) $violationType);
        $canonicalType = match ($rawType) {
            'late_login', 'late_mark', 'early_out', 'early_logout' => 'discipline',
            'missed_punch' => 'missed_punch',
            'blocked_punch' => 'blocked_punch',
            default => $rawType,
        };

        if ($canonicalType === 'blocked_punch') {
            return '-';
        }

        $asOfDate = Carbon::parse($date, self::TIMEZONE);

        $query = DB::table('attendance_violations')
            ->where('employee_id', $employeeId)
            ->whereYear('violation_date', $asOfDate->year)
            ->whereMonth('violation_date', $asOfDate->month)
            ->whereDate('violation_date', '<=', $date);

        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($canonicalType === 'discipline') {
            $query->whereIn('type', ['late_login', 'late_mark', 'early_logout', 'early_out']);
        } else {
            $query->where('type', $canonicalType);
        }

        $count = $query->count();
        if ($count === 0) {
            return "0 / {$limit}";
        }

        $pos = (($count - 1) % $limit) + 1;
        return "{$pos} / {$limit}";
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
     * Penalty conversions (Converted to Half Day / LWP) are associated ONLY with the 3/3 occurrence.
     */
    public function resolvePenaltyStatus(object $violation, ?object $attendance = null, bool $isThirdOccurrence = false): array
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

        if (isset($violation->policy_action) && $violation->policy_action === 'resolved') {
            return [
                'label' => 'Resolved',
                'badge' => 'orb-badge-success',
                'bg_color' => '#10B981',
            ];
        }

        // Show penalty results (Converted to Half Day / LWP) ONLY on the 3rd occurrence (3/3)
        if ($isThirdOccurrence) {
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
        }

        if (! empty($violation->is_consumed)) {
            return [
                'label' => 'Consumed',
                'badge' => 'orb-badge-secondary',
                'bg_color' => '#64748B',
            ];
        }

        return [
            'label' => 'Active',
            'badge' => 'orb-badge-primary',
            'bg_color' => '#3B82F6',
        ];
    }

    /**
     * Compute chronological violation cycle positions and penalty statuses for a list/collection of violations.
     * Scoped month-wise so counts reset at the beginning of each calendar month.
     * Late Login and Early Logout share a combined 3-strike discipline cycle.
     */
    public function enrichViolationsWithCycles(iterable $violations): void
    {
        $employeeIds = collect($violations)->pluck('employee_id')->unique()->filter()->values();

        if ($employeeIds->isEmpty()) {
            return;
        }

        $query = DB::table('attendance_violations')
            ->whereIn('employee_id', $employeeIds);

        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $allEmpViolations = $query->orderBy('employee_id', 'asc')
            ->orderBy('violation_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $metaMap = [];
        $grouped = $allEmpViolations->groupBy('employee_id');

        foreach ($grouped as $empId => $empViolations) {
            // Group by year and month so counters reset cleanly every month!
            $byMonth = $empViolations->groupBy(function ($v) {
                return Carbon::parse($v->violation_date)->format('Y-m');
            });

            foreach ($byMonth as $yearMonth => $monthViolations) {
                $counters = [
                    'discipline' => 0, // Combined Late Login + Early Logout
                    'missed_punch' => 0,
                ];

                foreach ($monthViolations as $v) {
                    $rawType = strtolower((string) ($v->type ?? ''));
                    $canonicalType = match ($rawType) {
                        'late_login', 'late_mark', 'early_out', 'early_logout' => 'discipline',
                        'missed_punch' => 'missed_punch',
                        'blocked_punch' => 'blocked_punch',
                        default => $rawType,
                    };

                    if ($canonicalType === 'blocked_punch' || ! isset($counters[$canonicalType])) {
                        $metaMap[$v->id] = [
                            'active_counter' => '-',
                            'is_third_occurrence' => false,
                            'cycle_position' => 0,
                        ];
                        continue;
                    }

                    $counters[$canonicalType]++;
                    $seq = $counters[$canonicalType];
                    $pos = (($seq - 1) % 3) + 1; // Sequence: 1, 2, 3, 1, 2, 3...
                    $isThird = ($pos === 3);

                    $metaMap[$v->id] = [
                        'active_counter' => "{$pos} / 3",
                        'is_third_occurrence' => $isThird,
                        'cycle_position' => $pos,
                    ];
                }
            }
        }

        foreach ($violations as $row) {
            $vId = $row->id ?? null;
            $meta = $vId ? ($metaMap[$vId] ?? null) : null;

            if (! $meta) {
                $meta = [
                    'active_counter' => '-',
                    'is_third_occurrence' => false,
                    'cycle_position' => 0,
                ];
            }

            $row->active_counter = $meta['active_counter'];
            $row->is_third_occurrence = $meta['is_third_occurrence'];
            $row->cycle_position = $meta['cycle_position'];

            $statusData = $this->resolvePenaltyStatus($row, null, $meta['is_third_occurrence']);
            $row->penalty_status_label = $statusData['label'];
            $row->penalty_badge_class = $statusData['badge'];
        }
    }

    /**
     * Read-only payload for Employee Summary Side Drawer.
     */
    public function getEmployeeAuditPayload(int $employeeId, ?string $month = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        $employee = Employee::with(['department', 'designation', 'profile'])->find($employeeId);
        if (! $employee) {
            return ['error' => 'Employee profile not found.'];
        }

        $now = Carbon::now(self::TIMEZONE);
        $policy = $this->ruleResolver->getPolicyForEmployee($employee, $now);
        $disciplineLimit = (int) ($policy->combined_violation_limit ?? 3);
        $missedLimit = ((int) ($policy->allowed_missed_punches ?? 2)) + 1;

        // Resolve date filter
        if (! empty($fromDate) && ! empty($toDate)) {
            $start = Carbon::parse($fromDate, self::TIMEZONE)->startOfDay();
            $end = Carbon::parse($toDate, self::TIMEZONE)->endOfDay();
            $periodLabel = $start->format('d M Y') . ' - ' . $end->format('d M Y');
        } elseif (! empty($month) && $month !== 'all') {
            try {
                $parsed = Carbon::createFromFormat('Y-m', $month, self::TIMEZONE);
                $start = $parsed->copy()->startOfMonth();
                $end = $parsed->copy()->endOfMonth();
                $periodLabel = $parsed->format('F Y');
            } catch (\Throwable $e) {
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $periodLabel = $now->format('F Y');
            }
        } else {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $periodLabel = $now->format('F Y');
        }

        // Active counters for this month/period
        $discQuery = DB::table('attendance_violations')
            ->where('employee_id', $employeeId)
            ->whereBetween('violation_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('type', ['late_login', 'late_mark', 'early_logout', 'early_out']);
        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $discQuery->whereNull('deleted_at');
        }
        $discCount = $discQuery->count();
        $discCounter = $discCount === 0 ? "0 / {$disciplineLimit}" : ((($discCount - 1) % $disciplineLimit) + 1) . " / {$disciplineLimit}";

        $missedQuery = DB::table('attendance_violations')
            ->where('employee_id', $employeeId)
            ->whereBetween('violation_date', [$start->toDateString(), $end->toDateString()])
            ->where('type', 'missed_punch');
        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $missedQuery->whereNull('deleted_at');
        }
        $missedCount = $missedQuery->count();
        $missedCounter = $missedCount === 0 ? "0 / {$missedLimit}" : ((($missedCount - 1) % $missedLimit) + 1) . " / {$missedLimit}";

        // Violation history & timeline strictly within this period
        $violationsQuery = DB::table('attendance_violations')
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_violations.attendance_id')
            ->where('attendance_violations.employee_id', $employeeId)
            ->whereBetween('attendance_violations.violation_date', [$start->toDateString(), $end->toDateString()])
            ->select([
                'attendance_violations.*',
                'attendances.punch_in_time',
                'attendances.punch_out_time',
                'attendances.attendance_status',
            ]);

        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $violationsQuery->whereNull('attendance_violations.deleted_at');
        }

        $violations = $violationsQuery->orderBy('attendance_violations.violation_date', 'desc')
            ->orderBy('attendance_violations.id', 'desc')
            ->get();

        $this->enrichViolationsWithCycles($violations);

        $timeline = [];
        foreach ($violations as $v) {
            $statusData = $this->resolvePenaltyStatus($v);
            $timeline[] = [
                'date' => Carbon::parse($v->violation_date)->format('d M Y'),
                'type' => $this->resolveHumanViolationLabel($v->type, $v->policy_action),
                'raw_type' => $v->type,
                'minutes' => (int) ($v->minutes ?? 0),
                'active_counter' => $v->active_counter ?? '-',
                'penalty_status' => $statusData['label'],
                'badge_class' => $statusData['badge'],
                'remarks' => $v->remarks ?? '-',
            ];
        }

        // Recent Attendances in this period
        $recentAttQuery = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()]);

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
            'period_label' => $periodLabel,
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
                'discipline_limit' => $disciplineLimit,
                'missed_limit' => $missedLimit,
            ],
            'counters' => [
                'discipline' => $discCounter,
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
                'designation' => $employee?->designation?->name ?? 'N/A',
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
                'policy_name' => $policy->policy_name ?? $policy->name ?? 'General Shift Policy',
            ],
        ];
    }
}
