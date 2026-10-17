<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Leave\LeaveRequestDateM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveBalanceTransactionM;
use App\Enums\LeaveBucket;
use App\Models\HRMS\Payroll\PayrollAttendanceImpactM;
use App\Services\HRMS\Attendance\AttendanceSyncFromLeaveService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Core\UserM;
use App\Services\HRMS\Notification\NotificationS;

class LeaveApprovalService
{
    public function __construct(
        private LeaveCalculationService $calculationService,
        private LeaveAllocationService $allocationService,
        private AttendanceSyncFromLeaveService $attendanceSyncService,
        private CompOffService $compOffService,
        private LeaveBalanceMovementService $movementService,
        private LeaveBalanceService $balanceService
    ) {
    }

    public function approveManagerStage(LeaveRequestM $leaveRequest, int $managerUserId, ?string $note = null): LeaveRequestM
    {
        return DB::transaction(function () use ($leaveRequest, $managerUserId, $note) {
            $leaveRequest = LeaveRequestM::with(['employee', 'leaveType'])->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leaveRequest->status === 'approved' || $leaveRequest->approval_level === 'manager_approved' || ! empty($leaveRequest->manager_approved_at)) {
                return $leaveRequest;
            }

            if ($leaveRequest->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending leave requests can be approved by Manager.']);
            }

            if (empty($leaveRequest->reporting_manager_employee_id)) {
                $mgrId = $leaveRequest->employee?->reporting_manager_employee_id 
                    ?: DB::table('employees_new')->where('id', $leaveRequest->employee_id)->value('reporting_manager_employee_id');
                if ($mgrId) {
                    $leaveRequest->reporting_manager_employee_id = $mgrId;
                    $leaveRequest->save();
                } else {
                    throw ValidationException::withMessages(['reporting_manager' => 'This employee has no Reporting Manager assigned. HR approval is required directly.']);
                }
            }

            $leaveRequest->forceFill([
                'approval_level' => 'manager_approved',
                'manager_approved_by' => $managerUserId,
                'manager_approved_at' => Carbon::now('Asia/Kolkata'),
                'manager_note' => $note,
            ])->save();

            $this->notifyLeaveDecision($leaveRequest->fresh(['employee.user', 'leaveType', 'dates']), 'leave_manager_approved', null, $managerUserId);

            return $leaveRequest->fresh(['employee', 'leaveType', 'dates']);
        });
    }

    public function approve(LeaveRequestM $leaveRequest, int $approvedByUserId, ?string $note = null): LeaveRequestM
    {
        return DB::transaction(function () use ($leaveRequest, $approvedByUserId, $note) {
            $leaveRequest = LeaveRequestM::with(['employee', 'leaveType'])->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leaveRequest->status === 'approved') {
                return $leaveRequest;
            }

            if (! in_array($leaveRequest->status, ['pending', 'manager_approved', 'rejected'], true)) {
                throw ValidationException::withMessages(['status' => 'Only pending, manager approved, or rejected leave can be approved by HR.']);
            }

            if (LeaveBalanceTransactionM::query()->where('leave_request_id', $leaveRequest->id)
                ->whereIn('transaction_type', ['leave_debit', 'reversal'])->exists()) {
                throw ValidationException::withMessages(['status' => 'A debited leave request cannot be approved again; submit a new request.']);
            }

            $years = $this->requestYears($leaveRequest);
            $lockedAllocations = LeaveAllocationM::query()->where('employee_id', $leaveRequest->employee_id)
                ->whereIn('year', $years)->orderBy('id')->lockForUpdate()->get()->keyBy('year');

            $calculation = $this->calculationService->calculate($leaveRequest->employee, $leaveRequest->leaveType, [
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
                'is_half_day' => $leaveRequest->is_half_day,
                'attachment_path' => $leaveRequest->attachment_path,
            ], $leaveRequest, true);

            $allocation = $calculation['allocation'];
            $ledgerBacked = $allocation->isLedgerBacked();
            if (! $ledgerBacked && strtolower((string) $allocation->employment_stage) === LeaveAllocationService::STAGE_PERMANENT) {
                throw ValidationException::withMessages([
                    'allocation' => 'Legacy permanent leave approval is disabled. Enable the employee in the new ledger leave model first.',
                ]);
            }
            if ($ledgerBacked) {
                foreach ($years as $year) {
                    $yearAllocation = $lockedAllocations->get($year);
                    if (! $yearAllocation || ! $yearAllocation->isLedgerBacked()) {
                        throw ValidationException::withMessages(['allocation' => "Ledger allocation for {$year} is missing or not enabled."]);
                    }
                    if ($yearAllocation->is_locked) throw ValidationException::withMessages(['allocation' => "Leave allocation {$year} is locked."]);
                }
            } else {
                if ($allocation->is_locked) {
                    throw ValidationException::withMessages(['allocation' => 'Leave allocation is locked for this employee/year.']);
                }
            }

            LeaveRequestDateM::where('leave_request_id', $leaveRequest->id)->delete();
            $dateIdsByDate = [];
            foreach ($calculation['dates'] as $row) {
                $savedDate = LeaveRequestDateM::create(array_merge($row, [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                ]));
                $dateIdsByDate[Carbon::parse($savedDate->leave_date, 'Asia/Kolkata')->toDateString()] = (int) $savedDate->id;
            }

            if ($ledgerBacked) {
                $this->writeLedgerDebits($leaveRequest, $calculation['dates'], $lockedAllocations, $approvedByUserId);
                $this->compOffService->postLedgerUsage(
                    $leaveRequest->employee,
                    (int) $leaveRequest->id,
                    $calculation['_comp_off_usage_allocations'] ?? [],
                    $dateIdsByDate,
                    $approvedByUserId
                );
            } else {
                $allocation = $this->allocationService->adjustLegacyAllocation(
                    $allocation,
                    [
                        'paid_used' => (string) $calculation['paid_days'],
                        'sick_used' => (string) $calculation['sick_days'],
                        'comp_off_used' => (string) $calculation['comp_off_days'],
                        'lwp_used' => (string) $calculation['lwp_days'],
                    ],
                    $approvedByUserId,
                    'leave_approved',
                    'Approved leave request #' . $leaveRequest->id,
                    (int) $leaveRequest->id,
                    (int) $leaveRequest->leave_type_id
                );
            }

            if (! $ledgerBacked && (float) $calculation['comp_off_days'] > 0) {
                $this->compOffService->consume(
                    $leaveRequest->employee,
                    (string) $calculation['comp_off_days'],
                    $leaveRequest->id,
                    $approvedByUserId
                );
            }

            $hasManager = ! empty($leaveRequest->reporting_manager_employee_id);
            $managerApprovedBy = $leaveRequest->manager_approved_by;
            $managerApprovedAt = $leaveRequest->manager_approved_at;

            $leaveRequest->forceFill([
                'requested_days' => $calculation['requested_days'],
                'deducted_days' => $calculation['deducted_days'],
                'paid_days' => $calculation['paid_days'],
                'sick_days' => $calculation['sick_days'],
                'comp_off_days' => $calculation['comp_off_days'],
                'lwp_days' => $calculation['lwp_days'],
                'sandwich_applied' => $calculation['sandwich_applied'],
                'approval_level' => 'hr_approved',
                'status' => 'approved',
                'manager_approved_by' => $managerApprovedBy,
                'manager_approved_at' => $managerApprovedAt,
                'approved_by_user_id' => $approvedByUserId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
                'hr_approved_by' => $approvedByUserId,
                'hr_approved_at' => Carbon::now('Asia/Kolkata'),
                'hr_note' => $note,
                'auto_converted_to_lwp' => (float) $calculation['lwp_days'] > 0,
            ])->save();

            $this->refreshMonthlyPassbookForRequest($leaveRequest);
            $this->attendanceSyncService->syncApprovedLeave($leaveRequest->fresh(['dates']), $approvedByUserId);
            $this->createPayrollImpacts($leaveRequest->fresh(['dates']), $approvedByUserId);
            $this->notifyLeaveDecision($leaveRequest->fresh(['employee.user', 'leaveType', 'dates']), 'leave_approved', null, $approvedByUserId);

            return $leaveRequest->fresh(['employee', 'leaveType', 'dates']);
        });
    }

    public function reject(LeaveRequestM $leaveRequest, int $rejectedByUserId, string $reason): LeaveRequestM
    {
        return DB::transaction(function () use ($leaveRequest, $rejectedByUserId, $reason) {
            $leaveRequest = LeaveRequestM::with(['employee', 'leaveType'])->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leaveRequest->status === 'approved') {
                $this->reverseApprovedLeave($leaveRequest, $rejectedByUserId, 'leave_rejected_reversal');
            }

            $hasManager = ! empty($leaveRequest->reporting_manager_employee_id);
            $managerApproved = ! empty($leaveRequest->manager_approved_at) || $leaveRequest->approval_level === 'manager_approved';
            $level = ($hasManager && ! $managerApproved) ? 'manager_rejected' : 'hr_rejected';

            $leaveRequest->forceFill([
                'status' => 'rejected',
                'approval_level' => $level,
                'approved_by_user_id' => $rejectedByUserId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
                'rejection_reason' => $reason,
            ])->save();

            $this->refreshMonthlyPassbookForRequest($leaveRequest);
            $this->notifyLeaveDecision($leaveRequest->fresh(['employee.user', 'leaveType', 'dates']), 'leave_rejected', $reason, $rejectedByUserId);

            return $leaveRequest->fresh(['employee', 'leaveType', 'dates']);
        });
    }

    public function cancel(LeaveRequestM $leaveRequest, int $cancelledByUserId, ?string $reason = null): LeaveRequestM
    {
        return DB::transaction(function () use ($leaveRequest, $cancelledByUserId, $reason) {
            $leaveRequest = LeaveRequestM::with(['employee', 'leaveType'])->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leaveRequest->status === 'cancelled') return $leaveRequest;

            if ($leaveRequest->status === 'approved') {
                $this->reverseApprovedLeave($leaveRequest, $cancelledByUserId, 'leave_cancelled_reversal');
            }

            if (! in_array($leaveRequest->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages(['status' => 'Only pending or approved leave can be cancelled.']);
            }

            $leaveRequest->forceFill([
                'status' => 'cancelled',
                'cancelled_by_user_id' => $cancelledByUserId,
                'cancelled_at' => Carbon::now('Asia/Kolkata'),
                'cancel_reason' => $reason,
            ])->save();

            $this->refreshMonthlyPassbookForRequest($leaveRequest);
            return $leaveRequest->fresh(['employee', 'leaveType', 'dates']);
        });
    }

    public function voidLeave(LeaveRequestM $leaveRequest, int $voidedByUserId, string $note): LeaveRequestM
    {
        return DB::transaction(function () use ($leaveRequest, $voidedByUserId, $note) {
            $leaveRequest = LeaveRequestM::with(['employee', 'leaveType', 'dates'])->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leaveRequest->status === 'void') return $leaveRequest;

            if ($leaveRequest->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Only approved leave requests can be marked as Null & Void.']);
            }

            $this->reverseApprovedLeave($leaveRequest, $voidedByUserId, 'leave_void_reversal');

            $leaveRequest->forceFill([
                'status' => 'void',
                'approval_level' => 'hr_voided',
                'hr_note' => $note,
                'cancel_reason' => $note,
                'cancelled_by_user_id' => $voidedByUserId,
                'cancelled_at' => Carbon::now('Asia/Kolkata'),
            ])->save();

            $this->refreshMonthlyPassbookForRequest($leaveRequest);
            $this->notifyLeaveDecision($leaveRequest->fresh(['employee.user', 'leaveType', 'dates']), 'leave_voided', $note);

            return $leaveRequest->fresh(['employee', 'leaveType', 'dates']);
        });
    }

    private function reverseApprovedLeave(LeaveRequestM $leaveRequest, int $userId, string $action): void
    {
        try {
            $allocation = $this->allocationService->getOrGenerate($leaveRequest->employee, Carbon::parse($leaveRequest->start_date)->year, $userId);
            if ($allocation->isLedgerBacked()) {
                $debits = LeaveBalanceTransactionM::query()->where('leave_request_id', $leaveRequest->id)
                    ->where('transaction_type', 'leave_debit')->orderBy('leave_allocation_id')->orderBy('id')->get();
                $allocationIds = $debits->pluck('leave_allocation_id')->unique()->sort()->values();
                LeaveAllocationM::query()->whereIn('id', $allocationIds)->orderBy('id')->lockForUpdate()->get();
                foreach ($debits as $debit) $this->movementService->reverse((int) $debit->id, $userId, $action);
                $this->attendanceSyncService->reverseLeaveSync($leaveRequest, $userId);
                DB::table('payroll_attendance_impacts')->where('leave_request_id', $leaveRequest->id)
                    ->where('is_processed_in_payroll', 0)->delete();
                if ((float) $leaveRequest->comp_off_days > 0) {
                    $reversed = $this->compOffService->reverseLedgerUsageForRequest(
                        $leaveRequest->employee,
                        (int) $leaveRequest->id,
                        $userId,
                        ucfirst(str_replace('_', ' ', $action)) . ' for leave request #' . $leaveRequest->id
                    );
                    if (! $reversed) {
                        $this->compOffService->refund(
                            $leaveRequest->employee,
                            (string) $leaveRequest->comp_off_days,
                            $leaveRequest->id,
                            $userId
                        );
                    }
                }
                return;
            }
            if (strtolower((string) $allocation->employment_stage) === LeaveAllocationService::STAGE_PERMANENT) {
                throw ValidationException::withMessages([
                    'allocation' => 'Legacy permanent leave reversals are disabled. Reconcile this request through the new leave ledger.',
                ]);
            }
            if ($allocation->is_locked) {
                throw ValidationException::withMessages(['allocation' => 'Leave allocation is locked and cannot be reversed.']);
            }

            $this->attendanceSyncService->reverseLeaveSync($leaveRequest, $userId);

            $allocation = $this->allocationService->adjustLegacyAllocation(
                $allocation,
                [
                    'paid_used' => bcsub('0', (string) $leaveRequest->paid_days, 6),
                    'sick_used' => bcsub('0', (string) $leaveRequest->sick_days, 6),
                    'comp_off_used' => bcsub('0', (string) $leaveRequest->comp_off_days, 6),
                    'lwp_used' => bcsub('0', (string) $leaveRequest->lwp_days, 6),
                ],
                $userId,
                $action,
                'Reversal for leave request #' . $leaveRequest->id,
                (int) $leaveRequest->id,
                (int) $leaveRequest->leave_type_id
            );

            if ((float) $leaveRequest->comp_off_days > 0) {
                $this->compOffService->refund(
                    $leaveRequest->employee,
                    (string) $leaveRequest->comp_off_days,
                    $leaveRequest->id,
                    $userId
                );
            }

            DB::table('payroll_attendance_impacts')
                ->where('leave_request_id', $leaveRequest->id)
                ->where('is_processed_in_payroll', 0)
                ->delete();

        } catch (\Throwable $e) {
            Log::error('Leave reversal failed', ['leave_request_id' => $leaveRequest->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function refreshMonthlyPassbookForRequest(LeaveRequestM $leaveRequest): void
    {
        $employee = $leaveRequest->employee;
        if (! $employee) {
            return;
        }

        $periods = LeaveRequestDateM::query()
            ->where('leave_request_id', $leaveRequest->id)
            ->pluck('leave_date')
            ->map(fn ($date): string => Carbon::parse($date, 'Asia/Kolkata')->format('Y-n'))
            ->unique()
            ->map(function (string $period): array {
                [$year, $month] = array_map('intval', explode('-', $period));
                return ['year' => $year, 'month' => $month];
            })
            ->groupBy('year');

        foreach ($periods as $year => $months) {
            app(LeaveMonthlyBalanceService::class)->rebuildEmployeeThrough(
                $employee,
                (int) $year,
                (int) $months->max('month')
            );
        }
    }

    /** @return list<int> */
    private function requestYears(LeaveRequestM $request): array
    {
        $years = LeaveRequestDateM::query()->where('leave_request_id', $request->id)
            ->where('deduct_as_leave', true)->pluck('leave_date')
            ->map(fn ($date): int => (int) Carbon::parse($date, 'Asia/Kolkata')->year)->unique()->values()->all();
        return $years ?: [Carbon::parse($request->start_date, 'Asia/Kolkata')->year];
    }

    /** Debit each bucket against its date's allocation year; caller has locked allocations in stable order. */
    private function writeLedgerDebits(LeaveRequestM $request, array $dates, $allocations, int $actorId): void
    {
        $totals = [];
        foreach ($dates as $date) {
            if (! ($date['deduct_as_leave'] ?? false)) continue;
            $year = (int) Carbon::parse($date['leave_date'], 'Asia/Kolkata')->year;
            $totals[$year]['PL'] = bcadd($totals[$year]['PL'] ?? '0.000000', (string) ($date['paid_day'] ?? 0), 6);
            $totals[$year]['SL'] = bcadd($totals[$year]['SL'] ?? '0.000000', (string) ($date['sick_day'] ?? 0), 6);
        }

        $selectedBucket = null;
        try {
            if ($request->leaveType && ! $request->leaveType->is_comp_off && ! $request->leaveType->is_lwp) {
                $selectedBucket = LeaveBucket::fromLeaveType($request->leaveType);
            }
        } catch (\Throwable) {
            $selectedBucket = null;
        }

        foreach ($totals as $year => $buckets) {
            $allocation = $allocations->get($year);
            foreach ([LeaveBucket::PL, LeaveBucket::SL] as $bucket) {
                $amount = $buckets[$bucket->value] ?? '0.000000';
                if (bccomp($amount, '0', 6) <= 0) continue;
                $date = collect($dates)->first(fn (array $row): bool =>
                    Carbon::parse($row['leave_date'], 'Asia/Kolkata')->year === (int) $year
                    && bccomp((string) ($row[$bucket === LeaveBucket::PL ? 'paid_day' : 'sick_day'] ?? 0), '0', 6) > 0
                );

                $reason = 'Approved leave debit';
                if ($request->leaveType?->is_comp_off) {
                    $reason = sprintf('Approved leave debit (Comp-Off shortfall covered by %s)', $bucket->value);
                } elseif ($selectedBucket && $bucket !== $selectedBucket) {
                    $reason = sprintf(
                        'Approved leave debit (adjusted from %s due to insufficient %s balance)',
                        $bucket->value,
                        $selectedBucket->value
                    );
                }

                $this->movementService->debit(
                    $allocation, $bucket, (int) $request->id, (int) $year,
                    (int) Carbon::parse($date['leave_date'], 'Asia/Kolkata')->month,
                    $amount, Carbon::parse($date['leave_date'], 'Asia/Kolkata')->toDateString(), $actorId,
                    'system', $reason
                );
            }
        }
    }

    private function createPayrollImpacts(LeaveRequestM $leaveRequest, int $userId): void
    {
        $monthlyLwp = [];
        foreach ($leaveRequest->dates as $dateRow) {
            $lwpDay = (float) $dateRow->lwp_day;
            if ($lwpDay <= 0) {
                continue;
            }

            $date = Carbon::parse($dateRow->leave_date, 'Asia/Kolkata');
            $key = $date->year . '-' . $date->month;
            if (! isset($monthlyLwp[$key])) {
                $monthlyLwp[$key] = ['month' => $date->month, 'year' => $date->year, 'days' => 0.0];
            }
            $monthlyLwp[$key]['days'] = round($monthlyLwp[$key]['days'] + $lwpDay, 2);
        }

        foreach ($monthlyLwp as $row) {
            PayrollAttendanceImpactM::updateOrCreate(
                [
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_request_id' => $leaveRequest->id,
                    'month' => $row['month'],
                    'year' => $row['year'],
                    'impact_type' => 'lwp',
                ],
                [
                    'impact_days' => $row['days'],
                    'impact_amount' => 0,
                    'remarks' => 'LWP generated from leave approval by user #' . $userId,
                    'is_processed_in_payroll' => false,
                ]
            );
        }
    }

    private function getApproverRoleTitle(int $actorId): string
    {
        $user = UserM::with('role')->find($actorId);
        if (! $user) {
            return 'Approver';
        }

        $roleName = strtolower((string) ($user->role?->name ?? $user->role?->code ?? $user->role?->title ?? ''));
        if (str_contains($roleName, 'super')) {
            return 'Super Admin';
        }
        if (str_contains($roleName, 'admin') && ! str_contains($roleName, 'hr')) {
            return 'Admin';
        }
        if (str_contains($roleName, 'hr')) {
            return 'HR';
        }
        if (str_contains($roleName, 'manager')) {
            return 'Manager';
        }

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('super_admin')) return 'Super Admin';
            if ($user->hasRole('admin')) return 'Admin';
            if ($user->hasRole('hr_admin') || $user->hasRole('hr')) return 'HR';
            if ($user->hasRole('manager')) return 'Manager';
        }

        return 'HR';
    }

    private function notifyLeaveDecision(LeaveRequestM $leaveRequest, string $type, ?string $reason = null, ?int $actorId = null): void
    {
        $userId = $leaveRequest->user_id ?: $leaveRequest->employee?->user_id;
        if (! $userId) {
            return;
        }

        $approverRole = 'HR';
        if ($type === 'leave_manager_approved' || $leaveRequest->approval_level === 'manager_rejected') {
            $approverRole = 'Manager';
        } elseif ($actorId) {
            $approverRole = $this->getApproverRoleTitle($actorId);
        }

        $leaveType = $leaveRequest->leaveType?->name ?: 'Leave';
        $fromDate = Carbon::parse($leaveRequest->start_date)->format('Y-m-d');
        $toDate = Carbon::parse($leaveRequest->end_date)->format('Y-m-d');
        $dateRange = $fromDate . ' to ' . $toDate;

        $title = match ($type) {
            'leave_manager_approved' => "Leave Approved by {$approverRole}",
            'leave_approved' => "Leave Approved by {$approverRole}",
            default => "Leave Rejected by {$approverRole}",
        };

        $message = match ($type) {
            'leave_manager_approved' => "Your {$leaveType} leave from {$fromDate} to {$toDate} has been approved by your Manager.",
            'leave_approved' => "Your {$leaveType} leave from {$fromDate} to {$toDate} has been approved by {$approverRole}.",
            default => "Your {$leaveType} leave from {$fromDate} to {$toDate} has been rejected by {$approverRole}." . ($reason ? " Reason: {$reason}" : ""),
        };

        app(NotificationS::class)->notifyEmployee(
            $title,
            $message,
            $type,
            'leave',
            ['leave_id' => $leaveRequest->id],
            [
                'leave_id' => $leaveRequest->id,
                'leave_type' => $leaveType,
                'leave_dates' => $dateRange,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'start_date' => (string) $leaveRequest->start_date,
                'end_date' => (string) $leaveRequest->end_date,
                'employee_id' => $leaveRequest->employee_id,
                'employee_name' => $leaveRequest->employee?->display_name,
                'status' => $leaveRequest->status,
                'reason' => $reason,
                'attachment_url' => $this->leaveAttachmentUrl($leaveRequest->attachment_path),
                'attachment_type' => $leaveRequest->attachment_path ? $this->attachmentType($leaveRequest->attachment_path) : '',
                'attachment_name' => $leaveRequest->attachment_path ? basename($leaveRequest->attachment_path) : '',
            ],
            $userId
        );
    }

    private function leaveAttachmentUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    private function attachmentType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'image';
        }

        return $extension === 'pdf' ? 'pdf' : 'document';
    }
}
