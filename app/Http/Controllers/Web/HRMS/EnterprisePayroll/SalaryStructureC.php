<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureHistoryM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryStructureC extends Controller
{
    use HrmsCrudPage;

    public function index()
    {
        $rows = $this->employeeJoinedQuery('enterprise_salary_structures')
            ->orderByDesc('enterprise_salary_structures.effective_from')
            ->get();

        return view('hrms.enterprise-payroll.salary-structures.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'rows' => $rows,
            'employees' => $this->employeeOptions(),
            'canManage' => $this->userHasPermission('enterprise_salary_structure.manage'),
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
            'basic_annual' => ['nullable', 'numeric', 'min:0'],
            'basic_monthly' => ['nullable', 'numeric', 'min:0'],
            'hra_annual' => ['nullable', 'numeric', 'min:0'],
            'hra_monthly' => ['nullable', 'numeric', 'min:0'],
            'special_allowance_annual' => ['nullable', 'numeric', 'min:0'],
            'special_allowance_monthly' => ['nullable', 'numeric', 'min:0'],
            'professional_tax_monthly' => ['nullable', 'numeric', 'min:0'],
            'tds_annual' => ['nullable', 'numeric', 'min:0'],
            'tds_monthly' => ['nullable', 'numeric', 'min:0'],
            'other_deduction_monthly' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data['monthly_ctc'] = $data['monthly_ctc'] ?: round(((float) $data['annual_ctc']) / 12, 2);
        foreach (['basic', 'hra', 'special_allowance', 'tds'] as $component) {
            $annualKey = "{$component}_annual";
            $monthlyKey = "{$component}_monthly";
            $data[$annualKey] = (float) ($data[$annualKey] ?? 0);
            $data[$monthlyKey] = (float) ($data[$monthlyKey] ?? ($data[$annualKey] / 12));
        }

        $data['professional_tax_monthly'] = (float) ($data['professional_tax_monthly'] ?? 0);
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
