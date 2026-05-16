<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;

class AttendanceRuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAttendanceTypes();
        $this->seedAttendanceShifts();
    }

    private function seedAttendanceTypes(): void
    {
        $types = [
            ['name' => 'Present', 'code' => 'present', 'color' => '#12B76A', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Absent', 'code' => 'absent', 'color' => '#F04438', 'is_paid' => false, 'is_active' => true],
            ['name' => 'Half Day', 'code' => 'half_day', 'color' => '#F79009', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Leave', 'code' => 'leave', 'color' => '#2563EB', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Holiday', 'code' => 'holiday', 'color' => '#7A5AF8', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Week Off', 'code' => 'week_off', 'color' => '#667085', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Late', 'code' => 'late', 'color' => '#EA580C', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Early Leave', 'code' => 'early_leave', 'color' => '#0EA5E9', 'is_paid' => true, 'is_active' => true],
            ['name' => 'LWP', 'code' => 'lwp', 'color' => '#B42318', 'is_paid' => false, 'is_active' => true],
            ['name' => 'Pending HR', 'code' => 'pending_hr', 'color' => '#F97316', 'is_paid' => true, 'is_active' => true],
            ['name' => 'Punch Blocked', 'code' => 'punch_blocked', 'color' => '#DC2626', 'is_paid' => false, 'is_active' => true],
        ];

        foreach ($types as $type) {
            AttendanceType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }

    private function seedAttendanceShifts(): void
    {
        AttendanceTime::where('code', 'GENERAL')->update([
            'is_default' => false,
            'is_active' => false,
        ]);

        AttendanceTime::query()->update(['is_default' => false]);

        $shifts = [
            [
                'name' => 'General Shift',
                'code' => 'general_shift',
                'punch_allowed_from' => '09:00:00',
                'early_login_from' => '09:00:00',
                'normal_login_from' => '10:00:00',
                'shift_start_time' => '10:00:00',
                'late_after_time' => '11:00:00',
                'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00',
                'half_day_after_time' => '14:00:00',
                'shift_end_time' => '19:00:00',
                'required_office_minutes' => 540,
                'required_work_minutes' => 480,
                'half_day_min_minutes' => 270,
                'break_minutes' => 60,
                'lunch_break_minutes' => 60,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Part Time Morning Shift',
                'code' => 'part_time_morning',
                'punch_allowed_from' => '09:00:00',
                'early_login_from' => '09:00:00',
                'normal_login_from' => '10:00:00',
                'shift_start_time' => '10:00:00',
                'late_after_time' => '11:00:00',
                'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00',
                'half_day_after_time' => null,
                'shift_end_time' => '16:30:00',
                'required_office_minutes' => 360,
                'required_work_minutes' => 300,
                'half_day_min_minutes' => 180,
                'break_minutes' => 30,
                'lunch_break_minutes' => 30,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Part Time Evening Shift',
                'code' => 'part_time_evening',
                'punch_allowed_from' => '14:00:00',
                'early_login_from' => '14:00:00',
                'normal_login_from' => '14:00:00',
                'shift_start_time' => '14:00:00',
                'late_after_time' => '15:00:00',
                'warning_after_time' => '15:06:00',
                'block_after_time' => '15:15:00',
                'half_day_after_time' => null,
                'shift_end_time' => '20:30:00',
                'required_office_minutes' => 360,
                'required_work_minutes' => 300,
                'half_day_min_minutes' => 180,
                'break_minutes' => 30,
                'lunch_break_minutes' => 30,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'WFH Shift',
                'code' => 'wfh_shift',
                'punch_allowed_from' => '09:00:00',
                'early_login_from' => '09:00:00',
                'normal_login_from' => '10:00:00',
                'shift_start_time' => '09:00:00',
                'late_after_time' => '11:00:00',
                'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00',
                'half_day_after_time' => null,
                'shift_end_time' => '18:00:00',
                'required_office_minutes' => 540,
                'required_work_minutes' => 480,
                'half_day_min_minutes' => 270,
                'break_minutes' => 60,
                'lunch_break_minutes' => 60,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Half Day Morning Shift',
                'code' => 'half_day_morning',
                'punch_allowed_from' => '09:00:00',
                'early_login_from' => '09:00:00',
                'normal_login_from' => '10:00:00',
                'shift_start_time' => '10:00:00',
                'late_after_time' => '11:00:00',
                'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00',
                'half_day_after_time' => null,
                'shift_end_time' => '14:30:00',
                'required_office_minutes' => 270,
                'required_work_minutes' => 270,
                'half_day_min_minutes' => 135,
                'break_minutes' => 0,
                'lunch_break_minutes' => 0,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Half Day Evening Shift',
                'code' => 'half_day_evening',
                'punch_allowed_from' => '14:00:00',
                'early_login_from' => '14:00:00',
                'normal_login_from' => '14:00:00',
                'shift_start_time' => '14:00:00',
                'late_after_time' => '15:00:00',
                'warning_after_time' => '15:06:00',
                'block_after_time' => '15:15:00',
                'half_day_after_time' => null,
                'shift_end_time' => '18:30:00',
                'required_office_minutes' => 270,
                'required_work_minutes' => 270,
                'half_day_min_minutes' => 135,
                'break_minutes' => 0,
                'lunch_break_minutes' => 0,
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            AttendanceTime::updateOrCreate(
                ['code' => $shift['code']],
                $shift
            );
        }
    }
}
