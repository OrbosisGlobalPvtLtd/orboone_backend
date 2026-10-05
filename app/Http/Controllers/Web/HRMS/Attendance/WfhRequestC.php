<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Designation\DesignationM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\WfhRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WfhRequestC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private WfhRequestService $service)
    {
    }

    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || in_array((int) ($user->system_role_id ?? $user->role_id ?? 0), [1, 2, 3], true);

        $teamEmpIds = $this->teamEmployeeIds(false);
        $isManager = (! empty($teamEmpIds) || (method_exists($user, 'hasRole') && $user->hasRole('manager'))) && ! $isHrOrAdmin;
        $isEmployee = ! $isHrOrAdmin && ! $isManager;

        $canOwn = $this->userHasPermission('attendance.wfh.own')
            || (method_exists($user, 'isEmployee') && $user->isEmployee())
            || $this->userHasPermission('attendance.wfh.view')
            || in_array((int) ($user->system_role_id ?? $user->role_id ?? 0), [7, 8], true);

        abort_unless($isHrOrAdmin || $isManager || $canOwn, 403);

        $ownEmployeeId = $this->ownEmployeeId();
        $query = $this->employeeJoinedQuery('wfh_requests');

        if ($isHrOrAdmin) {
            // Global view for Super Admin / Admin / HR Admin
        } elseif ($isManager) {
            // Scoped strictly to supervised team members + own
            $allowedEmpIds = array_merge($teamEmpIds, array_filter([$ownEmployeeId]));
            $query->whereIn('wfh_requests.employee_id', array_filter($allowedEmpIds));
        } else {
            // Self only
            abort_unless($ownEmployeeId, 403, 'Employee profile not found.');
            $query->where('wfh_requests.employee_id', $ownEmployeeId);
            $request->merge(['employee_id' => $ownEmployeeId]);
        }

        $this->applyCommonFilters($query, $request, [
            'filterMap' => [
                'employee_id' => 'wfh_requests.employee_id',
                'status' => 'wfh_requests.status',
                'request_type' => 'wfh_requests.request_type',
                'reason_category' => 'wfh_requests.reason_category',
            ],
        ]);

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');
        $currentMonthKey = Carbon::now('Asia/Kolkata')->format('Y-m');
        $month = $request->input('month');

        // Default to current month if no filter params specified and not a reset request
        if ($month === null && ! $fromDate && ! $toDate && ! $request->has('reset')) {
            $month = $currentMonthKey;
            $request->merge(['month' => $month]);
        }

        if ($fromDate || $toDate) {
            if ($fromDate) {
                $query->where(function ($q) use ($fromDate) {
                    $q->whereDate('wfh_requests.to_date', '>=', $fromDate)
                      ->orWhere(function ($sub) use ($fromDate) {
                          $sub->whereNull('wfh_requests.to_date')
                              ->whereDate('wfh_requests.request_date', '>=', $fromDate);
                      });
                });
            }
            if ($toDate) {
                $query->where(function ($q) use ($toDate) {
                    $q->whereDate('wfh_requests.from_date', '<=', $toDate)
                      ->orWhere(function ($sub) use ($toDate) {
                          $sub->whereNull('wfh_requests.from_date')
                              ->whereDate('wfh_requests.request_date', '<=', $toDate);
                      });
                });
            }
        } elseif ($month && $month !== 'all' && $month !== 'custom') {
            try {
                $monthDate = Carbon::parse($month . '-01');
                $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
                $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();

                if ($month === $currentMonthKey) {
                    // Current month selected: show current month requests + all future applied requests
                    $query->where(function ($q) use ($startOfMonth) {
                        $q->whereDate('wfh_requests.to_date', '>=', $startOfMonth)
                          ->orWhereDate('wfh_requests.from_date', '>=', $startOfMonth)
                          ->orWhereDate('wfh_requests.request_date', '>=', $startOfMonth)
                          ->orWhereDate('wfh_requests.created_at', '>=', $startOfMonth);
                    });
                } else {
                    $query->where(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where(function ($sub) use ($startOfMonth, $endOfMonth) {
                            $sub->whereDate('wfh_requests.from_date', '<=', $endOfMonth)
                                ->whereDate('wfh_requests.to_date', '>=', $startOfMonth);
                        })->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                            $sub->whereNull('wfh_requests.from_date')
                                ->whereBetween('wfh_requests.request_date', [$startOfMonth, $endOfMonth]);
                        })->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                            $sub->whereBetween(DB::raw('DATE(wfh_requests.created_at)'), [$startOfMonth, $endOfMonth]);
                        });
                    });
                }
            } catch (\Throwable $e) {
                // Ignore parse errors
            }
        }

        $statsQuery = clone $query;

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $rows = $query->latest('wfh_requests.id')->paginate($perPage)->withQueryString();
        $approverIds = $rows->getCollection()
            ->flatMap(fn ($row) => [(int) ($row->manager_approved_by ?? 0), (int) ($row->hr_approved_by ?? 0), (int) ($row->assigned_by ?? 0)])
            ->filter()
            ->unique()
            ->values()
            ->all();
        $approverMap = empty($approverIds)
            ? []
            : User::query()->whereIn('id', $approverIds)->pluck('name', 'id')->toArray();

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

        if ($isHrOrAdmin) {
            $allEmployees = $this->employeeOptions();
            $employees = $allEmployees->filter(function ($emp) {
                $mode = strtolower(trim((string) ($emp->work_mode ?? 'wfo')));
                return $mode === 'wfo' || (! in_array($mode, ['wfh', 'permanent_wfh', 'permanent wfh'], true));
            })->values();
        } elseif ($isManager) {
            $allowedEmpIds = array_merge($teamEmpIds, array_filter([$ownEmployeeId]));
            $employees = DB::table('employees_new')
                ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
                ->whereIn('employees_new.id', $allowedEmpIds)
                ->where(function ($q) {
                    $q->where('employees_new.work_mode', 'wfo')
                      ->orWhere(function ($sub) {
                          $sub->whereNull('employees_new.work_mode')
                              ->orWhereRaw("LOWER(TRIM(employees_new.work_mode)) NOT IN ('wfh', 'permanent_wfh', 'permanent wfh')");
                      });
                })
                ->select(
                    'employees_new.id',
                    'employees_new.employee_code',
                    'employees_new.work_mode',
                    DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name")
                )
                ->orderByRaw("COALESCE(users.name, employees_new.employee_code)")
                ->get();
        } else {
            $employees = DB::table('employees_new')
                ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
                ->where('employees_new.id', $ownEmployeeId)
                ->select(
                    'employees_new.id',
                    'employees_new.employee_code',
                    'employees_new.work_mode',
                    DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name")
                )
                ->get();
        }

        $monthOptions = [
            'all' => 'All Months',
            'custom' => 'Custom Date Range',
        ];
        $cursorMonth = Carbon::now('Asia/Kolkata')->addMonths(2);
        for ($i = 0; $i < 15; $i++) {
            $m = $cursorMonth->copy()->subMonths($i);
            $monthOptions[$m->format('Y-m')] = $m->format('F Y');
        }

        return view('hrms.attendance.wfh.index', [
            'rows' => $rows,
            'employees' => $employees,
            'monthOptions' => $monthOptions,
            'activeMonth' => $month ?? $currentMonthKey,
            'departments' => DepartmentM::query()->orderBy('name')->get(['id', 'name']),
            'designations' => DesignationM::query()->orderBy('name')->get(['id', 'name']),
            'accesses' => $this->accesses(),
            'active' => 'attendance',
            'isSuperAdmin' => $isSuperAdmin,
            'isHrOrAdmin' => $isHrOrAdmin,
            'isManager' => $isManager,
            'isEmployee' => $isEmployee,
            'userEmpId' => $ownEmployeeId,
            'canApprove' => $isHrOrAdmin || ($isManager && $this->userHasPermission('attendance.wfh.approve')),
            'canReject' => $isHrOrAdmin || ($isManager && $this->userHasPermission('attendance.wfh.reject')),
            'canMarkLwp' => $isHrOrAdmin && $this->userHasPermission('attendance.wfh.mark_lwp'),
            'canAssign' => $isHrOrAdmin && $this->userHasPermission('attendance.wfh.assign'),
            'canOverrideQuota' => $isHrOrAdmin && $this->canOverrideQuota(),
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'pending' => (clone $statsQuery)->whereIn('wfh_requests.status', ['pending', 'manager_approved'])->count(),
                'approved' => (clone $statsQuery)->where('wfh_requests.status', 'approved')->count(),
                'rejected' => (clone $statsQuery)->where('wfh_requests.status', 'rejected')->count(),
                'company_assigned' => (clone $statsQuery)->where('wfh_requests.request_type', 'company_assigned_wfh')->count(),
                'lwp' => (clone $statsQuery)->where('wfh_requests.payroll_impact', 'lwp')->count(),
            ],
        ]);
    }

    public function approve(int $id, Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || $this->userHasPermission('attendance.wfh.approve');

        $supervisorEmpId = $this->ownEmployeeId();
        $row = WfhRequestM::findOrFail($id);
        $employee = EmployeeM::find($row->employee_id);
        $managerEmpId = $employee?->reporting_manager_employee_id;
        $hasManager = ! empty($managerEmpId);
        $isAssignedManager = ($supervisorEmpId && $managerEmpId && (int) $supervisorEmpId === (int) $managerEmpId);
        $isManagerApproved = ! empty($row->manager_approved_at) || $row->status === 'manager_approved';

        // Stage 1: Reporting Manager Approval
        if ($isAssignedManager && ! $isHrOrAdmin) {
            if ($isManagerApproved) {
                return back()->with('error', 'You have already approved this request at Manager stage. Awaiting HR Admin final approval.');
            }
            $note = $request->input('remarks');
            try {
                $this->service->approveManagerStage($row, (int) $this->actorId(), $note);
                return back()->with('success', 'WFH request approved at Manager stage. Sent to HR Admin for final approval.');
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // Stage 2: HR Admin / Super Admin Final Approval
        if ($isHrOrAdmin) {
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
                return back()->with('success', 'WFH request approved & finalized.');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $msg = collect($e->errors())->flatten()->first() ?: $e->getMessage();
                return back()->with('error', $msg)->withErrors($e->errors());
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        abort(403, 'Unauthorized to approve this WFH request.');
    }

    public function reject(int $id, Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || $this->userHasPermission('attendance.wfh.reject');

        $supervisorEmpId = $this->ownEmployeeId();
        $row = WfhRequestM::findOrFail($id);
        $employee = EmployeeM::find($row->employee_id);
        $managerEmpId = $employee?->reporting_manager_employee_id;
        $isAssignedManager = ($supervisorEmpId && $managerEmpId && (int) $supervisorEmpId === (int) $managerEmpId);

        abort_unless($isSuperAdmin || $isHrOrAdmin || $isAssignedManager, 403);

        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
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

    public function update(Request $request, int $id)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || in_array((int) ($user->system_role_id ?? $user->role_id ?? 0), [1, 2, 3], true);

        $record = WfhRequestM::findOrFail($id);
        $ownEmployeeId = $this->ownEmployeeId();

        if (! $isHrOrAdmin) {
            abort_unless((int) $record->employee_id === (int) $ownEmployeeId, 403, 'Unauthorized to edit this WFH request.');
            if (! in_array($record->status, ['pending', 'manager_approved'], true)) {
                return back()->with('error', 'Only pending requests can be modified.');
            }
        }

        $payload = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'request_type' => 'nullable|string|max:100',
            'reason_category' => 'required|string|max:100',
            'reason' => 'required|string|max:2000',
            'status' => 'nullable|string|in:pending,approved,rejected,cancelled',
            'payroll_impact' => 'nullable|string|in:none,lwp',
        ]);

        $employee = EmployeeM::find($record->employee_id);
        if ($employee) {
            try {
                $stats = $this->service->calculateRangeStats($employee, (string) $payload['from_date'], (string) $payload['to_date']);
                $record->from_date = $payload['from_date'];
                $record->to_date = $payload['to_date'];
                $record->request_date = $payload['from_date'];
                $record->total_days = $stats['total_days'] ?? 1;
                $record->working_days = $stats['working_days'] ?? 1;
                $record->weekoff_days = $stats['weekoff_days'] ?? 0;
                $record->holiday_days = $stats['holiday_days'] ?? 0;
            } catch (\Throwable $e) {
                $record->from_date = $payload['from_date'];
                $record->to_date = $payload['to_date'];
                $record->request_date = $payload['from_date'];
            }
        } else {
            $record->from_date = $payload['from_date'];
            $record->to_date = $payload['to_date'];
            $record->request_date = $payload['from_date'];
        }

        $record->reason_category = $payload['reason_category'];
        $record->reason = $payload['reason'];
        if (!empty($payload['request_type'])) {
            $record->request_type = $payload['request_type'];
        }

        if ($isHrOrAdmin) {
            if (!empty($payload['status'])) {
                $record->status = $payload['status'];
            }
            if (!empty($payload['payroll_impact'])) {
                $record->payroll_impact = $payload['payroll_impact'];
            }
        }

        $record->save();

        return back()->with('success', 'WFH request updated successfully.');
    }

    public function assign(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.wfh.assign'), 403);

        $payload = $request->validate([
            'assignment_scope' => 'required|in:single,multiple,all',
            'employee_id' => 'required_if:assignment_scope,single|nullable|integer|exists:employees_new,id',
            'employee_ids' => 'required_if:assignment_scope,multiple|nullable|array|min:1',
            'employee_ids.*' => 'integer|exists:employees_new,id',
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
        $employee = EmployeeM::where('user_id', Auth::id())->first();
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
            : User::query()->whereIn('id', $approverIds)->pluck('name', 'id')->toArray();

        $rows->getCollection()->transform(function ($row) use ($approverMap) {
            $fromDate = $row->from_date ?: $row->request_date;
            $toDate = $row->to_date ?: $fromDate;

            $fromCarbon = Carbon::parse($fromDate);
            $toCarbon = Carbon::parse($toDate);

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
        $employee = EmployeeM::where('user_id', Auth::id())->first();
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
        $employee = EmployeeM::where('user_id', Auth::id())->first();
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
            return redirect()->back()->with('success', 'WFH request submitted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function cancel(int $id)
    {
        $employee = EmployeeM::where('user_id', Auth::id())->first();
        if (! $employee) {
            abort(403, 'Employee profile not found.');
        }

        $requestRecord = WfhRequestM::where('employee_id', $employee->id)->findOrFail($id);

        try {
            $this->service->cancel($requestRecord);
            return redirect()->back()->with('success', 'WFH request cancelled successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    private function actorSource(): string
    {
        /** @var User|null $user */
        $user = Auth::user();
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
        /** @var User|null $user */
        $user = Auth::user();
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
