<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_monthly_balances')) {
            return;
        }

        Schema::create('leave_monthly_balances', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_allocation_id')->nullable();
            $table->unsignedBigInteger('policy_id')->nullable();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('employment_stage', 30)->nullable();
            $table->string('balance_source', 24)->default('legacy_projection');

            foreach (['pl', 'sl'] as $bucket) {
                $table->decimal("{$bucket}_opening", 10, 6)->default(0);
                $table->decimal("{$bucket}_accrued", 10, 6)->default(0);
                $table->decimal("{$bucket}_used", 10, 6)->default(0);
                $table->decimal("{$bucket}_adjustment", 10, 6)->default(0);
                $table->decimal("{$bucket}_lapsed", 10, 6)->default(0);
                $table->decimal("{$bucket}_closing", 10, 6)->default(0);
            }

            $table->decimal('comp_off_earned', 10, 6)->default(0);
            $table->decimal('comp_off_used', 10, 6)->default(0);
            $table->decimal('comp_off_lapsed', 10, 6)->default(0);
            $table->decimal('comp_off_closing', 10, 6)->default(0);
            $table->decimal('lwp_used', 10, 6)->default(0);

            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by_user_id')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'month'], 'leave_monthly_employee_period_unique');
            $table->index(['year', 'month'], 'leave_monthly_period_idx');
            $table->index(['employee_id', 'year'], 'leave_monthly_employee_year_idx');
            $table->index('leave_allocation_id', 'leave_monthly_allocation_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_monthly_balances');
    }
};
