<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Leave\StoreEmployeeLeaveRequest;
use App\Http\Requests\Web\HRMS\Employee\StoreEmployeeRequest;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use App\Models\HRMS\Leave\EmployeeLeaveRequestM as EmployeeLeaveRequest;
use App\Models\Core\LogM as Log;
use App\Models\Core\AccessM as Access;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeLeaveRequestsC extends Controller
{
    private $employeeLeaveRequests;

    public function __construct()
    {
        $this->middleware('auth');

        $this->employeeLeaveRequests = resolve(EmployeeLeaveRequest::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all leave requests, ordered by EmployeeDetail name to allow section-wise display
        $employeeLeaveRequests = EmployeeLeaveRequest::with(['employee.employeeDetail'])
            ->leftJoin('employee_details', 'employee_details.employee_id', '=', 'employee_leave_requests.employee_id')
            ->select('employee_leave_requests.*')
            ->orderBy('employee_details.name', 'ASC')
            ->orderBy('employee_leave_requests.id', 'DESC')
            ->paginate(10);

        $accesses = Access::where('role_id', auth()->user()->role_id)->get();

        return view('hrms.leave.employee_requests.index', compact('employeeLeaveRequests', 'accesses'))->with('active', 'leave-approval');
    }

    public function summary()
    {
        $year = Carbon::now()->year;
        $employees = Employee::with(['leaveAllocations' => function($q) use ($year) {
                $q->where('year', $year);
            }, 'leaveRequests' => function($q) use ($year) {
                $q->whereYear('start_date', $year);
            }])
            ->where('is_active', 1)
            ->get();

        $holidays = \App\Models\HRMS\Leave\HolidayM::orderBy('date', 'asc')->get();
        $accesses = Access::where('role_id', auth()->user()->role_id)->get();

        return view('hrms.leave.employee_requests.summary', compact('employees', 'accesses', 'holidays'))->with('active', 'leave-summary');
    }

    private function initializeLeaveQuotas($employee)
    {
        $year = Carbon::now()->year;
        $joinDate = $employee->start_of_contract ? Carbon::parse($employee->start_of_contract) : Carbon::create($year, 1, 1);
        
        // Effective calculation: months remaining in the year from joining date
        $calcYear = $year;
        if ($joinDate->year > $year) {
            return; // Joined in future year
        }
        
        $startMonth = ($joinDate->year < $year) ? 1 : $joinDate->month;
        $monthsActive = 12 - $startMonth + 1;

        $leaveTypes = [
            'Paid Leave' => ['per_month' => 1.5],
            'Sick Leave' => ['per_month' => 0.58],
            'Casual Leave' => ['per_month' => 0.83], // 10 per year
            'Work From Home' => ['fixed' => 999],
            'Unpaid Leave' => ['fixed' => 999],
        ];

        foreach ($leaveTypes as $type => $rules) {
            $existing = EmployeeLeave::where('employee_id', $employee->id)
                ->where('leave_type', $type)
                ->whereYear('start_date', $year)
                ->first();

            if (!$existing) {
                if (isset($rules['fixed'])) {
                    $allocatedQuota = $rules['fixed'];
                } else {
                    $allocatedQuota = ceil($rules['per_month'] * $monthsActive);
                }
                
                EmployeeLeave::create([
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'leave_type' => $type,
                    'start_date' => Carbon::create($year, 1, 1),
                    'end_date' => Carbon::create($year, 12, 31),
                    'reason' => 'Annual Allocation ' . $year,
                    'leaves_quota' => $allocatedQuota,
                    'used_leaves' => 0,
                    'status' => 'Active'
                ]);
            }
        }
    }

    public function create()
    {
        $user = auth()->user();
        $employee = $user->employee;
        
        // Ensure quotas are initialized for current user
        if ($employee) {
            $this->initializeLeaveQuotas($employee);
        }

        $employeeLeaves = EmployeeLeave::where('employee_id', $employee->id)
            ->whereYear('start_date', Carbon::now()->year)
            ->get();

        $accesses = Access::where('role_id', $user->role_id)->get();

        return view('hrms.leave.employee_requests.create', compact('employee', 'employeeLeaves', 'accesses'))->with('active', 'leave-request');
    }

    public function store(StoreEmployeeLeaveRequest $request)
    {
        $employeeId = $request->input('employee_id') ?? optional(auth()->user()->employee)->id;
        $leaveType = $request->input('leave_type', 'Paid Leave');
        
        $employeeLeave = EmployeeLeave::where('employee_id', $employeeId)
            ->where('leave_type', $leaveType)
            ->whereYear('start_date', Carbon::now()->year)
            ->first();

        if (!$employeeLeave) {
            return back()->with('status', "Error: No $leaveType quota record found for this employee.")->withInput();
        }

        $from = Carbon::parse($request->input('from'));
        $to = Carbon::parse($request->input('to'));

        Carbon::setWeekendDays([Carbon::SUNDAY]);
        $diff = $from->diffInWeekdays($to) + 1;

        $available = $employeeLeave->leaves_quota - $employeeLeave->used_leaves;

        // Allow WFH and Unpaid even if quota is "exceeded" (though we set them high)
        if (!in_array($leaveType, ['Work From Home', 'Unpaid Leave']) && $available < $diff) {
            return back()->with('status', "Error: Insufficient $leaveType quota. Requested $diff days, but only $available available.")->withInput();
        }

        EmployeeLeaveRequest::create([
            'employee_id' => $employeeId,
            'leave_type' => $leaveType,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'message' => $request->input('message'),
            'status' => 'Pending'
        ]);

        Log::create([
            'description' => optional(auth()->user()->employee)->display_name . " applied for $diff days $leaveType from '" . $request->input('from') . "' to '" . $request->input('to') . "'"
        ]);

        return redirect()->route('employees-leave-request')->with('status', 'Leave application submitted successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveRequestM  $employeeLeaveRequest
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeLeaveRequest $employeeLeaveRequest)
    {
        $employeeLeaveRequest->load('employee', 'checkedBy');

        $employeeLeave = EmployeeLeave::where('employee_id', $employeeLeaveRequest->employee_id)->first();

        $from = Carbon::parse($employeeLeaveRequest->from);
        $to = Carbon::parse($employeeLeaveRequest->to);

        Carbon::setWeekendDays([
            Carbon::SUNDAY,
        ]);

        $diff = $from->diffInWeekdays($to);

        return view('hrms.leave.employee_requests.show', compact('employeeLeaveRequest', 'employeeLeave', 'diff'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveRequestM  $employeeLeaveRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeLeaveRequest $employeeLeaveRequest)
    {
        return view('hrms.leave.employee_requests.edit', compact('employeeLeaveRequest'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveRequestM  $employeeLeaveRequest
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, EmployeeLeaveRequest $employeeLeaveRequest)
    {
        if ($request->type == 'edit') {
            $employeeLeaveRequest->update([
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'message' => $request->input('message')
            ]);

            Log::create([
                'description' => optional(auth()->user()->employee)->display_name . " updated a leave request's detail"
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Leave request updated successfully.']);
            }

            return redirect()->route('employees-leave-request')->with('status', 'Successfully updated employee leave request.');
        } else if ($request->type == 'accept') {
            $employeeLeave = EmployeeLeave::where('employee_id', $employeeLeaveRequest->employee_id)
                ->where('leave_type', $employeeLeaveRequest->leave_type ?? 'Paid Leave')
                ->whereYear('start_date', Carbon::parse($employeeLeaveRequest->from)->year)
                ->first();

            // Ensure quota record exists even at approval time
            if (!$employeeLeave) {
                $year = Carbon::parse($employeeLeaveRequest->from)->year;
                $employeeLeave = EmployeeLeave::create([
                    'employee_id' => $employeeLeaveRequest->employee_id,
                    'user_id' => $employeeLeaveRequest->employee->user_id ?? auth()->user()->id,
                    'leave_type' => $employeeLeaveRequest->leave_type ?? 'Paid Leave',
                    'start_date' => Carbon::create($year, 1, 1),
                    'end_date' => Carbon::create($year, 12, 31),
                    'reason' => 'Auto-initialized at approval',
                    'leaves_quota' => 24,
                    'used_leaves' => 0,
                    'status' => 'Active'
                ]);
            }

            $from = Carbon::parse($employeeLeaveRequest->from);
            $to = Carbon::parse($employeeLeaveRequest->to);

            Carbon::setWeekendDays([Carbon::SUNDAY]);
            $diff = $from->diffInWeekdays($to) + 1;

            $employeeLeaveRequest->update([
                'status' => 'Approved',
                'checked_by' => optional(auth()->user()->employee)->id,
                'comment' => $request->input('comment')
            ]);

            $employeeLeave->update(['used_leaves' => $employeeLeave->used_leaves + $diff]);

            Log::create([
                'description' => optional(auth()->user()->employee)->display_name . " approved " . optional($employeeLeaveRequest->employee)->display_name  . "'s leave request from '" . $employeeLeaveRequest->from . "' to '" . $employeeLeaveRequest->to . "'"
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Leave request approved successfully.']);
            }

            return redirect()->route('employees-leave-request')->with('status', 'Successfully accepted employee leave request.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HRMS\Leave\EmployeeLeaveRequestM  $employeeLeaveRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, EmployeeLeaveRequest $employeeLeaveRequest)
    {
        $employeeLeaveRequest->update([
            'status' => 'Rejected',
            'checked_by' => optional(auth()->user()->employee)->id,
            'comment' => $request->input('comment')
        ]);

        Log::create([
            'description' => optional(auth()->user()->employee)->display_name . " rejected " . optional($employeeLeaveRequest->employee)->display_name  . "'s leave request from '" . $employeeLeaveRequest->from . "' to '" . $employeeLeaveRequest->to . "'"
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Leave request rejected successfully.']);
        }

        return redirect()->route('employees-leave-request')->with('status', 'Successfully rejected employee leave request.');
    }

    public function print()
    {
        $employeeLeaveRequests = EmployeeLeaveRequest::with('employee', 'checkedBy')->latest()->get();

        return view('hrms.leave.employee_requests.print', compact('employeeLeaveRequests'));
    }
}
