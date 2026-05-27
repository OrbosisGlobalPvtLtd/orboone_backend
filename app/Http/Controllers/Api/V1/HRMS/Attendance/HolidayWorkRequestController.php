<?php

namespace App\Http\Controllers\Api\V1\HRMS\Attendance;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\HRMS\Attendance\HolidayWorkRequestResource;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\WeekoffHolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HolidayWorkRequestController extends ApiController
{
    public function index(Request $request)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->error('Employee profile not found.', 404);
        }

        $rows = HolidayWorkRequestM::where('employee_id', $employee->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate((int) $request->input('per_page', 20));

        return $this->success('Holiday work requests fetched successfully.', [
            'records' => HolidayWorkRequestResource::collection($rows->items()),
            'pagination' => [
                'total' => $rows->total(),
                'per_page' => $rows->perPage(),
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'next_page_url' => $rows->nextPageUrl(),
                'prev_page_url' => $rows->previousPageUrl(),
            ],
        ]);
    }

    public function show(int $id)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->error('Employee profile not found.', 404);
        }

        $row = HolidayWorkRequestM::where('id', $id)->where('employee_id', $employee->id)->first();
        if (! $row) {
            return $this->error('Holiday work request not found.', 404);
        }

        return $this->success('Holiday work request fetched successfully.', new HolidayWorkRequestResource($row));
    }

    public function store(Request $request, WeekoffHolidayService $weekoffHolidayService)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->error('Employee profile not found.', 404);
        }

        $request->validate([
            'worked_date' => 'nullable|date',
            'worked_dates' => 'nullable|array',
            'worked_dates.*' => 'date',
            'work_type' => 'required|in:holiday_work,weekoff_work,holiday,weekoff',
            'work_mode' => 'nullable|in:wfo,wfh,WFO,WFH',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $dates = $request->input('worked_dates') ?: [$request->input('worked_date')];
        $dates = array_filter(array_unique($dates));

        if (empty($dates)) {
            return $this->error('At least one worked date is required.', 422);
        }

        // Validate each date first
        foreach ($dates as $dateStr) {
            $workedDate = Carbon::parse($dateStr, 'Asia/Kolkata');
            $dayInfo = $weekoffHolidayService->dayInfo($workedDate);
            if (!($dayInfo['is_holiday'] ?? false) && !($dayInfo['is_weekoff'] ?? false)) {
                return $this->error("Date {$dateStr} is a regular working day. Work request can only be submitted for holidays or weekoffs.", 422);
            }

            $duplicate = HolidayWorkRequestM::where('employee_id', $employee->id)
                ->whereDate('worked_date', $workedDate->toDateString())
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->exists();

            if ($duplicate) {
                return $this->error("A work request already exists for {$dateStr}.", 422);
            }
        }

        $workTypeMap = [
            'holiday' => 'holiday_work',
            'weekoff' => 'weekoff_work',
            'holiday_work' => 'holiday_work',
            'weekoff_work' => 'weekoff_work',
        ];
        $workType = $workTypeMap[strtolower($request->input('work_type'))] ?? 'holiday_work';
        $workMode = strtolower($request->input('work_mode', 'wfo'));

        $attachmentPath = null;
        if ($request->hasFile('attachment') && Schema::hasColumn('holiday_work_requests', 'attachment_path')) {
            $attachmentPath = $request->file('attachment')->store('attendance/holiday-work', 'public');
        }

        $createdRequests = [];
        \DB::transaction(function() use ($employee, $dates, $workType, $workMode, $request, $attachmentPath, &$createdRequests) {
            foreach ($dates as $dateStr) {
                $workedDate = Carbon::parse($dateStr, 'Asia/Kolkata')->toDateString();
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $workedDate)
                    ->first();

                $insertData = [
                    'employee_id' => $employee->id,
                    'attendance_id' => $attendance?->id,
                    'worked_date' => $workedDate,
                    'work_type' => $workType,
                    'work_mode' => $workMode,
                    'reason' => $request->input('reason'),
                    'notes' => $request->input('notes'),
                    'status' => 'pending',
                ];

                if (Schema::hasColumn('holiday_work_requests', 'attachment_path')) {
                    $insertData['attachment_path'] = $attachmentPath;
                }

                $row = HolidayWorkRequestM::create($insertData);
                $createdRequests[] = $row;
            }
        });

        $notificationService = app(\App\Services\HRMS\Notification\NotificationS::class);
        foreach ($createdRequests as $req) {
            try {
                $employeeName = $employee->display_name;
                $workTypeLabel = str_contains(strtolower($req->work_type), 'weekoff') ? 'Weekoff' : 'Holiday';
                $formattedDate = Carbon::parse($req->worked_date)->format('d M Y');
                
                $title = "New Work Request Submitted";
                $message = "{$employeeName} submitted a {$workTypeLabel} work request for {$formattedDate}.";
                
                $actionUrl = route('hrms.attendance.holiday_work.index', [], false);
                
                $notificationService->notifyHrAndSuperAdmin(
                    $title,
                    $message,
                    'holiday_work_request_submitted',
                    'hrms.attendance.holiday_work.index',
                    [],
                    [
                        'employee_id' => $employee->id,
                        'request_id' => $req->id,
                        'dates' => $req->worked_date ? $req->worked_date->toDateString() : '',
                        'work_type' => $req->work_type,
                        'action_url' => $actionUrl,
                        'route_name' => 'hrms.attendance.holiday_work.index',
                        'route_params' => [],
                    ]
                );
            } catch (\Throwable $e) {
                \Log::error("Failed to send submission notification for request #{$req->id}: " . $e->getMessage());
            }
        }

        if (count($createdRequests) === 1) {
            return $this->success('Holiday/weekoff work request created successfully.', new HolidayWorkRequestResource($createdRequests[0]), 201);
        }

        return $this->success('Holiday/weekoff work requests created successfully.', HolidayWorkRequestResource::collection($createdRequests), 201);
    }

    public function approve(int $id)
    {
        if (! $this->canManageHolidayWork()) {
            return $this->error('Unauthorized.', 403);
        }

        $request = HolidayWorkRequestM::with('employee')->find($id);
        if (! $request) {
            return $this->error('Holiday work request not found.', 404);
        }
        if ($request->status !== 'pending') {
            return $this->error('Only pending requests can be approved.', 422);
        }

        // 1. Mark request as approved
        $request->update([
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => Carbon::now('Asia/Kolkata'),
        ]);

        // Comp off must only be generated by scheduler after attendance eligibility checks.
        $request = $request->fresh();

        if ($request->attendance_id) {
            $attendance = Attendance::find($request->attendance_id);
            if ($attendance) {
                $attendance->is_blocked = false;
                $attendance->is_punch_blocked = false;
                $attendance->is_lwp = false;
                $attendance->lwp_reason = null;
                $attendance->save();
            }
        }

        $userId = $request->employee ? $request->employee->user_id : null;
        if ($userId) {
            try {
                $formattedDate = Carbon::parse($request->worked_date)->format('d M Y');
                $title = "Work Request Approved";
                $message = "Your work request for {$formattedDate} has been approved.";
                $actionUrl = route('hrms.attendance.holiday_work.index', [], false);
                
                app(\App\Services\HRMS\Notification\NotificationS::class)->notifyEmployee(
                    $title,
                    $message,
                    'holiday_work_request_approved',
                    'hrms.attendance.holiday_work.index',
                    [],
                    [
                        'request_id' => $request->id,
                        'dates' => $request->worked_date ? $request->worked_date->toDateString() : '',
                        'status' => 'approved',
                        'comp_off_generated' => (bool)$request->comp_off_generated,
                        'action_url' => $actionUrl,
                        'employee_id' => $request->employee_id,
                    ],
                    $userId
                );
            } catch (\Throwable $e) {
                \Log::error("Failed to send approval notification to employee: " . $e->getMessage());
            }
        }

        return $this->success('Holiday work request approved successfully.', new HolidayWorkRequestResource($request));
    }

    public function reject(int $id, Request $request)
    {
        if (! $this->canManageHolidayWork()) {
            return $this->error('Unauthorized.', 403);
        }

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $row = HolidayWorkRequestM::find($id);
        if (! $row) {
            return $this->error('Holiday work request not found.', 404);
        }
        if ($row->status !== 'pending') {
            return $this->error('Only pending requests can be rejected.', 422);
        }

        $row->update([
            'status' => 'rejected',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        $row = $row->fresh(['employee']);
        $userId = $row->employee ? $row->employee->user_id : null;
        if ($userId) {
            try {
                $formattedDate = Carbon::parse($row->worked_date)->format('d M Y');
                $title = "Work Request Rejected";
                $message = "Your work request for {$formattedDate} has been rejected.";
                $actionUrl = route('hrms.attendance.holiday_work.index', [], false);
                $reviewerName = auth()->user()->name ?? 'HR Admin';
                
                app(\App\Services\HRMS\Notification\NotificationS::class)->notifyEmployee(
                    $title,
                    $message,
                    'holiday_work_request_rejected',
                    'hrms.attendance.holiday_work.index',
                    [],
                    [
                        'request_id' => $row->id,
                        'dates' => $row->worked_date ? $row->worked_date->toDateString() : '',
                        'status' => 'rejected',
                        'rejection_reason' => $row->rejection_reason,
                        'reviewer_name' => $reviewerName,
                        'action_url' => $actionUrl,
                        'employee_id' => $row->employee_id,
                    ],
                    $userId
                );
            } catch (\Throwable $e) {
                \Log::error("Failed to send rejection notification to employee: " . $e->getMessage());
            }
        }

        return $this->success('Holiday work request rejected successfully.', new HolidayWorkRequestResource($row->fresh()));
    }

    private function employee(): ?EmployeeM
    {
        return EmployeeM::where('user_id', auth()->id())->first();
    }

    private function canManageHolidayWork(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return method_exists($user, 'hasPermission')
            && ($user->hasPermission('attendance.holiday_work.approve') || $user->hasPermission('attendance.holiday_work.reject'));
    }

    private function success(string $message, $data, int $code = 200)
    {
        return response()->json([
            'success' => true,
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function error(string $message, int $code)
    {
        return response()->json([
            'success' => false,
            'status' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }
}
