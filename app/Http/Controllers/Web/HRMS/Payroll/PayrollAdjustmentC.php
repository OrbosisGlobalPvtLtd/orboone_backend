<?php

namespace App\Http\Controllers\Web\HRMS\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Payroll\PayrollAdjustmentM;
use Illuminate\Http\Request;

class PayrollAdjustmentC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.adjustments.manage'), 403);

        $query = $this->employeeJoinedQuery('payroll_adjustments');
        $this->applyCommonFilters($query, $request, [
            'filterMap' => [
                'employee_id' => 'payroll_adjustments.employee_id',
                'month' => 'payroll_adjustments.month',
                'year' => 'payroll_adjustments.year',
                'type' => 'payroll_adjustments.type',
            ],
        ]);

        return view('hrms.payroll.adjustments.index', [
            'accesses' => $this->accesses(),
            'active' => 'payroll',
            'pageTitle' => 'Payroll Adjustments',
            'pageSubtitle' => 'Manage approved bonus, incentive, TDS and deduction entries used by payroll generation.',
            'rows' => $query->orderByDesc('payroll_adjustments.year')->orderByDesc('payroll_adjustments.month')->paginate(50),
            'columns' => [
                ['key' => 'employee_display_name', 'label' => 'Employee'],
                ['key' => 'month', 'label' => 'Month'],
                ['key' => 'year', 'label' => 'Year'],
                ['key' => 'type', 'label' => 'Type'],
                ['key' => 'amount', 'label' => 'Amount'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ['key' => 'processed_at', 'label' => 'Processed At', 'type' => 'datetime'],
            ],
            'filters' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray()],
                ['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => array_combine(range(1, 12), range(1, 12))],
                ['name' => 'year', 'label' => 'Year', 'type' => 'number'],
                ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['bonus' => 'Bonus', 'incentive' => 'Incentive', 'tds' => 'TDS', 'deduction' => 'Other Deduction']],
            ],
            'summaryCards' => [
                ['label' => 'Approved', 'value' => PayrollAdjustmentM::where('status', 'approved')->count()],
                ['label' => 'Bonus', 'value' => PayrollAdjustmentM::where('type', 'bonus')->sum('amount')],
                ['label' => 'Incentives', 'value' => PayrollAdjustmentM::where('type', 'incentive')->sum('amount')],
                ['label' => 'Deductions', 'value' => PayrollAdjustmentM::whereIn('type', ['tds', 'deduction'])->sum('amount')],
            ],
            'canCreate' => true,
            'canEdit' => true,
            'storeRoute' => 'hrms.payroll.adjustments.store',
            'updateRoute' => 'hrms.payroll.adjustments.update',
            'formFields' => $this->formFields(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.adjustments.manage'), 403);

        $data = $this->validated($request);
        $data['status'] = 'approved';
        $data['created_by'] = auth()->id();
        $data['approved_by'] = auth()->id();
        $data['approved_at'] = now();

        PayrollAdjustmentM::create($data);

        return back()->with('success', 'Payroll adjustment saved.');
    }

    public function update(Request $request, $id)
    {
        abort_unless($this->userHasPermission('payroll.adjustments.manage'), 403);

        $adjustment = PayrollAdjustmentM::findOrFail($id);
        abort_if($adjustment->payroll_id || $adjustment->processed_at, 422, 'Processed payroll adjustments cannot be edited.');

        $data = $this->validated($request);
        $data['status'] = 'approved';
        $data['approved_by'] = auth()->id();
        $data['approved_at'] = now();

        $adjustment->update($data);

        return back()->with('success', 'Payroll adjustment updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => 'required|integer|exists:employees_new,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
            'type' => 'required|in:bonus,incentive,tds,deduction',
            'amount' => 'required|numeric|min:0',
            'title' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:2000',
        ]);
    }

    private function formFields(): array
    {
        return [
            ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray(), 'col' => 6],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['bonus' => 'Bonus', 'incentive' => 'Incentive', 'tds' => 'TDS', 'deduction' => 'Other Deduction'], 'col' => 6],
            ['name' => 'month', 'label' => 'Month', 'type' => 'number', 'default' => now()->month, 'col' => 4],
            ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'default' => now()->year, 'col' => 4],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'text', 'col' => 4],
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'col' => 6],
            ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 6],
        ];
    }
}
