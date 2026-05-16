<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeavePolicySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('leave_policies')->updateOrInsert(
            ['policy_name' => 'Default Leave Policy'],
            [
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
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }
}
