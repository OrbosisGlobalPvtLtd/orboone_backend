<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Http\Requests\Web\HRMS\Leave\ApproveLeaveRequestRequest;
use App\Http\Requests\Web\HRMS\Leave\RejectLeaveRequestRequest;
use App\Models\Core\AccessM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Services\HRMS\Leave\LeaveApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveApprovalC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private LeaveApprovalService $approvalService)
    {
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('leave.approvals.view_team')
            || $this->userHasPermission('leave.approvals.view'),
            403
        );

        $requests = LeaveRequestM::with(['employee.user', 'employee.employeeDetail', 'leaveType', 'dates'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status));

        if ($this->canViewAll('leave.approvals.view_all') || $this->userHasPermission('leave.approvals.view')) {
            $requests->when($request->employee_id, fn ($query) => $query->where('employee_id', $request->employee_id));
        } else {
            $teamIds = $this->teamEmployeeIds(false);
            abort_if(empty($teamIds), 403);
            $requests->whereIn('employee_id', $teamIds);
            if ($request->employee_id && in_array((int) $request->employee_id, $teamIds, true)) {
                $requests->where('employee_id', $request->employee_id);
            }
        }

        $requests = $requests
            ->when($request->leave_type_id, fn ($query) => $query->where('leave_type_id', $request->leave_type_id))
            ->when($request->from, fn ($query) => $query->whereDate('start_date', '>=', $request->from))
            ->when($request->to, fn ($query) => $query->whereDate('end_date', '<=', $request->to))
            ->latest()
            ->paginate(25);

        $employees = $this->canViewAll('leave.approvals.view_all') || $this->userHasPermission('leave.approvals.view')
            ? $this->employeeOptions()
            : $this->scopedEmployeeOptions('leave.approvals.view_all', 'leave.approvals.view_team');
        $leaveTypes = LeaveTypeM::orderBy('name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.approvals.index', compact('requests', 'employees', 'leaveTypes', 'accesses'))
            ->with('active', 'leave_management');
    }

    public function approve(ApproveLeaveRequestRequest $request, $id)
    {
        abort_unless($this->userHasPermission('leave.approvals.approve'), 403);

        try {
            $leaveRequest = LeaveRequestM::findOrFail($id);
            $this->authorizeLeaveRequestForApproval($leaveRequest);
            $this->approvalService->approve($leaveRequest, Auth::id(), $request->input('note') ?: $request->input('remark') ?: $request->input('admin_remark'));

            return back()->with('success', 'Leave request approved and attendance synced.');
        } catch (\Throwable $e) {
            Log::error('Leave approval failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(RejectLeaveRequestRequest $request, $id)
    {
        abort_unless($this->userHasPermission('leave.approvals.reject'), 403);

        try {
            $reason = $request->input('reason') ?: $request->input('remark') ?: $request->input('admin_remark') ?: 'Rejected by approver.';
            $leaveRequest = LeaveRequestM::findOrFail($id);
            $this->authorizeLeaveRequestForApproval($leaveRequest);
            $this->approvalService->reject($leaveRequest, Auth::id(), $reason);

            return back()->with('success', 'Leave request rejected.');
        } catch (\Throwable $e) {
            Log::error('Leave rejection failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    private function authorizeLeaveRequestForApproval(LeaveRequestM $leaveRequest): void
    {
        if ($this->canViewAll('leave.approvals.view_all') || $this->userHasPermission('leave.approvals.view')) {
            return;
        }

        abort_unless(
            $this->userHasPermission('leave.approvals.view_team')
            && in_array((int) $leaveRequest->employee_id, $this->teamEmployeeIds(false), true),
            403
        );
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }

    private function employeeOptions()
    {
        return EmployeeM::query()
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->select('employees_new.*', 'users.name as user_name')
            ->orderByRaw('COALESCE(users.name, employees_new.employee_code)')
            ->get();
    }
}
