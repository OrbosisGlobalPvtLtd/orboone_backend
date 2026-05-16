<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Leave\HolidayM as Holiday;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkHolidays extends Command
{
    protected $signature = 'attendance:mark-holidays {date?}';
    protected $description = 'Mark holidays for employees based on holiday calendar.';

    public function handle(AttendanceS $attendanceService): int
    {
        $dateStr = $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        
        $isHoliday = Holiday::whereDate('date', $dateStr)->where('is_active', true)->exists();
        
        if (!$isHoliday) {
            $this->info("{$dateStr} is not a holiday. Skipping.");
            return self::SUCCESS;
        }

        $type = AttendanceType::where('code', 'holiday')->first();
        if (!$type) {
            $this->error('Holiday attendance type not found.');
            return self::FAILURE;
        }

        $employees = Employee::where('is_active', true)->get();
        $count = 0;

        foreach ($employees as $employee) {
            $exists = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $dateStr)
                ->exists();

            if (!$exists) {
                Attendance::create([
                    'user_id' => $employee->user_id,
                    'employee_id' => $employee->id,
                    'attendance_date' => $dateStr,
                    'attendance_type_id' => $type->id,
                    'remarks' => 'Auto marked Holiday',
                    'is_locked' => true,
                ]);
                $count++;
            }
        }

        $this->info("Marked {$count} employees as Holiday for {$dateStr}.");
        return self::SUCCESS;
    }
}
