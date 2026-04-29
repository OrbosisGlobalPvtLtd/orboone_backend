<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveAllocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveApprovalController extends Controller
{
    /**
     * Admin – all leave requests
     */
    public function index()
    {
        $requests = \App\Models\LeaveApplication::with('employee.employeeDetail')
            ->latest()
            ->paginate(20);

        $accesses = \App\Models\Access::where('role_id', auth()->user()->role_id)->get();

        return view('pages.leave_approvals.index', compact('requests', 'accesses'))
            ->with('active', 'leave-approvals');
    }

    /**
     * Approve a leave request and deduct from the allocation.
     * If quota exceeded, remainder goes to LWP.
     */
    public function approve(Request $request, $id)
    {
        $leaveApplication = \App\Models\LeaveApplication::with('employee')->findOrFail($id);

        if ($leaveApplication->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $year = Carbon::parse($leaveApplication->start_date)->year;
        
        // Ensure allocation exists for the employee and year
        $allocation = \App\Models\LeaveAllocation::firstOrCreate(
            ['employee_id' => $leaveApplication->employee_id, 'year' => $year],
            ['total_pl' => 18, 'total_sl' => 7, 'used_pl' => 0, 'used_sl' => 0, 'lwp_days' => 0]
        );

        $days = (float) $leaveApplication->total_days;
        $type = strtoupper($leaveApplication->leave_type);

        if ($type === 'PL') {
            $available = (float) ($allocation->total_pl - $allocation->used_pl);
            if ($available >= $days) {
                $allocation->used_pl += $days;
            } else {
                $consumed = max(0, $available);
                $allocation->used_pl += $consumed;
                $lwp = $days - $consumed;
                $allocation->lwp_days += $lwp;
                
                // Track embedded LWP in the application record
                $leaveApplication->lwp_days = $lwp;
            }
        } elseif ($type === 'SL') {
            $available = (float) ($allocation->total_sl - $allocation->used_sl);
            if ($available >= $days) {
                $allocation->used_sl += $days;
            } else {
                $consumed = max(0, $available);
                $allocation->used_sl += $consumed;
                $lwp = $days - $consumed;
                $allocation->lwp_days += $lwp;
                
                // Track embedded LWP in the application record
                $leaveApplication->lwp_days = $lwp;
            }
        } else {
            // LWP type or others (counts directly to lwp_days)
            $allocation->lwp_days += $days;
        }

        $allocation->save();

        $leaveApplication->update([
            'status'       => 'approved',
            'approved_by'  => Auth::id(),
            'admin_remark' => $request->remark ?? $request->admin_remark // Handle both possible field names
        ]);

        return back()->with('success', 'Leave request approved successfully. Balances updated.');
    }

    /**
     * Reject a leave request (no deduction)
     */
    public function reject(Request $request, $id)
    {
        $leaveApplication = \App\Models\LeaveApplication::findOrFail($id);
        
        if ($leaveApplication->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $leaveApplication->update([
            'status'       => 'rejected',
            'approved_by'  => Auth::id(),
            'admin_remark' => $request->remark ?? $request->admin_remark
        ]);

        return back()->with('success', 'Leave request has been rejected.');
    }
}

