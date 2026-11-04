<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Enums\LeaveBucket;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeavePolicyCalculatorS
{
    public function __construct(
        private LeavePolicyService $policyService,
        private LeaveAllocationService $allocationService,
        private SandwichLeaveService $sandwichLeaveService,
        private LeaveBalanceService $balanceService,
        private PaidLeaveSplitter $paidLeaveSplitter,
        private LeaveUnitPolicy $unitPolicy,
        private ?CompOffService $compOffService = null
    ) {
    }

    public function calculate(
        EmployeeM $employee,
        LeaveTypeM $leaveType,
        array $payload,
        ?LeaveRequestM $existingRequest = null,
        bool $lockCompOffCredits = false
    ): array
    {
        $start = Carbon::parse($payload['start_date'], 'Asia/Kolkata')->startOfDay();
        $end = Carbon::parse($payload['end_date'], 'Asia/Kolkata')->startOfDay();

        if ($end->lt($start)) {
            throw ValidationException::withMessages(['end_date' => 'End date must be after or equal to start date.']);
        }

        $policy = $this->policyForEmployee($employee, $start);
        $allocation = $this->allocationService->getOrGenerate($employee, $start->year, Auth::id());
        $isHalfDay = (bool) ($payload['is_half_day'] ?? false);

        if ($isHalfDay && ! $leaveType->allow_half_day) {
            throw ValidationException::withMessages(['is_half_day' => 'Half-day leave is not allowed for this leave type.']);
        }

        if ($isHalfDay && ! $start->isSameDay($end)) {
            throw ValidationException::withMessages(['is_half_day' => 'Half-day leave can be applied for one date only.']);
        }

        // Build dates including advanced sandwich checks
        $dateRows = $this->sandwichLeaveService->buildDates($employee, $start, $end, $leaveType, $existingRequest?->id, $isHalfDay);
        $ledgerSplit = $this->usesLedgerForDates($employee, $dateRows);
        $stage = strtolower((string) $allocation->employment_stage);
        if ($stage === LeaveAllocationService::STAGE_PERMANENT && ! $ledgerSplit) {
            throw ValidationException::withMessages([
                'allocation' => 'This permanent employee is not enabled for the new leave model. Run the approved leave rollout first.',
            ]);
        }
        
        $requestedCalendarDays = $start->diffInDays($end) + 1;
        $workingDaysCount = 0;
        $sandwichDaysCount = 0;
        $workingDaysDeducted = '0.000000';
        $sandwichDaysDeducted = '0.000000';
        $weekendHolidayCounted = 0;
        $deductedDaysExact = '0.000000';

        foreach ($dateRows as $row) {
            $unit = $isHalfDay ? '0.500000' : '1.000000';
            if (! empty($row['is_working_day'])) {
                $workingDaysCount++;
                if (! empty($row['deduct_as_leave'])) {
                    $workingDaysDeducted = bcadd($workingDaysDeducted, $unit, 6);
                }
            }
            if (! empty($row['is_sandwich_day'])) {
                $sandwichDaysCount++;
                if (! empty($row['deduct_as_leave'])) {
                    $sandwichDaysDeducted = bcadd($sandwichDaysDeducted, '1.000000', 6);
                }
            }
            if ((! empty($row['is_weekoff']) || ! empty($row['is_holiday'])) && ! empty($row['deduct_as_leave'])) {
                $weekendHolidayCounted++;
            }
            if (! empty($row['deduct_as_leave'])) {
                $dayUnit = ! empty($row['is_sandwich_day']) ? '1.000000' : $unit;
                $deductedDaysExact = bcadd($deductedDaysExact, $dayUnit, 6);
            }
        }

        if (bccomp($deductedDaysExact, '0.000000', 6) <= 0) {
            throw ValidationException::withMessages(['start_date' => 'Selected range has no payable leave days.']);
        }

        $stageRequestAmount = $deductedDaysExact;
        if (! $this->unitPolicy->isValidRequestAmount($stageRequestAmount, $policy)) {
            throw ValidationException::withMessages([
                'requested_days' => 'Leave quantity must use the request unit configured in the effective leave policy.',
            ]);
        }
        $this->allocationService->assertStageRequestAllowed($employee, $allocation, $stageRequestAmount);



        if (empty($payload['bypass_duplicate_validation'])) {
            $this->ensureNoDuplicateDates($employee, $dateRows, $existingRequest);
        }
        
        // Leave Advance Notice Validation
        $isSick = (bool) $leaveType->is_sick;
        $isLwp = (bool) $leaveType->is_lwp;
        $isEmergency = (bool) ($payload['emergency_leave'] ?? false);

        if (! $isSick && ! $isLwp && ! $isEmergency && ! $isHalfDay && ! $existingRequest && ! ($payload['bypass_notice_period'] ?? false)) {
            $today = Carbon::now('Asia/Kolkata')->startOfDay();
            $startDate = Carbon::parse($payload['start_date'], 'Asia/Kolkata')->startOfDay();
            if ($startDate->gte($today) && $today->diffInDays($startDate, false) < 2) {
                throw ValidationException::withMessages([
                    'start_date' => 'Normal leaves must be applied at least 2 days in advance.'
                ]);
            }
        }

        // Sick leave certificate validation
        $requiresMedicalCertificate = false;
        if ($leaveType->is_sick) {
            $consecutiveCount = $this->maxConsecutiveSickDaysIncludingRequest($employee, $dateRows, $existingRequest?->id);
            if ($consecutiveCount > 2) {
                $requiresMedicalCertificate = true;
                if (empty($payload['bypass_attachment_validation']) && empty($payload['attachment_path']) && empty($payload['attachment'])) {
                    throw ValidationException::withMessages([
                        'attachment' => 'Medical certificate is required for more than 2 consecutive sick leave days.'
                    ]);
                }
            }
        }

        // Split days into categories
        $compOffUsageAllocations = [];
        $novDecMeta = [];
        if ($ledgerSplit) {
            [$paid, $sick, $compOff, $lwp, $dateRows, $compOffUsageAllocations] = $this->splitLedgerDates(
                $employee,
                $leaveType,
                $dateRows,
                $isHalfDay,
                $lockCompOffCredits,
                $existingRequest?->id,
                $novDecMeta
            );
        } else {
        [$paid, $sick, $compOff, $lwp] = $this->splitDays(
            $employee,
            $leaveType,
            $policy,
            $allocation,
            $start,
            $dateRows,
            $deductedDaysExact,
            $novDecMeta
        );

        $dateRows = $this->applySplitToDates(
            $dateRows,
            $isHalfDay ? '0.500000' : '1.000000',
            (string) $paid,
            (string) $sick,
            (string) $compOff,
            (string) $lwp
        );
        }

        // Calculate balance after split
        if ($ledgerSplit) {
            $snapshot = $this->balanceService->getLedgerSnapshot($allocation);
            $plAfter = bcsub($snapshot['PL']['available'], (string) $paid, 6);
            $slAfter = bcsub($snapshot['SL']['available'], (string) $sick, 6);
            $compOffAfter = $this->compOffService()->availableLedgerBalanceForDate($employee, $end);
            if ($compOffUsageAllocations !== []) {
                $deductibleDates = collect($dateRows)->where('deduct_as_leave', true);
                $lastPlan = null;
                foreach ($deductibleDates->reverse() as $dRow) {
                    $dKey = Carbon::parse($dRow['leave_date'], 'Asia/Kolkata')->toDateString();
                    if (isset($compOffUsageAllocations[$dKey]['available_after'])) {
                        $lastPlan = $compOffUsageAllocations[$dKey];
                        break;
                    }
                }
                if ($lastPlan && isset($lastPlan['available_after'])) {
                    $compOffAfter = (string) $lastPlan['available_after'];
                }
            }
            $balanceAfterSplit = [
                'paid_remaining' => bccomp($plAfter, '0', 6) < 0 ? '0.000000' : $plAfter,
                'sick_remaining' => bccomp($slAfter, '0', 6) < 0 ? '0.000000' : $slAfter,
                'comp_off_remaining' => $compOffAfter,
                'total_remaining' => bcadd(
                    bcadd(bccomp($plAfter, '0', 6) < 0 ? '0.000000' : $plAfter,
                        bccomp($slAfter, '0', 6) < 0 ? '0.000000' : $slAfter, 6),
                    $compOffAfter,
                    6
                ),
            ];
        } else {
            $paidAfter = bcsub((string) $allocation->paid_remaining, (string) $paid, 6);
            $sickAfter = bcsub((string) $allocation->sick_remaining, (string) $sick, 6);
            $compAfter = bcsub((string) $allocation->comp_off_remaining, (string) $compOff, 6);
            $totalAfter = bcsub((string) $allocation->total_remaining,
                bcadd(bcadd((string) $paid, (string) $sick, 6), (string) $compOff, 6), 6);
            $balanceAfterSplit = [
                'paid_remaining' => bccomp($paidAfter, '0', 6) < 0 ? '0.000000' : $paidAfter,
                'sick_remaining' => bccomp($sickAfter, '0', 6) < 0 ? '0.000000' : $sickAfter,
                'comp_off_remaining' => bccomp($compAfter, '0', 6) < 0 ? '0.000000' : $compAfter,
                'total_remaining' => bccomp($totalAfter, '0', 6) < 0 ? '0.000000' : $totalAfter,
            ];
        }

        $sandwichDetails = [];
        $sandwichMessage = null;
        if ($sandwichDaysCount > 0) {
            foreach ($dateRows as $r) {
                if (! empty($r['is_sandwich_day'])) {
                    $typeStr = ! empty($r['is_weekoff']) ? 'Week Off' : (! empty($r['is_holiday']) ? 'Holiday' : 'Non-Working Day');
                    $formattedDate = Carbon::parse($r['leave_date'])->format('d M Y');
                    $sandwichDetails[] = [
                        'date' => $r['leave_date'],
                        'formatted_date' => $formattedDate,
                        'day_name' => $r['day_name'] ?? Carbon::parse($r['leave_date'])->format('l'),
                        'type' => $typeStr,
                        'reason' => "{$formattedDate} ({$r['day_name']} - {$typeStr}) is enclosed by leave days and included as leave.",
                    ];
                }
            }
            $dateFormattedList = implode(', ', array_column($sandwichDetails, 'formatted_date'));
            $daysDisplay = $isHalfDay ? (float) $sandwichDaysDeducted : $sandwichDaysCount;
            $sandwichMessage = "Sandwich Policy Applied: {$daysDisplay} non-working day(s) ({$dateFormattedList}) falling between leave days are included as leave.";
        }

        if ($novDecMeta !== []) {
            $novDecPaidInReq = '0.000000';
            $otherPaidInReq = '0.000000';
            $novDecLwpInReq = '0.000000';
            foreach ($dateRows as $r) {
                if (! empty($r['deduct_as_leave'])) {
                    $m = (int) Carbon::parse($r['leave_date'], 'Asia/Kolkata')->month;
                    $p = bcadd((string)($r['paid_day'] ?? 0), (string)($r['sick_day'] ?? 0), 6);
                    $l = (string)($r['lwp_day'] ?? 0);
                    if (in_array($m, [11, 12], true)) {
                        $novDecPaidInReq = bcadd($novDecPaidInReq, $p, 6);
                        $novDecLwpInReq = bcadd($novDecLwpInReq, $l, 6);
                    } else {
                        $otherPaidInReq = bcadd($otherPaidInReq, $p, 6);
                    }
                }
            }
            $novDecMeta['request_nov_dec_paid'] = (float) $novDecPaidInReq;
            $novDecMeta['request_other_months_paid'] = (float) $otherPaidInReq;
            $novDecMeta['request_nov_dec_lwp'] = (float) $novDecLwpInReq;
            $novDecMeta['cap_exceeded'] = ! empty($novDecMeta['cap_exceeded']);
        }

        return [
            'policy' => $policy,
            'allocation' => $allocation,
            'dates' => $dateRows,
            'requested_calendar_days' => $requestedCalendarDays,
            'working_days' => $isHalfDay ? (float) $workingDaysDeducted : $workingDaysCount,
            'sandwich_days' => $isHalfDay ? (float) $sandwichDaysDeducted : $sandwichDaysCount,
            'working_days_count' => $workingDaysCount,
            'sandwich_days_count' => $sandwichDaysCount,
            'working_days_deducted' => (float) $workingDaysDeducted,
            'sandwich_days_deducted' => (float) $sandwichDaysDeducted,
            'weekend_holiday_counted' => $weekendHolidayCounted,
            'requested_days' => $ledgerSplit ? $deductedDaysExact : (float) $deductedDaysExact,
            'deducted_days' => $ledgerSplit ? $deductedDaysExact : (float) $deductedDaysExact,
            'sandwich_applied' => $sandwichDaysCount > 0,
            'sandwich_details' => $sandwichDetails,
            'sandwich_message' => $sandwichMessage,
            'paid_days' => $ledgerSplit ? $paid : (float) $paid,
            'sick_days' => $ledgerSplit ? $sick : (float) $sick,
            'comp_off_days' => $ledgerSplit ? $compOff : (float) $compOff,
            'lwp_days' => $ledgerSplit ? $lwp : (float) $lwp,
            'requires_medical_certificate' => $requiresMedicalCertificate,
            'rejection_reason' => null,
            'balance_after_split' => $balanceAfterSplit,
            '_comp_off_usage_allocations' => $compOffUsageAllocations,
            'nov_dec_rule' => $novDecMeta !== [] ? $novDecMeta : null,
        ];
    }

    private function usesLedgerForDates(EmployeeM $employee, array $dateRows): bool
    {
        $years = collect($dateRows)->where('deduct_as_leave', true)
            ->map(fn (array $row) => Carbon::parse($row['leave_date'], 'Asia/Kolkata')->year)->unique();
        foreach ($years as $year) {
            $allocation = LeaveAllocationM::query()->where('employee_id', $employee->id)->where('year', $year)->first();
            if ($allocation?->isLedgerBacked()) return true;
        }
        return false;
    }

    private function compOffService(): CompOffService
    {
        return $this->compOffService ?? app(CompOffService::class);
    }

    private function loadYearBalances(EmployeeM $employee, int $year, Carbon $date): array
    {
        $allocation = LeaveAllocationM::query()->where('employee_id', $employee->id)->where('year', $year)->first();
        $snapshot = $allocation?->isLedgerBacked() ? $this->balanceService->getLedgerSnapshot($allocation) : null;
        $balances = [
            'allocation' => $allocation,
            'PL' => $snapshot['PL']['available'] ?? '0.000000',
            'SL' => $snapshot['SL']['available'] ?? '0.000000',
        ];
        $lapsePolicy = $this->policyForEmployee($employee, Carbon::create($year, 12, 31, 0, 0, 0, 'Asia/Kolkata'));
        $lapseAt = Carbon::create($year, (int) $lapsePolicy->leave_lapse_month, (int) $lapsePolicy->leave_lapse_day, 0, 0, 0, 'Asia/Kolkata');
        $cutoff = $lapseAt->copy()->addDays((int) $lapsePolicy->lapse_grace_days);
        if ($date->year < Carbon::now('Asia/Kolkata')->year && Carbon::now('Asia/Kolkata')->startOfDay()->gt($cutoff)) {
            $balances['PL'] = $balances['SL'] = '0.000000';
        }

        return $balances;
    }

    /** Ledger-only per-date split: each date consumes its own calendar-year buckets. */
    private function splitLedgerDates(
        EmployeeM $employee,
        LeaveTypeM $leaveType,
        array $dateRows,
        bool $isHalfDay,
        bool $lockCompOffCredits = false,
        ?int $existingRequestId = null,
        array &$novDecMeta = []
    ): array
    {
        $selected = $leaveType->is_lwp || $leaveType->is_comp_off ? null : LeaveBucket::fromLeaveType($leaveType);
        $totals = ['PL' => '0.000000', 'SL' => '0.000000', 'COMP' => '0.000000', 'LOP' => '0.000000'];
        $balances = [];
        $compOffUsageAllocations = [];
        $novDecCapacities = [];
        $novDecExceededDates = [];

        if ($leaveType->is_lwp) {
            foreach ($dateRows as &$row) {
                $row['paid_day'] = $row['sick_day'] = $row['comp_off_day'] = $row['lwp_day'] = 0;
                if (! $row['deduct_as_leave']) continue;
                $unit = $isHalfDay ? '0.500000' : '1.000000';
                $row['lwp_day'] = $unit;
                $row['remarks'] = 'Leave Without Pay (LWP)';
                $totals['LOP'] = bcadd($totals['LOP'], $unit, 6);
            }
            unset($row);
        } elseif ($leaveType->is_comp_off) {
            $compOffUsageAllocations = $this->compOffService()->planLedgerUsageForDates(
                $employee,
                $dateRows,
                $isHalfDay ? '0.500000' : '1.000000',
                $lockCompOffCredits
            );

            foreach ($dateRows as &$row) {
                $row['paid_day'] = $row['sick_day'] = $row['comp_off_day'] = $row['lwp_day'] = 0;
                if (! $row['deduct_as_leave']) continue;
                $date = Carbon::parse($row['leave_date'], 'Asia/Kolkata');
                $year = (int) $date->year;
                if (! array_key_exists($year, $balances)) {
                    $balances[$year] = $this->loadYearBalances($employee, $year, $date);
                }
                $isNovDecDate = in_array((int) $date->month, [11, 12], true);
                if ($isNovDecDate && ! array_key_exists($year, $novDecCapacities)) {
                    $novDecCapacities[$year] = $this->getNovDecQuotaForYear($employee, $year, $existingRequestId, $preNovDeductedInCurrentRequest ?? '0.000000');
                }
                $novDecRule = $novDecCapacities[$year] ?? ['active' => false];
                $unit = $isHalfDay ? '0.500000' : '1.000000';
                $compOffPlan = $compOffUsageAllocations[$date->toDateString()] ?? ['amount' => '0.000000', 'slices' => [], 'available_after' => '0.000000'];
                $takeComp = (string) $compOffPlan['amount'];
                $shortfall = bcsub($unit, $takeComp, 6);

                $effPL = $balances[$year]['PL'];
                $effSL = $balances[$year]['SL'];
                if ($isNovDecDate && ! empty($novDecRule['active'])) {
                    if (empty($novDecMeta)) {
                        $novDecMeta = $novDecRule;
                    }
                    $cap = $novDecCapacities[$year]['allowed_remaining'];
                    $usableCap = $this->unitPolicy->usable($cap, $this->policyForEmployee($employee, $date));
                    $effPL = $this->minDecimal($effPL, $usableCap);
                    $remCapForSL = bcsub($usableCap, $effPL, 6);
                    if (bccomp($remCapForSL, '0', 6) < 0) {
                        $remCapForSL = '0.000000';
                    }
                    $effSL = $this->minDecimal($effSL, $remCapForSL);
                }

                $paidSplit = $this->paidLeaveSplitter->split(
                    LeaveBucket::PL,
                    $shortfall,
                    $effPL,
                    $effSL,
                    $this->policyForEmployee($employee, $date),
                    false,
                    true
                );
                $split = ['pl' => $paidSplit['pl'], 'sl' => $paidSplit['sl'], 'lop' => $paidSplit['lop']];
                $balances[$year]['PL'] = bcsub($balances[$year]['PL'], $split['pl'], 6);
                $balances[$year]['SL'] = bcsub($balances[$year]['SL'], $split['sl'], 6);

                if ($isNovDecDate && ! empty($novDecRule['active'])) {
                    $usedInNovDec = bcadd($split['pl'], $split['sl'], 6);
                    $novDecCapacities[$year]['allowed_remaining'] = bcsub($novDecCapacities[$year]['allowed_remaining'], $usedInNovDec, 6);
                    if (bccomp($novDecCapacities[$year]['allowed_remaining'], '0', 6) < 0) {
                        $novDecCapacities[$year]['allowed_remaining'] = '0.000000';
                    }
                } else {
                    $preNovDeductedInCurrentRequest = bcadd($preNovDeductedInCurrentRequest, bcadd($split['pl'], $split['sl'], 6), 6);
                }

                $row['paid_day'] = $split['pl'];
                $row['sick_day'] = $split['sl'];
                $row['comp_off_day'] = $takeComp;
                $row['lwp_day'] = $split['lop'];

                $totals['COMP'] = bcadd($totals['COMP'], $takeComp, 6);
                $totals['PL'] = bcadd($totals['PL'], $split['pl'], 6);
                $totals['SL'] = bcadd($totals['SL'], $split['sl'], 6);
                $totals['LOP'] = bcadd($totals['LOP'], $split['lop'], 6);

                if (bccomp($split['pl'], '0', 6) > 0) {
                    $row['remarks'] = 'Adjusted to Paid Leave (PL) due to insufficient Comp-Off balance';
                }
                if (bccomp($split['sl'], '0', 6) > 0) {
                    $row['remarks'] = ($row['remarks'] ?? '') !== ''
                        ? $row['remarks'] . '; adjusted to Sick Leave (SL) due to insufficient Comp-Off and PL balance'
                        : 'Adjusted to Sick Leave (SL) due to insufficient Comp-Off balance';
                }
                if (bccomp($split['lop'], '0', 6) > 0) {
                    $row['remarks'] = ($row['remarks'] ?? '') !== ''
                        ? $row['remarks'] . '; Excess converted to LWP due to insufficient balance'
                        : 'Converted to LWP due to insufficient balance';
                }
                $compOffUsageAllocations[$date->toDateString()]['slices'] = $compOffPlan['slices'];
            }
            unset($row);
        } else {
            // Selected is PL or SL.
            // Step 1: Compute initial PL/SL split for each deductible date row.
            $initialSplits = [];
            $shortfalls = [];
            $unit = $isHalfDay ? '0.500000' : '1.000000';
            $preNovDeductedInCurrentRequest = '0.000000';

            foreach ($dateRows as $idx => $row) {
                if (! $row['deduct_as_leave']) continue;
                $date = Carbon::parse($row['leave_date'], 'Asia/Kolkata');
                $year = (int) $date->year;
                if (! array_key_exists($year, $balances)) {
                    $balances[$year] = $this->loadYearBalances($employee, $year, $date);
                }
                $isNovDecDate = in_array((int) $date->month, [11, 12], true);
                if ($isNovDecDate && ! array_key_exists($year, $novDecCapacities)) {
                    $novDecCapacities[$year] = $this->getNovDecQuotaForYear($employee, $year, $existingRequestId, $preNovDeductedInCurrentRequest);
                }
                $novDecRule = $novDecCapacities[$year] ?? ['active' => false];
                $datePolicy = $this->policyForEmployee($employee, $date);

                $effPL = $balances[$year]['PL'];
                $effSL = $balances[$year]['SL'];
                if ($isNovDecDate && ! empty($novDecRule['active'])) {
                    if (empty($novDecMeta)) {
                        $novDecMeta = $novDecRule;
                    }
                    $cap = $novDecCapacities[$year]['allowed_remaining'];
                    $usableCap = $this->unitPolicy->usable($cap, $datePolicy);
                    if ($selected === LeaveBucket::PL) {
                        $effPL = $this->minDecimal($effPL, $usableCap);
                        $remCapForSL = bcsub($usableCap, $effPL, 6);
                        if (bccomp($remCapForSL, '0', 6) < 0) {
                            $remCapForSL = '0.000000';
                        }
                        $effSL = $this->minDecimal($effSL, $remCapForSL);
                    } else {
                        $effSL = $this->minDecimal($effSL, $usableCap);
                        $remCapForPL = bcsub($usableCap, $effSL, 6);
                        if (bccomp($remCapForPL, '0', 6) < 0) {
                            $remCapForPL = '0.000000';
                        }
                        $effPL = $this->minDecimal($effPL, $remCapForPL);
                    }
                }

                $split = $this->paidLeaveSplitter->split($selected, $unit, $effPL, $effSL, $datePolicy);
                $balances[$year]['PL'] = bcsub($balances[$year]['PL'], $split['pl'], 6);
                $balances[$year]['SL'] = bcsub($balances[$year]['SL'], $split['sl'], 6);

                if ($isNovDecDate && ! empty($novDecRule['active'])) {
                    $usedInNovDec = bcadd($split['pl'], $split['sl'], 6);
                    $novDecCapacities[$year]['allowed_remaining'] = bcsub($novDecCapacities[$year]['allowed_remaining'], $usedInNovDec, 6);
                    if (bccomp($novDecCapacities[$year]['allowed_remaining'], '0', 6) < 0) {
                        $novDecCapacities[$year]['allowed_remaining'] = '0.000000';
                    }
                    if (bccomp($split['lop'], '0', 6) > 0 && bccomp($cap, $unit, 6) < 0) {
                        $novDecExceededDates[$date->toDateString()] = true;
                    }
                } else {
                    $preNovDeductedInCurrentRequest = bcadd($preNovDeductedInCurrentRequest, bcadd($split['pl'], $split['sl'], 6), 6);
                }

                $initialSplits[$idx] = $split;

                $dateKey = $date->toDateString();
                if (bccomp($split['lop'], '0', 6) > 0) {
                    $shortfalls[$dateKey] = $split['lop'];
                }
            }

            // Step 2: If any shortfall exists, check and plan valid Comp-Off before falling back to LWP.
            if (count($shortfalls) > 0) {
                $compOffUsageAllocations = $this->compOffService()->planLedgerUsageForDates(
                    $employee,
                    $dateRows,
                    $shortfalls,
                    $lockCompOffCredits
                );
            }

            // Step 3: Populate row values, using Comp-Off for shortfall where available.
            foreach ($dateRows as $idx => &$row) {
                $row['paid_day'] = $row['sick_day'] = $row['comp_off_day'] = $row['lwp_day'] = 0;
                if (! $row['deduct_as_leave']) continue;
                $date = Carbon::parse($row['leave_date'], 'Asia/Kolkata');
                $dateKey = $date->toDateString();
                $split = $initialSplits[$idx];

                $compPlan = $compOffUsageAllocations[$dateKey] ?? null;
                $takeComp = (string) ($compPlan['amount'] ?? '0.000000');
                $finalLop = bcsub($split['lop'], $takeComp, 6);
                if (bccomp($finalLop, '0', 6) < 0) {
                    $finalLop = '0.000000';
                }

                $row['paid_day'] = $split['pl'];
                $row['sick_day'] = $split['sl'];
                $row['comp_off_day'] = $takeComp;
                $row['lwp_day'] = $finalLop;

                $totals['PL'] = bcadd($totals['PL'], $split['pl'], 6);
                $totals['SL'] = bcadd($totals['SL'], $split['sl'], 6);
                $totals['COMP'] = bcadd($totals['COMP'], $takeComp, 6);
                $totals['LOP'] = bcadd($totals['LOP'], $finalLop, 6);

                if ($selected === LeaveBucket::PL) {
                    if (bccomp($split['sl'], '0', 6) > 0) {
                        $row['remarks'] = 'Adjusted to Sick Leave (SL) due to insufficient Paid Leave (PL) balance';
                    }
                    if (bccomp($takeComp, '0', 6) > 0) {
                        $compMsg = bccomp($split['sl'], '0', 6) > 0
                            ? '; adjusted to Comp-Off due to insufficient Paid Leave (PL) and Sick Leave (SL) balance'
                            : 'Adjusted to Comp-Off due to insufficient Paid Leave (PL) balance';
                        $row['remarks'] = ($row['remarks'] ?? '') !== '' ? $row['remarks'] . $compMsg : $compMsg;
                    }
                } elseif ($selected === LeaveBucket::SL) {
                    if (bccomp($split['pl'], '0', 6) > 0) {
                        $row['remarks'] = 'Adjusted to Paid Leave (PL) due to insufficient Sick Leave (SL) balance';
                    }
                    if (bccomp($takeComp, '0', 6) > 0) {
                        $compMsg = bccomp($split['pl'], '0', 6) > 0
                            ? '; adjusted to Comp-Off due to insufficient Sick Leave (SL) and Paid Leave (PL) balance'
                            : 'Adjusted to Comp-Off due to insufficient Sick Leave (SL) balance';
                        $row['remarks'] = ($row['remarks'] ?? '') !== '' ? $row['remarks'] . $compMsg : $compMsg;
                    }
                }

                if (! empty($novDecExceededDates[$dateKey])) {
                    $novDecRemark = 'Converted to LWP: Exceeded 50% November & December leave utilization cap';
                    $row['remarks'] = ($row['remarks'] ?? '') !== '' ? $row['remarks'] . '; ' . $novDecRemark : $novDecRemark;
                } elseif (bccomp($finalLop, '0', 6) > 0) {
                    $row['remarks'] = ($row['remarks'] ?? '') !== ''
                        ? $row['remarks'] . '; Excess converted to LWP due to insufficient balance'
                        : 'Converted to LWP due to insufficient balance';
                }

                if ($compPlan && isset($compPlan['slices'])) {
                    $compOffUsageAllocations[$dateKey]['slices'] = $compPlan['slices'];
                }
            }
            unset($row);
        }

        $compOffUsageAllocations = array_filter(
            $compOffUsageAllocations,
            static fn (array $plan): bool => bccomp((string) ($plan['amount'] ?? '0'), '0', 6) > 0
        );

        if ($novDecMeta !== []) {
            $novDecMeta['cap_exceeded'] = count($novDecExceededDates) > 0;
            $novDecMeta['exceeded_dates_count'] = count($novDecExceededDates);
        }

        return [$totals['PL'], $totals['SL'], $totals['COMP'], $totals['LOP'], $dateRows, $compOffUsageAllocations];
    }

    private function getNovDecQuotaForYear(EmployeeM $employee, int $year, ?int $existingRequestId, string $preNovDeductedInCurrentReq = '0.000000'): array
    {
        try {
            $policy = $this->policyForEmployee($employee, Carbon::create($year, 11, 1, 0, 0, 0, 'Asia/Kolkata'));
        } catch (\Throwable $e) {
            return ['active' => false];
        }

        if (! ($policy->nov_dec_half_usage_enabled ?? true)) {
            return ['active' => false];
        }

        $allocation = null;
        try {
            $allocation = $this->allocationService->getOrGenerate($employee, $year, Auth::id() ?: 1);
        } catch (\Throwable $e) {
            $allocation = LeaveAllocationM::query()->where('employee_id', $employee->id)->where('year', $year)->first();
        }

        if (! $allocation) {
            return ['active' => false];
        }

        // Case 2: Annual Balance Based (Nov + Dec Combined Pool)
        $annualAllocated = null;
        try {
            $fromDate = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata');
            $toDate = Carbon::create($year, 12, 31, 23, 59, 59, 'Asia/Kolkata');
            $stage = $employee->employee_stage ?: $employee->employment_type ?: 'permanent';
            $entitlement = $this->allocationService->calculateEntitlement($policy, (string) $stage, $fromDate, $toDate);
            if ($entitlement && (float) $entitlement->totalAllocated > 0) {
                $annualAllocated = (string) $entitlement->totalAllocated;
            }
        } catch (\Throwable $e) {
            $annualAllocated = null;
        }

        if ($annualAllocated === null) {
            $snapshot = $allocation->isLedgerBacked() ? $this->balanceService->getLedgerSnapshot($allocation) : null;
            $plAvail = $snapshot['PL']['available'] ?? (string)($allocation->paid_remaining ?? '0');
            $slAvail = $snapshot['SL']['available'] ?? (string)($allocation->sick_remaining ?? '0');
            $annualAllocated = bcadd((string) $plAvail, (string) $slAvail, 6);
        }

        $preNovApprovedUsed = '0.000000';
        $alreadyUsedInNovDec = '0.000000';
        try {
            $ledgerStats = null;
            if (! empty($allocation->id)) {
                $ledgerStats = DB::table('leave_balance_transactions')
                    ->where('leave_allocation_id', $allocation->id)
                    ->whereIn('transaction_type', ['leave_debit', 'reversal'])
                    ->when($existingRequestId, fn($q) => $q->where(fn($sub) => $sub->whereNull('leave_request_id')->orWhere('leave_request_id', '!=', $existingRequestId)))
                    ->selectRaw("
                        SUM(CASE WHEN MONTH(effective_date) NOT IN (11, 12) THEN amount ELSE 0 END) as pre_nov_sum,
                        SUM(CASE WHEN MONTH(effective_date) IN (11, 12) THEN amount ELSE 0 END) as nov_dec_sum,
                        COUNT(*) as count_tx
                    ")
                    ->first();
            }

            if ($ledgerStats && (int) $ledgerStats->count_tx > 0) {
                $preNovApprovedUsed = (string) abs((float) ($ledgerStats->pre_nov_sum ?? 0));
                $alreadyUsedInNovDec = (string) abs((float) ($ledgerStats->nov_dec_sum ?? 0));
            } elseif (! empty($employee->id)) {
                $requestStats = DB::table('leave_request_dates')
                    ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
                    ->where('leave_request_dates.employee_id', $employee->id)
                    ->whereYear('leave_request_dates.leave_date', $year)
                    ->where('leave_request_dates.deduct_as_leave', true)
                    ->whereIn('leave_requests.status', ['approved'])
                    ->when($existingRequestId, fn($q) => $q->where('leave_requests.id', '!=', $existingRequestId))
                    ->selectRaw("
                        SUM(CASE WHEN MONTH(leave_request_dates.leave_date) NOT IN (11, 12) THEN leave_request_dates.paid_day + leave_request_dates.sick_day ELSE 0 END) as pre_nov_req,
                        SUM(CASE WHEN MONTH(leave_request_dates.leave_date) IN (11, 12) THEN leave_request_dates.paid_day + leave_request_dates.sick_day ELSE 0 END) as nov_dec_req
                    ")
                    ->first();

                if ($requestStats) {
                    $preNovApprovedUsed = (string) ((float) ($requestStats->pre_nov_req ?? 0));
                    $alreadyUsedInNovDec = (string) ((float) ($requestStats->nov_dec_req ?? 0));
                }
            }
        } catch (\Throwable $e) {
            $preNovApprovedUsed = '0.000000';
            $alreadyUsedInNovDec = '0.000000';
        }

        $totalAvail = bcsub(bcsub($annualAllocated, $preNovApprovedUsed, 6), $preNovDeductedInCurrentReq, 6);
        if (bccomp($totalAvail, '0', 6) < 0) {
            $totalAvail = '0.000000';
        }

        $threshold = (string) ($policy->nov_dec_half_usage_threshold ?? $policy->nov_dec_full_balance_threshold ?? $policy->nov_dec_threshold_balance ?? '10.000000');
        $usagePct = (string) ($policy->nov_dec_half_usage_percentage ?? $policy->nov_dec_usage_percentage ?? '50.000000');

        if (bccomp($totalAvail, $threshold, 6) <= 0) {
            return [
                'active' => false,
                'total_remaining' => (float) $totalAvail,
                'threshold' => (float) $threshold,
            ];
        }

        $allowedTotal = bcdiv(bcmul($totalAvail, $usagePct, 6), '100', 6);

        $remainingNovDecCap = bcsub($allowedTotal, $alreadyUsedInNovDec, 6);
        if (bccomp($remainingNovDecCap, '0', 6) < 0) {
            $remainingNovDecCap = '0.000000';
        }

        $lapsedLeaves = bcsub($totalAvail, $allowedTotal, 6);

        return [
            'active' => true,
            'threshold' => (float) $threshold,
            'usage_percentage' => (float) $usagePct,
            'total_remaining' => (float) $totalAvail,
            'allowed_limit' => (float) $allowedTotal,
            'already_used' => (float) $alreadyUsedInNovDec,
            'allowed_remaining' => $remainingNovDecCap,
            'initial_allowed_remaining' => (float) $remainingNovDecCap,
            'lapsed_leaves' => (float) $lapsedLeaves,
            'annual_allocated' => (float) $annualAllocated,
            'message' => "November & December Rule Active: Your annual balance entering November is " . number_format((float)$totalAvail, 2) . " days (> " . (int)$threshold . " days). Only 50% (" . number_format((float)$allowedTotal, 2) . " days) can be used across Nov & Dec combined. " . ((float)$alreadyUsedInNovDec > 0 ? "You already used " . number_format((float)$alreadyUsedInNovDec, 2) . " days, leaving " . number_format((float)$remainingNovDecCap, 2) . " days available. " : "") . "The remaining " . number_format((float)$lapsedLeaves, 2) . " leaves will lapse on 31st December.",
        ];
    }

    private function splitDays(
        EmployeeM $employee, 
        LeaveTypeM $leaveType, 
        LeavePolicyM $policy, 
        LeaveAllocationM $allocation, 
        Carbon $start, 
        array $dateRows, 
        string $deductedDays,
        array &$novDecMeta = []
    ): array
    {
        $paid = '0.000000';
        $sick = '0.000000';
        $compOff = '0.000000';
        $lwp = '0.000000';

        $paidCapacity = bcadd((string) ($allocation->paid_remaining ?? '0'), '0', 6);
        $sickCapacity = bcadd((string) ($allocation->sick_remaining ?? '0'), '0', 6);
        $compCapacity = bcadd((string) ($allocation->comp_off_remaining ?? '0'), '0', 6);
        $stageRules = strtolower((string) $allocation->employment_stage) !== LeaveAllocationService::STAGE_PERMANENT;
        if ($stageRules) {
            $monthlyAvailable = $paidCapacity;
        } else {
            throw ValidationException::withMessages([
                'allocation' => 'The legacy monthly quota leave flow has been retired. Enable a ledger-backed allocation first.',
            ]);
        }

        // November & December rule (100% DB Driven)
        if (in_array((int) $start->month, [11, 12], true) && (bool) ($policy->nov_dec_half_usage_enabled ?? true)) {
            $threshold = (string) ($policy->nov_dec_half_usage_threshold ?? $policy->nov_dec_full_balance_threshold ?? $policy->nov_dec_threshold_balance ?? '10.000000');
            $usagePct = (string) ($policy->nov_dec_half_usage_percentage ?? $policy->nov_dec_usage_percentage ?? '50.000000');
            $remaining = bcadd((string) ($allocation->total_remaining ?? '0'), '0', 6);

            if (bccomp($remaining, $threshold, 6) > 0) {
                $allowed = bcdiv(bcmul($remaining, $usagePct, 6), '100', 6);
                $paidCapacity = $this->minDecimal($paidCapacity, $allowed);
                $sickCapacity = $this->minDecimal($sickCapacity, $allowed);
                $compCapacity = $this->minDecimal($compCapacity, $allowed);

                $lapsed = bcsub($remaining, $allowed, 2);
                $novDecMeta = [
                    'active' => true,
                    'threshold' => (float) $threshold,
                    'usage_percentage' => (float) $usagePct,
                    'total_remaining' => (float) $remaining,
                    'allowed_limit' => (float) $allowed,
                    'already_used' => 0.0,
                    'allowed_remaining' => (float) $allowed,
                    'lapsed_leaves' => (float) $lapsed,
                    'message' => "November & December Rule Active: Your total remaining balance is " . number_format((float)$remaining, 2) . " days (> " . (int)$threshold . " days). Only 50% (" . number_format((float)$allowed, 2) . " days) can be used across Nov & Dec. The remaining " . number_format((float)$lapsed, 2) . " leaves will lapse at year end.",
                ];
            }
        }

        // Preserve the existing stage rule while keeping all arithmetic in decimal strings.
        $maxAllowedPaid = $this->minDecimal($paidCapacity, $monthlyAvailable);

        $deductCount = count(array_filter($dateRows, fn($r) => ! empty($r['deduct_as_leave'])));
        $dayUnit = $deductCount > 0 ? bcdiv($deductedDays, (string) $deductCount, 6) : '0.000000';

        foreach ($dateRows as $row) {
            if (! $row['deduct_as_leave']) {
                continue;
            }

            if ($leaveType->is_lwp) {
                $lwp = bcadd($lwp, $dayUnit, 6);
            } elseif ($leaveType->is_comp_off) {
                $remUnit = $dayUnit;
                $allocatedComp = $this->minDecimal($remUnit, $compCapacity);
                $compOff = bcadd($compOff, $allocatedComp, 6);
                $compCapacity = bcsub($compCapacity, $allocatedComp, 6);
                $remUnit = bcsub($remUnit, $allocatedComp, 6);

                if (bccomp($remUnit, '0', 6) > 0) {
                    $paidAvailable = bcsub($maxAllowedPaid, $paid, 6);
                    if (bccomp($paidAvailable, '0', 6) < 0) $paidAvailable = '0.000000';
                    $split = $this->paidLeaveSplitter->split(
                        LeaveBucket::PL,
                        $remUnit,
                        $paidAvailable,
                        '0.000000',
                        $policy,
                        false,
                        false
                    );
                    $paid = bcadd($paid, $split['pl'], 6);
                    $lwp = bcadd($lwp, $split['lop'], 6);
                }
            } else {
                $selected = LeaveBucket::fromLeaveType($leaveType);
                $paidAvailable = bcsub($maxAllowedPaid, $paid, 6);
                $sickAvailable = bcsub($sickCapacity, $sick, 6);
                if (bccomp($paidAvailable, '0', 6) < 0) $paidAvailable = '0.000000';
                if (bccomp($sickAvailable, '0', 6) < 0) $sickAvailable = '0.000000';
                $split = $this->paidLeaveSplitter->split(
                    $selected,
                    $dayUnit,
                    $paidAvailable,
                    $sickAvailable,
                    $policy,
                    false,
                    false
                );
                $paid = bcadd($paid, $split['pl'], 6);
                $sick = bcadd($sick, $split['sl'], 6);
                $lwp = bcadd($lwp, $split['lop'], 6);
            }
        }

        return [$paid, $sick, $compOff, $lwp];
    }

    private function ensureNoDuplicateDates(EmployeeM $employee, array $dateRows, ?LeaveRequestM $existingRequest): void
    {
        $dates = collect($dateRows)
            ->where('deduct_as_leave', true)
            ->pluck('leave_date')
            ->values()
            ->all();

        $duplicate = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->whereIn('leave_request_dates.leave_date', $dates)
            ->whereNotIn('leave_requests.status', ['rejected', 'cancelled', 'void', 'expired']);

        if ($existingRequest) {
            $duplicate->where('leave_requests.id', '<>', $existingRequest->id);
        }

        if ($duplicate->exists()) {
            throw ValidationException::withMessages(['start_date' => 'A non-cancelled leave request already exists for one or more selected dates.']);
        }
    }

    private function maxConsecutiveSickDaysIncludingRequest(EmployeeM $employee, array $dateRows, ?int $excludeRequestId = null): int
    {
        $requestedDates = collect($dateRows)
            ->filter(fn (array $row) => (bool) ($row['deduct_as_leave'] ?? false))
            ->pluck('leave_date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date, 'Asia/Kolkata')->toDateString())
            ->values()
            ->all();

        if (empty($requestedDates)) {
            return 0;
        }

        $approvedSickDates = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->where('leave_requests.status', 'approved')
            ->where(function ($query) {
                $query->where('leave_types.is_sick', 1)
                    ->orWhere('leave_types.code', 'sick_leave');
            })
            ->where('leave_request_dates.sick_day', '>', 0)
            ->when($excludeRequestId, fn($q) => $q->where('leave_requests.id', '<>', $excludeRequestId))
            ->pluck('leave_request_dates.leave_date')
            ->map(fn ($date) => Carbon::parse($date, 'Asia/Kolkata')->toDateString())
            ->all();

        $allSickSet = array_flip(array_merge($approvedSickDates, $requestedDates));

        $first = Carbon::parse(min($requestedDates), 'Asia/Kolkata');
        $last = Carbon::parse(max($requestedDates), 'Asia/Kolkata');

        $leftCount = 0;
        $cursor = $first->copy()->subDay();
        while (isset($allSickSet[$cursor->toDateString()])) {
            $leftCount++;
            $cursor->subDay();
        }

        $rightCount = 0;
        $cursor = $last->copy()->addDay();
        while (isset($allSickSet[$cursor->toDateString()])) {
            $rightCount++;
            $cursor->addDay();
        }

        $requestedSickCount = count($requestedDates);

        return $leftCount + $requestedSickCount + $rightCount;
    }

    private function applySplitToDates(array $dateRows, string $unit, string $paid, string $sick, string $compOff, string $lwp): array
    {
        foreach ($dateRows as &$row) {
            $row['paid_day'] = 0.0;
            $row['sick_day'] = 0.0;
            $row['comp_off_day'] = 0.0;
            $row['lwp_day'] = 0.0;

            if (! $row['deduct_as_leave']) {
                continue;
            }

            $remaining = $unit;

            $take = $this->minDecimal($remaining, $compOff);
            $row['comp_off_day'] = (float) $take;
            $compOff = bcsub($compOff, $take, 6);
            $remaining = bcsub($remaining, $take, 6);

            $take = $this->minDecimal($remaining, $paid);
            $row['paid_day'] = (float) $take;
            $paid = bcsub($paid, $take, 6);
            $remaining = bcsub($remaining, $take, 6);

            $take = $this->minDecimal($remaining, $sick);
            $row['sick_day'] = (float) $take;
            $sick = bcsub($sick, $take, 6);
            $remaining = bcsub($remaining, $take, 6);

            $take = $this->minDecimal($remaining, $lwp);
            $row['lwp_day'] = (float) $take;
            $lwp = bcsub($lwp, $take, 6);
        }

        return $dateRows;
    }

    private function minDecimal(string $left, string $right): string
    {
        return bccomp($left, $right, 6) <= 0 ? $left : $right;
    }

    private function policyForEmployee(EmployeeM $employee, Carbon $date): LeavePolicyM
    {
        $policy = $this->policyService->forEmployee($employee, $date);
        if (! $policy) {
            throw ValidationException::withMessages([
                'leave_type_id' => $this->policyService->lastResolutionReason() ?? 'Employee has no active leave policy.',
            ]);
        }

        return $policy;
    }
}
