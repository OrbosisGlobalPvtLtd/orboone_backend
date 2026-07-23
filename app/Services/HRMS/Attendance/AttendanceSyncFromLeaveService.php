<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceDailyStatusLogM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceSyncFromLeaveService
{
    public function syncApprovedLeave(LeaveRequestM $leaveRequest, ?int $userId = null): void
    {
        $leaveTypeId = AttendanceTypeM::where('code', 'leave')->value('id');
        $lwpTypeId = AttendanceTypeM::where('code', 'lwp')->value('id') ?: $leaveTypeId;

        foreach ($leaveRequest->dates()->where('deduct_as_leave', true)->get() as $dateRow) {
            try {
                $attendance = AttendanceM::firstOrNew([
                    'employee_id' => $leaveRequest->employee_id,
                    'attendance_date' => $dateRow->leave_date->toDateString(),
                ]);

                if ($attendance->exists && ($attendance->is_locked || $attendance->payroll_processed)) {
                    continue;
                }

                $oldStatus = $attendance->attendance_status;
                $dayValue = (float) $dateRow->paid_day + (float) $dateRow->sick_day + (float) $dateRow->comp_off_day + (float) $dateRow->lwp_day;

                $attendance->fill([
                    'user_id' => $leaveRequest->user_id,
                    'employee_id' => $leaveRequest->employee_id,
                    'attendance_type_id' => $leaveTypeId,
                    'leave_request_id' => $leaveRequest->id,
                    'attendance_date' => $dateRow->leave_date->toDateString(),
                    'attendance_status' => 'leave',
                    'attendance_source' => 'leave_auto',
                    'is_lwp' => false,
                    'lwp_reason' => null,
                    'is_half_day' => $dayValue > 0 && $dayValue < 1,
                    'half_day_reason' => $dayValue > 0 && $dayValue < 1 ? 'Half-day leave' : null,
                    'total_work_minutes' => 0,
                    'gross_work_minutes' => 0,
                    'is_late' => false,
                    'late_minutes' => 0,
                    'is_early_out' => false,
                    'early_out_minutes' => 0,
                    'missed_punch' => false,
                    'is_missed_punch' => false,
                    'is_punch_blocked' => false,
                    'is_blocked' => false,
                ]);

                $attendance->save();

                AttendanceDailyStatusLogM::create([
                    'employee_id' => $leaveRequest->employee_id,
                    'attendance_id' => $attendance->id,
                    'status_date' => $dateRow->leave_date->toDateString(),
                    'old_status' => $oldStatus,
                    'new_status' => $attendance->attendance_status,
                    'source' => 'leave_auto',
                    'remarks' => 'Synced from approved leave request #' . $leaveRequest->id,
                    'created_by_user_id' => $userId,
                ]);
            } catch (\Throwable $e) {
                Log::error('Leave attendance sync failed', [
                    'leave_request_id' => $leaveRequest->id,
                    'date' => $dateRow->leave_date,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        $leaveRequest->forceFill(['attendance_synced' => true])->save();
    }

    public function reverseLeaveSync(LeaveRequestM $leaveRequest, ?int $userId = null): void
    {
        $attendances = AttendanceM::where('leave_request_id', $leaveRequest->id)->get();

        foreach ($attendances as $attendance) {
            if ($attendance->is_locked || $attendance->payroll_processed) {
                throw new \RuntimeException('Attendance is locked or payroll processed for ' . Carbon::parse($attendance->attendance_date)->toDateString());
            }

            $oldStatus = $attendance->attendance_status;
            $attendance->leave_request_id = null;
            $attendance->attendance_type_id = null;
            $attendance->attendance_status = 'pending';
            $attendance->attendance_source = 'leave_reversed';
            $attendance->is_lwp = false;
            $attendance->lwp_reason = null;
            $attendance->is_half_day = false;
            $attendance->half_day_reason = null;
            $attendance->save();

            AttendanceDailyStatusLogM::create([
                'employee_id' => $attendance->employee_id,
                'attendance_id' => $attendance->id,
                'status_date' => Carbon::parse($attendance->attendance_date)->toDateString(),
                'old_status' => $oldStatus,
                'new_status' => 'pending',
                'source' => 'leave_reversal',
                'remarks' => 'Leave sync reversed for request #' . $leaveRequest->id,
                'created_by_user_id' => $userId,
            ]);
        }

        $leaveRequest->forceFill(['attendance_synced' => false])->save();
    }
}
