<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceBackfillShiftAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:backfill-shift-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill shift assignments for existing employees into employee_shift_timings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $employees = DB::table('employees_new')->get();
        $count = 0;

        foreach ($employees as $employee) {
            // Check if employee already has an active assignment in employee_shift_timings
            $exists = DB::table('employee_shift_timings')
                ->where('employee_id', $employee->id)
                ->where('is_active', 1)
                ->exists();

            if ($exists) {
                continue;
            }

            // Resolve the current shift template
            $shift = $this->resolveShiftBySchedule(
                $employee->work_schedule_type,
                $employee->work_mode ?? 'office',
                $employee->employment_type ?? 'full_time'
            );

            if (!$shift) {
                $this->warn("Could not resolve shift for employee ID: {$employee->id}. Skipping.");
                continue;
            }

            // Use joining_date or created_at or today's date
            $joiningDate = $employee->joining_date ?: ($employee->created_at ? Carbon::parse($employee->created_at)->toDateString() : Carbon::now('Asia/Kolkata')->toDateString());

            DB::table('employee_shift_timings')->insert([
                'employee_id' => $employee->id,
                'attendance_time_id' => $shift->id,
                'punch_allowed_from' => $shift->punch_allowed_from,
                'shift_start_time' => $shift->shift_start_time,
                'late_after_time' => $shift->late_after_time,
                'half_day_after_time' => $shift->half_day_after_time,
                'block_after_time' => $shift->block_after_time,
                'shift_end_time' => $shift->shift_end_time,
                'required_work_minutes' => $shift->required_work_minutes,
                'lunch_minutes' => $shift->lunch_break_minutes,
                'effective_from' => $joiningDate,
                'effective_to' => null,
                'is_active' => 1,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        $this->info("Successfully backfilled {$count} employee shift timing assignments.");
        return 0;
    }

    /**
     * Resolve shift template for employee based on work schedule config or defaults.
     */
    private function resolveShiftBySchedule(?string $schedule, string $workMode, string $employmentType)
    {
        $scheduleKey = $schedule;
        if ($scheduleKey === 'full_day') {
            $scheduleKey = 'general';
        } elseif ($scheduleKey === 'part_day') {
            $scheduleKey = 'part_time';
        } elseif ($scheduleKey === 'hourly') {
            $scheduleKey = 'half_day';
        } elseif ($scheduleKey === 'shift_based' || $scheduleKey === 'shift_based_morning') {
            $scheduleKey = 'half_day_morning';
        } elseif ($scheduleKey === 'shift_based_evening') {
            $scheduleKey = 'half_day_evening';
        }

        if (empty($scheduleKey)) {
            if ($workMode === 'wfh') {
                $scheduleKey = 'wfh';
            } elseif ($employmentType === 'part_time') {
                $scheduleKey = 'part_time';
            } else {
                $scheduleKey = 'general';
            }
        }

        $shiftCode = 'general_shift';
        $config = config('hrms.work_schedule_shifts');
        if ($config && isset($config[$scheduleKey])) {
            $shiftCode = $config[$scheduleKey]['shift_code'];
        } else {
            if ($scheduleKey === 'flexible_part_time') {
                $shiftCode = 'flexible_part_time';
            } elseif ($scheduleKey === 'general_shift' || $scheduleKey === 'general' || $scheduleKey === 'full_day') {
                $shiftCode = 'general_shift';
            } elseif ($workMode === 'wfh' || $scheduleKey === 'wfh_shift' || $scheduleKey === 'wfh') {
                $shiftCode = 'wfh_shift';
            } elseif ($scheduleKey === 'part_time_shift' || $scheduleKey === 'part_time' || $scheduleKey === 'part_day') {
                $shiftCode = 'part_time_shift';
            } elseif ($scheduleKey === 'half_day_shift' || $scheduleKey === 'half_day' || $scheduleKey === 'hourly') {
                $shiftCode = 'half_day_shift';
            } elseif ($scheduleKey === 'half_day_morning') {
                $shiftCode = 'half_day_morning';
            } elseif ($scheduleKey === 'half_day_evening') {
                $shiftCode = 'half_day_evening';
            }
        }

        return DB::table('attendance_times')->where('code', $shiftCode)->first();
    }
}
