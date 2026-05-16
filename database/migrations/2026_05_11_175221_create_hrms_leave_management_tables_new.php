<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();

            $table->string('policy_name')->default('Default Leave Policy');

            $table->decimal('annual_total_leaves', 6, 2)->default(25);
            $table->decimal('annual_paid_leaves', 6, 2)->default(18);
            $table->decimal('annual_sick_leaves', 6, 2)->default(7);

            $table->decimal('monthly_leave_limit', 6, 2)->default(2);
            $table->boolean('allow_monthly_balance_accumulation')->default(true);
            $table->decimal('max_leave_at_once', 6, 2)->default(15);

            $table->boolean('carry_forward_enabled')->default(false);
            $table->unsignedTinyInteger('leave_lapse_month')->default(12);
            $table->unsignedTinyInteger('leave_lapse_day')->default(31);

            $table->boolean('sandwich_enabled')->default(true);
            $table->boolean('weekoff_included_in_sandwich')->default(true);
            $table->boolean('holiday_included_in_sandwich')->default(true);

            $table->boolean('nov_dec_half_usage_enabled')->default(true);
            $table->decimal('nov_dec_threshold_balance', 6, 2)->default(10);
            $table->decimal('nov_dec_usage_percentage', 5, 2)->default(50);

            $table->decimal('probation_leave_limit', 6, 2)->default(1);
            $table->decimal('internship_leave_limit', 6, 2)->default(1);

            $table->unsignedTinyInteger('medical_certificate_after_days')->default(2);
            $table->boolean('comp_off_expiry_same_month')->default(true);

            $table->string('rounding_method', 20)->default('nearest'); // nearest, floor, ceil
            $table->boolean('allow_negative_balance')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();

            $table->boolean('is_paid')->default(false);
            $table->boolean('is_sick')->default(false);
            $table->boolean('is_lwp')->default(false);
            $table->boolean('is_comp_off')->default(false);

            $table->boolean('requires_attachment')->default(false);
            $table->unsignedTinyInteger('medical_certificate_after_days')->nullable();

            $table->decimal('max_days_per_month', 6, 2)->nullable();
            $table->decimal('max_days_per_request', 6, 2)->nullable();

            $table->boolean('allow_half_day')->default(true);
            $table->boolean('applicable_after_confirmation')->default(false);

            $table->string('color', 30)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        Schema::create('weekoff_rules', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('weekday'); // 1=Mon, 7=Sun
            $table->unsignedTinyInteger('week_number')->nullable(); // 1,2,3,4,5

            $table->boolean('is_working')->default(false);
            $table->boolean('is_off')->default(true);

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['weekday', 'week_number', 'is_active']);
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->date('holiday_date');

            $table->string('holiday_type')->default('company'); // company, national, optional, event
            $table->boolean('is_national')->default(false);
            $table->boolean('is_optional')->default(false);

            $table->boolean('is_working_day_override')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->timestamps();

            $table->unique(['holiday_date', 'title'], 'holidays_date_title_unique');
            $table->index(['holiday_date', 'is_active']);
            $table->index('created_by_user_id');
        });

        Schema::create('leave_allocations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedInteger('year');
            $table->unsignedBigInteger('policy_id')->nullable();

            $table->string('employment_stage')->default('permanent'); // internship, probation, permanent
            $table->date('confirmation_date')->nullable();

            $table->date('allocation_from_date')->nullable();
            $table->date('allocation_to_date')->nullable();

            $table->decimal('total_allocated', 8, 2)->default(0);
            $table->decimal('paid_allocated', 8, 2)->default(0);
            $table->decimal('sick_allocated', 8, 2)->default(0);
            $table->decimal('comp_off_allocated', 8, 2)->default(0);

            $table->decimal('total_used', 8, 2)->default(0);
            $table->decimal('paid_used', 8, 2)->default(0);
            $table->decimal('sick_used', 8, 2)->default(0);
            $table->decimal('comp_off_used', 8, 2)->default(0);
            $table->decimal('lwp_used', 8, 2)->default(0);

            $table->decimal('total_remaining', 8, 2)->default(0);
            $table->decimal('paid_remaining', 8, 2)->default(0);
            $table->decimal('sick_remaining', 8, 2)->default(0);
            $table->decimal('comp_off_remaining', 8, 2)->default(0);

            $table->string('allocation_reason')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->boolean('is_locked')->default(false);

            $table->timestamps();

            $table->unique(['employee_id', 'year', 'employment_stage'], 'leave_alloc_emp_year_stage_unique');
            $table->index(['employee_id', 'year']);
            $table->index('policy_id');
            $table->index('created_by_user_id');
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('leave_type_id');

            $table->unsignedBigInteger('reporting_manager_employee_id')->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('requested_days', 8, 2)->default(0);
            $table->decimal('deducted_days', 8, 2)->default(0);

            $table->boolean('is_half_day')->default(false);
            $table->string('half_day_type')->nullable(); // first_half, second_half

            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->unsignedBigInteger('cancelled_by_user_id')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->boolean('sandwich_applied')->default(false);

            $table->decimal('paid_days', 8, 2)->default(0);
            $table->decimal('sick_days', 8, 2)->default(0);
            $table->decimal('comp_off_days', 8, 2)->default(0);
            $table->decimal('lwp_days', 8, 2)->default(0);

            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('user_id');
            $table->index('leave_type_id');
            $table->index('approved_by_user_id');
            $table->index('cancelled_by_user_id');
            $table->index('reporting_manager_employee_id');
        });

        Schema::create('leave_request_dates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('leave_request_id');
            $table->unsignedBigInteger('employee_id');

            $table->date('leave_date');
            $table->string('day_name')->nullable();

            $table->boolean('is_working_day')->default(true);
            $table->boolean('is_weekoff')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_sandwich_day')->default(false);

            $table->boolean('deduct_as_leave')->default(true);

            $table->string('leave_type_code')->nullable();

            $table->decimal('paid_day', 4, 2)->default(0);
            $table->decimal('sick_day', 4, 2)->default(0);
            $table->decimal('comp_off_day', 4, 2)->default(0);
            $table->decimal('lwp_day', 4, 2)->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(['leave_request_id', 'leave_date'], 'leave_request_dates_unique');
            $table->index(['employee_id', 'leave_date']);
            $table->index(['leave_date', 'deduct_as_leave']);
        });

        Schema::create('comp_offs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');

            $table->date('worked_date');
            $table->decimal('earned_days', 4, 2)->default(1);

            $table->date('expiry_date')->nullable();

            $table->enum('status', [
                'earned',
                'used',
                'expired',
                'cancelled',
            ])->default('earned');

            $table->unsignedBigInteger('used_against_leave_request_id')->nullable();

            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['worked_date', 'expiry_date']);
            $table->index('used_against_leave_request_id');
            $table->index('approved_by_user_id');
        });

        Schema::create('leave_balance_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_allocation_id')->nullable();
            $table->unsignedBigInteger('leave_request_id')->nullable();
            $table->unsignedBigInteger('leave_type_id')->nullable();

            $table->string('action');

            $table->decimal('credit', 8, 2)->default(0);
            $table->decimal('debit', 8, 2)->default(0);

            $table->decimal('balance_before', 8, 2)->default(0);
            $table->decimal('balance_after', 8, 2)->default(0);

            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'action']);
            $table->index('leave_allocation_id');
            $table->index('leave_request_id');
            $table->index('leave_type_id');
            $table->index('created_by_user_id');
        });

        $now = now();

        DB::table('leave_policies')->insert([
            'policy_name' => 'Default Leave Policy',
            'annual_total_leaves' => 25,
            'annual_paid_leaves' => 18,
            'annual_sick_leaves' => 7,
            'monthly_leave_limit' => 2,
            'allow_monthly_balance_accumulation' => true,
            'max_leave_at_once' => 15,
            'carry_forward_enabled' => false,
            'leave_lapse_month' => 12,
            'leave_lapse_day' => 31,
            'sandwich_enabled' => true,
            'weekoff_included_in_sandwich' => true,
            'holiday_included_in_sandwich' => true,
            'nov_dec_half_usage_enabled' => true,
            'nov_dec_threshold_balance' => 10,
            'nov_dec_usage_percentage' => 50,
            'probation_leave_limit' => 1,
            'internship_leave_limit' => 1,
            'medical_certificate_after_days' => 2,
            'comp_off_expiry_same_month' => true,
            'rounding_method' => 'nearest',
            'allow_negative_balance' => false,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('leave_types')->insert([
            [
                'name' => 'Paid Leave',
                'code' => 'paid_leave',
                'is_paid' => true,
                'is_sick' => false,
                'is_lwp' => false,
                'is_comp_off' => false,
                'requires_attachment' => false,
                'medical_certificate_after_days' => null,
                'max_days_per_month' => 2,
                'max_days_per_request' => 15,
                'allow_half_day' => true,
                'applicable_after_confirmation' => true,
                'color' => '#12B76A',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'sick_leave',
                'is_paid' => true,
                'is_sick' => true,
                'is_lwp' => false,
                'is_comp_off' => false,
                'requires_attachment' => false,
                'medical_certificate_after_days' => 2,
                'max_days_per_month' => 2,
                'max_days_per_request' => 15,
                'allow_half_day' => true,
                'applicable_after_confirmation' => true,
                'color' => '#F79009',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Comp Off',
                'code' => 'comp_off',
                'is_paid' => true,
                'is_sick' => false,
                'is_lwp' => false,
                'is_comp_off' => true,
                'requires_attachment' => false,
                'medical_certificate_after_days' => null,
                'max_days_per_month' => null,
                'max_days_per_request' => 15,
                'allow_half_day' => true,
                'applicable_after_confirmation' => false,
                'color' => '#4B00E8',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Leave Without Pay',
                'code' => 'lwp',
                'is_paid' => false,
                'is_sick' => false,
                'is_lwp' => true,
                'is_comp_off' => false,
                'requires_attachment' => false,
                'medical_certificate_after_days' => null,
                'max_days_per_month' => null,
                'max_days_per_request' => null,
                'allow_half_day' => true,
                'applicable_after_confirmation' => false,
                'color' => '#D92D20',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('weekoff_rules')->insert([
            ['weekday' => 1, 'week_number' => null, 'is_working' => true,  'is_off' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 2, 'week_number' => null, 'is_working' => true,  'is_off' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 3, 'week_number' => null, 'is_working' => true,  'is_off' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 4, 'week_number' => null, 'is_working' => true,  'is_off' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 5, 'week_number' => null, 'is_working' => true,  'is_off' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['weekday' => 6, 'week_number' => 1, 'is_working' => true,  'is_off' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 6, 'week_number' => 2, 'is_working' => false, 'is_off' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 6, 'week_number' => 3, 'is_working' => true,  'is_off' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 6, 'week_number' => 4, 'is_working' => false, 'is_off' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 6, 'week_number' => 5, 'is_working' => false, 'is_off' => true,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['weekday' => 7, 'week_number' => null, 'is_working' => false, 'is_off' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_logs');
        Schema::dropIfExists('comp_offs');
        Schema::dropIfExists('leave_request_dates');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_allocations');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('weekoff_rules');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('leave_policies');
    }
};
