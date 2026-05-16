<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
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

        if (! $this->canViewAll('leave.allocation.view_all') && ! $this->userHasPermission('leave.allocation.manage')) {
            $employeeId = $this->ownEmployeeId();
            abort_if(! $employeeId, 403);
            $allocations->where('employee_id', $employeeId);
        }

        $allocations = $allocations->paginate(30);
        $employees = $this->scopedEmployeeOptions('leave.allocation.view_all');
        $accesses = $this->accesses();
        $canManageAllocations = $this->userHasPermission('leave.allocation.manage');

        return view('hrms.leave.allocations.index', compact('allocations', 'employees', 'year', 'accesses', 'canManageAllocations'))
            ->with('active', 'leave_management');
    }

    public function processAllocations(Request $request)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage'), 403);

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $count = 0;

        foreach (EmployeeM::where('is_active', 1)->orWhereNull('is_active')->cursor() as $employee) {
            $this->allocationService->generateForEmployee($employee, $year, Auth::id());
            $count++;
        }

        return back()->with('success', "Leave allocations generated for {$count} employee(s).");
    }

    public function allocateSingle(Request $request)
    {
        abort_unless($this->userHasPermission('leave.allocation.manage'), 403);

        $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        try {
            $employee = EmployeeM::findOrFail($request->employee_id);
            $this->allocationService->generateForEmployee($employee, (int) $request->year, Auth::id());

            return back()->with('success', 'Leave allocation generated successfully.');
        } catch (\Throwable $e) {
            Log::error('Single leave allocation failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
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
