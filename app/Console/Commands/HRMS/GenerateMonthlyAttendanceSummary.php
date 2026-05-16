<?php

namespace App\Console\Commands\HRMS;

use App\Services\HRMS\Attendance\PayrollAttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyAttendanceSummary extends Command
{
    protected $signature = 'hrms:attendance-monthly-summary {--month=} {--year=} {--employee_id=}';
    protected $description = 'Generate monthly attendance summaries for payroll.';

    public function handle(PayrollAttendanceSummaryService $summaryService): int
    {
        $now = Carbon::now('Asia/Kolkata')->subMonthNoOverflow();
        $month = (int) ($this->option('month') ?: $now->month);
        $year = (int) ($this->option('year') ?: $now->year);
        $employeeId = $this->option('employee_id') ? (int) $this->option('employee_id') : null;

        $count = $summaryService->generate($month, $year, $employeeId);
        $this->info("Generated monthly attendance summary for {$count} employee(s).");

        return self::SUCCESS;
    }
}
