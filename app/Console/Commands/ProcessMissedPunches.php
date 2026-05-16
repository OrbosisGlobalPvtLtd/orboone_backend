<?php

namespace App\Console\Commands;

use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessMissedPunches extends Command
{
    protected $signature = 'attendance:process-missed-punch {date? : Optional attendance date for backward compatibility} {--date= : Attendance date YYYY-MM-DD} {--dry-run : Show what would happen without writing changes}';
    protected $description = 'Process missed punches and mark absent/LWP.';

    public function handle(AttendanceS $attendanceService): int
    {
        $date = $this->option('date') ?: $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        $dryRun = (bool) $this->option('dry-run');
        $counts = $attendanceService->processMissedPunches($date, $dryRun);

        Log::info('Command attendance:process-missed-punch finished.', $counts);

        $this->info('Process missed punches finished' . ($dryRun ? ' (dry-run).' : '.'));
        foreach ($counts as $key => $value) {
            $this->line(str_replace('_', ' ', ucfirst($key)) . ': ' . $value);
        }
        return self::SUCCESS;
    }
}
