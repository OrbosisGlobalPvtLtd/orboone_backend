<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Helper method to add column safely if it does not exist.
     */
    private function addColumn(Blueprint $table, string $tableName, string $columnName, string $type, mixed $default = null, bool $nullable = false): void
    {
        if (! Schema::hasColumn($tableName, $columnName)) {
            $col = match ($type) {
                'string' => $table->string($columnName),
                'text' => $table->text($columnName),
                'integer' => $table->integer($columnName),
                'boolean' => $table->boolean($columnName),
                default => $table->string($columnName),
            };

            if ($nullable) {
                $col->nullable();
            }
            if ($default !== null) {
                $col->default($default);
            }
        }
    }

    public function up(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                // Basic
                $this->addColumn($table, 'attendance_policy_rules', 'description', 'text', null, true);
                $this->addColumn($table, 'attendance_policy_rules', 'is_active', 'boolean', true);

                // Attendance Tracking Policy
                $this->addColumn($table, 'attendance_policy_rules', 'attendance_tracking_enabled', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'punch_in_required', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'punch_out_required', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'allow_web_punch', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'allow_mobile_punch', 'boolean', true);

                // Working Hours Policy
                $this->addColumn($table, 'attendance_policy_rules', 'working_hours_per_day', 'integer', 9);
                $this->addColumn($table, 'attendance_policy_rules', 'working_days_per_week', 'integer', 6);
                $this->addColumn($table, 'attendance_policy_rules', 'weekly_off_count', 'integer', 1);

                // Missed Punch Policy
                $this->addColumn($table, 'attendance_policy_rules', 'allowed_missed_punches', 'integer', 2);
                $this->addColumn($table, 'attendance_policy_rules', 'missed_punch_lwp_after', 'integer', 3);
                $this->addColumn($table, 'attendance_policy_rules', 'auto_lwp_for_missed_punch', 'boolean', true);

                // Late Policy
                $this->addColumn($table, 'attendance_policy_rules', 'late_mark_limit', 'integer', 2);
                $this->addColumn($table, 'attendance_policy_rules', 'early_out_limit', 'integer', 2);
                $this->addColumn($table, 'attendance_policy_rules', 'late_half_day_after', 'integer', 2);
                $this->addColumn($table, 'attendance_policy_rules', 'late_lwp_after', 'integer', 3);
                $this->addColumn($table, 'attendance_policy_rules', 'count_early_out_with_late', 'boolean', true);

                // Half Day Policy
                $this->addColumn($table, 'attendance_policy_rules', 'half_day_enabled', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'half_day_after_late', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'half_day_after_early_out', 'boolean', true);

                // Auto Block Policy
                $this->addColumn($table, 'attendance_policy_rules', 'auto_block_enabled', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'auto_absent_enabled', 'boolean', true);

                // WFH Policy
                $this->addColumn($table, 'attendance_policy_rules', 'wfh_enabled', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'monthly_wfh_limit', 'integer', 2);
                $this->addColumn($table, 'attendance_policy_rules', 'manager_approval_required', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'internet_proof_required', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'speed_test_required', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'internet_issue_lwp', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'electricity_issue_lwp', 'boolean', true);

                // Regularization Policy
                $this->addColumn($table, 'attendance_policy_rules', 'regularization_enabled', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'regularization_days', 'integer', 7);
                $this->addColumn($table, 'attendance_policy_rules', 'regularization_requires_approval', 'boolean', true);

                // Leave Policy
                $this->addColumn($table, 'attendance_policy_rules', 'lwp_enabled', 'boolean', true);
                $this->addColumn($table, 'attendance_policy_rules', 'grace_late_allowed', 'integer', 0);
                $this->addColumn($table, 'attendance_policy_rules', 'grace_early_allowed', 'integer', 0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                $columns = [
                    'description',
                    'attendance_tracking_enabled',
                    'punch_in_required',
                    'punch_out_required',
                    'allow_web_punch',
                    'allow_mobile_punch',
                    'working_hours_per_day',
                    'working_days_per_week',
                    'weekly_off_count',
                    'missed_punch_lwp_after',
                    'auto_lwp_for_missed_punch',
                    'late_mark_limit',
                    'early_out_limit',
                    'late_half_day_after',
                    'late_lwp_after',
                    'count_early_out_with_late',
                    'half_day_enabled',
                    'half_day_after_late',
                    'half_day_after_early_out',
                    'wfh_enabled',
                    'monthly_wfh_limit',
                    'manager_approval_required',
                    'internet_proof_required',
                    'speed_test_required',
                    'internet_issue_lwp',
                    'electricity_issue_lwp',
                    'regularization_enabled',
                    'regularization_days',
                    'regularization_requires_approval',
                    'lwp_enabled',
                    'grace_late_allowed',
                    'grace_early_allowed',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('attendance_policy_rules', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
