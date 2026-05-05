<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM as LeaveAllocation;
use App\Services\HRMS\Leave\LeaveS;
use Illuminate\Http\Request;

class LeaveAllocationC extends Controller
{
    private LeaveS $leaveService;

    public function __construct(LeaveS $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index()
    {
        $year = date('Y');
        $allocations = LeaveAllocation::with('employee.employeeDetail')
            ->where('year', $year)
            ->orderBy('employee_id')
            ->get();

        $employees = EmployeeM::with('employeeDetail')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $accesses = \App\Models\Core\AccessM::where('role_id', auth()->user()->role_id)->get();

        return view('hrms.leave.allocations.index', compact('allocations', 'employees', 'year', 'accesses'))
            ->with('active', 'leave-allocations');
    }

    public function processAllocations(Request $request)
    {
        $year = (int) ($request->year ?? date('Y'));
        $employees = EmployeeM::where('is_active', 1)->get();

        $count = 0;
        foreach ($employees as $employee) {
            $this->leaveService->calculateAllocationForEmployee($employee, $year);
            $count++;
        }

        return back()->with('success', "Leave Allocations for {$year} calculated for {$count} employees successfully.");
    }

    public function allocateSingle(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        $employee = EmployeeM::findOrFail($request->employee_id);
        $this->leaveService->calculateAllocationForEmployee($employee, (int) $request->year);

        return back()->with('success', "Leave successfully allocated for {$employee->name} ({$request->year}).");
    }

    public function calculateAllocationForEmployee(EmployeeM $employee, $year)
    {
        return $this->leaveService->calculateAllocationForEmployee($employee, (int) $year);
    }

    public function getBalance(Request $request)
    {
        $employee = EmployeeM::where('user_id', auth()->id())->first();
        if (! $employee) {
            return response()->json(['error' => 'No profile'], 404);
        }

        $year = date('Y');
        $alloc = LeaveAllocation::where('employee_id', $employee->id)
            ->where('year', $year)
            ->first();

        return response()->json([
            'pl_total' => $alloc->total_pl ?? 0,
            'pl_used' => $alloc->used_pl ?? 0,
            'pl_balance' => max(0, ($alloc->total_pl ?? 0) - ($alloc->used_pl ?? 0)),
            'sl_total' => $alloc->total_sl ?? 0,
            'sl_used' => $alloc->used_sl ?? 0,
            'sl_balance' => max(0, ($alloc->total_sl ?? 0) - ($alloc->used_sl ?? 0)),
            'lwp_count' => $alloc->lwp_days ?? 0,
        ]);
    }
}
