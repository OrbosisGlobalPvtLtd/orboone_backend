<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendancePolicyRuleSeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'policy_name' => 'Default Attendance Policy',
                'punch_allowed_from' => '09:00:00', 'shift_start_time' => '10:00:00',
                'late_after_time' => '11:05:00', 'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00', 'shift_end_time' => '19:00:00',
                'required_work_minutes' => 480, 'half_day_min_minutes' => 270,
                'absent_below_minutes' => 270, 'lunch_break_minutes' => 60,
                'allowed_missed_punches' => 2, 'combined_violation_limit' => 3,
                'late_violation_limit' => 3, 'early_violation_limit' => 3,
                'auto_block_enabled' => 1, 'auto_absent_enabled' => 1, 'is_active' => 1,
            ],
            [
                'policy_name' => 'Part Time Attendance Policy',
                'punch_allowed_from' => '09:00:00', 'shift_start_time' => '10:00:00',
                'late_after_time' => '11:05:00', 'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00', 'shift_end_time' => '19:00:00',
                'required_work_minutes' => 360, 'half_day_min_minutes' => 180,
                'absent_below_minutes' => 180, 'lunch_break_minutes' => 30,
                'allowed_missed_punches' => 2, 'combined_violation_limit' => 3,
                'late_violation_limit' => 3, 'early_violation_limit' => 3,
                'auto_block_enabled' => 1, 'auto_absent_enabled' => 1, 'is_active' => 1,
            ],
            [
                'policy_name' => 'Half Day Attendance Policy',
                'punch_allowed_from' => '09:00:00', 'shift_start_time' => '10:00:00',
                'late_after_time' => '11:05:00', 'warning_after_time' => '11:06:00',
                'block_after_time' => '11:15:00', 'shift_end_time' => '19:00:00',
                'required_work_minutes' => 270, 'half_day_min_minutes' => 135,
                'absent_below_minutes' => 135, 'lunch_break_minutes' => 0,
                'allowed_missed_punches' => 2, 'combined_violation_limit' => 3,
                'late_violation_limit' => 3, 'early_violation_limit' => 3,
                'auto_block_enabled' => 1, 'auto_absent_enabled' => 1, 'is_active' => 1,
            ],
        ];

        foreach ($policies as $policy) {
            if (! Schema::hasColumn('attendance_policy_rules', 'lunch_break_minutes') && Schema::hasColumn('attendance_policy_rules', 'break_minutes')) {
                $policy['break_minutes'] = $policy['lunch_break_minutes'];
                unset($policy['lunch_break_minutes']);
            }

            DB::table('attendance_policy_rules')->updateOrInsert(
                ['policy_name' => $policy['policy_name']],
                $this->columns('attendance_policy_rules', $policy + ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }

    private function columns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }
}
