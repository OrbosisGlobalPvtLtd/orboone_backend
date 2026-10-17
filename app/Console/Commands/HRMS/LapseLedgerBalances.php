<?php

declare(strict_types=1);

namespace App\Console\Commands\HRMS;

use App\Enums\LeaveBucket;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Services\HRMS\Leave\LeaveBalanceMovementService;
use App\Services\HRMS\Leave\LeaveMonthlyBalanceService;
use App\Services\HRMS\Leave\LeavePolicyService;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class LapseLedgerBalances extends Command
{
    protected $signature = 'hrms:leave-lapse-ledger {--date=}';
    protected $description = 'Lapse old-year ledger balances after the configured grace period.';

    public function handle(
        LeaveBalanceMovementService $movementService,
        LeavePolicyService $policyService,
        LeaveMonthlyBalanceService $monthlyBalanceService
    ): int
    {
        $today = $this->option('date')
            ? Carbon::parse((string) $this->option('date'), 'Asia/Kolkata')->startOfDay()
            : Carbon::today('Asia/Kolkata');
        $processed = 0;
        $unsupported = 0;

        LeaveAllocationM::query()->with('employee')->where('year', '<', $today->year)->chunkById(250, function ($allocations) use ($today, $movementService, $policyService, $monthlyBalanceService, &$processed, &$unsupported): void {
            foreach ($allocations as $allocation) {
                if (! $allocation->isLedgerBacked()) {
                    continue;
                }
                try {
                    $policyDate = Carbon::create((int) $allocation->year, 12, 31, 0, 0, 0, 'Asia/Kolkata');
                    $policy = $policyService->requireForEmployee($allocation->employee, $policyDate);
                } catch (\Throwable $exception) {
                    $unsupported++;
                    $this->warn("Skipped allocation {$allocation->id}: {$exception->getMessage()}");
                    continue;
                }
                if ($policy->carry_forward_enabled) {
                    $unsupported++;
                    $this->warn("Skipped allocation {$allocation->id}: carry-forward is enabled by policy and is not supported by ledger lapse.");
                    continue;
                }
                $lapseMonth = (int) $policy->leave_lapse_month;
                $lapseDay = (int) $policy->leave_lapse_day;
                if ($lapseMonth < 1 || $lapseMonth > 12 || $lapseDay < 1 || $lapseDay > 31) {
                    $unsupported++;
                    $this->warn("Skipped allocation {$allocation->id}: leave lapse date is missing or invalid in its effective policy.");
                    continue;
                }
                $lapseDate = Carbon::create((int) $allocation->year, $lapseMonth, $lapseDay, 0, 0, 0, 'Asia/Kolkata');
                if ((int) $lapseDate->month !== $lapseMonth || (int) $lapseDate->day !== $lapseDay) {
                    $unsupported++;
                    $this->warn("Skipped allocation {$allocation->id}: leave lapse date is invalid in its effective policy.");
                    continue;
                }
                $cutoff = $lapseDate->copy()->addDays(max(0, (int) $policy->lapse_grace_days));
                if (! $today->gt($cutoff)) {
                    continue;
                }
                foreach (LeaveBucket::cases() as $bucket) {
                    $movementService->lapse($allocation, $bucket, (int) $allocation->year, $lapseDate->toDateString(), (int) $policy->id);
                }
                if ($allocation->employee) {
                    $monthlyBalanceService->rebuildEmployeeThrough($allocation->employee, (int) $allocation->year, 12);
                }
                $processed++;
            }
        });

        $this->info("Lapsed ledger balances for {$processed} allocation(s); {$unsupported} allocation(s) skipped by policy or missing data.");
        return self::SUCCESS;
    }
}
