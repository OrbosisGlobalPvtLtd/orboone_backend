<?php

namespace App\Http\Controllers\Api\V1\HRMS\Attendance;

use App\Http\Resources\HRMS\Attendance\AttendanceRegularizationResource;
use App\Http\Controllers\Api\V1\ApiController;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceRegularizationM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceRegularizationController extends ApiController
{
    public function requestRegularization(Request $request)
    {
        $employee = EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Employee profile not found.', 'data' => null], 404);
        }

        $data = $request->validate([
            'attendance_id' => 'nullable|exists:attendances,id',
            'request_type' => 'required|string|max:80',
            'requested_punch_in' => 'nullable|date',
            'requested_punch_out' => 'nullable|date',
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

        DB::transaction(function () use ($row, $attendanceService) {
            $attendance = $row->attendance_id
                ? Attendance::where('id', $row->attendance_id)->where('employee_id', $row->employee_id)->first()
                : null;

            if (! $attendance) {
                $date = $row->requested_punch_in
                    ? Carbon::parse($row->requested_punch_in, $attendanceService->attendanceTimezone())->toDateString()
                    : Carbon::now($attendanceService->attendanceTimezone())->toDateString();
                $attendance = Attendance::firstOrCreate(
                    ['employee_id' => $row->employee_id, 'attendance_date' => $date],
                    ['user_id' => optional(EmployeeM::find($row->employee_id))->user_id]
                );
            }

            if ($row->requested_punch_in) {
                $attendance->punch_in_time = Carbon::parse($row->requested_punch_in, $attendanceService->attendanceTimezone())->format('H:i:s');
            }
            if ($row->requested_punch_out) {
                $attendance->punch_out_time = Carbon::parse($row->requested_punch_out, $attendanceService->attendanceTimezone())->format('H:i:s');
            }
            $attendance->save();

            if ($attendance->punch_in_time && $attendance->punch_out_time) {
                $attendanceService->calculateAttendanceStats($attendance);
            }

            $row->update([
                'attendance_id' => $attendance->id,
                'status' => 'approved',
                'approved_by_user_id' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

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
        if ($row->status !== 'pending') {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Only pending requests can be rejected.', 'data' => null], 422);
        }

        $row->update([
            'status' => 'rejected',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

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
