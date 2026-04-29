<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\LeaveAllocation;
use App\Models\Holiday;
use Carbon\Carbon;

class LeaveAllocationController extends Controller
{
    /**
     * Display all allocations for the current year
     */
    public function index()
    {
        $year = date('Y');
        $allocations = LeaveAllocation::with('employee.employeeDetail')
            ->where('year', $year)
            ->orderBy('employee_id')
            ->get();

        $employees = Employee::with('employeeDetail')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $accesses = \App\Models\Access::where('role_id', auth()->user()->role_id)->get();

        return view('pages.leave_allocations.index', compact('allocations', 'employees', 'year', 'accesses'))
            ->with('active', 'leave-allocations');
    }

    /**
     * Run mass allocation for all active employees for the given year
     */
    public function processAllocations(Request $request)
    {
        $year = $request->year ?? date('Y');
        $employees = Employee::where('is_active', 1)->get();

        $count = 0;
        foreach ($employees as $employee) {
            $this->calculateAllocationForEmployee($employee, $year);
            $count++;
        }

        return back()->with('success', "Leave Allocations for {$year} calculated for {$count} employees successfully.");
    }

    /**
     * Allocate leave for a single employee (Admin action)
     */
    public function allocateSingle(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year'        => 'required|integer|min:2020|max:2099',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $this->calculateAllocationForEmployee($employee, $request->year);

        return back()->with('success', "Leave successfully allocated for {$employee->name} ({$request->year}).");
    }

    /**
     * THE MATH ENGINE – Single Employee
     * 100% Accurate Pro-Rata Policy:
     *   Intern / Probation → exactly 1 PL fixed (Total Year)
     *   Permanent (Full-Time) → 18 PL/year (1.5 per month) and 7 SL/year (0.5833 per month)
     *   Pro-rata logic: Calculated on remaining full months of joining year.
     */
    public function calculateAllocationForEmployee(Employee $employee, $year)
    {
        if (!$employee->start_of_contract) {
            return null;
        }

        $joiningDate = Carbon::parse($employee->start_of_contract);

        // Joined in a future year
        if ($joiningDate->year > (int)$year) {
            return $this->saveAllocation($employee->id, $year, 0, 0);
        }

        // ── 1. INTERN / PROBATION ────────────────────────────────────
        // Policy: Exactly 1 PL total allocation (no SL)
        $isPermanent = $this->isEmployeePermanent($employee, $year);
        
        if ($employee->employment_type === 'Intern' || !$isPermanent) {
            return $this->saveAllocation($employee->id, $year, 1, 0);
        }

        // ── 2. PERMANENT PRO-RATA ────────────────────────────────────
        // Rate: 1.5 PL / month, 0.5833 SL / month
        // Count: If join on 1st -> that month + remaining. Else -> only remaining full months.
        
        $accrualMonthCount = 12; // Base for existing employees

        if ($joiningDate->year === (int)$year) {
            $month = (int)$joiningDate->month;
            $day   = (int)$joiningDate->day;
            
            // If joined on 1st, include joining month. Else count only subsequent months.
            $accrualMonthCount = (12 - $month) + ($day === 1 ? 1 : 0);
        }

        $totalPl = $accrualMonthCount * 1.5;
        $totalSl = round($accrualMonthCount * (7 / 12), 2); 

        return $this->saveAllocation($employee->id, $year, $totalPl, $totalSl);
    }

    /**
     * Determine if an employee should be treated as "Permanent" (Confirmed)
     */
    private function isEmployeePermanent(Employee $employee, $year): bool
    {
        // 1. Explicitly Permanent?
        if ($employee->probation_status === 'Permanent') {
            return true;
        }
        
        // 2. In Probation? (Fixed 1 PL)
        if ($employee->probation_status === 'Probation') {
            // Check if probation has elapsed based on date
            if ($employee->probation_end_date) {
                $probationEnd = Carbon::parse($employee->probation_end_date);
                if ($probationEnd->year < (int)$year) {
                    return true;
                }
            }
            return false;
        }

        // 3. Interns are never permanent members (Fixed 1 PL)
        if ($employee->employment_type === 'Intern') {
            return false;
        }

        // 4. Default for Full-Time with no status is treated as Permanent for allocation safety
        return $employee->employment_type === 'Full-Time';
    }

    /**
     * Persist or Update Allocation
     */
    private function saveAllocation(int $empId, $year, $pl, $sl): LeaveAllocation
    {
        $alloc = LeaveAllocation::firstOrNew([
            'employee_id' => $empId,
            'year'        => $year,
        ]);

        $alloc->total_pl = $pl;
        $alloc->total_sl = $sl;

        if (!$alloc->exists) {
            $alloc->used_pl  = 0;
            $alloc->used_sl  = 0;
            $alloc->lwp_days = 0;
        }

        $alloc->save();
        return $alloc;
    }

    /**
     * Return live balance JSON for AJAX calls
     */
    public function getBalance(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        if (!$employee) return response()->json(['error' => 'No profile'], 404);

        $year = date('Y');
        $alloc = LeaveAllocation::where('employee_id', $employee->id)
            ->where('year', $year)->first();

        return response()->json([
            'pl_total'   => $alloc->total_pl ?? 0,
            'pl_used'    => $alloc->used_pl  ?? 0,
            'pl_balance' => max(0, ($alloc->total_pl ?? 0) - ($alloc->used_pl ?? 0)),
            'sl_total'   => $alloc->total_sl ?? 0,
            'sl_used'    => $alloc->used_sl  ?? 0,
            'sl_balance' => max(0, ($alloc->total_sl ?? 0) - ($alloc->used_sl ?? 0)),
            'lwp_count'  => $alloc->lwp_days ?? 0,
        ]);
    }
}
