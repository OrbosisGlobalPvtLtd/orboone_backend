<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Leave\LeaveBalanceLogM;
use App\Models\HRMS\Leave\LeaveRequestDateM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Payroll\PayrollAttendanceImpactM;
use App\Services\HRMS\Attendance\AttendanceSyncFromLeaveService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeaveApprovalService
{
    public function __construct(
        private LeaveCalculationService $calculationService,
        private LeaveAllocationService $allocationService,
        private AttendanceSyncFromLeaveService $attendanceSyncService,
        private CompOffService $compOffService
    ) {
    }

    public function approve(LeaveRequestM $leaveRequest, int $approvedByUserId, ?string $note = null): LeaveRequestM
    {
        return DB::transaction(function () use ($leaveRequest, $approvedByUserId, $note) {
            $leaveRequest = LeaveRequestM::with(['employee', 'leaveType'])->lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leaveRequest->status === 'approved') {
                return $leaveRequest;
            }

            if (! in_array($leaveRequest->status, ['pending', 'rejected'], true)) {
                throw ValidationException::withMessages(['status' => 'Only pending or rejected leave can be approved.']);
            }

            $calculation = $this->calculationService->calculate($leaveRequest->employee, $leaveRequest->leaveType, [
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
                'is_half_day' => $leaveRequest->is_half_day,
                'attachment_path' => $leaveRequest->attachment_path,
            ], $leaveRequest);

            $allocation = $calculation['allocation'];
            if ($allocation->is_locked) {
                throw ValidationException::withMessages(['allocation' => 'Leave allocation is locked for this employee/year.']);
            }

            LeaveRequestDateM::where('leave_request_id', $leaveRequest->id)->delete();
            foreach ($calculation['dates'] as $row) {
                LeaveRequestDateM::create(array_merge($row, [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                ]));
            }

            $before = (float) $allocation->total_remaining;
            $allocation->paid_used = (float) $allocation->paid_used + (float) $calculation['paid_days'];
            $allocation->sick_used = (float) $allocation->sick_used + (float) $calculation['sick_days'];
            $allocation->comp_off_used = (float) $allocation->comp_off_used + (float) $calculation['comp_off_days'];
            $allocation->lwp_used = (float) $allocation->lwp_used + (float) $calculation['lwp_days'];
            $this->allocationService->recalculateAllocationFields($allocation);
            $allocation->save();

            if ((float) $calculation['comp_off_days'] > 0) {
                $this->compOffService->consume($leaveRequest->employee, (float) $calculation['comp_off_days'], $leaveRequest->id);
            }

            $leaveRequest->forceFill([
                'requested_days' => $calculation['requested_days'],
                'deducted_days' => $calculation['deducted_days'],
                'paid_days' => $calculation['paid_days'],
                'sick_days' => $calculation['sick_days'],
                'comp_off_days' => $calculation['comp_off_days'],
                'lwp_days' => $calculation['lwp_days'],
                'sandwich_applied' => $calculation['sandwich_applied'],
                'status' => 'approved',
                'approved_by_user_id' => $approvedByUserId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
                'hr_approved_by' => $approvedByUserId,
                'hr_approved_at' => Carbon::now('Asia/Kolkata'),
                'hr_note' => $note,
                'auto_converted_to_lwp' => (float) $calculation['lwp_days'] > 0,
            ])->save();

            $this->logBalance($leaveRequest, $allocation, 'leave_approved', $before, (float) $allocation->total_remaining, $approvedByUserId);
            $this->attendanceSyncService->syncApprovedLeave($leaveRequest->fresh(['dates']), $approvedByUserId);
            $this->createPayrollImpacts($leaveRequest->fresh(['dates']), $approvedByUserId);

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

            $leaveRequest->forceFill([
                'status' => 'rejected',
                'approved_by_user_id' => $rejectedByUserId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
                'rejection_reason' => $reason,
            ])->save();

            return $leaveRequest->fresh(['employee', 'leaveType', 'dates']);
        });
    }

    public function cancel(LeaveRequestM $leaveRequest, int $cancelledByUserId, ?string $reason = null): LeaveRequestM
    {
        return DB::transaction(function () use ($leaveRequest, $cancelledByUserId, $reason) {
            $leaveRequest = LeaveRequestM::with(['employee', 'leaveType'])->lockForUpdate()->findOrFail($leaveRequest->id);

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

            return $leaveRequest->fresh(['employee', 'leaveType', 'dates']);
        });
    }

    private function reverseApprovedLeave(LeaveRequestM $leaveRequest, int $userId, string $action): void
    {
        try {
            $allocation = $this->allocationService->getOrGenerate($leaveRequest->employee, Carbon::parse($leaveRequest->start_date)->year, $userId);
            if ($allocation->is_locked) {
                throw ValidationException::withMessages(['allocation' => 'Leave allocation is locked and cannot be reversed.']);
            }

            $this->attendanceSyncService->reverseLeaveSync($leaveRequest, $userId);

            $before = (float) $allocation->total_remaining;
            $allocation->paid_used = max(0, (float) $allocation->paid_used - (float) $leaveRequest->paid_days);
            $allocation->sick_used = max(0, (float) $allocation->sick_used - (float) $leaveRequest->sick_days);
            $allocation->comp_off_used = max(0, (float) $allocation->comp_off_used - (float) $leaveRequest->comp_off_days);
            $allocation->lwp_used = max(0, (float) $allocation->lwp_used - (float) $leaveRequest->lwp_days);
            $this->allocationService->recalculateAllocationFields($allocation);
            $allocation->save();

            DB::table('payroll_attendance_impacts')
                ->where('leave_request_id', $leaveRequest->id)
                ->where('is_processed_in_payroll', 0)
                ->delete();

            $this->logBalance($leaveRequest, $allocation, $action, $before, (float) $allocation->total_remaining, $userId);
        } catch (\Throwable $e) {
            Log::error('Leave reversal failed', ['leave_request_id' => $leaveRequest->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function logBalance(LeaveRequestM $leaveRequest, $allocation, string $action, float $before, float $after, int $userId): void
    {
        LeaveBalanceLogM::create([
            'employee_id' => $leaveRequest->employee_id,
            'leave_allocation_id' => $allocation->id,
            'leave_request_id' => $leaveRequest->id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'action' => $action,
            'credit' => max(0, $after - $before),
            'debit' => max(0, $before - $after),
            'balance_before' => $before,
            'balance_after' => $after,
            'remarks' => 'Leave request #' . $leaveRequest->id,
            'created_by_user_id' => $userId,
        ]);
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
}
