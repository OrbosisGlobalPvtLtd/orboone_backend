<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessWorkingHours extends Command
{
    protected $signature = 'attendance:process-working-hours {date?}';
    protected $description = 'Recalculate working hours and status for attendances.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        
        $attendances = Attendance::whereDate('attendance_date', $date)
            ->whereNotNull('punch_in_time')
            ->whereNotNull('punch_out_time')
            ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            $attendanceService->calculateAttendanceStats($attendance);
            $count++;
        }

        $this->info("Recalculated working hours for {$count} records on {$date}.");
        return self::SUCCESS;
    }
}
