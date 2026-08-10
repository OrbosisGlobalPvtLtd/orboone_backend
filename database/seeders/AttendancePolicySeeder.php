<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendancePolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('attendance_policy_rules')) {
            return;
        }

        $policies = [
            [
                'policy_name' => 'Default Attendance Policy',
                'description' => 'Standard corporate attendance policy rule definitions for full-time employees.',
                'is_active' => true,

                // Attendance Tracking Policy
                'attendance_tracking_enabled' => true,
                'punch_in_required' => true,
                'punch_out_required' => true,
                'allow_web_punch' => true,
                'allow_mobile_punch' => true,

                // Working Hours & Minute Threshold Policies
                'working_hours_per_day' => 9,
                'working_days_per_week' => 5,
                'weekly_off_count' => 2,
                'required_work_minutes' => 540, // 9 hours full day work
                'half_day_min_minutes' => 240,  // 4 hours minimum half day work
                'absent_below_minutes' => 120,  // Under 2 hours treated as absent/LWP
                'lunch_break_minutes' => 60,    // 60 minutes lunch break threshold

                // Missed Punch Policy
                'allowed_missed_punches' => 2,
                'missed_punch_lwp_after' => 3,
                'missed_punch_action' => 'lwp',
                'auto_lwp_for_missed_punch' => true,
                'auto_lwp_enabled' => true,

                // Discipline Violation Policy
                'combined_violation_enabled' => true,
                'combined_violation_limit' => 3,
                'combined_violation_action' => 'half_day',

                // Late & Early Policy
                'late_mark_enabled' => true,
                'early_out_enabled' => true,
                'late_violation_enabled' => true,
                'early_violation_enabled' => true,
                'late_mark_limit' => 2,
                'early_out_limit' => 2,
                'late_half_day_after' => 2,
                'late_lwp_after' => 3,
                'count_early_out_with_late' => true,
                'late_violation_limit' => 2,
                'early_violation_limit' => 2,

                // Half Day Policy
                'half_day_enabled' => true,
                'half_day_after_late' => true,
                'half_day_after_early_out' => true,

                // Auto Block Policy
                'auto_block_enabled' => true,
                'auto_absent_enabled' => true,

                // WFH Policy
                'wfh_enabled' => true,
                'monthly_wfh_limit' => 2,
                'manager_approval_required' => true,
                'internet_proof_required' => true,
                'speed_test_required' => true,
                'internet_issue_lwp' => true,
                'electricity_issue_lwp' => true,

                // Regularization Policy
                'regularization_enabled' => true,
                'regularization_days' => 7,
                'regularization_requires_approval' => true,

                // Leave & Grace Policy
                'lwp_enabled' => true,
                'grace_late_allowed' => 0,
                'grace_early_allowed' => 0,

                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'policy_name' => 'Part Time Attendance Policy',
                'description' => 'Policy definitions for fixed part-time employee attendance tracking.',
                'is_active' => true,

                // Attendance Tracking Policy
                'attendance_tracking_enabled' => true,
                'punch_in_required' => true,
                'punch_out_required' => true,
                'allow_web_punch' => true,
                'allow_mobile_punch' => true,

                // Working Hours & Minute Threshold Policies
                'working_hours_per_day' => 5,
                'working_days_per_week' => 6,
                'weekly_off_count' => 1,
                'required_work_minutes' => 300, // 5 hours minimum full day work for part-time
                'half_day_min_minutes' => 150,  // 2.5 hours minimum half day work
                'absent_below_minutes' => 75,   // Under 1.25 hours treated as absent/LWP
                'lunch_break_minutes' => 0,     // 0 minutes default lunch break

                // Missed Punch Policy
                'allowed_missed_punches' => 2,
                'missed_punch_lwp_after' => 3,
                'auto_lwp_for_missed_punch' => true,

                // Late & Violation Limit Policy
                'late_mark_limit' => 2,
                'early_out_limit' => 2,
                'late_half_day_after' => 2,
                'late_lwp_after' => 3,
                'count_early_out_with_late' => true,
                'combined_violation_limit' => 3,
                'late_violation_limit' => 3,
                'early_violation_limit' => 3,

                // Half Day Policy
                'half_day_enabled' => true,
                'half_day_after_late' => true,
                'half_day_after_early_out' => true,

                // Auto Block Policy
                'auto_block_enabled' => true,
                'auto_absent_enabled' => true,

                // WFH Policy
                'wfh_enabled' => true,
                'monthly_wfh_limit' => 2,
                'manager_approval_required' => true,
                'internet_proof_required' => true,
                'speed_test_required' => true,
                'internet_issue_lwp' => true,
                'electricity_issue_lwp' => true,

                // Regularization Policy
                'regularization_enabled' => true,
                'regularization_days' => 7,
                'regularization_requires_approval' => true,

                // Leave & Grace Policy
                'lwp_enabled' => true,
                'grace_late_allowed' => 0,
                'grace_early_allowed' => 0,

                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'policy_name' => 'Flexible Part Time Policy',
                'description' => 'Policy definitions for flexible part-time employees with dynamic punch-in windows.',
                'is_active' => true,

                // Attendance Tracking Policy
                'attendance_tracking_enabled' => true,
                'punch_in_required' => true,
                'punch_out_required' => true,
                'allow_web_punch' => true,
                'allow_mobile_punch' => true,

                // Working Hours & Minute Threshold Policies
                'working_hours_per_day' => 5,
                'working_days_per_week' => 6,
                'weekly_off_count' => 1,
                'required_work_minutes' => 300, // 5 hours minimum full day work
                'half_day_min_minutes' => 180,  // 3 hours minimum half day work
                'absent_below_minutes' => 90,   // Under 1.5 hours treated as absent/LWP
                'lunch_break_minutes' => 0,     // 0 minutes default break

                // Missed Punch Policy
                'allowed_missed_punches' => 2,
                'missed_punch_lwp_after' => 3,
                'auto_lwp_for_missed_punch' => true,

                // Late & Violation Limit Policy
                'late_mark_limit' => 2,
                'early_out_limit' => 2,
                'late_half_day_after' => 2,
                'late_lwp_after' => 3,
                'count_early_out_with_late' => true,
                'combined_violation_limit' => 3,
                'late_violation_limit' => 3,
                'early_violation_limit' => 3,

                // Half Day Policy
                'half_day_enabled' => true,
                'half_day_after_late' => true,
                'half_day_after_early_out' => true,

                // Auto Block Policy
                'auto_block_enabled' => true,
                'auto_absent_enabled' => true,

                // WFH Policy
                'wfh_enabled' => true,
                'monthly_wfh_limit' => 2,
                'manager_approval_required' => true,
                'internet_proof_required' => true,
                'speed_test_required' => true,
                'internet_issue_lwp' => true,
                'electricity_issue_lwp' => true,

                // Regularization Policy
                'regularization_enabled' => true,
                'regularization_days' => 7,
                'regularization_requires_approval' => true,

                // Leave & Grace Policy
                'lwp_enabled' => true,
                'grace_late_allowed' => 0,
                'grace_early_allowed' => 0,

                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'policy_name' => 'Half Day Attendance Policy',
                'description' => 'Policy definitions for half-day schedule employees.',
                'is_active' => true,

                // Attendance Tracking Policy
                'attendance_tracking_enabled' => true,
                'punch_in_required' => true,
                'punch_out_required' => true,
                'allow_web_punch' => true,
                'allow_mobile_punch' => true,

                // Working Hours & Minute Threshold Policies
                'working_hours_per_day' => 4,
                'working_days_per_week' => 6,
                'weekly_off_count' => 1,
                'required_work_minutes' => 270, // 4.5 hours minimum full day work
                'half_day_min_minutes' => 135,  // 2.25 hours minimum half day work
                'absent_below_minutes' => 65,   // Under 1 hour treated as absent/LWP
                'lunch_break_minutes' => 0,

                // Missed Punch Policy
                'allowed_missed_punches' => 2,
                'missed_punch_lwp_after' => 3,
                'auto_lwp_for_missed_punch' => true,

                // Late & Violation Limit Policy
                'late_mark_limit' => 2,
                'early_out_limit' => 2,
                'late_half_day_after' => 2,
                'late_lwp_after' => 3,
                'count_early_out_with_late' => true,
                'combined_violation_limit' => 3,
                'late_violation_limit' => 3,
                'early_violation_limit' => 3,

                // Half Day Policy
                'half_day_enabled' => true,
                'half_day_after_late' => true,
                'half_day_after_early_out' => true,

                // Auto Block Policy
                'auto_block_enabled' => true,
                'auto_absent_enabled' => true,

                // WFH Policy
                'wfh_enabled' => true,
                'monthly_wfh_limit' => 2,
                'manager_approval_required' => true,
                'internet_proof_required' => true,
                'speed_test_required' => true,
                'internet_issue_lwp' => true,
                'electricity_issue_lwp' => true,

                // Regularization Policy
                'regularization_enabled' => true,
                'regularization_days' => 7,
                'regularization_requires_approval' => true,

                // Leave & Grace Policy
                'lwp_enabled' => true,
                'grace_late_allowed' => 0,
                'grace_early_allowed' => 0,

                'updated_at' => now(),
                'created_at' => now(),
            ],
        ];

        foreach ($policies as $policyData) {
            $filteredData = collect($policyData)
                ->filter(fn ($val, $col) => Schema::hasColumn('attendance_policy_rules', $col))
                ->all();

            DB::table('attendance_policy_rules')->updateOrInsert(
                ['policy_name' => $policyData['policy_name']],
                $filteredData
            );
        }
    }
}
