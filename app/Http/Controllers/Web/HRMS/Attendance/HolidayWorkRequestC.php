<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\CompOffService;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HRMS\Attendance\AttendanceM;

class HolidayWorkRequestC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $scope = $this->resolveUserScope();
        $isHrOrAdmin = $scope['isHrOrAdmin'];
        $isManager = $scope['isManager'];
        $isEmployee = $scope['isEmployee'];
        $canApprove = $scope['canApprove'];
        $canReject = $scope['canReject'];
        $currentEmployee = $scope['currentEmployee'];
        $ownEmployeeId = $scope['ownEmployeeId'];

        $query = $this->employeeJoinedQuery('holiday_work_requests')
            ->leftJoin('users as approvers', 'approvers.id', '=', 'holiday_work_requests.approved_by_user_id')
            ->addSelect('approvers.name as approved_by_name')
            ->whereNull('holiday_work_requests.deleted_at');

        // Role-based backend database scoping & employee filter
        if ($isHrOrAdmin) {
            if ($request->filled('employee_id') && $request->input('employee_id') !== 'all') {
                $query->where('holiday_work_requests.employee_id', (int) $request->input('employee_id'));
            }
        } elseif ($isManager) {
            $teamIds = $this->teamEmployeeIds(true);
            $query->whereIn('holiday_work_requests.employee_id', !empty($teamIds) ? $teamIds : [-1]);
            if ($request->filled('employee_id') && $request->input('employee_id') !== 'all' && in_array((int) $request->input('employee_id'), $teamIds, true)) {
                $query->where('holiday_work_requests.employee_id', (int) $request->input('employee_id'));
            }
        } else {
            abort_unless($ownEmployeeId, 403, 'Employee profile not linked to user account.');
            $query->where('holiday_work_requests.employee_id', $ownEmployeeId);
        }

        // Apply Server-Side Filters
        $this->applyCommonFilters($query, $request, [
            'filterMap' => [
                'status' => 'holiday_work_requests.status',
                'work_type' => 'holiday_work_requests.work_type',
                'work_mode' => 'holiday_work_requests.work_mode',
            ],
        ]);

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');
        $month = $request->input('month');

        if ($month === null && !$fromDate && !$toDate && !$request->has('reset')) {
            $month = now()->format('Y-m');
        }

        if ($fromDate || $toDate) {
            if ($fromDate) {
                $query->whereDate('holiday_work_requests.worked_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('holiday_work_requests.worked_date', '<=', $toDate);
            }
        } elseif ($month && $month !== 'all' && $month !== 'custom') {
            try {
                $monthDate = Carbon::parse($month . '-01');
                $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
                $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();
                $query->whereBetween('holiday_work_requests.worked_date', [$startOfMonth, $endOfMonth]);
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('employees_new.employee_code', 'like', "%{$search}%")
                  ->orWhere('holiday_work_requests.reason', 'like', "%{$search}%")
                  ->orWhere('holiday_work_requests.work_type', 'like', "%{$search}%");
            });
        }

        // Server-Side Show Entries (Pagination Limit)
        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $rows = $query->latest('holiday_work_requests.id')->paginate($perPage)->withQueryString();

        return view('hrms.attendance.holiday-work.index', $this->pageData(
            $rows,
            $isHrOrAdmin,
            $isManager,
            $isEmployee,
            $canApprove,
            $canReject,
            $currentEmployee
        ));
    }

    public function store(Request $request)
    {
        $scope = $this->resolveUserScope();
        $isHrOrAdmin = $scope['isHrOrAdmin'];
        $ownEmployeeId = $scope['ownEmployeeId'];

        $rules = [
            'worked_date' => 'required|date',
            'work_type' => 'required|string|in:holiday_work,weekoff_work',
            'work_mode' => 'nullable|string|in:wfo,wfh,WFO,WFH',
            'reason' => 'required|string|max:1000',
        ];

        if ($isHrOrAdmin) {
            $rules['employee_id'] = 'required|exists:employees_new,id';
            $rules['status'] = 'nullable|in:pending,approved,rejected,cancelled';
        }

        $data = $request->validate($rules);

        // Security: Lock employee_id and status for normal employees
        $targetEmployeeId = $isHrOrAdmin ? (int) $data['employee_id'] : $ownEmployeeId;
        abort_unless($targetEmployeeId, 403, 'Employee record not found.');

        $status = $isHrOrAdmin ? ($data['status'] ?? 'pending') : 'pending';
        $workMode = strtolower($data['work_mode'] ?? 'wfo');

        // Prevent duplicate request for the same date if already pending or approved
        $exists = HolidayWorkRequestM::where('employee_id', $targetEmployeeId)
            ->whereDate('worked_date', $data['worked_date'])
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A request for this date already exists and is pending/approved.');
        }

        $row = HolidayWorkRequestM::create([
            'employee_id' => $targetEmployeeId,
            'worked_date' => $data['worked_date'],
            'work_type' => $data['work_type'],
            'work_mode' => $workMode,
            'reason' => $data['reason'],
            'status' => $status,
        ]);

        if ($row->status === 'pending') {
            try {
                $employee = EmployeeM::find($row->employee_id);
                if ($employee) {
                    $notificationService = app(NotificationS::class);
                    $employeeName = $employee->display_name;
                    $workTypeLabel = str_contains(strtolower($row->work_type), 'weekoff') ? 'Weekoff' : 'Holiday';
                    $formattedDate = Carbon::parse($row->worked_date)->format('d M Y');
                    
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
                                'dates' => $row->worked_date ? Carbon::parse($row->worked_date)->toDateString() : '',
                                'work_type' => $row->work_type,
                                'action_url' => $actionUrl,
                                'route_name' => 'hrms.attendance.holiday_work.index',
                                'route_params' => [],
                            ]
                        );
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to send web submission notification for request #{$row->id}: " . $e->getMessage());
                }
            }

            return back()->with('success', 'Holiday work request saved successfully.');
        }

        public function update(Request $request, int|string $id)
        {
            $scope = $this->resolveUserScope();
            $isHrOrAdmin = $scope['isHrOrAdmin'];
            $ownEmployeeId = $scope['ownEmployeeId'];

            $record = HolidayWorkRequestM::findOrFail($id);

            // Security check for normal employees
            if (!$isHrOrAdmin) {
                abort_unless((int) $record->employee_id === $ownEmployeeId, 403, 'Unauthorized to modify another employee record.');
                if ($record->status !== 'pending') {
                    return back()->with('error', 'Only pending requests can be modified.');
                }
            }

            $rules = [
                'worked_date' => 'required|date',
                'work_type' => 'required|string|in:holiday_work,weekoff_work',
                'work_mode' => 'nullable|string|in:wfo,wfh,WFO,WFH',
                'reason' => 'required|string|max:1000',
            ];

            if ($isHrOrAdmin) {
                $rules['employee_id'] = 'required|exists:employees_new,id';
                $rules['status'] = 'nullable|in:pending,approved,rejected,cancelled';
            }

            $data = $request->validate($rules);

            $updateData = [
                'worked_date' => $data['worked_date'],
                'work_type' => $data['work_type'],
                'work_mode' => strtolower($data['work_mode'] ?? 'wfo'),
                'reason' => $data['reason'],
                'updated_at' => now(),
            ];

            if ($isHrOrAdmin) {
                $updateData['employee_id'] = (int) $data['employee_id'];
                if (isset($data['status'])) {
                    $updateData['status'] = $data['status'];
                }
            }

            DB::table('holiday_work_requests')->where('id', $id)->update($updateData);

            return back()->with('success', 'Holiday work request updated.');
        }

        public function approve(int|string $id)
        {
            $scope = $this->resolveUserScope();
            $isHrOrAdmin = $scope['isHrOrAdmin'];
            $canApprove = $scope['canApprove'];

            abort_unless($canApprove, 403, 'Unauthorized to approve holiday work requests.');

            $request = HolidayWorkRequestM::with('employee')->findOrFail($id);

            if (!$isHrOrAdmin && $this->userHasPermission('attendance.holiday_work.view_team')) {
                $teamIds = $this->teamEmployeeIds(true);
                abort_unless(in_array((int) $request->employee_id, $teamIds, true), 403, 'Unauthorized to approve requests outside your team.');
            }

            $notes = request('notes', request('remarks'));
            $updateData = [
                'status' => 'approved',
                'approved_by_user_id' => $this->actorId(),
                'approved_at' => now(),
            ];
            if ($notes) {
                $updateData['notes'] = $notes;
            }

            $request->update($updateData);

            $request = $request->fresh();

            // Sync attendance status if it exists
            if ($request->attendance_id) {
                $attendance = AttendanceM::find($request->attendance_id);
                if ($attendance) {
                    $attendance->is_blocked = false;
                    $attendance->is_punch_blocked = false;
                    $attendance->is_lwp = false;
                    $attendance->lwp_reason = null;
                    $attendance->save();
                }
            }

            // Reconcile Comp-Off credit
            app(CompOffService::class)->reconcileRequest($request, $this->actorId());

            // Notify Employee
            $userId = $request->employee ? $request->employee->user_id : null;
            if ($userId) {
                try {
                    $formattedDate = Carbon::parse($request->worked_date)->format('d M Y');
                    $title = "Work Request Approved";
                    $message = "Your work request for {$formattedDate} has been approved.";
                    $actionUrl = route('hrms.attendance.holiday_work.index', [], false);
                    
                    app(NotificationS::class)->notifyEmployee(
                        $title,
                        $message,
                        'holiday_work_request_approved',
                        'hrms.attendance.holiday_work.index',
                        [],
                        [
                            'request_id' => $request->id,
                            'dates' => $request->worked_date ? Carbon::parse($request->worked_date)->toDateString() : '',
                            'status' => 'approved',
                            'comp_off_generated' => (bool) $request->comp_off_generated,
                            'action_url' => $actionUrl,
                            'employee_id' => $request->employee_id,
                        ],
                        $userId
                    );
                } catch (\Throwable $e) {
                    Log::error("Failed to send web approval notification to employee: " . $e->getMessage());
                }
            }

            return back()->with('success', 'Holiday work approved.');
        }

        public function reject(int|string $id)
        {
            $scope = $this->resolveUserScope();
            $isHrOrAdmin = $scope['isHrOrAdmin'];
            $canReject = $scope['canReject'];

            abort_unless($canReject, 403, 'Unauthorized to reject holiday work requests.');

            $request = HolidayWorkRequestM::with('employee')->findOrFail($id);

            if (!$isHrOrAdmin && $this->userHasPermission('attendance.holiday_work.view_team')) {
                $teamIds = $this->teamEmployeeIds(true);
                abort_unless(in_array((int) $request->employee_id, $teamIds, true), 403, 'Unauthorized to reject requests outside your team.');
            }

            $rejectionReason = request('rejection_reason', 'Rejected by HR Admin');
            $notes = request('notes', request('remarks'));
            
            $updateData = [
                'status' => 'rejected',
                'approved_by_user_id' => $this->actorId(),
                'approved_at' => now(),
                'rejection_reason' => $rejectionReason,
            ];
            if ($notes) {
                $updateData['notes'] = $notes;
            }

            $request->update($updateData);

            app(CompOffService::class)->reverseRequest($request);

            $userId = $request->employee ? $request->employee->user_id : null;
            if ($userId) {
                try {
                    $formattedDate = Carbon::parse($request->worked_date)->format('d M Y');
                    $title = "Work Request Rejected";
                    $message = "Your work request for {$formattedDate} has been rejected.";
                    $actionUrl = route('hrms.attendance.holiday_work.index', [], false);
                    $reviewerName = Auth::user()?->name ?? 'HR Admin';
                    
                    app(NotificationS::class)->notifyEmployee(
                        $title,
                        $message,
                        'holiday_work_request_rejected',
                    'hrms.attendance.holiday_work.index',
                    [],
                    [
                        'request_id' => $request->id,
                        'dates' => $request->worked_date ? Carbon::parse($request->worked_date)->toDateString() : '',
                        'status' => 'rejected',
                        'rejection_reason' => $rejectionReason,
                        'reviewer_name' => $reviewerName,
                        'action_url' => $actionUrl,
                        'employee_id' => $request->employee_id,
                    ],
                    $userId
                );
            } catch (\Throwable $e) {
                Log::error("Failed to send web rejection notification to employee: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Holiday work rejected.');
    }

    public function destroy(int|string $id)
    {
        $scope = $this->resolveUserScope();
        $isHrOrAdmin = $scope['isHrOrAdmin'];
        $ownEmployeeId = $scope['ownEmployeeId'];

        $request = HolidayWorkRequestM::findOrFail($id);

        if (!$isHrOrAdmin) {
            abort_unless((int) $request->employee_id === $ownEmployeeId, 403, 'Unauthorized to delete another employee record.');
            if ($request->status !== 'pending') {
                return back()->with('error', 'Only pending requests can be deleted.');
            }
        }

        if ($request->status === 'approved') {
            app(CompOffService::class)->reverseRequest($request);
        }

        $request->delete();

        return back()->with('success', 'Holiday work request deleted.');
    }

    private function resolveUserScope(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isEmployeeRole = $user && method_exists($user, 'hasRole') && $user->hasRole('employee') && ! $user->hasRole(['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'manager']);
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');
        $isSuperAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || in_array($roleId, [1, 2], true);
        $isHrOrAdmin = ! $isEmployeeRole && ($isSuperAdmin || in_array($roleId, [1, 2, 3], true) || in_array($roleName, ['admin', 'super_admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true));
        
        $teamSubordinateIds = $this->teamEmployeeIds(false);
        $isManager = ! $isHrOrAdmin && (! empty($teamSubordinateIds) || $this->userHasPermission('attendance.holiday_work.view_team') || (method_exists($user, 'hasRole') && $user->hasRole(['manager', 'lead', 'team_lead'])));

        $canApprove = ! $isEmployeeRole && ($isHrOrAdmin || $isManager || $this->userHasPermission('attendance.holiday_work.approve'));
        $canReject = ! $isEmployeeRole && ($isHrOrAdmin || $isManager || $this->userHasPermission('attendance.holiday_work.reject'));
        $currentEmployee = $this->currentEmployee();
        $ownEmployeeId = $currentEmployee ? (int) $currentEmployee->id : null;
        $isEmployee = $isEmployeeRole || (! $isHrOrAdmin && ! $isManager);

        return [
            'user' => $user,
            'isEmployeeRole' => $isEmployeeRole,
            'isSuperAdmin' => $isSuperAdmin,
            'isHrOrAdmin' => $isHrOrAdmin,
            'canApprove' => $canApprove,
            'canReject' => $canReject,
            'isManager' => $isManager,
            'currentEmployee' => $currentEmployee,
            'ownEmployeeId' => $ownEmployeeId,
            'isEmployee' => $isEmployee,
        ];
    }

    private function pageData(
        mixed $rows,
        bool $isHrOrAdmin,
        bool $isManager,
        bool $isEmployee,
        bool $canApprove,
        bool $canReject,
        mixed $currentEmployee
    ): array {
        // Scoped employees options for filter & create modal
        if ($isHrOrAdmin) {
            $filterEmployees = $this->employeeOptions()->pluck('display_name', 'id')->toArray();
            $createEmployees = $filterEmployees;
        } elseif ($isManager) {
            $teamIds = $this->teamEmployeeIds(true);
            $filterEmployees = DB::table('employees_new')
                ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
                ->whereIn('employees_new.id', !empty($teamIds) ? $teamIds : [-1])
                ->select('employees_new.id', DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name"))
                ->orderByRaw("COALESCE(users.name, employees_new.employee_code)")
                ->pluck('display_name', 'id')
                ->toArray();
            $createEmployees = $currentEmployee ? [$currentEmployee->id => ($currentEmployee->display_name ?? Auth::user()?->name)] : [];
        } else {
            $filterEmployees = $currentEmployee ? [$currentEmployee->id => ($currentEmployee->display_name ?? Auth::user()?->name)] : [];
            $createEmployees = $filterEmployees;
        }

        $months = [
            'custom' => 'Custom Date Range',
            'all' => 'All Months',
        ];
        for ($i = 0; $i < 24; $i++) {
            $m = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $months[$key] = $m->format('F Y') . ($i === 0 ? ' (Current)' : '');
        }

        $filters = [];
        $filters[] = [
            'name' => 'employee_id',
            'label' => 'Employee',
            'type' => 'select',
            'options' => $filterEmployees,
            'placeholder' => 'All Employee',
        ];

        $filters[] = ['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => $months];
        $filters[] = ['name' => 'from', 'label' => 'From Date', 'type' => 'date'];
        $filters[] = ['name' => 'to', 'label' => 'To Date', 'type' => 'date'];
        $filters[] = ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']];
        $filters[] = ['name' => 'work_type', 'label' => 'Work Type', 'type' => 'select', 'options' => ['holiday_work' => 'Holiday Work', 'weekoff_work' => 'Weekoff Work']];
        $filters[] = ['name' => 'work_mode', 'label' => 'Work Mode', 'type' => 'select', 'options' => ['wfo' => 'WFO', 'wfh' => 'WFH']];

        // Form Fields for Add New / Edit
        $formFields = [];
        if ($isHrOrAdmin) {
            $formFields[] = ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $createEmployees];
        } else {
            $formFields[] = [
                'name' => 'employee_id', 
                'label' => 'Employee', 
                'type' => 'select', 
                'options' => $createEmployees, 
                'default' => $currentEmployee?->id,
                'value' => $currentEmployee?->id
            ];
        }

        $formFields[] = ['name' => 'worked_date', 'label' => 'Worked Date', 'type' => 'date'];
        $formFields[] = ['name' => 'work_type', 'label' => 'Work Type', 'type' => 'select', 'options' => ['holiday_work' => 'Holiday Work', 'weekoff_work' => 'Weekoff Work']];
        $formFields[] = ['name' => 'work_mode', 'label' => 'Work Mode', 'type' => 'select', 'options' => ['wfo' => 'WFO (Office)', 'wfh' => 'WFH (Home)']];

        if ($isHrOrAdmin) {
            $formFields[] = ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']];
        }

        $formFields[] = ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 12];

        // Row Actions: Only authorized roles get Approve / Reject
        $rowActions = [];
        if ($canApprove) {
            $rowActions[] = [
                'label' => 'Approve',
                'route' => 'hrms.attendance.holiday_work.approve',
                'icon' => 'fas fa-check text-success',
                'confirm' => 'Approve this work request? Comp off will be credited upon eligibility validation.',
            ];
        }
        if ($canReject) {
            $rowActions[] = [
                'label' => 'Reject',
                'route' => 'hrms.attendance.holiday_work.reject',
                'icon' => 'fas fa-times text-danger',
                'confirm' => 'Reject this request?',
            ];
        }

        return [
            'accesses' => $this->accesses(),
            'active' => 'attendance',
            'pageTitle' => 'Holiday Work Requests',
            'pageSubtitle' => 'Approve holiday/weekoff work. Comp off is generated after attendance eligibility validation.',
            'rows' => $rows,
            'columns' => [
                ['key' => 'employee_display_name', 'label' => 'Employee'],
                ['key' => 'employee_code', 'label' => 'Code'],
                ['key' => 'worked_date', 'label' => 'Worked Date', 'type' => 'date'],
                ['key' => 'work_type', 'label' => 'Work Type'],
                ['key' => 'work_mode', 'label' => 'Work Mode', 'type' => 'badge'],
                ['key' => 'comp_off_generated', 'label' => 'Comp. Off', 'type' => 'badge'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ],
            'filters' => $filters,
            'formFields' => $formFields,
            'canCreate' => true,
            'canEdit' => $isHrOrAdmin || $isEmployee,
            'canDelete' => $isHrOrAdmin || $isEmployee,
            'isHrOrAdmin' => $isHrOrAdmin,
            'isEmployee' => $isEmployee,
            'isManager' => $isManager,
            'storeRoute' => 'hrms.attendance.holiday_work.store',
            'updateRoute' => 'hrms.attendance.holiday_work.update',
            'deleteRoute' => 'hrms.attendance.holiday_work.destroy',
            'rowActions' => $rowActions,
        ];
    }
}

