<?php

namespace App\Console\Commands;

use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessBlockedAbsent extends Command
{
    protected $signature = 'attendance:process-blocked-absent {date?}';
    protected $description = 'Convert still-blocked punch_blocked attendances to absent/LWP after day end.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->argument('date') ?: Carbon::yesterday($attendanceService->attendanceTimezone())->toDateString();
        $count = $attendanceService->processBlockedAbsent($date);

        $this->info("Converted {$count} blocked attendance record(s) to absent/LWP for {$date}.");

        return self::SUCCESS;
    }
}
