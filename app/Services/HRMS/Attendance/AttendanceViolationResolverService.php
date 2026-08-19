<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceViolationResolverService
{
    public const TIMEZONE = 'Asia/Kolkata';

    public function __construct(
        private AttendanceRuleResolverService $ruleResolver
    ) {}

    /**
     * Helper to resolve standard 7-character cycle month string (YYYY-MM).
     */
    public function resolveCycleMonth(string $date): string
    {
        return Carbon::parse($date, self::TIMEZONE)->format('Y-m');
    }

    /**
     * Create or update a single attendance violation record.
     */
    public function recordOrSyncViolation(Attendance $attendance, string $type, array $payload = []): ?AttendanceViolationM
    {
        if (! $attendance->employee_id || ! $attendance->attendance_date) {
            return null;
        }

        $employee = Employee::find($attendance->employee_id);
        if (!app(\App\Services\HRMS\Employee\EmployeeEligibilityS::class)->canUseAttendance($employee)) {
            return null;
        }

        $date = Carbon::parse($attendance->attendance_date, self::TIMEZONE)->toDateString();

        // Approved Leave Bypass (First Half / Second Half / Full Leave)
        $approvedLeave = $this->ruleResolver->getApprovedLeaveOnDate($employee, $date);
        if ($approvedLeave) {
            return null;
        }

        $cycleMonth = $this->resolveCycleMonth($date);

        // Normalize violation type name
        $normalizedType = match ($type) {
            'late', 'late_login' => 'late_login',
            'early', 'early_out', 'early_logout' => 'early_logout',
            'missed_punch' => 'missed_punch',
            'blocked_punch' => 'blocked_punch',
            default => $type,
        };

        $existing = AttendanceViolationM::where('attendance_id', $attendance->id)
            ->where('type', $normalizedType)
            ->first();

        $createPayload = array_merge([
            'employee_id' => $attendance->employee_id,
            'attendance_id' => $attendance->id,
            'type' => $normalizedType,
            'violation_date' => $date,
            'cycle_month' => $cycleMonth,
            'violation_count' => 1,
            'status' => 'pending',
            'minutes' => $payload['minutes'] ?? 0,
            'is_consumed' => false,
            'converted_to_half_day' => false,
            'converted_to_lwp' => false,
        ], $payload);

        if ($existing) {
            $updateFields = [
                'minutes' => $payload['minutes'] ?? $existing->minutes,
                'source' => $payload['source'] ?? $existing->source,
                'remarks' => $payload['remarks'] ?? $existing->remarks,
            ];
            if (isset($payload['policy_action'])) {
                $updateFields['policy_action'] = $payload['policy_action'];
            }
            $existing->update($updateFields);
            return $existing;
        }

        return AttendanceViolationM::create($createPayload);
    }

    /**
     * Remove or mark violation resolved if condition is cleared.
     */
    public function clearViolation(Attendance $attendance, string $type): bool
    {
        $normalizedType = match ($type) {
            'late', 'late_login' => 'late_login',
            'early', 'early_out', 'early_logout' => 'early_logout',
            'missed_punch' => 'missed_punch',
            'blocked_punch' => 'blocked_punch',
            default => $type,
        };

        $violation = AttendanceViolationM::where('attendance_id', $attendance->id)
            ->where('type', $normalizedType)
            ->first();

        if (! $violation) {
            return false;
        }

        if (! $violation->is_consumed && $violation->status !== 'converted') {
            $violation->update([
                'status' => 'resolved',
                'policy_action' => 'resolved',
                'resolved_at' => now(),
            ]);

            if ($attendance->employee_id && $attendance->attendance_date) {
                $this->evaluateViolationsAndApplyPenalties($attendance->employee, (string) $attendance->attendance_date);
            }
            return true;
        }

        return false;
    }

    /**
     * Main Cycle Evaluation Engine:
     * Groups Late + Early Logout into Discipline Bucket.
     * Missed Punch into separate Missed Punch Bucket.
     * Evaluates month-scoped unconsumed count against policy limit.
     */
    public function evaluateViolationsAndApplyPenalties(Employee $employee, string $date, ?Attendance $triggerAttendance = null): array
    {
        $cycleMonth = $this->resolveCycleMonth($date);
        $policy = $this->ruleResolver->getPolicyForEmployee($employee, $date);

        $disciplineEnabled = $policy ? (bool) ($policy->combined_violation_enabled ?? true) : true;
        $disciplineLimit = $policy ? (int) ($policy->combined_violation_limit ?? 3) : 3;
        $disciplineAction = strtolower((string) ($policy->combined_violation_action ?? 'half_day'));

        $missedPunchLimit = $policy ? (int) ($policy->missed_punch_lwp_after ?? 3) : 3;
        $missedPunchAction = strtolower((string) ($policy->missed_punch_action ?? 'lwp'));
        $monthAttendances = Attendance::where('employee_id', $employee->id)
            ->whereRaw("DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$cycleMonth])
            ->get();
        foreach ($monthAttendances as $mAtt) {
            $approvedLeaveOnAttDate = $this->ruleResolver->getApprovedLeaveOnDate($employee, (string) $mAtt->attendance_date);
            if ($approvedLeaveOnAttDate) {
                continue;
            }
            if ($mAtt->is_late || (int) $mAtt->late_minutes > 0) {
                $this->recordOrSyncViolation($mAtt, 'late_login', [
                    'minutes' => (int) $mAtt->late_minutes,
                    'source' => $mAtt->attendance_source ?: 'system',
                    'policy_action' => 'late_mark',
                    'remarks' => $mAtt->punch_in_note ?: 'Late login detected.',
                ]);
            }
            if ($mAtt->is_early_out || (int) $mAtt->early_out_minutes > 0) {
                $this->recordOrSyncViolation($mAtt, 'early_logout', [
                    'minutes' => (int) $mAtt->early_out_minutes,
                    'source' => $mAtt->attendance_source ?: 'system',
                    'policy_action' => 'early_logout',
                    'remarks' => $mAtt->punch_out_note ?: 'Early logout detected.',
                ]);
            }
            if ($mAtt->missed_punch || $mAtt->is_missed_punch || $mAtt->attendance_status === 'missed_punch') {
                $this->recordOrSyncViolation($mAtt, 'missed_punch', [
                    'minutes' => 0,
                    'source' => $mAtt->attendance_source ?: 'system_auto',
                    'policy_action' => $mAtt->is_lwp ? 'lwp' : 'warning',
                    'converted_to_lwp' => (bool) $mAtt->is_lwp,
                    'remarks' => $mAtt->missed_punch_reason ?: 'Missed punch detected.',
                ]);
            }
        }

        // -------------------------------------------------------------
        // 1. DISCIPLINE BUCKET (Late Login + Early Logout)
        // -------------------------------------------------------------
        if ($disciplineEnabled && $disciplineLimit > 0) {
            $activeDisciplineViolations = AttendanceViolationM::where('employee_id', $employee->id)
                ->where('cycle_month', $cycleMonth)
                ->whereIn('type', ['late_login', 'early_logout'])
                ->where('is_consumed', false)
                ->whereNotIn('status', ['resolved', 'converted'])
                ->orderBy('violation_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $disciplineCount = $activeDisciplineViolations->sum(fn ($v) => (int) ($v->violation_count ?? 1));

            if ($disciplineCount >= $disciplineLimit) {
                // Take exact chunk of violations up to threshold to consume
                $chunkToConsume = collect();
                $accumulated = 0;
                foreach ($activeDisciplineViolations as $viol) {
                    $chunkToConsume->push($viol);
                    $accumulated += (int) ($viol->violation_count ?? 1);
                    if ($accumulated >= $disciplineLimit) {
                        break;
                    }
                }

                // Determine target attendance for penalty
                $lastViolation = $chunkToConsume->last();
                $targetAttendance = $lastViolation ? ($lastViolation->attendance ?: Attendance::find($lastViolation->attendance_id)) : $triggerAttendance;

                if ($targetAttendance) {
                    $reason = "{$disciplineLimit} attendance violations completed. Includes Late/Early Logout. (Attendance Discipline limit reached)";

                    if ($disciplineAction === 'lwp') {
                        $targetAttendance->update([
                            'is_lwp' => true,
                            'attendance_status' => 'lwp',
                            'lwp_reason' => $reason,
                        ]);
                        $typeObj = AttendanceTypeM::where('code', 'lwp')->first();
                        if ($typeObj) {
                            $targetAttendance->update(['attendance_type_id' => $typeObj->id]);
                        }
                    } else {
                        $targetAttendance->update([
                            'is_half_day' => true,
                            'attendance_status' => 'half_day',
                            'half_day_reason' => $reason,
                        ]);
                        $typeObj = AttendanceTypeM::where('code', 'half_day')->first();
                        if ($typeObj) {
                            $targetAttendance->update(['attendance_type_id' => $typeObj->id]);
                        }
                    }
                    $triggerAttendance?->refresh();

                    // Mark consumed & resolved
                    foreach ($chunkToConsume as $violToMark) {
                        $violToMark->update([
                            'is_consumed' => true,
                            'consumed_at' => now(),
                            'status' => 'converted',
                            'resolved_at' => now(),
                            'converted_to_half_day' => ($disciplineAction === 'half_day'),
                            'converted_to_lwp' => ($disciplineAction === 'lwp'),
                            'penalty_attendance_id' => $targetAttendance->id,
                        ]);
                    }
                }
            }
        }

        // -------------------------------------------------------------
        // 2. MISSED PUNCH BUCKET
        // -------------------------------------------------------------
        if ($missedPunchLimit > 0) {
            $activeMissedViolations = AttendanceViolationM::where('employee_id', $employee->id)
                ->where('cycle_month', $cycleMonth)
                ->where('type', 'missed_punch')
                ->where('is_consumed', false)
                ->whereNotIn('status', ['resolved', 'converted'])
                ->orderBy('violation_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $missedCount = $activeMissedViolations->sum(fn ($v) => (int) ($v->violation_count ?? 1));

            if ($missedCount >= $missedPunchLimit) {
                $chunkToConsumeMissed = collect();
                $accumulatedMissed = 0;
                foreach ($activeMissedViolations as $viol) {
                    $chunkToConsumeMissed->push($viol);
                    $accumulatedMissed += (int) ($viol->violation_count ?? 1);
                    if ($accumulatedMissed >= $missedPunchLimit) {
                        break;
                    }
                }

                $lastMissedViolation = $chunkToConsumeMissed->last();
                $targetAttendanceMissed = $lastMissedViolation ? ($lastMissedViolation->attendance ?: Attendance::find($lastMissedViolation->attendance_id)) : $triggerAttendance;

                if ($targetAttendanceMissed) {
                    $reason = "{$missedPunchLimit} missed punch violations completed.";

                    if ($missedPunchAction === 'half_day') {
                        $targetAttendanceMissed->update([
                            'is_half_day' => true,
                            'attendance_status' => 'half_day',
                            'half_day_reason' => $reason,
                        ]);
                        $typeObj = AttendanceTypeM::where('code', 'half_day')->first();
                        if ($typeObj) {
                            $targetAttendanceMissed->update(['attendance_type_id' => $typeObj->id]);
                        }
                    } else {
                        $targetAttendanceMissed->update([
                            'is_lwp' => true,
                            'attendance_status' => 'lwp',
                            'lwp_reason' => $reason,
                        ]);
                        $typeObj = AttendanceTypeM::where('code', 'lwp')->first();
                        if ($typeObj) {
                            $targetAttendanceMissed->update(['attendance_type_id' => $typeObj->id]);
                        }
                    }
                    $triggerAttendance?->refresh();

                    foreach ($chunkToConsumeMissed as $violToMark) {
                        $violToMark->update([
                            'is_consumed' => true,
                            'consumed_at' => now(),
                            'status' => 'converted',
                            'resolved_at' => now(),
                            'converted_to_half_day' => ($missedPunchAction === 'half_day'),
                            'converted_to_lwp' => ($missedPunchAction === 'lwp'),
                            'penalty_attendance_id' => $targetAttendanceMissed->id,
                        ]);
                    }
                }
            }
        }

        return $this->getEmployeeViolationSummary($employee, $date);
    }

    /**
     * Get active counter summary for an employee for a specific month.
     */
    public function getEmployeeViolationSummary(Employee $employee, string $date): array
    {
        $cycleMonth = $this->resolveCycleMonth($date);
        $policy = $this->ruleResolver->getPolicyForEmployee($employee, $date);

        $disciplineLimit = $policy ? (int) ($policy->combined_violation_limit ?? 2) : 2;
        $missedPunchLimit = $policy ? (int) ($policy->missed_punch_lwp_after ?? 3) : 3;

        $disciplineActiveCount = (int) AttendanceViolationM::where('employee_id', $employee->id)
            ->where('cycle_month', $cycleMonth)
            ->whereIn('type', ['late_login', 'early_logout'])
            ->where('is_consumed', false)
            ->whereNotIn('status', ['resolved', 'converted'])
            ->sum('violation_count');

        $missedActiveCount = (int) AttendanceViolationM::where('employee_id', $employee->id)
            ->where('cycle_month', $cycleMonth)
            ->where('type', 'missed_punch')
            ->where('is_consumed', false)
            ->whereNotIn('status', ['resolved', 'converted'])
            ->sum('violation_count');

        return [
            'cycle_month' => $cycleMonth,
            'discipline' => [
                'count' => $disciplineActiveCount,
                'limit' => $disciplineLimit,
                'remaining' => max(0, $disciplineLimit - $disciplineActiveCount),
            ],
            'missed_punch' => [
                'count' => $missedActiveCount,
                'limit' => $missedPunchLimit,
                'remaining' => max(0, $missedPunchLimit - $missedActiveCount),
            ],
        ];
    }

    /**
     * Rebuild and re-evaluate all cycles for an employee in a given month.
     */
    public function rebuildEmployeeViolationCycles(int $employeeId, string $dateOrMonth): void
    {
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return;
        }

        $cycleMonth = strlen($dateOrMonth) === 7 ? $dateOrMonth : $this->resolveCycleMonth($dateOrMonth);

        // Fetch all attendance records for employee in that month ordered by date
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereRaw("DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$cycleMonth])
            ->orderBy('attendance_date', 'asc')
            ->get();

        // 1. Reset penalty states on attendances triggered by violations
        foreach ($attendances as $att) {
            if ($att->half_day_reason && (str_contains($att->half_day_reason, 'violations completed') || str_contains($att->half_day_reason, 'Attendance Discipline'))) {
                $att->update([
                    'is_half_day' => false,
                    'half_day_reason' => null,
                ]);
                $presentType = AttendanceTypeM::where('code', 'present')->first();
                if ($presentType) {
                    $att->update([
                        'attendance_status' => 'present',
                        'attendance_type_id' => $presentType->id,
                    ]);
                }
            }
            if ($att->lwp_reason && (str_contains($att->lwp_reason, 'missed punch') || str_contains($att->lwp_reason, 'Missed Punch'))) {
                $att->update([
                    'is_lwp' => false,
                    'lwp_reason' => null,
                ]);
                $presentType = AttendanceTypeM::where('code', 'present')->first();
                if ($presentType) {
                    $att->update([
                        'attendance_status' => 'present',
                        'attendance_type_id' => $presentType->id,
                    ]);
                }
            }
        }

        // 2. Un-consume active non-resolved violations
        AttendanceViolationM::where('employee_id', $employeeId)
            ->where('cycle_month', $cycleMonth)
            ->where('status', '!=', 'resolved')
            ->update([
                'is_consumed' => false,
                'consumed_at' => null,
                'status' => 'pending',
                'resolved_at' => null,
                'converted_to_half_day' => false,
                'converted_to_lwp' => false,
                'penalty_attendance_id' => null,
            ]);

        // 3. Sequentially evaluate each attendance date
        $attendanceService = app(AttendanceS::class);
        foreach ($attendances as $att) {
            $attendanceService->syncAttendanceViolations($att);
            $this->evaluateViolationsAndApplyPenalties($employee, (string) $att->attendance_date, $att);
        }
    }
}
