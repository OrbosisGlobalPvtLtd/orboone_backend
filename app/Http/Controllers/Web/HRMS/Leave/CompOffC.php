<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\CompOffM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveCompOffTransactionM;
use App\Services\HRMS\Leave\CompOffService;
use App\Services\HRMS\Leave\LeaveAllocationService;
use App\Services\HRMS\Leave\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompOffC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private CompOffService $compOffService,
        private LeaveAllocationService $allocationService,
        private LeaveBalanceService $balanceService
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');

        $isExplicitAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true);

        $isEmployee = ($roleId === 7 || $roleName === 'employee' || (method_exists($user, 'isEmployee') && $user->isEmployee())) && ! $isExplicitAdmin;

        $isAdmin = ! $isEmployee && (
            $isExplicitAdmin
            || $this->userHasPermission('leave.comp_off.manage')
            || $this->userHasPermission('leave.comp_off.view_all')
        );

        $isManager = ! $isEmployee && ! $isAdmin && (
            $roleId === 9
            || $roleName === 'manager'
            || $this->userHasPermission('leave.comp_off.view_team')
        );

        $canViewOthers = ! $isEmployee && ($isAdmin || $isManager);
        $isEmployeeRole = $isEmployee || ! $canViewOthers;

        $ownEmp = $user->employee ?: $this->currentEmployee();
        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $currentMonth = (int) Carbon::now('Asia/Kolkata')->month;

        // Automatic current month filtering:
        // Default to current month from DB unless explicitly set to 'all' or another month
        if ($request->has('month')) {
            $selectedMonth = (string) $request->month;
        } else {
            $selectedMonth = (string) $currentMonth;
        }

        // 1. Employee Scoping
        $query = CompOffM::with(['employee.user', 'employee.department', 'employee.designation', 'approvedBy'])
            ->withCount('transactions as transaction_count')
            ->withSum('transactions as transaction_net_amount', 'amount');

        if ($isEmployeeRole) {
            abort_if(! $ownEmp, 403, 'No employee record linked to your account.');
            $query->where('employee_id', $ownEmp->id);
            $targetEmployee = $ownEmp;
            $employees = collect();
        } else {
            $employees = $this->scopedEmployeeOptions('leave.comp_off.view_all', 'leave.comp_off.view_team');
            if ($request->filled('employee_id')) {
                $targetEmployeeId = (int) $request->employee_id;
                $query->where('employee_id', $targetEmployeeId);
                $targetEmployee = EmployeeM::with('user')->find($targetEmployeeId);
            } else {
                $targetEmployee = null;
                $this->scopeEmployeeVisibility($query, 'leave.comp_off.view_all', 'leave.comp_off.view_team');
            }
        }

        // 2. Status, Month & Year Filters
        if ($request->filled('status')) {
            $statusFilter = strtolower((string) $request->status);
            if ($statusFilter === 'available') {
                $statusFilter = 'earned';
            }
            $query->where('status', $statusFilter);
        }

        if ($selectedMonth !== 'all' && $selectedMonth !== '') {
            if (str_contains($selectedMonth, '-')) {
                $parts = explode('-', $selectedMonth);
                $query->whereYear('worked_date', (int) $parts[0])
                      ->whereMonth('worked_date', (int) $parts[1]);
            } else {
                $query->whereMonth('worked_date', (int) $selectedMonth);
            }
        }

        if ($request->filled('year')) {
            $query->whereYear('worked_date', $request->year);
        }

        $perPage = (int) $request->get('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $compOffs = $query->latest('worked_date')->latest('id')->paginate($perPage)->appends($request->query());
        $compOffs->getCollection()->each(static function (CompOffM $lot): void {
            if ((int) $lot->transaction_count > 0) {
                $net = bcadd((string) ($lot->transaction_net_amount ?? '0'), '0', 6);
                $used = bccomp($net, '0.000000', 6) < 0 ? bcsub('0.000000', $net, 6) : '0.000000';
                $lot->setAttribute('used_days', bcadd($used, '0', 2));
            } elseif (strtolower((string) $lot->status) === 'used') {
                $lot->setAttribute('used_days', (string) $lot->earned_days);
            } else {
                $lot->setAttribute('used_days', '0.00');
            }
        });

        // 3. Stats Calculation (Leave Balance & Comp Off Totals)
        $baseQuery = CompOffM::query();
        if ($isEmployeeRole) {
            $baseQuery->where('employee_id', $ownEmp->id);
        } elseif ($targetEmployee) {
            $baseQuery->where('employee_id', $targetEmployee->id);
        } else {
            $this->scopeEmployeeVisibility($baseQuery, 'leave.comp_off.view_all', 'leave.comp_off.view_team');
        }

        if ($selectedMonth !== 'all' && $selectedMonth !== '') {
            if (str_contains($selectedMonth, '-')) {
                $parts = explode('-', $selectedMonth);
                $baseQuery->whereYear('worked_date', (int) $parts[0])
                          ->whereMonth('worked_date', (int) $parts[1]);
            } else {
                $baseQuery->whereMonth('worked_date', (int) $selectedMonth);
            }
        }

        if ($request->filled('year')) {
            $baseQuery->whereYear('worked_date', $request->year);
        }

        $totalEarned = (float) (clone $baseQuery)->whereIn('status', ['earned', 'available'])->sum('earned_days');
        $totalExpired = (float) (clone $baseQuery)->where('status', 'expired')->sum('earned_days');

        // Leave Allocation Comp Off Quota
        $compOffAllocated = 0.0;
        $compOffRemaining = 0.0;
        $compOffUsed = 0.0;
        $hasAllocationSnapshot = false;

        if ($isEmployeeRole && $ownEmp) {
            $alloc = $this->balanceService->allocationForRead(
                $ownEmp,
                Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata')
            );
            if ($alloc) {
                $hasAllocationSnapshot = true;
                $compOffAllocated = (float) $alloc->comp_off_allocated;
                $compOffRemaining = (float) $alloc->comp_off_remaining;
                $compOffUsed = (float) $alloc->comp_off_used;
            }
        } elseif ($targetEmployee) {
            $alloc = $this->balanceService->allocationForRead(
                $targetEmployee,
                Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kolkata')
            );
            if ($alloc) {
                $hasAllocationSnapshot = true;
                $compOffAllocated = (float) $alloc->comp_off_allocated;
                $compOffRemaining = (float) $alloc->comp_off_remaining;
                $compOffUsed = (float) $alloc->comp_off_used;
            }
        } else {
            $allocQuery = LeaveAllocationM::where('year', $year);
            $this->scopeEmployeeVisibility($allocQuery, 'leave.comp_off.view_all', 'leave.comp_off.view_team');
            $hasAllocationSnapshot = (clone $allocQuery)->exists();
            $compOffAllocated = (float) (clone $allocQuery)->sum('comp_off_allocated');
            $compOffRemaining = (float) (clone $allocQuery)->sum('comp_off_remaining');
            $compOffUsed = (float) (clone $allocQuery)->sum('comp_off_used');
        }

        $stats = [
            'total_available' => $hasAllocationSnapshot ? $compOffRemaining : $totalEarned,
            'total_used' => $compOffUsed,
            'total_expired' => $totalExpired,
            'comp_off_allocated' => $compOffAllocated,
            'comp_off_remaining' => $compOffRemaining,
        ];

        // 4. Pending Holiday Work Approvals (Only for Admin / HR / Managers with manage rights)
        $canManageCompOff = ! $isEmployeeRole && $isAdmin && $this->userHasPermission('leave.comp_off.manage');
        $holidayWorkRequests = collect();
        $grantEmployees = collect();

        if ($canManageCompOff) {
            $hwQuery = HolidayWorkRequestM::with(['employee.user', 'employee.department'])
                ->where('status', 'pending');
            $holidayWorkRequests = $hwQuery->latest()->get();
            $grantEmployees = EmployeeM::query()->with('user')
                ->where('is_active', 1)->orderBy('employee_code')->get();
        }

        // 5. Build Unified Comp-Off Transactions Ledger (Tab 2)
        $txQuery = LeaveCompOffTransactionM::with([
            'employee.user',
            'employee.department',
            'compOff',
            'leaveRequest.leaveType',
            'leaveRequest.approver',
            'leaveRequest.hrApprover',
            'creator',
        ]);

        if ($isEmployeeRole) {
            $txQuery->where('employee_id', $ownEmp->id);
        } elseif ($targetEmployee) {
            $txQuery->where('employee_id', $targetEmployee->id);
        } else {
            $this->scopeEmployeeVisibility($txQuery, 'leave.comp_off.view_all', 'leave.comp_off.view_team');
        }

        if ($selectedMonth !== 'all' && $selectedMonth !== '') {
            if (str_contains($selectedMonth, '-')) {
                $parts = explode('-', $selectedMonth);
                $txQuery->whereYear('effective_date', (int) $parts[0])
                        ->whereMonth('effective_date', (int) $parts[1]);
            } else {
                $txQuery->whereMonth('effective_date', (int) $selectedMonth);
            }
        }
        if ($year) {
            $txQuery->whereYear('effective_date', $year);
        }

        $directTransactions = $txQuery->latest('effective_date')->latest('id')->get();

        $transactionItems = collect();

        // 5a. Add Credits from CompOffM lots (Earned)
        $lotsForTxQuery = CompOffM::with(['employee.user', 'employee.department', 'approvedBy', 'transactions']);
        if ($isEmployeeRole) {
            $lotsForTxQuery->where('employee_id', $ownEmp->id);
        } elseif ($targetEmployee) {
            $lotsForTxQuery->where('employee_id', $targetEmployee->id);
        } else {
            $this->scopeEmployeeVisibility($lotsForTxQuery, 'leave.comp_off.view_all', 'leave.comp_off.view_team');
        }

        if ($request->filled('status')) {
            $lotsForTxQuery->where('status', $request->status);
        }

        if ($selectedMonth !== 'all' && $selectedMonth !== '') {
            if (str_contains($selectedMonth, '-')) {
                $parts = explode('-', $selectedMonth);
                $lotsForTxQuery->whereYear('worked_date', (int) $parts[0])
                               ->whereMonth('worked_date', (int) $parts[1]);
            } else {
                $lotsForTxQuery->whereMonth('worked_date', (int) $selectedMonth);
            }
        }
        if ($year) {
            $lotsForTxQuery->whereYear('worked_date', $year);
        }

        $lotsForTx = $lotsForTxQuery->latest('worked_date')->latest('id')->get();

        foreach ($lotsForTx as $lot) {
            $earnedAmt = (float) $lot->earned_days;
            if ($earnedAmt <= 0 && preg_match('/Used\s+(\d+(?:\.\d+)?)\s*day/i', (string) $lot->remarks, $m)) {
                $earnedAmt = (float) $m[1];
            }
            if ($earnedAmt <= 0) {
                $earnedAmt = 0.50;
            }

            $adminName = optional($lot->approvedBy)->name;
            if (! $adminName && $lot->approved_by_user_id) {
                $u = \App\Models\Core\UserM::find($lot->approved_by_user_id);
                $adminName = $u?->name;
            }

            $isHolidayWork = preg_match('/approved holiday\/weekoff/i', (string) $lot->remarks)
                || preg_match('/holiday/i', (string) $lot->remarks);

            if ($isHolidayWork) {
                $creditActionBy = $adminName ? "Holiday Work ({$adminName})" : 'Holiday Work';
            } elseif ($adminName) {
                $creditActionBy = "Manual ({$adminName})";
            } else {
                $creditActionBy = 'Manual';
            }

            $transactionItems->push((object) [
                'id' => 'credit-' . $lot->id,
                'comp_off_id' => $lot->id,
                'employee_id' => $lot->employee_id,
                'employee' => $lot->employee,
                'type' => 'credit',
                'type_label' => 'Credit (Earned)',
                'badge_class' => 'pill-earned',
                'amount' => '+' . number_format($earnedAmt, 2),
                'amount_numeric' => $earnedAmt,
                'date' => $lot->worked_date,
                'effective_date' => $lot->worked_date ? $lot->worked_date->format('d M Y') : 'N/A',
                'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d M Y') : 'N/A',
                'description' => $lot->remarks ?: 'Comp-Off Earned / Granted',
                'reference' => 'GRANT #' . $lot->id,
                'action_by' => $creditActionBy,
                'created_at' => $lot->created_at,
            ]);

            // If legacy lot has no direct LeaveCompOffTransactionM rows, but status is 'used'
            if ($lot->transactions->isEmpty() && strtolower((string) $lot->status) === 'used') {
                $approverName = null;
                if ($lot->used_against_leave_request_id) {
                    $lr = \App\Models\HRMS\Leave\LeaveRequestM::with(['hrApprover', 'approver'])->find($lot->used_against_leave_request_id);
                    $approverName = optional(optional($lr)->hrApprover)->name ?? optional(optional($lr)->approver)->name;
                }
                $debitActionBy = $approverName ? "Leave ({$approverName})" : 'Leave';

                $transactionItems->push((object) [
                    'id' => 'legacy-used-' . $lot->id,
                    'comp_off_id' => $lot->id,
                    'employee_id' => $lot->employee_id,
                    'employee' => $lot->employee,
                    'type' => 'debit',
                    'type_label' => 'Debit (Used)',
                    'badge_class' => 'pill-used',
                    'amount' => '-' . number_format($earnedAmt, 2),
                    'amount_numeric' => -$earnedAmt,
                    'date' => $lot->expiry_date ?: $lot->worked_date,
                    'effective_date' => ($lot->expiry_date ?: $lot->worked_date) ? ($lot->expiry_date ?: $lot->worked_date)->format('d M Y') : 'N/A',
                    'expiry_date' => null,
                    'description' => 'Comp-off utilized against approved leave',
                    'reference' => $lot->used_against_leave_request_id ? 'LEAVE #' . $lot->used_against_leave_request_id : 'USAGE #' . $lot->id,
                    'action_by' => $debitActionBy,
                    'created_at' => $lot->updated_at,
                ]);
            }

            // If expired
            if (strtolower((string) $lot->status) === 'expired') {
                $transactionItems->push((object) [
                    'id' => 'expired-' . $lot->id,
                    'comp_off_id' => $lot->id,
                    'employee_id' => $lot->employee_id,
                    'employee' => $lot->employee,
                    'type' => 'expired',
                    'type_label' => 'Expired (Lapsed)',
                    'badge_class' => 'pill-expired',
                    'amount' => '-' . number_format($earnedAmt, 2),
                    'amount_numeric' => -$earnedAmt,
                    'date' => $lot->expiry_date,
                    'effective_date' => $lot->expiry_date ? $lot->expiry_date->format('d M Y') : 'N/A',
                    'expiry_date' => null,
                    'description' => 'Lapsed at end of validity period (' . ($lot->expiry_date ? $lot->expiry_date->format('d M Y') : '') . ')',
                    'reference' => 'EXP #' . $lot->id,
                    'action_by' => 'System',
                    'created_at' => $lot->updated_at,
                ]);
            }
        }

        // 5b. Add Direct LeaveCompOffTransactionM rows (Debit & Reversal)
        foreach ($directTransactions as $dtx) {
            $isDebit = $dtx->transaction_type === 'debit';
            $isReversal = $dtx->transaction_type === 'reversal';
            $amt = abs((float) $dtx->amount);

            if ($isDebit) {
                $lr = $dtx->leaveRequest;
                $approverName = optional(optional($lr)->hrApprover)->name 
                    ?? optional(optional($lr)->approver)->name 
                    ?? optional($dtx->creator)->name;
                $directActionBy = $approverName ? "Leave ({$approverName})" : 'Leave';
            } elseif ($isReversal) {
                $revUser = optional($dtx->creator)->name;
                $directActionBy = $revUser ? "Reversal ({$revUser})" : 'Reversal';
            } else {
                $creator = optional($dtx->creator)->name;
                $directActionBy = $creator ? "Manual ({$creator})" : 'Manual';
            }

            $transactionItems->push((object) [
                'id' => 'dtx-' . $dtx->id,
                'comp_off_id' => $dtx->comp_off_id,
                'employee_id' => $dtx->employee_id,
                'employee' => $dtx->employee,
                'type' => $dtx->transaction_type,
                'type_label' => $isDebit ? 'Debit (Used)' : 'Reversal',
                'badge_class' => $isDebit ? 'pill-used' : 'pill-reversal',
                'amount' => ($isDebit ? '-' : '+') . number_format($amt, 2),
                'amount_numeric' => $isDebit ? -$amt : $amt,
                'date' => $dtx->effective_date,
                'effective_date' => $dtx->effective_date ? $dtx->effective_date->format('d M Y') : 'N/A',
                'expiry_date' => null,
                'description' => $dtx->reason ?: ($dtx->leave_request_id ? 'Deducted for Leave Request #' . $dtx->leave_request_id : 'Comp-Off ledger movement'),
                'reference' => $dtx->leave_request_id ? 'LEAVE #' . $dtx->leave_request_id : 'TX #' . $dtx->id,
                'action_by' => $directActionBy,
                'created_at' => $dtx->created_at,
            ]);
        }

        // Sort transactions by date descending
        $compOffTransactions = $transactionItems->sortByDesc(function ($item) {
            return $item->date ? $item->date->timestamp : 0;
        })->values();

        $accesses = $this->accesses();
        $grantUuid = (string) Str::uuid();

        return view('hrms.leave.comp_offs.index', compact(
            'compOffs',
            'compOffTransactions',
            'stats',
            'holidayWorkRequests',
            'employees',
            'targetEmployee',
            'isEmployeeRole',
            'canViewOthers',
            'canManageCompOff',
            'grantEmployees',
            'grantUuid',
            'selectedMonth',
            'year',
            'accesses'
        ))->with('active', 'leave_management');
    }

    public function grantManual(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');

        $isExplicitAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true);

        $isEmployee = ($roleId === 7 || $roleName === 'employee' || (method_exists($user, 'isEmployee') && $user->isEmployee())) && ! $isExplicitAdmin;

        abort_if($isEmployee, 403, 'Unauthorized action.');
        abort_unless($isExplicitAdmin || $this->userHasPermission('leave.comp_off.manage'), 403, 'Unauthorized action.');

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees_new,id'],
            'days' => ['required', 'string', 'regex:/^\d+(?:\.\d{1,2})?$/'],
            'effective_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
            'grant_uuid' => ['required', 'uuid'],
        ]);

        $employee = EmployeeM::query()->findOrFail((int) $validated['employee_id']);
        $effectiveDate = Carbon::parse($validated['effective_date'], 'Asia/Kolkata');

        try {
            $lot = $this->compOffService->grantManualCompOff(
                $employee,
                (string) $validated['days'],
                $effectiveDate,
                (string) $validated['reason'],
                (int) Auth::id(),
                (string) $validated['grant_uuid']
            );
        } catch (\DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('hrms.comp_offs.index', [
            'month' => $effectiveDate->month,
            'year' => $effectiveDate->year,
        ])->with('success', 'Comp-Off credit is recorded for ' . ($employee->display_name ?? $employee->user?->name ?? 'employee') . ' and expires on ' . $lot->expiry_date->format('d M Y') . '.');
    }

    public function approveHolidayWork($id)
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');

        $isExplicitAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true);

        $isEmployee = ($roleId === 7 || $roleName === 'employee' || (method_exists($user, 'isEmployee') && $user->isEmployee())) && ! $isExplicitAdmin;

        abort_if($isEmployee, 403, 'Unauthorized action.');
        abort_unless($isExplicitAdmin || $this->userHasPermission('leave.comp_off.manage'), 403, 'Unauthorized action.');

        $request = HolidayWorkRequestM::with('employee')->findOrFail($id);
        $this->compOffService->generateFromHolidayWork($request, Auth::id());

        return back()->with('success', 'Comp off generated successfully from holiday/weekoff work.');
    }

    public function expire()
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');

        $isExplicitAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true);

        $isEmployee = ($roleId === 7 || $roleName === 'employee' || (method_exists($user, 'isEmployee') && $user->isEmployee())) && ! $isExplicitAdmin;

        abort_if($isEmployee, 403, 'Unauthorized action.');
        abort_unless($isExplicitAdmin || $this->userHasPermission('leave.comp_off.manage'), 403, 'Unauthorized action.');

        $count = $this->compOffService->expireDue();

        return back()->with('success', "{$count} comp off record(s) expired.");
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user, 401);
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');

        $isExplicitAdmin = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true);

        $isEmployee = ($roleId === 7 || $roleName === 'employee' || (method_exists($user, 'isEmployee') && $user->isEmployee())) && ! $isExplicitAdmin;

        abort_if($isEmployee, 403, 'Unauthorized action.');
        abort_unless($isExplicitAdmin || $this->userHasPermission('leave.comp_off.manage'), 403, 'Unauthorized action.');

        $compOff = CompOffM::with('employee')->findOrFail($id);

        $validated = $request->validate([
            'worked_date' => ['required', 'date'],
            'earned_days' => ['required', 'numeric', 'min:0.5', 'max:5.0'],
            'expiry_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:earned,available,used,expired'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $workedDate = Carbon::parse($validated['worked_date'], 'Asia/Kolkata');
        $expiryDate = Carbon::parse($validated['expiry_date'], 'Asia/Kolkata');
        $newStatus = strtolower($validated['status']) === 'available' ? 'earned' : strtolower($validated['status']);

        $compOff->worked_date = $workedDate->toDateString();
        $compOff->earned_days = $validated['earned_days'];
        $compOff->expiry_date = $expiryDate->toDateString();
        $compOff->status = $newStatus;
        if ($request->has('remarks')) {
            $compOff->remarks = trim((string) $validated['remarks']);
        }
        $compOff->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comp-off record updated successfully.',
            ]);
        }

        return back()->with('success', 'Comp-off record updated successfully.');
    }
}
