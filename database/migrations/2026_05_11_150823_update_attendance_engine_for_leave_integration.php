<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | attendance_types
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('attendance_types')) {

            Schema::create('attendance_types', function (Blueprint $table) {

                $table->id();

                $table->string('name');
                $table->string('code')->unique();

                $table->boolean('is_paid')->default(false);

                $table->string('color', 30)->nullable();

                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
        }

        $types = [

            ['name' => 'Present', 'code' => 'present', 'is_paid' => 1, 'color' => '#12B76A'],
            ['name' => 'Absent', 'code' => 'absent', 'is_paid' => 0, 'color' => '#D92D20'],
            ['name' => 'Half Day', 'code' => 'half_day', 'is_paid' => 1, 'color' => '#F79009'],
            ['name' => 'Leave', 'code' => 'leave', 'is_paid' => 1, 'color' => '#4B00E8'],
            ['name' => 'Holiday', 'code' => 'holiday', 'is_paid' => 1, 'color' => '#0BA5EC'],
            ['name' => 'Week Off', 'code' => 'week_off', 'is_paid' => 1, 'color' => '#667085'],
            ['name' => 'Late', 'code' => 'late', 'is_paid' => 1, 'color' => '#F79009'],
            ['name' => 'Early Leave', 'code' => 'early_leave', 'is_paid' => 1, 'color' => '#F97316'],
            ['name' => 'LWP', 'code' => 'lwp', 'is_paid' => 0, 'color' => '#B42318'],
            ['name' => 'Pending HR', 'code' => 'pending_hr', 'is_paid' => 0, 'color' => '#7A5AF8'],
            ['name' => 'Punch Blocked', 'code' => 'punch_blocked', 'is_paid' => 0, 'color' => '#344054'],

        ];

        foreach ($types as $type) {

            DB::table('attendance_types')->updateOrInsert(

                ['code' => $type['code']],

                [
                    'name'       => $type['name'],
                    'is_paid'    => $type['is_paid'],
                    'color'      => $type['color'],
                    'is_active'  => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        /*
|--------------------------------------------------------------------------
| attendance_times
|--------------------------------------------------------------------------
*/

        if (!Schema::hasTable('attendance_times')) {

            Schema::create('attendance_times', function (Blueprint $table) {

                $table->id();

                $table->string('name');
                $table->string('code')->unique();

                $table->time('punch_allowed_from')->nullable();
                $table->time('shift_start_time')->nullable();
                $table->time('late_after_time')->nullable();
                $table->time('warning_after_time')->nullable();
                $table->time('block_after_time')->nullable();
                $table->time('shift_end_time')->nullable();

                $table->integer('required_work_minutes')->default(480);
                $table->integer('half_day_min_minutes')->default(270);
                $table->integer('absent_below_minutes')->default(270);
                $table->integer('lunch_break_minutes')->default(60);

                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
        } else {

            Schema::table('attendance_times', function (Blueprint $table) {

                if (!Schema::hasColumn('attendance_times', 'warning_after_time')) {
                    $table->time('warning_after_time')->nullable()->after('late_after_time');
                }

                if (!Schema::hasColumn('attendance_times', 'block_after_time')) {
                    $table->time('block_after_time')->nullable()->after('warning_after_time');
                }

                if (!Schema::hasColumn('attendance_times', 'absent_below_minutes')) {
                    $table->integer('absent_below_minutes')->default(270)->after('half_day_min_minutes');
                }

                if (!Schema::hasColumn('attendance_times', 'lunch_break_minutes')) {
                    $table->integer('lunch_break_minutes')->default(60)->after('absent_below_minutes');
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | attendances
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('attendances')) {

            Schema::create('attendances', function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger('user_id')->nullable();

                $table->unsignedBigInteger('employee_id')->nullable();

                $table->unsignedBigInteger('attendance_time_id')->nullable();

                $table->unsignedBigInteger('attendance_type_id')->nullable();

                $table->unsignedBigInteger('leave_request_id')->nullable();

                $table->unsignedBigInteger('comp_off_id')->nullable();

                $table->date('attendance_date');

                $table->timestamp('punch_in_time')->nullable();

                $table->timestamp('punch_out_time')->nullable();

                $table->string('attendance_status')->default('pending');

                $table->string('attendance_source')->default('mobile');

                $table->string('work_mode')->nullable();

                $table->decimal('punch_in_latitude', 10, 7)->nullable();

                $table->decimal('punch_in_longitude', 10, 7)->nullable();

                $table->text('punch_in_address')->nullable();

                $table->decimal('punch_out_latitude', 10, 7)->nullable();

                $table->decimal('punch_out_longitude', 10, 7)->nullable();

                $table->text('punch_out_address')->nullable();

                $table->string('punch_in_ip')->nullable();

                $table->string('punch_out_ip')->nullable();

                $table->text('punch_in_device')->nullable();

                $table->text('punch_out_device')->nullable();

                $table->integer('gross_work_minutes')->default(0);

                $table->integer('lunch_break_minutes')->default(0);

                $table->integer('total_work_minutes')->default(0);

                $table->boolean('is_late')->default(false);

                $table->integer('late_minutes')->default(0);

                $table->boolean('is_early_out')->default(false);

                $table->integer('early_out_minutes')->default(0);

                $table->integer('combined_violation_count')->default(0);

                $table->integer('violation_count_snapshot')->default(0);

                $table->boolean('is_blocked')->default(false);

                $table->string('blocked_type')->nullable();

                $table->text('block_reason')->nullable();

                $table->timestamp('auto_blocked_at')->nullable();

                $table->text('auto_block_reason')->nullable();

                $table->boolean('is_auto_absent_marked')->default(false);

                $table->unsignedBigInteger('hr_approved_by')->nullable();

                $table->timestamp('hr_approved_at')->nullable();

                $table->text('hr_approval_note')->nullable();

                $table->unsignedBigInteger('approved_unlock_by')->nullable();

                $table->timestamp('approved_unlock_at')->nullable();

                $table->boolean('is_profile_completed_at_punch')->default(false);

                $table->boolean('is_locked')->default(false);

                $table->boolean('is_missed_punch')->default(false);

                $table->text('missed_punch_reason')->nullable();

                $table->boolean('is_lwp')->default(false);

                $table->text('lwp_reason')->nullable();

                $table->boolean('is_half_day')->default(false);

                $table->text('half_day_reason')->nullable();

                $table->text('punch_in_note')->nullable();

                $table->text('punch_out_note')->nullable();

                $table->timestamps();

                $table->unique(['employee_id', 'attendance_date']);

                $table->index(['employee_id', 'attendance_date']);

                $table->index(['attendance_type_id']);

                $table->index(['attendance_time_id']);

                $table->index(['attendance_status']);

                $table->index(['is_blocked']);

                $table->index(['is_lwp']);

                $table->index(['is_half_day']);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | attendance_policy_rules
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('attendance_policy_rules')) {

            Schema::create('attendance_policy_rules', function (Blueprint $table) {

                $table->id();

                $table->string('policy_name')->default('Default Attendance Policy');

                $table->time('punch_allowed_from')->default('09:00:00');

                $table->time('shift_start_time')->default('10:00:00');

                $table->time('late_after_time')->default('11:05:00');

                $table->time('warning_after_time')->default('11:06:00');

                $table->time('block_after_time')->default('11:15:00');

                $table->time('shift_end_time')->default('19:00:00');

                $table->integer('required_work_minutes')->default(480);

                $table->integer('half_day_min_minutes')->default(270);

                $table->integer('absent_below_minutes')->default(270);

                $table->integer('lunch_break_minutes')->default(60);

                $table->integer('allowed_missed_punches')->default(2);

                $table->integer('combined_violation_limit')->default(3);

                $table->integer('late_violation_limit')->default(3);

                $table->integer('early_violation_limit')->default(3);

                $table->boolean('auto_block_enabled')->default(true);

                $table->boolean('auto_absent_enabled')->default(true);

                $table->boolean('mobile_only_punch')->default(true);

                $table->boolean('web_punch_disabled')->default(true);

                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | attendance_violations
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('attendance_violations')) {

            Schema::create('attendance_violations', function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger('employee_id');

                $table->unsignedBigInteger('attendance_id')->nullable();

                $table->date('violation_date');

                $table->string('type');

                $table->integer('minutes')->default(0);

                $table->string('source')->nullable();

                $table->string('policy_action')->nullable();

                $table->boolean('converted_to_half_day')->default(false);

                $table->boolean('converted_to_lwp')->default(false);

                $table->text('remarks')->nullable();

                $table->timestamps();

                $table->index(['employee_id', 'violation_date']);

                $table->index(['type']);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | attendance_work_logs
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('attendance_work_logs')) {

            Schema::create('attendance_work_logs', function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger('attendance_id');

                $table->unsignedBigInteger('employee_id');

                $table->unsignedBigInteger('user_id')->nullable();

                $table->date('work_date');

                $table->string('task_type')->nullable();

                $table->integer('duration_minutes')->default(0);

                $table->longText('work_summary');

                $table->unsignedBigInteger('project_id')->nullable();

                $table->unsignedBigInteger('project_task_id')->nullable();

                $table->timestamps();

                $table->index(['attendance_id']);

                $table->index(['employee_id', 'work_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_work_logs');
        Schema::dropIfExists('attendance_violations');
        Schema::dropIfExists('attendance_policy_rules');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_times');
        Schema::dropIfExists('attendance_types');
    }
};
