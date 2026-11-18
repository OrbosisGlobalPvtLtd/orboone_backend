<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendancePayableDayResolver
{
    public function __construct(private AttendanceRuleResolverService $ruleResolver)
    {
    }

    public function isCompOffWorkedDay(AttendanceM $attendance): bool
    {
        return $this->compOffWorkedDayType($attendance) !== null;
    }

    public function compOffWorkedDayType(AttendanceM $attendance): ?string
    {
        if (strtolower((string) $attendance->attendance_source) === 'comp_off_work') {
            $code = strtolower((string) optional($attendance->attendanceType)->code);
            return str_contains($code, 'holiday') ? 'holiday' : 'week_off';
        }

        if (!$attendance->employee_id || !$attendance->attendance_date) {
            return null;
        }

        $dateStr = Carbon::parse($attendance->attendance_date)->toDateString();

        $employee = $attendance->employee ?: EmployeeM::find($attendance->employee_id);
        if (!$employee) {
            return null;
        }

        $workRequest = DB::table('holiday_work_requests')
            ->where('employee_id', $attendance->employee_id)
            ->whereDate('worked_date', $dateStr)
            ->whereIn('status', ['approved', 'completed'])
            ->whereNull('deleted_at')
            ->first();

        if (!$workRequest) {
            return null;
        }

        if (($workRequest->work_type ?? '') === 'holiday_work') {
            return 'holiday';
        }
        if (($workRequest->work_type ?? '') === 'weekoff_work') {
            return 'week_off';
        }

        $dayContext = $this->ruleResolver->getDayContext($employee, $dateStr);
        if (!empty($dayContext['holiday_name']) || ($dayContext['is_holiday'] ?? false)) {
            return 'holiday';
        }

        return 'week_off';
    }

    public function resolve(AttendanceM $attendance): array
    {
        
        if ($this->isCompOffWorkedDay($attendance)) {
            return $this->row(1.0, 'paid', false, 'Holiday/Weekoff Comp-Off worked day (no payroll deduction).');
        }

        $code = strtolower((string) optional($attendance->attendanceType)->code);
        $status = strtolower((string) ($attendance->attendance_status ?? ''));
        $effective = $code !== '' ? $code : $status;

        $isBlocked = (bool) ($attendance->is_punch_blocked || $attendance->is_blocked || $effective === 'punch_blocked');
        $isPendingHr = $effective === 'pending_hr';
        $isMissedPunch = (bool) ($attendance->missed_punch || $attendance->is_missed_punch || $effective === 'missed_punch');

        if ($isBlocked) {
            return $this->row(0.0, 'unpaid', true, 'Punch blocked attendance is unresolved.');
        }

        if ((bool) $attendance->is_half_day || $effective === 'half_day') {
            return $this->row(0.5, 'partial_paid', false, 'Half day payable as 0.5.');
        }

        if ((bool) $attendance->is_lwp || in_array($effective, ['lwp', 'absent'], true)) {
            return $this->row(0.0, 'unpaid', false, 'Unpaid attendance.');
        }

        if ($isPendingHr || $isMissedPunch) {
            $hasApprovedReg = DB::table('attendance_regularizations')
                ->where('attendance_id', $attendance->id)
                ->where('status', 'approved')
                ->exists();

            if ($hasApprovedReg) {
                return $this->row(1.0, 'paid', false, 'Missed punch regularized and approved.');
            }

            return $this->row(1.0, 'paid', false, 'Missed punch within monthly grace limit (Warning - No salary deduction).');
        }

        if (in_array($effective, ['present', 'holiday', 'week_off', 'leave'], true)) {
            return $this->row(1.0, 'paid', false, 'Payable full day.');
        }

        return $this->row(0.0, 'unpaid', false, 'Default unpaid for unknown status.');
    }

    public function unresolvedCount(Collection $attendances): int
    {
        return $attendances->filter(fn (AttendanceM $attendance) => (bool) $this->resolve($attendance)['is_unresolved'])->count();
    }

    private function row(float $payableDay, string $impact, bool $unresolved, string $reason): array
    {
        return [
            'payable_day' => $payableDay,
            'payroll_impact' => $impact,
            'is_unresolved' => $unresolved,
            'reason' => $reason,
        ];
    }
}
