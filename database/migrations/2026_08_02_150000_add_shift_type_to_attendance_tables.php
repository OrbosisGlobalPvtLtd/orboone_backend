<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_times')) {
            Schema::table('attendance_times', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_times', 'shift_type')) {
                    $table->string('shift_type')->default('fixed')->after('code');
                }
            });

            DB::statement("ALTER TABLE `attendance_times` MODIFY `punch_allowed_from` TIME NULL DEFAULT '00:00:00', MODIFY `shift_start_time` TIME NULL DEFAULT '00:00:00', MODIFY `late_after_time` TIME NULL DEFAULT '23:59:59', MODIFY `shift_end_time` TIME NULL DEFAULT '23:59:59'");
        }

        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_policy_rules', 'shift_type')) {
                    $table->string('shift_type')->default('fixed')->after('policy_name');
                }
            });
        }

        // Seed default Flexible Part Time shift if not existing
        if (Schema::hasTable('attendance_times')) {
            $exists = DB::table('attendance_times')->where('code', 'flexible_part_time')->exists();
            if (! $exists) {
                DB::table('attendance_times')->insert([
                    'name' => 'Flexible Part Time',
                    'code' => 'flexible_part_time',
                    'shift_type' => 'flexible_part_time',
                    'punch_allowed_from' => null,
                    'early_login_from' => null,
                    'normal_login_from' => null,
                    'shift_start_time' => null,
                    'late_after_time' => null,
                    'warning_after_time' => null,
                    'block_after_time' => null,
                    'half_day_after_time' => null,
                    'shift_end_time' => null,
                    'required_work_minutes' => 300,
                    'required_office_minutes' => 300,
                    'break_minutes' => 0,
                    'half_day_min_minutes' => 180,
                    'absent_below_minutes' => 90,
                    'lunch_break_minutes' => 0,
                    'is_default' => false,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_times')) {
            Schema::table('attendance_times', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_times', 'shift_type')) {
                    $table->dropColumn('shift_type');
                }
            });
        }

        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_policy_rules', 'shift_type')) {
                    $table->dropColumn('shift_type');
                }
            });
        }
    }
};
