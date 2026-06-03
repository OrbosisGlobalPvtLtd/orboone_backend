<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseBonusIncentiveM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollAuditM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollItemM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\HRMS\Attendance\AttendanceM;

class EnterprisePayrollCalculatorS
{
    public function __construct(
        private EnterpriseAttendanceLeaveResolverS $attendanceResolver,
        private EnterprisePayrollPolicyS $policyService,
        private NotificationS $notificationService
    ) {
    }

    public function preview(int $month, int $year, ?int $employeeId = null): array
    {
        $employeesQuery = EmployeeM::query()->active()->with('user')->orderBy('id');
        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        }
        $employees = $employeesQuery->get();
        $rows = [];
        $errors = [];

        foreach ($employees as $employee) {
            try {
                $rows[] = $this->calculateEmployee($employee, $month, $year);
            } catch (ValidationException $e) {
                $errors[] = [
                    'employee_id' => $employee->id,
                    'employee' => $employee->display_name,
                    'error' => collect($e->errors())->flatten()->first() ?: $e->getMessage(),
                ];
            } catch (\Throwable $e) {
                $errors[] = [
                    'employee_id' => $employee->id,
                    'employee' => $employee->display_name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return compact('rows', 'errors');
    }

    public function generate(int $month, int $year, ?int $actorId = null, ?int $employeeId = null): EnterprisePayrollRunM
    {
        return DB::transaction(function () use ($month, $year, $actorId, $employeeId) {
            $run = EnterprisePayrollRunM::query()
                ->where('month', $month)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($run && in_array($run->status, ['approved', 'locked', 'paid'], true)) {
                throw ValidationException::withMessages([
                    'payroll_run' => 'Locked or approved payroll run cannot be recalculated.',
                ]);
            }

            $run = $run ?: EnterprisePayrollRunM::create([
                'month' => $month,
                'year' => $year,
                'status' => 'draft',
            ]);

            $draftQuery = EnterprisePayrollM::query()
                ->where('payroll_run_id', $run->id)
                ->where('status', 'draft');
            if ($employeeId) {
                $draftQuery->where('employee_id', $employeeId);
            }
            $draftQuery->delete();

            $employeesQuery = EmployeeM::query()->active()->with('user')->orderBy('id');
            if ($employeeId) {
                $employeesQuery->where('id', $employeeId);
            }
            $employees = $employeesQuery->get();

            $runErrors = [];
            foreach ($employees as $employee) {
                try {
                    $calculation = $this->calculateEmployee($employee, $month, $year);
                    $payroll = $this->storePayroll($run, $employee, $calculation, $actorId);
                } catch (ValidationException $e) {
                    $runErrors[] = "{$employee->display_name}: " . (collect($e->errors())->flatten()->first() ?: $e->getMessage());
                    continue;
                } catch (\Throwable $e) {
                    $runErrors[] = "{$employee->display_name}: " . $e->getMessage();
                    continue;
                }
            }

            $totals = [
                'total_employees' => (int) EnterprisePayrollM::where('payroll_run_id', $run->id)->count(),
                'total_gross' => (float) EnterprisePayrollM::where('payroll_run_id', $run->id)->sum('gross_salary'),
                'total_deductions' => (float) EnterprisePayrollM::where('payroll_run_id', $run->id)->sum('total_deductions'),
                'total_net' => (float) EnterprisePayrollM::where('payroll_run_id', $run->id)->sum('net_salary'),
            ];

            $remarks = !empty($runErrors) ? "Warnings/Skipped:\n" . implode("\n", $runErrors) : null;

            $run->update($totals + [
                'status' => 'processed',
                'processed_by_user_id' => $actorId,
                'processed_at' => Carbon::now('Asia/Kolkata'),
                'remarks' => $remarks,
            ]);

            $this->audit('generated', $run, null, null, null, $totals, $actorId);
            $this->notificationService->notifyHrAndSuperAdmin(
                'Enterprise Payroll Generated',
                "Enterprise payroll for {$month}/{$year} has been generated.",
                'enterprise_payroll_generated',
                'enterprise-payroll.runs.show',
                ['run' => $run->id],
                ['payroll_run_id' => $run->id]
            );

            return $run->fresh('payrolls.employee.user');
        });
    }

    public function approve(EnterprisePayrollRunM $run, ?int $actorId = null): EnterprisePayrollRunM
    {
        return DB::transaction(function () use ($run, $actorId) {
            $run = EnterprisePayrollRunM::query()->lockForUpdate()->findOrFail($run->id);
            $this->guardRunUnlocked($run);
            if ($run->status !== 'processed') {
                throw ValidationException::withMessages(['status' => 'Only generated payroll can be approved.']);
            }

            $run->payrolls()->update([
                'status' => 'approved',
                'approved_by_user_id' => $actorId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
            ]);

            $run->update([
                'status' => 'approved',
                'approved_by_user_id' => $actorId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
            ]);

            $this->audit('approved', $run, null, null, null, ['status' => 'approved'], $actorId);
            $this->notificationService->notifyHrAndSuperAdmin(
                'Enterprise Payroll Approved',
                "Enterprise payroll for {$run->month}/{$run->year} has been approved.",
                'enterprise_payroll_approved',
                'enterprise-payroll.runs.show',
                ['run' => $run->id],
                ['payroll_run_id' => $run->id]
            );

            return $run->fresh('payrolls');
        });
    }

    public function lock(EnterprisePayrollRunM $run, ?int $actorId = null): EnterprisePayrollRunM
    {
        return DB::transaction(function () use ($run, $actorId) {
            $run = EnterprisePayrollRunM::query()->lockForUpdate()->findOrFail($run->id);
            if ($run->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Only approved payroll can be locked.']);
            }

            $lockedAt = Carbon::now('Asia/Kolkata');

            $run->load('payrolls');
            foreach ($run->payrolls as $payroll) {
                EnterpriseBonusIncentiveM::query()
                    ->where('employee_id', $payroll->employee_id)
                    ->where('month', $payroll->month)
                    ->where('year', $payroll->year)
                    ->where('status', 'approved')
                    ->whereNull('paid_in_payroll_id')
                    ->update([
                        'paid_in_payroll_id' => $payroll->id,
                        'status' => 'paid',
                    ]);

                EnterpriseReimbursementM::query()
                    ->where('employee_id', $payroll->employee_id)
                    ->whereMonth('claim_date', $payroll->month)
                    ->whereYear('claim_date', $payroll->year)
                    ->where('status', 'approved')
                    ->whereNull('paid_in_payroll_id')
                    ->update([
                        'paid_in_payroll_id' => $payroll->id,
                        'status' => 'paid',
                    ]);
            }

            $run->payrolls()->update([
                'status' => 'locked',
                'locked_at' => $lockedAt,
            ]);

            $run->update([
                'status' => 'locked',
                'locked_by_user_id' => $actorId,
                'locked_at' => $lockedAt,
            ]);

            $run->load('payrolls.employee.user');
            foreach ($run->payrolls as $payroll) {
                if ($payroll->employee && $payroll->employee->user_id) {
                    $monthName = Carbon::create($payroll->year, $payroll->month, 1)->format('F');
                    $this->notificationService->notifyEmployee(
                        'Payroll Released',
                        "Your payroll for {$monthName} {$payroll->year} has been released and processed.",
                        'payroll_released',
                        'enterprise-payroll.self.payslips',
                        [],
                        [
                            'payroll_id' => $payroll->id,
                            'month' => $payroll->month,
                            'year' => $payroll->year,
                        ],
                        (int) $payroll->employee->user_id
                    );
                }
            }

            $this->audit('locked', $run, null, null, null, ['status' => 'locked'], $actorId);

            return $run->fresh('payrolls');
        });
    }

    public function reopen(EnterprisePayrollRunM $run, ?int $actorId = null): EnterprisePayrollRunM
    {
        return DB::transaction(function () use ($run, $actorId) {
            $actor = auth()->user();
            if (! $actor || ! method_exists($actor, 'isSuperAdmin') || ! $actor->isSuperAdmin()) {
                throw ValidationException::withMessages(['status' => 'Only Super Admin can reopen locked enterprise payroll.']);
            }

            $run = EnterprisePayrollRunM::query()->lockForUpdate()->findOrFail($run->id);
            if ($run->status !== 'locked') {
                throw ValidationException::withMessages(['status' => 'Only locked payroll can be reopened.']);
            }

            $run->payrolls()->update(['status' => 'draft']);
            $run->update([
                'status' => 'draft',
                'reopened_by_user_id' => $actorId,
                'reopened_at' => Carbon::now('Asia/Kolkata'),
            ]);

            $this->audit('reopened', $run, null, null, null, ['status' => 'draft'], $actorId);

            return $run->fresh('payrolls');
        });
    }

    public function calculateEmployee(EmployeeM $employee, int $month, int $year): array
    {
        $unresolvedRecord = AttendanceM::query()
            ->where('employee_id', $employee->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->where(function ($q) {
                $q->where('attendance_status', 'pending_hr')
                    ->orWhere('attendance_status', 'missed_punch')
                    ->orWhere('attendance_status', 'punch_blocked')
                    ->orWhere('is_punch_blocked', true)
                    ->orWhere('is_blocked', true)
                    ->orWhere('is_missed_punch', true)
                    ->orWhere('missed_punch', true);
            })
            ->first();

        if ($unresolvedRecord) {
            $formattedDate = Carbon::parse($unresolvedRecord->attendance_date)->format('Y-m-d');
            if ($unresolvedRecord->attendance_status === 'missed_punch' || $unresolvedRecord->missed_punch || $unresolvedRecord->is_missed_punch) {
                throw ValidationException::withMessages([
                    'attendance' => "Unresolved missed punch exists for {$formattedDate}. Regularization approval or LWP conversion required.",
                ]);
            }
            throw ValidationException::withMessages([
                'attendance' => 'Attendance contains unresolved records. Please resolve before payroll processing.',
            ]);
        }

        $salary = $this->activeSalaryStructure($employee, $month, $year);
        $attendance = $this->attendanceResolver->resolve($employee, $month, $year);
        $policy = $this->policyService->getActivePolicy();

        $grossMonthlyForBasis = round(
            (float) $salary->basic_monthly
            + (float) $salary->hra_monthly
            + (float) $salary->special_allowance_monthly,
            2
        );
        $policyWorkingDays = $this->policyService->policyWorkingDays($policy, $attendance, $month, $year);
        $perDaySalary = $this->policyService->perDaySalary($grossMonthlyForBasis, $policyWorkingDays);
        $lwpDeduction = $this->policyService->deductionByRatio($perDaySalary, (float) $attendance['lwp_days'], (float) $policy->lwp_payable_ratio);
        $halfDayDeduction = $this->policyService->deductionByRatio($perDaySalary, (float) $attendance['half_days'], (float) $policy->half_day_payable_ratio);
        $absentDeduction = $this->policyService->deductionByRatio($perDaySalary, (float) $attendance['absent_days'], (float) $policy->absent_payable_ratio);
        $attendanceDeduction = round($lwpDeduction + $halfDayDeduction + $absentDeduction, 2);

        $bonusAmount = $this->approvedBonusIncentiveAmount($employee->id, $month, $year, 'bonus');
        $incentiveAmount = $this->approvedBonusIncentiveAmount($employee->id, $month, $year, 'incentive');
        $reimbursementAmount = $this->approvedReimbursementAmount($employee->id, $month, $year);

        $grossSalary = round(
            (float) $salary->basic_monthly
            + (float) $salary->hra_monthly
            + (float) $salary->special_allowance_monthly
            + $bonusAmount
            + $incentiveAmount
            + $reimbursementAmount,
            2
        );

        $professionalTax = (bool) $policy->professional_tax_enabled ? (float) $policy->professional_tax_amount : 0.0;
        $pfAmount = (bool) $policy->pf_enabled ? round($grossMonthlyForBasis * ((float) $policy->pf_percentage / 100), 2) : 0.0;
        $esiAmount = (bool) $policy->esi_enabled ? round($grossMonthlyForBasis * ((float) $policy->esi_percentage / 100), 2) : 0.0;
        $tdsSource = $this->policyService->tdsSource($policy);
        if (! (bool) $policy->tds_enabled) {
            $tdsAmount = 0.0;
        } elseif ($tdsSource === 'salary_structure') {
            $tdsAmount = round((float) $salary->tds_monthly, 2);
        } else {
            $tdsAmount = round($grossMonthlyForBasis * ((float) $policy->tds_percentage / 100), 2);
        }

        $policyPayableDays = $this->policyService->payableDays($policy, $attendance);

        $totalDeductions = round(
            $professionalTax
            + $pfAmount
            + $esiAmount
            + $tdsAmount
            + $attendanceDeduction
            + (float) $salary->other_deduction_monthly,
            2
        );

        $netSalary = round($grossSalary - $totalDeductions, 2);
        if (! (bool) $policy->allow_negative_salary && $netSalary < 0) {
            $netSalary = 0;
        }

        $policySnapshot = $this->policyService->snapshot($policy, $month, $year, $policyWorkingDays, $perDaySalary);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->display_name,
            'salary_structure_id' => $salary->id,
            'attendance' => $attendance,
            'annual_ctc' => (float) $salary->annual_ctc,
            'monthly_ctc' => (float) $salary->monthly_ctc,
            'per_day_salary' => $perDaySalary,
            'policy_working_days' => $policyWorkingDays,
            'calendar_days' => (int) ($attendance['calendar_days'] ?? Carbon::create($year, $month, 1)->daysInMonth),
            'basic_salary' => (float) $salary->basic_monthly,
            'hra' => (float) $salary->hra_monthly,
            'special_allowance' => (float) $salary->special_allowance_monthly,
            'gross_salary' => $grossSalary,
            'professional_tax' => $professionalTax,
            'pf' => $pfAmount,
            'esi' => $esiAmount,
            'tds' => $tdsAmount,
            'attendance_deduction' => $attendanceDeduction,
            'lwp_deduction' => $lwpDeduction,
            'half_day_deduction' => $halfDayDeduction,
            'absent_deduction' => $absentDeduction,
            'other_deduction' => (float) $salary->other_deduction_monthly,
            'total_deductions' => $totalDeductions,
            'bonus_amount' => $bonusAmount,
            'incentive_amount' => $incentiveAmount,
            'reimbursement_amount' => $reimbursementAmount,
            'net_salary' => $netSalary,
            'net_salary_words' => $this->amountInWords($netSalary),
            'payable_days' => $policyPayableDays,
            'policy_snapshot' => $policySnapshot,
        ];
    }

    private function storePayroll(EnterprisePayrollRunM $run, EmployeeM $employee, array $calculation, ?int $actorId): EnterprisePayrollM
    {
        $attendance = $calculation['attendance'];

        $payload = [
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'month' => $run->month,
            'year' => $run->year,
            'status' => 'generated',
            'generated_at' => Carbon::now('Asia/Kolkata'),
            'total_working_days' => $attendance['total_working_days'],
            'present_days' => $attendance['present_days'],
            'paid_leave_days' => $attendance['paid_leave_days'],
            'sick_leave_days' => $attendance['sick_leave_days'],
            'comp_off_days' => $attendance['comp_off_days'],
            'holiday_days' => $attendance['holiday_days'],
            'week_off_days' => $attendance['week_off_days'],
            'half_days' => $attendance['half_days'],
            'lwp_days' => $attendance['lwp_days'],
            'absent_days' => $attendance['absent_days'],
            'late_count' => $attendance['late_count'],
            'early_out_count' => $attendance['early_out_count'],
            'missed_punch_count' => $attendance['missed_punch_count'],
            'payable_days' => $calculation['payable_days'],
            'annual_ctc' => $calculation['annual_ctc'],
            'monthly_ctc' => $calculation['monthly_ctc'],
            'per_day_salary' => $calculation['per_day_salary'],
            'basic_salary' => $calculation['basic_salary'],
            'hra' => $calculation['hra'],
            'special_allowance' => $calculation['special_allowance'],
            'gross_salary' => $calculation['gross_salary'],
            'professional_tax' => $calculation['professional_tax'],
            'tds' => $calculation['tds'],
            'attendance_deduction' => $calculation['attendance_deduction'],
            'lwp_deduction' => $calculation['lwp_deduction'],
            'half_day_deduction' => $calculation['half_day_deduction'],
            'absent_deduction' => $calculation['absent_deduction'],
            'other_deduction' => $calculation['other_deduction'],
            'total_deductions' => $calculation['total_deductions'],
            'bonus_amount' => $calculation['bonus_amount'],
            'incentive_amount' => $calculation['incentive_amount'],
            'reimbursement_amount' => $calculation['reimbursement_amount'],
            'net_salary' => $calculation['net_salary'],
            'net_salary_words' => $calculation['net_salary_words'],
            'calculation_snapshot' => $calculation,
        ];

        $payroll = EnterprisePayrollM::updateOrCreate(
            ['employee_id' => $employee->id, 'month' => $run->month, 'year' => $run->year],
            $payload
        );

        $payroll->items()->delete();
        foreach ($this->items($calculation) as $item) {
            EnterprisePayrollItemM::create($item + ['payroll_id' => $payroll->id]);
        }

        $this->audit('employee_generated', $run, $payroll, $employee->id, null, $payload, $actorId);

        return $payroll;
    }

    private function activeSalaryStructure(EmployeeM $employee, int $month, int $year): EnterpriseSalaryStructureM
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $salary = EnterpriseSalaryStructureM::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', $end)
            ->where(function ($query) use ($start) {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $start);
            })
            ->orderByDesc('effective_from')
            ->first();

        if (! $salary) {
            throw ValidationException::withMessages([
                'salary_structure' => "No active salary structure found for {$employee->display_name}.",
            ]);
        }

        return $salary;
    }

