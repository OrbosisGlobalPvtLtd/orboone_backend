<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HRMS\Leave\LeaveAllocationM as LeaveAllocation;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Leave\HolidayM as Holiday;
use App\Services\HRMS\Leave\LeaveS;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestC extends Controller
{
    private LeaveS $leaveService;

    public function __construct(LeaveS $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Employee's own leave dashboard
     */
    public function index()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) abort(403, 'No employee profile linked to your account.');

        $year     = date('Y');
        $requests = \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
            ->latest()
            ->get();

        $allocation = LeaveAllocation::where('employee_id', $employee->id)
            ->where('year', $year)->first();

        $balancePl  = $allocation ? max(0, $allocation->total_pl - $allocation->used_pl)  : 0;
        $balanceSl  = $allocation ? max(0, $allocation->total_sl - $allocation->used_sl)  : 0;
        $lwpCount   = $allocation ? $allocation->lwp_days : 0;

        $totalPl    = $allocation->total_pl ?? 0;
        $totalSl    = $allocation->total_sl ?? 0;

        $accesses   = \App\Models\Core\AccessM::where('role_id', Auth::user()->role_id)->get();

        return view('hrms.leave.requests.index', compact(
            'requests', 'allocation',
            'balancePl', 'balanceSl', 'lwpCount',
            'totalPl', 'totalSl', 'accesses'
        ))->with('active', 'leave-requests');
    }

    /**
     * Show apply form
     */
    public function create()
    {
        $accesses = \App\Models\Core\AccessM::where('role_id', Auth::user()->role_id)->get();
        return view('hrms.leave.requests.create', compact('accesses'))
            ->with('active', 'leave-requests');
    }

    /**
     * Store a new leave request
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:PL,SL',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|max:1000',
        ]);

        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) return back()->with('error', 'Employee profile not found.');

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        // Calculate working days excluding Sundays and national holidays
        $totalDays = $this->leaveService->calculateWorkingDays($start, $end);

        if ($totalDays <= 0) {
            return back()->with('error', 'No working days in the selected range (all days are Sundays or National Holidays).')
                         ->withInput();
        }

        \App\Models\HRMS\Leave\LeaveApplicationM::create([
            'employee_id' => $employee->id,
            'leave_type'  => $request->leave_type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'total_days'  => $totalDays,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return redirect()->route('leave-requests.index')
            ->with('success', "Leave request submitted for {$totalDays} working day(s). Awaiting approval.");
    }

    /**
     * Calculate working days between two dates (excludes Sundays and national holidays)
     */
    public static function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        return app(LeaveS::class)->calculateWorkingDays($start, $end);
    }
}
