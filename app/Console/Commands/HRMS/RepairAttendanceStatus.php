<?php

namespace App\Console\Commands\HRMS;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RepairAttendanceStatus extends Command
{
    protected $signature = 'hrms:repair-attendance-status {--date=} {--attendance-id=} {--dry-run}';
    protected $description = 'Repair mismatched attendance status/type/flags using authoritative final status resolver.';

    public function handle(AttendanceS $attendanceService): int
    {
        $timezone = $attendanceService->attendanceTimezone();
        $date = $this->option('date') ?: Carbon::now($timezone)->toDateString();
        $attendanceId = $this->option('attendance-id');
        $dryRun = (bool) $this->option('dry-run');

        $query = Attendance::with('attendanceType')
            ->whereDate('attendance_date', $date);

        if ($attendanceId) {
            $query->where('id', (int) $attendanceId);
        }

        $rows = $query->orderBy('id')->get();

        $checked = 0;
        $mismatched = 0;
        $updated = 0;

        foreach ($rows as $attendance) {
            $checked++;

            $resolved = $attendanceService->resolveFinalStatus($attendance);
            $resolvedCode = (string) ($resolved['status_code'] ?? 'absent');
            $resolvedTypeId = $attendanceService->attendanceType($resolvedCode)?->id;
            $resolvedIsHalfDay = $resolvedCode === 'half_day';
            $resolvedIsLwp = $resolvedCode === 'lwp';

            $currentTypeCode = strtolower((string) optional($attendance->attendanceType)->code);
            $currentStatus = strtolower((string) ($attendance->attendance_status ?? ''));

            $isMismatch = $currentStatus !== $resolvedCode
                || $currentTypeCode !== $resolvedCode
                || (bool) $attendance->is_half_day !== $resolvedIsHalfDay
                || (bool) $attendance->is_lwp !== $resolvedIsLwp;

            if (! $isMismatch) {
                continue;
            }

            $mismatched++;
            $this->line(sprintf(
                'ID %d | %s | type:%s status:%s half_day:%s lwp:%s => %s',
                $attendance->id,
                (string) $attendance->attendance_date,
                $currentTypeCode ?: '-',
                $currentStatus ?: '-',
                $attendance->is_half_day ? '1' : '0',
                $attendance->is_lwp ? '1' : '0',
                $resolvedCode
            ));

            if ($dryRun) {
                continue;
            }

            $attendance->fill([
                'attendance_type_id' => $resolvedTypeId ?: $attendance->attendance_type_id,
                'attendance_status' => $resolvedCode,
                'is_half_day' => $resolvedIsHalfDay,
                'is_lwp' => $resolvedIsLwp,
            ])->save();

            $updated++;
        }

        $this->info("Checked: {$checked}, Mismatched: {$mismatched}, Updated: {$updated}, Dry-run: " . ($dryRun ? 'yes' : 'no'));

        return self::SUCCESS;
    }
}
