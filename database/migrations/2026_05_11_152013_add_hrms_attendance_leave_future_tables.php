<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Update employees_new
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                if (!Schema::hasColumn('employees_new', 'employee_stage')) {
                    $table->string('employee_stage')->nullable()->after('employment_type');
                }

                if (!Schema::hasColumn('employees_new', 'confirmation_date')) {
                    $table->date('confirmation_date')->nullable()->after('joining_date');
                }

                if (!Schema::hasColumn('employees_new', 'probation_start_date')) {
                    $table->date('probation_start_date')->nullable()->after('confirmation_date');
                }

                if (!Schema::hasColumn('employees_new', 'probation_end_date')) {
                    $table->date('probation_end_date')->nullable()->after('probation_start_date');
                }

                if (!Schema::hasColumn('employees_new', 'internship_start_date')) {
                    $table->date('internship_start_date')->nullable()->after('probation_end_date');
                }

                if (!Schema::hasColumn('employees_new', 'internship_end_date')) {
                    $table->date('internship_end_date')->nullable()->after('internship_start_date');
                }

                if (!Schema::hasColumn('employees_new', 'leave_policy_id')) {
                    $table->unsignedBigInteger('leave_policy_id')->nullable()->after('internship_end_date');
                }

                if (!Schema::hasColumn('employees_new', 'attendance_policy_rule_id')) {
                    $table->unsignedBigInteger('attendance_policy_rule_id')->nullable()->after('leave_policy_id');
                }

                if (!Schema::hasColumn('employees_new', 'weekly_off_group')) {
                    $table->string('weekly_off_group')->nullable()->after('attendance_policy_rule_id');
                }

                if (!Schema::hasColumn('employees_new', 'is_attendance_exempt')) {
                    $table->boolean('is_attendance_exempt')->default(false)->after('weekly_off_group');
                }

                if (!Schema::hasColumn('employees_new', 'is_leave_exempt')) {
                    $table->boolean('is_leave_exempt')->default(false)->after('is_attendance_exempt');
                }
            });

            try {
                Schema::table('employees_new', function (Blueprint $table) {
                    $table->index(['employee_stage'], 'emp_new_stage_idx');
                    $table->index(['leave_policy_id'], 'emp_new_leave_policy_idx');
                    $table->index(['attendance_policy_rule_id'], 'emp_new_att_policy_idx');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update leave_requests
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('leave_requests', 'approval_level')) {
                    $table->string('approval_level')->nullable()->after('status');
                }

                if (!Schema::hasColumn('leave_requests', 'manager_approved_by')) {
                    $table->unsignedBigInteger('manager_approved_by')->nullable()->after('approval_level');
                }

                if (!Schema::hasColumn('leave_requests', 'manager_approved_at')) {
                    $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by');
                }

                if (!Schema::hasColumn('leave_requests', 'hr_approved_by')) {
                    $table->unsignedBigInteger('hr_approved_by')->nullable()->after('manager_approved_at');
                }

                if (!Schema::hasColumn('leave_requests', 'hr_approved_at')) {
                    $table->timestamp('hr_approved_at')->nullable()->after('hr_approved_by');
                }

                if (!Schema::hasColumn('leave_requests', 'cancel_reason')) {
                    $table->longText('cancel_reason')->nullable()->after('rejection_reason');
                }

                if (!Schema::hasColumn('leave_requests', 'cancelled_by_user_id')) {
                    $table->unsignedBigInteger('cancelled_by_user_id')->nullable()->after('cancel_reason');
                }

                if (!Schema::hasColumn('leave_requests', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('cancelled_by_user_id');
                }

                if (!Schema::hasColumn('leave_requests', 'auto_converted_to_lwp')) {
                    $table->boolean('auto_converted_to_lwp')->default(false)->after('cancelled_at');
                }

                if (!Schema::hasColumn('leave_requests', 'payroll_processed')) {
                    $table->boolean('payroll_processed')->default(false)->after('auto_converted_to_lwp');
                }

                if (!Schema::hasColumn('leave_requests', 'attendance_synced')) {
                    $table->boolean('attendance_synced')->default(false)->after('payroll_processed');
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance Regularizations
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('attendance_regularizations')) {
            Schema::create('attendance_regularizations', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('attendance_id')->nullable();

                $table->string('request_type')->default('manual_correction');

                $table->timestamp('existing_punch_in')->nullable();
                $table->timestamp('existing_punch_out')->nullable();

                $table->timestamp('requested_punch_in')->nullable();
                $table->timestamp('requested_punch_out')->nullable();

                $table->longText('reason')->nullable();
                $table->string('attachment_path')->nullable();

                $table->enum('status', [
                    'pending',
                    'approved',
                    'rejected',
                    'cancelled'
                ])->default('pending');

                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->longText('rejection_reason')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['employee_id', 'status']);
                $table->index(['attendance_id']);
                $table->index(['request_type']);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Holiday Work Requests
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('holiday_work_requests')) {
            Schema::create('holiday_work_requests', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('attendance_id')->nullable();

                $table->date('worked_date');
                $table->string('work_type')->default('holiday_work');

                $table->boolean('comp_off_generated')->default(false);
                $table->unsignedBigInteger('comp_off_id')->nullable();

                $table->longText('reason')->nullable();

                $table->enum('status', [
                    'pending',
                    'approved',
                    'rejected',
                    'cancelled'
                ])->default('pending');

                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->longText('rejection_reason')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['employee_id', 'worked_date']);
                $table->index(['status']);
                $table->index(['comp_off_id']);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Monthly Attendance Summaries
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('monthly_attendance_summaries')) {
            Schema::create('monthly_attendance_summaries', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('employee_id');

                $table->unsignedTinyInteger('month');
                $table->unsignedInteger('year');

                $table->decimal('present_days', 8, 2)->default(0);
                $table->decimal('paid_leave_days', 8, 2)->default(0);
                $table->decimal('sick_leave_days', 8, 2)->default(0);
                $table->decimal('comp_off_days', 8, 2)->default(0);
                $table->decimal('holiday_days', 8, 2)->default(0);
                $table->decimal('week_off_days', 8, 2)->default(0);
                $table->decimal('half_days', 8, 2)->default(0);
                $table->decimal('lwp_days', 8, 2)->default(0);
                $table->decimal('absent_days', 8, 2)->default(0);

                $table->unsignedInteger('late_count')->default(0);
                $table->unsignedInteger('early_out_count')->default(0);
                $table->unsignedInteger('missed_punch_count')->default(0);

                $table->integer('total_work_minutes')->default(0);

                $table->decimal('payable_days', 8, 2)->default(0);

                $table->boolean('is_locked')->default(false);
                $table->unsignedBigInteger('locked_by_user_id')->nullable();
                $table->timestamp('locked_at')->nullable();

                $table->timestamps();

                $table->unique(['employee_id', 'month', 'year'], 'monthly_attendance_unique');
                $table->index(['month', 'year']);
                $table->index(['is_locked']);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Leave Policy Employee Overrides
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('leave_policy_employee_overrides')) {
            Schema::create('leave_policy_employee_overrides', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('leave_policy_id');

                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();

                $table->longText('remarks')->nullable();

                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(
                    ['employee_id', 'leave_policy_id', 'effective_from'],
                    'leave_policy_override_unique'
                );

                $table->index(['employee_id', 'is_active'], 'leave_policy_emp_active_idx');
                $table->index(['leave_policy_id']);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance Policy Employee Overrides
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('attendance_policy_employee_overrides')) {
            Schema::create('attendance_policy_employee_overrides', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('attendance_policy_rule_id');

                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();

                $table->longText('remarks')->nullable();

                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(
                    ['employee_id', 'attendance_policy_rule_id', 'effective_from'],
                    'attendance_policy_override_unique'
                );

                $table->index(['employee_id', 'is_active'], 'attendance_policy_emp_active_idx');
                $table->index(['attendance_policy_rule_id'], 'attendance_policy_rule_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_policy_employee_overrides');
        Schema::dropIfExists('leave_policy_employee_overrides');
        Schema::dropIfExists('monthly_attendance_summaries');
        Schema::dropIfExists('holiday_work_requests');
        Schema::dropIfExists('attendance_regularizations');

        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $columns = [
                    'attendance_synced',
                    'payroll_processed',
                    'auto_converted_to_lwp',
                    'cancelled_at',
                    'cancelled_by_user_id',
                    'cancel_reason',
                    'hr_approved_at',
                    'hr_approved_by',
                    'manager_approved_at',
                    'manager_approved_by',
                    'approval_level',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('leave_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                $columns = [
                    'is_leave_exempt',
                    'is_attendance_exempt',
                    'weekly_off_group',
                    'attendance_policy_rule_id',
                    'leave_policy_id',
                    'internship_end_date',
                    'internship_start_date',
                    'probation_end_date',
                    'probation_start_date',
                    'confirmation_date',
                    'employee_stage',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('employees_new', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};