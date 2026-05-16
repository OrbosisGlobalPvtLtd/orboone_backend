<?php

namespace App\Console\Commands\HRMS;

use App\Services\HRMS\Attendance\AttendanceS;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoBlockMissedPunchIns extends Command
{
    protected $signature = 'hrms:auto-block-missed-punchins {date? : Optional attendance date for backward compatibility} {--date= : Attendance date YYYY-MM-DD} {--dry-run : Show what would happen without writing changes}';

    protected $description = 'Create blocked attendance rows after each employee attendance policy block time.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->option('date') ?: $this->argument('date');
        $dryRun = (bool) $this->option('dry-run');
        $counts = $attendanceService->autoBlockMissedPunchIns($date, $dryRun);

        Log::info('Command hrms:auto-block-missed-punchins finished.', $counts);

        $this->info('Auto block missed punch-ins finished' . ($dryRun ? ' (dry-run).' : '.'));
        foreach ($counts as $key => $value) {
            $this->line(str_replace('_', ' ', ucfirst($key)) . ': ' . $value);
        }

        return self::SUCCESS;
    }
}
