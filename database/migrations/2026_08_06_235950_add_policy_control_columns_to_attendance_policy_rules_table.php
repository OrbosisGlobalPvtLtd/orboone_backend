<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_policy_rules', 'combined_violation_enabled')) {
                    $table->boolean('combined_violation_enabled')->default(true)->after('weekly_off_count');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'combined_violation_action')) {
                    $table->string('combined_violation_action', 50)->default('half_day')->after('combined_violation_limit');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'missed_punch_action')) {
                    $table->string('missed_punch_action', 50)->default('lwp')->after('missed_punch_lwp_after');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'late_mark_enabled')) {
                    $table->boolean('late_mark_enabled')->default(true)->after('missed_punch_action');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'early_out_enabled')) {
                    $table->boolean('early_out_enabled')->default(true)->after('late_mark_enabled');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'late_violation_enabled')) {
                    $table->boolean('late_violation_enabled')->default(true)->after('early_out_enabled');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'early_violation_enabled')) {
                    $table->boolean('early_violation_enabled')->default(true)->after('late_violation_enabled');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'auto_lwp_enabled')) {
                    $table->boolean('auto_lwp_enabled')->default(true)->after('lwp_enabled');
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
                $columns = [
                    'combined_violation_enabled',
                    'combined_violation_action',
                    'missed_punch_action',
                    'late_mark_enabled',
                    'early_out_enabled',
                    'late_violation_enabled',
                    'early_violation_enabled',
                    'auto_lwp_enabled',
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
