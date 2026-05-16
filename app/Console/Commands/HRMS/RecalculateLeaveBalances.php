<?php

namespace App\Console\Commands\HRMS;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveAllocationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateLeaveBalances extends Command
{
    protected $signature = 'hrms:leave-recalculate-balances {--year=} {--employee_id=}';
    protected $description = 'Recalculate leave balances from approved leave requests.';

    public function handle(LeaveAllocationService $allocationService): int
    {
        $year = (int) ($this->option('year') ?: Carbon::now('Asia/Kolkata')->year);
        $employees = EmployeeM::query()
            ->when($this->option('employee_id'), fn ($query) => $query->where('id', $this->option('employee_id')))
            ->get();

        foreach ($employees as $employee) {
            $allocationService->recalculateForEmployee($employee, $year);
        }

        $this->info("Recalculated leave balances for {$employees->count()} employee(s).");
        return self::SUCCESS;
    }
}
