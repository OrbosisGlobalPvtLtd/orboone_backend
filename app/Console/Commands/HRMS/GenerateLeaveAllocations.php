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
        $employees = EmployeeM::query()
            ->when($this->option('employee_id'), fn ($query) => $query->where('id', $this->option('employee_id')))
            ->where(function ($query) {
                $query->where('is_active', 1)->orWhereNull('is_active');
            })
            ->get();

        foreach ($employees as $employee) {
            $allocationService->generateForEmployee($employee, $year);
        }

        $this->info("Generated leave allocation for {$employees->count()} employee(s) for {$year}.");
        return self::SUCCESS;
    }
}
