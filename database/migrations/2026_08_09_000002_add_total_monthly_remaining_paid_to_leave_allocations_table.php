<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('leave_allocations', 'total_monthly_remaining_paid')) {
            Schema::table('leave_allocations', function (Blueprint $table) {
                $table->decimal('total_monthly_remaining_paid', 8, 2)->default(0.00)->after('monthly_quota');
            });
        }

        // Synchronize total_monthly_remaining_paid = GREATEST(0, monthly_quota + monthly_carry_forward - monthly_used_this_month)
        DB::table('leave_allocations')->get()->each(function ($allocation) {
            $quota = (float) ($allocation->monthly_quota ?? 2.00);
            $carry = (float) ($allocation->monthly_carry_forward ?? 0.00);
            $used = (float) ($allocation->monthly_used_this_month ?? 0.00);
            $totalMonthlyRemaining = max(0.00, round(($quota + $carry) - $used, 2));

            DB::table('leave_allocations')
                ->where('id', $allocation->id)
                ->update([
                    'monthly_quota' => $quota,
                    'total_monthly_remaining_paid' => $totalMonthlyRemaining,
                    'paid_remaining' => $totalMonthlyRemaining,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('leave_allocations', 'total_monthly_remaining_paid')) {
            Schema::table('leave_allocations', function (Blueprint $table) {
                $table->dropColumn('total_monthly_remaining_paid');
            });
        }
    }
};
