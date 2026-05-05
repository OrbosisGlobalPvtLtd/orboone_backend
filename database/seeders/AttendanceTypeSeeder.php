<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Present', 'code' => 'present', 'is_paid' => 1, 'color' => '#16a34a'],
            ['name' => 'Absent', 'code' => 'absent', 'is_paid' => 0, 'color' => '#dc2626'],
            ['name' => 'Half Day', 'code' => 'half_day', 'is_paid' => 1, 'color' => '#f59e0b'],
            ['name' => 'Leave', 'code' => 'leave', 'is_paid' => 1, 'color' => '#2563eb'],
            ['name' => 'Holiday', 'code' => 'holiday', 'is_paid' => 1, 'color' => '#7c3aed'],
            ['name' => 'Week Off', 'code' => 'week_off', 'is_paid' => 1, 'color' => '#64748b'],
            ['name' => 'Pending HR Approval', 'code' => 'pending_hr', 'is_paid' => 0, 'color' => '#ea580c'],
        ];

        foreach ($types as $type) {
            DB::table('attendance_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}