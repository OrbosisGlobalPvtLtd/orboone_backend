<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balance_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_allocation_id');
            $table->unsignedBigInteger('policy_id')->nullable();
            $table->char('bucket', 2);
            $table->string('transaction_type', 32);
            $table->unsignedInteger('period_year');
            $table->unsignedTinyInteger('period_month')->nullable();
            $table->date('effective_date');
            $table->decimal('amount', 10, 6);
            $table->decimal('balance_before', 10, 6);
            $table->decimal('balance_after', 10, 6);
            $table->unsignedBigInteger('leave_request_id')->nullable();
            $table->unsignedBigInteger('reverses_transaction_id')->nullable();
            $table->string('idempotency_key', 150)->unique();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['employee_id', 'bucket', 'effective_date', 'id'], 'lbt_employee_bucket_effective_idx');
            $table->index(['employee_id', 'bucket', 'period_year', 'period_month'], 'lbt_employee_bucket_period_idx');
            $table->index('leave_allocation_id', 'lbt_allocation_idx');
            $table->index('leave_request_id', 'lbt_request_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_transactions');
    }
};
