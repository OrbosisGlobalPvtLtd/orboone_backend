<?php

namespace App\Console\Commands\HRMS;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveAllocationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateLeaveAllocations extends Command
{
    protected $signature = 'hrms:leave-generate-allocations {--year=} {--employee_id=}';
    protected $description = 'Generate leave allocations from active DB leave policies.';

    public function handle(LeaveAllocationService $allocationService): int
    {
        $year = (int) ($this->option('year') ?: Carbon::now('Asia/Kolkata')->year);
        if ($this->option('employee_id')) {
            $employee = EmployeeM::query()
                ->without(['user', 'department', 'designation', 'position', 'systemRole'])
                ->with('profile')
                ->where(function ($query) {
                    $query->where('is_active', 1)->orWhereNull('is_active');
                })
                ->find($this->option('employee_id'));
            $count = $employee ? 1 : 0;
            if ($employee) {
                $allocationService->generateForEmployee($employee, $year);
            }
        } else {
            $summary = $allocationService->generateYearly($year);
            $this->info("Leave allocation batch for {$year}: processed {$summary['total_processed']}, allocated {$summary['successful_allocations']}, skipped {$summary['skipped']}, failed {$summary['failed']}, duration {$summary['duration_seconds']}s.");
            if ($summary['failed_employee_ids']) {
                $this->error('Failed employee IDs: ' . implode(', ', $summary['failed_employee_ids']));
            }
            return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
        }

        $this->info("Generated leave allocation for {$count} employee(s) for {$year}.");
        return self::SUCCESS;
    }
}
