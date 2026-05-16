<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessLateMarks extends Command
{
    protected $signature = 'attendance:process-late-marks {date?}';
    protected $description = 'Process and audit late marks for a specific date.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        $attendances = Attendance::whereDate('attendance_date', $date)->whereNotNull('punch_in_time')->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            // Re-evaluating late mark
            $shift = $attendance->attendanceTime ?: AttendanceTime::where('is_default', true)->first();
            if (!$shift) continue;

            $punchIn = Carbon::parse($date . ' ' . $attendance->punch_in_time, $attendanceService->attendanceTimezone());
            $lateTime = Carbon::parse($date . ' ' . $shift->late_after_time, $attendanceService->attendanceTimezone());

            $isLate = $punchIn->gt($lateTime);
            if ($attendance->is_late !== $isLate) {
                $attendance->update(['is_late' => $isLate]);
                $count++;
            }
        }

        $this->info("Updated {$count} late mark(s) for {$date}.");
        return self::SUCCESS;
    }
}
