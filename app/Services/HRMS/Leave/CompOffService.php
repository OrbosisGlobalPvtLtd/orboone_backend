<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\CompOffM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompOffService
{
    public function __construct(private LeavePolicyService $policyService, private LeaveAllocationService $allocationService)
    {
    }

    public function generateFromHolidayWork(HolidayWorkRequestM $request, ?int $approvedByUserId = null): ?CompOffM
    {
        return DB::transaction(function () use ($request, $approvedByUserId) {
            $employee = $request->employee;
            $workedDate = Carbon::parse($request->worked_date, 'Asia/Kolkata');

            // 1. Prevent duplicate comp off generation
            if ($request->comp_off_generated || $request->status === 'completed') {
                if ($request->comp_off_id) {
                    return CompOffM::find($request->comp_off_id);
                }
                return null;
            }

            $duplicateCompOff = CompOffM::where('employee_id', $employee->id)
                ->whereDate('worked_date', $workedDate->toDateString())
                ->first();

            if ($duplicateCompOff) {
                $request->update([
                    'status' => 'approved',
                    'comp_off_generated' => true,
                    'comp_off_id' => $duplicateCompOff->id,
                ]);
                return $duplicateCompOff;
            }

            // Expiry date is the end of the worked date's month
            $expiryDate = $workedDate->copy()->endOfMonth();

            $compOff = CompOffM::create([
                'employee_id' => $employee->id,
                'worked_date' => $workedDate->toDateString(),
                'earned_days' => 1.0,
                'expiry_date' => $expiryDate->toDateString(),
                'status' => 'earned',
                'approved_by_user_id' => $approvedByUserId ?: $request->approved_by_user_id,
                'approved_at' => Carbon::now('Asia/Kolkata'),
                'remarks' => "Generated from approved holiday/weekoff work request #{$request->id}.",
            ]);

            $request->update([
                'status' => 'approved',
                'comp_off_generated' => true,
                'comp_off_id' => $compOff->id,
            ]);

            $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year, $approvedByUserId);
            $allocation->comp_off_allocated = (float) $allocation->comp_off_allocated + (float) $compOff->earned_days;
            $this->allocationService->recalculateAllocationFields($allocation);
            $allocation->save();

            return $compOff;
        });
    }

    public function validateAndProcessRequest(HolidayWorkRequestM $request): bool
    {
        if ($request->status !== 'approved' || $request->comp_off_generated) {
            return false;
        }

        $workedDate = Carbon::parse($request->worked_date, 'Asia/Kolkata');
        $today = Carbon::now('Asia/Kolkata')->startOfDay();
        if ($workedDate->greaterThan($today)) {
            return false;
        }

        $attendance = \App\Models\HRMS\Attendance\AttendanceM::where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $workedDate->toDateString())
            ->first();

        if (!$attendance || !$attendance->punch_in_time || !$attendance->punch_out_time) {
            return false;
        }

        $resolver = app(\App\Services\HRMS\Attendance\AttendanceRuleResolverService::class);
        $policy = $resolver->getPolicyForEmployee($request->employee, $workedDate->toDateString());
        $requiredMinutes = $policy->required_work_minutes ?? 480;

        $hasEligibleStatus = in_array(
            strtolower((string) ($attendance->status ?? $attendance->attendance_status ?? 'present')),
            ['present', 'approved', 'completed'],
            true
        );

        if ($hasEligibleStatus && (int) ($attendance->total_work_minutes ?? 0) >= (int) $requiredMinutes) {
            if (!$request->attendance_id) {
                $request->update(['attendance_id' => $attendance->id]);
            }

            $this->generateFromHolidayWork($request, $request->approved_by_user_id);
            return true;
        }

        return false;
    }

    public function expireDue(?Carbon $date = null): int
    {
        $date = $date ?: Carbon::now('Asia/Kolkata');

        return CompOffM::where('status', 'earned')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $date->toDateString())
            ->update(['status' => 'expired', 'updated_at' => now()]);
    }

    public function consume(EmployeeM $employee, float $days, int $leaveRequestId): float
    {
        $remaining = $days;
        $earned = CompOffM::where('employee_id', $employee->id)
            ->where('status', 'earned')
            ->where(function ($query) {
                $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', Carbon::now('Asia/Kolkata')->toDateString());
            })
            ->orderBy('expiry_date')
            ->lockForUpdate()
            ->get();

        foreach ($earned as $compOff) {
            if ($remaining <= 0) {
                break;
            }

            $consume = min($remaining, (float) $compOff->earned_days);
            $remaining = round($remaining - $consume, 2);
            $leftInRecord = round((float) $compOff->earned_days - $consume, 2);
            $compOff->earned_days = max(0, $leftInRecord);
            $compOff->status = $leftInRecord <= 0 ? 'used' : 'earned';
            $compOff->used_against_leave_request_id = $leftInRecord <= 0 ? $leaveRequestId : null;
            $compOff->remarks = trim(($compOff->remarks ? $compOff->remarks . "\n" : '') . 'Used ' . $consume . ' day(s).');
            $compOff->save();
        }

        return round($days - $remaining, 2);
    }
}
