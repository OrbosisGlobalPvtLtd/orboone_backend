<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Services\HRMS\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessLateMarks extends Command
{
    protected $signature = 'attendance:process-late-marks {date?}';
    protected $description = 'Process and audit late marks for a specific date.';

    public function handle(AttendanceService $attendanceService): int
    {
        $timezone = $attendanceService->attendanceTimezone();
        $date = $this->argument('date') ?: Carbon::now($timezone)->toDateString();
        $attendances = Attendance::whereDate('attendance_date', $date)->whereNotNull('punch_in_time')->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            // Half day status and late-exempted employees are not subject to late marks
            if ($attendance->is_late_exempted || $attendance->is_half_day || $attendance->attendance_status === 'half_day') {
                if ($attendance->is_late || (int) ($attendance->late_minutes ?? 0) > 0) {
                    $attendance->update([
                        'is_late' => false,
                        'late_minutes' => 0,
                    ]);
                    $count++;
                }
                continue;
            }

            // Re-evaluating shift
            $shift = $attendance->attendanceTime ?: AttendanceTime::where('is_default', true)->first();
            if (!$shift || ! $shift->late_after_time) {
                continue;
            }

            // Dynamic hours shifts do not have fixed start or late boundaries
            if ($shift->is_dynamic_hours || $shift->shift_type === 'dynamic_hours') {
                continue;
            }

            // Parse punch_in_time directly without prepending date (avoiding double date specification)
            $punchIn = Carbon::parse($attendance->punch_in_time, $timezone);
            $lateTimeStr = Carbon::parse($shift->late_after_time)->format('H:i:s');
            $lateTime = Carbon::parse($date . ' ' . $lateTimeStr, $timezone);

            $isLate = $punchIn->gt($lateTime);
            $lateMinutes = $isLate ? (int) $punchIn->diffInMinutes($lateTime) : 0;

            if ($attendance->is_late !== $isLate || (int) ($attendance->late_minutes ?? 0) !== $lateMinutes) {
                $attendance->update([
                    'is_late' => $isLate,
                    'late_minutes' => $lateMinutes,
                ]);
                $count++;
            }
        }

        $this->info("Updated {$count} late mark(s) for {$date}.");
        return self::SUCCESS;
    }
}
