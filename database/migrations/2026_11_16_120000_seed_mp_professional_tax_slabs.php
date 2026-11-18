<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enable Professional Tax on active Enterprise Payroll Policy
        if (Schema::hasTable('enterprise_payroll_policies')) {
            DB::table('enterprise_payroll_policies')->update([
                'professional_tax_enabled' => true,
                'professional_tax_amount' => 0.00,
                'updated_at' => now(),
            ]);
        }

        // 2. Insert MP Professional Tax Slabs
        if (Schema::hasTable('enterprise_professional_tax_slabs')) {
            $now = now();
            // Deactivate any existing MP slabs
            DB::table('enterprise_professional_tax_slabs')
                ->where('jurisdiction_code', 'MP')
                ->update(['is_active' => false, 'updated_at' => $now]);

            $slabs = [
                [
                    'policy_id' => null,
                    'jurisdiction_code' => 'MP',
                    'monthly_gross_min' => 0.00,
                    'monthly_gross_max' => 18750.00,
                    'monthly_tax_amount' => 0.00,
                    'balancing_month_amount' => null,
                    'balancing_month' => null,
                    'effective_from' => '2020-04-01',
                    'effective_to' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'policy_id' => null,
                    'jurisdiction_code' => 'MP',
                    'monthly_gross_min' => 18750.01,
                    'monthly_gross_max' => 25000.00,
                    'monthly_tax_amount' => 125.00,
                    'balancing_month_amount' => 125.00,
                    'balancing_month' => 3,
                    'effective_from' => '2020-04-01',
                    'effective_to' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'policy_id' => null,
                    'jurisdiction_code' => 'MP',
                    'monthly_gross_min' => 25000.01,
                    'monthly_gross_max' => 33333.33,
                    'monthly_tax_amount' => 166.00,
                    'balancing_month_amount' => 174.00,
                    'balancing_month' => 3,
                    'effective_from' => '2020-04-01',
                    'effective_to' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'policy_id' => null,
                    'jurisdiction_code' => 'MP',
                    'monthly_gross_min' => 33333.34,
                    'monthly_gross_max' => null,
                    'monthly_tax_amount' => 208.00,
                    'balancing_month_amount' => 212.00,
                    'balancing_month' => 3,
                    'effective_from' => '2020-04-01',
                    'effective_to' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            DB::table('enterprise_professional_tax_slabs')->insert($slabs);
        }

        // 3. Seed employee statutory settings for active employees
        if (Schema::hasTable('enterprise_employee_statutory_settings') && Schema::hasTable('employees_new')) {
            $employeeIds = DB::table('employees_new')->where('employment_status', 'active')->pluck('id');
            foreach ($employeeIds as $empId) {
                DB::table('enterprise_employee_statutory_settings')->updateOrInsert(
                    ['employee_id' => $empId],
                    [
                        'effective_from' => '2020-04-01',
                        'effective_to' => null,
                        'professional_tax_applicable' => true,
                        'jurisdiction_code' => 'MP',
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enterprise_professional_tax_slabs')) {
            DB::table('enterprise_professional_tax_slabs')->where('jurisdiction_code', 'MP')->delete();
        }

        if (Schema::hasTable('enterprise_payroll_policies')) {
            DB::table('enterprise_payroll_policies')->update([
                'professional_tax_enabled' => false,
                'updated_at' => now(),
            ]);
        }
    }
};

