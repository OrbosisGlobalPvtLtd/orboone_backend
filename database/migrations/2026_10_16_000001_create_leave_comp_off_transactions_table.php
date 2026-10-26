<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_comp_off_transactions')) {
            return;
        }

        Schema::create('leave_comp_off_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('comp_off_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_request_id')->nullable();
            $table->unsignedBigInteger('leave_request_date_id')->nullable();
            $table->string('transaction_type', 16);
            $table->date('effective_date');
            $table->decimal('amount', 10, 6);
            $table->unsignedBigInteger('reverses_transaction_id')->nullable();
            $table->string('idempotency_key', 180)->unique();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'effective_date', 'id'], 'leave_co_tx_employee_date_idx');
            $table->index(['comp_off_id', 'effective_date', 'id'], 'leave_co_tx_lot_date_idx');
            $table->index('leave_request_id', 'leave_co_tx_request_idx');
            $table->index('leave_request_date_id', 'leave_co_tx_request_date_idx');
            $table->index('reverses_transaction_id', 'leave_co_tx_reversal_idx');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('leave_comp_off_transactions')
            && \Illuminate\Support\Facades\DB::table('leave_comp_off_transactions')->exists()) {
            throw new \RuntimeException('Comp-Off transaction history exists; archive it before rolling back this migration.');
        }

        Schema::dropIfExists('leave_comp_off_transactions');
    }
};
