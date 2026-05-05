<?php

namespace App\Http\Controllers\Api\V1\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Leave\LeaveApplicationM as LeaveApplication;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function applyLeave(Request $request)
    {
        $request->validate([
            'leave_type' => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'reason'     => 'required'
        ]);

        $leave = LeaveApplication::create([
            'employee_id' => auth()->id(),
            'leave_type'  => $request->leave_type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'reason'      => $request->reason,
        ]);

        return response()->json([
            'status'=>true,
            'data'=>$leave
        ]);
    }
}