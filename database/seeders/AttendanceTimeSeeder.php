<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceTimeSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'General Shift', 'code' => 'general_shift',
                'punch_allowed_from' => '09:00:00', 'shift_start_time' => '10:00:00',
                'late_after_time' => '11:05:00', 'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00', 'shift_end_time' => '19:00:00',
                'required_work_minutes' => 480, 'half_day_min_minutes' => 270,
                'absent_below_minutes' => 270, 'lunch_break_minutes' => 60,
                'is_default' => 1, 'is_active' => 1,
            ],
            [
                'name' => 'Part Time Shift', 'code' => 'part_time_shift',
                'punch_allowed_from' => '09:00:00', 'shift_start_time' => '10:00:00',
                'late_after_time' => '11:05:00', 'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00', 'shift_end_time' => '16:00:00',
                'required_work_minutes' => 360, 'half_day_min_minutes' => 180,
                'absent_below_minutes' => 180, 'lunch_break_minutes' => 30,
                'is_default' => 0, 'is_active' => 1,
            ],
            [
                'name' => 'Half Day Shift', 'code' => 'half_day_shift',
                'punch_allowed_from' => '09:00:00', 'shift_start_time' => '10:00:00',
                'late_after_time' => '11:05:00', 'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00', 'shift_end_time' => '14:30:00',
                'required_work_minutes' => 270, 'half_day_min_minutes' => 135,
                'absent_below_minutes' => 135, 'lunch_break_minutes' => 0,
                'is_default' => 0, 'is_active' => 1,
            ],
        ];

        foreach ($shifts as $shift) {
            if (! Schema::hasColumn('attendance_times', 'lunch_break_minutes') && Schema::hasColumn('attendance_times', 'break_minutes')) {
                $shift['break_minutes'] = $shift['lunch_break_minutes'];
                unset($shift['lunch_break_minutes']);
            }

            DB::table('attendance_times')->updateOrInsert(
                ['code' => $shift['code']],
                $this->columns('attendance_times', $shift + ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }

    private function columns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }
}
