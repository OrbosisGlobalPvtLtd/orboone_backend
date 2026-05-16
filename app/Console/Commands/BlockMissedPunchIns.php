<?php

namespace App\Console\Commands;

use App\Services\HRMS\Attendance\AttendanceS;
use Illuminate\Console\Command;

class BlockMissedPunchIns extends Command
{
    protected $signature = 'attendance:block-missed-punch-ins';
    protected $description = 'At/after 11:15:01, create punch_blocked records for employees who did not punch in today.';

    public function handle(AttendanceS $attendanceService)
    {
        $this->info('Starting to block missed punch-ins...');
        $count = $attendanceService->blockMissedPunchIns();
        $this->info("Successfully blocked {$count} missed punch-ins.");
    }
}
