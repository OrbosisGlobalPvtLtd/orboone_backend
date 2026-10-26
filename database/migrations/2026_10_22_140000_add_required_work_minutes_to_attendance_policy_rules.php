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
                if (! Schema::hasColumn('attendance_policy_rules', 'required_work_minutes')) {
                    $table->integer('required_work_minutes')->nullable()->default(480)->after('half_day_enabled');
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'lunch_break_minutes')) {
                    $table->integer('lunch_break_minutes')->nullable()->default(60)->after('absent_below_minutes');
                }
            });

            // Set appropriate required work minutes based on policy scope
            DB::table('attendance_policy_rules')
                ->where('policy_name', 'like', '%part time%')
                ->orWhere('policy_name', 'like', '%half day%')
                ->update([
                    'required_work_minutes' => 240,
                    'lunch_break_minutes' => 30,
                ]);

            DB::table('attendance_policy_rules')
                ->whereNull('required_work_minutes')
                ->update([
                    'required_work_minutes' => 480,
                    'lunch_break_minutes' => 60,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_policy_rules', 'required_work_minutes')) {
                    $table->dropColumn('required_work_minutes');
                }
                if (Schema::hasColumn('attendance_policy_rules', 'lunch_break_minutes')) {
                    $table->dropColumn('lunch_break_minutes');
                }
            });
        }
    }
};
