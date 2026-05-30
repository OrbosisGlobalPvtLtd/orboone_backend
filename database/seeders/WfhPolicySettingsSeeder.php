<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WfhPolicySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $keys = [
            'wfh_enabled' => ['1', 'boolean'],
            'wfh_monthly_limit' => ['2', 'number'],
            'wfh_requires_manager_approval' => ['1', 'boolean'],
            'wfh_requires_hr_approval' => ['0', 'boolean'],
            'wfh_allow_on_weekoff' => ['1', 'boolean'],
            'wfh_allow_on_holiday' => ['1', 'boolean'],
            'wfh_internet_issue_to_lwp' => ['1', 'boolean'],
            'wfh_electricity_issue_to_lwp' => ['1', 'boolean'],
            'wfh_requires_reason' => ['1', 'boolean'],
            'wfh_requires_work_report' => ['1', 'boolean'],
        ];

        foreach ($keys as $key => [$value, $type]) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'wfh_policy',
                    'type' => $type,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}

