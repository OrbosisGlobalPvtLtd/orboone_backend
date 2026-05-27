<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayWorkRequestC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = $this->employeeJoinedQuery('holiday_work_requests')->whereNull('holiday_work_requests.deleted_at');
        $this->applyCommonFilters($query, $request, [
            'dateColumn' => 'holiday_work_requests.worked_date',
            'filterMap' => ['employee_id' => 'holiday_work_requests.employee_id', 'status' => 'holiday_work_requests.status', 'work_type' => 'holiday_work_requests.work_type'],
        ]);

        return view('hrms.attendance.holiday_work.index', $this->pageData($query->latest('holiday_work_requests.id')->paginate(50)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'worked_date' => 'required|date',
            'work_type' => 'required|string|max:80',
            'reason' => 'nullable|string',
            'status' => 'nullable|in:pending,approved,rejected,cancelled'
        ]);

        $row = HolidayWorkRequestM::create(array_merge($data, [
            'status' => $data['status'] ?? 'pending',
        ]));

        if ($row->status === 'pending') {
            try {
                $employee = \App\Models\HRMS\Employee\EmployeeM::find($row->employee_id);
                if ($employee) {
                    $notificationService = app(\App\Services\HRMS\Notification\NotificationS::class);
                    $employeeName = $employee->display_name;
                    $workTypeLabel = str_contains(strtolower($row->work_type), 'weekoff') ? 'Weekoff' : 'Holiday';
                    $formattedDate = \Carbon\Carbon::parse($row->worked_date)->format('d M Y');
                    
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
                            'request_id' => $row->id,
                            'dates' => $row->worked_date ? \Carbon\Carbon::parse($row->worked_date)->toDateString() : '',
                            'work_type' => $row->work_type,
                            'action_url' => $actionUrl,
                            'route_name' => 'hrms.attendance.holiday_work.index',
                            'route_params' => [],
                        ]
                    );
                }
            } catch (\Throwable $e) {
                \Log::error("Failed to send web submission notification for request #{$row->id}: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Holiday work request saved.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate(['employee_id' => 'required|exists:employees_new,id', 'worked_date' => 'required|date', 'work_type' => 'required|string|max:80', 'reason' => 'nullable|string', 'status' => 'nullable|in:pending,approved,rejected,cancelled']);
        DB::table('holiday_work_requests')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));
        return back()->with('success', 'Holiday work request updated.');
    }

    public function approve($id)
    {
        $request = HolidayWorkRequestM::with('employee')->findOrFail($id);
        
        $request->update([
            'status' => 'approved',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => now(),
        ]);

        $request = $request->fresh();

        // Sync attendance status as in API if it exists
        if ($request->attendance_id) {
            $attendance = \App\Models\HRMS\Attendance\AttendanceM::find($request->attendance_id);
            if ($attendance) {
                $attendance->is_blocked = false;
                $attendance->is_punch_blocked = false;
                $attendance->is_lwp = false;
                $attendance->lwp_reason = null;
                $attendance->save();
            }
        }

        // Notify Employee
        $userId = $request->employee ? $request->employee->user_id : null;
        if ($userId) {
            try {
                $formattedDate = \Carbon\Carbon::parse($request->worked_date)->format('d M Y');
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
                        'dates' => $request->worked_date ? \Carbon\Carbon::parse($request->worked_date)->toDateString() : '',
                        'status' => 'approved',
                        'comp_off_generated' => (bool)$request->comp_off_generated,
                        'action_url' => $actionUrl,
                        'employee_id' => $request->employee_id,
                    ],
                    $userId
                );
            } catch (\Throwable $e) {
                \Log::error("Failed to send web approval notification to employee: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Holiday work approved.');
    }

    public function reject($id)
    {
        $request = HolidayWorkRequestM::with('employee')->findOrFail($id);
        $rejectionReason = request('rejection_reason', 'Rejected by HR Admin');
        
        $request->update([
            'status' => 'rejected',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => now(),
            'rejection_reason' => $rejectionReason,
        ]);

        $userId = $request->employee ? $request->employee->user_id : null;
        if ($userId) {
            try {
                $formattedDate = \Carbon\Carbon::parse($request->worked_date)->format('d M Y');
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
                        'request_id' => $request->id,
                        'dates' => $request->worked_date ? \Carbon\Carbon::parse($request->worked_date)->toDateString() : '',
                        'status' => 'rejected',
                        'rejection_reason' => $rejectionReason,
                        'reviewer_name' => $reviewerName,
                        'action_url' => $actionUrl,
                        'employee_id' => $request->employee_id,
                    ],
                    $userId
                );
            } catch (\Throwable $e) {
                \Log::error("Failed to send web rejection notification to employee: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Holiday work rejected.');
    }

    public function destroy($id)
    {
        DB::table('holiday_work_requests')->where('id', $id)->update(['deleted_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Holiday work request deleted.');
    }

    private function pageData($rows): array
    {
        $employees = $this->employeeOptions()->pluck('display_name', 'id')->toArray();
        return [
            'accesses' => $this->accesses(), 'active' => 'attendance', 'pageTitle' => 'Holiday Work Requests',
            'pageSubtitle' => 'Approve holiday/weekoff work. Comp off is generated after attendance eligibility validation.',
            'rows' => $rows,
            'columns' => [
                ['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'employee_code', 'label' => 'Code'], ['key' => 'worked_date', 'label' => 'Worked Date', 'type' => 'date'], ['key' => 'work_type', 'label' => 'Work Type'], ['key' => 'comp_off_generated', 'label' => 'Comp Off', 'type' => 'badge'], ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ],
            'filters' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']], ['name' => 'work_type', 'label' => 'Work Type', 'type' => 'select', 'options' => ['holiday_work' => 'Holiday Work', 'weekoff_work' => 'Weekoff Work']], ['name' => 'from', 'label' => 'From', 'type' => 'date'], ['name' => 'to', 'label' => 'To', 'type' => 'date'],
            ],
            'formFields' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'worked_date', 'label' => 'Worked Date', 'type' => 'date'], ['name' => 'work_type', 'label' => 'Work Type', 'type' => 'select', 'options' => ['holiday_work' => 'Holiday Work', 'weekoff_work' => 'Weekoff Work']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']], ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 12],
            ],
            'canCreate' => true, 'canEdit' => true, 'canDelete' => true,
            'storeRoute' => 'hrms.attendance.holiday_work.store', 'updateRoute' => 'hrms.attendance.holiday_work.update', 'deleteRoute' => 'hrms.attendance.holiday_work.destroy',
            'rowActions' => [['label' => 'Approve', 'route' => 'hrms.attendance.holiday_work.approve', 'icon' => 'fas fa-check', 'confirm' => 'Approve this work request? Comp off will be generated only after work completion validation.'], ['label' => 'Reject', 'route' => 'hrms.attendance.holiday_work.reject', 'icon' => 'fas fa-times', 'confirm' => 'Reject this request?']],
        ];
    }
}
