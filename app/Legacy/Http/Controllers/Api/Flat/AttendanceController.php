<?php

namespace App\Legacy\Http\Controllers\Api\Flat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // CLOCK-IN API
    public function clockIn(Request $request)
    {
        $request->validate(
            [
            'attendance_time_id' => 'required',
            'attendance_type_id' => 'required',
            'message'            => 'nullable|string'
        ]);

        $attendance = Attendance::create([
            'employee_id'        => auth()->user()->employee->id,
            'attendance_time_id' => $request->attendance_time_id,
            'attendance_type_id' => $request->attendance_type_id,
            'message'            => $request->message,
        ]);

        return response()->json([
            'message' => 'Clock-In Recorded Successfully',
            'data'    => $attendance
        ]);
    }

    // CLOCK-OUT API
    public function clockOut(Request $request)
    {
        $request->validate([
            'attendance_time_id' => 'required',
            'message'            => 'nullable'
        ]);

        $employeeId = auth()->user()->employee->id;

        $attendance = Attendance::where('employee_id', $employeeId)
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No Clock-In Found'], 404);
        }

        $attendance->update([
            'attendance_time_id' => $request->attendance_time_id,
            'message'            => $request->message,
        ]);

        return response()->json([
            'message' => 'Clock-Out Successful',
            'data'    => $attendance
        ]);
    }

    // GET ATTENDANCE (ADMIN/EMPLOYEE)
    public function getAttendance(Request $request)
    {
        $attendance = (new Attendance())->paginate(10);

        return response()->json($attendance);
    }
}
