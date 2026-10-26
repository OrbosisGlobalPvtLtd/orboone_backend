<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_policy_rules')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        // Ordered definitions to logically group columns:
        // basic info -> punch settings -> working hours (half_day_*, absent_below_minutes) -> early out
        // -> late login -> combined violation -> missed punch -> WFH -> regularization -> timestamps
        $orderedColumns = [
            'policy_name' => "MODIFY `policy_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default Attendance Policy' AFTER `id`",
            'description' => "MODIFY `description` text COLLATE utf8mb4_unicode_ci NULL AFTER `policy_name`",
            'is_active' => "MODIFY `is_active` tinyint(1) NOT NULL DEFAULT '1' AFTER `description`",

            'attendance_tracking_enabled' => "MODIFY `attendance_tracking_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `is_active`",
            'punch_in_required' => "MODIFY `punch_in_required` tinyint(1) NOT NULL DEFAULT '1' AFTER `attendance_tracking_enabled`",
            'punch_out_required' => "MODIFY `punch_out_required` tinyint(1) NOT NULL DEFAULT '1' AFTER `punch_in_required`",
            'allow_web_punch' => "MODIFY `allow_web_punch` tinyint(1) NOT NULL DEFAULT '1' AFTER `punch_out_required`",
            'allow_mobile_punch' => "MODIFY `allow_mobile_punch` tinyint(1) NOT NULL DEFAULT '1' AFTER `allow_web_punch`",
            'mobile_only_punch' => "MODIFY `mobile_only_punch` tinyint(1) NOT NULL DEFAULT '1' AFTER `allow_mobile_punch`",
            'web_punch_disabled' => "MODIFY `web_punch_disabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `mobile_only_punch`",
            'punch_block_enabled' => "MODIFY `punch_block_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `web_punch_disabled`",

            'half_day_enabled' => "MODIFY `half_day_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `punch_block_enabled`",
            'half_day_min_minutes' => "MODIFY `half_day_min_minutes` int NOT NULL DEFAULT '270' AFTER `half_day_enabled`",
            'absent_below_minutes' => "MODIFY `absent_below_minutes` int NOT NULL DEFAULT '270' AFTER `half_day_min_minutes`",
            'auto_absent_enabled' => "MODIFY `auto_absent_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `absent_below_minutes`",

            'early_out_enabled' => "MODIFY `early_out_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `auto_absent_enabled`",
            'early_out_limit' => "MODIFY `early_out_limit` int NOT NULL DEFAULT '2' AFTER `early_out_enabled`",
            'early_violation_enabled' => "MODIFY `early_violation_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `early_out_limit`",
            'early_violation_limit' => "MODIFY `early_violation_limit` int NOT NULL DEFAULT '3' AFTER `early_violation_enabled`",
            'early_out_half_day_minutes' => "MODIFY `early_out_half_day_minutes` int unsigned NOT NULL DEFAULT '90' AFTER `early_violation_limit`",
            'early_out_grace_minutes' => "MODIFY `early_out_grace_minutes` int unsigned NOT NULL DEFAULT '60' AFTER `early_out_half_day_minutes`",

            'late_mark_enabled' => "MODIFY `late_mark_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `early_out_grace_minutes`",
            'late_mark_limit' => "MODIFY `late_mark_limit` int NOT NULL DEFAULT '2' AFTER `late_mark_enabled`",
            'late_violation_enabled' => "MODIFY `late_violation_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `late_mark_limit`",
            'late_violation_limit' => "MODIFY `late_violation_limit` int NOT NULL DEFAULT '3' AFTER `late_violation_enabled`",
            'grace_late_allowed' => "MODIFY `grace_late_allowed` int NOT NULL DEFAULT '0' AFTER `late_violation_limit`",
            'late_lwp_after' => "MODIFY `late_lwp_after` int NOT NULL DEFAULT '3' AFTER `grace_late_allowed`",

            'combined_violation_enabled' => "MODIFY `combined_violation_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `late_lwp_after`",
            'combined_violation_limit' => "MODIFY `combined_violation_limit` int NOT NULL DEFAULT '3' AFTER `combined_violation_enabled`",
            'combined_violation_action' => "MODIFY `combined_violation_action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'half_day' AFTER `combined_violation_limit`",

            'allowed_missed_punches' => "MODIFY `allowed_missed_punches` int NOT NULL DEFAULT '2' AFTER `combined_violation_action`",
            'missed_punch_after_minutes' => "MODIFY `missed_punch_after_minutes` int NOT NULL DEFAULT '60' AFTER `allowed_missed_punches`",
            'missed_punch_action' => "MODIFY `missed_punch_action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lwp' AFTER `missed_punch_after_minutes`",
            'missed_punch_lwp_after' => "MODIFY `missed_punch_lwp_after` int NOT NULL DEFAULT '3' AFTER `missed_punch_action`",
            'auto_lwp_for_missed_punch' => "MODIFY `auto_lwp_for_missed_punch` tinyint(1) NOT NULL DEFAULT '1' AFTER `missed_punch_lwp_after`",

            'wfh_enabled' => "MODIFY `wfh_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `auto_lwp_for_missed_punch`",
            'monthly_wfh_limit' => "MODIFY `monthly_wfh_limit` int NOT NULL DEFAULT '2' AFTER `wfh_enabled`",
            'manager_approval_required' => "MODIFY `manager_approval_required` tinyint(1) NOT NULL DEFAULT '1' AFTER `monthly_wfh_limit`",
            'internet_proof_required' => "MODIFY `internet_proof_required` tinyint(1) NOT NULL DEFAULT '1' AFTER `manager_approval_required`",
            'speed_test_required' => "MODIFY `speed_test_required` tinyint(1) NOT NULL DEFAULT '1' AFTER `internet_proof_required`",
            'internet_issue_lwp' => "MODIFY `internet_issue_lwp` tinyint(1) NOT NULL DEFAULT '1' AFTER `speed_test_required`",
            'electricity_issue_lwp' => "MODIFY `electricity_issue_lwp` tinyint(1) NOT NULL DEFAULT '1' AFTER `internet_issue_lwp`",

            'regularization_enabled' => "MODIFY `regularization_enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `electricity_issue_lwp`",
            'regularization_days' => "MODIFY `regularization_days` int NOT NULL DEFAULT '7' AFTER `regularization_enabled`",
            'regularization_requires_approval' => "MODIFY `regularization_requires_approval` tinyint(1) NOT NULL DEFAULT '1' AFTER `regularization_days`",

            'created_at' => "MODIFY `created_at` timestamp NULL DEFAULT NULL AFTER `regularization_requires_approval`",
            'updated_at' => "MODIFY `updated_at` timestamp NULL DEFAULT NULL AFTER `created_at`",
        ];

        foreach ($orderedColumns as $col => $sql) {
            if (Schema::hasColumn('attendance_policy_rules', $col)) {
                try {
                    DB::statement("ALTER TABLE `attendance_policy_rules` " . $sql);
                } catch (\Throwable $e) {
                    // Ignore non-fatal column reordering issues
                }
            }
        }
    }

    public function down(): void
    {
        // Reordering is cosmetic only, no down rollback needed.
    }
};
