<?php

namespace App\Services\HRMS\EnterprisePayroll;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class EnterpriseSalaryComponentRulesS
{
    public function breakdown(float $monthlyGross, ?int $policyId = null, ?string $effectiveDate = null): array
    {
        $effectiveDate = $effectiveDate ?: now()->toDateString();
        $rules = collect();

        if (Schema::hasTable('enterprise_salary_component_rules')) {
            $rules = DB::table('enterprise_salary_component_rules')
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('policy_id')->orWhere('policy_id', $policyId))
                ->where(fn ($q) => $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $effectiveDate))
                ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $effectiveDate))
                ->orderByRaw('CASE WHEN policy_id IS NULL THEN 0 ELSE 1 END')
                ->orderBy('effective_from')->orderBy('sort_order')->orderBy('id')->get()->keyBy('component_code');
        }

        $basicPct = (float) ($rules['basic']->percentage ?? 50);
        $hraPct = (float) ($rules['hra']->percentage ?? 20);
        $specialPct = (float) ($rules['special_allowance']->percentage ?? max(0, 100 - $basicPct - $hraPct));
        if ($basicPct < 0 || $hraPct < 0 || $specialPct < 0 || abs($basicPct + $hraPct + $specialPct - 100) > 0.01) {
            throw new RuntimeException('Effective salary component rules must be non-negative and total 100%.');
        }

        $basic = round($monthlyGross * $basicPct / 100, 2);
        $hra = round($monthlyGross * $hraPct / 100, 2);
        $special = round($monthlyGross - $basic - $hra, 2);

        return [
            'basic' => $basic,
            'hra' => $hra,
            'special_allowance' => $special,
            'percentages' => ['basic' => $basicPct, 'hra' => $hraPct, 'special_allowance' => $specialPct],
        ];
    }
}
