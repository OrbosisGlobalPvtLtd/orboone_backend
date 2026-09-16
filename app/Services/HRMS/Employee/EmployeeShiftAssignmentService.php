<?php

namespace App\Services\HRMS\Employee;

use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Employee\EmployeeShiftTimingM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeShiftAssignmentService
{
    /**
     * Atomically assign a shift to an employee with concurrency protection and business date rules.
     *
     * @param int $employeeId
     * @param array $shiftData
     * @param int|null $actorUserId
     * @return array
     */
    public function assignShift(int $employeeId, array $shiftData, ?int $actorUserId = null): array
    {
        $actorUserId = $actorUserId ?: (Auth::id() ?: 1);

        return DB::transaction(function () use ($employeeId, $shiftData, $actorUserId) {
            // Lock all existing shift timing records for this employee to prevent concurrency race conditions
            $existingTimings = EmployeeShiftTimingM::where('employee_id', $employeeId)
                ->lockForUpdate()
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->get();

            $activeTiming = $existingTimings->firstWhere('is_active', 1);

            $attendanceTimeId = (int) ($shiftData['attendance_time_id'] ?? 0);
            $shiftTemplate = AttendanceTimeM::find($attendanceTimeId);

            if (!$shiftTemplate && $attendanceTimeId > 0) {
                $shiftTemplate = DB::table('attendance_times')->where('id', $attendanceTimeId)->first();
            }

            $isFlexible = ($shiftData['work_schedule_type'] ?? '') === 'flexible_part_time';
            $isDynamicHours = false;
            if ($shiftTemplate) {
                $shiftType = strtolower($shiftTemplate->shift_type ?? '');
                $isDynamicHours = ($shiftType === 'dynamic_hours') || (($shiftData['work_schedule_type'] ?? '') === 'dynamic_hours');
                if ($shiftType === 'flexible_part_time') {
                    $isFlexible = true;
                }
            }

            // Resolve timing overrides
            if ($isDynamicHours) {
                $punchAllowed = null;
                $shiftStart   = null;
                $lateAfter    = null;
                $halfDayAfter = null;
                $blockAfter   = null;
                $shiftEnd     = null;
                $reqMinutes   = isset($shiftData['required_work_minutes']) && $shiftData['required_work_minutes'] !== null
                    ? (int) $shiftData['required_work_minutes']
                    : ($shiftTemplate->required_work_minutes ?? null);
                $lunchMinutes = isset($shiftData['lunch_minutes']) && $shiftData['lunch_minutes'] !== null
                    ? (int) $shiftData['lunch_minutes']
                    : ($shiftTemplate->lunch_break_minutes ?? 0);
            } else {
                $punchAllowed = $shiftData['punch_allowed_from'] ?? ($shiftTemplate->punch_allowed_from ?? null);
                $shiftStart   = $shiftData['shift_start_time'] ?? ($shiftTemplate->shift_start_time ?? null);
                $lateAfter    = $shiftData['late_after_time'] ?? ($shiftTemplate->late_after_time ?? null);
                $halfDayAfter = $shiftData['half_day_after_time'] ?? ($shiftTemplate->half_day_after_time ?? null);
                $blockAfter   = $shiftData['block_after_time'] ?? ($shiftTemplate->block_after_time ?? ($shiftTemplate->half_day_after_time ?? ($shiftTemplate->shift_end_time ?? null)));
                $shiftEnd     = $shiftData['shift_end_time'] ?? ($shiftTemplate->shift_end_time ?? null);
                $reqMinutes   = isset($shiftData['required_work_minutes']) && $shiftData['required_work_minutes'] !== null
                    ? (int) $shiftData['required_work_minutes']
                    : ($shiftTemplate->required_work_minutes ?? null);
                $lunchMinutes = isset($shiftData['lunch_minutes']) && $shiftData['lunch_minutes'] !== null
                    ? (int) $shiftData['lunch_minutes']
                    : ($shiftTemplate->lunch_break_minutes ?? 0);
            }

            // Standardize format for comparison
            $punchAllowed = $punchAllowed ? Carbon::parse($punchAllowed)->format('H:i:s') : null;
            $shiftStart   = $shiftStart ? Carbon::parse($shiftStart)->format('H:i:s') : null;
            $lateAfter    = $lateAfter ? Carbon::parse($lateAfter)->format('H:i:s') : null;
            $halfDayAfter = $halfDayAfter ? Carbon::parse($halfDayAfter)->format('H:i:s') : null;
            $blockAfter   = $blockAfter ? Carbon::parse($blockAfter)->format('H:i:s') : null;
            $shiftEnd     = $shiftEnd ? Carbon::parse($shiftEnd)->format('H:i:s') : null;

            // Target effective date
            $targetEffectiveFrom = !empty($shiftData['effective_from'])
                ? Carbon::parse($shiftData['effective_from'])->toDateString()
                : Carbon::now('Asia/Kolkata')->toDateString();

            // Check if identical to current active assignment (no-op)
            if ($activeTiming) {
                $activePunchAllowed = $activeTiming->punch_allowed_from ? Carbon::parse($activeTiming->punch_allowed_from)->format('H:i:s') : null;
                $activeShiftStart   = $activeTiming->shift_start_time ? Carbon::parse($activeTiming->shift_start_time)->format('H:i:s') : null;
                $activeLateAfter    = $activeTiming->late_after_time ? Carbon::parse($activeTiming->late_after_time)->format('H:i:s') : null;
                $activeHalfDayAfter = $activeTiming->half_day_after_time ? Carbon::parse($activeTiming->half_day_after_time)->format('H:i:s') : null;
                $activeBlockAfter   = $activeTiming->block_after_time ? Carbon::parse($activeTiming->block_after_time)->format('H:i:s') : null;
                $activeShiftEnd     = $activeTiming->shift_end_time ? Carbon::parse($activeTiming->shift_end_time)->format('H:i:s') : null;

                $isIdentical = ((int) $activeTiming->attendance_time_id === $attendanceTimeId)
                    && ($activePunchAllowed === $punchAllowed)
                    && ($activeShiftStart === $shiftStart)
                    && ($activeLateAfter === $lateAfter)
                    && ($activeHalfDayAfter === $halfDayAfter)
                    && ($activeBlockAfter === $blockAfter)
                    && ($activeShiftEnd === $shiftEnd)
                    && ((int) $activeTiming->required_work_minutes === (int) $reqMinutes)
                    && ((int) $activeTiming->lunch_minutes === (int) $lunchMinutes);

                if ($isIdentical && $activeTiming->is_active) {
                    // Make sure no other active rows exist
                    EmployeeShiftTimingM::where('employee_id', $employeeId)
                        ->where('id', '!=', $activeTiming->id)
                        ->where('is_active', 1)
                        ->update(['is_active' => 0, 'updated_at' => now()]);

                    return [
                        'success' => true,
                        'is_identical' => true,
                        'is_delayed_to_tomorrow' => false,
                        'shift_timing' => $activeTiming,
                        'message' => 'Shift configuration is already active for this employee.',
                    ];
                }

                // Flexible -> Flexible same-day in-place update
                $oldShiftType = DB::table('attendance_times')->where('id', $activeTiming->attendance_time_id)->value('shift_type');
                $isFlexibleOld = ($oldShiftType === 'flexible_part_time');
                $isFlexibleNew = $isFlexible;

                if ($isFlexibleOld && $isFlexibleNew && $activeTiming->effective_from && Carbon::parse($activeTiming->effective_from)->eq(Carbon::parse($targetEffectiveFrom))) {
                    $activeTiming->update([
                        'punch_allowed_from' => $punchAllowed,
                        'shift_start_time' => $shiftStart,
                        'late_after_time' => $lateAfter,
                        'half_day_after_time' => $halfDayAfter,
                        'block_after_time' => $blockAfter,
                        'shift_end_time' => $shiftEnd,
                        'required_work_minutes' => $reqMinutes,
                        'lunch_minutes' => $lunchMinutes,
                        'updated_by' => $actorUserId,
                    ]);

                    // Deactivate any other active rows
                    EmployeeShiftTimingM::where('employee_id', $employeeId)
                        ->where('id', '!=', $activeTiming->id)
                        ->where('is_active', 1)
                        ->update(['is_active' => 0, 'updated_at' => now()]);

                    return [
                        'success' => true,
                        'is_identical' => false,
                        'is_delayed_to_tomorrow' => false,
                        'shift_timing' => $activeTiming->fresh(),
                        'message' => 'Flexible shift timing updated.',
                    ];
                }
            }

            // Punch check for target date
            $hasPunchedIn = DB::table('attendances')
                ->where('employee_id', $employeeId)
                ->whereDate('attendance_date', $targetEffectiveFrom)
                ->whereNotNull('punch_in_time')
                ->exists();

            if ($hasPunchedIn) {
                $newEffectiveFrom = Carbon::parse($targetEffectiveFrom)->addDay()->toDateString();

                if ($activeTiming) {
                    if ($activeTiming->effective_from && Carbon::parse($activeTiming->effective_from)->gt(Carbon::parse($targetEffectiveFrom))) {
                        EmployeeShiftTimingM::where('id', $activeTiming->id)->delete();
                    } else {
                        EmployeeShiftTimingM::where('id', $activeTiming->id)->update([
                            'is_active' => 0,
                            'effective_to' => $targetEffectiveFrom,
                            'updated_by' => $actorUserId,
                            'updated_at' => now(),
                        ]);
                    }
                }

                $delayedMessage = "The employee has already punched in today. The current day's attendance will remain on the existing shift. The new shift will automatically become effective from tomorrow.";
            } else {
                $newEffectiveFrom = $targetEffectiveFrom;
                $yesterday = Carbon::parse($targetEffectiveFrom)->subDay()->toDateString();

                if ($activeTiming) {
                    if ($activeTiming->effective_from && Carbon::parse($activeTiming->effective_from)->gte(Carbon::parse($newEffectiveFrom))) {
                        EmployeeShiftTimingM::where('id', $activeTiming->id)->delete();
                    } else {
                        EmployeeShiftTimingM::where('id', $activeTiming->id)->update([
                            'is_active' => 0,
                            'effective_to' => $yesterday,
                            'updated_by' => $actorUserId,
                            'updated_at' => now(),
                        ]);
                    }
                }

                $delayedMessage = null;
            }

            // Deactivate ALL existing active records for this employee to strictly enforce 1 active assignment
            EmployeeShiftTimingM::where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->update(['is_active' => 0, 'updated_by' => $actorUserId, 'updated_at' => now()]);

            // Create new active shift timing record
            $newShiftRecord = EmployeeShiftTimingM::create([
                'employee_id' => $employeeId,
                'attendance_time_id' => $attendanceTimeId,
                'attendance_policy_rule_id' => $shiftData['attendance_policy_rule_id'] ?? ($shiftData['attendance_policy_id'] ?? DB::table('employees_new')->where('id', $employeeId)->value('attendance_policy_rule_id')),
                'punch_allowed_from' => $punchAllowed,
                'shift_start_time' => $shiftStart,
                'late_after_time' => $lateAfter,
                'half_day_after_time' => $halfDayAfter,
                'block_after_time' => $blockAfter,
                'shift_end_time' => $shiftEnd,
                'required_work_minutes' => $reqMinutes,
                'lunch_minutes' => $lunchMinutes,
                'effective_from' => $newEffectiveFrom,
                'effective_to' => $shiftData['effective_to'] ?? null,
                'is_active' => $shiftData['is_active'] ?? 1,
                'created_by' => $actorUserId,
            ]);

            return [
                'success' => true,
                'is_identical' => false,
                'is_delayed_to_tomorrow' => $hasPunchedIn,
                'shift_timing' => $newShiftRecord,
                'warning' => $delayedMessage,
                'message' => 'Shift assigned successfully.',
            ];
        });
    }

    /**
     * Update an existing shift assignment record with concurrency and uniqueness protection.
     *
     * @param int $assignmentId
     * @param array $data
     * @param int|null $actorUserId
     * @return EmployeeShiftTimingM
     */
    public function updateShiftAssignment(int $assignmentId, array $data, ?int $actorUserId = null): EmployeeShiftTimingM
    {
        $actorUserId = $actorUserId ?: (Auth::id() ?: 1);

        return DB::transaction(function () use ($assignmentId, $data, $actorUserId) {
            $assignment = EmployeeShiftTimingM::where('id', $assignmentId)->lockForUpdate()->firstOrFail();
            $isActive = !empty($data['is_active']);

            if ($isActive) {
                // Lock other records and deactivate them
                EmployeeShiftTimingM::where('employee_id', $assignment->employee_id)
                    ->where('id', '!=', $assignment->id)
                    ->lockForUpdate()
                    ->where('is_active', 1)
                    ->update(['is_active' => 0, 'updated_by' => $actorUserId, 'updated_at' => now()]);
            }

            $shiftTime = AttendanceTimeM::find($data['attendance_time_id'] ?? $assignment->attendance_time_id);

            if ($shiftTime && $shiftTime->isDynamicHours()) {
                $punchAllowedFrom = null;
                $shiftStartTime   = null;
                $lateAfterTime    = null;
                $blockAfterTime   = null;
                $halfDayAfterTime = null;
                $shiftEndTime     = null;
                $requiredMinutes  = isset($data['required_work_minutes']) && $data['required_work_minutes'] !== null
                    ? (int) $data['required_work_minutes']
                    : $shiftTime->required_work_minutes;
                $lunchMinutes     = isset($data['lunch_minutes']) && $data['lunch_minutes'] !== null
                    ? (int) $data['lunch_minutes']
                    : ($shiftTime->lunch_break_minutes ?? 0);
            } else {
                $punchAllowedFrom = array_key_exists('punch_allowed_from', $data) && $data['punch_allowed_from'] !== null ? $data['punch_allowed_from'] : ($shiftTime->punch_allowed_from ?? $assignment->punch_allowed_from);
                $shiftStartTime   = array_key_exists('shift_start_time', $data) && $data['shift_start_time'] !== null ? $data['shift_start_time'] : ($shiftTime->shift_start_time ?? $assignment->shift_start_time);
                $lateAfterTime    = array_key_exists('late_after_time', $data) && $data['late_after_time'] !== null ? $data['late_after_time'] : ($shiftTime->late_after_time ?? $assignment->late_after_time);
                $blockAfterTime   = array_key_exists('block_after_time', $data) && $data['block_after_time'] !== null ? $data['block_after_time'] : ($shiftTime->block_after_time ?? ($shiftTime->half_day_after_time ?? ($shiftTime->shift_end_time ?? $assignment->block_after_time)));
                $halfDayAfterTime = array_key_exists('half_day_after_time', $data) && $data['half_day_after_time'] !== null ? $data['half_day_after_time'] : ($shiftTime->half_day_after_time ?? $assignment->half_day_after_time);
                $shiftEndTime     = array_key_exists('shift_end_time', $data) && $data['shift_end_time'] !== null ? $data['shift_end_time'] : ($shiftTime->shift_end_time ?? $assignment->shift_end_time);
                $requiredMinutes  = isset($data['required_work_minutes']) && $data['required_work_minutes'] !== null ? (int) $data['required_work_minutes'] : ($shiftTime->required_work_minutes ?? $assignment->required_work_minutes);
                $lunchMinutes     = isset($data['lunch_minutes']) && $data['lunch_minutes'] !== null ? (int) $data['lunch_minutes'] : ($shiftTime->lunch_break_minutes ?? $assignment->lunch_minutes);
            }

            $assignment->update([
                'attendance_time_id' => $data['attendance_time_id'] ?? $assignment->attendance_time_id,
                'punch_allowed_from' => $punchAllowedFrom ? Carbon::parse($punchAllowedFrom)->format('H:i:s') : null,
                'shift_start_time' => $shiftStartTime ? Carbon::parse($shiftStartTime)->format('H:i:s') : null,
                'late_after_time' => $lateAfterTime ? Carbon::parse($lateAfterTime)->format('H:i:s') : null,
                'block_after_time' => $blockAfterTime ? Carbon::parse($blockAfterTime)->format('H:i:s') : null,
                'half_day_after_time' => $halfDayAfterTime ? Carbon::parse($halfDayAfterTime)->format('H:i:s') : null,
                'shift_end_time' => $shiftEndTime ? Carbon::parse($shiftEndTime)->format('H:i:s') : null,
                'required_work_minutes' => $requiredMinutes,
                'lunch_minutes' => $lunchMinutes,
                'effective_from' => $data['effective_from'] ?? $assignment->effective_from,
                'effective_to' => array_key_exists('effective_to', $data) ? $data['effective_to'] : $assignment->effective_to,
                'is_active' => $isActive,
                'updated_by' => $actorUserId,
            ]);

            return $assignment->fresh();
        });
    }
}
