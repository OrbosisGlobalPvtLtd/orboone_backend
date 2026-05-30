<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollPolicyM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnterprisePayrollPolicyS
{
    public function getActivePolicy(?int $companyId = null): EnterprisePayrollPolicyM
    {
        $query = EnterprisePayrollPolicyM::query()->where('is_active', true);

        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })->orderByRaw('CASE WHEN company_id IS NULL THEN 1 ELSE 0 END');
        }

        return $query->orderByDesc('id')->first() ?: $this->fallbackPolicy();
    }

    public function fallbackPolicy(): EnterprisePayrollPolicyM
    {
        return new EnterprisePayrollPolicyM([
            'policy_name' => 'Default Payroll Policy',
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
        ]);
    }

    public function calendarDays(int $month, int $year): int
    {
        return Carbon::create($year, $month, 1)->daysInMonth;
    }

    public function policyWorkingDays(EnterprisePayrollPolicyM $policy, array $attendance, int $month, int $year): float
    {
        $calendarDays = $this->calendarDays($month, $year);
        $weekOff = (float) ($attendance['week_off_days'] ?? 0);
        $holiday = (float) ($attendance['holiday_days'] ?? 0);
        $sundayCount = Carbon::create($year, $month, 1)
            ->daysUntil(Carbon::create($year, $month, 1)->endOfMonth()->addDay())
            ->filter(fn (Carbon $day) => $day->isSunday())
            ->count();

        $basis = (string) ($policy->salary_day_basis ?? 'working_days');
        if ($basis === 'calendar_days') {
            return (float) $calendarDays;
        }
        if ($basis === 'fixed_30_days') {
            return 30.0;
        }
        if ($basis === 'custom_fixed_days') {
            $custom = (int) ($policy->custom_fixed_days ?? 0);
            return $custom > 0 ? (float) $custom : (float) $calendarDays;
        }

        $mode = (string) ($policy->working_day_mode ?? 'exclude_weekoffs');
        $workingDays = (float) $calendarDays;
        if ($mode === 'exclude_sundays') {
            $workingDays -= (float) $sundayCount;
        } elseif ($mode === 'exclude_weekoffs') {
            $workingDays -= $weekOff;
        } elseif ($mode === 'exclude_holidays') {
            $workingDays -= $holiday;
        } elseif ($mode === 'exclude_weekoffs_and_holidays') {
            $workingDays -= ($weekOff + $holiday);
        }

        return max(1.0, round($workingDays, 2));
    }

    public function perDaySalary(float $monthlyGross, float $policyWorkingDays): float
    {
        return $policyWorkingDays > 0 ? round($monthlyGross / $policyWorkingDays, 4) : 0.0;
    }

    public function deductionByRatio(float $perDay, float $days, float $ratio): float
    {
        return round($perDay * $days * max(0, 1 - $ratio), 2);
    }

    public function payableDays(EnterprisePayrollPolicyM $policy, array $attendance): float
    {
        $weekOffDays = (float) ($attendance['week_off_days'] ?? 0);
        $holidayDays = (float) ($attendance['holiday_days'] ?? 0);

        return round(
            ((float) ($attendance['present_days'] ?? 0))
            + ((float) ($attendance['paid_leave_days'] ?? 0) * (float) $policy->paid_leave_payable_ratio)
            + ((float) ($attendance['sick_leave_days'] ?? 0) * (float) $policy->paid_leave_payable_ratio)
            + ((float) ($attendance['comp_off_days'] ?? 0) * (float) $policy->paid_leave_payable_ratio)
            + ((float) ($attendance['half_days'] ?? 0) * (float) $policy->half_day_payable_ratio)
            + ((float) ($attendance['lwp_days'] ?? 0) * (float) $policy->lwp_payable_ratio)
            + ((float) ($attendance['absent_days'] ?? 0) * (float) $policy->absent_payable_ratio)
            + (($policy->include_weekoff_in_payable ? $weekOffDays : 0) * (float) $policy->weekoff_payable_ratio)
            + (($policy->include_holiday_in_payable ? $holidayDays : 0) * (float) $policy->holiday_payable_ratio),
            2
        );
    }

    public function snapshot(EnterprisePayrollPolicyM $policy, int $month, int $year, float $policyWorkingDays, float $perDaySalary): array
    {
        $tdsSource = $this->tdsSource($policy);

        return [
            'policy_name' => $policy->policy_name,
            'salary_day_basis' => $policy->salary_day_basis,
            'working_day_mode' => $policy->working_day_mode,
            'custom_fixed_days' => $policy->custom_fixed_days,
            'calendar_days' => $this->calendarDays($month, $year),
            'policy_working_days' => $policyWorkingDays,
            'per_day_salary' => $perDaySalary,
            'professional_tax_enabled' => (bool) $policy->professional_tax_enabled,
            'professional_tax_amount' => (float) $policy->professional_tax_amount,
            'pf_enabled' => (bool) $policy->pf_enabled,
            'pf_percentage' => (float) $policy->pf_percentage,
            'esi_enabled' => (bool) $policy->esi_enabled,
            'esi_percentage' => (float) $policy->esi_percentage,
            'tds_enabled' => (bool) $policy->tds_enabled,
            'tds_percentage' => (float) $policy->tds_percentage,
            'tds_source' => $tdsSource,
            'payable_ratios' => [
                'half_day_payable_ratio' => (float) $policy->half_day_payable_ratio,
                'absent_payable_ratio' => (float) $policy->absent_payable_ratio,
                'lwp_payable_ratio' => (float) $policy->lwp_payable_ratio,
                'paid_leave_payable_ratio' => (float) $policy->paid_leave_payable_ratio,
                'weekoff_payable_ratio' => (float) $policy->weekoff_payable_ratio,
                'holiday_payable_ratio' => (float) $policy->holiday_payable_ratio,
            ],
            'salary_credit_window' => [
                'current' => [
                    'start_day' => (int) $policy->salary_credit_start_day,
                    'end_day' => (int) $policy->salary_credit_end_day,
                ],
                'future_target' => [
                    'start_day' => (int) $policy->future_salary_credit_start_day,
                    'end_day' => (int) $policy->future_salary_credit_end_day,
                ],
            ],
        ];
    }

    public function tdsSource(EnterprisePayrollPolicyM $policy): string
    {
        $inline = (string) ($policy->tds_source ?? '');
        if (in_array($inline, ['policy', 'salary_structure'], true)) {
            return $inline;
        }

        if (Schema::hasTable('settings')) {
            $fromSettings = (string) DB::table('settings')
                ->whereIn('key', ['enterprise_payroll_tds_source', 'tds_source'])
                ->value('value');
            if (in_array($fromSettings, ['policy', 'salary_structure'], true)) {
                return $fromSettings;
            }
        }

        return 'policy';
    }
}
