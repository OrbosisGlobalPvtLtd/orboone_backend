<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnterprisePayrollPolicySeeder extends Seeder
{
    public function run(): void
    {
        $payload = [
            'company_id' => null,
            'salary_day_basis' => 'working_days',
            'working_day_mode' => 'exclude_weekoffs',
            'custom_fixed_days' => null,
            'professional_tax_enabled' => true,
            'professional_tax_amount' => 200,
            'pf_enabled' => false,
            'pf_percentage' => 0,
            'esi_enabled' => false,
            'esi_percentage' => 0,
            'tds_enabled' => false,
            'tds_percentage' => 0,
            'tds_source' => 'policy',
            'allow_negative_salary' => false,
            'payroll_lock_after_generation' => false,
            'include_weekoff_in_payable' => true,
            'include_holiday_in_payable' => true,
            'half_day_payable_ratio' => 0.5,
            'absent_payable_ratio' => 0,
            'lwp_payable_ratio' => 0,
            'paid_leave_payable_ratio' => 1,
            'weekoff_payable_ratio' => 1,
            'holiday_payable_ratio' => 1,
            'salary_credit_start_day' => 7,
            'salary_credit_end_day' => 10,
            'future_salary_credit_start_day' => 5,
            'future_salary_credit_end_day' => 7,
            'is_active' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        DB::table('enterprise_payroll_policies')->updateOrInsert(
            ['policy_name' => 'Default Payroll Policy'],
            $this->columns('enterprise_payroll_policies', $payload)
        );
    }

    private function columns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }
}
