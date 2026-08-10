<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to drop legacy clock time windows from attendance_policy_rules table.
     * Retains work minute thresholds (required_work_minutes, half_day_min_minutes, absent_below_minutes, lunch_break_minutes)
     * as policy rule thresholds.
     */
    public function up(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                $columns = [
                    'punch_allowed_from',
                    'shift_start_time',
                    'late_after_time',
                    'warning_after_time',
                    'block_after_time',
                    'shift_end_time',
                    'shift_type',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('attendance_policy_rules', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_policy_rules', 'punch_allowed_from')) {
                    $table->time('punch_allowed_from')->nullable();
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'shift_start_time')) {
                    $table->time('shift_start_time')->nullable();
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'late_after_time')) {
                    $table->time('late_after_time')->nullable();
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'warning_after_time')) {
                    $table->time('warning_after_time')->nullable();
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'block_after_time')) {
                    $table->time('block_after_time')->nullable();
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'shift_end_time')) {
                    $table->time('shift_end_time')->nullable();
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'shift_type')) {
                    $table->string('shift_type')->nullable();
                }
            });
        }
    }
};
