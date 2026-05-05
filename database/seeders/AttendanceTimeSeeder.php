<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceTimeSeeder extends Seeder
{
    public function run()
    {
        DB::table('attendance_times')->updateOrInsert(
            ['code' => 'general_shift'],
            [
                'name' => 'General Shift',
                'punch_allowed_from' => '09:00:00',
                'shift_start_time' => '10:00:00',
                'late_after_time' => '11:15:00',
                'half_day_after_time' => '14:00:00',
                'shift_end_time' => '19:00:00',
                'required_work_minutes' => 480,
                'half_day_min_minutes' => 240,
                'lunch_break_minutes' => 60,
                'is_default' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}