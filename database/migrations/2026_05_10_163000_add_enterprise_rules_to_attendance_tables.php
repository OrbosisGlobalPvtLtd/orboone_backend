<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnterpriseRulesToAttendanceTables extends Migration
{
    public function up()
    {
        // Update attendance_times table
        Schema::table('attendance_times', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_times', 'block_after_time')) {
                $table->time('block_after_time')->nullable()->after('late_after_time');
            }
            if (!Schema::hasColumn('attendance_times', 'break_minutes')) {
                $table->unsignedInteger('break_minutes')->default(60)->after('half_day_min_minutes');
            }
        });

        // Update attendances table
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'break_minutes')) {
                $table->unsignedInteger('break_minutes')->default(60)->after('lunch_break_minutes');
            }
            if (!Schema::hasColumn('attendances', 'violation_count')) {
                $table->unsignedInteger('violation_count')->default(0)->after('early_out_minutes');
            }
            if (!Schema::hasColumn('attendances', 'is_half_day')) {
                $table->boolean('is_half_day')->default(false)->after('violation_count');
            }
            if (!Schema::hasColumn('attendances', 'is_lwp')) {
                $table->boolean('is_lwp')->default(false)->after('is_half_day');
            }
            if (!Schema::hasColumn('attendances', 'missed_punch')) {
                $table->boolean('missed_punch')->default(false)->after('is_lwp');
            }
            if (!Schema::hasColumn('attendances', 'is_punch_blocked')) {
                $table->boolean('is_punch_blocked')->default(false)->after('missed_punch');
            }
            if (!Schema::hasColumn('attendances', 'blocked_reason')) {
                $table->string('blocked_reason')->nullable()->after('block_reason');
            }
            if (!Schema::hasColumn('attendances', 'is_admin_unlocked')) {
                $table->boolean('is_admin_unlocked')->default(false)->after('blocked_reason');
            }
            if (!Schema::hasColumn('attendances', 'unlocked_by')) {
                $table->unsignedBigInteger('unlocked_by')->nullable()->after('is_admin_unlocked');
            }
            if (!Schema::hasColumn('attendances', 'unlocked_at')) {
                $table->timestamp('unlocked_at')->nullable()->after('unlocked_by');
            }
            if (!Schema::hasColumn('attendances', 'old_pending_hr_logic')) {
                $table->text('old_pending_hr_logic')->nullable()->after('unlocked_at');
            }
            if (!Schema::hasColumn('attendances', 'pending_hr_reason')) {
                $table->text('pending_hr_reason')->nullable()->after('old_pending_hr_logic');
            }
            if (!Schema::hasColumn('attendances', 'remarks')) {
                $table->text('remarks')->nullable()->after('pending_hr_reason');
            }

            // Ensure foreign key for unlocked_by if not exists
            // $table->foreign('unlocked_by')->references('id')->on('users')->nullOnDelete();
        });
        
        // Add foreign key separately to avoid issues if table is already populated or if it fails
        Schema::table('attendances', function (Blueprint $table) {
             if (Schema::hasColumn('attendances', 'unlocked_by')) {
                 $table->foreign('unlocked_by')->references('id')->on('users')->nullOnDelete();
             }
        });
    }

    public function down()
    {
        Schema::table('attendance_times', function (Blueprint $table) {
            $table->dropColumn(['block_after_time', 'break_minutes']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['unlocked_by']);
            $table->dropColumn([
                'break_minutes',
                'violation_count',
                'is_half_day',
                'is_lwp',
                'missed_punch',
                'is_punch_blocked',
                'blocked_reason',
                'is_admin_unlocked',
                'unlocked_by',
                'unlocked_at',
                'old_pending_hr_logic',
                'pending_hr_reason',
                'remarks'
            ]);
        });
    }
}
