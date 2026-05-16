<?php

namespace App\Console\Commands\HRMS;

use App\Services\HRMS\Attendance\AttendanceS;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessMissedPunches extends Command
{
    protected $signature = 'hrms:process-missed-punches {date? : Optional attendance date for backward compatibility} {--date= : Attendance date YYYY-MM-DD} {--dry-run : Show what would happen without writing changes}';

    protected $description = 'At day end, mark attendances with punch-in but no punch-out as missed punch and absent/LWP.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->option('date') ?: $this->argument('date');
        $dryRun = (bool) $this->option('dry-run');
        $counts = $attendanceService->processMissedPunches($date, $dryRun);

        Log::info('Command hrms:process-missed-punches finished.', $counts);

        $this->info('Process missed punches finished' . ($dryRun ? ' (dry-run).' : '.'));
        foreach ($counts as $key => $value) {
            $this->line(str_replace('_', ' ', ucfirst($key)) . ': ' . $value);
        }

        return self::SUCCESS;
    }
}
