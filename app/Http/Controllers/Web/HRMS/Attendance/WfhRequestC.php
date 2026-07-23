<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Services\HRMS\Attendance\WfhRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WfhRequestC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private WfhRequestService $service)
    {
    }

    public function index(Request $request)
    {
        $canView = $this->userHasPermission('attendance.wfh.view');
        $canOwn = $this->userHasPermission('attendance.wfh.own');
        abort_unless($canView || $canOwn, 403);

        $query = $this->employeeJoinedQuery('wfh_requests');

        if (! $canView && $canOwn) {
            $ownEmployeeId = $this->ownEmployeeId();
            abort_unless($ownEmployeeId, 403);
            $query->where('wfh_requests.employee_id', $ownEmployeeId);
        }

        $this->applyCommonFilters($query, $request, [
            'dateColumn' => 'wfh_requests.request_date',
            'filterMap' => [
                'employee_id' => 'wfh_requests.employee_id',
                'status' => 'wfh_requests.status',
                'request_type' => 'wfh_requests.request_type',
                'reason_category' => 'wfh_requests.reason_category',
            ],
        ]);

        $statsQuery = clone $query;

        $rows = $query->latest('wfh_requests.id')->paginate(50);
        $approverIds = $rows->getCollection()
            ->flatMap(fn ($row) => [(int) ($row->manager_approved_by ?? 0), (int) ($row->hr_approved_by ?? 0), (int) ($row->assigned_by ?? 0)])
            ->filter()
            ->unique()
            ->values()
            ->all();
        $approverMap = empty($approverIds)
            ? []
            : UserM::query()->whereIn('id', $approverIds)->pluck('name', 'id')->toArray();

        $policy = $this->service->policy();
        $monthlyLimit = (int) ($policy['wfh_monthly_limit'] ?? 2);

        $rows->getCollection()->transform(function ($row) use ($approverMap, $monthlyLimit) {
            $fromDate = $row->from_date ?: $row->request_date;
            $toDate = $row->to_date ?: $fromDate;

            $fromCarbon = \Carbon\Carbon::parse($fromDate);
            $toCarbon = \Carbon\Carbon::parse($toDate);

            $row->from_date_formatted = $fromCarbon->format('d M Y');
            $row->to_date_formatted = $toCarbon->format('d M Y');
            $row->date_range_label = ($row->from_date_formatted === $row->to_date_formatted)
                ? $row->from_date_formatted
                : ($row->from_date_formatted . ' – ' . $row->to_date_formatted);

            $row->total_days = (int) ($row->total_days ?: 1);
            $row->working_days = (int) ($row->working_days ?: 1);
            $row->weekoff_days = (int) ($row->weekoff_days ?: 0);
            $row->holiday_days = (int) ($row->holiday_days ?: 0);

            $meta = $this->remarksMeta((string) ($row->remarks ?? ''));
            $row->source_label = $this->sourceLabel($row, $meta);
            $row->approval_stage = ucwords(str_replace('_', ' ', (string) ($row->status ?? 'pending')));

            $approvedById = (int) ($row->hr_approved_by ?: $row->manager_approved_by ?: $row->assigned_by ?: 0);
            $row->approved_by_label = $approvedById > 0 ? ($approverMap[$approvedById] ?? ('User #' . $approvedById)) : '-';
            $assignedById = (int) ($row->assigned_by ?? 0);
            $row->assigned_by_label = $assignedById > 0 ? ($approverMap[$assignedById] ?? ('User #' . $assignedById)) : '-';

            $row->monthly_quota = $monthlyLimit;
            $row->already_approved_quota = $this->service->approvedQuotaCountForEmployee((int) $row->employee_id, (int) $fromCarbon->month, (int) $fromCarbon->year, [(int) $row->id]);
            $row->requested_working_days = (int) $row->working_days;
            $row->exceeds_quota = (bool) ($row->counts_in_monthly_quota ?? true) && (($row->already_approved_quota + $row->requested_working_days) > $row->monthly_quota);

            return $row;
        });

        return view('hrms.attendance.wfh.index', [
            'rows' => $rows,
            'employees' => $this->employeeOptions(),
            'departments' => \App\Models\HRMS\Department\DepartmentM::query()->orderBy('name')->get(['id', 'name']),
            'designations' => \App\Models\HRMS\Designation\DesignationM::query()->orderBy('name')->get(['id', 'name']),
            'accesses' => $this->accesses(),
            'active' => 'attendance',
            'canApprove' => $this->userHasPermission('attendance.wfh.approve'),
            'canReject' => $this->userHasPermission('attendance.wfh.reject'),
            'canMarkLwp' => $this->userHasPermission('attendance.wfh.mark_lwp'),
            'canAssign' => $this->userHasPermission('attendance.wfh.assign'),
            'canOverrideQuota' => $this->canOverrideQuota(),
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'pending' => (clone $statsQuery)->whereIn('wfh_requests.status', ['pending', 'manager_approved', 'hr_approved'])->count(),
                'approved' => (clone $statsQuery)->where('wfh_requests.status', 'approved')->count(),
                'rejected' => (clone $statsQuery)->where('wfh_requests.status', 'rejected')->count(),
                'company_assigned' => (clone $statsQuery)->where('wfh_requests.request_type', 'company_assigned_wfh')->count(),
                'lwp' => (clone $statsQuery)->where('wfh_requests.payroll_impact', 'lwp')->count(),
            ],
        ]);
    }

    public function approve(int $id, Request $request)
    {
        $row = WfhRequestM::findOrFail($id);
        $partialRange = null;
        if ($request->filled('approved_from_date') && $request->filled('approved_to_date')) {
            $partialRange = [
                'approved_from_date' => $request->input('approved_from_date'),
                'approved_to_date' => $request->input('approved_to_date'),
            ];
        }

        $canOverride = $this->canOverrideQuota();
        $allowOverride = $canOverride && ($request->boolean('override_quota') || $request->has('override_quota'));

        try {
            $this->service->approve($row, (int) $this->actorId(), $partialRange, $allowOverride || $canOverride);
            return back()->with('success', 'WFH request approved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: $e->getMessage();
            return back()->with('error', $msg)->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(int $id, Request $request)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
        $row = WfhRequestM::findOrFail($id);
        $this->service->reject($row, (int) $this->actorId(), $data['rejection_reason']);
        return back()->with('success', 'WFH request rejected.');
    }

    public function markLwp(int $id, Request $request)
    {
        abort_unless($this->userHasPermission('attendance.wfh.mark_lwp'), 403);

        $data = $request->validate([
            'lwp_reason' => 'required|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $row = WfhRequestM::findOrFail($id);
        $this->service->markAsLwp($row, (int) $this->actorId(), $data['lwp_reason'], $data['remarks'] ?? null);

        return back()->with('success', 'WFH request marked as LWP.');
    }

    public function assign(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.wfh.assign'), 403);

        $payload = $request->validate([
            'assignment_scope' => 'required|in:single,multiple,department,designation,all',
            'employee_id' => 'nullable|integer|exists:employees_new,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees_new,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'designation_id' => 'nullable|integer|exists:designations,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'reason' => 'required|string|max:2000',
            'work_report_required' => 'nullable|boolean',
            'counts_in_monthly_quota' => 'nullable|boolean',
            'payroll_impact' => 'nullable|in:none,lwp',
            'reason_category' => 'nullable|in:normal,personal_reason,manager_assigned,internet_issue,electricity_issue,other,company_assigned',
        ]);

        $result = $this->service->assignCompanyWfh(
            $payload,
            (int) $this->actorId(),
            $this->actorSource()
        );

        return back()->with('success', "Company-assigned WFH processed. Created: {$result['created']}, Skipped duplicates: {$result['skipped']}.");
    }

    public function myWfh(Request $request)
    {
        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            abort(403, 'Employee profile not found.');
        }

        $query = $this->employeeJoinedQuery('wfh_requests')
            ->where('wfh_requests.employee_id', $employee->id);

        $this->applyCommonFilters($query, $request, [
            'dateColumn' => 'wfh_requests.request_date',
            'filterMap' => [
                'status' => 'wfh_requests.status',
                'reason_category' => 'wfh_requests.reason_category',
            ],
        ]);

        $rows = $query->latest('wfh_requests.id')->paginate(20);

        $approverIds = $rows->getCollection()
            ->flatMap(fn ($row) => [(int) ($row->manager_approved_by ?? 0), (int) ($row->hr_approved_by ?? 0), (int) ($row->assigned_by ?? 0)])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $approverMap = empty($approverIds)
            ? []
            : UserM::query()->whereIn('id', $approverIds)->pluck('name', 'id')->toArray();

        $rows->getCollection()->transform(function ($row) use ($approverMap) {
            $fromDate = $row->from_date ?: $row->request_date;
            $toDate = $row->to_date ?: $fromDate;

            $fromCarbon = \Carbon\Carbon::parse($fromDate);
            $toCarbon = \Carbon\Carbon::parse($toDate);

            $row->from_date_formatted = $fromCarbon->format('d M Y');
            $row->to_date_formatted = $toCarbon->format('d M Y');
            $row->date_range_label = ($row->from_date_formatted === $row->to_date_formatted)
                ? $row->from_date_formatted
                : ($row->from_date_formatted . ' – ' . $row->to_date_formatted);

            $row->total_days = (int) ($row->total_days ?: 1);
            $row->working_days = (int) ($row->working_days ?: 1);
            $row->weekoff_days = (int) ($row->weekoff_days ?: 0);
            $row->holiday_days = (int) ($row->holiday_days ?: 0);

            $meta = $this->remarksMeta((string) ($row->remarks ?? ''));
            $row->source_label = $this->sourceLabel($row, $meta);
            $row->approval_stage = ucwords(str_replace('_', ' ', (string) ($row->status ?? 'pending')));

            $approvedById = (int) ($row->hr_approved_by ?: $row->manager_approved_by ?: $row->assigned_by ?: 0);
            $row->approved_by_label = $approvedById > 0 ? ($approverMap[$approvedById] ?? ('User #' . $approvedById)) : '-';
            $assignedById = (int) ($row->assigned_by ?? 0);
            $row->assigned_by_label = $assignedById > 0 ? ($approverMap[$assignedById] ?? ('User #' . $assignedById)) : '-';

            return $row;
        });

        $rows->getCollection()->transform(function ($row) use ($approverMap) {
            $approvedById = (int) ($row->hr_approved_by ?: $row->manager_approved_by ?: $row->assigned_by ?: 0);
            $row->approved_by_label = $approvedById > 0 ? ($approverMap[$approvedById] ?? ('User #' . $approvedById)) : '-';
            $assignedById = (int) ($row->assigned_by ?? 0);
            $row->assigned_by_label = $assignedById > 0 ? ($approverMap[$assignedById] ?? ('User #' . $assignedById)) : '-';
            return $row;
        });

        $now = now();
        $balance = $this->service->balance($employee, (int) $now->month, (int) $now->year);
        $isPermanentWfh = $employee ? $employee->isPermanentWfh() : false;

        return view('hrms.attendance.wfh.my-wfh', [
            'rows' => $rows,
            'balance' => $balance,
            'isPermanentWfh' => $isPermanentWfh,
            'accesses' => $this->accesses(),
            'active' => 'attendance',
        ]);
    }

    public function calculateDays(Request $request)
    {
        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 403);
        }

        $fromDate = $request->input('from_date') ?: $request->input('date_from') ?: $request->input('request_date');
        $toDate = $request->input('to_date') ?: $request->input('date_to') ?: $fromDate;

        if (! $fromDate || ! $toDate) {
            return response()->json(['status' => false, 'message' => 'From Date and To Date are required.'], 422);
        }

        try {
            $stats = $this->service->calculateRangeStats($employee, (string) $fromDate, (string) $toDate);
            return response()->json(['status' => true, 'data' => $stats]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage()], 422);
        }
    }

    public function apply(Request $request)
    {
        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            abort(403, 'Employee profile not found.');
        }

        $payload = $request->validate([
            'request_date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'reason_category' => 'required|string|max:100',
            'reason' => 'required|string|max:2000',
        ]);

        try {
            $this->service->apply($employee, $payload);
            return redirect()->route('hrms.attendance.my-wfh.index')->with('success', 'WFH request submitted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function cancel(int $id)
    {
        $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            abort(403, 'Employee profile not found.');
        }

        $requestRecord = WfhRequestM::where('employee_id', $employee->id)->findOrFail($id);

        try {
            $this->service->cancel($requestRecord);
            return redirect()->route('hrms.attendance.my-wfh.index')->with('success', 'WFH request cancelled successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    private function actorSource(): string
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'hasRole')) {
            if ($user->hasRole('super_admin') || $user->hasRole('admin')) return 'admin_assigned';
            if ($user->hasRole('hr_admin')) return 'hr_assigned';
            if ($user->hasRole('manager')) return 'manager_assigned';
        }
        return 'company_assigned';
    }

    private function remarksMeta(string $remarks): array
    {
        $decoded = json_decode($remarks, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function sourceLabel(object $row, array $meta): string
    {
        $source = strtolower((string) ($meta['source'] ?? ''));
        $reasonCategory = strtolower((string) ($row->reason_category ?? ''));
        $requestType = strtolower((string) ($row->request_type ?? ''));

        if ($source === 'admin_assigned') return 'Admin Assigned';
        if ($source === 'hr_assigned') return 'HR Assigned';
        if ($source === 'manager_assigned') return 'Manager Assigned';
        if ($source === 'company_assigned') return 'Company Assigned';

        if ($requestType === 'company_assigned_wfh' || $reasonCategory === 'company_assigned') return 'Company Assigned';
        if ($reasonCategory === 'manager_assigned') return 'Manager Assigned';

        return 'Employee Requested';
    }

    private function canOverrideQuota(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->userHasPermission('attendance.wfh.override_quota')) {
            return true;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('hr_admin')) {
                return true;
            }
        }

        if ($this->userHasPermission('attendance.wfh.approve') && (! method_exists($user, 'hasRole') || ! $user->hasRole('manager'))) {
            return true;
        }

        return false;
    }
}
