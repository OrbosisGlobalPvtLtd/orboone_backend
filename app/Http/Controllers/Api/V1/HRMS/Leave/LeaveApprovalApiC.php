<?php

namespace App\Http\Controllers\Api\V1\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Services\HRMS\Leave\LeaveApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveApprovalApiC extends Controller
{
    public function __construct(private LeaveApprovalService $approvalService)
    {
    }

    public function pending(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->fail('Unauthenticated.', 401);
        }

        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole('super_admin'))
            || in_array((int)($user->system_role_id ?? 0), [1, 2], true);

        $canViewAll = $isSuperAdmin || (method_exists($user, 'hasPermission') && $user->hasPermission('leave.approvals.view_all'));
        $canViewTeam = method_exists($user, 'hasPermission') && ($user->hasPermission('leave.approvals.view_team') || $user->hasPermission('leave.approvals.view'));

        if (!$canViewAll && !$canViewTeam) {
            return $this->fail('Unauthorized to view leave approvals.', 403);
        }

        $requests = LeaveRequestM::with(['employee.user', 'leaveType', 'dates'])
            ->where('status', 'pending')
            ->when(!$canViewAll, function ($query) use ($user) {
                $employeeId = \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', $user->id)->value('id');
                $teamEmployeeIds = $employeeId ? app(\App\Services\HRMS\Team\TeamManagementScopeS::class)->getTeamEmployeeIds((int)$employeeId) : [];
                $query->whereIn('employee_id', $teamEmployeeIds);
            })
            ->when($request->employee_id, fn ($query) => $query->where('employee_id', $request->employee_id))
            ->latest()
            ->paginate((int) ($request->per_page ?: 20));

        return $this->ok('Pending leave requests fetched successfully.', $requests);
    }

    public function approve(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->fail('Unauthenticated.', 401);
        }

        $leaveRequest = LeaveRequestM::with('employee')->findOrFail($id);
        $employeeId = \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', $user->id)->value('id');
        $isAssignedManager = $employeeId && (int)$leaveRequest->reporting_manager_employee_id === (int)$employeeId;

        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole('super_admin'))
            || in_array((int)($user->system_role_id ?? 0), [1, 2], true);

        $canApprove = $isSuperAdmin
            || (method_exists($user, 'hasPermission') && $user->hasPermission('leave.approvals.approve'))
            || ($isAssignedManager && method_exists($user, 'hasPermission') && $user->hasPermission('leave.approvals.view_team'));

        if (!$canApprove) {
            return $this->fail('Unauthorized. You do not have permission to approve this leave request.', 403);
        }

        $request->validate(['note' => 'nullable|string|max:2000']);

        try {
            if (!$isSuperAdmin && $isAssignedManager && $leaveRequest->approval_level === 'pending_manager') {
                $updated = $this->approvalService->approveManagerStage($leaveRequest, $user->id, $request->note);
            } else {
                $updated = $this->approvalService->approve($leaveRequest, $user->id, $request->note);
            }

            return $this->ok('Leave request approved successfully.', $updated);
        } catch (\Throwable $e) {
            Log::error('API leave approve failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->fail('Unauthenticated.', 401);
        }

        $leaveRequest = LeaveRequestM::with('employee')->findOrFail($id);
        $employeeId = \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', $user->id)->value('id');
        $isAssignedManager = $employeeId && (int)$leaveRequest->reporting_manager_employee_id === (int)$employeeId;

        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole('super_admin'))
            || in_array((int)($user->system_role_id ?? 0), [1, 2], true);

        $canReject = $isSuperAdmin
            || (method_exists($user, 'hasPermission') && $user->hasPermission('leave.approvals.reject'))
            || ($isAssignedManager && method_exists($user, 'hasPermission') && $user->hasPermission('leave.approvals.view_team'));

        if (!$canReject) {
            return $this->fail('Unauthorized. You do not have permission to reject this leave request.', 403);
        }

        $request->validate(['reason' => 'required|string|max:2000']);

        try {
            $updated = $this->approvalService->reject($leaveRequest, $user->id, $request->reason);

            return $this->ok('Leave request rejected successfully.', $updated);
        } catch (\Throwable $e) {
            Log::error('API leave reject failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 422);
        }
    }

    private function ok(string $message, $data = [], int $code = 200)
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, int $code = 400, $data = [])
    {
        return response()->json(['status' => false, 'message' => $message, 'data' => $data], $code);
    }
}
