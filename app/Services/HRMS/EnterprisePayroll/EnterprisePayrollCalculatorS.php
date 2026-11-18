<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Services\HRMS\Attendance\PayrollAttendanceSummaryService;
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
use App\Services\HRMS\Employee\EmployeeEligibilityS;
use Illuminate\Support\Facades\Log;

class EnterprisePayrollCalculatorS
{
    public function __construct(
        private EnterpriseAttendanceLeaveResolverS $attendanceResolver,
        private EnterprisePayrollPolicyS $policyService,
        private EnterpriseStatutoryCalculatorS $statutoryCalculator,
        private NotificationS $notificationService,
        private ?PayrollAttendanceSummaryService $attendanceSummaryService = null
    ) {
        $this->attendanceSummaryService = $this->attendanceSummaryService ?: app(PayrollAttendanceSummaryService::class);
    }

    public function preview(int $month, int $year, ?int $employeeId = null): array
    {
        $this->attendanceSummaryService->generate($month, $year, $employeeId);

        $employeesQuery = EmployeeM::query()->active()->with('user')->orderBy('id');
        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        }
        $employees = $employeesQuery->get();
        $rows = [];
        $errors = [];

        $eligibilityService = app(EmployeeEligibilityS::class);
        foreach ($employees as $employee) {
            if (!$eligibilityService->canUsePayroll($employee, $month, $year)) {
                Log::info("Payroll generation skipped for employee #{$employee->id} ({$employee->display_name}): Profile pending, exit completed, terminated, unpaid intern, or not onboarded in {$month}/{$year}.");
                continue;
            }
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

        $hasPendingRegularizations = DB::table('attendance_regularizations')
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_regularizations.attendance_id')
            ->where('attendance_regularizations.status', 'pending')
            ->where(function ($q) use ($month, $year) {
                $q->where(function ($sub) use ($month, $year) {
                    $sub->whereMonth('attendances.attendance_date', $month)
                        ->whereYear('attendances.attendance_date', $year);
                })
                ->orWhere(function ($sub) use ($month, $year) {
                    $sub->whereMonth('attendance_regularizations.requested_punch_in', $month)
                        ->whereYear('attendance_regularizations.requested_punch_in', $year);
                })
                ->orWhere(function ($sub) use ($month, $year) {
                    $sub->whereMonth('attendance_regularizations.created_at', $month)
                        ->whereYear('attendance_regularizations.created_at', $year);
                });
            })
            ->when($employeeId, fn ($q) => $q->where('attendance_regularizations.employee_id', $employeeId))
            ->exists();

        return compact('rows', 'errors', 'hasPendingRegularizations');
    }

