<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_policy_rules', 'punch_block_enabled')) {
                    $table->boolean('punch_block_enabled')->default(true);
                }
            });

            if (Schema::hasColumn('attendance_policy_rules', 'auto_block_enabled') && Schema::hasColumn('attendance_policy_rules', 'punch_block_enabled')) {
                DB::table('attendance_policy_rules')->update([
                    'punch_block_enabled' => DB::raw('auto_block_enabled'),
                ]);
            }

            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                $columnsToDrop = [
                    'working_hours_per_day',
                    'required_work_minutes',
                    'lunch_break_minutes',
                    'working_days_per_week',
                    'weekly_off_count',
                    'auto_block_enabled',
                    'late_half_day_after',
                    'half_day_after_late',
                    'half_day_after_early_out',
                    'count_early_out_with_late',
                    'lwp_enabled',
                    'auto_lwp_enabled',
                    'grace_early_allowed',
                ];

                foreach ($columnsToDrop as $column) {
                    if (Schema::hasColumn('attendance_policy_rules', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_policy_rules', 'auto_block_enabled')) {
                    $table->boolean('auto_block_enabled')->default(true);
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'required_work_minutes')) {
                    $table->integer('required_work_minutes')->default(480);
                }
            });
        }
    }
};