    private function approvedBonusIncentiveAmount(int $employeeId, int $month, int $year, string $type): float
    {
        return (float) EnterpriseBonusIncentiveM::query()
            ->where('employee_id', $employeeId)
            ->where('type', $type)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->whereNull('paid_in_payroll_id')
            ->sum('amount');
    }

    private function approvedReimbursementAmount(int $employeeId, int $month, int $year): float
    {
        return (float) EnterpriseReimbursementM::query()
            ->where('employee_id', $employeeId)
            ->whereMonth('claim_date', $month)
            ->whereYear('claim_date', $year)
            ->where('status', 'approved')
            ->whereNull('paid_in_payroll_id')
            ->sum('approved_amount');
    }

    private function items(array $calculation): array
    {
        return [
            ['item_type' => 'earning', 'item_code' => 'basic', 'item_name' => 'Basic Salary', 'amount' => $calculation['basic_salary'], 'is_taxable' => true],
            ['item_type' => 'earning', 'item_code' => 'hra', 'item_name' => 'HRA', 'amount' => $calculation['hra'], 'is_taxable' => true],
            ['item_type' => 'earning', 'item_code' => 'special_allowance', 'item_name' => 'Special Allowance', 'amount' => $calculation['special_allowance'], 'is_taxable' => true],
            ['item_type' => 'earning', 'item_code' => 'bonus', 'item_name' => 'Bonus', 'amount' => $calculation['bonus_amount'], 'is_taxable' => true],
            ['item_type' => 'earning', 'item_code' => 'incentive', 'item_name' => 'Incentive', 'amount' => $calculation['incentive_amount'], 'is_taxable' => true],
            ['item_type' => 'earning', 'item_code' => 'reimbursement', 'item_name' => 'Reimbursement', 'amount' => $calculation['reimbursement_amount'], 'is_taxable' => false],
            ['item_type' => 'deduction', 'item_code' => 'professional_tax', 'item_name' => 'Professional Tax', 'amount' => $calculation['professional_tax'], 'is_taxable' => false],
            ['item_type' => 'deduction', 'item_code' => 'pf', 'item_name' => 'PF', 'amount' => $calculation['pf'] ?? 0, 'is_taxable' => false],
            ['item_type' => 'deduction', 'item_code' => 'esi', 'item_name' => 'ESI', 'amount' => $calculation['esi'] ?? 0, 'is_taxable' => false],
            ['item_type' => 'deduction', 'item_code' => 'tds', 'item_name' => 'TDS', 'amount' => $calculation['tds'], 'is_taxable' => false],
            ['item_type' => 'deduction', 'item_code' => 'attendance_deduction', 'item_name' => 'Attendance Deduction', 'amount' => $calculation['attendance_deduction'], 'is_taxable' => false],
            ['item_type' => 'deduction', 'item_code' => 'other_deduction', 'item_name' => 'Other Deduction', 'amount' => $calculation['other_deduction'], 'is_taxable' => false],
        ];
    }

    private function guardRunUnlocked(EnterprisePayrollRunM $run): void
    {
        if ($run->status === 'locked') {
            throw ValidationException::withMessages(['status' => 'Locked payroll must never be recalculated or edited.']);
        }
    }

    private function audit(string $action, EnterprisePayrollRunM $run, ?EnterprisePayrollM $payroll, ?int $employeeId, ?array $old, ?array $new, ?int $actorId): void
    {
        EnterprisePayrollAuditM::create([
            'payroll_run_id' => $run->id,
            'payroll_id' => optional($payroll)->id,
            'employee_id' => $employeeId,
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'performed_by_user_id' => $actorId,
        ]);
    }

    private function amountInWords(float $amount): string
    {
        $whole = (int) round($amount);
        if ($whole === 0) {
            return 'Zero Rupees Only';
        }

        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
            return ucwords($formatter->format($whole)) . ' Rupees Only';
        }

        return number_format($whole) . ' Rupees Only';
    }
}
