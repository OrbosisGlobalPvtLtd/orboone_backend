<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('leave_types')) {
            $now = now();
            DB::table('leave_types')->updateOrInsert(
                ['code' => 'leave'],
                [
                    'name' => 'Leave',
                    'code' => 'leave',
                    'is_paid' => false,
                    'is_sick' => false,
                    'is_lwp' => true,
                    'is_comp_off' => false,
                    'requires_attachment' => false,
                    'medical_certificate_after_days' => null,
                    'max_days_per_month' => null,
                    'max_days_per_request' => 15,
                    'allow_half_day' => true,
                    'applicable_after_confirmation' => false,
                    'color' => '#7C3AED',
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leave_types')) {
            DB::table('leave_types')->where('code', 'leave')->delete();
        }
    }
};

