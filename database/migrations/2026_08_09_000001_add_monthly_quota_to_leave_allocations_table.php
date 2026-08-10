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
        if (! Schema::hasColumn('leave_allocations', 'monthly_quota')) {
            Schema::table('leave_allocations', function (Blueprint $table) {
                $table->decimal('monthly_quota', 8, 2)->default(0.00)->after('monthly_used_this_month');
            });
        }

        // Backfill monthly_quota safely from active policy or default 2.00
        DB::table('leave_allocations')
            ->where('monthly_quota', 0.00)
            ->update(['monthly_quota' => 2.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('leave_allocations', 'monthly_quota')) {
            Schema::table('leave_allocations', function (Blueprint $table) {
                $table->dropColumn('monthly_quota');
            });
        }
    }
};
