<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceRegularizationM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Services\HRMS\Payroll\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AttendanceRegularizationService
{
    public const TIMEZONE = 'Asia/Kolkata';

    public function __construct(
        private AttendanceRuleResolverService $ruleResolver,
        private AttendanceS $attendanceService
    ) {}

    /**
     * Inspects employee attendance record, status, leaves, holidays, week-offs, payroll,
     * and existing pending requests for a date, returning only valid regularization options.
     */
    public function getAvailableRegularizationTypes(Employee $employee, Carbon|string $date): array
    {
        $carbonDate = Carbon::parse($date, self::TIMEZONE);
        $dateStr = $carbonDate->toDateString();
        $month = (int) $carbonDate->format('m');
        $year = (int) $carbonDate->format('Y');

        // 1. Future Date Check
        if ($carbonDate->startOfDay()->gt(Carbon::now(self::TIMEZONE)->startOfDay())) {
            return [
                'success' => false,
                'can_regularize' => false,
                'code' => 'FUTURE_DATE',
                'attendance_status' => 'Future Date',
                'message' => 'Regularization is not allowed for future dates.',
                'available_options' => [],
            ];
        }

        // 2. Day Context Checks (Holiday, Week Off, Approved Leave)
        $dayContext = $this->ruleResolver->getDayContext($employee, $carbonDate);

        if ($dayContext['is_holiday']) {
            return [
                'success' => false,
                'can_regularize' => false,
                'code' => 'HOLIDAY',
                'attendance_status' => 'Holiday',
                'message' => 'Regularization is not allowed on Holidays.',
                'available_options' => [],
            ];
        }

        if ($dayContext['is_weekoff']) {
            return [
                'success' => false,
                'can_regularize' => false,
                'code' => 'WEEK_OFF',
                'attendance_status' => 'Week Off',
                'message' => 'Regularization is not allowed on Week Offs.',
                'available_options' => [],
            ];
        }

        if ($dayContext['is_on_leave']) {
            return [
                'success' => false,
                'can_regularize' => false,
                'code' => 'APPROVED_LEAVE',
                'attendance_status' => 'Approved Leave',
                'message' => 'Regularization is not allowed during Approved Leave.',
                'available_options' => [],
            ];
        }

        // Fetch attendance record if exists
        $attendance = Attendance::with('attendanceType')
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $dateStr)
            ->first();

        // 3. Payroll Lock Validation (DB-driven, actual payroll tables check)
        $isAttendancePayrollProcessed = Schema::hasColumn('attendances', 'payroll_processed') && (bool) ($attendance?->payroll_processed ?? false);

        $summaryLockedQuery = DB::table('monthly_attendance_summaries')
            ->where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->where(function ($q) {
                $q->where('is_locked', 1);
                if (Schema::hasColumn('monthly_attendance_summaries', 'payroll_processed')) {
                    $q->orWhere('payroll_processed', 1);
                }
            });

        $payrollProcessedQuery = false;
        if (Schema::hasTable('payrolls')) {
            $payrollProcessedQuery = DB::table('payrolls')
                ->where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->whereIn('status', ['processed', 'locked', 'finalized', 'paid', 'approved'])
                ->exists();
        }
        if (! $payrollProcessedQuery && Schema::hasTable('enterprise_payrolls')) {
            $payrollProcessedQuery = DB::table('enterprise_payrolls')
                ->where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->whereIn('status', ['processed', 'locked', 'finalized', 'paid', 'approved'])
                ->exists();
        }

        if ($isAttendancePayrollProcessed || $summaryLockedQuery->exists() || $payrollProcessedQuery) {
            $monthYearStr = $carbonDate->format('F Y');
            return [
                'success' => false,
                'can_regularize' => false,
                'code' => 'PAYROLL_LOCKED',
                'attendance_status' => 'Payroll Processed',
                'message' => "Regularization is not allowed because Payroll for {$monthYearStr} has already been processed.",
                'available_options' => [],
            ];
        }

        // 4. Pending Regularization Check (Rule 1)
        $attendanceId = $attendance?->id;
        $pendingExists = DB::table('attendance_regularizations')
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($dateStr, $attendanceId) {
                if ($attendanceId) {
                    $q->where('attendance_id', $attendanceId);
                }
                $q->orWhereDate('requested_punch_in', $dateStr)
                  ->orWhereDate('requested_punch_out', $dateStr)
                  ->orWhereDate('created_at', $dateStr);
            })
            ->exists();

        if ($pendingExists) {
            $formattedDateStr = $carbonDate->format('d-M-Y');
            return [
                'success' => false,
                'can_regularize' => false,
                'code' => 'PENDING_REQUEST',
                'attendance_status' => 'Pending HR Approval',
                'message' => "Regularization Request for {$formattedDateStr} has already been submitted and is currently Pending HR Approval.",
                'available_options' => [],
            ];
        }

        // 5. Approved Regularization Check (Rule 2)
        $approvedExists = DB::table('attendance_regularizations')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($dateStr, $attendanceId) {
                if ($attendanceId) {
                    $q->where('attendance_id', $attendanceId);
                }
                $q->orWhereDate('requested_punch_in', $dateStr)
                  ->orWhereDate('requested_punch_out', $dateStr);
            })
            ->exists() || ($attendance && $attendance->attendance_source === 'regularization');

        if ($approvedExists) {
            $formattedDateStr = $carbonDate->format('d-M-Y');
            return [
                'success' => false,
                'can_regularize' => false,
                'code' => 'ALREADY_APPROVED',
                'attendance_status' => 'Already Approved',
                'message' => "Attendance for {$formattedDateStr} has already been regularized and approved.",
                'available_options' => [],
            ];
        }

        // 5. Attendance State Matrix
        $hasIn = ! empty($attendance?->punch_in_time);
        $hasOut = ! empty($attendance?->punch_out_time);

        $availableOptions = [];
        $statusLabel = $attendance?->attendance_status
            ? ucfirst(str_replace('_', ' ', $attendance->attendance_status))
            : 'Absent';

        if (! $hasIn && ! $hasOut) {
            // Case 1: Absent (Punch In = NULL, Punch Out = NULL or record missing)
            $availableOptions[] = [
                'id' => 'regular_attendance',
                'value' => 'regular_attendance',
                'label' => 'Regular Attendance',
                'description' => 'Create full attendance record for absent day.',
            ];
            $statusLabel = 'Absent';
        } elseif ($hasIn && ! $hasOut) {
            // Case 2: Punch In exists, Punch Out NULL
            $availableOptions[] = [
                'id' => 'missed_punch_out',
                'value' => 'missed_punch_out',
                'label' => 'Missed Punch Out',
                'description' => 'Punch In recorded. Add missing Punch Out.',
            ];
            $statusLabel = 'Punch In Recorded';
        } elseif (! $hasIn && $hasOut) {
            // Case 3: Punch In NULL, Punch Out exists
            $availableOptions[] = [
                'id' => 'missed_punch_in',
                'value' => 'missed_punch_in',
                'label' => 'Missed Punch In',
                'description' => 'Punch Out recorded. Add missing Punch Out.',
            ];
            $statusLabel = 'Punch Out Recorded';
        } else {
            // Case 4: Punch In exists, Punch Out exists
            $availableOptions[] = [
                'id' => 'attendance_correction',
                'value' => 'attendance_correction',
                'label' => 'Attendance Correction',
                'description' => 'Correct existing punch timings.',
            ];
            $statusLabel = $attendance?->attendance_status ? ucfirst(str_replace('_', ' ', $attendance->attendance_status)) : 'Present';
        }

        return [
            'success' => true,
            'can_regularize' => true,
            'code' => null,
            'attendance_status' => $statusLabel,
            'message' => null,
            'warning' => null,
            'available_options' => $availableOptions,
            'attendance_record' => [
                'id' => $attendance?->id,
                'punch_in_time' => $attendance?->punch_in_time,
                'punch_out_time' => $attendance?->punch_out_time,
                'work_mode' => $attendance?->work_mode,
            ],
        ];
    }

    /**
     * Centralized HR Approval method that applies the regularization to attendance record,
     * clears all stale/blocked flags, recalculates working stats via AttendanceS,
     * rebuilds the monthly attendance summary, and syncs payroll.
     */
    public function applyApprovedRegularization(AttendanceRegularizationM|int $regularization, ?int $approvedByUserId = null): array
    {
        if (is_numeric($regularization)) {
            $regularization = AttendanceRegularizationM::findOrFail($regularization);
        }

        if ($regularization->status !== 'pending') {
            throw new \RuntimeException('Only pending requests can be approved.');
        }

        $employee = Employee::find($regularization->employee_id);
        if (! $employee) {
            throw new \RuntimeException('Employee profile not found.');
        }

        $attendanceService = $this->attendanceService;
        $attendance = $regularization->attendance_id
            ? Attendance::where('id', $regularization->attendance_id)->where('employee_id', $regularization->employee_id)->first()
            : null;

        if (! $attendance) {
            $date = $regularization->requested_punch_in
                ? Carbon::parse($regularization->requested_punch_in, self::TIMEZONE)->toDateString()
                : Carbon::parse($regularization->created_at, self::TIMEZONE)->toDateString();

            $attendance = Attendance::firstOrCreate(
                ['employee_id' => $regularization->employee_id, 'attendance_date' => $date],
                ['user_id' => $employee->user_id]
            );
        }

        $carbonAttDate = Carbon::parse($attendance->attendance_date, self::TIMEZONE);
        $month = (int) $carbonAttDate->format('m');
        $year = (int) $carbonAttDate->format('Y');

        // Check if Payroll is locked for this month
        $isPayrollLocked = (Schema::hasColumn('attendances', 'payroll_processed') && (bool) ($attendance->payroll_processed ?? false))
            || DB::table('monthly_attendance_summaries')
                ->where('employee_id', $regularization->employee_id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('is_locked', 1)
                ->exists();

        $type = $regularization->request_type;

        // Apply Punch Updates based on request_type
        if ($type === 'regular_attendance') {
            if ($regularization->requested_punch_in) {
                $attendance->punch_in_time = Carbon::parse($regularization->requested_punch_in, self::TIMEZONE)->format('H:i:s');
            }
            if ($regularization->requested_punch_out) {
                $attendance->punch_out_time = Carbon::parse($regularization->requested_punch_out, self::TIMEZONE)->format('H:i:s');
            }
            if (empty($attendance->work_mode)) {
                $attendance->work_mode = $employee->work_mode ?: 'wfo';
            }
        } elseif ($type === 'missed_punch_out') {
            if ($regularization->requested_punch_out) {
                $attendance->punch_out_time = Carbon::parse($regularization->requested_punch_out, self::TIMEZONE)->format('H:i:s');
            }
        } elseif ($type === 'missed_punch_in') {
            if ($regularization->requested_punch_in) {
                $attendance->punch_in_time = Carbon::parse($regularization->requested_punch_in, self::TIMEZONE)->format('H:i:s');
            }
        } else {
            // attendance_correction or legacy request types
            if ($regularization->requested_punch_in) {
                $attendance->punch_in_time = Carbon::parse($regularization->requested_punch_in, self::TIMEZONE)->format('H:i:s');
            }
            if ($regularization->requested_punch_out) {
                $attendance->punch_out_time = Carbon::parse($regularization->requested_punch_out, self::TIMEZONE)->format('H:i:s');
            }
        }

        $attendance->attendance_source = 'regularization';

        $attDateStr = $carbonAttDate->toDateString();

        // Step 2: Clear temporary flags only (do NOT manually force attendance_status, attendance_type_id, is_half_day, or is_lwp)
        $attendance->missed_punch = false;
        $attendance->is_missed_punch = false;
        $attendance->is_blocked = false;
        $attendance->is_punch_blocked = false;
        $attendance->missed_punch_reason = null;
        $attendance->pending_hr_reason = null;
        $attendance->blocked_reason = null;
        $attendance->block_reason = null;
        $attendance->is_locked = false;

        // Step 3: Delete stale violations belonging strictly to this attendance record
        if (Schema::hasTable('attendance_violations')) {
            DB::table('attendance_violations')
                ->where(function ($q) use ($attendance, $attDateStr) {
                    $q->where('attendance_id', $attendance->id)
                      ->orWhere(function ($sub) use ($attendance, $attDateStr) {
                          $sub->where('employee_id', $attendance->employee_id)
                              ->whereDate('violation_date', $attDateStr);
                      });
                })
                ->whereIn('type', ['late_login', 'early_logout', 'missed_punch'])
                ->delete();
        }

        // Step 4: Recalculate Late Login using approved punch in against shift policy
        $shift = null;
        if ($attendance->attendance_time_id) {
            $shift = $this->ruleResolver->getPolicyFromAttendanceTimeId($attendance->attendance_time_id, $employee, $attDateStr);
        }
        if (! $shift) {
            $shift = $this->ruleResolver->getPolicyForEmployee($employee, $attDateStr);
        }
        if ($attendance->punch_in_time) {
            $punchInDate = Carbon::parse($attDateStr . ' ' . $this->ruleResolver->timeString($attendance->punch_in_time), self::TIMEZONE);
            $isLate = ! $attendance->is_late_exempted && $shift && $shift->late_after_time && $punchInDate->gt(Carbon::parse($attDateStr . ' ' . $shift->late_after_time, self::TIMEZONE));
            $attendance->is_late = $isLate;
            if ($isLate && $shift?->late_after_time) {
                $lateAfter = Carbon::parse($attDateStr . ' ' . $shift->late_after_time, self::TIMEZONE);
                $attendance->late_minutes = $lateAfter->diffInMinutes($punchInDate);
            } else {
                $attendance->late_minutes = 0;
            }
        }

        // Step 5: Recalculate Early Logout using approved punch out against shift policy
        if ($attendance->punch_out_time) {
            $targetStr = $attendance->target_punch_out_time
                ?: ($shift?->punch_out_time ?? '18:00:00');
            $punchOutDate = Carbon::parse($attDateStr . ' ' . $this->ruleResolver->timeString($attendance->punch_out_time), self::TIMEZONE);
            $targetDate = Carbon::parse($attDateStr . ' ' . $this->ruleResolver->timeString($targetStr), self::TIMEZONE);

            if ($targetDate->lt($punchInDate ?? $targetDate)) {
                $targetDate->addDay();
            }
            if ($punchOutDate->lt($punchInDate ?? $punchOutDate)) {
                $punchOutDate->addDay();
            }

            $isEarly = $punchOutDate->lt($targetDate);
            $attendance->is_early_out = $isEarly;
            $attendance->early_out_minutes = $isEarly ? $punchOutDate->diffInMinutes($targetDate) : 0;
        }

        $attendance->save();

        // Step 6: Execute AttendanceS::calculateAttendanceStats() and rebuild violation cycles
        $attendanceService->calculateAttendanceStats($attendance);
        $attendanceService->rebuildEmployeeViolationCycles($employee->id, $attDateStr);

        // 4. Rebuild Monthly Attendance Summary
        $summaryService = app(PayrollAttendanceSummaryService::class);
        $summaryService->generateForEmployee($employee, $month, $year);

        // 5. Synchronize Payroll if unlocked
        if (! $isPayrollLocked) {
            try {
                if (class_exists(PayrollCalculationService::class)) {
                    app(PayrollCalculationService::class)->generateMonth($month, $year, $employee->id, $approvedByUserId);
                }
            } catch (\Throwable $e) {
                // Ignore payroll generation errors if salary structure is missing
            }
        }

        // 6. Update regularization record
        $regularization->update([
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'approved_by_user_id' => $approvedByUserId ?: auth()->id(),
            'approved_at' => now(),
        ]);

        $warning = $isPayrollLocked
            ? 'Attendance corrected successfully. Payroll has already been processed for this month; please regenerate payroll manually if required.'
            : null;

        $message = $warning
            ?: 'Regularization approved and attendance recalculated successfully.';

        return [
            'success' => true,
            'message' => $message,
            'warning' => $warning,
            'attendance' => $attendance->fresh(),
            'regularization' => $regularization->fresh(),
        ];
    }

    /**
     * Centralized validation for regularization punch in and punch out times.
     * Evaluates shift policy, overnight shift context, and sequence constraints.
     * Throws ValidationException if Punch Out is not strictly later than Punch In.
     *
     * @throws ValidationException
     */
    public function validateRegularizationTimes(
        Employee $employee,
        Carbon|string $attendanceDate,
        string $requestType,
        ?string $requestedPunchIn,
        ?string $requestedPunchOut,
        ?string $existingPunchIn = null,
        ?string $existingPunchOut = null
    ): void {
        $dateStr = Carbon::parse($attendanceDate, self::TIMEZONE)->toDateString();

        // 1. Resolve dynamic shift policy for employee and date
        $attendance = \App\Models\HRMS\Attendance\AttendanceM::where('employee_id', $employee->id)->whereDate('attendance_date', $dateStr)->first();
        $shift = null;
        if ($attendance && $attendance->attendance_time_id) {
            $shift = $this->ruleResolver->getPolicyFromAttendanceTimeId($attendance->attendance_time_id, $employee, $dateStr);
        }
        if (! $shift) {
            $shift = $this->ruleResolver->getPolicyForEmployee($employee, $dateStr);
        }

        // 2. Determine effective punch in and punch out times based on request_type
        $finalPunchInStr = null;
        $finalPunchOutStr = null;

        if (in_array($requestType, ['regular_attendance', 'wrong_punch_time', 'attendance_correction', 'punch_time_correction'], true)) {
            $finalPunchInStr = $requestedPunchIn;
            $finalPunchOutStr = $requestedPunchOut;

            if (empty($finalPunchInStr)) {
                $exception = ValidationException::withMessages([
                    'requested_punch_in' => ['Requested punch in time is required.'],
                    'punch_in_time' => ['Requested punch in time is required.'],
                ]);
                throw $exception;
            }
            if (empty($finalPunchOutStr)) {
                $exception = ValidationException::withMessages([
                    'requested_punch_out' => ['Requested punch out time is required.'],
                    'punch_out_time' => ['Requested punch out time is required.'],
                ]);
                throw $exception;
            }
        } elseif ($requestType === 'missed_punch_in') {
            $finalPunchInStr = $requestedPunchIn;
            $finalPunchOutStr = $existingPunchOut;

            if (empty($finalPunchInStr)) {
                $exception = ValidationException::withMessages([
                    'requested_punch_in' => ['Requested punch in time is required for missed punch in.'],
                    'punch_in_time' => ['Requested punch in time is required.'],
                ]);
                throw $exception;
            }
        } elseif ($requestType === 'missed_punch_out') {
            $finalPunchInStr = $existingPunchIn;
            $finalPunchOutStr = $requestedPunchOut;

            if (empty($finalPunchOutStr)) {
                $exception = ValidationException::withMessages([
                    'requested_punch_out' => ['Requested punch out time is required for missed punch out.'],
                    'punch_out_time' => ['Requested punch out time is required.'],
                ]);
                throw $exception;
            }
        } else {
            // Other request types (e.g. late_mark_exemption, early_logout_correction, geofence_issue, system_error, other)
            $finalPunchInStr = ! empty($requestedPunchIn) ? $requestedPunchIn : $existingPunchIn;
            $finalPunchOutStr = ! empty($requestedPunchOut) ? $requestedPunchOut : $existingPunchOut;
        }

        // If either punch is missing after fallback for types where one or both might not be set, skip order check
        if (empty($finalPunchInStr) || empty($finalPunchOutStr)) {
            return;
        }

        // 3. Extract time component (HH:mm:ss or HH:mm)
        $inTimeOnly = Carbon::parse($finalPunchInStr, self::TIMEZONE)->format('H:i:s');
        $outTimeOnly = Carbon::parse($finalPunchOutStr, self::TIMEZONE)->format('H:i:s');

        // 4. Resolve Overnight Shift Status
        $isOvernight = false;
        if ($shift && ! empty($shift->shift_start_time) && ! empty($shift->shift_end_time)) {
            $shiftStartCarbon = Carbon::parse($dateStr . ' ' . $this->ruleResolver->timeString($shift->shift_start_time), self::TIMEZONE);
            $shiftEndCarbon = Carbon::parse($dateStr . ' ' . $this->ruleResolver->timeString($shift->shift_end_time), self::TIMEZONE);
            if ($shiftEndCarbon->lt($shiftStartCarbon)) {
                $isOvernight = true;
            }
        }

        // 5. Construct full Carbon datetime objects
        $inDateTime = Carbon::parse($dateStr . ' ' . $inTimeOnly, self::TIMEZONE);
        $outDateTime = Carbon::parse($dateStr . ' ' . $outTimeOnly, self::TIMEZONE);

        if ($isOvernight) {
            // In an overnight shift, if punch out time is earlier or equal to punch in on same day, treat punch out as next calendar day
            if ($outDateTime->lte($inDateTime)) {
                $outDateTime->addDay();
            }
        }

        // 6. Validate Punch Out > Punch In
        if ($outDateTime->lte($inDateTime)) {
            $errorMessage = 'Please select the correct Punch Out time. Punch Out must be later than Punch In.';
            $validationErrors = [
                'punch_out_time' => ['Please select the correct Punch Out time.'],
                'requested_punch_out' => ['Please select the correct Punch Out time.'],
            ];

            $exception = ValidationException::withMessages($validationErrors);
            $property = new \ReflectionProperty(ValidationException::class, 'message');
            $property->setAccessible(true);
            $property->setValue($exception, $errorMessage);

            throw $exception;
        }
    }
}

