<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;

class MonthlyLeaveAccrualService
{
    public function __construct(
        private MonthlyLeaveQuotaService $quotaService
    ) {
    }

    /**
     * Accrue / manage monthly leave quota and carry-forward for all eligible confirmed employees.
     */
    public function accrueMonthlyLeaves(?Carbon $date = null, ?int $userId = null): array
    {
        $date = $date ? $date->copy()->setTimezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');

        $employees = EmployeeM::where('employment_status', 'active')
            ->where(function ($query) {
                $query->where('is_permanent', true)
                    ->orWhere('employee_stage', 'permanent')
                    ->orWhere('employment_type', 'permanent');
            })
            ->get();

        $processed = 0;
        foreach ($employees as $employee) {
            if (app(\App\Services\HRMS\Employee\EmployeeEligibilityS::class)->canUseLeave($employee)) {
                $this->quotaService->getMonthlyQuota($employee, $date, $userId);
                $processed++;
            }
        }

        return [
            'year' => (int) $date->year,
            'month' => (int) $date->month,
            'processed_count' => $processed,
            'skipped_count' => 0,
        ];
    }

    /**
     * Year-end lapse: Resets accumulated monthly carry-forward on 31st December per DB policy.
     */
    public function processYearEndLapse(int $year, ?int $userId = null): array
    {
        return $this->quotaService->resetYearEndQuota($year, $userId);
    }
}
