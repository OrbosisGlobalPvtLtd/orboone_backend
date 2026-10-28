<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Services\HRMS\Employee\EmployeeEligibilityS;
use App\Services\HRMS\Leave\LeaveAllocationService;
use App\Services\HRMS\Leave\LeaveBalanceService;
use App\Services\HRMS\Leave\LeaveMonthlyBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LeaveAllocationC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private LeaveAllocationService $allocationService,
        private EmployeeEligibilityS $eligibilityService,
        private LeaveBalanceService $balanceService
    ) {
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('leave.allocation.view_all')
            || $this->userHasPermission('leave.allocation.view_own')
            || $this->userHasPermission('leave.allocation.view')
            || $this->userHasPermission('leave.allocation.manage'),
            403
        );

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $allocationsQuery = LeaveAllocationM::with(['employee.user', 'employee.profile', 'policy'])
            ->where('year', $year)
            ->whereHas('employee', function ($q) {
                $q->activeApproved();
            });

        // Server-side filter: Search (name, employee_code)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $allocationsQuery->whereHas('employee', function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Server-side filter: Employment Stage
        if ($request->filled('stage')) {
            $allocationsQuery->where('employment_stage', strtolower(trim($request->stage)));
        }

        // Server-side filter: Leave Policy
        if ($request->filled('policy_id')) {
            $allocationsQuery->where('policy_id', $request->policy_id);
        } elseif ($request->filled('policy')) {
            $policyName = trim($request->policy);
            $allocationsQuery->whereHas('policy', function ($p) use ($policyName) {
                $p->where('policy_name', 'like', "%{$policyName}%");
            });
        }

        // Server-side filter: Status
        if ($request->filled('status')) {
            $status = strtolower(trim($request->status));
            $today = Carbon::today('Asia/Kolkata')->toDateString();
            if ($status === 'active') {
                $allocationsQuery->where(function ($q) use ($today) {
                    $q->where(function ($sub) use ($today) {
                        $sub->whereNull('allocation_from_date')
                            ->orWhere('allocation_from_date', '<=', $today);
                    })->where(function ($sub) use ($today) {
                        $sub->whereNull('allocation_to_date')
                            ->orWhere('allocation_to_date', '>=', $today);
                    })->where(function ($sub) {
                        $sub->where('is_locked', 0)->orWhereNull('is_locked');
                    });
                });
            } elseif ($status === 'upcoming') {
                $allocationsQuery->where('allocation_from_date', '>', $today);
            } elseif ($status === 'inactive') {
                $allocationsQuery->where(function ($q) use ($today) {
                    $q->where('is_locked', 1)
                        ->orWhere('allocation_to_date', '<', $today);
                });
            }
        }

        $canManageAllocations = $this->userHasPermission('leave.allocation.manage')
            || $this->canViewAll('leave.allocation.view_all')
            || $this->userHasPermission('leave.allocation.view');

        $canViewAllAllocations = $canManageAllocations;

        if (! $canViewAllAllocations) {
            $employeeId = $this->ownEmployeeId();
            if ($employeeId) {
                $allocationsQuery->where('employee_id', $employeeId);
            } else {
                $allocationsQuery->whereRaw('1 = 0');
            }
        }

        $activeTab = in_array($request->query('tab'), ['monthly', 'transactions'], true) ? (string) $request->query('tab') : 'allocations';
        $monthlyBalances = collect();
        $transactions = collect();
        $currentMonth = (int) now('Asia/Kolkata')->month;
        $selectedMonth = $request->query('month');
        if ($selectedMonth === null) {
            $selectedMonth = (string) $currentMonth;
        }

        if ($activeTab === 'monthly') {
            $monthlyQuery = app(LeaveMonthlyBalanceService::class)
                ->queryForYear($year)
                ->whereHas('employee');

            if ($selectedMonth !== '' && $selectedMonth !== 'all') {
                $monthlyQuery->where('leave_monthly_balances.month', (int) $selectedMonth);
            }

            if (! $canViewAllAllocations) {
                $monthlyQuery->where('leave_monthly_balances.employee_id', $this->ownEmployeeId() ?: 0);
            }
            if ($request->filled('search')) {
                $search = trim((string) $request->query('search'));
                $monthlyQuery->whereHas('employee', function ($employeeQuery) use ($search): void {
                    $employeeQuery->where('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            }
            if ($request->filled('stage')) {
                $monthlyQuery->where('leave_monthly_balances.employment_stage', strtolower(trim((string) $request->query('stage'))));
            }
            if ($request->filled('policy_id')) {
                $monthlyQuery->where('leave_monthly_balances.policy_id', (int) $request->query('policy_id'));
            } elseif ($request->filled('policy')) {
                $policyName = trim((string) $request->query('policy'));
                $monthlyQuery->whereHas('policy', fn ($policyQuery) => $policyQuery->where('policy_name', 'like', "%{$policyName}%"));
            }

            $monthlyQuery->join('employees_new', 'employees_new.id', '=', 'leave_monthly_balances.employee_id')
                ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
                ->orderByRaw("COALESCE(users.name, employees_new.employee_code) ASC")
                ->orderBy('leave_monthly_balances.month')
                ->select('leave_monthly_balances.*');

            $monthlyBalances = $monthlyQuery->paginate(50)->withQueryString();
        }
        if ($activeTab === 'transactions') {
            $transactionQuery = DB::table('leave_balance_transactions as t')
                ->join('employees_new as e', 'e.id', '=', 't.employee_id')
                ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
                ->leftJoin('users as cu', 'cu.id', '=', 't.created_by')
                ->where('e.employment_status', 'active')
                ->selectRaw("t.*, COALESCE(eu.name, e.employee_code) as employee_name, e.employee_code, COALESCE(cu.name, CASE WHEN t.created_by IS NULL THEN 'Scheduler' ELSE 'Unknown user' END) as actor_name")
                ->orderByDesc('t.effective_date')->orderByDesc('t.id');
            if (! $canViewAllAllocations) $transactionQuery->where('t.employee_id', $this->ownEmployeeId() ?: 0);

            $txYear = $request->filled('year') ? (int) $request->query('year') : $year;
            $transactionQuery->where('t.period_year', $txYear);

            if ($selectedMonth !== '' && $selectedMonth !== 'all') {
                $transactionQuery->where('t.period_month', (int) $selectedMonth);
            }

            foreach (['employee_id' => 't.employee_id', 'bucket' => 't.bucket', 'type' => 't.transaction_type', 'source' => 't.source'] as $input => $column) {
                if ($request->filled($input)) $transactionQuery->where($column, $request->query($input));
            }
            if ($request->filled('employee_code')) {
                $code = trim((string) $request->query('employee_code'));
                $transactionQuery->where('e.employee_code', 'like', "%{$code}%");
            }
            if ($request->filled('search')) {
                $needle = trim((string) $request->query('search'));
                $transactionQuery->where(fn ($query) => $query->where('eu.name', 'like', "%{$needle}%")
                    ->orWhere('e.employee_code', 'like', "%{$needle}%"));
            }
            $transactions = $transactionQuery->paginate(50)->withQueryString();
        }

        // Server-side Default Sorting by Employee Name (A-Z)
        $allocationsQuery->select('leave_allocations.*')
            ->join('employees_new', 'employees_new.id', '=', 'leave_allocations.employee_id')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->orderByRaw("COALESCE(users.name, employees_new.employee_code) ASC");

        $allocations = $allocationsQuery->paginate(30)->withQueryString();
        $ledgerDisplaySummaries = $this->balanceService->getAllocationDisplaySummaries(
            $allocations->getCollection(),
            Carbon::now('Asia/Kolkata')
        );

        if ($canViewAllAllocations) {
            $employees = EmployeeM::activeApproved()
                ->with('user')
                ->get()
                ->map(function ($emp) {
                    $name = $emp->user->name ?? $emp->employee_code ?? 'N/A';
                    return (object) [
                        'id' => $emp->id,
                        'employee_code' => $emp->employee_code,
                        'display_name' => $name,
                        'user_name' => $name,
                    ];
                })
                ->sortBy('display_name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        } else {
            $employeeId = $this->ownEmployeeId();
            $employees = $employeeId ? $this->scopedEmployeeOptions('leave.allocation.view_all') : collect();
        }

        $policies = LeavePolicyM::orderBy('policy_name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.allocations.index', compact('allocations', 'employees', 'policies', 'year', 'accesses', 'canManageAllocations', 'ledgerDisplaySummaries', 'activeTab', 'monthlyBalances', 'transactions', 'selectedMonth'))
            ->with('active', 'leave_management');
    }

    public function show(int $id)
    {
        abort_unless(
            $this->userHasPermission('leave.allocation.view_all')
            || $this->userHasPermission('leave.allocation.view_own')
            || $this->userHasPermission('leave.allocation.view')
            || $this->userHasPermission('leave.allocation.manage'),
            403
        );

        $allocation = LeaveAllocationM::with(['employee.user', 'employee.profile', 'policy'])->findOrFail($id);

        $isLedgerBacked = $allocation->isLedgerBacked();
        $paidAllocated = (float) $allocation->paid_allocated;
        $sickAllocated = (float) $allocation->sick_allocated;
        $totalAllocated = (float) ($allocation->total_allocated ?? ($paidAllocated + $sickAllocated));
        $accruedPaid = (float) $allocation->paid_allocated;
        $accruedSick = (float) $allocation->sick_allocated;
        $accruedPeriod = Carbon::now('Asia/Kolkata')->format('M Y');

        if ($isLedgerBacked) {
            $summaries = $this->balanceService->getAllocationDisplaySummaries(
                collect([$allocation]),
                Carbon::now('Asia/Kolkata')
            );
            $summary = $summaries[$allocation->id] ?? null;
            if ($summary) {
                $paidAllocated = round((float) ($summary['year_end_pl'] ?? $paidAllocated), 2);
                $sickAllocated = round((float) ($summary['year_end_sl'] ?? $sickAllocated), 2);
                $totalAllocated = round((float) ($summary['year_end_total'] ?? ($paidAllocated + $sickAllocated)), 2);
                $accruedPaid = round((float) ($summary['accrued_to_date_pl'] ?? $accruedPaid), 2);
                $accruedSick = round((float) ($summary['accrued_to_date_sl'] ?? $accruedSick), 2);
                $accruedPeriod = $summary['period_label'] ?? $accruedPeriod;
            }
        }

        return response()->json([
            'success' => true,
            'allocation' => [
                'id' => $allocation->id,
                'employee_id' => $allocation->employee_id,
                'employee_name' => $allocation->employee?->user?->name ?? $allocation->employee?->employee_code ?? 'N/A',
                'employee_code' => $allocation->employee?->employee_code ?? 'N/A',
                'policy_id' => $allocation->policy_id,
                'policy_name' => $allocation->policy?->policy_name ?? 'Default Policy',
                'year' => (int) $allocation->year,
                'employment_stage' => $allocation->employment_stage,
                'paid_allocated' => $paidAllocated,
                'sick_allocated' => $sickAllocated,
                'comp_off_allocated' => (float) ($allocation->comp_off_allocated ?? 0),
                'comp_off_remaining' => (float) ($allocation->comp_off_remaining ?? 0),
                'total_allocated' => $totalAllocated,
                'accrued_paid_to_date' => $accruedPaid,
                'accrued_sick_to_date' => $accruedSick,
                'accrued_period' => $accruedPeriod,
                'paid_used' => (float) ($allocation->paid_used ?? 0),
                'sick_used' => (float) ($allocation->sick_used ?? 0),
                'comp_off_used' => (float) ($allocation->comp_off_used ?? 0),
                'lwp_used' => (float) ($allocation->lwp_used ?? 0),
                'paid_remaining' => (string) ($allocation->paid_remaining ?? '0.00'),
                'sick_remaining' => (string) ($allocation->sick_remaining ?? '0.00'),
                'is_ledger_backed' => $isLedgerBacked,
                'adjustment_uuid' => (string) Str::uuid(),
                'allocation_from_date' => $allocation->allocation_from_date ? Carbon::parse($allocation->allocation_from_date)->format('Y-m-d') : '',
                'allocation_to_date' => $allocation->allocation_to_date ? Carbon::parse($allocation->allocation_to_date)->format('Y-m-d') : '',
                'allocation_reason' => $allocation->allocation_reason ?? '',
                'is_locked' => (bool) $allocation->is_locked,
                'is_unpaid_intern' => $this->allocationService->isUnpaidIntern($allocation),
                'update_url' => route('leave-allocations.update', $allocation->id),
            ]
        ]);
    }

    public function processAllocations(Request $request)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage') || $this->canViewAll('leave.allocation.view_all'), 403);

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $summary = $this->allocationService->generateYearly($year, Auth::id());

        $message = "Leave allocation run for {$year}: {$summary['successful_allocations']} processed, {$summary['skipped']} skipped, {$summary['failed']} failed.";
        if (($summary['accrual_entries_posted'] ?? 0) > 0) {
            $message .= " Monthly accrual entries posted: {$summary['accrual_entries_posted']}.";
        }
        return redirect()->route('leave-allocations.index', ['year' => $year])->with('success', $message);
    }

    public function allocateSingle(Request $request)
    {
        abort_unless(
            $this->userHasPermission('leave.allocation.manage')
            || $this->canViewAll('leave.allocation.view_all'),
            403
        );

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees_new,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
        ]);

        $year = (int) $validated['year'];
        $employeeId = (int) $validated['employee_id'];

        try {
            $allocation = $this->allocationService->generateSingle($employeeId, $year, Auth::id());
            $message = "Leave allocation is ready for year {$year}.";
            if ($allocation->isLedgerBacked()) {
                $message .= ' Current balance: PL ' . number_format((float) $allocation->paid_remaining, 2)
                    . ' days, SL ' . number_format((float) $allocation->sick_remaining, 2) . ' days.';
            }

            return redirect()
                ->route('leave-allocations.index', ['year' => $year])
                ->with('success', $message);
        } catch (\DomainException $e) {
            return redirect()
                ->route('leave-allocations.index', ['year' => $year])
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Single leave allocation failed.', [
                'employee_id' => $employeeId,
                'year' => $year,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage') || $this->canViewAll('leave.allocation.view_all'), 403);

        $allocation = LeaveAllocationM::query()->findOrFail($id);
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'employment_stage' => ['nullable', 'string', 'max:30'],
            'policy_id' => ['nullable', 'integer', 'exists:leave_policies,id'],
            'paid_allocated' => ['nullable', 'numeric', 'min:0'],
            'sick_allocated' => ['nullable', 'numeric', 'min:0'],
            'comp_off_allocated' => ['nullable', 'numeric', 'min:0'],
            'paid_used' => ['nullable', 'numeric', 'min:0'],
            'sick_used' => ['nullable', 'numeric', 'min:0'],
            'comp_off_used' => ['nullable', 'numeric', 'min:0'],
            'lwp_used' => ['nullable', 'numeric', 'min:0'],
            'allocation_from_date' => ['nullable', 'date'],
            'allocation_to_date' => ['nullable', 'date'],
            'allocation_reason' => ['nullable', 'string', 'max:255'],
            'adjustment_reason' => ['nullable', 'string', 'max:255'],
            'comp_off_reason' => ['nullable', 'string', 'min:3', 'max:255'],
            'is_locked' => ['nullable'],
            'paid_remaining' => ['nullable', 'numeric', 'min:0'],
            'sick_remaining' => ['nullable', 'numeric', 'min:0'],
            'pl_target_edited' => ['nullable'],
            'sl_target_edited' => ['nullable'],
            'adjustment_uuid' => ['nullable', 'uuid'],
            'comp_off_grant_uuid' => ['nullable', 'uuid'],
        ]);
        $validated['is_locked'] = $request->boolean('is_locked');

        try {
            $allocation = $this->allocationService->updateAllocation($id, $validated, Auth::id());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('leave-allocations.index', ['year' => $allocation->year])
            ->with('success', 'Leave allocation updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage'), 403);
        $reason = trim((string) $request->input('reason'));
        if ($reason === '') return back()->with('error', 'Enter a deletion reason.');
        $allocation = LeaveAllocationM::query()->findOrFail($id);
        if (DB::table('leave_balance_transactions')->where('leave_allocation_id', $id)->exists()) {
            return back()->with('error', 'This allocation cannot be deleted because leave ledger transactions reference it.');
        }
        DB::transaction(function () use ($allocation, $reason): void {
            $before = (float) ($allocation->total_remaining ?? 0);
            \App\Models\HRMS\Leave\LeaveBalanceLogM::create([
                'employee_id' => $allocation->employee_id, 'leave_allocation_id' => $allocation->id,
                'action' => 'allocation_manual_delete', 'credit' => 0, 'debit' => max(0, $before),
                'balance_before' => $before, 'balance_after' => 0, 'remarks' => $reason,
                'created_by_user_id' => Auth::id(),
            ]);
            $allocation->delete();
        });
        return back()->with('success', 'Allocation deleted and recorded in the balance log.');
    }

    public function getBalance()
    {
        $balance = $this->allocationService->getEmployeeBalance(Auth::id(), Carbon::now('Asia/Kolkata')->year);
        if (! $balance) {
            return response()->json(['error' => 'No employee profile found.'], 404);
        }

        return response()->json($balance);
    }

    public function calculateQuota(Request $request)
    {
        $preview = $this->allocationService->previewQuota(
            $request->filled('policy_id') ? (int) $request->policy_id : null,
            (string) $request->get('employment_stage', 'permanent'),
            $request->get('allocation_from_date'),
            $request->get('allocation_to_date'),
            $request->filled('employee_id') ? (int) $request->employee_id : null
        );
        return response()->json($preview);
    }
}
