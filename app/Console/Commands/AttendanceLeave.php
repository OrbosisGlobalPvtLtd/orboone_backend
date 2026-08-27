<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Leave\EmployeeLeaveRequestM as EmployeeLeaveRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttendanceLeave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:leave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will inserts records of employees who is on leave days every 9am';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $employeeLeaveRequests = EmployeeLeaveRequest::where('status', 'APPROVED')->latest()->get();
        $attendanceTime = AttendanceTime::whereName('OTHER')->first() ?: AttendanceTime::first();
        $attendanceType = AttendanceType::where('name', 'ON_LEAVE_DAYS')->orWhere('code', 'leave')->first();

        if (!$attendanceType) {
            $this->error('Leave attendance type not found.');
            return self::FAILURE;
        }

        $attendanceTimeId = $attendanceTime ? $attendanceTime->id : null;
        $attendanceTypeId = $attendanceType->id;
        
        foreach( $employeeLeaveRequests as $leaveReq ) {
            $from = Carbon::parse($leaveReq->from);
            $to = Carbon::parse($leaveReq->to);

            if(Carbon::now()->between($from, $to)) {
                Attendance::create([
                    'employee_id' => $leaveReq->employee_id,
                    'attendance_time_id' => $attendanceTimeId,
                    'attendance_type_id' => $attendanceTypeId
                ]);
            }
        }
        return self::SUCCESS;
    }
}
