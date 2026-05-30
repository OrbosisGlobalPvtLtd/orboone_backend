<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enterprise_payroll_policies')) {
            return;
        }

        Schema::create('enterprise_payroll_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('policy_name')->default('Default Payroll Policy');
            $table->string('salary_day_basis', 40)->default('working_days');
            $table->string('working_day_mode', 60)->default('exclude_weekoffs');
            $table->unsignedSmallInteger('custom_fixed_days')->nullable();

            $table->boolean('professional_tax_enabled')->default(true);
            $table->decimal('professional_tax_amount', 12, 2)->default(200);

            $table->boolean('pf_enabled')->default(false);
            $table->decimal('pf_percentage', 5, 2)->default(0);

            $table->boolean('esi_enabled')->default(false);
            $table->decimal('esi_percentage', 5, 2)->default(0);

            $table->boolean('tds_enabled')->default(false);
            $table->decimal('tds_percentage', 5, 2)->default(0);

            $table->boolean('allow_negative_salary')->default(false);
            $table->boolean('payroll_lock_after_generation')->default(false);

            $table->boolean('include_weekoff_in_payable')->default(true);
            $table->boolean('include_holiday_in_payable')->default(true);

            $table->decimal('half_day_payable_ratio', 5, 2)->default(0.5);
            $table->decimal('absent_payable_ratio', 5, 2)->default(0);
            $table->decimal('lwp_payable_ratio', 5, 2)->default(0);
            $table->decimal('paid_leave_payable_ratio', 5, 2)->default(1);
            $table->decimal('weekoff_payable_ratio', 5, 2)->default(1);
            $table->decimal('holiday_payable_ratio', 5, 2)->default(1);

            $table->unsignedTinyInteger('salary_credit_start_day')->default(7);
            $table->unsignedTinyInteger('salary_credit_end_day')->default(10);
            $table->unsignedTinyInteger('future_salary_credit_start_day')->default(5);
            $table->unsignedTinyInteger('future_salary_credit_end_day')->default(7);

            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_payroll_policies');
    }
};

