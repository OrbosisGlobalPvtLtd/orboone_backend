<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table: attendance_policy_rules
        if (Schema::hasTable('attendance_policy_rules')) {
            // Rename early_out_full_day_minutes to early_out_grace_minutes if it exists and early_out_grace_minutes doesn't
            if (Schema::hasColumn('attendance_policy_rules', 'early_out_full_day_minutes') && ! Schema::hasColumn('attendance_policy_rules', 'early_out_grace_minutes')) {
                Schema::table('attendance_policy_rules', function (Blueprint $table) {
                    $table->renameColumn('early_out_full_day_minutes', 'early_out_grace_minutes');
                });
            }

            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                // Add early_out_grace_minutes if still not present
                if (! Schema::hasColumn('attendance_policy_rules', 'early_out_grace_minutes')) {
                    $col = $table->unsignedInteger('early_out_grace_minutes')->default(60);
                    if (Schema::hasColumn('attendance_policy_rules', 'early_out_half_day_minutes')) {
                        $col->after('early_out_half_day_minutes');
                    }
                }

                // Change early_out_half_day_minutes default from 60 to 90
                if (Schema::hasColumn('attendance_policy_rules', 'early_out_half_day_minutes')) {
                    $table->unsignedInteger('early_out_half_day_minutes')->default(90)->change();
                }

                // Drop obsolete columns if they exist
                if (Schema::hasColumn('attendance_policy_rules', 'early_out_threshold_minutes')) {
                    $table->dropColumn('early_out_threshold_minutes');
                }
                if (Schema::hasColumn('attendance_policy_rules', 'early_out_absent_minutes')) {
                    $table->dropColumn('early_out_absent_minutes');
                }
            });

            // Set existing rows for early_out_half_day_minutes to 90
            DB::table('attendance_policy_rules')
                ->where('early_out_half_day_minutes', 60)
                ->orWhereNull('early_out_half_day_minutes')
                ->update(['early_out_half_day_minutes' => 90]);
        }

        // 2. Table: attendance_times - clean up any rule columns (rules live ONLY in attendance_policy_rules)
        if (Schema::hasTable('attendance_times')) {
            $shiftColsToDrop = [];
            foreach (['early_out_full_day_minutes', 'early_out_grace_minutes', 'early_out_threshold_minutes', 'early_out_absent_minutes'] as $col) {
                if (Schema::hasColumn('attendance_times', $col)) {
                    $shiftColsToDrop[] = $col;
                }
            }
            if (! empty($shiftColsToDrop)) {
                Schema::table('attendance_times', function (Blueprint $table) use ($shiftColsToDrop) {
                    $table->dropColumn($shiftColsToDrop);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_policy_rules')) {
            // Restore row values before dropping/changing schema. This keeps data
            // rollback atomic with respect to schema operations where possible.
            if (Schema::hasColumn('attendance_policy_rules', 'early_out_half_day_minutes')) {
                DB::table('attendance_policy_rules')
                    ->where('early_out_half_day_minutes', 90)
                    ->update(['early_out_half_day_minutes' => 60]);
            }

            Schema::table('attendance_policy_rules', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_policy_rules', 'early_out_grace_minutes')) {
                    $table->dropColumn('early_out_grace_minutes');
                }
                if (Schema::hasColumn('attendance_policy_rules', 'early_out_half_day_minutes')) {
                    $table->unsignedInteger('early_out_half_day_minutes')->default(60)->change();
                }
            });
        }
    }
};
