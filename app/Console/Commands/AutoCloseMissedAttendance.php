<?php

namespace App\Console\Commands;

use App\Services\HRMS\Attendance\AttendanceService;
use Illuminate\Console\Command;

class AutoCloseMissedAttendance extends Command
{
    protected $signature = 'attendance:auto-close-missed-punchouts';

    protected $description = 'Auto mark past attendance records absent when punch out was missed before day end.';

    public function handle(AttendanceService $attendanceService): int
    {
        $closed = $attendanceService->autoCloseMissedPunchouts();

        $this->info("Auto closed {$closed} missed attendance record(s).");

        return self::SUCCESS;
    }
}