    public function generate(int $month, int $year, ?int $actorId = null, ?int $employeeId = null): EnterprisePayrollRunM
    {
        $this->attendanceSummaryService->generate($month, $year, $employeeId);

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
            $eligibilityService = app(EmployeeEligibilityS::class);
            foreach ($employees as $employee) {
                if (!$eligibilityService->canUsePayroll($employee, $month, $year)) {
                    Log::info("Payroll generation skipped for employee #{$employee->id} ({$employee->display_name}): Profile pending, exit completed, terminated, unpaid intern, or not onboarded in {$month}/{$year}.");
                    continue;
                }
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
                    ->where('status', 'approved')
                    ->whereNull('paid_in_payroll_id')
                    ->where(function ($q) use ($payroll) {
                        $q->where('year', '<', $payroll->year)
                            ->orWhere(function ($sq) use ($payroll) {
                                $sq->where('year', $payroll->year)->where('month', '<=', $payroll->month);
                            });
                    })
                    ->update([
                        'paid_in_payroll_id' => $payroll->id,
                        'status' => 'paid',
                    ]);

                $periodEnd = Carbon::create($payroll->year, $payroll->month, 1)->endOfMonth()->toDateString();
                EnterpriseReimbursementM::query()
                    ->where('employee_id', $payroll->employee_id)
                    ->where('status', 'approved')
                    ->whereNull('paid_in_payroll_id')
                    ->where(function ($q) use ($payroll, $periodEnd) {
                        $q->whereDate('claim_date', '<=', $periodEnd)
                            ->orWhere(function ($sq) use ($payroll) {
                                $sq->whereMonth('approved_at', $payroll->month)->whereYear('approved_at', $payroll->year);
                            });
                    })
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

            $payrollIds = $run->payrolls()->pluck('id');
            if ($payrollIds->isNotEmpty()) {
                EnterpriseBonusIncentiveM::query()
                    ->whereIn('paid_in_payroll_id', $payrollIds)
                    ->update([
                        'status' => 'approved',
                        'paid_in_payroll_id' => null,
                    ]);

                EnterpriseReimbursementM::query()
                    ->whereIn('paid_in_payroll_id', $payrollIds)
                    ->update([
                        'status' => 'approved',
                        'paid_in_payroll_id' => null,
                    ]);
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
        $blockedRecord = AttendanceM::query()
            ->where('employee_id', $employee->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->where(function ($q) {
                $q->where('attendance_status', 'punch_blocked')
                    ->orWhere('is_punch_blocked', true)
                    ->orWhere('is_blocked', true);
            })
            ->first();

        if ($blockedRecord) {
            $formattedDate = Carbon::parse($blockedRecord->attendance_date)->format('Y-m-d');
            throw ValidationException::withMessages([
                'attendance' => "Punch blocked attendance exists for {$formattedDate}. HR resolution required before payroll processing.",
            ]);
        }

        $salary = $this->activeSalaryStructure($employee, $month, $year);
        $attendance = $this->attendanceResolver->resolve($employee, $month, $year);
        $policy = $this->policyService->getActivePolicy($employee->company_id ?? null);

        $effectiveDate = Carbon::create($year, $month, 1)->toDateString();
        $monthlyCtc = (float) ($salary->monthly_ctc > 0 ? $salary->monthly_ctc : ($salary->annual_ctc / 12));
        if ($monthlyCtc <= 0) {
            $monthlyCtc = round((float) $salary->basic_monthly + (float) $salary->hra_monthly + (float) $salary->special_allowance_monthly, 2);
        }
        $breakdown = app(EnterpriseSalaryComponentRulesS::class)->breakdown($monthlyCtc, $policy->id ? (int) $policy->id : null, $effectiveDate);

        $basicMonthly = (float) ($salary->basic_monthly > 0 ? $salary->basic_monthly : $breakdown['basic']);
        $hraMonthly = (float) ($salary->hra_monthly > 0 ? $salary->hra_monthly : $breakdown['hra']);
        $specialAllowanceMonthly = (float) ($salary->special_allowance_monthly > 0 ? $salary->special_allowance_monthly : $breakdown['special_allowance']);

        if ($monthlyCtc > 0) {
            $basicMonthly = $breakdown['basic'];
            $hraMonthly = $breakdown['hra'];
            $specialAllowanceMonthly = $breakdown['special_allowance'];
        }

        $grossMonthlyForBasis = round($basicMonthly + $hraMonthly + $specialAllowanceMonthly, 2);
        $policyWorkingDays = $this->policyService->policyWorkingDays($policy, $attendance, $month, $year);
        $calendarDays = (int) ($attendance['calendar_days'] ?? Carbon::create($year, $month, 1)->daysInMonth);
        $salaryDayBase = max(1, (int) ($policy->salary_day_base ?? 26)); // DB policy based (default 26)
        $perDaySalary = round($grossMonthlyForBasis / $salaryDayBase, 4);

        $policyPayableDays = $this->policyService->payableDays($policy, $attendance);
        if ($policyPayableDays < 0 || $policyPayableDays > $calendarDays) {
            throw ValidationException::withMessages([
                'attendance' => "Resolved payable days ({$policyPayableDays}) exceed calendar days ({$calendarDays}) for {$employee->display_name}. Review attendance, holiday and leave totals before payroll.",
            ]);
        }

        // Required 26-Day Formula:
        // Step 1: Salary Days = (Payable Days × 26) / Calendar Days
        $rawSalaryDays = ($policyPayableDays * $salaryDayBase) / max(1, $calendarDays);
        $salaryDays = round($rawSalaryDays, 5);
        $rawDeductionDays = max(0.0, $salaryDayBase - $rawSalaryDays);
        $deductionDays = max(0.0, round($salaryDayBase - $salaryDays, 5));

        // Step 2: Unpaid Deduction = Gross Salary × (26 − Salary Days) / 26
        $attendanceDeduction = round(($grossMonthlyForBasis * $rawDeductionDays) / $salaryDayBase, 2);

        // Step 3: Earned Salary = Gross Salary − Unpaid Deduction
        $earnedBaseGross = round(max(0.0, $grossMonthlyForBasis - $attendanceDeduction), 2);

        $missedPunchCount = (int) ($attendance['missed_punch_count'] ?? 0);
        $earlyOutCount = (int) ($attendance['early_out_count'] ?? 0);

        // 3rd occurrence policy: 1st warning, 2nd warning, 3rd triggers 1 day deduction (cycle resets)
        $missedPunchDeductionDays = intdiv($missedPunchCount, 3);
        $earlyOutDeductionDays = intdiv($earlyOutCount, 3);

        $lwpDays = (float) ($attendance['lwp_days'] ?? 0);
        $halfDays = (float) ($attendance['half_days'] ?? 0);
        $absentDays = (float) ($attendance['absent_days'] ?? 0);

        // Missed Punch is an attendance penalty that converts the violation day into LWP.
        // It is NOT an additional financial penalty. Therefore, if converted LWP is already in $lwpDays,
        // it is included in LWP and not deducted separately.
        $missedPunchIncludedInLwp = ($missedPunchDeductionDays > 0 && $lwpDays >= $missedPunchDeductionDays);

        $totalUnpaidDays = $lwpDays + ($halfDays * 0.5) + $absentDays;
        if ($totalUnpaidDays > 0 && $attendanceDeduction > 0) {
            $halfDayDeduction = round($attendanceDeduction * (($halfDays * 0.5) / $totalUnpaidDays), 2);
            $lwpDeduction = round(max(0.0, $attendanceDeduction - $halfDayDeduction), 2);
            $absentDeduction = 0.0;
        } else {
            $lwpDeduction = $attendanceDeduction;
            $halfDayDeduction = 0.0;
            $absentDeduction = 0.0;
        }

        $missedPunchDeduction = $missedPunchIncludedInLwp ? 0.0 : round($missedPunchDeductionDays * ($calendarDays > 0 ? $grossMonthlyForBasis / $calendarDays : 0.0), 2);
        $earlyOutDeduction = round($earlyOutDeductionDays * ($calendarDays > 0 ? $grossMonthlyForBasis / $calendarDays : 0.0), 2);

        $bonusAmount = $this->approvedBonusIncentiveAmount($employee->id, $month, $year, 'bonus');
        $incentiveAmount = $this->approvedBonusIncentiveAmount($employee->id, $month, $year, 'incentive');
        $reimbursementAmount = $this->approvedReimbursementAmount($employee->id, $month, $year);

        $grossSalary = round(
            $grossMonthlyForBasis
            + $bonusAmount
            + $incentiveAmount
            + $reimbursementAmount,
            2
        );

        $statutory = $this->statutoryCalculator->calculate(
            $employee,
            $policy,
            $month,
            $year,
            $grossMonthlyForBasis,
            $earnedBaseGross,
            (float) $salary->basic_monthly * ($grossMonthlyForBasis > 0 ? $earnedBaseGross / $grossMonthlyForBasis : 0),
            $earnedBaseGross + $bonusAmount + $incentiveAmount
        );
        $professionalTax = $statutory['professional_tax'];
        $pfAmount = $statutory['pf'];
        $esiAmount = $statutory['esi'];
        $tdsAmount = $statutory['tds'];

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
            'employee_code' => $employee->employee_code,
            'department_name' => optional($employee->department)->name,
            'designation_name' => optional($employee->designation)->name,
            'bank_account_no' => optional($employee->profile)->bank_account_no,
            'bank_ifsc' => optional($employee->profile)->ifsc_code,
            'salary_structure_id' => $salary->id,
            'salary_component_rules' => $salary->component_rules_snapshot,
            'policy_id' => $policy->id,
            'attendance' => $attendance,
            'annual_ctc' => (float) $salary->annual_ctc,
            'monthly_ctc' => (float) $salary->monthly_ctc,
            'per_day_salary' => $perDaySalary,
            'policy_working_days' => $policyWorkingDays,
            'calendar_days' => $calendarDays,
            'salary_days' => $salaryDays,
            'deduction_days' => $deductionDays,
            'salary_day_base' => $salaryDayBase,
            'contractual_monthly_gross' => $grossMonthlyForBasis,
            'earned_base_gross' => $earnedBaseGross,
            'basic_salary' => $basicMonthly,
            'hra' => $hraMonthly,
            'special_allowance' => $specialAllowanceMonthly,
            'gross_salary' => $grossSalary,
            'professional_tax' => $professionalTax,
            'pf' => $pfAmount,
            'esi' => $esiAmount,
            'tds' => $tdsAmount,
            'attendance_deduction' => $attendanceDeduction,
            'lwp_deduction' => $lwpDeduction,
            'half_day_deduction' => $halfDayDeduction,
            'absent_deduction' => $absentDeduction,
            'missed_punch_count' => $missedPunchCount,
            'missed_punch_deduction_days' => $missedPunchDeductionDays,
            'missed_punch_deduction' => $missedPunchDeduction,
            'missed_punch_included_in_lwp' => $missedPunchIncludedInLwp,
            'early_out_count' => $earlyOutCount,
            'early_out_deduction_days' => $earlyOutDeductionDays,
            'early_out_deduction' => $earlyOutDeduction,
            'other_deduction' => (float) $salary->other_deduction_monthly,
            'total_deductions' => $totalDeductions,
            'bonus_amount' => $bonusAmount,
            'incentive_amount' => $incentiveAmount,
            'reimbursement_amount' => $reimbursementAmount,
            'net_salary' => $netSalary,
            'net_salary_words' => $this->amountInWords($netSalary),
            'payable_days' => $policyPayableDays,
            'policy_snapshot' => $policySnapshot,
            'statutory_settings' => $statutory['settings'],
            'tds_calculation' => $statutory['tds_snapshot'],
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
            'present_days' => round($calculation['salary_days'], 2),
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
            'salary_structure_id' => $calculation['salary_structure_id'],
            'policy_id' => $calculation['policy_id'],
            'salary_days' => $calculation['salary_days'],
            'earned_base_gross' => $calculation['earned_base_gross'],
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

        // 1. Find structure effective within the payroll period (e.g. historical structures)
        $salary = EnterpriseSalaryStructureM::query()
            ->where('employee_id', $employee->id)
            ->whereDate('effective_from', '<=', $end)
            ->where(function ($query) use ($start) {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $start);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        // 2. Fallback: currently active structure
        if (! $salary) {
            $salary = EnterpriseSalaryStructureM::query()
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->orderByDesc('effective_from')
                ->first();
        }

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
            ->where('status', 'approved')
            ->whereNull('paid_in_payroll_id')
            ->where(function ($q) use ($month, $year) {
                $q->where('year', '<', $year)
                    ->orWhere(function ($sq) use ($month, $year) {
                        $sq->where('year', $year)->where('month', '<=', $month);
                    });
            })
            ->sum('amount');
    }

    private function approvedReimbursementAmount(int $employeeId, int $month, int $year): float
    {
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        return (float) EnterpriseReimbursementM::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereNull('paid_in_payroll_id')
            ->where(function ($q) use ($month, $year, $periodEnd) {
                $q->whereDate('claim_date', '<=', $periodEnd)
                    ->orWhere(function ($sq) use ($month, $year) {
                        $sq->whereMonth('approved_at', $month)->whereYear('approved_at', $year);
                    });
            })
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
        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);

        if ($whole === 0 && $fraction === 0) {
            return 'Zero Rupees Only';
        }

        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
            $rupeesText = ucwords($formatter->format($whole));
            if ($fraction > 0) {
                $paiseText = ucwords($formatter->format($fraction));
                return "{$rupeesText} Rupees and {$paiseText} Paise Only";
            }
            return "{$rupeesText} Rupees Only";
        }

        $rupeesText = number_format($whole);
        if ($fraction > 0) {
            return "{$rupeesText} Rupees and {$fraction} Paise Only";
        }

        return "{$rupeesText} Rupees Only";
    }
}
