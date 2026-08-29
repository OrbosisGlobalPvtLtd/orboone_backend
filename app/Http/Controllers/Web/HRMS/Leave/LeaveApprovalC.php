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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveApprovalC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private LeaveApprovalService $approvalService,
        private \App\Services\HRMS\Reporting\ReportingScopeS $scopeS
    ) {
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('leave.approvals.view_team')
            || $this->userHasPermission('leave.approvals.view'),
            403
        );

        app(\App\Services\HRMS\Leave\AutoExpireLeaveService::class)->expirePastPendingRequests();

        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);

        $query = DB::table('leave_requests')
            ->join('employees_new as e', 'e.id', '=', 'leave_requests.employee_id')
            ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
            ->leftJoin('designations as d', 'd.id', '=', 'e.designation_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'e.department_id')
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'leave_requests.leave_type_id')
            ->leftJoin('users as mu', 'mu.id', '=', 'leave_requests.manager_approved_by')
            ->leftJoin('users as hru', 'hru.id', '=', 'leave_requests.hr_approved_by')
            ->leftJoin('employees_new as rm', 'rm.id', '=', 'e.reporting_manager_employee_id')
            ->leftJoin('users as rmu', 'rmu.id', '=', 'rm.user_id');

        $query = $this->scopeS->scopeLeaveQuery($query, $supervisorEmpId);

        if ($request->filled('employee_id')) {
            $query->where('leave_requests.employee_id', $request->employee_id);
        }

        if ($request->filled('reporting_manager_id')) {
            $query->where('e.reporting_manager_employee_id', $request->reporting_manager_id);
        }

        if ($request->filled('status')) {
            $st = strtolower($request->status);
            if ($st === 'pending_manager') {
                $query->where('leave_requests.status', 'pending')
                    ->whereNotNull('e.reporting_manager_employee_id')
                    ->whereNull('leave_requests.manager_approved_at');
            } elseif ($st === 'pending_hr') {
                $query->where('leave_requests.status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('e.reporting_manager_employee_id')
                            ->orWhereNotNull('leave_requests.manager_approved_at')
                            ->orWhere('leave_requests.approval_level', 'manager_approved');
                    });
            } elseif ($st === 'all') {
                // Show all statuses (pending, approved, rejected)
            } else {
                $query->where('leave_requests.status', $st);
            }
        } else {
            // Default to PENDING leaves only
            $query->where('leave_requests.status', 'pending');
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_requests.leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('leave_requests.start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('leave_requests.end_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('eu.name', 'like', $search)
                    ->orWhere('e.employee_code', 'like', $search);
            });
        }

        $leaveRequests = $query->select(
            'leave_requests.*',
            'e.reporting_manager_employee_id as current_reporting_manager_id',
            DB::raw('COALESCE(eu.name, e.employee_code) as display_name'),
            'e.employee_code',
            'd.name as designation_name',
            'dept.name as department_name',
            'lt.name as leave_type_name',
            'mu.name as manager_approver_name',
            'hru.name as hr_approver_name',
            'rmu.name as reporting_manager_name'
        )
            ->orderByDesc('leave_requests.id')
            ->paginate(20)
            ->appends($request->query());

        // Base query for summary counts
        $baseCountQuery = DB::table('leave_requests')
            ->join('employees_new as e', 'e.id', '=', 'leave_requests.employee_id');
        $baseCountQuery = $this->scopeS->scopeLeaveQuery($baseCountQuery, $supervisorEmpId);

        $totalPendingCount = (clone $baseCountQuery)->where('leave_requests.status', 'pending')->count();
        $managerPendingCount = (clone $baseCountQuery)
            ->where('leave_requests.status', 'pending')
            ->whereNotNull('e.reporting_manager_employee_id')
            ->whereNull('leave_requests.manager_approved_at')
            ->count();
        $hrPendingCount = (clone $baseCountQuery)
            ->where('leave_requests.status', 'pending')
            ->where(function ($q) {
                $q->whereNull('e.reporting_manager_employee_id')
                    ->orWhereNotNull('leave_requests.manager_approved_at')
                    ->orWhere('leave_requests.approval_level', 'manager_approved');
            })
            ->count();
        $approvedLeaveCount = (clone $baseCountQuery)->where('leave_requests.status', 'approved')->count();
        $rejectedLeaveCount = (clone $baseCountQuery)->where('leave_requests.status', 'rejected')->count();

        $user = auth()->user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin', 'manager']))
            || $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('leave.approvals.view')
            || $this->userHasPermission('leave.approvals.approve');

        if ($isHrOrAdmin || $isSuperAdmin) {
            $employees = EmployeeM::with(['user'])->active()->get();
        } else {
            $employees = EmployeeM::with(['user'])->active()->whereIn('id', $supervisedEmpIds)->get();
        }

        $leaveTypes = LeaveTypeM::orderBy('name')->get();
        $reportingManagers = EmployeeM::whereIn('id', function ($q) {
            $q->select('reporting_manager_employee_id')->from('employees_new')->whereNotNull('reporting_manager_employee_id');
        })->with(['user'])->get();

        return view('hrms.leave.approvals.index', compact(
            'leaveRequests',
            'totalPendingCount',
            'managerPendingCount',
            'hrPendingCount',
            'approvedLeaveCount',
            'rejectedLeaveCount',
            'employees',
            'leaveTypes',
            'reportingManagers',
            'supervisorEmpId',
            'isHrOrAdmin',
            'isSuperAdmin'
        ));
    }

    public function approve(ApproveLeaveRequestRequest $request, $id)
    {
        $referer = $request->header('referer') ?: route('reporting.leave');
        $user = auth()->user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('leave.approvals.view')
            || $this->userHasPermission('leave.approvals.approve');

        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $isReportingManager = ! empty($supervisorEmpId) && \Illuminate\Support\Facades\DB::table('employees_new')->where('reporting_manager_employee_id', $supervisorEmpId)->exists();

        abort_unless($isSuperAdmin || $isHrOrAdmin || $isReportingManager, 403);

        try {
            $leaveRequest = LeaveRequestM::findOrFail($id);
            if ($leaveRequest->status === 'approved') {
                return redirect()->to($referer)->with('error', 'Leave request is already approved.');
            }

            $this->authorizeLeaveRequestForApproval($leaveRequest);

            $note = $request->input('note') ?: $request->input('remark') ?: $request->input('admin_remark');
            $employee = EmployeeM::find($leaveRequest->employee_id);
            $managerEmpId = $employee?->reporting_manager_employee_id ?: $leaveRequest->reporting_manager_employee_id;
            $hasManager = ! empty($managerEmpId);
            $managerApproved = ! empty($leaveRequest->manager_approved_at) || $leaveRequest->approval_level === 'manager_approved';

            $isAssignedManager = ($supervisorEmpId && $managerEmpId && (int)$supervisorEmpId === (int)$managerEmpId);

            // CASE 1: Super Admin or HR Admin Full Approval
            if ($isSuperAdmin || $isHrOrAdmin) {
                if ($hasManager && ! $managerApproved) {
                    $leaveRequest->manager_approved_by = Auth::id();
                    $leaveRequest->manager_approved_at = \Carbon\Carbon::now('Asia/Kolkata');
                    $leaveRequest->manager_note = $isSuperAdmin ? 'Super Admin Override Approval' : 'HR Admin Direct Approval';
                    $leaveRequest->save();
                }
                $this->approvalService->approve($leaveRequest, Auth::id(), $note ?: ($isSuperAdmin ? 'Approved by Super Admin' : 'Approved by HR Admin'));
                return redirect()->to($referer)->with('success', 'Leave request approved & finalized.');
            }

            // CASE 2: Employee HAS a Reporting Manager and Manager stage is pending
            if ($hasManager && ! $managerApproved) {
                if (! $isAssignedManager) {
                    return redirect()->to($referer)->with('error', 'Awaiting Reporting Manager approval.');
                }

                $this->approvalService->approveManagerStage($leaveRequest, Auth::id(), $note);
                return redirect()->to($referer)->with('success', 'Leave request approved by Manager. Sent to HR for final approval.');
            }

            // CASE 3: HR Stage Approval
            if (! $isHrOrAdmin && ! $isAssignedManager) {
                abort(403, 'Unauthorized. HR Admin permission is required for final leave approval.');
            }

            if (! $isHrOrAdmin && $managerApproved) {
                return redirect()->to($referer)->with('error', 'You have already approved this request at Manager stage. Awaiting HR Admin final approval.');
            }

            $this->approvalService->approve($leaveRequest, Auth::id(), $note);

            return redirect()->to($referer)->with('success', 'Leave request approved and attendance synced.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?: 'Validation failed for leave approval.';
            return redirect()->to($referer)->with('error', $firstError);
        } catch (\Throwable $e) {
            Log::error('Leave approval failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->to($referer)->with('error', $e->getMessage());
        }
    }

    public function reject(RejectLeaveRequestRequest $request, $id)
    {
        $referer = $request->header('referer') ?: route('reporting.leave');
        $user = auth()->user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('leave.approvals.view')
            || $this->userHasPermission('leave.approvals.reject');

        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $isReportingManager = ! empty($supervisorEmpId) && \Illuminate\Support\Facades\DB::table('employees_new')->where('reporting_manager_employee_id', $supervisorEmpId)->exists();

        abort_unless($isSuperAdmin || $isHrOrAdmin || $isReportingManager, 403);

        try {
            $leaveRequest = LeaveRequestM::findOrFail($id);
            if (! in_array($leaveRequest->status, ['pending', 'manager_approved', 'approved'], true)) {
                return redirect()->to($referer)->with('error', 'Only pending or approved leave requests can be rejected.');
            }
            $reason = $request->input('reason') ?: $request->input('remark') ?: $request->input('admin_remark') ?: 'Rejected by approver.';
            $this->authorizeLeaveRequestForApproval($leaveRequest);
            $this->approvalService->reject($leaveRequest, Auth::id(), $reason);

            return redirect()->to($referer)->with('success', 'Leave request rejected.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?: 'Validation failed for leave rejection.';
            return redirect()->to($referer)->with('error', $firstError);
        } catch (\Throwable $e) {
            Log::error('Leave rejection failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->to($referer)->with('error', $e->getMessage());
        }
    }

    private function authorizeLeaveRequestForApproval(LeaveRequestM $leaveRequest): void
    {
        $user = auth()->user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || $this->userHasPermission('leave.approvals.view_all')
            || $this->userHasPermission('leave.approvals.view')
            || $this->userHasPermission('leave.approvals.approve')
            || $this->userHasPermission('leave.approvals.reject');

        if ($isHrOrAdmin || $this->canViewAll('leave.approvals.view_all') || $this->userHasPermission('leave.approvals.view')) {
            return;
        }

        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $employee = EmployeeM::find($leaveRequest->employee_id);
        $managerEmpId = $employee?->reporting_manager_employee_id ?: $leaveRequest->reporting_manager_employee_id;
        $isAssignedManager = ($supervisorEmpId && $managerEmpId && (int) $supervisorEmpId === (int) $managerEmpId);
        $isTeamMember = in_array((int) $leaveRequest->employee_id, $this->teamEmployeeIds(false), true);

        if ($isAssignedManager || $isTeamMember) {
            return;
        }

        abort(403, 'Unauthorized. You can only approve/reject leave requests for your assigned reporting team.');
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
