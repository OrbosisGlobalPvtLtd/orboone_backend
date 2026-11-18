<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureHistoryM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollPolicyS;
use App\Services\HRMS\EnterprisePayroll\EnterpriseSalaryComponentRulesS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryStructureC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private EnterpriseSalaryComponentRulesS $componentRules,
        private EnterprisePayrollPolicyS $policyService
    ) {
    }

    public function index(Request $request)
    {
        $defaultPolicy = $this->policyService->getActivePolicy();
        $componentRulePreview = $this->componentRules->breakdown(10000, $defaultPolicy->id ? (int) $defaultPolicy->id : null)['percentages'];
        $query = $this->employeeJoinedQuery('enterprise_salary_structures');

        if ($request->filled('employee_id')) {
            $query->where('enterprise_salary_structures.employee_id', $request->input('employee_id'));
        }

        if ($request->filled('stage')) {
            $query->where('enterprise_salary_structures.stage', $request->input('stage'));
        }

        if ($request->filled('status')) {
            $query->where('enterprise_salary_structures.status', $request->input('status'));
        }

        $rows = $query->orderByDesc('enterprise_salary_structures.effective_from')
            ->get();

        return view('hrms.enterprise-payroll.salary-structures.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'rows' => $rows,
            'employees' => $this->employeeOptions(),
            'canManage' => $this->userHasPermission('enterprise_salary_structure.manage'),
            'componentRulePreview' => $componentRulePreview,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            if (($data['status'] ?? 'active') === 'active') {
                $this->closePreviousActive((int) $data['employee_id'], $data['effective_from']);
            }

            $structure = EnterpriseSalaryStructureM::create($data + [
                'created_by_user_id' => $this->actorId(),
                'approved_by_user_id' => $this->actorId(),
                'approved_at' => Carbon::now('Asia/Kolkata'),
            ]);

            EnterpriseSalaryStructureHistoryM::create([
                'salary_structure_id' => $structure->id,
                'employee_id' => $structure->employee_id,
                'old_values' => null,
                'new_values' => $structure->toArray(),
                'revision_reason' => 'Enterprise salary structure created.',
                'changed_by_user_id' => $this->actorId(),
            ]);
        });

        return back()->with('success', 'Salary structure saved successfully.');
    }

    public function update(Request $request, int $salaryStructure)
    {
        $structure = EnterpriseSalaryStructureM::findOrFail($salaryStructure);
        $data = $this->validated($request);

        DB::transaction(function () use ($structure, $data) {
            $old = $structure->toArray();

            if (($data['status'] ?? 'active') === 'active') {
                $this->closePreviousActive((int) $data['employee_id'], $data['effective_from'], $structure->id);
            }

            $structure->update($data);

            EnterpriseSalaryStructureHistoryM::create([
                'salary_structure_id' => $structure->id,
                'employee_id' => $structure->employee_id,
                'old_values' => $old,
                'new_values' => $structure->fresh()->toArray(),
                'revision_reason' => $data['remarks'] ?? 'Enterprise salary structure revised.',
                'changed_by_user_id' => $this->actorId(),
            ]);
        });

        return back()->with('success', 'Salary structure updated successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees_new,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'annual_ctc' => ['required', 'numeric', 'min:0'],
            'monthly_ctc' => ['nullable', 'numeric', 'min:0'],
            'other_deduction_monthly' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data['monthly_ctc'] = round((float) ($data['monthly_ctc'] ?: ((float) $data['annual_ctc'] / 12)), 2);
        $data['annual_ctc'] = round($data['monthly_ctc'] * 12, 2);
        $employee = EmployeeM::query()->findOrFail((int) $data['employee_id']);
        $policy = $this->policyService->getActivePolicy($employee->company_id ?? null);
        $components = $this->componentRules->breakdown(
            (float) $data['monthly_ctc'],
            $policy->id ? (int) $policy->id : null,
            $data['effective_from']
        );
        foreach (['basic', 'hra', 'special_allowance'] as $component) {
            $annualKey = "{$component}_annual";
            $monthlyKey = "{$component}_monthly";
            $data[$monthlyKey] = $components[$component];
            $data[$annualKey] = round($components[$component] * 12, 2);
        }

        $data['professional_tax_monthly'] = 0.0;
        $data['tds_annual'] = 0.0;
        $data['tds_monthly'] = 0.0;
        $data['component_rules_snapshot'] = $components['percentages'];
        $data['other_deduction_monthly'] = (float) ($data['other_deduction_monthly'] ?? 0);

        return $data;
    }

    private function closePreviousActive(int $employeeId, string $effectiveFrom, ?int $exceptId = null): void
    {
        $closeDate = Carbon::parse($effectiveFrom)->subDay()->toDateString();

        EnterpriseSalaryStructureM::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->update([
                'status' => 'inactive',
                'effective_to' => $closeDate,
            ]);
    }
}
