<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Leave\HolidayM as Holiday;
use App\Services\HRMS\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkHolidays extends Command
{
    protected $signature = 'attendance:mark-holidays {date?}';
    protected $description = 'Mark holidays for employees based on holiday calendar.';

    public function handle(AttendanceService $attendanceService): int
    {
        $dateStr = $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        
        $holiday = Holiday::where(function ($query) use ($dateStr) {
            $query->whereDate('holiday_date', $dateStr);
        })->where('is_active', true)->first();
        
        if (!$holiday || (bool) $holiday->is_working_day_override) {
            $this->info("{$dateStr} is not an active holiday. Skipping.");
            return self::SUCCESS;
        }

        $type = AttendanceType::where('code', 'holiday')->first();
        if (!$type) {
            $this->error('Holiday attendance type not found.');
            return self::FAILURE;
        }

        $employees = Employee::activeEligible($dateStr)->get();
        $count = 0;
        $holidayTitle = $holiday->title ?: ($holiday->name ?: 'Holiday');

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
                    'attendance_status' => 'holiday',
                    'attendance_source' => 'auto_holiday',
                    'remarks' => "Auto marked Holiday ({$holidayTitle})",
                    'is_locked' => true,
                ]);
                $count++;
            }
        }

        $this->info("Marked {$count} employees as Holiday for {$dateStr} ({$holidayTitle}).");
        return self::SUCCESS;
    }
}
