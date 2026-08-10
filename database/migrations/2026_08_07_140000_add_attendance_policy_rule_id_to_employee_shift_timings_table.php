<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAttendancePolicyRuleIdToEmployeeShiftTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_shift_timings') && ! Schema::hasColumn('employee_shift_timings', 'attendance_policy_rule_id')) {
            Schema::table('employee_shift_timings', function (Blueprint $table) {
                $table->unsignedBigInteger('attendance_policy_rule_id')->nullable()->after('attendance_time_id');
                if (Schema::hasTable('attendance_policy_rules')) {
                    $table->foreign('attendance_policy_rule_id')->references('id')->on('attendance_policy_rules')->onDelete('set null');
                }
            });

            // Backfill default or employee policy rule id for existing shift timing records
            $defaultPolicyId = null;
            if (Schema::hasTable('attendance_policy_rules')) {
                $defaultPolicyId = DB::table('attendance_policy_rules')->where('is_active', 1)->orderBy('id')->value('id')
                    ?: DB::table('attendance_policy_rules')->orderBy('id')->value('id');
            }

            if ($defaultPolicyId) {
                $shiftTimings = DB::table('employee_shift_timings')->whereNull('attendance_policy_rule_id')->get();
                foreach ($shiftTimings as $st) {
                    $empPolicyId = null;
                    if (Schema::hasTable('employees_new')) {
                        $emp = DB::table('employees_new')->where('id', $st->employee_id)->first();
                        $empPolicyId = $emp?->attendance_policy_rule_id ?? $emp?->attendance_policy_id ?? null;
                    }
                    $policyToSet = $empPolicyId ?: $defaultPolicyId;
                    DB::table('employee_shift_timings')->where('id', $st->id)->update([
                        'attendance_policy_rule_id' => $policyToSet,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('employee_shift_timings') && Schema::hasColumn('employee_shift_timings', 'attendance_policy_rule_id')) {
            Schema::table('employee_shift_timings', function (Blueprint $table) {
                $table->dropForeign(['attendance_policy_rule_id']);
                $table->dropColumn('attendance_policy_rule_id');
            });
        }
    }
}
