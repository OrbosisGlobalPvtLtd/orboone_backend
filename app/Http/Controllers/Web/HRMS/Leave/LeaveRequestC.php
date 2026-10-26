<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Http\Requests\Web\HRMS\Leave\StoreLeaveRequestRequest;
use App\Mail\HrWorkflowAlertMail;
use App\Models\Core\AccessM;
use App\Models\Core\UserM;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\HRMS\Leave\LeaveValidationService;
use Illuminate\Validation\ValidationException;
use App\Services\HRMS\Notification\NotificationS;
use App\Services\HRMS\Leave\AutoExpireLeaveService;

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

    private function currentUser(): ?UserM
    {
        /** @var UserM|null $user */
        $user = Auth::user();

        return $user;
    }

    private function isAdminOrHr(?UserM $user = null): bool
    {
        $user = $user ?: $this->currentUser();
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->isHrAdmin() || $user->isSuperAdmin() || ($user->system_role_id ?? null) === 1 || ($user->role_id ?? null) === 1;
    }

    public function index(Request $request)
    {
        $user = $this->currentUser();
        if (! $user) {
            abort(401);
        }

        $employee = EmployeeM::where('user_id', $user->id)->first();
        $isAdminOrHr = $this->isAdminOrHr($user);

        if (! $employee && ! $isAdminOrHr) {
            abort(403, 'No employee profile linked to your account.');
        }

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $currentMonth = (int) Carbon::now('Asia/Kolkata')->month;

        if ($request->has('month')) {
            $selectedMonth = (string) $request->month;
        } else {
            $selectedMonth = (string) $currentMonth;
        }

        // Auto-expire past pending leaves whose end date has passed
        app(AutoExpireLeaveService::class)->expirePastPendingRequests();

        $query = LeaveRequestM::with([
            'leaveType',
            'dates',
            'employee.user',
            'employee.department',
            'employee.designation',
            'employee.reportingManager.user',
            'approver',
            'managerApprover',
            'hrApprover'
        ])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->leave_type_id, fn ($q) => $q->where('leave_type_id', $request->leave_type_id));

        if ($selectedMonth !== 'all' && $selectedMonth !== '') {
            $m = (int) $selectedMonth;
            $monthStart = Carbon::createFromDate($year, $m, 1, 'Asia/Kolkata')->startOfMonth()->toDateString();
            $monthEnd = Carbon::createFromDate($year, $m, 1, 'Asia/Kolkata')->endOfMonth()->toDateString();

            $query->where(function ($q) use ($monthStart, $monthEnd) {
                $q->where(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereDate('start_date', '<=', $monthEnd)
                        ->whereDate('end_date', '>=', $monthStart);
                })->orWhereHas('dates', function ($dq) use ($monthStart, $monthEnd) {
                    $dq->whereDate('leave_date', '>=', $monthStart)
                       ->whereDate('leave_date', '<=', $monthEnd);
                })->orWhere(function ($cq) use ($monthStart, $monthEnd) {
                    $cq->whereDate('created_at', '>=', $monthStart)
                       ->whereDate('created_at', '<=', $monthEnd);
                });
            });
        } elseif ($year) {
            $yearStart = Carbon::createFromDate($year, 1, 1, 'Asia/Kolkata')->startOfYear()->toDateString();
            $yearEnd = Carbon::createFromDate($year, 12, 31, 'Asia/Kolkata')->endOfYear()->toDateString();
            $query->where(function ($q) use ($yearStart, $yearEnd) {
                $q->where(function ($sub) use ($yearStart, $yearEnd) {
                    $sub->whereDate('start_date', '<=', $yearEnd)
                        ->whereDate('end_date', '>=', $yearStart);
                })->orWhereHas('dates', function ($dq) use ($yearStart, $yearEnd) {
                    $dq->whereDate('leave_date', '>=', $yearStart)
                        ->whereDate('leave_date', '<=', $yearEnd);
                })->orWhere(function ($cq) use ($yearStart, $yearEnd) {
                    $cq->whereDate('created_at', '>=', $yearStart)
                       ->whereDate('created_at', '<=', $yearEnd);
                });
            });
        }

        if ($isAdminOrHr && $request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        } elseif (! $isAdminOrHr) {
            $query->where('employee_id', $employee?->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('employee.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('employee', fn ($eq) => $eq->where('employee_code', 'like', "%{$search}%"))
                  ->orWhereHas('leaveType', fn ($tq) => $tq->where('name', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        $requests = $perPage > 0 ? $query->latest()->paginate($perPage) : $query->latest()->paginate(1000);

        // Enrich requests with allocation metrics and names for Leave Request Details view modal
        $empIds = $requests->pluck('employee_id')->filter()->unique()->all();
        $allocations = !empty($empIds)
            ? DB::table('leave_allocations')
                ->whereIn('employee_id', $empIds)
                ->where('year', $year ?: Carbon::now('Asia/Kolkata')->year)
                ->get()
                ->keyBy('employee_id')
            : collect();

        foreach ($requests as $item) {
            $emp = $item->employee;
            $item->display_name = $emp?->user?->name ?? $emp?->employee_code ?? 'Employee';
            $item->employee_code = $emp?->employee_code ?? '—';
            $item->department_name = $emp?->department?->name ?? 'General';
            $item->designation_name = $emp?->designation?->name ?? 'Employee';
            $item->leave_type_name = $item->leaveType?->name ?? 'Leave';
            $item->current_reporting_manager_id = $emp?->reporting_manager_employee_id;
            $item->reporting_manager_name = $emp?->reportingManager?->user?->name ?? null;
            $item->manager_approver_name = $item->managerApprover?->name ?? null;
            $item->hr_approver_name = $item->hrApprover?->name ?? ($item->approver?->name ?? null);
            $item->rejected_by_name = $item->approver?->name ?? null;

            $la = $allocations->get($item->employee_id);
            $item->paid_allocated = $la?->paid_allocated ?? 0;
            $item->paid_remaining = $la?->paid_remaining ?? 0;
            $item->paid_used = $la?->paid_used ?? 0;
            $item->sick_allocated = $la?->sick_allocated ?? 0;
            $item->sick_remaining = $la?->sick_remaining ?? 0;
            $item->sick_used = $la?->sick_used ?? 0;
            $item->comp_off_allocated = $la?->comp_off_allocated ?? 0;
            $item->comp_off_remaining = $la?->comp_off_remaining ?? 0;
            $item->comp_off_used = $la?->comp_off_used ?? 0;
        }

        $balanceService = app(\App\Services\HRMS\Leave\LeaveBalanceService::class);
        $ledgerShape = $employee ? $balanceService->getApiBalanceShape($employee) : null;
        $allocation = $ledgerShape['allocation'] ?? ($employee ? $balanceService->allocationForRead($employee) : null);
        $centralBalance = $ledgerShape['central'] ?? ($employee ? $balanceService->getCentralBalance($employee) : null);
        $ledgerFields = $ledgerShape['fields'] ?? null;
        $leaveTypes = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $employees = $isAdminOrHr ? EmployeeM::where('is_active', true)->get()->sortBy(fn ($e) => strtolower($e->display_name))->values() : collect();
        $accesses = $this->accesses();

        $isSuperAdminUser = $user->isSuperAdmin();
        $isHrAdminUser = $isAdminOrHr;
        $authEmpId = $employee?->id;

        return view('hrms.leave.requests.index', compact(
            'requests',
            'allocation',
            'leaveTypes',
            'employee',
            'employees',
            'isAdminOrHr',
            'accesses',
            'centralBalance',
            'ledgerFields',
            'selectedMonth',
            'year',
            'isSuperAdminUser',
            'isHrAdminUser',
            'authEmpId'
        ))->with('active', 'leave_management');
    }

    public function create()
    {
        $user = $this->currentUser();
        if (! $user) {
            abort(401);
        }

        $employee = EmployeeM::where('user_id', $user->id)->first();
        $isAdminOrHr = $this->isAdminOrHr($user);

        if (! $employee && ! $isAdminOrHr) {
            abort(403, 'No employee profile linked to your account.');
        }

        if ($employee && ! $this->isEligibleForLeaveRequest($employee) && ! $isAdminOrHr) {
            return back()->with('error', 'Leave applications are currently restricted to Engineering/Development and QA/Testing team members.');
        }

        $year = Carbon::now('Asia/Kolkata')->year;
        $allocation = $employee
            ? app(\App\Services\HRMS\Leave\LeaveBalanceService::class)->allocationForRead(
                $employee,
                Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata')
            )
            : null;

        $leaveTypes = LeaveTypeM::where('is_active', true)->orderBy('name')->get();
        $employees = $isAdminOrHr ? EmployeeM::where('is_active', true)->get()->sortBy(fn ($e) => strtolower($e->display_name))->values() : collect();
        $accesses = $this->accesses();

        return view('hrms.leave.requests.create', compact('leaveTypes', 'employee', 'employees', 'isAdminOrHr', 'accesses', 'allocation'))
            ->with('active', 'leave_management');
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        $attachmentPath = null;
        $requestPersisted = false;
        try {
            $user = $this->currentUser();
            $isAdminOrHr = $this->isAdminOrHr($user);

            $employee = $request->filled('employee_id') && $isAdminOrHr
                ? EmployeeM::findOrFail($request->employee_id)
                : EmployeeM::where('user_id', Auth::id())->first();

            if (! $employee) {
                return back()->with('error', 'No employee profile selected or linked to your account.')->withInput();
            }

            if (! $this->isEligibleForLeaveRequest($employee) && ! $isAdminOrHr) {
                return back()->with('error', 'Leave applications are currently restricted to Engineering/Development and QA/Testing team members.')->withInput();
            }

            $leaveType = LeaveTypeM::findOrFail($request->leave_type_id);
            $sanitized = LeaveValidationService::sanitizePayload($request->validated());
            $leaveRequest = DB::transaction(function () use ($request, $employee, $leaveType, $sanitized, &$attachmentPath) {
            $attachmentPath = $this->storeAttachment($request);
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
            return $leaveRequest->fresh(['employee.user', 'employee.department', 'leaveType', 'dates']);
            });
            $requestPersisted = true;

            $this->notifyLeaveApplied($leaveRequest);

            return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $requestPersisted && ! empty($attachmentPath)) Storage::disk('private')->delete($attachmentPath);
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            if (! $requestPersisted && ! empty($attachmentPath)) Storage::disk('private')->delete($attachmentPath);
            Log::error('Web leave request failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int|string $id)
    {
        $newAttachmentPath = null;
        $oldAttachmentPath = null;
        try {
            $user = $this->currentUser();
            $isAdminOrHr = $this->isAdminOrHr($user);

            $leaveRequest = LeaveRequestM::findOrFail($id);
            $oldAttachmentPath = $leaveRequest->attachment_path;
            $employee = $isAdminOrHr ? $leaveRequest->employee : EmployeeM::where('user_id', Auth::id())->first();

            if (! $isAdminOrHr) {
                abort_unless($employee && (int) $leaveRequest->employee_id === (int) $employee->id, 403);
            }
            abort_unless($leaveRequest->status === 'pending', 400, 'Cannot edit request after approval or rejection.');

            $rules = LeaveValidationService::rules($request->all());
            $rules['reason'] = 'required|string|max:1000';
            $request->validate($rules, LeaveValidationService::messages());
            $sanitized = [
                'leave_type_id' => (int) $request->leave_type_id,
                'start_date' => (string) $request->start_date,
                'end_date' => (string) $request->end_date,
                'is_half_day' => $request->boolean('is_half_day'),
                'half_day_type' => $request->input('half_day_type', 'first_half'),
                'emergency_leave' => $request->boolean('emergency_leave'),
            ];

            $leaveRequest = DB::transaction(function () use ($request, $leaveRequest, $employee, $sanitized, &$newAttachmentPath) {
            $leaveType = LeaveTypeM::findOrFail($sanitized['leave_type_id']);
            if ($request->hasFile('attachment')) {
                $directory = $this->paths->employeeLeave((int) $employee->id, 'attachments');
                $newAttachmentPath = $request->file('attachment')->store($directory, 'private');
            }
            $calculation = $this->calculationService->calculate($employee, $leaveType,
                $sanitized + ['attachment_path' => $newAttachmentPath ?: $leaveRequest->attachment_path], $leaveRequest);
            $leaveRequest->update([
                'leave_type_id' => $sanitized['leave_type_id'],
                'start_date' => $sanitized['start_date'],
                'end_date' => $sanitized['end_date'],
                'requested_days' => $calculation['requested_days'],
                'deducted_days' => $calculation['deducted_days'],
                'is_half_day' => $sanitized['is_half_day'],
                'half_day_type' => $sanitized['half_day_type'],
                'reason' => $request->reason,
                'attachment_path' => $newAttachmentPath ?: $leaveRequest->attachment_path,
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
            return $leaveRequest->fresh();
            });
            if ($newAttachmentPath && $oldAttachmentPath) Storage::disk('private')->delete($oldAttachmentPath);

            return redirect()->route('leave-requests.index')->with('success', 'Leave request updated successfully.');
        } catch (\Throwable $e) {
            if ($newAttachmentPath) Storage::disk('private')->delete($newAttachmentPath);
            Log::error('Web leave request update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function preview(Request $request)
    {
        try {
            $user = $this->currentUser();
            $isAdminOrHr = $this->isAdminOrHr($user);

            $existingRequestId = $request->input('leave_request_id');
            $existingRequest = $existingRequestId ? LeaveRequestM::find($existingRequestId) : null;

            $employee = ($request->filled('employee_id') && $isAdminOrHr)
                ? EmployeeM::find($request->employee_id)
                : ($existingRequest ? $existingRequest->employee : EmployeeM::where('user_id', Auth::id())->first());

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
                'bypass_notice_period' => true,
                'bypass_attachment_validation' => true,
                'bypass_duplicate_validation' => true,
            ];

            $calc = $this->calculationService->calculate($employee, $leaveType, $sanitized, $existingRequest);
            $ledgerBalances = app(\App\Services\HRMS\Leave\LeaveBalanceService::class)->getLedgerApiBalances($employee);
            $centralBalance = $ledgerBalances !== null
                ? app(\App\Services\HRMS\Leave\LeaveBalanceService::class)->getCentralBalance($employee)
                : null;

            $isSick = (bool) $leaveType->is_sick;
            $isLwp = (bool) $leaveType->is_lwp;
            $isEmergency = $request->boolean('emergency_leave');
            $isHalfDay = $request->boolean('is_half_day');

            $noticeWarning = null;
            if (! $isSick && ! $isLwp && ! $isEmergency && ! $isHalfDay) {
                $today = Carbon::now('Asia/Kolkata')->startOfDay();
                $startCarbon = Carbon::parse($startDate, 'Asia/Kolkata')->startOfDay();
                if ($startCarbon->gte($today) && $today->diffInDays($startCarbon, false) < 2) {
                    $noticeWarning = 'Normal leaves must be applied at least 2 days in advance. Check "Emergency Leave" if applying due to urgent emergency.';
                }
            }

            $hasDuplicate = false;
            $calcDates = collect($calc['dates'] ?? [])
                ->where('deduct_as_leave', true)
                ->pluck('leave_date')
                ->values()
                ->all();
            if (! empty($calcDates)) {
                $dupQuery = DB::table('leave_request_dates')
                    ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
                    ->where('leave_request_dates.employee_id', $employee->id)
                    ->whereIn('leave_request_dates.leave_date', $calcDates)
                    ->whereNotIn('leave_requests.status', ['rejected', 'cancelled', 'void', 'expired']);
                if ($existingRequest) {
                    $dupQuery->where('leave_requests.id', '!=', $existingRequest->id);
                }
                $hasDuplicate = $dupQuery->exists();
            }
            $duplicateWarning = $hasDuplicate ? 'A non-cancelled leave request already exists for one or more selected dates.' : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'requested_calendar_days' => $calc['requested_calendar_days'],
                    'working_days' => $calc['working_days'],
                    'sandwich_days' => $calc['sandwich_days'],
                    'deducted_days' => $calc['deducted_days'],
                    'sandwich_applied' => $calc['sandwich_applied'],
                    'sandwich_details' => $calc['sandwich_details'] ?? [],
                    'sandwich_message' => $calc['sandwich_message'] ?? null,
                    'notice_warning' => $noticeWarning,
                    'duplicate_warning' => $duplicateWarning,
                    'paid_days' => $calc['paid_days'],
                    'sick_days' => $calc['sick_days'],
                    'comp_off_days' => $calc['comp_off_days'],
                    'lwp_days' => $calc['lwp_days'],
                    'pl_days' => $calc['paid_days'],
                    'sl_days' => $calc['sick_days'],
                    'lop_days' => $calc['lwp_days'],
                    'requires_medical_certificate' => (bool) ($calc['requires_medical_certificate'] ?? false),
                    'leave_type_code' => $leaveType->code,
                    'is_comp_off' => (bool) $leaveType->is_comp_off,
                    'is_sick' => (bool) $leaveType->is_sick,
                    'is_lwp' => (bool) $leaveType->is_lwp,
                    'ledger_backed' => $ledgerBalances !== null,
                    'current_balance' => $centralBalance ? [
                        'paid_remaining' => $centralBalance['paid_remaining'],
                        'sick_remaining' => $centralBalance['sick_remaining'],
                        'comp_off_remaining' => $centralBalance['comp_off_remaining'],
                        'total_remaining' => $centralBalance['paid_remaining'] + $centralBalance['sick_remaining'] + $centralBalance['comp_off_remaining'],
                    ] : [
                        'paid_remaining' => (float) ($calc['allocation']->paid_remaining ?? 0),
                        'sick_remaining' => (float) ($calc['allocation']->sick_remaining ?? 0),
                        'comp_off_remaining' => (float) ($calc['allocation']->comp_off_remaining ?? 0),
                        'total_remaining' => (float) ($calc['allocation']->total_remaining ?? 0),
                    ],
                    ...($ledgerBalances ?? []),
                    'balance_after_split' => $calc['balance_after_split'] ?? [],
                ],
            ]);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: $e->getMessage();
            return response()->json(['success' => false, 'message' => $msg], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, int|string $id)
    {
        try {
            $user = $this->currentUser();
            $isAdminOrHr = $this->isAdminOrHr($user);

            $leaveRequest = LeaveRequestM::findOrFail($id);
            $employeeId = $this->ownEmployeeId();

            if (! $isAdminOrHr) {
                abort_unless($employeeId && (int) $leaveRequest->employee_id === (int) $employeeId, 403);
            }

            $this->approvalService->cancel($leaveRequest, Auth::id(), $request->input('reason'));

            return back()->with('success', 'Leave request cancelled successfully.');
        } catch (\Throwable $e) {
            Log::error('Leave cancellation failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function attachment(int|string $id)
    {
        $user = $this->currentUser();
        if (! $user) {
            abort(401);
        }

        $leaveRequest = LeaveRequestM::findOrFail($id);
        $isAdminOrHr = $this->isAdminOrHr($user);
        $employee = EmployeeM::where('user_id', $user->id)->first();

        if (! $isAdminOrHr && (! $employee || (int) $leaveRequest->employee_id !== (int) $employee->id)) {
            abort(403, 'Unauthorized access to leave attachment.');
        }

        if (! $leaveRequest->attachment_path) {
            abort(404, 'No attachment associated with this leave request.');
        }

        $stream = $this->resolver->secureFileStream($leaveRequest->attachment_path);
        if (! $stream) {
            abort(404, 'Attachment file not found on disk.');
        }

        return $stream;
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

        app(NotificationS::class)->notifyHrAndSuperAdmin(
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
        $user = $this->currentUser();
        if (! $user) {
            return false;
        }

        if ($this->isAdminOrHr($user)) {
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
        $user = $this->currentUser();
        $roleId = $user?->system_role_id ?? $user?->role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
