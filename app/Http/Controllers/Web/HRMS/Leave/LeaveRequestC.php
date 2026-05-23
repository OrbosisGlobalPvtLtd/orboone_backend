<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Http\Requests\Web\HRMS\Leave\StoreLeaveRequestRequest;
use App\Models\Core\AccessM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Services\HRMS\Leave\LeaveApprovalService;
use App\Services\HRMS\Leave\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveRequestC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private LeaveCalculationService $calculationService,
        private LeaveApprovalService $approvalService
    ) {
    }

    public function index(Request $request)
    {
        $employee = EmployeeM::where('user_id', Auth::id())->first();
        abort_if(! $employee, 403, 'No employee profile linked to your account.');

        $requests = LeaveRequestM::with(['leaveType', 'dates'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status));

        if ($this->canViewAll('leave.approvals.view_all')) {
            $requests->when($request->employee_id, fn ($query) => $query->where('employee_id', $request->employee_id));
        } elseif ($this->canViewTeam('leave.approvals.view_team')) {
            $requests->whereIn('employee_id', $this->teamEmployeeIds(true));
        } else {
            $requests->where('employee_id', $employee->id);
        }

        $requests = $requests->latest()->paginate(20);

        $allocation = $employee->leaveAllocations()->where('year', Carbon::now('Asia/Kolkata')->year)->latest()->first();
        $leaveTypes = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.requests.index', compact('requests', 'allocation', 'leaveTypes', 'employee', 'accesses'))
            ->with('active', 'leave_management');
    }

    public function create()
    {
        $employee = EmployeeM::where('user_id', Auth::id())->first();
        abort_if(! $employee, 403, 'No employee profile linked to your account.');

        $leaveTypes = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.requests.create', compact('leaveTypes', 'employee', 'accesses'))
            ->with('active', 'leave_management');
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        try {
            abort_unless($this->userHasPermission('leave.my_requests.create'), 403);

            $employee = $request->filled('employee_id') && $this->canViewAll('leave.approvals.view_all')
                ? EmployeeM::findOrFail($request->employee_id)
                : EmployeeM::where('user_id', Auth::id())->firstOrFail();

            $leaveType = LeaveTypeM::findOrFail($request->leave_type_id);
            $attachmentPath = $this->storeAttachment($request);
            $payload = array_merge($request->validated(), ['attachment_path' => $attachmentPath]);
            $calculation = $this->calculationService->calculate($employee, $leaveType, $payload);

            $leaveRequest = LeaveRequestM::create([
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'leave_type_id' => $leaveType->id,
                'reporting_manager_employee_id' => $employee->reporting_manager_employee_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'requested_days' => $calculation['requested_days'],
                'deducted_days' => $calculation['deducted_days'],
                'is_half_day' => $request->boolean('is_half_day'),
                'half_day_type' => $request->half_day_type,
                'reason' => $request->reason,
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
                'sandwich_applied' => $calculation['sandwich_applied'],
                'paid_days' => $calculation['paid_days'],
                'sick_days' => $calculation['sick_days'],
                'comp_off_days' => $calculation['comp_off_days'],
                'lwp_days' => $calculation['lwp_days'],
                'applied_from' => 'web',
                'emergency_leave' => $request->boolean('emergency_leave'),
            ]);

            foreach ($calculation['dates'] as $row) {
                $leaveRequest->dates()->create(array_merge($row, ['employee_id' => $employee->id]));
            }

            $this->notifyLeaveApplied($leaveRequest->fresh(['employee.user', 'leaveType', 'dates']));

            return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully.');
        } catch (\Throwable $e) {
            Log::error('Web leave request failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            abort_unless($this->userHasPermission('leave.my_requests.cancel'), 403);

            $leaveRequest = LeaveRequestM::findOrFail($id);
            $employeeId = $this->ownEmployeeId();
            abort_unless($employeeId && (int) $leaveRequest->employee_id === (int) $employeeId, 403);
            $this->approvalService->cancel($leaveRequest, Auth::id(), $request->input('reason'));

            return back()->with('success', 'Leave request cancelled successfully.');
        } catch (\Throwable $e) {
            Log::error('Leave cancellation failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    private function storeAttachment(StoreLeaveRequestRequest $request): ?string
    {
        if (! $request->hasFile('attachment')) {
            return null;
        }

        $file = $request->file('attachment');
        $directory = public_path('uploads/leave_attachments');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $fileName = 'leave_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $fileName);

        return 'uploads/leave_attachments/' . $fileName;
    }

    private function notifyLeaveApplied(LeaveRequestM $leaveRequest): void
    {
        $leaveType = $leaveRequest->leaveType?->name ?: 'Leave';
        $employeeName = $leaveRequest->employee?->display_name ?: 'Employee';
        $dateRange = Carbon::parse($leaveRequest->start_date)->format('d M Y') . ' to ' . Carbon::parse($leaveRequest->end_date)->format('d M Y');
        $payload = [
            'leave_id' => $leaveRequest->id,
            'leave_type' => $leaveType,
            'leave_dates' => $dateRange,
            'from_date' => (string) $leaveRequest->start_date,
            'to_date' => (string) $leaveRequest->end_date,
            'start_date' => (string) $leaveRequest->start_date,
            'end_date' => (string) $leaveRequest->end_date,
            'employee_id' => $leaveRequest->employee_id,
            'employee_name' => $employeeName,
            'status' => 'pending',
            'attachment_url' => $this->leaveAttachmentUrl($leaveRequest->attachment_path),
            'attachment_type' => $leaveRequest->attachment_path ? $this->attachmentType($leaveRequest->attachment_path) : '',
            'attachment_name' => $leaveRequest->attachment_path ? basename($leaveRequest->attachment_path) : '',
        ];

        app(\App\Services\HRMS\Notification\NotificationS::class)->notifyHrAndSuperAdmin(
            'New Leave Request',
            "{$employeeName} applied for {$leaveType} leave from {$payload['from_date']} to {$payload['to_date']}.",
            'leave_applied',
            'leave-approvals.index',
            ['leave_id' => $leaveRequest->id],
            $payload
        );
    }

    private function leaveAttachmentUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        return preg_match('/^https?:\/\//i', $path) ? $path : asset(ltrim($path, '/'));
    }

    private function attachmentType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'image';
        }

        return $extension === 'pdf' ? 'pdf' : 'document';
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
