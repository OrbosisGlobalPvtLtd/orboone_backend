<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Present', 'code' => 'present', 'is_paid' => 1, 'color' => '#12B76A', 'is_active' => 1],
            ['name' => 'Absent', 'code' => 'absent', 'is_paid' => 0, 'color' => '#D92D20', 'is_active' => 1],
            ['name' => 'Half Day', 'code' => 'half_day', 'is_paid' => 1, 'color' => '#F79009', 'is_active' => 1],
            ['name' => 'Leave', 'code' => 'leave', 'is_paid' => 1, 'color' => '#4B00E8', 'is_active' => 1],
            ['name' => 'Holiday', 'code' => 'holiday', 'is_paid' => 1, 'color' => '#0BA5EC', 'is_active' => 1],
            ['name' => 'Week Off', 'code' => 'week_off', 'is_paid' => 1, 'color' => '#667085', 'is_active' => 1],
            ['name' => 'Late', 'code' => 'late', 'is_paid' => 1, 'color' => '#F79009', 'is_active' => 1],
            ['name' => 'Early Leave', 'code' => 'early_leave', 'is_paid' => 1, 'color' => '#F97316', 'is_active' => 1],
            ['name' => 'Leave Without Pay', 'code' => 'lwp', 'is_paid' => 0, 'color' => '#B42318', 'is_active' => 1],
            ['name' => 'Pending HR Approval', 'code' => 'pending_hr', 'is_paid' => 0, 'color' => '#7A5AF8', 'is_active' => 1],
            ['name' => 'Punch Blocked', 'code' => 'punch_blocked', 'is_paid' => 0, 'color' => '#344054', 'is_active' => 1],
            ['name' => 'Missed Punch', 'code' => 'missed_punch', 'is_paid' => 0, 'color' => '#F97316', 'is_active' => 1],
        ];

        foreach ($types as $type) {
            DB::table('attendance_types')->updateOrInsert(
                ['code' => $type['code']],
                $this->columns('attendance_types', $type + ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }

    private function columns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }
}
