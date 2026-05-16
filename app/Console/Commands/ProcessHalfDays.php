<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessHalfDays extends Command
{
    protected $signature = 'attendance:process-half-days {date?}';
    protected $description = 'Audit and process half day status.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        $attendances = Attendance::whereDate('attendance_date', $date)->whereNotNull('punch_out_time')->get();
        
        $count = 0;
        foreach ($attendances as $attendance) {
            $attendanceService->calculateAttendanceStats($attendance);
            if ($attendance->is_half_day) $count++;
        }

        $this->info("Processed {$count} half day(s) for {$date}.");
        return self::SUCCESS;
    }
}
