<?php

namespace App\Services\HRMS\Payroll;

use App\Models\HRMS\Attendance\MonthlyAttendanceSummaryM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Payroll\ClaimM;
use App\Models\HRMS\Payroll\PayrollAdjustmentM;
use App\Models\HRMS\Payroll\PayrollM;
use App\Models\HRMS\Payroll\SalaryStructureM;
use App\Models\HRMS\Payroll\StatutorySettingM;
use App\Services\HRMS\Attendance\PayrollAttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollCalculationService
{
    /*
     * LEGACY PAYROLL MODULE - DO NOT USE for new enterprise payroll.
     * This service remains only for old payroll backup/reference flows.
     */
    public const STATUS_GENERATED = 'generated';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_LOCKED = 'locked';

    public function __construct(private PayrollAttendanceSummaryService $attendanceSummaryService)
    {
    }

    public function generateMonth(int $month, int $year, ?int $employeeId = null, ?int $actorId = null): array
    {
        $this->attendanceSummaryService->generate($month, $year, $employeeId);

        $employees = EmployeeM::query()
            ->with('salaryStructure')
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->active()
            ->get();

        $result = [
            'generated' => 0,
            'skipped_locked' => 0,
            'skipped_missing_salary_structure' => 0,
            'skipped_missing_attendance_summary' => 0,
        ];

        foreach ($employees as $employee) {
            try {
                $payroll = $this->generateForEmployee($employee, $month, $year, $actorId);
                if ($payroll) {
                    $result['generated']++;
                }
            } catch (ValidationException $e) {
                $key = array_key_first($e->errors()) ?: 'skipped_missing_salary_structure';
                if (array_key_exists($key, $result)) {
                    $result[$key]++;
                }
            }
        }

        return $result;
    }

    public function generateForEmployee(EmployeeM $employee, int $month, int $year, ?int $actorId = null): ?PayrollM
    {
        return DB::transaction(function () use ($employee, $month, $year, $actorId) {
            $existing = PayrollM::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($existing && strtolower((string) $existing->status) === self::STATUS_LOCKED) {
                throw ValidationException::withMessages(['skipped_locked' => 'Locked payroll cannot be recalculated.']);
            }

            $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata')->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
            $structure = $this->activeSalaryStructure($employee, $periodEnd);

            if (! $structure) {
                throw ValidationException::withMessages(['skipped_missing_salary_structure' => 'Employee has no active salary structure.']);
            }

            $summary = MonthlyAttendanceSummaryM::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (! $summary) {
                throw ValidationException::withMessages(['skipped_missing_attendance_summary' => 'Monthly attendance summary is missing.']);
            }

            $values = $this->calculateValues($employee, $structure, $summary, $month, $year);

            $payroll = PayrollM::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'year' => $year,
                ],
                array_merge($values, [
                    'salary_structure_id' => $structure->id,
                    'monthly_attendance_summary_id' => $summary->id,
                    'status' => $existing?->status ?: self::STATUS_GENERATED,
                    'generated_by' => $actorId,
                    'generated_at' => Carbon::now('Asia/Kolkata'),
                ])
            );

            PayrollAdjustmentM::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', 'approved')
                ->whereIn('type', ['bonus', 'incentive', 'tds', 'deduction'])
                ->update([
                    'payroll_id' => $payroll->id,
                    'processed_at' => Carbon::now('Asia/Kolkata'),
                ]);

            ClaimM::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('payroll_month', $month)
                ->where('payroll_year', $year)
                ->update(['updated_at' => Carbon::now('Asia/Kolkata')]);

            DB::table('payroll_attendance_impacts')
                ->where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('is_processed_in_payroll', 0)
                ->update([
                    'payroll_id' => $payroll->id,
                    'is_processed_in_payroll' => 1,
                    'processed_at' => Carbon::now('Asia/Kolkata'),
                    'updated_at' => Carbon::now('Asia/Kolkata'),
                ]);

            return $payroll->fresh(['employee', 'salaryStructure', 'attendanceSummary']);
        });
    }

    public function approve(PayrollM $payroll, int $actorId): PayrollM
    {
        if (strtolower((string) $payroll->status) === self::STATUS_LOCKED) {
            return $payroll;
        }

        $payroll->forceFill([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $actorId,
            'approved_at' => Carbon::now('Asia/Kolkata'),
        ])->save();

        return $payroll->fresh();
    }

    public function lockMonth(int $month, int $year, int $actorId): int
    {
        return PayrollM::where('month', $month)
            ->where('year', $year)
            ->whereIn('status', [self::STATUS_GENERATED, self::STATUS_APPROVED])
            ->update([
                'status' => self::STATUS_LOCKED,
                'locked_by' => $actorId,
                'locked_at' => Carbon::now('Asia/Kolkata'),
            ]);
    }

    public function canGeneratePayslip(PayrollM $payroll): bool
    {
        return in_array(strtolower((string) $payroll->status), [self::STATUS_APPROVED, self::STATUS_LOCKED], true);
    }

    private function calculateValues(EmployeeM $employee, SalaryStructureM $structure, MonthlyAttendanceSummaryM $summary, int $month, int $year): array
    {
        $workingDays = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kolkata')->daysInMonth;
        $payableDays = min($workingDays, max(0, (float) $summary->payable_days));
        $paidDays = (int) round($payableDays);

        $monthlyBasic = (float) $structure->basic_salary;
        $monthlyHra = round($monthlyBasic * ((float) $structure->hra_percent / 100), 2);
        $monthlyAllowance = (float) ($structure->allowance ?? 0);
        $monthlyGross = round($monthlyBasic + $monthlyHra + $monthlyAllowance, 2);
        $dailyGrossRate = $workingDays > 0 ? round($monthlyGross / $workingDays, 4) : 0;

        $basic = $this->prorate($monthlyBasic, $payableDays, $workingDays);
        $hra = $this->prorate($monthlyHra, $payableDays, $workingDays);
        $allowance = $this->prorate($monthlyAllowance, $payableDays, $workingDays);

        $bonus = $this->adjustmentTotal($employee->id, $month, $year, ['bonus']);
        $incentive = $this->adjustmentTotal($employee->id, $month, $year, ['incentive']);
        $reimbursements = $this->approvedClaimTotal($employee->id, $month, $year);
        $tds = $this->adjustmentTotal($employee->id, $month, $year, ['tds']);
        $otherDeductions = $this->adjustmentTotal($employee->id, $month, $year, ['deduction']);
        $pt = $this->professionalTax($structure, $basic + $hra + $allowance);

        $lwpDeduction = round((float) $summary->lwp_days * $dailyGrossRate, 2);
        $absentDeduction = round((float) $summary->absent_days * $dailyGrossRate, 2);
        $halfDayDeduction = round((float) $summary->half_days * 0.5 * $dailyGrossRate, 2);
        $attendanceLossDays = max(0, round($workingDays - $payableDays, 2));

        $grossSalary = round($basic + $hra + $allowance + $bonus + $incentive + $reimbursements, 2);
        $totalDeductions = round($pt + $tds + $otherDeductions, 2);
        $netSalary = round($grossSalary - $totalDeductions, 2);

        return [
            'working_days' => $workingDays,
            'paid_days' => $paidDays,
            'payable_days' => $payableDays,
            'present_days' => (float) $summary->present_days,
            'paid_leave_days' => (float) $summary->paid_leave_days,
            'sick_leave_days' => (float) $summary->sick_leave_days,
            'comp_off_days' => (float) $summary->comp_off_days,
            'holiday_days' => (float) $summary->holiday_days,
            'week_off_days' => (float) $summary->week_off_days,
            'half_days' => (float) $summary->half_days,
            'lwp_days' => (float) $summary->lwp_days,
            'absent_days' => (float) $summary->absent_days,
            'basic' => $basic,
            'hra' => $hra,
            'allowance' => $allowance,
            'monthly_gross_salary' => $monthlyGross,
            'daily_gross_rate' => $dailyGrossRate,
            'attendance_loss_days' => $attendanceLossDays,
            'lwp_deduction' => $lwpDeduction,
            'absent_deduction' => $absentDeduction,
            'half_day_deduction' => $halfDayDeduction,
            'bonus' => $bonus,
            'incentive' => $incentive,
            'reimbursements' => $reimbursements,
            'gross_salary' => $grossSalary,
            'pt' => $pt,
            'tds' => $tds,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'calculation_snapshot' => [
                'employee_id' => $employee->id,
                'salary_structure_id' => $structure->id,
                'monthly_attendance_summary_id' => $summary->id,
                'period' => ['month' => $month, 'year' => $year],
                'formula' => 'net = prorated basic + prorated hra + prorated allowance + bonus + incentive + reimbursements - pt - tds - other_deductions',
                'source_tables' => ['salary_structures', 'monthly_attendance_summaries', 'claims', 'payroll_adjustments'],
            ],
        ];
    }

    private function activeSalaryStructure(EmployeeM $employee, Carbon $periodEnd): ?SalaryStructureM
    {
        $structure = $employee->salaryStructure;

        if (! $structure) {
            return null;
        }

        if ((int) ($structure->status ?? 1) !== 1) {
            return null;
        }

        if ($structure->effective_date && Carbon::parse($structure->effective_date)->gt($periodEnd)) {
            return null;
        }

        return $structure;
    }

    private function adjustmentTotal(int $employeeId, int $month, int $year, array $types): float
    {
        return (float) PayrollAdjustmentM::where('employee_id', $employeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->whereIn('type', $types)
            ->sum('amount');
    }

    private function approvedClaimTotal(int $employeeId, int $month, int $year): float
    {
        return (float) ClaimM::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->sum('amount');
    }

    private function professionalTax(SalaryStructureM $structure, float $gross): float
    {
        if ((float) ($structure->pt_amount ?? 0) > 0) {
            return (float) $structure->pt_amount;
        }

        $settings = StatutorySettingM::query()->first();
        $ptPercent = (float) ($settings->pt_percent ?? 0);

        return $ptPercent > 0 ? round($gross * ($ptPercent / 100), 2) : 0.0;
    }

    private function prorate(float $amount, float $payableDays, int $workingDays): float
    {
        if ($amount <= 0 || $workingDays <= 0) {
            return 0.0;
        }

        return round($amount * ($payableDays / $workingDays), 2);
    }
}
