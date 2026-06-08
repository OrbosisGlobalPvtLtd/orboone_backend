<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\WeekoffHolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyHolidayWorkRequestC extends Controller
{
    /**
     * Display a paginated listing of the employee's holiday work requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $employee = EmployeeM::where('user_id', Auth::id())->first();

        if (!$employee) {
            abort(404, 'Employee record not found.');
        }

        $requests = HolidayWorkRequestM::where('employee_id', $employee->id)
            ->latest('worked_date')
            ->paginate(15);

        return view('hrms.attendance.my_holiday_work.index', compact('requests', 'employee'));
    }

    /**
     * Store a newly created holiday work request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\HRMS\Leave\WeekoffHolidayService  $weekoffHolidayService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, WeekoffHolidayService $weekoffHolidayService)
    {
        $employee = EmployeeM::where('user_id', Auth::id())->first();

        if (!$employee) {
            abort(404, 'Employee record not found.');
        }

        // Custom validation messages as required by Phase 3
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'work_type' => 'required',
            'reason' => 'required|string|max:1000',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'work_mode' => 'nullable|in:wfo,wfh,WFO,WFH',
            'notes' => 'nullable|string|max:1000',
        ], [
            'work_type.required' => 'Please select work type.',
            'reason.required' => 'Please enter work summary.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->with('error', $validator->errors()->first());
        }

        $workType = $request->input('work_type');
        if (!in_array($workType, ['holiday_work', 'weekoff_work'])) {
            return redirect()->back()->withInput()->with('error', 'Please select work type.');
        }

        // Extract dates (support worked_dates array first, fallback to single worked_date)
        $dates = $request->input('worked_dates') ?: ($request->input('worked_date') ? [$request->input('worked_date')] : []);
        $dates = array_filter(array_unique($dates));

        if (empty($dates)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one worked date.');
        }

        // Validate time ordering
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        if ($startTime && $endTime) {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            if ($end->lte($start)) {
                return redirect()->back()->withInput()->with('error', 'Work end time must be after start time.');
            }
        }

        // Validation for each date before starting transaction (ensure all-or-nothing check)
        foreach ($dates as $dateStr) {
            try {
                $workedDate = Carbon::parse($dateStr, 'Asia/Kolkata');
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', 'Invalid date selected.');
            }

            $dayInfo = $weekoffHolidayService->dayInfo($workedDate);
            $isHoliday = $dayInfo['is_holiday'] ?? false;
            $isWeekoff = $dayInfo['is_weekoff'] ?? false;

            if ($workType === 'holiday_work' && !$isHoliday) {
                return redirect()->back()->withInput()->with('error', 'Selected date is not an official holiday.');
            }

            if ($workType === 'weekoff_work' && !$isWeekoff) {
                return redirect()->back()->withInput()->with('error', 'Selected date is not your week-off.');
            }

            // A normal working day is a day that is neither holiday nor week-off
            if (!$isHoliday && !$isWeekoff) {
                return redirect()->back()->withInput()->with('error', 'Selected date is a regular working day.');
            }

            // Duplicate request check (pending, approved, or completed)
            $duplicate = HolidayWorkRequestM::where('employee_id', $employee->id)
                ->whereDate('worked_date', $workedDate->toDateString())
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->exists();

            if ($duplicate) {
                return redirect()->back()->withInput()->with('error', 'A request already exists for one of the selected dates.');
            }
        }

        $workMode = strtolower($request->input('work_mode', 'wfo'));
        $createdRequests = [];

        DB::transaction(function () use ($employee, $dates, $workType, $workMode, $request, &$createdRequests) {
            foreach ($dates as $dateStr) {
                $workedDate = Carbon::parse($dateStr, 'Asia/Kolkata')->toDateString();
                $attendance = \App\Models\HRMS\Attendance\AttendanceM::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $workedDate)
                    ->first();

                $timeDetails = "Hours: " . $request->input('start_time') . " - " . $request->input('end_time');
                $notes = trim($timeDetails . "\n" . ($request->input('notes') ?? ''));

                $row = HolidayWorkRequestM::create([
                    'employee_id' => $employee->id,
                    'attendance_id' => $attendance?->id,
                    'worked_date' => $workedDate,
                    'work_type' => $workType,
                    'work_mode' => $workMode,
                    'reason' => $request->input('reason'),
                    'notes' => $notes,
                    'status' => 'pending',
                ]);
                $createdRequests[] = $row;
            }
        });

        // 4. Notify HR/Admin (Grouped notification for multiple dates)
        try {
            $notificationService = app(\App\Services\HRMS\Notification\NotificationS::class);
            $employeeName = $employee->display_name;

            if (count($dates) === 1) {
                $workTypeLabel = ($workType === 'weekoff_work') ? 'Weekoff' : 'Holiday';
                $formattedDate = Carbon::parse($dates[0])->format('d M Y');
                $message = "{$employeeName} submitted a {$workTypeLabel} work request for {$formattedDate}.";
            } else {
                $message = "{$employeeName} submitted Holiday / Week-Off Work request for " . count($dates) . " date(s).";
            }

            $title = "New Work Request Submitted";
            $actionUrl = route('hrms.attendance.holiday_work.index', [], false);

            $notificationService->notifyHrAndSuperAdmin(
                $title,
                $message,
                'holiday_work_request_submitted',
                'hrms.attendance.holiday_work.index',
                [],
                [
                    'employee_id' => $employee->id,
                    'request_id' => $createdRequests[0]->id,
                    'dates' => implode(',', array_map(fn($d) => Carbon::parse($d)->toDateString(), $dates)),
                    'work_type' => $workType,
                    'action_url' => $actionUrl,
                    'route_name' => 'hrms.attendance.holiday_work.index',
                    'route_params' => [],
                ]
            );
        } catch (\Throwable $e) {
            \Log::error("Failed to send web submission notification: " . $e->getMessage());
        }

        return redirect()->route('hrms.attendance.my-holiday-work.index')
            ->with('success', 'Holiday/Weekoff work request submitted successfully.');
    }
}
