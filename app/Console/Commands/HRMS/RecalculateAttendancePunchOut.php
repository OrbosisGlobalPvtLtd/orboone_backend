<?php

namespace App\Console\Commands\HRMS;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use App\Services\HRMS\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateAttendancePunchOut extends Command
{
    protected $signature = 'hrms:recalculate-attendance-punchout
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--date= : Single date (YYYY-MM-DD)}
                            {--employee-id= : Filter by employee ID}
                            {--dry-run : Preview changes without persisting}';

    protected $description = 'Recalculate attendance records according to new Early Logout and Attendance Policy rules';

    public function handle(
        AttendanceService $attendanceService,
        AttendanceRuleResolverService $ruleResolver
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $date = $this->option('date');
        $from = $this->option('from');
        $to = $this->option('to');
        $employeeId = $this->option('employee-id');

        if ($date) {
            $from = $date;
            $to = $date;
        } elseif (! $from && ! $to) {
            $from = Carbon::now($attendanceService->attendanceTimezone())->toDateString();
            $to = $from;
        } elseif ($from && ! $to) {
            $to = $from;
        } elseif ($to && ! $from) {
            $from = $to;
        }

        $this->info(sprintf(
            'Recalculating attendance punch-out rules from %s to %s%s%s',
            $from,
            $to,
            $employeeId ? " for Employee #{$employeeId}" : '',
            $dryRun ? ' [DRY RUN - No changes will be saved]' : ' [PERSISTING CHANGES]'
        ));

        $query = Attendance::whereBetween('attendance_date', [$from, $to])
            ->whereNotNull('punch_in_time')
            ->whereNotNull('punch_out_time');

        if ($employeeId) {
            $query->where('employee_id', (int) $employeeId);
        }

        $records = $query->orderBy('attendance_date')->orderBy('id')->get();
        $total = $records->count();

        $this->line("Found {$total} attendance records with both punch-in and punch-out.");

        $changed = 0;
        $tableRows = [];

        foreach ($records as $attendance) {
            $oldStatus = (string) ($attendance->attendance_status ?? 'present');
            $oldEarlyOut = (bool) $attendance->is_early_out;
            $oldEarlyMins = (int) ($attendance->early_out_minutes ?? 0);

            // Calculate working hours using the new classifier
            $dateStr = Carbon::parse($attendance->attendance_date)->toDateString();
            $employee = $attendance->employee ?: Employee::find($attendance->employee_id);
            if (! $employee) {
                continue;
            }

            $shift = $ruleResolver->resolveShiftPolicy($employee, $dateStr, $attendance->attendance_time_id);
            $policy = $ruleResolver->getPolicyForEmployee($employee, $dateStr);

            $targetTimeStr = $attendance->target_punch_out_time ?: ($shift?->shift_end_time ?? '19:00:00');
            $targetCarbon = Carbon::parse($dateStr . ' ' . $ruleResolver->timeString($targetTimeStr), AttendanceRuleResolverService::TIMEZONE);
            $punchInCarbon = Carbon::parse($dateStr . ' ' . $ruleResolver->timeString($attendance->punch_in_time), AttendanceRuleResolverService::TIMEZONE);
            $punchOutCarbon = Carbon::parse($dateStr . ' ' . $ruleResolver->timeString($attendance->punch_out_time), AttendanceRuleResolverService::TIMEZONE);

            if ($targetCarbon->lt($punchInCarbon)) {
                $targetCarbon->addDay();
            }
            if ($punchOutCarbon->lt($punchInCarbon)) {
                $punchOutCarbon->addDay();
            }

            $grossMinutes = (int) $punchInCarbon->diffInMinutes($punchOutCarbon);
            $breakMinutes = (int) ($policy?->lunch_break_minutes ?? $shift?->break_minutes ?? 0);
            $netMinutes = max(0, $grossMinutes - $breakMinutes);

            $isFlexible = method_exists($shift, 'isDynamicShift') ? $shift->isDynamicShift() : false;
            $approvedLeave = $ruleResolver->getApprovedLeaveOnDate($employee, $dateStr);
            $hasApprovedFullDay = (bool) ($approvedLeave && ! ($approvedLeave['is_half_day'] ?? false) && empty($approvedLeave['half_day_type']));
            $hasApprovedHalfDay = (bool) ($approvedLeave && ((bool) ($approvedLeave['is_half_day'] ?? false) || ! empty($approvedLeave['half_day_type'])));

            $isHalfDayPunch = false;
            if (! $isFlexible && $shift && $shift->half_day_after_time) {
                $halfDayAfter = Carbon::parse($dateStr . ' ' . $ruleResolver->timeString($shift->half_day_after_time), AttendanceRuleResolverService::TIMEZONE);
                if ($punchInCarbon->gte($halfDayAfter)) {
                    $isHalfDayPunch = true;
                }
            }
            $isHalfDayContext = $hasApprovedHalfDay || $isHalfDayPunch || (bool) $attendance->is_half_day || in_array(strtolower((string) $attendance->attendance_status), ['half_day', 'half_leave', 'first_half_leave', 'second_half_leave'], true);

            $classification = \App\Services\HRMS\Attendance\AttendancePunchOutClassifier::classify(
                $punchOutCarbon,
                $targetCarbon,
                $policy ?: $shift,
                $netMinutes,
                [
                    'is_flexible' => $isFlexible,
                    'has_approved_full_day_leave' => $hasApprovedFullDay,
                    'has_approved_half_day_leave' => $hasApprovedHalfDay,
                    'is_half_day' => $isHalfDayContext,
                ]
            );

            $newStatus = $classification['status'];
            $newEarlyOut = $classification['is_early_out'];
            $newEarlyMins = $classification['early_out_minutes'];

            if (! $dryRun) {
                $attendanceService->calculateWorkingHours($attendance);
            }

            $hasChanged = ($oldStatus !== $newStatus)
                || ($oldEarlyOut !== $newEarlyOut)
                || ($oldEarlyMins !== $newEarlyMins);

            if ($hasChanged) {
                $changed++;
                $tableRows[] = [
                    $attendance->id,
                    $attendance->employee_id,
                    $dateStr,
                    $attendance->punch_out_time,
                    "{$oldStatus} (EO: " . ($oldEarlyOut ? "Yes({$oldEarlyMins}m)" : 'No') . ')',
                    "{$newStatus} (EO: " . ($newEarlyOut ? "Yes({$newEarlyMins}m)" : 'No') . ')',
                ];
            }
        }

        if (! empty($tableRows)) {
            $this->table(
                ['ID', 'Emp ID', 'Date', 'Punch Out', 'Old State', 'New State'],
                array_slice($tableRows, 0, 50)
            );
            if (count($tableRows) > 50) {
                $this->line('... and ' . (count($tableRows) - 50) . ' more records changed.');
            }
        }

        $this->info(sprintf(
            'Completed: %d of %d records %s.',
            $changed,
            $total,
            $dryRun ? 'would be updated' : 'were updated'
        ));

        return Command::SUCCESS;
    }
}
