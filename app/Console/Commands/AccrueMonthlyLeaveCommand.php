<?php

namespace App\Console\Commands;

use App\Services\HRMS\Leave\MonthlyLeaveAccrualService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AccrueMonthlyLeaveCommand extends Command
{
    protected $signature = 'hrms:accrue-monthly-leave {--month=} {--year=}';

    protected $description = 'Accrue monthly earned leave for eligible employees and carry forward unused balance.';

    public function handle(MonthlyLeaveAccrualService $accrualService): int
    {
        $month = $this->option('month') ? (int) $this->option('month') : (int) date('m');
        $year = $this->option('year') ? (int) $this->option('year') : (int) date('Y');

        $targetDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata');

        $this->info("Processing Monthly Earned Leave Accrual for Month {$month}, Year {$year}...");

        $res = $accrualService->accrueMonthlyLeaves($targetDate);

        $this->info("Accrual completed. Processed: {$res['processed_count']}, Skipped: {$res['skipped_count']}.");

        return Command::SUCCESS;
    }
}
