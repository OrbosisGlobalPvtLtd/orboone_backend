<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

use App\Models\Attendance;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'work_type' => 'required|in:WFH,WFO'
        ]);

        $result = $this->attendanceService->processPunchIn(
            auth()->id(),
            $request->work_type
        );

        return response()->json($result);
    }

    public function clockOut(Request $request)
    {
        $result = $this->attendanceService->processPunchOut(auth()->id());
        return response()->json($result);
    }

     // ------------------------------------------------
    // 7. ATTENDANCE LIST
    // ------------------------------------------------
    public function getAttendance()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->orderBy('date', 'DESC')
            ->paginate(10);

        return response()->json([
            'status'  => true,
            'message' => 'Attendance List Fetched Successfully',
            'data'    => $attendance->items(),
            'pagination' => [
                'total'         => $attendance->total(),
                'per_page'      => $attendance->perPage(),
                'current_page'  => $attendance->currentPage(),
                'last_page'     => $attendance->lastPage(),
                'next_page_url' => $attendance->nextPageUrl(),
                'prev_page_url' => $attendance->previousPageUrl(),
            ]
        ]);
    }
}