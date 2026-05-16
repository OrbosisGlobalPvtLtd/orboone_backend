<?php

namespace App\Console\Commands\HRMS;

use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeavePolicyM;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LapseYearEndLeaves extends Command
{
    protected $signature = 'hrms:leave-lapse-year-end {--year=}';
    protected $description = 'Lapse non-carried leave balances at year end according to policy.';

    public function handle(): int
    {
        $year = (int) ($this->option('year') ?: Carbon::now('Asia/Kolkata')->year);
        $count = 0;

        DB::transaction(function () use ($year, &$count) {
            LeaveAllocationM::with('policy')->where('year', $year)->lockForUpdate()->chunk(100, function ($allocations) use (&$count) {
                foreach ($allocations as $allocation) {
                    $policy = $allocation->policy ?: LeavePolicyM::where('is_active', true)->first();
                    if (! $policy || $policy->carry_forward_enabled || $allocation->is_locked) {
                        continue;
                    }

                    $before = (float) $allocation->total_remaining;
                    if ($before <= 0) {
                        continue;
                    }

                    $allocation->paid_allocated = $allocation->paid_used;
                    $allocation->sick_allocated = $allocation->sick_used;
                    $allocation->comp_off_allocated = $allocation->comp_off_used;
                    $allocation->paid_remaining = 0;
                    $allocation->sick_remaining = 0;
                    $allocation->comp_off_remaining = 0;
                    $allocation->total_remaining = 0;
                    $allocation->save();

                    LeaveBalanceLogM::create([
                        'employee_id' => $allocation->employee_id,
                        'leave_allocation_id' => $allocation->id,
                        'action' => 'year_end_lapse',
                        'debit' => $before,
                        'balance_before' => $before,
                        'balance_after' => 0,
                        'remarks' => 'Year-end leave lapsed by policy.',
                    ]);
                    $count++;
                }
            });
        });

        $this->info("Lapsed balances for {$count} allocation(s).");
        return self::SUCCESS;
    }
}
