<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;

class LeaveCalculationService
{
    public function __construct(
        private LeavePolicyCalculatorS $policyCalculator
    ) {
    }

    public function calculate(EmployeeM $employee, LeaveTypeM $leaveType, array $payload, ?LeaveRequestM $existingRequest = null): array
    {
        return $this->policyCalculator->calculate($employee, $leaveType, $payload, $existingRequest);
    }
}
