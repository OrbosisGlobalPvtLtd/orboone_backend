<?php

namespace App\Console\Commands;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkWeekOffs extends Command
{
    protected $signature = 'attendance:mark-weekoffs {date?}';
    protected $description = 'Mark week offs (Sundays, 2nd & 4th Saturdays) for employees.';

    public function handle(AttendanceS $attendanceService): int
    {
        $dateStr = $this->argument('date') ?: Carbon::now($attendanceService->attendanceTimezone())->toDateString();
        $date = Carbon::parse($dateStr);
        
        if (!$attendanceService->isOffDay($date)) {
            $this->info("{$dateStr} is not a week off day. Skipping.");
            return self::SUCCESS;
        }

        $type = AttendanceType::where('code', 'week_off')->first();
        if (!$type) {
            $this->error('Week off attendance type not found.');
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
                    'remarks' => 'Auto marked Week Off',
                    'is_locked' => true,
                ]);
                $count++;
            }
        }

        $this->info("Marked {$count} employees as Week Off for {$dateStr}.");
        return self::SUCCESS;
    }
}
