<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollPolicyM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollPolicyS;
use Illuminate\Http\Request;

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

        return view('hrms.enterprise-payroll.policies.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'policy' => $policy,
            'canUpdate' => $this->userHasPermission('enterprise_payroll.policy.update'),
            'preview' => $preview,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'salary_day_basis' => 'required|in:working_days,calendar_days,fixed_30_days,custom_fixed_days',
            'working_day_mode' => 'required|in:include_all_days,exclude_sundays,exclude_weekoffs,exclude_holidays,exclude_weekoffs_and_holidays',
            'custom_fixed_days' => 'nullable|integer|min:1|max:31',

            'professional_tax_enabled' => 'nullable|boolean',
            'professional_tax_amount' => 'required|numeric|min:0',
            'pf_enabled' => 'nullable|boolean',
            'pf_percentage' => 'required|numeric|min:0|max:100',
            'esi_enabled' => 'nullable|boolean',
            'esi_percentage' => 'required|numeric|min:0|max:100',
            'tds_enabled' => 'nullable|boolean',
            'tds_source' => 'required|in:policy,salary_structure',
            'tds_percentage' => 'required|numeric|min:0|max:100',
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
            'future_salary_credit_start_day' => 'required|integer|min:1|max:31',
            'future_salary_credit_end_day' => 'required|integer|min:1|max:31',
        ]);

        if (($validated['salary_day_basis'] ?? null) === 'custom_fixed_days' && empty($validated['custom_fixed_days'])) {
            return back()->withErrors(['custom_fixed_days' => 'Custom fixed days is required when salary day basis is custom fixed days.'])->withInput();
        }

        $policy = $this->policyService->getActivePolicy();
        $policy->fill([
            'salary_day_basis' => $validated['salary_day_basis'],
            'working_day_mode' => $validated['working_day_mode'],
            'custom_fixed_days' => $validated['salary_day_basis'] === 'custom_fixed_days' ? $validated['custom_fixed_days'] : null,
            'professional_tax_enabled' => $request->boolean('professional_tax_enabled'),
            'professional_tax_amount' => $validated['professional_tax_amount'],
            'pf_enabled' => $request->boolean('pf_enabled'),
            'pf_percentage' => $validated['pf_percentage'],
            'esi_enabled' => $request->boolean('esi_enabled'),
            'esi_percentage' => $validated['esi_percentage'],
            'tds_enabled' => $request->boolean('tds_enabled'),
            'tds_source' => $validated['tds_source'],
            'tds_percentage' => $validated['tds_percentage'],
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
            'future_salary_credit_start_day' => $validated['future_salary_credit_start_day'],
            'future_salary_credit_end_day' => $validated['future_salary_credit_end_day'],
            'updated_by' => $this->actorId(),
        ]);
        $policy->save();

        return redirect()->route('enterprise-payroll.policies.index')->with('success', 'Payroll policy updated successfully.');
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
        $salary = 10000.0;
        $absentDays = 2.0;
        $pt = (bool) $policy->professional_tax_enabled ? (float) ($policy->professional_tax_amount ?? 0) : 0.0;
        $perDay = $workingDays > 0 ? round($salary / $workingDays, 2) : 0.0;
        $attendanceDeduction = round($perDay * $absentDays * (1 - (float) ($policy->absent_payable_ratio ?? 0)), 2);
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

