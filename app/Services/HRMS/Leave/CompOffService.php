<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\CompOffM;
use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveCompOffTransactionM;
use App\Models\HRMS\Leave\LeaveRequestDateM;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CompOffService
{
    public function __construct(
        private LeavePolicyService $policyService,
        private LeaveAllocationService $allocationService,
        private LeaveUnitPolicy $unitPolicy
    ) {
    }

    /** Create an auditable manual credit lot; its balance is the lot plus debit/reversal movements. */
    public function grantManualCompOff(
        EmployeeM $employee,
        string $days,
        Carbon $effectiveDate,
        string $reason,
        int $actorId,
        string $idempotencyKey
    ): CompOffM {
        $days = trim($days);
        $reason = trim($reason);
        $effectiveDate = $effectiveDate->copy()->timezone('Asia/Kolkata')->startOfDay();

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $days) || $reason === '' || mb_strlen($reason) > 255
            || ! Str::isUuid($idempotencyKey)) {
            throw new \DomainException('Enter a valid Comp-Off amount, reason, and grant reference.');
        }

        return DB::transaction(function () use ($employee, $days, $effectiveDate, $reason, $actorId, $idempotencyKey): CompOffM {
            $lockedEmployee = EmployeeM::query()->whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $policy = $this->policyService->forEmployee($lockedEmployee, $effectiveDate);
            if (! $policy) {
                throw new \DomainException('No active leave policy is available for the Comp-Off effective date.');
            }
            if (! $this->unitPolicy->isValidRequestAmount($days, $policy)) {
                throw new \DomainException('Comp-Off amount must follow the employee policy request unit.');
            }

            $today = Carbon::today('Asia/Kolkata');
            if ($effectiveDate->gt($today)) {
                throw new \DomainException('A manual Comp-Off credit cannot be effective in the future.');
            }

            $existing = CompOffM::query()->where('employee_id', $lockedEmployee->id)
                ->whereDate('worked_date', $effectiveDate->toDateString())
                ->where('earned_days', $days)
                ->where('remarks', $reason)
                ->where('created_at', '>=', Carbon::now('Asia/Kolkata')->subSeconds(30))
                ->first();
            if ($existing) {
                return $existing;
            }

            $allocation = $this->allocationService->getOrGenerate($lockedEmployee, (int) $effectiveDate->year, $actorId);
            if (! $allocation->exists) {
                throw new \DomainException('Generate the employee leave allocation for this year before granting Comp-Off.');
            }
            $allocation = LeaveAllocationM::query()->whereKey($allocation->id)->lockForUpdate()->firstOrFail();
            if ($allocation->is_locked) {
                throw new \DomainException('This leave allocation is locked.');
            }

            $monthLocked = (Schema::hasTable('leave_monthly_balances')
                    && DB::table('leave_monthly_balances')->where('employee_id', $lockedEmployee->id)
                        ->where('year', $effectiveDate->year)->where('month', $effectiveDate->month)->where('is_locked', true)->exists())
                || (Schema::hasTable('monthly_attendance_summaries')
                    && DB::table('monthly_attendance_summaries')->where('employee_id', $lockedEmployee->id)
                        ->where('year', $effectiveDate->year)->where('month', $effectiveDate->month)->where('is_locked', true)->exists());
            if ($monthLocked) {
                throw new \DomainException('Comp-Off cannot be granted into a payroll-locked month.');
            }

            $expiryDate = $effectiveDate->copy()->endOfMonth();
            $availableNow = $effectiveDate->lte($today) && $expiryDate->gte($today);
            $lot = CompOffM::query()->create([
                'employee_id' => $lockedEmployee->id,
                'worked_date' => $effectiveDate->toDateString(),
                'earned_days' => $days,
                'expiry_date' => $expiryDate->toDateString(),
                'status' => $expiryDate->lt($today) ? 'expired' : 'earned',
                'approved_by_user_id' => $actorId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
                'remarks' => $reason,
            ]);

            $before = (string) ($allocation->total_remaining ?? '0.00');
            $this->applyAllocationSnapshotDeltas(
                $allocation,
                $days,
                '0.000000',
                $availableNow ? $days : '0.000000'
            );
            $allocation->refresh();

            LeaveBalanceLogM::query()->create([
                'employee_id' => $lockedEmployee->id,
                'leave_allocation_id' => $allocation->id,
                'action' => 'comp_off_manual_grant',
                'credit' => $availableNow ? $days : '0.00',
                'debit' => '0.00',
                'balance_before' => $before,
                'balance_after' => (string) ($allocation->total_remaining ?? '0.00'),
                'remarks' => 'Manual Comp-Off grant (' . $days . ' days): ' . $reason,
                'created_by_user_id' => $actorId,
            ]);

            app(LeaveMonthlyBalanceService::class)->recordCompOffEarnedDelta(
                $lockedEmployee,
                $effectiveDate,
                bcadd($days, '0', 6)
            );

            return $lot;
        });
    }

    /** Reduce only unused manual lots by cancelling the original lot and preserving its remainder as a new lot. */
    public function reduceManualCompOff(
        EmployeeM $employee,
        string $days,
        Carbon $effectiveDate,
        string $reason,
        int $actorId,
        string $idempotencyKey
    ): void {
        $days = trim($days);
        $reason = trim($reason);
        $effectiveDate = $effectiveDate->copy()->timezone('Asia/Kolkata')->startOfDay();
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $days) || $reason === '' || mb_strlen($reason) > 255
            || ! Str::isUuid($idempotencyKey)) {
            throw new \DomainException('Enter a valid Comp-Off reduction amount, reason, and adjustment reference.');
        }

        DB::transaction(function () use ($employee, $days, $effectiveDate, $reason, $actorId, $idempotencyKey): void {
            $lockedEmployee = EmployeeM::query()->whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $policy = $this->policyService->forEmployee($lockedEmployee, $effectiveDate);
            if (! $policy || ! $this->unitPolicy->isValidRequestAmount($days, $policy)) {
                throw new \DomainException('Comp-Off reduction must follow the employee policy request unit.');
            }

            $today = Carbon::today('Asia/Kolkata');
            if (! $effectiveDate->equalTo($today)) {
                throw new \DomainException('Manual Comp-Off reductions can only adjust the current allocation period.');
            }

            $marker = '[manual-reduction:' . strtolower($idempotencyKey) . ']';
            if (CompOffM::query()->where('employee_id', $lockedEmployee->id)
                ->where('remarks', 'like', '%' . $marker . '%')->exists()) {
                return;
            }

            $allocation = LeaveAllocationM::query()->where('employee_id', $lockedEmployee->id)
                ->where('year', $effectiveDate->year)->lockForUpdate()->first();
            if (! $allocation) {
                throw new \DomainException('No leave allocation exists for this Comp-Off adjustment.');
            }
            if ($allocation->is_locked) {
                throw new \DomainException('This leave allocation is locked.');
            }
            if (bccomp((string) ($allocation->comp_off_remaining ?? '0'), $days, 6) < 0) {
                throw new \DomainException('The Comp-Off snapshot does not contain enough available balance to reduce.');
            }

            $monthLocked = (Schema::hasTable('leave_monthly_balances')
                    && DB::table('leave_monthly_balances')->where('employee_id', $lockedEmployee->id)
                        ->where('year', $effectiveDate->year)->where('month', $effectiveDate->month)->where('is_locked', true)->exists())
                || (Schema::hasTable('monthly_attendance_summaries')
                    && DB::table('monthly_attendance_summaries')->where('employee_id', $lockedEmployee->id)
                        ->where('year', $effectiveDate->year)->where('month', $effectiveDate->month)->where('is_locked', true)->exists());
            if ($monthLocked) {
                throw new \DomainException('Comp-Off cannot be reduced in a payroll-locked month.');
            }

            $availableToReduce = '0.000000';
            $lots = CompOffM::query()->where('employee_id', $lockedEmployee->id)
                ->where('status', 'earned')
                ->whereDate('worked_date', '<=', $today->toDateString())
                ->whereDate('expiry_date', '>=', $today->toDateString())
                ->where(function ($query) {
                    $query->where('remarks', 'not like', 'Generated from approved holiday%')
                        ->orWhereNull('remarks');
                })
                ->orderByDesc('expiry_date')->orderByDesc('id')->lockForUpdate()->get();
            $lotsWithMovements = LeaveCompOffTransactionM::query()->whereIn('comp_off_id', $lots->modelKeys())
                ->distinct()->pluck('comp_off_id')->flip();
            foreach ($lots as $lot) {
                if ($lotsWithMovements->has((int) $lot->id)) {
                    continue;
                }
                $availableToReduce = bcadd($availableToReduce, (string) $lot->earned_days, 6);
            }
            if (bccomp($availableToReduce, $days, 6) < 0) {
                throw new \DomainException('Only unused, unexpired manual Comp-Off credits can be reduced. Used or earned-work credits are unchanged.');
            }

            $remaining = bcadd($days, '0', 6);
            $reducedByMonth = [];
            foreach ($lots as $lot) {
                if (bccomp($remaining, '0.000000', 6) <= 0) {
                    break;
                }
                if ($lotsWithMovements->has((int) $lot->id)) {
                    continue;
                }

                $originalAmount = bcadd((string) $lot->earned_days, '0', 6);
                $reduction = bccomp($remaining, $originalAmount, 6) <= 0 ? $remaining : $originalAmount;
                $replacementAmount = bcsub($originalAmount, $reduction, 6);
                $monthKey = Carbon::parse($lot->worked_date, 'Asia/Kolkata')->format('Y-m');
                $reducedByMonth[$monthKey] ??= [
                    'date' => Carbon::parse($lot->worked_date, 'Asia/Kolkata'),
                    'amount' => '0.000000',
                ];
                $reducedByMonth[$monthKey]['amount'] = bcadd($reducedByMonth[$monthKey]['amount'], $reduction, 6);

                $lot->status = 'cancelled';
                $lot->remarks = trim((string) $lot->remarks . "\nCancelled by HR: " . $reason);
                $lot->save();

                if (bccomp($replacementAmount, '0.000000', 6) > 0) {
                    CompOffM::query()->create([
                        'employee_id' => $lockedEmployee->id,
                        'worked_date' => $lot->worked_date->toDateString(),
                        'earned_days' => bcadd($replacementAmount, '0', 2),
                        'expiry_date' => $lot->expiry_date?->toDateString(),
                        'status' => 'earned',
                        'approved_by_user_id' => $actorId,
                        'approved_at' => Carbon::now('Asia/Kolkata'),
                        'remarks' => $reason,
                    ]);
                }
                $remaining = bcsub($remaining, $reduction, 6);
            }

            $before = (string) ($allocation->total_remaining ?? '0.00');
            $negativeDays = bcsub('0.000000', $days, 6);
            $this->applyAllocationSnapshotDeltas($allocation, $negativeDays, '0.000000', $negativeDays);
            $allocation->refresh();
            LeaveBalanceLogM::query()->create([
                'employee_id' => $lockedEmployee->id,
                'leave_allocation_id' => $allocation->id,
                'action' => 'comp_off_manual_reduction',
                'credit' => '0.00',
                'debit' => bcadd($days, '0', 2),
                'balance_before' => $before,
                'balance_after' => (string) ($allocation->total_remaining ?? '0.00'),
                'remarks' => 'Manual Comp-Off reduction (' . $days . ' days): ' . $reason,
                'created_by_user_id' => $actorId,
            ]);

            foreach ($reducedByMonth as $entry) {
                app(LeaveMonthlyBalanceService::class)->recordCompOffEarnedDelta(
                    $lockedEmployee,
                    $entry['date'],
                    bcsub('0.000000', $entry['amount'], 6)
                );
            }
        });
    }

    public function generateFromHolidayWork(HolidayWorkRequestM $request, ?int $approvedByUserId = null): ?CompOffM
    {
        if ($request->status !== 'approved') {
            $request->update(['status' => 'approved', 'approved_by_user_id' => $approvedByUserId]);
        }
        return $this->reconcileRequest($request, $approvedByUserId);
    }

    public function validateAndProcessRequest(HolidayWorkRequestM $request): bool
    {
        if ($request->status !== 'approved') {
            return false;
        }

        $workedDate = Carbon::parse($request->worked_date, 'Asia/Kolkata');
        $today = Carbon::now('Asia/Kolkata')->startOfDay();
        if ($workedDate->greaterThan($today)) {
            return false;
        }

        $compOff = $this->reconcileRequest($request);
        return $compOff !== null && (float) $compOff->earned_days > 0;
    }

    public function reconcileRequest(HolidayWorkRequestM $request, ?int $approvedByUserId = null): ?CompOffM
    {
        return DB::transaction(function () use ($request, $approvedByUserId) {
            $request = HolidayWorkRequestM::where('id', $request->id)->lockForUpdate()->first();
            if (!$request) {
                return null;
            }

            if ($request->status !== 'approved') {
                $this->reverseRequest($request);
                return null;
            }

            $employee = $request->employee ?: EmployeeM::find($request->employee_id);
            if (!$employee) {
                return null;
            }

            $workedDate = Carbon::parse($request->worked_date, 'Asia/Kolkata');
            $dateStr = $workedDate->toDateString();

            $attendance = AttendanceM::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $dateStr)
                ->first();

            $targetEarnedDays = $this->calculateTargetEarnedDays($employee, $dateStr, $attendance);

            $existingCompOff = $this->holidayWorkCreditForRequest($request, $dateStr);

            if ($targetEarnedDays > 0) {
                $expiryDate = $workedDate->copy()->endOfMonth();

                if (!$existingCompOff) {
                    $compOff = CompOffM::create([
                        'employee_id' => $employee->id,
                        'worked_date' => $dateStr,
                        'earned_days' => $targetEarnedDays,
                        'expiry_date' => $expiryDate->toDateString(),
                        'status' => 'earned',
                        'approved_by_user_id' => $request->approved_by_user_id
                            ?: (($approvedByUserId && $approvedByUserId !== (int) $employee->user_id) ? $approvedByUserId : null),
                        'approved_at' => Carbon::now('Asia/Kolkata'),
                        'remarks' => "Generated from approved holiday/weekoff work request #{$request->id}.",
                    ]);

                    $request->update([
                        'comp_off_generated' => true,
                        'comp_off_id' => $compOff->id,
                        'attendance_id' => $attendance?->id ?: $request->attendance_id,
                    ]);

                    $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year, $approvedByUserId);
                    if (! $allocation->isLedgerBacked()) {
                        $this->allocationService->adjustLegacyAllocation(
                            $allocation,
                            ['comp_off_allocated' => number_format($targetEarnedDays, 6, '.', '')],
                            $approvedByUserId,
                            'comp_off_earned',
                            "Comp-off earned from holiday work request #{$request->id}"
                        );
                    } else {
                        $credit = number_format((float) $targetEarnedDays, 2, '.', '');
                        $this->applyAllocationSnapshotDeltas(
                            $allocation,
                            $credit,
                            '0.000000',
                            $expiryDate->gte(Carbon::today('Asia/Kolkata')) ? $credit : '0.000000'
                        );
                    }
                    app(LeaveMonthlyBalanceService::class)->recordCompOffEarnedDelta(
                        $employee,
                        $workedDate,
                        number_format((float) $targetEarnedDays, 6, '.', '')
                    );

                    return $compOff;
                } else {
                    $currentEarned = (float) $existingCompOff->earned_days;
                    $diff = round($targetEarnedDays - $currentEarned, 2);

                    if ($diff != 0 && $this->hasLedgerTransactions((int) $existingCompOff->id)) {
                        throw new \DomainException('A Comp-Off credit with leave usage cannot be changed; reverse its linked leave first.');
                    }

                    if ($diff != 0) {
                        $existingCompOff->update([
                            'earned_days' => $targetEarnedDays,
                            'status' => 'earned',
                            'approved_by_user_id' => $request->approved_by_user_id
                                ?: (($approvedByUserId && $approvedByUserId !== (int) $employee->user_id) ? $approvedByUserId : $existingCompOff->approved_by_user_id),
                            'remarks' => "Reconciled from approved holiday/weekoff work request #{$request->id}.",
                        ]);

                        $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year, $approvedByUserId);
                        if (! $allocation->isLedgerBacked()) {
                            $this->allocationService->adjustLegacyAllocation(
                                $allocation,
                                ['comp_off_allocated' => number_format($diff, 6, '.', '')],
                                $approvedByUserId,
                                'comp_off_reconciled',
                                "Comp-off reconciled from holiday work request #{$request->id}"
                            );
                        } else {
                            $diffAmount = number_format($diff, 2, '.', '');
                            $isAvailableNow = ! $existingCompOff->expiry_date
                                || $existingCompOff->expiry_date->toDateString() >= Carbon::today('Asia/Kolkata')->toDateString();
                            $this->applyAllocationSnapshotDeltas(
                                $allocation,
                                $diffAmount,
                                '0.000000',
                                $isAvailableNow ? $diffAmount : '0.000000'
                            );
                        }
                        app(LeaveMonthlyBalanceService::class)->recordCompOffEarnedDelta(
                            $employee,
                            $workedDate,
                            number_format($diff, 6, '.', '')
                        );
                    }

                    $request->update([
                        'comp_off_generated' => true,
                        'comp_off_id' => $existingCompOff->id,
                        'attendance_id' => $attendance?->id ?: $request->attendance_id,
                    ]);

                    if ($diff == 0.0) {
                        $this->refreshMonthlyPassbook($employee, $workedDate);
                    }

                    return $existingCompOff;
                }
            } else {
                if ($existingCompOff) {
                    if ($this->hasLedgerTransactions((int) $existingCompOff->id)) {
                        throw new \DomainException('A Comp-Off credit with leave usage cannot be removed; reverse its linked leave first.');
                    }

                    $currentEarned = (float) $existingCompOff->earned_days;
                    if ($currentEarned > 0) {
                        $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year, $approvedByUserId);
                        if (! $allocation->isLedgerBacked()) {
                            $this->allocationService->adjustLegacyAllocation(
                                $allocation,
                                ['comp_off_allocated' => number_format(-$currentEarned, 6, '.', '')],
                                $approvedByUserId,
                                'comp_off_reversed',
                                "Comp-off reversed from holiday work request #{$request->id}"
                            );
                        } else {
                            $isAvailableNow = ! $existingCompOff->expiry_date
                                || $existingCompOff->expiry_date->toDateString() >= Carbon::today('Asia/Kolkata')->toDateString();
                            $negativeCredit = bcsub('0.000000', (string) $existingCompOff->earned_days, 6);
                            $this->applyAllocationSnapshotDeltas(
                                $allocation,
                                $negativeCredit,
                                '0.000000',
                                $isAvailableNow ? $negativeCredit : '0.000000'
                            );
                        }
                    }

                    $existingCompOff->delete();
                }

                $request->update([
                    'comp_off_generated' => false,
                    'comp_off_id' => null,
                    'attendance_id' => $attendance?->id ?: $request->attendance_id,
                ]);

                $this->refreshMonthlyPassbook($employee, $workedDate);

                return null;
            }
        });
    }

    public function calculateTargetEarnedDays(EmployeeM $employee, string $dateStr, ?AttendanceM $attendance): float
    {
        if (!$attendance || !$attendance->punch_in_time || !$attendance->punch_out_time) {
            return 0.0;
        }

        if ($attendance->is_blocked || $attendance->is_punch_blocked) {
            return 0.0;
        }

        $status = strtolower((string) ($attendance->attendance_status ?? ''));
        if (in_array($status, ['absent', 'lwp', 'blocked', 'punch_blocked'], true) || (bool) $attendance->is_lwp) {
            return 0.0;
        }

        $resolver = app(AttendanceRuleResolverService::class);
        $policy = $resolver->getPolicyForEmployee($employee, $dateStr);
        $requiredMinutes = (int) ($policy->required_work_minutes ?? 480);
        $halfDayMinutes = (int) ($policy->half_day_min_minutes ?? ($requiredMinutes > 0 ? (int)($requiredMinutes / 2) : 240));

        $totalWorkMinutes = (int) ($attendance->total_work_minutes ?? 0);
        $isHalfDay = (bool) $attendance->is_half_day || in_array($status, ['half_day', 'half_leave'], true);

        if ($isHalfDay) {
            return $totalWorkMinutes >= $halfDayMinutes ? 0.5 : 0.0;
        }

        if ($totalWorkMinutes >= $requiredMinutes) {
            return 1.0;
        }

        if ($totalWorkMinutes >= $halfDayMinutes) {
            return 0.5;
        }

        return 0.0;
    }

    public function reverseRequest(HolidayWorkRequestM $request): void
    {
        DB::transaction(function () use ($request) {
            $employee = $request->employee ?: EmployeeM::find($request->employee_id);
            $workedDate = Carbon::parse($request->worked_date, 'Asia/Kolkata');
            $dateStr = $workedDate->toDateString();

            $existingCompOff = $this->holidayWorkCreditForRequest($request, $dateStr);

            if ($existingCompOff) {
                if ($this->hasLedgerTransactions((int) $existingCompOff->id)) {
                    throw new \DomainException('A Comp-Off credit with leave usage cannot be deleted; reverse its linked leave first.');
                }

                $earnedDays = (float) $existingCompOff->earned_days;
                if ($employee && $earnedDays > 0) {
                    $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year);
                    if (! $allocation->isLedgerBacked()) {
                        $this->allocationService->adjustLegacyAllocation(
                            $allocation,
                            ['comp_off_allocated' => number_format(-$earnedDays, 6, '.', '')],
                            $request->approved_by_user_id ? (int) $request->approved_by_user_id : null,
                            'comp_off_reversed',
                            "Comp-off reversed from holiday work request #{$request->id}"
                        );
                    } else {
                        $isAvailableNow = ! $existingCompOff->expiry_date
                            || $existingCompOff->expiry_date->toDateString() >= Carbon::today('Asia/Kolkata')->toDateString();
                        $negativeCredit = bcsub('0.000000', (string) $existingCompOff->earned_days, 6);
                        $this->applyAllocationSnapshotDeltas(
                            $allocation,
                            $negativeCredit,
                            '0.000000',
                            $isAvailableNow ? $negativeCredit : '0.000000'
                        );
                    }
                }
                $existingCompOff->delete();
            }

            $request->update([
                'comp_off_generated' => false,
                'comp_off_id' => null,
            ]);
            if ($employee) {
                $this->refreshMonthlyPassbook($employee, $workedDate);
            }
        });
    }

    public function reconcileForEmployeeAndDate(EmployeeM|int $employee, string|Carbon $date): ?CompOffM
    {
        $employeeObj = is_numeric($employee) ? EmployeeM::find($employee) : $employee;
        if (!$employeeObj) {
            return null;
        }

        $dateStr = Carbon::parse($date, 'Asia/Kolkata')->toDateString();

        $request = HolidayWorkRequestM::where('employee_id', $employeeObj->id)
            ->whereDate('worked_date', $dateStr)
            ->whereNull('deleted_at')
            ->first();

        if (!$request) {
            return null;
        }

        if ($request->status === 'approved') {
            return $this->reconcileRequest($request);
        } else {
            $this->reverseRequest($request);
            return null;
        }
    }

    public function expireDue(?Carbon $date = null): int
    {
        $date = $date ?: Carbon::now('Asia/Kolkata');
        $date = $date->copy()->timezone('Asia/Kolkata')->startOfDay();

        $due = CompOffM::query()->where('status', 'earned')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $date->toDateString())
            ->get(['id', 'employee_id', 'worked_date', 'earned_days']);
        if ($due->isEmpty()) {
            return 0;
        }

        $expiredByAllocation = [];
        foreach ($due as $lot) {
            $netMovements = Schema::hasTable('leave_comp_off_transactions')
                ? (string) LeaveCompOffTransactionM::query()->where('comp_off_id', $lot->id)
                    ->whereDate('effective_date', '<=', $date->toDateString())->sum('amount')
                : '0.000000';
            $remaining = bcadd((string) $lot->earned_days, $netMovements, 6);
            if (bccomp($remaining, '0.000000', 6) < 0) {
                $remaining = '0.000000';
            }
            $key = (int) $lot->employee_id . ':' . (int) Carbon::parse($lot->worked_date, 'Asia/Kolkata')->year;
            $expiredByAllocation[$key] = [
                'employee_id' => (int) $lot->employee_id,
                'year' => (int) Carbon::parse($lot->worked_date, 'Asia/Kolkata')->year,
                'amount' => bcadd($expiredByAllocation[$key]['amount'] ?? '0.000000', $remaining, 6),
            ];
        }

        $expired = CompOffM::query()->whereIn('id', $due->pluck('id'))
            ->update(['status' => 'expired', 'updated_at' => now()]);
        $employees = EmployeeM::query()->whereIn('id', $due->pluck('employee_id')->unique())->get()->keyBy('id');
        foreach ($expiredByAllocation as $item) {
            $this->applyAllocationSnapshotDeltas(
                LeaveAllocationM::query()->where('employee_id', $item['employee_id'])->where('year', $item['year'])->first(),
                '0.000000',
                '0.000000',
                bcsub('0.000000', $item['amount'], 6)
            );
        }
        foreach ($due->groupBy(fn (CompOffM $item): string => $item->employee_id . ':' . Carbon::parse($item->worked_date)->format('Y-m')) as $period) {
            $first = $period->first();
            $employee = $employees->get($first->employee_id);
            if ($employee) {
                $this->refreshMonthlyPassbook($employee, Carbon::parse($first->worked_date, 'Asia/Kolkata'));
            }
        }

        return $expired;
    }

    /**
     * Plan Comp-Off slices for each deductible leave date without reserving them.
     * In approval transactions, lock the credit lots and their movements before returning the plan.
     *
     * @param list<array<string, mixed>> $dateRows
     * @return array<string, array{amount: string, slices: list<array{comp_off_id: int, amount: string}>, available_after: string}>
     */
    public function planLedgerUsageForDates(
        EmployeeM $employee,
        array $dateRows,
        string|array $perDateAmount,
        bool $lockForUpdate = false
    ): array {
        $dates = collect($dateRows)
            ->filter(static fn (array $row): bool => (bool) ($row['deduct_as_leave'] ?? false))
            ->map(static fn (array $row): Carbon => Carbon::parse($row['leave_date'], 'Asia/Kolkata')->startOfDay())
            ->values();

        if ($dates->isEmpty()) {
            return [];
        }

        $firstDate = $dates->min()->toDateString();
        $lastDate = $dates->max()->toDateString();
        $lotsQuery = CompOffM::query()
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('worked_date', '<=', $lastDate)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $firstDate)
            ->orderByRaw('expiry_date IS NULL, expiry_date, id');

        if ($lockForUpdate) {
            $lotsQuery->lockForUpdate();
        }

        $lots = $lotsQuery->get();
        if ($lots->isEmpty()) {
            return $dates->mapWithKeys(static fn (Carbon $date): array => [
                $date->toDateString() => ['amount' => '0.000000', 'slices' => [], 'available_after' => '0.000000'],
            ])->all();
        }

        $movementsQuery = LeaveCompOffTransactionM::query()
            ->whereIn('comp_off_id', $lots->modelKeys())
            ->orderBy('id');
        if ($lockForUpdate) {
            $movementsQuery->lockForUpdate();
        }
        $movementsByLot = [];
        foreach ($movementsQuery->get(['comp_off_id', 'amount', 'effective_date']) as $movement) {
            $id = (int) $movement->comp_off_id;
            $movementsByLot[$id][] = [
                'effective_date' => Carbon::parse($movement->effective_date, 'Asia/Kolkata')->toDateString(),
                'amount' => (string) $movement->amount,
            ];
        }

        $earnedByLot = [];
        foreach ($lots as $lot) {
            $id = (int) $lot->id;
            $earnedByLot[$id] = (string) $lot->earned_days;
        }

        $plans = [];
        $deductibleRows = array_values(array_filter($dateRows, static fn (array $row): bool => (bool) ($row['deduct_as_leave'] ?? false)));
        usort($deductibleRows, static fn (array $a, array $b): int => strcmp(
            Carbon::parse($a['leave_date'], 'Asia/Kolkata')->toDateString(),
            Carbon::parse($b['leave_date'], 'Asia/Kolkata')->toDateString()
        ));
        $plannedByLot = [];
        foreach ($deductibleRows as $row) {
            $date = Carbon::parse($row['leave_date'], 'Asia/Kolkata')->startOfDay();
            $dateKey = $date->toDateString();
            $remainingByLot = [];
            foreach ($lots as $lot) {
                $lotId = (int) $lot->id;
                $balance = $earnedByLot[$lotId];
                foreach ($movementsByLot[$lotId] ?? [] as $movement) {
                    if ($movement['effective_date'] <= $dateKey) {
                        $balance = bcadd($balance, $movement['amount'], 6);
                    }
                }
                $remainingByLot[$lotId] = bcsub($balance, $plannedByLot[$lotId] ?? '0.000000', 6);
                if (bccomp($remainingByLot[$lotId], '0.000000', 6) < 0) {
                    throw new \DomainException("Comp-Off credit {$lotId} has a negative calculated balance.");
                }
            }
            $eligibleAvailable = '0.000000';
            foreach ($lots as $lot) {
                if (Carbon::parse($lot->worked_date, 'Asia/Kolkata')->startOfDay()->gt($date)
                    || ($lot->expiry_date && Carbon::parse($lot->expiry_date, 'Asia/Kolkata')->startOfDay()->lt($date))) {
                    continue;
                }
                $eligibleAvailable = bcadd($eligibleAvailable, $remainingByLot[(int) $lot->id] ?? '0.000000', 6);
            }

            $policy = $this->policyService->forEmployee($employee, $date);
            if (! $policy) {
                throw new \DomainException('No active leave policy is available for the Comp-Off leave date.');
            }
            $usableAvailable = $this->unitPolicy->usable($eligibleAvailable, $policy);
            $requestedForDate = is_array($perDateAmount)
                ? bcadd((string) ($perDateAmount[$dateKey] ?? '0'), '0', 6)
                : bcadd($perDateAmount, '0', 6);
            $requestedCompOff = bccomp($requestedForDate, $usableAvailable, 6) <= 0
                ? $requestedForDate
                : $usableAvailable;
            $remaining = bcadd($requestedCompOff, '0', 6);
            $slices = [];

            foreach ($lots as $lot) {
                if (bccomp($remaining, '0.000000', 6) <= 0) {
                    break;
                }

                if (Carbon::parse($lot->worked_date, 'Asia/Kolkata')->startOfDay()->gt($date)) {
                    continue;
                }
                if ($lot->expiry_date && Carbon::parse($lot->expiry_date, 'Asia/Kolkata')->startOfDay()->lt($date)) {
                    continue;
                }

                $lotId = (int) $lot->id;
                $available = $remainingByLot[$lotId] ?? '0.000000';
                if (bccomp($available, '0.000000', 6) <= 0) {
                    continue;
                }

                $take = bccomp($remaining, $available, 6) <= 0 ? $remaining : $available;
                $slices[] = ['comp_off_id' => $lotId, 'amount' => $take];
                $remaining = bcsub($remaining, $take, 6);
                $remainingByLot[$lotId] = bcsub($available, $take, 6);
                $plannedByLot[$lotId] = bcadd($plannedByLot[$lotId] ?? '0.000000', $take, 6);
            }

            $plans[$dateKey] = [
                'amount' => bcsub($requestedCompOff, $remaining, 6),
                'slices' => $slices,
                'available_after' => $this->availableFromLotsForDate($lots, $remainingByLot, $date),
            ];
        }

        return $plans;
    }

    public function availableLedgerBalanceForDate(EmployeeM $employee, Carbon $date): string
    {
        $date = $date->copy()->startOfDay();
        $lots = CompOffM::query()
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('worked_date', '<=', $date->toDateString())
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $date->toDateString())
            ->orderByRaw('expiry_date IS NULL, expiry_date, id')
            ->get();

        if ($lots->isEmpty()) {
            return '0.000000';
        }

        $movementTotals = [];
        foreach (LeaveCompOffTransactionM::query()->whereIn('comp_off_id', $lots->modelKeys())
            ->whereDate('effective_date', '<=', $date->toDateString())->get(['comp_off_id', 'amount']) as $movement) {
            $id = (int) $movement->comp_off_id;
            $movementTotals[$id] = bcadd($movementTotals[$id] ?? '0.000000', (string) $movement->amount, 6);
        }

        $balance = '0.000000';
        foreach ($lots as $lot) {
            $lotBalance = bcadd((string) $lot->earned_days, $movementTotals[(int) $lot->id] ?? '0.000000', 6);
            if (bccomp($lotBalance, '0.000000', 6) > 0) {
                $balance = bcadd($balance, $lotBalance, 6);
            }
        }

        return $balance;
    }

    /** Post the planned lot debits after the approved date rows have been persisted. */
    public function postLedgerUsage(
        EmployeeM $employee,
        int $leaveRequestId,
        array $slicesByDate,
        array $dateIdsByDate,
        ?int $actorId,
        bool $updateAllocationSnapshots = true
    ): void {
        $slices = [];
        foreach ($slicesByDate as $date => $dateSlices) {
            if (isset($dateSlices['slices']) && is_array($dateSlices['slices'])) {
                $dateSlices = $dateSlices['slices'];
            }
            foreach ($dateSlices as $slice) {
                if (bccomp((string) ($slice['amount'] ?? '0'), '0.000000', 6) > 0) {
                    $slices[] = [
                        'date' => Carbon::parse((string) $date, 'Asia/Kolkata')->toDateString(),
                        'comp_off_id' => (int) $slice['comp_off_id'],
                        'amount' => bcadd((string) $slice['amount'], '0', 6),
                    ];
                }
            }
        }

        if ($slices === []) {
            return;
        }

        usort($slices, static fn (array $a, array $b): int => [$a['comp_off_id'], $a['date']] <=> [$b['comp_off_id'], $b['date']]);
        $lotIds = array_values(array_unique(array_column($slices, 'comp_off_id')));
        $lots = CompOffM::query()->where('employee_id', $employee->id)->whereIn('id', $lotIds)
            ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        $movements = LeaveCompOffTransactionM::query()->whereIn('comp_off_id', $lotIds)
            ->orderBy('id')->lockForUpdate()->get();
        $remainingByLot = [];
        foreach ($lots as $lot) {
            $remainingByLot[(int) $lot->id] = (string) $lot->earned_days;
        }
        foreach ($movements as $movement) {
            $lotId = (int) $movement->comp_off_id;
            $remainingByLot[$lotId] = bcadd($remainingByLot[$lotId] ?? '0.000000', (string) $movement->amount, 6);
        }

        $keys = array_map(static fn (array $slice): string => sprintf(
            'comp-off-debit:%d:%s:%d', $leaveRequestId, $slice['date'], $slice['comp_off_id']
        ), $slices);
        $existingKeys = LeaveCompOffTransactionM::query()->whereIn('idempotency_key', $keys)->pluck('idempotency_key')->all();
        if (count($existingKeys) === count($keys)) {
            return;
        }
        if ($existingKeys !== []) {
            throw new \DomainException('Comp-Off usage is partially recorded for this request; refusing a duplicate approval write.');
        }

        foreach ($slices as $slice) {
            $lot = $lots->get($slice['comp_off_id']);
            if (! $lot || (int) $lot->employee_id !== (int) $employee->id) {
                throw new \DomainException('A planned Comp-Off credit is missing or belongs to another employee.');
            }
            $date = Carbon::parse($slice['date'], 'Asia/Kolkata')->startOfDay();
            if (Carbon::parse($lot->worked_date, 'Asia/Kolkata')->startOfDay()->gt($date)
                || ($lot->expiry_date && Carbon::parse($lot->expiry_date, 'Asia/Kolkata')->startOfDay()->lt($date))) {
                throw new \DomainException('A planned Comp-Off credit is not valid on the leave date.');
            }

            $lotId = (int) $lot->id;
            if (bccomp($remainingByLot[$lotId] ?? '0.000000', $slice['amount'], 6) < 0) {
                throw new \DomainException('Comp-Off availability changed while the request was being approved. Recalculate the split.');
            }

            LeaveCompOffTransactionM::query()->create([
                'comp_off_id' => $lotId,
                'employee_id' => $employee->id,
                'leave_request_id' => $leaveRequestId,
                'leave_request_date_id' => $dateIdsByDate[$slice['date']] ?? null,
                'transaction_type' => 'debit',
                'effective_date' => $slice['date'],
                'amount' => bcsub('0.000000', $slice['amount'], 6),
                'idempotency_key' => sprintf('comp-off-debit:%d:%s:%d', $leaveRequestId, $slice['date'], $lotId),
                'reason' => 'Approved Comp-Off leave usage',
                'created_by' => $actorId,
            ]);
            $remainingByLot[$lotId] = bcsub($remainingByLot[$lotId], $slice['amount'], 6);
        }

        $snapshotDeltas = [];
        foreach ($slices as $slice) {
            $lot = $lots->get($slice['comp_off_id']);
            $year = (int) Carbon::parse($lot->worked_date, 'Asia/Kolkata')->year;
            $key = (int) $employee->id . ':' . $year;
            $snapshotDeltas[$key] ??= [
                'employee_id' => (int) $employee->id,
                'year' => $year,
                'used' => '0.000000',
                'remaining' => '0.000000',
            ];
            $snapshotDeltas[$key]['used'] = bcadd($snapshotDeltas[$key]['used'], $slice['amount'], 6);
            if (! $lot->expiry_date || $lot->expiry_date->toDateString() >= Carbon::today('Asia/Kolkata')->toDateString()) {
                $snapshotDeltas[$key]['remaining'] = bcsub($snapshotDeltas[$key]['remaining'], $slice['amount'], 6);
            }
        }

        foreach ($lotIds as $lotId) {
            $this->syncLotStatus($lots->get($lotId));
        }
        if ($updateAllocationSnapshots) {
            foreach ($snapshotDeltas as $delta) {
                $this->applyAllocationSnapshotDeltas(
                    LeaveAllocationM::query()->where('employee_id', $delta['employee_id'])->where('year', $delta['year'])->first(),
                    '0.000000',
                    $delta['used'],
                    $delta['remaining']
                );
            }
        }
    }

    /** Reverse ledger-backed Comp-Off usage once; false means the request predates Comp-Off movements. */
    public function reverseLedgerUsageForRequest(
        EmployeeM $employee,
        int $leaveRequestId,
        ?int $actorId,
        string $reason,
        bool $updateAllocationSnapshots = true
    ): bool {
        $debits = LeaveCompOffTransactionM::query()
            ->where('employee_id', $employee->id)
            ->where('leave_request_id', $leaveRequestId)
            ->where('transaction_type', 'debit')
            ->orderBy('comp_off_id')->orderBy('id')->get();
        if ($debits->isEmpty()) {
            return false;
        }

        $lotIds = $debits->pluck('comp_off_id')->unique()->sort()->values()->all();
        $lots = CompOffM::query()->where('employee_id', $employee->id)->whereIn('id', $lotIds)
            ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        LeaveCompOffTransactionM::query()->whereIn('comp_off_id', $lotIds)
            ->orderBy('id')->lockForUpdate()->get();

        $snapshotDeltas = [];
        foreach ($debits as $debit) {
            $key = 'comp-off-reversal:' . $debit->id;
            if (LeaveCompOffTransactionM::query()->where('idempotency_key', $key)->exists()) {
                continue;
            }

            $lot = $lots->get((int) $debit->comp_off_id);
            if (! $lot) {
                throw new \DomainException('A Comp-Off credit linked to this leave request no longer exists.');
            }
            $year = (int) Carbon::parse($lot->worked_date, 'Asia/Kolkata')->year;
            $allocationKey = (int) $employee->id . ':' . $year;
            $snapshotDeltas[$allocationKey] ??= [
                'employee_id' => (int) $employee->id,
                'year' => $year,
                'used' => '0.000000',
                'remaining' => '0.000000',
            ];
            $reversedAmount = bcsub('0.000000', (string) $debit->amount, 6);
            $snapshotDeltas[$allocationKey]['used'] = bcsub($snapshotDeltas[$allocationKey]['used'], $reversedAmount, 6);
            if (! $lot->expiry_date || $lot->expiry_date->toDateString() >= Carbon::today('Asia/Kolkata')->toDateString()) {
                $snapshotDeltas[$allocationKey]['remaining'] = bcadd($snapshotDeltas[$allocationKey]['remaining'], $reversedAmount, 6);
            }

            LeaveCompOffTransactionM::query()->create([
                'comp_off_id' => $debit->comp_off_id,
                'employee_id' => $employee->id,
                'leave_request_id' => $leaveRequestId,
                'leave_request_date_id' => $debit->leave_request_date_id,
                'transaction_type' => 'reversal',
                'effective_date' => Carbon::now('Asia/Kolkata')->toDateString(),
                'amount' => bcsub('0.000000', (string) $debit->amount, 6),
                'reverses_transaction_id' => $debit->id,
                'idempotency_key' => $key,
                'reason' => $reason,
                'created_by' => $actorId,
            ]);
        }

        foreach ($lotIds as $lotId) {
            $this->syncLotStatus($lots->get($lotId));
        }
        if ($updateAllocationSnapshots) {
            foreach ($snapshotDeltas as $delta) {
                $this->applyAllocationSnapshotDeltas(
                    LeaveAllocationM::query()->where('employee_id', $delta['employee_id'])->where('year', $delta['year'])->first(),
                    '0.000000',
                    $delta['used'],
                    $delta['remaining']
                );
            }
        }

        return true;
    }

    private function availableFromLotsForDate($lots, array $remainingByLot, Carbon $date): string
    {
        $balance = '0.000000';
        foreach ($lots as $lot) {
            if (Carbon::parse($lot->worked_date, 'Asia/Kolkata')->startOfDay()->gt($date)
                || ($lot->expiry_date && Carbon::parse($lot->expiry_date, 'Asia/Kolkata')->startOfDay()->lt($date))) {
                continue;
            }
            $amount = $remainingByLot[(int) $lot->id] ?? '0.000000';
            if (bccomp($amount, '0.000000', 6) > 0) {
                $balance = bcadd($balance, $amount, 6);
            }
        }

        return $balance;
    }

    private function hasLedgerTransactions(int $compOffId): bool
    {
        return Schema::hasTable('leave_comp_off_transactions')
            && LeaveCompOffTransactionM::query()->where('comp_off_id', $compOffId)->exists();
    }

    private function holidayWorkCreditForRequest(HolidayWorkRequestM $request, string $workedDate): ?CompOffM
    {
        $query = CompOffM::query()->where('employee_id', $request->employee_id)
            ->whereDate('worked_date', $workedDate);

        if ($request->comp_off_id) {
            $query->whereKey($request->comp_off_id);
        } else {
            $requestMarker = 'holiday/weekoff work request #' . (int) $request->id . '.';
            $query->where(function ($query) use ($requestMarker): void {
                $query->where('remarks', 'like', '%' . $requestMarker . '%')
                    ->orWhere(function ($legacyQuery): void {
                        $legacyQuery->whereNull('used_against_leave_request_id')
                            ->where(function ($remarksQuery): void {
                                $remarksQuery->whereNull('remarks')
                                    ->orWhere('remarks', 'not like', 'Manual grant by HR:%');
                            });
                    });
            });
        }

        return $query->lockForUpdate()->first();
    }

    private function applyAllocationSnapshotDeltas(
        ?LeaveAllocationM $allocation,
        string $allocatedDelta,
        string $usedDelta,
        string $remainingDelta
    ): void {
        if (! $allocation) {
            return;
        }

        DB::transaction(function () use ($allocation, $allocatedDelta, $usedDelta, $remainingDelta): void {
            $locked = LeaveAllocationM::query()->whereKey($allocation->id)->lockForUpdate()->first();
            if (! $locked || $locked->is_locked) {
                return;
            }

            $oldAllocated = bcadd((string) ($locked->comp_off_allocated ?? '0'), '0', 6);
            $oldUsed = bcadd((string) ($locked->comp_off_used ?? '0'), '0', 6);
            $oldRemaining = bcadd((string) ($locked->comp_off_remaining ?? '0'), '0', 6);
            $newAllocated = bcadd($oldAllocated, $allocatedDelta, 6);
            $newUsed = bcadd($oldUsed, $usedDelta, 6);
            $newRemaining = bcadd($oldRemaining, $remainingDelta, 6);

            if (bccomp($newAllocated, '0.000000', 6) < 0 || bccomp($newUsed, '0.000000', 6) < 0) {
                throw new \DomainException('Comp-Off snapshot totals cannot become negative.');
            }
            if (bccomp($newRemaining, '0.000000', 6) < 0) {
                if (bccomp($remainingDelta, '0.000000', 6) < 0) {
                    $newRemaining = '0.000000';
                } else {
                    throw new \DomainException('Comp-Off available balance cannot become negative.');
                }
            }

            $locked->comp_off_allocated = $newAllocated;
            $locked->comp_off_used = $newUsed;
            $locked->comp_off_remaining = $newRemaining;
            $locked->total_used = bcadd(
                bcsub((string) ($locked->total_used ?? '0'), $oldUsed, 6),
                $newUsed,
                6
            );
            $locked->total_remaining = bcadd(
                bcadd(bcsub((string) ($locked->total_remaining ?? '0'), $oldRemaining, 6), $newRemaining, 6),
                '0',
                2
            );
            $locked->save();
        });
    }

    public function syncLotStatus(?CompOffM $lot): void
    {
        if (! $lot || $lot->status === 'cancelled') {
            return;
        }

        $net = '0.000000';
        foreach (LeaveCompOffTransactionM::query()->where('comp_off_id', $lot->id)->get(['amount']) as $movement) {
            $net = bcadd($net, (string) $movement->amount, 6);
        }
        $remaining = bcadd((string) $lot->earned_days, $net, 6);
        $today = Carbon::today('Asia/Kolkata')->toDateString();

        if (bccomp($remaining, '0.000000', 6) <= 0) {
            $lot->status = 'used';
        } elseif ($lot->expiry_date && $lot->expiry_date->toDateString() < $today) {
            $lot->status = 'expired';
        } else {
            $lot->status = 'earned';
        }

        if ($this->hasLedgerTransactions((int) $lot->id)) {
            $reversedDebitIds = LeaveCompOffTransactionM::query()
                ->where('comp_off_id', $lot->id)
                ->where('transaction_type', 'reversal')
                ->whereNotNull('reverses_transaction_id')
                ->pluck('reverses_transaction_id');

            $activeDebit = LeaveCompOffTransactionM::query()
                ->where('comp_off_id', $lot->id)
                ->where('transaction_type', 'debit')
                ->whereNotIn('id', $reversedDebitIds)
                ->whereNotNull('leave_request_id')
                ->latest('id')
                ->first();

            $lot->used_against_leave_request_id = $activeDebit?->leave_request_id;
        }

        $lot->save();
    }

    public function consume(EmployeeM $employee, string $days, int $leaveRequestId, ?int $actorId = null): string
    {
        $requested = bcadd($days, '0', 6);
        if (bccomp($requested, '0.000000', 6) <= 0) {
            return '0.000000';
        }

        return DB::transaction(function () use ($employee, $requested, $leaveRequestId, $actorId): string {
            $requestDates = LeaveRequestDateM::query()->where('leave_request_id', $leaveRequestId)
                ->where('employee_id', $employee->id)
                ->where('deduct_as_leave', true)
                ->where('comp_off_day', '>', 0)
                ->orderBy('leave_date')->orderBy('id')->get();
            if ($requestDates->isEmpty()) {
                throw new \DomainException('The approved leave has no dated Comp-Off split to record.');
            }

            $dateRows = [];
            $amountByDate = [];
            $dateIdsByDate = [];
            $datedTotal = '0.000000';
            foreach ($requestDates as $requestDate) {
                $date = $requestDate->leave_date->toDateString();
                $amount = bcadd((string) $requestDate->comp_off_day, '0', 6);
                $dateRows[] = ['leave_date' => $date, 'deduct_as_leave' => true];
                $amountByDate[$date] = bcadd($amountByDate[$date] ?? '0.000000', $amount, 6);
                $dateIdsByDate[$date] = (int) $requestDate->id;
                $datedTotal = bcadd($datedTotal, $amount, 6);
            }
            if (bccomp($datedTotal, $requested, 6) !== 0) {
                throw new \DomainException('The dated Comp-Off split does not match the approved request total.');
            }

            $plans = $this->planLedgerUsageForDates($employee, $dateRows, $amountByDate, true);
            foreach ($amountByDate as $date => $amount) {
                if (bccomp((string) ($plans[$date]['amount'] ?? '0'), $amount, 6) !== 0) {
                    throw new \DomainException('Comp-Off availability changed before approval. Recalculate the leave split.');
                }
            }

            $this->postLedgerUsage($employee, $leaveRequestId, $plans, $dateIdsByDate, $actorId, false);

            return $requested;
        });
    }

    public function refund(EmployeeM $employee, string $days, int $leaveRequestId, ?int $actorId = null): void
    {
        if ($this->reverseLedgerUsageForRequest(
            $employee,
            $leaveRequestId,
            $actorId,
            'Reversal for leave request #' . $leaveRequestId,
            false
        )) {
            return;
        }

        $usedCompOffs = CompOffM::where('employee_id', $employee->id)
            ->where('used_against_leave_request_id', $leaveRequestId)
            ->get();

        foreach ($usedCompOffs as $compOff) {
            $compOff->status = 'earned';
            $compOff->used_against_leave_request_id = null;
            $compOff->remarks = trim(($compOff->remarks ? $compOff->remarks . "\n" : '') . 'Restored from voided/cancelled leave request #' . $leaveRequestId);
            $compOff->save();
        }
    }

    private function refreshMonthlyPassbook(EmployeeM $employee, Carbon $date): void
    {
        app(LeaveMonthlyBalanceService::class)->rebuildEmployeeThrough($employee, (int) $date->year, (int) $date->month);
    }
}
