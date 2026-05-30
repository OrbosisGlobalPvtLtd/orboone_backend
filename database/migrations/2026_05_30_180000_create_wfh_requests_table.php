<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wfh_requests')) {
            return;
        }

        Schema::create('wfh_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('request_date');
            $table->string('request_type', 40)->default('working_day_wfh');
            $table->string('reason_category', 40)->default('normal');
            $table->text('reason')->nullable();
            $table->string('status', 40)->default('pending');

            $table->unsignedBigInteger('manager_approved_by')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->unsignedBigInteger('hr_approved_by')->nullable();
            $table->timestamp('hr_approved_at')->nullable();

            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->boolean('counts_in_monthly_quota')->default(true);
            $table->string('payroll_impact', 20)->default('none');

            $table->text('today_tasks')->nullable();
            $table->text('completed_tasks')->nullable();
            $table->text('work_summary')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('request_date');
            $table->index('status');
            $table->index('request_type');
            $table->index('reason_category');
            $table->index(['employee_id', 'request_date'], 'wfh_emp_date_idx');
            $table->index(['employee_id', 'status', 'counts_in_monthly_quota'], 'wfh_emp_status_quota_idx');
            $table->index(['request_date', 'status'], 'wfh_date_status_idx');
            $table->unique(['employee_id', 'request_date'], 'wfh_emp_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wfh_requests');
    }
};

