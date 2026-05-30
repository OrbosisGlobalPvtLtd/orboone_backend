<?php

namespace App\Services\HRMS\Attendance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WfhPolicyService
{
    private const GROUP = 'wfh_policy';

    public function ensureDefaults(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach ($this->defaults() as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'group' => self::GROUP,
                    'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string'),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function all(): array
    {
        $defaults = $this->defaults();
        if (! Schema::hasTable('settings')) {
            return $defaults;
        }

        $rows = DB::table('settings')
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->toArray();

        return [
            'wfh_enabled' => $this->toBool($rows['wfh_enabled'] ?? $defaults['wfh_enabled']),
            'wfh_monthly_limit' => (int) ($rows['wfh_monthly_limit'] ?? $defaults['wfh_monthly_limit']),
            'wfh_requires_manager_approval' => $this->toBool($rows['wfh_requires_manager_approval'] ?? $defaults['wfh_requires_manager_approval']),
            'wfh_requires_hr_approval' => $this->toBool($rows['wfh_requires_hr_approval'] ?? $defaults['wfh_requires_hr_approval']),
            'wfh_allow_on_weekoff' => $this->toBool($rows['wfh_allow_on_weekoff'] ?? $defaults['wfh_allow_on_weekoff']),
            'wfh_allow_on_holiday' => $this->toBool($rows['wfh_allow_on_holiday'] ?? $defaults['wfh_allow_on_holiday']),
            'wfh_internet_issue_to_lwp' => $this->toBool($rows['wfh_internet_issue_to_lwp'] ?? $defaults['wfh_internet_issue_to_lwp']),
            'wfh_electricity_issue_to_lwp' => $this->toBool($rows['wfh_electricity_issue_to_lwp'] ?? $defaults['wfh_electricity_issue_to_lwp']),
            'wfh_requires_reason' => $this->toBool($rows['wfh_requires_reason'] ?? $defaults['wfh_requires_reason']),
            'wfh_requires_work_report' => $this->toBool($rows['wfh_requires_work_report'] ?? $defaults['wfh_requires_work_report']),
        ];
    }

    private function defaults(): array
    {
        return [
            'wfh_enabled' => true,
            'wfh_monthly_limit' => 2,
            'wfh_requires_manager_approval' => true,
            'wfh_requires_hr_approval' => false,
            'wfh_allow_on_weekoff' => true,
            'wfh_allow_on_holiday' => true,
            'wfh_internet_issue_to_lwp' => true,
            'wfh_electricity_issue_to_lwp' => true,
            'wfh_requires_reason' => true,
            'wfh_requires_work_report' => true,
        ];
    }

    private function toBool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

