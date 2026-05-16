<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessLwp extends Command
{
    protected $signature = 'attendance:process-lwp {date?}';
    protected $description = 'Audit and process LWP status.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        $attendances = Attendance::whereDate('attendance_date', $date)->get();
        
        $count = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->is_lwp) $count++;
        }

        $this->info("Found and verified {$count} LWP(s) for {$date}.");
        return self::SUCCESS;
    }
}
