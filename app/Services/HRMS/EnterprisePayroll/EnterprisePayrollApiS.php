<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EnterprisePayrollApiS
{
    public function employeeForUser(?int $userId): ?EmployeeM
    {
        if (! $userId) {
            return null;
        }

        return EmployeeM::with(['user', 'department', 'designation', 'profile'])
            ->where('user_id', $userId)
            ->first();
    }

    public function latestVisiblePayslip(int $employeeId): ?EnterprisePayslipM
    {
        return EnterprisePayslipM::with(['payroll', 'employee.user', 'employee.department', 'employee.designation'])
            ->where('employee_id', $employeeId)
            ->where('is_visible_to_employee', true)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->first();
    }

    public function visiblePayslip(int $employeeId, int $payslipId): ?EnterprisePayslipM
    {
        return EnterprisePayslipM::with(['payroll.items', 'employee.user', 'employee.department', 'employee.designation'])
            ->where('id', $payslipId)
            ->where('employee_id', $employeeId)
            ->where('is_visible_to_employee', true)
            ->first();
    }

    public function latestSalaryStructure(int $employeeId): ?EnterpriseSalaryStructureM
    {
        return EnterpriseSalaryStructureM::where('employee_id', $employeeId)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }

    public function payslipListItem(EnterprisePayslipM $payslip): array
    {
        $payroll = $payslip->payroll;

        return [
            'id' => $payslip->id,
            'month' => (int) $payslip->month,
            'month_name' => $this->monthName($payslip->month, $payslip->year),
            'year' => (int) $payslip->year,
            'payslip_no' => $payslip->payslip_no,
            'gross_salary' => $this->amount($payroll?->gross_salary),
            'total_deductions' => $this->amount($payroll?->total_deductions),
            'net_salary' => $this->amount($payroll?->net_salary),
            'status' => $payroll?->status,
            'generated_at' => $this->dateTime($payslip->generated_at ?? $payroll?->generated_at),
            'download_url' => $this->payslipDownloadUrl($payslip->id),
        ];
    }

    public function payslipDetail(EnterprisePayslipM $payslip): array
    {
        $payroll = $payslip->payroll;
        $employee = $payslip->employee;

        return [
            'id' => $payslip->id,
            'month' => (int) $payslip->month,
            'month_name' => $this->monthName($payslip->month, $payslip->year),
            'year' => (int) $payslip->year,
            'payslip_no' => $payslip->payslip_no,
            'employee' => [
                'id' => $employee?->id,
                'employee_code' => $employee?->employee_code,
                'name' => $employee?->user?->name,
                'email' => $employee?->user?->email,
                'department' => $employee?->department?->name,
                'designation' => $employee?->designation?->name,
            ],
            'attendance' => $this->attendanceSummary($payroll),
            'earnings' => $this->payrollItems($payroll, ['earning', 'earnings']),
            'deductions' => $this->payrollItems($payroll, ['deduction', 'deductions']),
            'gross_salary' => $this->amount($payroll?->gross_salary),
            'total_deductions' => $this->amount($payroll?->total_deductions),
            'net_salary' => $this->amount($payroll?->net_salary),
            'net_salary_words' => $payroll?->net_salary_words,
            'download_url' => $this->payslipDownloadUrl($payslip->id),
        ];
    }

    public function salaryStructureItem(EnterpriseSalaryStructureM $structure): array
    {
        return [
            'id' => $structure->id,
            'stage' => $structure->stage,
            'source' => $structure->source,
            'monthly_ctc' => $this->amount($structure->monthly_ctc),
            'annual_ctc' => $this->amount($structure->annual_ctc),
            'effective_from' => $this->date($structure->effective_from),
            'effective_to' => $this->date($structure->effective_to),
            'status' => $structure->status,
        ];
    }

    public function salarySummary(?EnterprisePayrollM $payroll, ?EnterpriseSalaryStructureM $structure): array
    {
        return [
            'gross_salary' => $this->amount($payroll?->gross_salary),
            'total_deductions' => $this->amount($payroll?->total_deductions),
            'net_salary' => $this->amount($payroll?->net_salary),
            'monthly_ctc' => $this->amount($structure?->monthly_ctc ?? $payroll?->monthly_ctc),
            'annual_ctc' => $this->amount($structure?->annual_ctc ?? $payroll?->annual_ctc),
            'status' => $payroll?->status,
        ];
    }

    public function reimbursementItem(EnterpriseReimbursementM $reimbursement): array
    {
        return [
            'id' => $reimbursement->id,
            'title' => $reimbursement->title,
            'claim_date' => $this->date($reimbursement->claim_date),
            'amount' => $this->amount($reimbursement->amount),
            'approved_amount' => $this->amount($reimbursement->approved_amount),
            'status' => $reimbursement->status,
            'attachment_url' => $this->publicFileUrl($reimbursement->attachment_path),
            'has_attachment' => ! empty($reimbursement->attachment_path),
            'remarks' => $reimbursement->remarks,
            'rejection_reason' => $reimbursement->rejection_reason,
            'approved_at' => $this->dateTime($reimbursement->approved_at),
            'created_at' => $this->dateTime($reimbursement->created_at),
        ];
    }

    public function payslipDownloadUrl(int $payslipId): string
    {
        return url("/api/v1/hrms/enterprise-payroll/payslips/{$payslipId}/download");
    }

    public function publicFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public function amount(mixed $value): float
    {
        return round((float) ($value ?? 0), 2);
    }

    private function attendanceSummary(?EnterprisePayrollM $payroll): array
    {
        return [
            'total_working_days' => $this->amount($payroll?->total_working_days),
            'present_days' => $this->amount($payroll?->present_days),
            'paid_leave_days' => $this->amount($payroll?->paid_leave_days),
            'sick_leave_days' => $this->amount($payroll?->sick_leave_days),
            'comp_off_days' => $this->amount($payroll?->comp_off_days),
            'holiday_days' => $this->amount($payroll?->holiday_days),
            'week_off_days' => $this->amount($payroll?->week_off_days),
            'half_days' => $this->amount($payroll?->half_days),
            'lwp_days' => $this->amount($payroll?->lwp_days),
            'absent_days' => $this->amount($payroll?->absent_days),
            'late_count' => (int) ($payroll?->late_count ?? 0),
            'early_out_count' => (int) ($payroll?->early_out_count ?? 0),
            'missed_punch_count' => (int) ($payroll?->missed_punch_count ?? 0),
            'payable_days' => $this->amount($payroll?->payable_days),
        ];
    }

    private function payrollItems(?EnterprisePayrollM $payroll, array $types): array
    {
        if (! $payroll) {
            return [];
        }

        $items = $payroll->items
            ->filter(fn ($item) => in_array(strtolower((string) $item->item_type), $types, true))
            ->map(fn ($item) => [
                'id' => $item->id,
                'code' => $item->item_code,
                'name' => $item->item_name,
                'amount' => $this->amount($item->amount),
                'is_taxable' => (bool) $item->is_taxable,
            ])
            ->values()
            ->all();

        if (! empty($items)) {
            return $items;
        }

        return $this->fallbackPayrollItems($payroll, $types);
    }

    private function fallbackPayrollItems(EnterprisePayrollM $payroll, array $types): array
    {
        $isDeduction = in_array('deduction', $types, true);
        $fields = $isDeduction
            ? [
                'professional_tax' => 'Professional Tax',
                'tds' => 'TDS',
                'attendance_deduction' => 'Attendance Deduction',
                'lwp_deduction' => 'LWP Deduction',
                'half_day_deduction' => 'Half Day Deduction',
                'absent_deduction' => 'Absent Deduction',
                'other_deduction' => 'Other Deduction',
            ]
            : [
                'basic_salary' => 'Basic Salary',
                'hra' => 'HRA',
                'special_allowance' => 'Special Allowance',
                'bonus_amount' => 'Bonus',
                'incentive_amount' => 'Incentive',
                'reimbursement_amount' => 'Reimbursement',
            ];

        $items = [];
        foreach ($fields as $field => $label) {
            $amount = $this->amount($payroll->{$field} ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $items[] = [
                'id' => null,
                'code' => $field,
                'name' => $label,
                'amount' => $amount,
                'is_taxable' => false,
            ];
        }

        return $items;
    }

    private function monthName(mixed $month, mixed $year = null): ?string
    {
        $month = (int) ($month ?? 0);
        if ($month < 1 || $month > 12) {
            return null;
        }

        return Carbon::create((int) ($year ?: now()->year), $month, 1)->format('F');
    }

    private function date(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $value instanceof Carbon
            ? $value->toDateString()
            : Carbon::parse($value)->toDateString();
    }

    private function dateTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $value instanceof Carbon
            ? $value->format('Y-m-d H:i:s')
            : Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
