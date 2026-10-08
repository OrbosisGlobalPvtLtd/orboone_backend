<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Services\HRMS\Employee\EmployeeEligibilityS;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AttendanceViolationResolverService
{
    public const TIMEZONE = 'Asia/Kolkata';

    private static bool $isRebuildingCycles = false;

    public function __construct(
        private AttendanceRuleResolverService $ruleResolver,
        private ?NotificationS $notificationService = null,
        private ?EmployeeEligibilityS $eligibilityService = null
    ) {
        $this->notificationService = $notificationService ?: app(NotificationS::class);
        $this->eligibilityService = $eligibilityService ?: app(EmployeeEligibilityS::class);
    }

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
        if (! $employee || ! $this->eligibilityService->canUseAttendance($employee)) {
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

        $violation->update([
            'status' => 'resolved',
            'policy_action' => 'resolved',
            'is_consumed' => false,
            'consumed_at' => null,
            'converted_to_half_day' => false,
            'converted_to_lwp' => false,
            'penalty_attendance_id' => null,
            'resolved_at' => now(),
        ]);

        if ($normalizedType === 'missed_punch') {
            $attendance->update([
                'missed_punch' => 0,
                'is_missed_punch' => 0,
            ]);
        }

        if (! self::$isRebuildingCycles && $attendance->employee_id && $attendance->attendance_date) {
            $this->rebuildEmployeeViolationCycles($attendance->employee_id, (string) $attendance->attendance_date);
        }
        return true;
    }

    /**
     * Main Cycle Evaluation Engine:
     * Groups Late + Early Logout into Discipline Bucket.
     * Missed Punch into separate Missed Punch Bucket.
     * Evaluates month-scoped unconsumed count against policy limit.
     */
    public function evaluateViolationsAndApplyPenalties(Employee $employee, string $date, ?Attendance $triggerAttendance = null): array
    {
        if (! $this->eligibilityService->canUseAttendance($employee)) {
            return $this->getEmployeeViolationSummary($employee, $date);
        }

        $cycleMonth = $this->resolveCycleMonth($date);
        $policy = $this->ruleResolver->getPolicyForEmployee($employee, $date);

        $disciplineEnabled = $policy ? (bool) ($policy->combined_violation_enabled ?? true) : true;
        $disciplineLimit = $policy ? (int) ($policy->combined_violation_limit ?? 3) : 3;
        $disciplineAction = strtolower((string) ($policy->combined_violation_action ?? 'half_day'));

        $missedPunchLimit = $policy ? (int) ($policy->missed_punch_lwp_after ?? 3) : 3;
        $missedPunchAction = strtolower((string) ($policy->missed_punch_action ?? 'lwp'));

        // -------------------------------------------------------------
        // 1. DISCIPLINE BUCKET (Late Login + Early Logout)
        // -------------------------------------------------------------
        if ($disciplineEnabled && $disciplineLimit > 0) {
            $activeDisciplineViolations = AttendanceViolationM::where('employee_id', $employee->id)
                ->where('cycle_month', $cycleMonth)
                ->whereIn('type', ['late_login', 'early_logout'])
                ->where('is_consumed', false)
                ->whereNotIn('status', ['resolved', 'regularized', 'converted'])
                ->where(function ($q) {
                    $q->whereNull('policy_action')->orWhere('policy_action', '!=', 'resolved');
                })
                ->orderBy('violation_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $disciplineCount = $activeDisciplineViolations->sum(fn ($v) => (int) ($v->violation_count ?? 1));

            // Send warning notification on reaching warning quota
            if ($disciplineCount === ($disciplineLimit - 1) && ! empty($employee->user_id)) {
                $cacheKey = "att_discipline_warning_2:{$employee->id}:{$cycleMonth}";
                if (Cache::add($cacheKey, 1, now()->endOfMonth())) {
                    try {
                        $actionLabel = ($disciplineAction === 'lwp') ? 'LWP' : 'Half Day';
                        $warningLimit = $disciplineLimit - 1;
                        $this->notificationService->notifyUser(
                            (int) $employee->user_id,
                            "Attendance Violation Warning",
                            "You have reached {$disciplineCount}/{$warningLimit} Late or Early Logout warnings this month. 3rd violation will result in {$actionLabel}.",
                            [
                                'type' => 'attendance_violation_warning',
                                'employee_id' => $employee->id,
                                'cycle_month' => $cycleMonth,
                                'violation_category' => 'discipline',
                                'active_count' => $disciplineCount,
                                'limit' => $disciplineLimit,
                            ]
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Failed to dispatch discipline 2nd strike notification: ' . $e->getMessage());
                    }
                }
            }

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

                    // Reset warning cache key for next cycle in same month
                    Cache::forget("att_discipline_warning_2:{$employee->id}:{$cycleMonth}");

                    // Dispatch penalty notification to employee with clear reason
                    if (! empty($employee->user_id)) {
                        try {
                            $actionLabel = ($disciplineAction === 'lwp') ? 'LWP' : 'Half Day';
                            $attDateFormatted = Carbon::parse($targetAttendance->attendance_date)->format('d M Y');
                            $lastType = $lastViolation?->type ?? 'violation';
                            $typeDesc = match ($lastType) {
                                'late_login', 'late', 'late_mark' => 'Late Login',
                                'early_logout', 'early', 'early_out' => 'Early Logout',
                                default => 'Late / Early Violation',
                            };
                            $ordinal = match ($disciplineLimit) {
                                1 => '1st',
                                2 => '2nd',
                                3 => '3rd',
                                default => "{$disciplineLimit}th",
                            };
                            $notifTitle = "{$actionLabel} Marked ({$ordinal} {$typeDesc})";

                            $this->notificationService->notifyUser(
                                (int) $employee->user_id,
                                $notifTitle,
                                "Your attendance on {$attDateFormatted} has been marked as {$actionLabel} due to completing {$disciplineLimit} Late / Early Logout violations this month.",
                                [
                                    'type' => ($disciplineAction === 'lwp') ? 'attendance_lwp_action' : 'attendance_half_day_action',
                                    'action' => $disciplineAction,
                                    'attendance_id' => $targetAttendance->id,
                                    'attendance_date' => $targetAttendance->attendance_date,
                                    'employee_id' => $employee->id,
                                    'cycle_month' => $cycleMonth,
                                    'violation_category' => 'discipline',
                                    'limit' => $disciplineLimit,
                                ]
                            );
                        } catch (\Throwable $e) {
                            Log::warning('Failed to dispatch discipline penalty notification: ' . $e->getMessage());
                        }
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
                ->whereNotIn('status', ['resolved', 'regularized', 'converted'])
                ->where(function ($q) {
                    $q->whereNull('policy_action')->orWhere('policy_action', '!=', 'resolved');
                })
                ->orderBy('violation_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $missedCount = $activeMissedViolations->sum(fn ($v) => (int) ($v->violation_count ?? 1));

            // Send warning notification on 2nd missed punch (1 strike remaining before penalty action)
            if ($missedCount === ($missedPunchLimit - 1) && ! empty($employee->user_id)) {
                $cacheKey = "att_missed_punch_warning_2:{$employee->id}:{$cycleMonth}";
                if (Cache::add($cacheKey, 1, now()->endOfMonth())) {
                    try {
                        $actionLabel = ($missedPunchAction === 'half_day') ? 'Half Day' : 'LWP';
                        $warningLimit = $missedPunchLimit - 1;
                        $this->notificationService->notifyUser(
                            (int) $employee->user_id,
                            "Attendance Violation Warning",
                            "You have reached {$missedCount}/{$warningLimit} Missed Punch warnings this month. 3rd violation will result in {$actionLabel}.",
                            [
                                'type' => 'attendance_missed_punch_warning',
                                'employee_id' => $employee->id,
                                'cycle_month' => $cycleMonth,
                                'violation_category' => 'missed_punch',
                                'active_count' => $missedCount,
                                'limit' => $missedPunchLimit,
                            ]
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Failed to dispatch missed punch 2nd strike notification: ' . $e->getMessage());
                    }
                }
            }

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

                    // Reset warning cache key for next cycle in same month
                    Cache::forget("att_missed_punch_warning_2:{$employee->id}:{$cycleMonth}");

                    // Dispatch penalty notification to employee with clear reason
                    if (! empty($employee->user_id)) {
                        try {
                            $actionLabel = ($missedPunchAction === 'half_day') ? 'Half Day' : 'LWP';
                            $attDateFormatted = Carbon::parse($targetAttendanceMissed->attendance_date)->format('d M Y');
                            $ordinal = match ($missedPunchLimit) {
                                1 => '1st',
                                2 => '2nd',
                                3 => '3rd',
                                default => "{$missedPunchLimit}th",
                            };
                            $notifTitle = "{$actionLabel} Marked ({$ordinal} Missed Punch)";

                            $this->notificationService->notifyUser(
                                (int) $employee->user_id,
                                $notifTitle,
                                "Your attendance on {$attDateFormatted} has been marked as {$actionLabel} due to completing {$missedPunchLimit} Missed Punch violations this month.",
                                [
                                    'type' => ($missedPunchAction === 'half_day') ? 'attendance_half_day_action' : 'attendance_lwp_action',
                                    'action' => $missedPunchAction,
                                    'attendance_id' => $targetAttendanceMissed->id,
                                    'attendance_date' => $targetAttendanceMissed->attendance_date,
                                    'employee_id' => $employee->id,
                                    'cycle_month' => $cycleMonth,
                                    'violation_category' => 'missed_punch',
                                    'limit' => $missedPunchLimit,
                                ]
                            );
                        } catch (\Throwable $e) {
                            Log::warning('Failed to dispatch missed punch penalty notification: ' . $e->getMessage());
                        }
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
        if (self::$isRebuildingCycles) {
            return;
        }

        self::$isRebuildingCycles = true;
        try {
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
                if ($att->half_day_reason && (str_contains($att->half_day_reason, 'violations completed') || str_contains($att->half_day_reason, 'Attendance Discipline') || str_contains($att->half_day_reason, 'violation'))) {
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
                if ($att->lwp_reason && (str_contains($att->lwp_reason, 'missed punch') || str_contains($att->lwp_reason, 'Missed Punch') || str_contains($att->lwp_reason, 'violation'))) {
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
            $attendanceService = app(AttendanceService::class);
            foreach ($attendances as $att) {
                $att->refresh();
                $attendanceService->syncAttendanceViolations($att);
            }
        } finally {
            self::$isRebuildingCycles = false;
        }
    }
}
