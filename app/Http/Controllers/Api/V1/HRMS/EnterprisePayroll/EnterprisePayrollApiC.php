<?php

namespace App\Http\Controllers\Api\V1\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollApiS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnterprisePayrollApiC extends Controller
{
    public function __construct(private EnterprisePayrollApiS $service)
    {
    }

    public function summary(Request $request): JsonResponse
    {
        $employee = $this->service->employeeForUser($request->user()?->id);
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, null, 404);
        }

        $month = $request->get('month');
        $year = $request->get('year');

        if ($month && $year) {
            $latestPayslip = $this->service->visiblePayslipByMonthYear($employee->id, (int) $month, (int) $year);
        } else {
            $latestPayslip = $this->service->latestVisiblePayslip($employee->id);
        }

        $latestSalaryStructure = $this->service->latestSalaryStructure($employee->id);
        $recentReimbursements = EnterpriseReimbursementM::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => $this->service->reimbursementItem($item))
            ->values();

        $fullOverview = $this->service->buildFullOverview($employee, $latestPayslip, $latestSalaryStructure);
        $availableMonths = $this->service->availablePayslipMonths($employee->id);

        return $this->apiResponse(true, 'Payroll summary fetched successfully.', [
            'latest_payslip' => $latestPayslip ? $this->service->payslipListItem($latestPayslip) : (object) [],
            'salary_summary' => $this->service->salarySummary($latestPayslip?->payroll, $latestSalaryStructure),
            'payroll_status' => $fullOverview['payroll_status'],
            'financial_overview' => $fullOverview['financial_overview'],
            'salary_breakdown' => $fullOverview['salary_breakdown'],
            'recent_activity' => $fullOverview['recent_activity'],
            'available_months' => $availableMonths,
            'payslip_count' => EnterprisePayslipM::where('employee_id', $employee->id)
                ->where('is_visible_to_employee', true)
                ->count(),
            'reimbursement_count' => EnterpriseReimbursementM::where('employee_id', $employee->id)->count(),
            'pending_reimbursement_count' => EnterpriseReimbursementM::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->count(),
            'latest_salary_structure' => $latestSalaryStructure
                ? $this->service->salaryStructureItem($latestSalaryStructure)
                : (object) [],
            'recent_reimbursements' => $recentReimbursements,
        ]);
    }

    public function salaryHistory(Request $request): JsonResponse
    {
        $employee = $this->service->employeeForUser($request->user()?->id);
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, null, 404);
        }

        $rows = EnterpriseSalaryStructureM::where('employee_id', $employee->id)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($item) => $this->service->salaryStructureItem($item))
            ->values();

        return $this->apiResponse(true, 'Salary history fetched successfully.', $rows);
    }

    private function apiResponse(bool $success, string $message, mixed $data = null, mixed $errors = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }
}
