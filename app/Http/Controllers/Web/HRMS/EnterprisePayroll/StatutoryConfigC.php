<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollPolicyM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StatutoryConfigC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $year = (int) $request->input('financial_year_start', now()->month >= 4 ? now()->year : now()->year - 1);
        $employees = $this->employeeOptions(false);
        $employeeIds = $employees->pluck('id');
        $profiles = DB::table('enterprise_employee_tax_profiles')
            ->where('financial_year_start', $year)->whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');
        $statutorySettings = DB::table('enterprise_employee_statutory_settings')
            ->whereIn('employee_id', $employeeIds)->orderByDesc('effective_from')->orderByDesc('id')->get()->unique('employee_id')->keyBy('employee_id');
        $policies = EnterprisePayrollPolicyM::query()->orderBy('policy_name')->get();
        $componentRules = DB::table('enterprise_salary_component_rules')->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', now()->toDateString()))
            ->orderBy('policy_id')->orderBy('effective_from')->orderBy('sort_order')->get();
        $taxRules = DB::table('enterprise_income_tax_rules')->where('financial_year_start', $year)->orderBy('tax_regime')->get();
        $taxSlabs = DB::table('enterprise_income_tax_slabs')->where('financial_year_start', $year)->where('is_active', true)->orderBy('tax_regime')->orderBy('age_category')->orderBy('income_from')->get();
        $surcharges = DB::table('enterprise_income_tax_surcharges')->where('financial_year_start', $year)->where('is_active', true)->orderBy('tax_regime')->orderBy('income_threshold')->get();
        $ptSlabs = DB::table('enterprise_professional_tax_slabs')->where('is_active', true)->orderBy('jurisdiction_code')->orderBy('effective_from')->orderBy('monthly_gross_min')->get();

        return view('hrms.enterprise-payroll.statutory.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'employees' => $employees,
            'profiles' => $profiles,
            'statutorySettings' => $statutorySettings,
            'policies' => $policies,
            'componentRules' => $componentRules,
            'financialYearStart' => $year,
            'taxRules' => $taxRules,
            'taxSlabs' => $taxSlabs,
            'surcharges' => $surcharges,
            'ptSlabs' => $ptSlabs,
        ]);
    }

    public function saveSalaryComponentRules(Request $request)
    {
        $data = $request->validate([
            'policy_id' => 'nullable|integer|exists:enterprise_payroll_policies,id',
            'effective_from' => 'required|date',
            'basic_percentage' => 'required|numeric|min:0|max:100',
            'hra_percentage' => 'required|numeric|min:0|max:100',
            'special_allowance_percentage' => 'required|numeric|min:0|max:100',
        ]);
        $total = (float) $data['basic_percentage'] + (float) $data['hra_percentage'] + (float) $data['special_allowance_percentage'];
        if (abs($total - 100) > 0.01) {
            throw ValidationException::withMessages(['basic_percentage' => 'Basic + HRA + Special Allowance percentages must total exactly 100%.']);
        }

        DB::transaction(function () use ($data) {
            $effectiveFrom = $data['effective_from'];
            $previousDate = \Carbon\Carbon::parse($effectiveFrom)->subDay()->toDateString();
            $query = DB::table('enterprise_salary_component_rules')->whereIn('component_code', ['basic', 'hra', 'special_allowance'])
                ->where('is_active', true)->where(fn ($q) => $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $effectiveFrom))
                ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $effectiveFrom));
            if (isset($data['policy_id'])) {
                $query->where('policy_id', $data['policy_id']);
            } else {
                $query->whereNull('policy_id');
            }
            $query->update(['effective_to' => $previousDate, 'updated_at' => now()]);

            foreach ([
                ['basic', $data['basic_percentage'], 1],
                ['hra', $data['hra_percentage'], 2],
                ['special_allowance', $data['special_allowance_percentage'], 3],
            ] as [$code, $percentage, $order]) {
                DB::table('enterprise_salary_component_rules')->insert([
                    'policy_id' => $data['policy_id'] ?? null,
                    'component_code' => $code,
                    'percentage' => $percentage,
                    'sort_order' => $order,
                    'effective_from' => $effectiveFrom,
                    'effective_to' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Effective salary component percentages saved. New salary structures will use this rule.');
    }

    public function saveEmployeeEligibility(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees_new,id',
            'effective_from' => 'required|date',
            'professional_tax_applicable' => 'nullable|boolean',
            'pf_applicable' => 'nullable|boolean',
            'esi_applicable' => 'nullable|boolean',
            'tds_applicable' => 'nullable|boolean',
            'jurisdiction_code' => 'nullable|string|max:20',
            'pf_wage_ceiling_override' => 'nullable|numeric|min:0',
            'esi_wage_ceiling_override' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $key = ['employee_id' => $data['employee_id'], 'effective_from' => $data['effective_from']];
        $settingExists = DB::table('enterprise_employee_statutory_settings')->where($key)->exists();
        DB::table('enterprise_employee_statutory_settings')->updateOrInsert(
            $key,
            [
                'effective_to' => null,
                'professional_tax_applicable' => $request->boolean('professional_tax_applicable'),
                'pf_applicable' => $request->boolean('pf_applicable'),
                'esi_applicable' => $request->boolean('esi_applicable'),
                'tds_applicable' => $request->boolean('tds_applicable'),
                'jurisdiction_code' => $data['jurisdiction_code'] ?? null,
                'pf_wage_ceiling_override' => $data['pf_wage_ceiling_override'] ?? null,
                'esi_wage_ceiling_override' => $data['esi_wage_ceiling_override'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'updated_by_user_id' => $this->actorId(),
                ...($settingExists ? [] : ['created_at' => now()]),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Employee statutory applicability saved.');
    }

    public function saveTaxProfile(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees_new,id',
            'financial_year_start' => 'required|integer|min:2000|max:2100',
            'tax_regime' => 'required|string|max:40',
            'residency_status' => 'required|in:resident,non_resident,not_ordinary_resident',
            'age_category' => 'required|in:all,senior,super_senior',
            'previous_employer_income_annual' => 'nullable|numeric|min:0',
            'previous_employer_tds_annual' => 'nullable|numeric|min:0',
            'other_taxable_income_annual' => 'nullable|numeric|min:0',
            'deductions_annual' => 'nullable|numeric|min:0',
            'exemptions_annual' => 'nullable|numeric|min:0',
            'verified' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $verified = $request->boolean('verified');
        $key = ['employee_id' => $data['employee_id'], 'financial_year_start' => $data['financial_year_start']];
        $profileExists = DB::table('enterprise_employee_tax_profiles')->where($key)->exists();
        DB::table('enterprise_employee_tax_profiles')->updateOrInsert(
            $key,
            [
                'tax_regime' => $data['tax_regime'],
                'residency_status' => $data['residency_status'],
                'age_category' => $data['age_category'],
                'previous_employer_income_annual' => $data['previous_employer_income_annual'] ?? 0,
                'previous_employer_tds_annual' => $data['previous_employer_tds_annual'] ?? 0,
                'other_taxable_income_annual' => $data['other_taxable_income_annual'] ?? 0,
                'deductions_annual' => $data['deductions_annual'] ?? 0,
                'exemptions_annual' => $data['exemptions_annual'] ?? 0,
                'verified_at' => $verified ? now() : null,
                'verified_by_user_id' => $verified ? $this->actorId() : null,
                'remarks' => $data['remarks'] ?? null,
                ...($profileExists ? [] : ['created_at' => now()]),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', $verified ? 'Verified tax profile saved.' : 'Tax profile saved as unverified; payroll will not withhold TDS from it.');
    }

    public function saveIncomeTaxRules(Request $request)
    {
        $data = $request->validate([
            'financial_year_start' => 'required|integer|min:2000|max:2100',
            'tax_regime' => 'required|string|max:40',
            'standard_deduction' => 'required|numeric|min:0',
            'rebate_income_limit' => 'required|numeric|min:0',
            'rebate_max_amount' => 'required|numeric|min:0',
            'cess_percentage' => 'required|numeric|min:0|max:100',
            'surcharge_applicable_from' => 'nullable|numeric|min:0',
            'slabs_json' => 'required|string',
            'surcharges_json' => 'nullable|string',
            'is_complete' => 'nullable|boolean',
        ]);

        $slabs = $this->decodeRows($data['slabs_json'], 'slabs_json');
        $surcharges = $this->decodeRows($data['surcharges_json'] ?? '[]', 'surcharges_json');
        $this->validateIncomeTaxSlabs($slabs, $request->boolean('is_complete'));
        foreach ($surcharges as $row) {
            if (! isset($row['income_threshold'], $row['surcharge_rate']) || ! is_numeric($row['income_threshold']) || ! is_numeric($row['surcharge_rate'])) {
                throw ValidationException::withMessages(['surcharges_json' => 'Each surcharge row needs numeric income_threshold and surcharge_rate.']);
            }
            if ((float) $row['income_threshold'] < 0 || (float) $row['surcharge_rate'] < 0 || (float) $row['surcharge_rate'] > 100) {
                throw ValidationException::withMessages(['surcharges_json' => 'Surcharge thresholds must be non-negative and rates must be between 0 and 100.']);
            }
        }
        if ($request->boolean('is_complete') && ! empty($data['surcharge_applicable_from']) && $surcharges === []) {
            throw ValidationException::withMessages(['surcharges_json' => 'Add surcharge tiers or leave surcharge_applicable_from empty before marking this regime complete.']);
        }

        DB::transaction(function () use ($data, $request, $slabs, $surcharges) {
            $now = now();
            $ruleId = DB::table('enterprise_income_tax_rules')->where('financial_year_start', $data['financial_year_start'])
                ->where('tax_regime', $data['tax_regime'])->value('id');
            $payload = [
                'standard_deduction' => $data['standard_deduction'],
                'rebate_income_limit' => $data['rebate_income_limit'],
                'rebate_max_amount' => $data['rebate_max_amount'],
                'cess_percentage' => $data['cess_percentage'],
                'surcharge_applicable_from' => $data['surcharge_applicable_from'] ?? null,
                'is_complete' => $request->boolean('is_complete'),
                'is_active' => true,
                'updated_at' => $now,
            ];
            if ($ruleId) {
                DB::table('enterprise_income_tax_rules')->where('id', $ruleId)->update($payload);
            } else {
                $ruleId = DB::table('enterprise_income_tax_rules')->insertGetId($payload + [
                    'financial_year_start' => $data['financial_year_start'], 'tax_regime' => $data['tax_regime'], 'created_at' => $now,
                ]);
            }

            DB::table('enterprise_income_tax_slabs')->where('financial_year_start', $data['financial_year_start'])
                ->where('tax_regime', $data['tax_regime'])->update(['is_active' => false, 'updated_at' => $now]);
            foreach ($slabs as $slab) {
                DB::table('enterprise_income_tax_slabs')->insert([
                    'financial_year_start' => $data['financial_year_start'],
                    'tax_regime' => $data['tax_regime'],
                    'age_category' => $slab['age_category'] ?? 'all',
                    'income_from' => $slab['income_from'],
                    'income_to' => $slab['income_to'] ?? null,
                    'tax_rate' => $slab['tax_rate'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('enterprise_income_tax_surcharges')->where('financial_year_start', $data['financial_year_start'])
                ->where('tax_regime', $data['tax_regime'])->update(['is_active' => false, 'updated_at' => $now]);
            foreach ($surcharges as $surcharge) {
                DB::table('enterprise_income_tax_surcharges')->insert([
                    'financial_year_start' => $data['financial_year_start'],
                    'tax_regime' => $data['tax_regime'],
                    'income_threshold' => $surcharge['income_threshold'],
                    'surcharge_rate' => $surcharge['surcharge_rate'],
                    'marginal_relief' => (bool) ($surcharge['marginal_relief'] ?? true),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return back()->with('success', 'Effective income tax rules saved.');
    }

    public function saveProfessionalTaxSlabs(Request $request)
    {
        $data = $request->validate([
            'policy_id' => 'nullable|integer|exists:enterprise_payroll_policies,id',
            'jurisdiction_code' => 'required|string|max:20',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'slabs_json' => 'required|string',
        ]);
        $slabs = $this->decodeRows($data['slabs_json'], 'slabs_json');
        if ($slabs === []) {
            throw ValidationException::withMessages(['slabs_json' => 'At least one professional-tax slab is required.']);
        }
        usort($slabs, fn ($a, $b) => (float) ($a['monthly_gross_min'] ?? 0) <=> (float) ($b['monthly_gross_min'] ?? 0));
        $previousMax = null;
        foreach ($slabs as $row) {
            if (! isset($row['monthly_gross_min'], $row['monthly_tax_amount'])
                || ! is_numeric($row['monthly_gross_min']) || ! is_numeric($row['monthly_tax_amount'])) {
                throw ValidationException::withMessages(['slabs_json' => 'Each PT row needs numeric monthly_gross_min and monthly_tax_amount.']);
            }
            if (isset($row['monthly_gross_max']) && $row['monthly_gross_max'] !== null && ! is_numeric($row['monthly_gross_max'])) {
                throw ValidationException::withMessages(['slabs_json' => 'monthly_gross_max must be numeric or null.']);
            }
            $minimum = (float) $row['monthly_gross_min'];
            $maximum = isset($row['monthly_gross_max']) ? (float) $row['monthly_gross_max'] : INF;
            if ($minimum < 0 || (float) $row['monthly_tax_amount'] < 0 || $maximum < $minimum) {
                throw ValidationException::withMessages(['slabs_json' => 'PT ranges and tax amounts must be non-negative, and max must not be below min.']);
            }
            if ($previousMax !== null && $minimum <= $previousMax) {
                throw ValidationException::withMessages(['slabs_json' => 'Professional-tax ranges must not overlap.']);
            }
            $previousMax = $maximum;
        }

        DB::transaction(function () use ($data, $slabs) {
            $now = now();
            DB::table('enterprise_professional_tax_slabs')->where('jurisdiction_code', $data['jurisdiction_code'])
                ->where('policy_id', $data['policy_id'] ?? null)->update(['is_active' => false, 'updated_at' => $now]);
            foreach ($slabs as $slab) {
                DB::table('enterprise_professional_tax_slabs')->insert([
                    'policy_id' => $data['policy_id'] ?? null,
                    'jurisdiction_code' => $data['jurisdiction_code'],
                    'monthly_gross_min' => $slab['monthly_gross_min'],
                    'monthly_gross_max' => $slab['monthly_gross_max'] ?? null,
                    'monthly_tax_amount' => $slab['monthly_tax_amount'],
                    'balancing_month_amount' => $slab['balancing_month_amount'] ?? null,
                    'balancing_month' => $slab['balancing_month'] ?? null,
                    'effective_from' => $data['effective_from'],
                    'effective_to' => $data['effective_to'] ?? null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return back()->with('success', 'Professional tax slabs saved for the selected jurisdiction.');
    }

    private function decodeRows(string $json, string $field): array
    {
        $rows = json_decode($json, true);
        if (! is_array($rows) || ! array_is_list($rows)) {
            throw ValidationException::withMessages([$field => 'Enter a JSON array of rule rows.']);
        }
        return $rows;
    }

    private function validateIncomeTaxSlabs(array $slabs, bool $complete): void
    {
        if ($slabs === []) {
            throw ValidationException::withMessages(['slabs_json' => 'At least one slab is required.']);
        }
        $groups = [];
        foreach ($slabs as $row) {
            if (! isset($row['income_from'], $row['tax_rate']) || ! is_numeric($row['income_from']) || ! is_numeric($row['tax_rate'])) {
                throw ValidationException::withMessages(['slabs_json' => 'Each slab needs numeric income_from and tax_rate.']);
            }
            if ((float) $row['income_from'] < 0 || (float) $row['tax_rate'] < 0 || (float) $row['tax_rate'] > 100) {
                throw ValidationException::withMessages(['slabs_json' => 'Slab income must be non-negative and tax_rate must be between 0 and 100.']);
            }
            if (isset($row['income_to']) && $row['income_to'] !== null && ! is_numeric($row['income_to'])) {
                throw ValidationException::withMessages(['slabs_json' => 'income_to must be numeric or null for the final open-ended slab.']);
            }
            $age = (string) ($row['age_category'] ?? 'all');
            if (! in_array($age, ['all', 'senior', 'super_senior'], true)) {
                throw ValidationException::withMessages(['slabs_json' => 'Unsupported age_category in slab rows.']);
            }
            $groups[$age][] = $row;
        }

        foreach ($groups as $rows) {
            usort($rows, fn ($a, $b) => (float) $a['income_from'] <=> (float) $b['income_from']);
            if ($complete && (float) $rows[0]['income_from'] !== 0.0) {
                throw ValidationException::withMessages(['slabs_json' => 'Complete slabs must start from income_from 0.']);
            }
            $previous = 0.0;
            foreach ($rows as $index => $row) {
                if (abs((float) $row['income_from'] - $previous) >= 0.005) {
                    throw ValidationException::withMessages(['slabs_json' => 'Tax slabs must be contiguous and must not overlap.']);
                }
                $upper = $row['income_to'] ?? null;
                if ($upper === null) {
                    if ($index !== count($rows) - 1) {
                        throw ValidationException::withMessages(['slabs_json' => 'Only the final slab can have income_to null.']);
                    }
                    $previous = INF;
                } else {
                    if ((float) $upper <= (float) $row['income_from']) {
                        throw ValidationException::withMessages(['slabs_json' => 'income_to must exceed income_from.']);
                    }
                    $previous = (float) $upper;
                }
            }
            if ($complete && $previous !== INF) {
                throw ValidationException::withMessages(['slabs_json' => 'The final complete slab must have income_to null.']);
            }
        }
        if ($complete && ! array_key_exists('all', $groups)) {
            throw ValidationException::withMessages(['slabs_json' => 'Complete rules need an all-age slab group for employees without an age-based slab.']);
        }
    }
}
