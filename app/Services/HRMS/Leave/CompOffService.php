<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\CompOffM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompOffService
{
    public function __construct(private LeavePolicyService $policyService, private LeaveAllocationService $allocationService)
    {
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

            $existingCompOff = CompOffM::where('employee_id', $employee->id)
                ->whereDate('worked_date', $dateStr)
                ->lockForUpdate()
                ->first();

            if ($targetEarnedDays > 0) {
                $expiryDate = $workedDate->copy()->endOfMonth();

                if (!$existingCompOff) {
                    $compOff = CompOffM::create([
                        'employee_id' => $employee->id,
                        'worked_date' => $dateStr,
                        'earned_days' => $targetEarnedDays,
                        'expiry_date' => $expiryDate->toDateString(),
                        'status' => 'earned',
                        'approved_by_user_id' => $approvedByUserId ?: $request->approved_by_user_id,
                        'approved_at' => Carbon::now('Asia/Kolkata'),
                        'remarks' => "Generated from approved holiday/weekoff work request #{$request->id}.",
                    ]);

                    $request->update([
                        'comp_off_generated' => true,
                        'comp_off_id' => $compOff->id,
                        'attendance_id' => $attendance?->id ?: $request->attendance_id,
                    ]);

                    $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year, $approvedByUserId);
                    $allocation->comp_off_allocated = (float) $allocation->comp_off_allocated + $targetEarnedDays;
                    $this->allocationService->recalculateAllocationFields($allocation);
                    $allocation->save();

                    return $compOff;
                } else {
                    $currentEarned = (float) $existingCompOff->earned_days;
                    $diff = round($targetEarnedDays - $currentEarned, 2);

                    if ($diff != 0) {
                        $existingCompOff->update([
                            'earned_days' => $targetEarnedDays,
                            'status' => 'earned',
                            'approved_by_user_id' => $approvedByUserId ?: $request->approved_by_user_id ?: $existingCompOff->approved_by_user_id,
                            'remarks' => "Reconciled from approved holiday/weekoff work request #{$request->id}.",
                        ]);

                        $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year, $approvedByUserId);
                        $allocation->comp_off_allocated = max(0.0, (float) $allocation->comp_off_allocated + $diff);
                        $this->allocationService->recalculateAllocationFields($allocation);
                        $allocation->save();
                    }

                    $request->update([
                        'comp_off_generated' => true,
                        'comp_off_id' => $existingCompOff->id,
                        'attendance_id' => $attendance?->id ?: $request->attendance_id,
                    ]);

                    return $existingCompOff;
                }
            } else {
                if ($existingCompOff) {
                    $currentEarned = (float) $existingCompOff->earned_days;
                    if ($currentEarned > 0) {
                        $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year, $approvedByUserId);
                        $allocation->comp_off_allocated = max(0.0, (float) $allocation->comp_off_allocated - $currentEarned);
                        $this->allocationService->recalculateAllocationFields($allocation);
                        $allocation->save();
                    }

                    $existingCompOff->delete();
                }

                $request->update([
                    'comp_off_generated' => false,
                    'comp_off_id' => null,
                    'attendance_id' => $attendance?->id ?: $request->attendance_id,
                ]);

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

            $existingCompOff = CompOffM::where('employee_id', $request->employee_id)
                ->whereDate('worked_date', $dateStr)
                ->lockForUpdate()
                ->first();

            if ($existingCompOff) {
                $earnedDays = (float) $existingCompOff->earned_days;
                if ($employee && $earnedDays > 0) {
                    $allocation = $this->allocationService->getOrGenerate($employee, $workedDate->year);
                    $allocation->comp_off_allocated = max(0.0, (float) $allocation->comp_off_allocated - $earnedDays);
                    $this->allocationService->recalculateAllocationFields($allocation);
                    $allocation->save();
                }
                $existingCompOff->delete();
            }

            $request->update([
                'comp_off_generated' => false,
                'comp_off_id' => null,
            ]);
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

    public function refund(EmployeeM $employee, float $days, int $leaveRequestId): void
    {
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
}
