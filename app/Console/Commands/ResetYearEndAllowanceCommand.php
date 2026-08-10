<?php

namespace App\Console\Commands;

use App\Services\HRMS\Leave\MonthlyLeaveQuotaService;
use Illuminate\Console\Command;

class ResetYearEndAllowanceCommand extends Command
{
    protected $signature = 'hrms:reset-year-end-allowance {--year=}';

    protected $description = 'Reset accumulated monthly quota on December 31st according to DB leave policy.';

    public function handle(MonthlyLeaveQuotaService $quotaService): int
    {
        $year = $this->option('year') ? (int) $this->option('year') : (int) date('Y');

        $this->info("Processing Year-End Quota Reset for Year {$year}...");

        $res = $quotaService->resetYearEndQuota($year);

        $this->info("Year-end reset completed. Reset count: {$res['reset_count']}.");

        return Command::SUCCESS;
    }
}
