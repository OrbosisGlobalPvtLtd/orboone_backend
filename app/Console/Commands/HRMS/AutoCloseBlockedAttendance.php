<?php

namespace App\Console\Commands\HRMS;

use App\Services\HRMS\Attendance\AttendanceS;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCloseBlockedAttendance extends Command
{
    protected $signature = 'hrms:auto-close-blocked-attendance {date? : Optional attendance date for backward compatibility} {--date= : Attendance date YYYY-MM-DD} {--dry-run : Show what would happen without writing changes}';

    protected $description = 'At day end, mark unresolved blocked attendance records as absent.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->option('date') ?: $this->argument('date');
        $dryRun = (bool) $this->option('dry-run');
        $counts = $attendanceService->autoCloseBlockedAttendance($date, $dryRun);

        Log::info('Command hrms:auto-close-blocked-attendance finished.', $counts);

        $this->info('Auto close blocked attendance finished' . ($dryRun ? ' (dry-run).' : '.'));
        foreach ($counts as $key => $value) {
            $this->line(str_replace('_', ' ', ucfirst($key)) . ': ' . $value);
        }

        return self::SUCCESS;
    }
}
