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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveAllocationC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private LeaveAllocationService $allocationService,
        private EmployeeEligibilityS $eligibilityService
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

        // Server-side Default Sorting by Employee Name (A-Z)
        $allocationsQuery->select('leave_allocations.*')
            ->join('employees_new', 'employees_new.id', '=', 'leave_allocations.employee_id')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->orderByRaw("COALESCE(users.name, employees_new.employee_code) ASC");

        $allocations = $allocationsQuery->paginate(30)->withQueryString();

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

        return view('hrms.leave.allocations.index', compact('allocations', 'employees', 'policies', 'year', 'accesses', 'canManageAllocations'))
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
                'paid_allocated' => (float) $allocation->paid_allocated,
                'sick_allocated' => (float) $allocation->sick_allocated,
                'comp_off_allocated' => (float) ($allocation->comp_off_allocated ?? 0),
                'total_allocated' => (float) ($allocation->total_allocated ?? ($allocation->paid_allocated + $allocation->sick_allocated)),
                'paid_used' => (float) ($allocation->paid_used ?? 0),
                'sick_used' => (float) ($allocation->sick_used ?? 0),
                'comp_off_used' => (float) ($allocation->comp_off_used ?? 0),
                'lwp_used' => (float) ($allocation->lwp_used ?? 0),
                'monthly_quota' => (float) ($allocation->monthly_quota ?? 0),
                'monthly_carry_forward' => (float) ($allocation->monthly_carry_forward ?? 0),
                'monthly_used_this_month' => (float) ($allocation->monthly_used_this_month ?? 0),
                'total_monthly_remaining_paid' => (float) ($allocation->total_monthly_remaining_paid ?? 0),
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

        return redirect()->route('leave-allocations.index', ['year' => $year])
            ->with('success', "Leave allocations for {$year}: {$summary['successful_allocations']} allocated, {$summary['skipped']} skipped, {$summary['failed']} failed.");
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
            $this->allocationService->generateSingle($employeeId, $year, Auth::id());

            return redirect()
                ->route('leave-allocations.index', ['year' => $year])
                ->with('success', "Leave allocation generated successfully for year {$year}.");
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

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'policy_id' => 'nullable|exists:leave_policies,id',
            'employment_stage' => 'required|string|max:50',
            'paid_allocated' => 'required|numeric|min:0',
            'sick_allocated' => 'required|numeric|min:0',
            'comp_off_allocated' => 'nullable|numeric|min:0',
            'paid_used' => 'nullable|numeric|min:0',
            'sick_used' => 'nullable|numeric|min:0',
            'comp_off_used' => 'nullable|numeric|min:0',
            'lwp_used' => 'nullable|numeric|min:0',
            'monthly_quota' => 'nullable|numeric|min:0',
            'monthly_carry_forward' => 'nullable|numeric|min:0',
            'monthly_used_this_month' => 'nullable|numeric|min:0',
            'total_monthly_remaining_paid' => 'nullable|numeric|min:0',
            'allocation_from_date' => 'nullable|date',
            'allocation_to_date' => 'nullable|date',
            'allocation_reason' => 'nullable|string|max:255',
            'is_locked' => 'nullable',
        ]);

        $allocation = $this->allocationService->updateAllocation($id, $validated, Auth::id());

        return redirect()->route('leave-allocations.index', ['year' => $allocation->year])
            ->with('success', 'Leave allocation updated successfully.');
    }

    public function destroy(int $id)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage'), 403);

        $allocation = LeaveAllocationM::findOrFail($id);
        $year = $allocation->year;
        $allocation->delete();

        return redirect()->route('leave-allocations.index', ['year' => $year])
            ->with('success', 'Leave allocation deleted successfully.');
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
