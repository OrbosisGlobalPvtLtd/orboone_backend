<?php

namespace App\Legacy\Http\Controllers\Api\Flat;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    // APPLY LEAVE
    public function applyLeave(Request $request)
    {
        $request->validate([
            'leave_type' => 'required',
            'start_date' => 'required',
            'end_date'   => 'required',
            'reason'     => 'required'
        ]);

        $leave = LeaveRequest::create([
            'user_id'    => auth()->id(),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'reason'     => $request->reason,
            'status'     => 'Pending'
        ]);

        return response()->json([
            'message' => 'Leave Request Submitted',
            'data'    => $leave
        ]);
    }
}
