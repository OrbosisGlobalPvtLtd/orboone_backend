<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollPolicyM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Calculates employee-side statutory deductions from effective DB rules. */
class EnterpriseStatutoryCalculatorS
{
    public function calculate(
        EmployeeM $employee,
        EnterprisePayrollPolicyM $policy,
        int $month,
        int $year,
        float $monthlyWages,
        float $earnedGrossWages,
        float $earnedBasicWages,
        float $earnedTaxableWages
    ): array {
        $settings = $this->employeeSettings($employee->id, $month, $year);

        $pt = 0.0;
        $isPtApplicable = isset($settings['professional_tax_applicable'])
            ? (bool) $settings['professional_tax_applicable']
            : true;
        if ($policy->professional_tax_enabled && $isPtApplicable) {
            $pt = $this->professionalTax($policy, $settings, $earnedGrossWages, $month, $year);
        }

        $pf = 0.0;
        if ($policy->pf_enabled && ($settings['pf_applicable'] ?? false) === true) {
            $ceiling = (float) ($settings['pf_wage_ceiling_override'] ?? $policy->pf_wage_ceiling ?? 0);
            $pfWageBasis = (string) ($policy->pf_wage_basis ?? 'basic');
            $pfBase = $pfWageBasis === 'gross' ? $earnedGrossWages : $earnedBasicWages;
            $pfWages = $ceiling > 0 ? min($pfBase, $ceiling) : $pfBase;
            $pf = round($pfWages * (float) $policy->pf_percentage / 100, 2);
        }

        $esi = 0.0;
        if ($policy->esi_enabled && ($settings['esi_applicable'] ?? false) === true) {
            $ceiling = (float) ($settings['esi_wage_ceiling_override'] ?? $policy->esi_wage_ceiling ?? 0);
            // No configured wage ceiling means the employee's ESI eligibility is incomplete.
            $esiWageBasis = (string) ($policy->esi_wage_basis ?? 'gross');
            $esiBase = $esiWageBasis === 'basic' ? $earnedBasicWages : $earnedGrossWages;
            if ($ceiling > 0 && $esiBase <= $ceiling) {
                $esi = round($esiBase * (float) $policy->esi_percentage / 100, 2);
            }
        }

        $tdsResult = ['amount' => 0.0, 'snapshot' => ['eligible' => false, 'reason' => 'employee_not_marked_applicable']];
        if ($policy->tds_enabled && ($settings['tds_applicable'] ?? false) === true) {
            $tdsResult = $this->incomeTaxWithholding($employee->id, $year, $month, $earnedTaxableWages, $monthlyWages);
        }

        return [
            'professional_tax' => $pt,
            'pf' => $pf,
            'esi' => $esi,
            'tds' => (float) $tdsResult['amount'],
            'settings' => $settings,
            'tds_snapshot' => $tdsResult['snapshot'],
        ];
    }

    private function employeeSettings(int $employeeId, int $month, int $year): array
    {
        if (! Schema::hasTable('enterprise_employee_statutory_settings')) {
            return [];
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        $row = DB::table('enterprise_employee_statutory_settings')
            ->where('employee_id', $employeeId)
            ->whereDate('effective_from', '<=', $end)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $start))
            ->orderByDesc('effective_from')->orderByDesc('id')->first();

        if (! $row) {
            return [];
        }

