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

    public function generateFromHolidayWork(HolidayWorkRequestM $request, ?int $approvedByUserId = null): CompOffM
    {
        return DB::transaction(function () use ($request, $approvedByUserId) {
            $employee = $request->employee;
            $policy = $this->policyService->forEmployee($employee, Carbon::parse($request->worked_date, 'Asia/Kolkata'));
            $workedDate = Carbon::parse($request->worked_date, 'Asia/Kolkata');
            $expiryDate = $policy->comp_off_expiry_same_month ? $workedDate->copy()->endOfMonth() : null;

            $compOff = CompOffM::updateOrCreate(
                ['employee_id' => $employee->id, 'worked_date' => $workedDate->toDateString()],
                [
                    'earned_days' => 1,
                    'expiry_date' => $expiryDate?->toDateString(),
                    'status' => 'earned',
                    'approved_by_user_id' => $approvedByUserId,
                    'approved_at' => Carbon::now('Asia/Kolkata'),
                    'remarks' => 'Generated from approved holiday/weekoff work.',
                ]
            );

            $request->update([
                'status' => 'approved',
                'approved_by_user_id' => $approvedByUserId,
                'approved_at' => Carbon::now('Asia/Kolkata'),
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
