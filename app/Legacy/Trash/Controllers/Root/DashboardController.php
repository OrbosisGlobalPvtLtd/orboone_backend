<?php

namespace App\Legacy\Trash\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\Employee;
use App\Models\RecruitmentCandidate;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $announcements;
    private $employees;
    private $recruitmentCandidates;

    public function __construct()
    {
        $this->middleware('auth');

        $this->announcements = resolve(Announcement::class);
        $this->employees = resolve(Employee::class);
        $this->recruitmentCandidates = resolve(RecruitmentCandidate::class);
    }
    
    
    public function generateStorageLink()
{
    $link = public_path('storage');

    // Check if already exists
    if (File::exists($link)) {
        return response()->json([
            'status' => true,
            'message' => 'Storage link already exists'
        ]);
    }

    try {
        // Laravel command (BEST for shared hosting)
        Artisan::call('storage:link');

        return response()->json([
            'status' => true,
            'message' => 'Storage link created successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to create storage link',
            'error' => $e->getMessage()
        ]);
    }
}

    public function index()
    {
        $user = auth()->user();

        // General Data
        $announcements = $this->announcements->paginate();
        
        $employeesCount = $this->employees->getCount();
        $recruitmentCandidatesCount = $this->recruitmentCandidates->getCount();
        $endingEmployees = $this->employees->getEndingContractEmployees();

        // -------------------------------------------
        // USER ATTENDANCE RECORDS (CLOCK-IN / CLOCK-OUT)
        // -------------------------------------------
        $attendanceRecords = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'DESC')
            ->paginate(5);

        // -------------------------------------------
        // TODAY STATUS (Holidays, Birthdays, etc)
        // -------------------------------------------
        $today = \Carbon\Carbon::now();
        
        $holidays = \App\Models\HRMS\Leave\HolidayM::whereDate('date', $today->format('Y-m-d'))->get();
        $nationalHolidays = \App\Models\HRMS\Leave\NationalHolidayM::whereDate('holiday_date', $today->format('Y-m-d'))->get();
        $allHolidays = $holidays->pluck('name')->merge($nationalHolidays->pluck('name'));
        
        $rawBirthdays = \App\Models\Employee::whereHas('employeeDetail', function ($query) use ($today) {
            $query->whereMonth('date_of_birth', $today->month)
                  ->whereDay('date_of_birth', $today->day);
        })
        ->with(['user', 'employeeDetail', 'department'])
        ->get();
            
        $birthdays = $rawBirthdays->map(function ($emp) {
            $empImageUrl = null;
            if ($emp->employeeDetail && $emp->employeeDetail->image) {
                $publicPath  = public_path('uploads/employee/' . $emp->employeeDetail->image);
                $storagePath = storage_path('app/public/' . $emp->employeeDetail->image);
                if (file_exists($publicPath)) {
                    $empImageUrl = asset('uploads/employee/' . $emp->employeeDetail->image);
                } elseif (file_exists($storagePath)) {
                    $empImageUrl = asset('storage/' . $emp->employeeDetail->image);
                }
            }
            return (object) [
                'employee_id' => $emp->employee_id,
                'name'        => $emp->user->name ?? 'Unknown',
                'image_url'   => $empImageUrl,
                'department'  => $emp->department->name ?? null,
            ];
        });

        $todayStatus = (object) [
            'is_holiday' => $allHolidays->isNotEmpty(),
            'holidays'   => $allHolidays,
            'birthdays'  => $birthdays,
        ];

        return view(
            'pages.dashboard',
            compact(
                'announcements',
                'employeesCount',
                'recruitmentCandidatesCount',
                'endingEmployees',
                'attendanceRecords',
                'todayStatus'
            )
        );
    }
}