        return [
            'setting_id' => $row->id,
            'professional_tax_applicable' => (bool) $row->professional_tax_applicable,
            'pf_applicable' => (bool) $row->pf_applicable,
            'esi_applicable' => (bool) $row->esi_applicable,
            'tds_applicable' => (bool) $row->tds_applicable,
            'jurisdiction_code' => $row->jurisdiction_code,
            'pf_wage_ceiling_override' => $row->pf_wage_ceiling_override ?? null,
            'esi_wage_ceiling_override' => $row->esi_wage_ceiling_override ?? null,
        ];
    }

    private function professionalTax(EnterprisePayrollPolicyM $policy, array $settings, float $monthlyWages, int $month, int $year): float
    {
        $jurisdiction = trim((string) ($settings['jurisdiction_code'] ?? ''));
        if ($jurisdiction === '') {
            $jurisdiction = 'MP';
        }
        if (! Schema::hasTable('enterprise_professional_tax_slabs')) {
            return 0.0;
        }

        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        $slab = DB::table('enterprise_professional_tax_slabs')
            ->where('jurisdiction_code', $jurisdiction)->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('policy_id')->orWhere('policy_id', $policy->id))
            ->whereDate('effective_from', '<=', $periodEnd)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $periodEnd))
            ->where('monthly_gross_min', '<=', $monthlyWages)
            ->where(fn ($q) => $q->whereNull('monthly_gross_max')->orWhere('monthly_gross_max', '>=', $monthlyWages))
            ->orderByDesc('policy_id')->orderBy('monthly_gross_min')->first();

        if (! $slab) {
            return 0.0;
        }

        return round($month === (int) $slab->balancing_month && $slab->balancing_month_amount !== null
            ? (float) $slab->balancing_month_amount
            : (float) $slab->monthly_tax_amount, 2);
    }

    private function incomeTaxWithholding(int $employeeId, int $year, int $month, float $currentTaxableWages, float $monthlyWages): array
    {
        if (! Schema::hasTable('enterprise_employee_tax_profiles') || ! Schema::hasTable('enterprise_income_tax_rules') || ! Schema::hasTable('enterprise_income_tax_slabs')) {
            return ['amount' => 0.0, 'snapshot' => ['eligible' => false, 'reason' => 'tax_configuration_missing']];
        }

        $financialYearStart = $month >= 4 ? $year : $year - 1;
        $profile = DB::table('enterprise_employee_tax_profiles')
            ->where('employee_id', $employeeId)
            ->where('financial_year_start', $financialYearStart)
            ->whereIn('residency_status', ['resident', 'non_resident', 'not_ordinary_resident'])
            ->whereNotNull('verified_at')->first();
        if (! $profile) {
            return ['amount' => 0.0, 'snapshot' => ['eligible' => false, 'reason' => 'verified_tax_profile_missing']];
        }

        $regime = (string) $profile->tax_regime;
        $ageCategory = (string) ($profile->age_category ?: 'all');
        $rule = DB::table('enterprise_income_tax_rules')
            ->where('financial_year_start', $financialYearStart)
            ->where('tax_regime', $regime)->where('is_complete', true)->where('is_active', true)->first();
        if (! $rule) {
            return ['amount' => 0.0, 'snapshot' => ['eligible' => false, 'reason' => 'complete_tax_year_rules_missing']];
        }

        $slabsQuery = DB::table('enterprise_income_tax_slabs')
            ->where('financial_year_start', $financialYearStart)->where('tax_regime', $regime)
            ->where('is_active', true);
        $specificSlabs = (clone $slabsQuery)->where('age_category', $ageCategory)->orderBy('income_from')->orderBy('id')->get();
        $slabs = $specificSlabs->isNotEmpty()
            ? $specificSlabs
            : $slabsQuery->where('age_category', 'all')->orderBy('income_from')->orderBy('id')->get();
        if ($slabs->isEmpty() || (float) $slabs->first()->income_from > 0 || $slabs->last()->income_to !== null) {
            return ['amount' => 0.0, 'snapshot' => ['eligible' => false, 'reason' => 'tax_brackets_incomplete']];
        }
        $previousUpper = 0.0;
        foreach ($slabs as $slab) {
            if (abs((float) $slab->income_from - $previousUpper) >= 0.005) {
                return ['amount' => 0.0, 'snapshot' => ['eligible' => false, 'reason' => 'tax_brackets_have_gap_or_overlap']];
            }
            $previousUpper = $slab->income_to === null ? INF : (float) $slab->income_to;
        }

        $fyStartMonth = 4;
        $elapsedMonths = $month >= 4 ? $month - $fyStartMonth : $month + 8;
        $futureMonths = max(0, 11 - $elapsedMonths);
        $installmentsRemaining = max(1, 12 - $elapsedMonths);
        $priorStartYear = $financialYearStart;

        $priorTaxable = $this->priorTaxableEarnings($employeeId, $priorStartYear, $year, $month);
        $priorTds = $this->priorTaxWithheld($employeeId, $priorStartYear, $year, $month);
        $priorTaxable += $this->legacyPriorTaxableEarnings($employeeId, $priorStartYear, $year, $month);
        $priorTds += $this->legacyPriorTaxWithheld($employeeId, $priorStartYear, $year, $month);
        $projectedIncome = $priorTaxable + $currentTaxableWages + ($monthlyWages * $futureMonths)
            + (float) $profile->previous_employer_income_annual
            + (float) $profile->other_taxable_income_annual;
        $taxableIncome = max(0, $projectedIncome - (float) $rule->standard_deduction - (float) $profile->deductions_annual - (float) $profile->exemptions_annual);

        $baseTax = 0.0;
        foreach ($slabs as $slab) {
            $lower = (float) $slab->income_from;
            $upper = $slab->income_to === null ? $taxableIncome : min($taxableIncome, (float) $slab->income_to);
            if ($upper > $lower) {
                $baseTax += ($upper - $lower) * (float) $slab->tax_rate / 100;
            }
        }

        if ($taxableIncome <= (float) $rule->rebate_income_limit) {
            $baseTax = max(0, $baseTax - (float) $rule->rebate_max_amount);
        }

        $surcharge = $this->surcharge($financialYearStart, $regime, $ageCategory, $taxableIncome, $baseTax, $rule);
        if ($surcharge === null) {
            return ['amount' => 0.0, 'snapshot' => ['eligible' => false, 'reason' => 'surcharge_configuration_incomplete']];
        }
        $annualTax = round(($baseTax + $surcharge) * (1 + (float) $rule->cess_percentage / 100), 2);
        $taxOutstanding = max(0, $annualTax - $priorTds - (float) $profile->previous_employer_tds_annual);

        return [
            'amount' => round($taxOutstanding / $installmentsRemaining, 2),
            'snapshot' => [
                'eligible' => true,
                'financial_year_start' => $financialYearStart,
                'tax_regime' => $regime,
                'profile_id' => $profile->id,
                'taxable_annual_income' => round($taxableIncome, 2),
                'projected_annual_tax' => $annualTax,
                'tax_withheld_before_this_month' => round($priorTds + (float) $profile->previous_employer_tds_annual, 2),
                'installments_remaining' => $installmentsRemaining,
                'tax_rule_id' => $rule->id,
                'slab_ids' => $slabs->pluck('id')->all(),
            ],
        ];
    }

    private function priorTaxableEarnings(int $employeeId, int $financialYearStart, int $payrollYear, int $month): float
    {
        if (! Schema::hasTable('enterprise_payrolls')) {
            return 0.0;
        }

        $periodStart = Carbon::create($financialYearStart, 4, 1)->toDateString();
        $periodEnd = Carbon::create($payrollYear, $month, 1)->subDay()->toDateString();
        if ($periodEnd < $periodStart || ! Schema::hasColumn('enterprise_payrolls', 'attendance_deduction')) {
            return 0.0;
        }

        return (float) DB::table('enterprise_payrolls')
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['generated', 'approved', 'locked', 'paid'])
            ->whereRaw("STR_TO_DATE(CONCAT(year, '-', LPAD(month, 2, '0'), '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$periodStart, $periodEnd])
            ->selectRaw('COALESCE(SUM(gross_salary - reimbursement_amount - attendance_deduction), 0) as taxable')
            ->value('taxable');
    }

    private function priorTaxWithheld(int $employeeId, int $financialYearStart, int $payrollYear, int $month): float
    {
        if (! Schema::hasTable('enterprise_payrolls')) {
            return 0.0;
        }

        $periodStart = Carbon::create($financialYearStart, 4, 1)->toDateString();
        $periodEnd = Carbon::create($payrollYear, $month, 1)->subDay()->toDateString();
        if ($periodEnd < $periodStart) {
            return 0.0;
        }

        return (float) DB::table('enterprise_payrolls')
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['generated', 'approved', 'locked', 'paid'])
            ->whereRaw("STR_TO_DATE(CONCAT(year, '-', LPAD(month, 2, '0'), '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$periodStart, $periodEnd])
            ->sum('tds');
    }

    private function legacyPriorTaxableEarnings(int $employeeId, int $financialYearStart, int $payrollYear, int $month): float
    {
        if (! $this->legacyPayrollQueryAvailable()) {
            return 0.0;
        }
        $periodStart = Carbon::create($financialYearStart, 4, 1)->toDateString();
        $periodEnd = Carbon::create($payrollYear, $month, 1)->subDay()->toDateString();
        if ($periodEnd < $periodStart) {
            return 0.0;
        }

        $query = DB::table('payrolls as old_payroll')
            ->join('employees as old_employee', 'old_employee.id', '=', 'old_payroll.employee_id')
            ->join('employees_new as employee', 'employee.user_id', '=', 'old_employee.user_id')
            ->where('employee.id', $employeeId)
            ->whereIn('old_payroll.status', ['generated', 'approved', 'locked', 'paid', 'processed'])
            ->whereRaw("STR_TO_DATE(CONCAT(old_payroll.year, '-', LPAD(old_payroll.month, 2, '0'), '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$periodStart, $periodEnd]);

        if (Schema::hasTable('enterprise_payrolls')) {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')->from('enterprise_payrolls as current_payroll')
                    ->whereColumn('current_payroll.employee_id', 'employee.id')
                    ->whereColumn('current_payroll.month', 'old_payroll.month')
                    ->whereColumn('current_payroll.year', 'old_payroll.year');
            });
        }

        $grossColumn = Schema::hasColumn('payrolls', 'gross_salary') ? 'old_payroll.gross_salary' : '0';
        $reimbursementColumn = Schema::hasColumn('payrolls', 'reimbursements') ? 'COALESCE(old_payroll.reimbursements, 0)' : '0';
        return (float) $query->selectRaw("COALESCE(SUM({$grossColumn} - {$reimbursementColumn}), 0) as taxable")->value('taxable');
    }

    private function legacyPriorTaxWithheld(int $employeeId, int $financialYearStart, int $payrollYear, int $month): float
    {
        if (! $this->legacyPayrollQueryAvailable() || ! Schema::hasColumn('payrolls', 'tds')) {
            return 0.0;
        }
        $periodStart = Carbon::create($financialYearStart, 4, 1)->toDateString();
        $periodEnd = Carbon::create($payrollYear, $month, 1)->subDay()->toDateString();
        if ($periodEnd < $periodStart) {
            return 0.0;
        }

        $query = DB::table('payrolls as old_payroll')
            ->join('employees as old_employee', 'old_employee.id', '=', 'old_payroll.employee_id')
            ->join('employees_new as employee', 'employee.user_id', '=', 'old_employee.user_id')
            ->where('employee.id', $employeeId)
            ->whereIn('old_payroll.status', ['generated', 'approved', 'locked', 'paid', 'processed'])
            ->whereRaw("STR_TO_DATE(CONCAT(old_payroll.year, '-', LPAD(old_payroll.month, 2, '0'), '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$periodStart, $periodEnd]);

        if (Schema::hasTable('enterprise_payrolls')) {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')->from('enterprise_payrolls as current_payroll')
                    ->whereColumn('current_payroll.employee_id', 'employee.id')
                    ->whereColumn('current_payroll.month', 'old_payroll.month')
                    ->whereColumn('current_payroll.year', 'old_payroll.year');
            });
        }

        return (float) $query->sum('old_payroll.tds');
    }

    private function legacyPayrollQueryAvailable(): bool
    {
        return Schema::hasTable('payrolls')
            && Schema::hasTable('employees')
            && Schema::hasTable('employees_new')
            && Schema::hasColumn('employees', 'user_id');
    }

    private function surcharge(int $financialYearStart, string $regime, string $ageCategory, float $taxableIncome, float $baseTax, object $rule): ?float
    {
        if (! Schema::hasTable('enterprise_income_tax_surcharges')) {
            return $taxableIncome >= (float) ($rule->surcharge_applicable_from ?? INF) ? null : 0.0;
        }

        $tiers = DB::table('enterprise_income_tax_surcharges')
            ->where('financial_year_start', $financialYearStart)->where('tax_regime', $regime)
            ->where('is_active', true)->orderBy('income_threshold')->get();
        $tier = $tiers->filter(fn ($item) => $taxableIncome >= (float) $item->income_threshold)->last();
        if (! $tier) {
            if ($taxableIncome >= (float) ($rule->surcharge_applicable_from ?? INF)) {
                return null;
            }
            return 0.0;
        }

        $amount = $baseTax * (float) $tier->surcharge_rate / 100;
        $previousTier = $tiers->filter(fn ($item) => (float) $item->income_threshold < (float) $tier->income_threshold)->last();
        if ($tier->marginal_relief && $previousTier) {
            $taxAtThreshold = $this->taxAtThreshold($financialYearStart, $regime, $ageCategory, (float) $tier->income_threshold);
            $amount = min($amount, max(0, $taxAtThreshold + $taxableIncome - (float) $tier->income_threshold - $baseTax));
        }

        return max(0, $amount);
    }

    private function taxAtThreshold(int $financialYearStart, string $regime, string $ageCategory, float $income): float
    {
        $slabsQuery = DB::table('enterprise_income_tax_slabs')->where('financial_year_start', $financialYearStart)
            ->where('tax_regime', $regime)->where('is_active', true);
        $slabs = (clone $slabsQuery)->where('age_category', $ageCategory)->get();
        if ($slabs->isEmpty()) {
            $slabs = $slabsQuery->where('age_category', 'all')->get();
        }
        $tax = 0.0;
        foreach ($slabs as $slab) {
            $upper = $slab->income_to === null ? $income : min($income, (float) $slab->income_to);
            if ($upper > (float) $slab->income_from) {
                $tax += ($upper - (float) $slab->income_from) * (float) $slab->tax_rate / 100;
            }
        }

        return $tax;
    }
}
