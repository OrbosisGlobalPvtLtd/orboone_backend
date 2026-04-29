<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveAllocation;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Employee's own leave dashboard
     */
    public function index()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) abort(403, 'No employee profile linked to your account.');

        $year     = date('Y');
        $requests = \App\Models\LeaveApplication::where('employee_id', $employee->id)
            ->latest()
            ->get();

        $allocation = LeaveAllocation::where('employee_id', $employee->id)
            ->where('year', $year)->first();

        $balancePl  = $allocation ? max(0, $allocation->total_pl - $allocation->used_pl)  : 0;
        $balanceSl  = $allocation ? max(0, $allocation->total_sl - $allocation->used_sl)  : 0;
        $lwpCount   = $allocation ? $allocation->lwp_days : 0;

        $totalPl    = $allocation->total_pl ?? 0;
        $totalSl    = $allocation->total_sl ?? 0;

        $accesses   = \App\Models\Access::where('role_id', Auth::user()->role_id)->get();

        return view('pages.leave_requests.index', compact(
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
        $accesses = \App\Models\Access::where('role_id', Auth::user()->role_id)->get();
        return view('pages.leave_requests.create', compact('accesses'))
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
        $totalDays = $this->calculateWorkingDays($start, $end);

        if ($totalDays <= 0) {
            return back()->with('error', 'No working days in the selected range (all days are Sundays or National Holidays).')
                         ->withInput();
        }

        \App\Models\LeaveApplication::create([
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
        $holidays = \App\Models\NationalHoliday::whereBetween('holiday_date', [
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
        ])->pluck('holiday_date')->toArray();

        $count   = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // Skip Sunday (Carbon::SUNDAY = 0)
            if ($current->dayOfWeek !== Carbon::SUNDAY
                && !in_array($current->format('Y-m-d'), $holidays)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }
}
