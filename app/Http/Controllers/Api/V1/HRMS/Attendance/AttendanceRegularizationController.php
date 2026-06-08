<?php

namespace App\Http\Controllers\Api\V1\HRMS\Attendance;

use App\Http\Resources\HRMS\Attendance\AttendanceRegularizationResource;
use App\Http\Controllers\Api\V1\ApiController;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceRegularizationM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceRegularizationController extends ApiController
{
    private const REQUEST_TYPES = [
        'missed_punch_in',
        'missed_punch_out',
        'wrong_punch_time',
        'late_mark_exemption',
        'early_logout_correction',
        'geofence_issue',
        'system_error',
        'other',
    ];

    public function requestRegularization(Request $request)
    {
        $employee = EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Employee profile not found.', 'data' => null], 404);
        }

        $data = $request->validate([
            'attendance_id' => 'nullable|exists:attendances,id',
            'attendance_date' => 'nullable|date',
            'request_type' => 'required|string|in:' . implode(',', self::REQUEST_TYPES),
            'requested_punch_in' => 'nullable|date',
            'requested_punch_out' => 'nullable|date',
            'requested_punch_in_time' => 'nullable|string',
            'requested_punch_out_time' => 'nullable|string',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $attendance = null;
        if (! empty($data['attendance_id'])) {
            $attendance = Attendance::where('id', $data['attendance_id'])
                ->where('employee_id', $employee->id)
                ->first();
            if (! $attendance) {
                return response()->json(['success' => false, 'status' => false, 'message' => 'Attendance record not found for employee.', 'data' => null], 422);
            }
        }

        $attendanceDate = $data['attendance_date'] ?? ($attendance?->attendance_date ? Carbon::parse($attendance->attendance_date)->toDateString() : null);
        if ($attendanceDate && Carbon::parse($attendanceDate)->isFuture()) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Future attendance date is not allowed.', 'data' => null], 422);
        }

        if (! $attendance && $attendanceDate) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $attendanceDate)
                ->first();
        }

        if ($attendanceDate && empty($data['requested_punch_in']) && ! empty($data['requested_punch_in_time'])) {
            $data['requested_punch_in'] = Carbon::parse($attendanceDate . ' ' . $data['requested_punch_in_time'], 'Asia/Kolkata')->toDateTimeString();
        }
        if ($attendanceDate && empty($data['requested_punch_out']) && ! empty($data['requested_punch_out_time'])) {
            $data['requested_punch_out'] = Carbon::parse($attendanceDate . ' ' . $data['requested_punch_out_time'], 'Asia/Kolkata')->toDateTimeString();
        }

        if ($data['request_type'] === 'missed_punch_in' && empty($data['requested_punch_in'])) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Requested punch in time is required for missed punch in.', 'data' => null], 422);
        }

        if ($data['request_type'] === 'missed_punch_out' && empty($data['requested_punch_out'])) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Requested punch out time is required for missed punch out.', 'data' => null], 422);
        }

        if (in_array($data['request_type'], ['wrong_punch_time', 'early_logout_correction'], true) && empty($data['requested_punch_out']) && empty($data['requested_punch_in'])) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Requested punch in/out time is required for this request type.', 'data' => null], 422);
        }

        $pendingExists = AttendanceRegularizationM::where('employee_id', $employee->id)
            ->where('request_type', $data['request_type'])
            ->where('status', 'pending')
            ->when($attendanceDate, function ($q) use ($attendanceDate) {
                $q->where(function ($sq) use ($attendanceDate) {
                    $sq->whereHas('attendance', fn ($a) => $a->whereDate('attendance_date', $attendanceDate))
                        ->orWhereDate('requested_punch_in', $attendanceDate)
                        ->orWhereDate('requested_punch_out', $attendanceDate);
                });
            })
            ->exists();
        if ($pendingExists) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Duplicate pending regularization request exists for same date and type.', 'data' => null], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attendance/regularizations', 'public');
        }

        $payload = [
            'employee_id' => $employee->id,
            'attendance_id' => $attendance?->id,
            'request_type' => $data['request_type'],
            'existing_punch_in' => $attendance?->punch_in_time,
            'existing_punch_out' => $attendance?->punch_out_time,
            'requested_punch_in' => $data['requested_punch_in'] ?? null,
            'requested_punch_out' => $data['requested_punch_out'] ?? null,
            'reason' => $data['reason'],
            'status' => 'pending',
        ];

        if (Schema::hasColumn('attendance_regularizations', 'attachment_path')) {
            $payload['attachment_path'] = $attachmentPath;
        }

        $regularization = AttendanceRegularizationM::create($payload);
        app(NotificationS::class)->notifyHrAndSuperAdmin(
            'Attendance Regularization Request',
            'Regularization request submitted by ' . ($employee->full_name ?? $employee->name ?? 'Employee'),
            'attendance_regularization_submitted',
            'hrms.attendance.regularizations.index',
            [],
            [
                'employee_id' => $employee->id,
                'regularization_id' => $regularization->id,
                'target_date' => $attendanceDate,
            ]
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Regularization request created successfully.',
            'data' => new AttendanceRegularizationResource($regularization),
        ], 201);
    }

    public function myRegularizationRequests()
    {
        $request = request();
        $employee = EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Employee profile not found.', 'data' => null], 404);
        }

        $rows = AttendanceRegularizationM::where('employee_id', $employee->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Regularization requests fetched successfully.',
            'data' => [
                'records' => AttendanceRegularizationResource::collection($rows->items()),
                'pagination' => [
                    'total' => $rows->total(),
                    'per_page' => $rows->perPage(),
                    'current_page' => $rows->currentPage(),
                    'last_page' => $rows->lastPage(),
                    'next_page_url' => $rows->nextPageUrl(),
                    'prev_page_url' => $rows->previousPageUrl(),
                ],
            ],
        ]);
    }

    public function showRegularizationRequest(int $id)
    {
        $employee = EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Employee profile not found.', 'data' => null], 404);
        }

        $row = AttendanceRegularizationM::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (! $row) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Regularization request not found.', 'data' => null], 404);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Regularization request fetched successfully.',
            'data' => new AttendanceRegularizationResource($row),
        ]);
    }

    public function cancelRegularizationRequest(int $id)
    {
        $employee = EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Employee profile not found.', 'data' => null], 404);
        }

        $row = AttendanceRegularizationM::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();
        if (! $row) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Regularization request not found.', 'data' => null], 404);
        }
        if ($row->status !== 'pending') {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Only pending requests can be cancelled.', 'data' => null], 422);
        }

        $row->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Regularization request cancelled successfully.',
            'data' => new AttendanceRegularizationResource($row->fresh()),
        ]);
    }

    public function approveRegularization($id, Request $request)
    {
        $attendanceService = app(AttendanceS::class);
        if (! $this->canApproveRegularization()) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Unauthorized.', 'data' => null], 403);
        }

        $request->validate([
            'approval_note' => 'nullable|string|max:2000',
        ]);

        $row = AttendanceRegularizationM::find($id);
        if (! $row) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Regularization request not found.', 'data' => null], 404);
        }

        if ($row->status !== 'pending') {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Only pending requests can be approved.', 'data' => null], 422);
        }

        try {
            DB::transaction(function () use ($row, $attendanceService, $request) {
            $attendance = $row->attendance_id
                ? Attendance::where('id', $row->attendance_id)->where('employee_id', $row->employee_id)->first()
                : null;

            if (! $attendance) {
                $date = $row->requested_punch_in
                    ? Carbon::parse($row->requested_punch_in, $attendanceService->attendanceTimezone())->toDateString()
                    : Carbon::parse($row->created_at, $attendanceService->attendanceTimezone())->toDateString();
                $attendance = Attendance::firstOrCreate(
                    ['employee_id' => $row->employee_id, 'attendance_date' => $date],
                    ['user_id' => optional(EmployeeM::find($row->employee_id))->user_id]
                );
            }

            if ($attendance->payroll_processed || $attendance->is_locked) {
                throw new \RuntimeException('Attendance is locked/payroll processed for this date.');
            }

            $summaryLocked = DB::table('monthly_attendance_summaries')
                ->where('employee_id', $row->employee_id)
                ->where('month', (int) Carbon::parse($attendance->attendance_date)->format('m'))
                ->where('year', (int) Carbon::parse($attendance->attendance_date)->format('Y'))
                ->where('is_locked', 1)
                ->exists();
            if ($summaryLocked) {
                throw new \RuntimeException('Attendance is locked/payroll processed for this date.');
            }

            if ($row->requested_punch_in) {
                $attendance->punch_in_time = Carbon::parse($row->requested_punch_in, $attendanceService->attendanceTimezone())->format('H:i:s');
            }
            if ($row->requested_punch_out) {
                $attendance->punch_out_time = Carbon::parse($row->requested_punch_out, $attendanceService->attendanceTimezone())->format('H:i:s');
            }
            $attendance->missed_punch = false;
            $attendance->is_missed_punch = false;
            $attendance->missed_punch_reason = null;
            $attendance->pending_hr_reason = null;
            $attendance->is_locked = false;
            $attendance->save();
            $attendanceService->calculateAttendanceStats($attendance);

            $row->update([
                'attendance_id' => $attendance->id,
                'status' => 'approved',
                'approved_by_user_id' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->input('approval_note'),
            ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'status' => false, 'message' => app(\App\Services\Shared\MobileApiMessageS::class)->friendly($e), 'data' => null], 422);
        }

        $fresh = $row->fresh();
        $employee = EmployeeM::find($fresh?->employee_id);
        if ($employee?->user_id) {
            app(NotificationS::class)->notifyEmployee(
                'Attendance Regularization Update',
                'Your regularization request has been approved.',
                'attendance_regularization_approved',
                'hrms.attendance.regularizations.index',
                [],
                ['regularization_id' => $fresh->id],
                (int) $employee->user_id
            );
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Regularization approved successfully.',
            'data' => new AttendanceRegularizationResource($row->fresh()),
        ]);
    }

    public function rejectRegularization(int $id, Request $request)
    {
        if (! $this->canApproveRegularization()) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Unauthorized.', 'data' => null], 403);
        }

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $row = AttendanceRegularizationM::find($id);
        if (! $row) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Regularization request not found.', 'data' => null], 404);
        }
        try {
            DB::transaction(function () use ($row, $data) {
                $attendanceService = app(AttendanceS::class);
                $attendance = $row->attendance_id
                    ? Attendance::where('id', $row->attendance_id)->where('employee_id', $row->employee_id)->first()
                    : null;

                if (!$attendance) {
                    $date = $row->requested_punch_in
                        ? Carbon::parse($row->requested_punch_in, $attendanceService->attendanceTimezone())->toDateString()
                        : Carbon::parse($row->created_at, $attendanceService->attendanceTimezone())->toDateString();
                    $attendance = Attendance::firstOrCreate(
                        ['employee_id' => $row->employee_id, 'attendance_date' => $date],
                        ['user_id' => optional(EmployeeM::find($row->employee_id))->user_id]
                    );
                }

                if ($attendance && !$attendance->payroll_processed && !$attendance->is_locked) {
                    $lwpType = $attendanceService->attendanceType('lwp');
                    $attendance->attendance_status = 'lwp';
                    if ($lwpType) {
                        $attendance->attendance_type_id = $lwpType->id;
                    }
                    $attendance->is_lwp = true;
                    $attendance->lwp_reason = 'Missed punch regularization rejected';
                    $attendance->remarks = 'Missed punch regularization rejected';
                    $attendance->save();

                    $attendanceService->syncAttendanceViolations($attendance);
                }

                $row->update([
                    'status' => 'rejected',
                    'approved_by_user_id' => auth()->id(),
                    'approved_at' => now(),
                    'rejection_reason' => $data['rejection_reason'],
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'status' => false, 'message' => app(\App\Services\Shared\MobileApiMessageS::class)->friendly($e), 'data' => null], 422);
        }

        $employee = EmployeeM::find($row->employee_id);
        if ($employee?->user_id) {
            app(NotificationS::class)->notifyEmployee(
                'Attendance Regularization Update',
                'Your regularization request has been rejected.',
                'attendance_regularization_rejected',
                'hrms.attendance.regularizations.index',
                [],
                ['regularization_id' => $row->id],
                (int) $employee->user_id
            );
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Regularization rejected successfully.',
            'data' => new AttendanceRegularizationResource($row->fresh()),
        ]);
    }

    private function canApproveRegularization(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return method_exists($user, 'hasPermission') && (
            $user->hasPermission('attendance.regularization.approve')
            || $user->hasPermission('attendance.regularization.reject')
        );
    }
}
