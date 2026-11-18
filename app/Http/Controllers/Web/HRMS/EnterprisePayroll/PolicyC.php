<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollPolicyM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollPolicyS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PolicyC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private EnterprisePayrollPolicyS $policyService)
    {
    }

    public function index()
    {
        $policy = $this->policyService->getActivePolicy();
        $workingDays = $this->sampleWorkingDays($policy);
        $preview = $this->samplePreview($policy, $workingDays);

        $ptSlabs = DB::table('enterprise_professional_tax_slabs')
            ->where('is_active', true)
            ->orderBy('monthly_gross_min')
            ->get();

        return view('hrms.enterprise-payroll.policies.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'policy' => $policy,
            'canUpdate' => $this->userHasPermission('enterprise_payroll.policy.update'),
            'preview' => $preview,
            'ptSlabs' => $ptSlabs,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'salary_day_basis' => 'required|in:working_days,calendar_days,fixed_30_days,custom_fixed_days',
            'working_day_mode' => 'required|in:include_all_days,exclude_sundays,exclude_weekoffs,exclude_holidays,exclude_weekoffs_and_holidays',
            'custom_fixed_days' => 'nullable|integer|min:1|max:31',
            'salary_day_base' => 'nullable|integer|min:1|max:31',
            'proration_method' => 'nullable|string|max:40',

            'professional_tax_enabled' => 'nullable|boolean',
            'pf_enabled' => 'nullable|boolean',
            'pf_percentage' => 'required|numeric|min:0|max:100',
            'pf_wage_ceiling' => 'nullable|numeric|min:0',
            'pf_wage_basis' => 'required|in:basic,gross',
            'esi_enabled' => 'nullable|boolean',
            'esi_percentage' => 'required|numeric|min:0|max:100',
            'esi_wage_ceiling' => 'nullable|numeric|min:0',
            'esi_wage_basis' => 'required|in:basic,gross',
            'tds_enabled' => 'nullable|boolean',
            'allow_negative_salary' => 'nullable|boolean',
            'payroll_lock_after_generation' => 'nullable|boolean',

            'half_day_payable_ratio' => 'required|numeric|min:0|max:1',
            'absent_payable_ratio' => 'required|numeric|min:0|max:1',
            'lwp_payable_ratio' => 'required|numeric|min:0|max:1',
            'paid_leave_payable_ratio' => 'required|numeric|min:0|max:1',
            'weekoff_payable_ratio' => 'required|numeric|min:0|max:1',
            'holiday_payable_ratio' => 'required|numeric|min:0|max:1',
            'include_weekoff_in_payable' => 'nullable|boolean',
            'include_holiday_in_payable' => 'nullable|boolean',

            'salary_credit_start_day' => 'required|integer|min:1|max:31',
            'salary_credit_end_day' => 'required|integer|min:1|max:31',
            'future_salary_credit_start_day' => 'nullable|integer|min:1|max:31',
            'future_salary_credit_end_day' => 'nullable|integer|min:1|max:31',
        ]);

        if (($validated['salary_day_basis'] ?? null) === 'custom_fixed_days' && empty($validated['custom_fixed_days'])) {
            return back()->withErrors(['custom_fixed_days' => 'Custom fixed days is required when salary day basis is custom fixed days.'])->withInput();
        }

        $policy = $this->policyService->getActivePolicy();
        $policy->fill([
            'salary_day_basis' => $validated['salary_day_basis'],
            'working_day_mode' => $validated['working_day_mode'],
            'custom_fixed_days' => $validated['salary_day_basis'] === 'custom_fixed_days' ? $validated['custom_fixed_days'] : null,
            'salary_day_base' => (int) ($validated['salary_day_base'] ?? 26),
            'proration_method' => $validated['proration_method'] ?? 'calendar_26',
            'professional_tax_enabled' => $request->boolean('professional_tax_enabled'),
            'pf_enabled' => $request->boolean('pf_enabled'),
            'pf_percentage' => $validated['pf_percentage'],
            'pf_wage_ceiling' => $validated['pf_wage_ceiling'] ?? null,
            'pf_wage_basis' => $validated['pf_wage_basis'],
            'esi_enabled' => $request->boolean('esi_enabled'),
            'esi_percentage' => $validated['esi_percentage'],
            'esi_wage_ceiling' => $validated['esi_wage_ceiling'] ?? null,
            'esi_wage_basis' => $validated['esi_wage_basis'],
            'tds_enabled' => $request->boolean('tds_enabled'),
            'allow_negative_salary' => $request->boolean('allow_negative_salary'),
            'payroll_lock_after_generation' => $request->boolean('payroll_lock_after_generation'),
            'include_weekoff_in_payable' => $request->boolean('include_weekoff_in_payable'),
            'include_holiday_in_payable' => $request->boolean('include_holiday_in_payable'),
            'half_day_payable_ratio' => $validated['half_day_payable_ratio'],
            'absent_payable_ratio' => $validated['absent_payable_ratio'],
            'lwp_payable_ratio' => $validated['lwp_payable_ratio'],
            'paid_leave_payable_ratio' => $validated['paid_leave_payable_ratio'],
            'weekoff_payable_ratio' => $validated['weekoff_payable_ratio'],
            'holiday_payable_ratio' => $validated['holiday_payable_ratio'],
            'salary_credit_start_day' => $validated['salary_credit_start_day'],
            'salary_credit_end_day' => $validated['salary_credit_end_day'],
            'future_salary_credit_start_day' => $validated['future_salary_credit_start_day'] ?? $policy->future_salary_credit_start_day ?? $validated['salary_credit_start_day'],
            'future_salary_credit_end_day' => $validated['future_salary_credit_end_day'] ?? $policy->future_salary_credit_end_day ?? $validated['salary_credit_end_day'],
            'updated_by' => $this->actorId(),
        ]);
        $policy->save();

        return redirect()->route('enterprise-payroll.policies.index')->with('success', 'Payroll policy updated successfully.');
    }

    public function updatePtSlabs(Request $request)
    {
        $validated = $request->validate([
            'slabs' => 'required|array|min:1',
            'slabs.*.id' => 'required|integer|exists:enterprise_professional_tax_slabs,id',
            'slabs.*.monthly_tax_amount' => 'required|numeric|min:0',
            'slabs.*.balancing_month_amount' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['slabs'] as $row) {
            DB::table('enterprise_professional_tax_slabs')
                ->where('id', $row['id'])
                ->update([
                    'monthly_tax_amount' => $row['monthly_tax_amount'],
                    'balancing_month_amount' => $row['balancing_month_amount'] ?? null,
                    'updated_at' => now(),
                ]);
        }

        return redirect()->route('enterprise-payroll.policies.index')->with('success', 'Professional tax slabs updated successfully.');
    }

    private function sampleWorkingDays(EnterprisePayrollPolicyM $policy): float
    {
        $basis = (string) ($policy->salary_day_basis ?? 'working_days');
        if ($basis === 'calendar_days') {
            return 30.0;
        }
        if ($basis === 'fixed_30_days') {
            return 30.0;
        }
        if ($basis === 'custom_fixed_days') {
            return max(1.0, (float) ($policy->custom_fixed_days ?: 26));
        }
        return 26.0;
    }

    private function samplePreview(EnterprisePayrollPolicyM $policy, float $workingDays): array
    {
        $salary = 25000.0;
        $absentDays = 2.0;
        $calendarDays = 30.0;
        $salaryDayBase = max(1, (int) ($policy->salary_day_base ?? 26));
        $payableCalendarDays = max(0, $calendarDays - $absentDays);
        $perDay = round($salary / $salaryDayBase, 2);
        $attendanceDeduction = round($salary * (1 - $payableCalendarDays / $calendarDays), 2);
        $earnedGross = max(0, $salary - $attendanceDeduction);

        $pt = 0.0;
        if ($policy->professional_tax_enabled) {
            $slab = DB::table('enterprise_professional_tax_slabs')
                ->where('is_active', true)
                ->where('monthly_gross_min', '<=', $earnedGross)
                ->where(function ($q) use ($earnedGross) {
                    $q->whereNull('monthly_gross_max')
                        ->orWhere('monthly_gross_max', '>=', $earnedGross);
                })
                ->orderByDesc('monthly_gross_min')
                ->first();

            if ($slab) {
                $pt = (float) $slab->monthly_tax_amount;
            }
        }
        $net = round($salary - $attendanceDeduction - $pt, 2);

        return [
            'salary' => $salary,
            'working_days' => $workingDays,
            'absent_days' => $absentDays,
            'professional_tax' => $pt,
            'per_day_salary' => $perDay,
            'attendance_deduction' => $attendanceDeduction,
            'net_salary' => $net,
        ];
    }
}

