<?php

namespace App\Services\HRMS\Payroll;

use App\Services\HRMS\Attendance\PayrollAttendanceSummaryService;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollCalculatorS;

/**
 * Compatibility entry point for existing attendance workflows.
 * All payroll values and storage now come from the Enterprise payroll engine.
 */
class PayrollCalculationService
{
    public function __construct(
        private PayrollAttendanceSummaryService $attendanceSummaryService,
        private EnterprisePayrollCalculatorS $enterprisePayrollCalculator
    ) {
    }

    public function generateMonth(int $month, int $year, ?int $employeeId = null, ?int $actorId = null): array
    {
        $this->attendanceSummaryService->generate($month, $year, $employeeId);
        $run = $this->enterprisePayrollCalculator->generate($month, $year, $actorId, $employeeId);

        return [
            'generated' => (int) $run->payrolls()->count(),
            'skipped_locked' => 0,
            'skipped_missing_salary_structure' => 0,
            'skipped_missing_attendance_summary' => 0,
            'payroll_run_id' => $run->id,
            'status' => $run->status,
        ];
    }
}
