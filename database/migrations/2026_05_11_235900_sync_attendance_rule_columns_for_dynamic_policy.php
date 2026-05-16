<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_times')) {
            Schema::table('attendance_times', function (Blueprint $table) {
                $this->string($table, 'attendance_times', 'name');
                $this->string($table, 'attendance_times', 'code');
                $this->time($table, 'attendance_times', 'warning_after_time');
                $this->integer($table, 'attendance_times', 'absent_below_minutes', 0);
                $this->integer($table, 'attendance_times', 'lunch_break_minutes', 0);
            });
        }

        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                $this->string($table, 'attendance_policy_rules', 'policy_name');
                $this->time($table, 'attendance_policy_rules', 'punch_allowed_from');
                $this->time($table, 'attendance_policy_rules', 'shift_start_time');
                $this->time($table, 'attendance_policy_rules', 'late_after_time');
                $this->time($table, 'attendance_policy_rules', 'warning_after_time');
                $this->time($table, 'attendance_policy_rules', 'block_after_time');
                $this->time($table, 'attendance_policy_rules', 'shift_end_time');
                $this->integer($table, 'attendance_policy_rules', 'required_work_minutes', 0);
                $this->integer($table, 'attendance_policy_rules', 'half_day_min_minutes', 0);
                $this->integer($table, 'attendance_policy_rules', 'absent_below_minutes', 0);
                $this->integer($table, 'attendance_policy_rules', 'lunch_break_minutes', 0);
                $this->integer($table, 'attendance_policy_rules', 'allowed_missed_punches', 0);
                $this->integer($table, 'attendance_policy_rules', 'combined_violation_limit', 0);
                $this->integer($table, 'attendance_policy_rules', 'late_violation_limit', 0);
                $this->integer($table, 'attendance_policy_rules', 'early_violation_limit', 0);
                $this->boolean($table, 'attendance_policy_rules', 'auto_block_enabled', true);
                $this->boolean($table, 'attendance_policy_rules', 'auto_absent_enabled', true);
                $this->boolean($table, 'attendance_policy_rules', 'is_active', true);
            });
        }

        if (Schema::hasTable('attendance_types')) {
            Schema::table('attendance_types', function (Blueprint $table) {
                $this->string($table, 'attendance_types', 'code');
                $this->boolean($table, 'attendance_types', 'is_paid', true);
                $this->string($table, 'attendance_types', 'color');
                $this->boolean($table, 'attendance_types', 'is_active', true);
            });
        }

        if (Schema::hasTable('weekoff_rules')) {
            Schema::table('weekoff_rules', function (Blueprint $table) {
                $this->integer($table, 'weekoff_rules', 'weekday', 0);
                $this->integer($table, 'weekoff_rules', 'week_number');
                $this->boolean($table, 'weekoff_rules', 'is_working', false);
                $this->boolean($table, 'weekoff_rules', 'is_off', false);
                $this->date($table, 'weekoff_rules', 'effective_from');
                $this->date($table, 'weekoff_rules', 'effective_to');
                $this->boolean($table, 'weekoff_rules', 'is_active', true);
            });
        }
    }

    public function down(): void
    {
        //
    }

    private function time(Blueprint $table, string $schemaTable, string $column): void
    {
        if (! Schema::hasColumn($schemaTable, $column)) {
            $table->time($column)->nullable();
        }
    }

    private function integer(Blueprint $table, string $schemaTable, string $column, ?int $default = null): void
    {
        if (! Schema::hasColumn($schemaTable, $column)) {
            $definition = $table->integer($column)->nullable();
            if ($default !== null) {
                $definition->default($default);
            }
        }
    }

    private function boolean(Blueprint $table, string $schemaTable, string $column, bool $default): void
    {
        if (! Schema::hasColumn($schemaTable, $column)) {
            $table->boolean($column)->default($default);
        }
    }

    private function string(Blueprint $table, string $schemaTable, string $column): void
    {
        if (! Schema::hasColumn($schemaTable, $column)) {
            $table->string($column)->nullable();
        }
    }

    private function date(Blueprint $table, string $schemaTable, string $column): void
    {
        if (! Schema::hasColumn($schemaTable, $column)) {
            $table->date($column)->nullable();
        }
    }
};
