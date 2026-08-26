<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Http\Requests\Web\HRMS\Leave\StoreLeaveRequestRequest;
use App\Mail\HrWorkflowAlertMail;
use App\Models\Core\AccessM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Services\HRMS\Leave\LeaveApprovalService;
use App\Services\HRMS\Leave\LeaveCalculationService;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LeaveRequestC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private LeaveCalculationService $calculationService,
        private LeaveApprovalService $approvalService,
        private HrmsStoragePathS $paths,
        private HrmsFileResolverS $resolver
    ) {
    }

    public function index(Request $request)
    {
        $employee = EmployeeM::where('user_id', Auth::id())->first();
        abort_if(! $employee, 403, 'No employee profile linked to your account.');

        // Auto-expire past pending leaves
        app(\App\Services\HRMS\Leave\AutoExpireLeaveService::class)->expirePastPendingRequests();

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

        if (! $this->isEligibleForLeaveRequest($employee)) {
            return back()->with('error', 'Leave applications are currently restricted to Engineering/Development and QA/Testing team members.');
        }

        $year = Carbon::now('Asia/Kolkata')->year;
        $allocation = resolve(\App\Services\HRMS\Leave\LeaveAllocationService::class)->getOrGenerate($employee, $year, auth()->id());

        $leaveTypes = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.requests.create', compact('leaveTypes', 'employee', 'accesses', 'allocation'))
            ->with('active', 'leave_management');
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        try {
            abort_unless($this->userHasPermission('leave.my_requests.create'), 403);

            $employee = $request->filled('employee_id') && $this->canViewAll('leave.approvals.view_all')
                ? EmployeeM::findOrFail($request->employee_id)
                : EmployeeM::where('user_id', Auth::id())->firstOrFail();

            if (! $this->isEligibleForLeaveRequest($employee)) {
                return back()->with('error', 'Leave applications are currently restricted to Engineering/Development and QA/Testing team members.')->withInput();
            }

            $leaveType = LeaveTypeM::findOrFail($request->leave_type_id);
            $attachmentPath = $this->storeAttachment($request);
            $sanitized = \App\Services\HRMS\Leave\LeaveValidationService::sanitizePayload($request->validated());
            $payload = array_merge($sanitized, ['attachment_path' => $attachmentPath]);
            $calculation = $this->calculationService->calculate($employee, $leaveType, $payload);

            $hasReportingManager = ! empty($employee->reporting_manager_employee_id);
            $approvalLevel = $hasReportingManager ? 'pending_manager' : 'pending_hr';

            $leaveRequest = LeaveRequestM::create([
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'leave_type_id' => $leaveType->id,
                'reporting_manager_employee_id' => $employee->reporting_manager_employee_id,
                'start_date' => $sanitized['start_date'],
                'end_date' => $sanitized['end_date'],
                'requested_days' => $calculation['requested_days'],
                'deducted_days' => $calculation['deducted_days'],
                'is_half_day' => $sanitized['is_half_day'],
                'half_day_type' => $sanitized['half_day_type'],
                'reason' => $request->reason,
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
                'approval_level' => $approvalLevel,
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

            $this->notifyLeaveApplied($leaveRequest->fresh(['employee.user', 'employee.department', 'leaveType', 'dates']));

            return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully.');
        } catch (\Throwable $e) {
            Log::error('Web leave request failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequestM::findOrFail($id);
            $employee = EmployeeM::where('user_id', Auth::id())->first();
            abort_unless($employee && (int) $leaveRequest->employee_id === (int) $employee->id, 403);
            abort_unless($leaveRequest->status === 'pending', 400, 'Cannot edit request after approval or rejection.');

            $request->validate([
                'leave_type_id' => 'required|exists:leave_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:1000',
            ]);

            $sanitized = [
                'leave_type_id' => (int) $request->leave_type_id,
                'start_date' => (string) $request->start_date,
                'end_date' => (string) $request->end_date,
                'is_half_day' => $request->boolean('is_half_day'),
                'half_day_type' => $request->input('half_day_type', 'first_half'),
                'emergency_leave' => $request->boolean('emergency_leave'),
            ];

            $calculation = $this->calculationService->calculateLeaveRequest($employee, $sanitized);

            $leaveRequest->update([
                'leave_type_id' => $sanitized['leave_type_id'],
                'start_date' => $sanitized['start_date'],
                'end_date' => $sanitized['end_date'],
                'requested_days' => $calculation['requested_days'],
                'deducted_days' => $calculation['deducted_days'],
                'is_half_day' => $sanitized['is_half_day'],
                'half_day_type' => $sanitized['half_day_type'],
                'reason' => $request->reason,
                'sandwich_applied' => $calculation['sandwich_applied'],
                'paid_days' => $calculation['paid_days'],
                'sick_days' => $calculation['sick_days'],
                'comp_off_days' => $calculation['comp_off_days'],
                'lwp_days' => $calculation['lwp_days'],
                'emergency_leave' => $sanitized['emergency_leave'],
            ]);

            $leaveRequest->dates()->delete();
            foreach ($calculation['dates'] as $row) {
                $leaveRequest->dates()->create(array_merge($row, ['employee_id' => $employee->id]));
            }

            return redirect()->route('leave-requests.index')->with('success', 'Leave request updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Web leave request update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function preview(Request $request)
    {
        try {
            $employee = EmployeeM::where('user_id', Auth::id())->first();
            if (! $employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 400);
            }

            $leaveTypeId = (int) $request->input('leave_type_id');
            $leaveType = LeaveTypeM::find($leaveTypeId);
            if (! $leaveType) {
                return response()->json(['success' => false, 'message' => 'Invalid leave type.'], 400);
            }

            $startDate = (string) $request->input('start_date');
            $endDate = (string) $request->input('end_date', $startDate);

            if (! $startDate) {
                return response()->json(['success' => false, 'message' => 'Start date is required.'], 400);
            }

            $sanitized = [
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_half_day' => $request->boolean('is_half_day'),
                'half_day_type' => $request->input('half_day_type', 'first_half'),
                'emergency_leave' => $request->boolean('emergency_leave'),
            ];

            $calc = $this->calculationService->calculate($employee, $leaveType, $sanitized);

            return response()->json([
                'success' => true,
                'data' => [
                    'requested_calendar_days' => $calc['requested_calendar_days'],
                    'working_days' => $calc['working_days'],
                    'sandwich_days' => $calc['sandwich_days'],
                    'deducted_days' => $calc['deducted_days'],
                    'sandwich_applied' => $calc['sandwich_applied'],
                    'paid_days' => $calc['paid_days'],
                    'sick_days' => $calc['sick_days'],
                    'comp_off_days' => $calc['comp_off_days'],
                    'lwp_days' => $calc['lwp_days'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: $e->getMessage();
            return response()->json(['success' => false, 'message' => $msg], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
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
        $employeeId = (int) ($this->ownEmployeeId() ?: 0);
        $directory = $employeeId > 0
            ? $this->paths->employeeLeave($employeeId, 'attachments')
            : $this->paths->temp('previews');

        return $file->store($directory, 'private');
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

        $hrEmail = config('hrms.emails.hr');
        $employeeEmail = $leaveRequest->employee?->user?->email;
        if ($hrEmail) {
            $details = [
                'Employee Name' => $employeeName,
                'Employee Code' => $leaveRequest->employee?->employee_code ?: 'N/A',
                'Department' => $leaveRequest->employee?->department?->name ?: 'N/A',
                'Leave Type' => $leaveType,
                'Start Date' => (string) $leaveRequest->start_date,
                'End Date' => (string) $leaveRequest->end_date,
                'Total Days' => (string) $leaveRequest->requested_days,
                'Reason' => (string) ($leaveRequest->reason ?: '-'),
                'Apply Date' => now()->toDateTimeString(),
            ];

            Mail::to($hrEmail)->queue(new HrWorkflowAlertMail(
                subjectText: 'Leave Request Submitted - ' . $employeeName,
                workflowTitle: 'New Leave Request',
                details: $details,
                actionUrl: route('leave-approvals.index', ['leave_id' => $leaveRequest->id]),
                replyToEmail: $employeeEmail
            ));
        }
    }

    private function leaveAttachmentUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        return preg_match('/^https?:\/\//i', $path) ? $path : $this->resolver->secureFileUrl($path);
    }

    private function attachmentType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'image';
        }

        return $extension === 'pdf' ? 'pdf' : 'document';
    }

    private function isEligibleForLeaveRequest(EmployeeM $employee): bool
    {
        $user = auth()->user();
        if ($this->canViewAll('leave.approvals.view_all') || ($user->role_id ?? null) == 1 || ($user->system_role_id ?? null) == 1) {
            return true;
        }

        $employee->loadMissing(['department', 'designation']);
        $deptName = strtolower($employee->department?->name ?? '');
        $desigName = strtolower($employee->designation?->name ?? '');
        $roleName = strtolower($user->role?->name ?? '');

        $keywords = ['dev', 'engineering', 'qa', 'testing', 'software', 'tech', 'developer', 'qa engineer', 'ui/ux', 'intern'];
        foreach ($keywords as $kw) {
            if (str_contains($deptName, $kw) || str_contains($desigName, $kw) || str_contains($roleName, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
