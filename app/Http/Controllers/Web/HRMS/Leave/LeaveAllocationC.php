<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Services\HRMS\Leave\LeaveAllocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveAllocationC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private LeaveAllocationService $allocationService)
    {
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
        $allocations = LeaveAllocationM::with(['employee.user', 'policy'])
            ->where('year', $year)
            ->orderBy('employee_id');

        $canManageAllocations = $this->userHasPermission('leave.allocation.manage')
            || $this->canViewAll('leave.allocation.view_all')
            || $this->userHasPermission('leave.allocation.view')
            || (auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin());

        $canViewAllAllocations = $canManageAllocations;

        if (! $canViewAllAllocations) {
            $employeeId = $this->ownEmployeeId();
            if ($employeeId) {
                $allocations->where('employee_id', $employeeId);
            } else {
                $allocations->whereRaw('1 = 0');
            }
        }

        $allocations = $allocations->paginate(30);

        if ($canViewAllAllocations) {
            $employees = \Illuminate\Support\Facades\DB::table('employees_new')
                ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
                ->select(
                    'employees_new.id',
                    'employees_new.employee_code',
                    \Illuminate\Support\Facades\DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name"),
                    \Illuminate\Support\Facades\DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as user_name")
                )
                ->orderByRaw("COALESCE(users.name, employees_new.employee_code)")
                ->get();
        } else {
            $employeeId = $this->ownEmployeeId();
            $employees = $employeeId ? $this->scopedEmployeeOptions('leave.allocation.view_all') : collect();
        }

        $policies = LeavePolicyM::orderBy('policy_name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.allocations.index', compact('allocations', 'employees', 'policies', 'year', 'accesses', 'canManageAllocations'))
            ->with('active', 'leave_management');
    }

    public function processAllocations(Request $request)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage') || $this->canViewAll('leave.allocation.view_all'), 403);

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $count = 0;

        foreach (EmployeeM::where('is_active', 1)->orWhereNull('is_active')->cursor() as $employee) {
            $this->allocationService->generateForEmployee($employee, $year, Auth::id());
            $count++;
        }

        return redirect()->route('leave-allocations.index', ['year' => $year])
            ->with('success', "Leave allocations generated for {$count} employee(s) for year {$year}.");
    }

    public function allocateSingle(Request $request)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage') || $this->canViewAll('leave.allocation.view_all'), 403);

        $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        try {
            $employee = EmployeeM::findOrFail($request->employee_id);
            $year = (int) $request->year;
            $this->allocationService->generateForEmployee($employee, $year, Auth::id());

            return redirect()->route('leave-allocations.index', ['year' => $year])
                ->with('success', "Leave allocation generated successfully for year {$year}.");
        } catch (\Throwable $e) {
            Log::error('Single leave allocation failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage') || $this->canViewAll('leave.allocation.view_all'), 403);

        $allocation = LeaveAllocationM::findOrFail($id);

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
            'allocation_from_date' => 'nullable|date',
            'allocation_to_date' => 'nullable|date',
            'allocation_reason' => 'nullable|string|max:255',
            'is_locked' => 'nullable',
        ]);

        $allocation->year = (int) $validated['year'];
        $allocation->policy_id = !empty($validated['policy_id']) ? $validated['policy_id'] : null;
        $allocation->employment_stage = strtolower($validated['employment_stage']);
        $allocation->paid_allocated = (float) $validated['paid_allocated'];
        $allocation->sick_allocated = (float) $validated['sick_allocated'];
        $allocation->comp_off_allocated = (float) ($validated['comp_off_allocated'] ?? 0);

        $allocation->total_allocated = round($allocation->paid_allocated + $allocation->sick_allocated + $allocation->comp_off_allocated, 2);

        $allocation->paid_used = (float) ($validated['paid_used'] ?? $allocation->paid_used ?? 0);
        $allocation->sick_used = (float) ($validated['sick_used'] ?? $allocation->sick_used ?? 0);
        $allocation->comp_off_used = (float) ($validated['comp_off_used'] ?? $allocation->comp_off_used ?? 0);
        $allocation->lwp_used = (float) ($validated['lwp_used'] ?? $allocation->lwp_used ?? 0);
        $allocation->monthly_quota = (float) ($validated['monthly_quota'] ?? $allocation->monthly_quota ?? 0);
        $allocation->monthly_carry_forward = (float) ($validated['monthly_carry_forward'] ?? $allocation->monthly_carry_forward ?? 0);
        if (isset($validated['monthly_used_this_month'])) {
            $allocation->monthly_used_this_month = (float) $validated['monthly_used_this_month'];
        }

        if (array_key_exists('allocation_from_date', $validated)) {
            $allocation->allocation_from_date = $validated['allocation_from_date'];
        }
        if (array_key_exists('allocation_to_date', $validated)) {
            $allocation->allocation_to_date = $validated['allocation_to_date'];
        }
        if (array_key_exists('allocation_reason', $validated)) {
            $allocation->allocation_reason = $validated['allocation_reason'];
        }
        $allocation->is_locked = $request->has('is_locked') && $request->is_locked == 1;

        $this->allocationService->recalculateAllocationFields($allocation);
        $allocation->save();

        return redirect()->route('leave-allocations.index', ['year' => $allocation->year])
            ->with('success', 'Leave allocation updated successfully.');
    }

    public function destroy($id)
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
        $employee = EmployeeM::where('user_id', Auth::id())->first();
        if (! $employee) {
            return response()->json(['error' => 'No employee profile found.'], 404);
        }

        $allocation = $this->allocationService->getOrGenerate($employee, Carbon::now('Asia/Kolkata')->year, Auth::id());

        return response()->json([
            'total_allocated' => $allocation->total_allocated,
            'total_remaining' => $allocation->total_remaining,
            'paid_remaining' => $allocation->paid_remaining,
            'sick_remaining' => $allocation->sick_remaining,
            'comp_off_remaining' => $allocation->comp_off_remaining,
            'lwp_used' => $allocation->lwp_used,
        ]);
    }
}
