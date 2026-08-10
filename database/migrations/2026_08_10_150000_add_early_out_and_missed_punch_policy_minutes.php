<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_policy_rules', 'early_out_half_day_minutes')) {
                    $table->integer('early_out_half_day_minutes')->default(60);
                }
                if (! Schema::hasColumn('attendance_policy_rules', 'missed_punch_after_minutes')) {
                    $table->integer('missed_punch_after_minutes')->default(60);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_policy_rules', 'early_out_half_day_minutes')) {
                    $table->dropColumn('early_out_half_day_minutes');
                }
                if (Schema::hasColumn('attendance_policy_rules', 'missed_punch_after_minutes')) {
                    $table->dropColumn('missed_punch_after_minutes');
                }
            });
        }
    }
};
