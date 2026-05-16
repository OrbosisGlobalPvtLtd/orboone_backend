<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeavePolicyOverrideC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = $this->employeeJoinedQuery('leave_policy_employee_overrides')
            ->leftJoin('leave_policies', 'leave_policies.id', '=', 'leave_policy_employee_overrides.leave_policy_id')
            ->addSelect('leave_policies.policy_name');
        $this->applyCommonFilters($query, $request, ['filterMap' => ['employee_id' => 'leave_policy_employee_overrides.employee_id', 'policy_id' => 'leave_policy_employee_overrides.leave_policy_id', 'active' => 'leave_policy_employee_overrides.is_active']]);

        return view('hrms.leave.policy_overrides.index', $this->pageData($query->latest('leave_policy_employee_overrides.id')->paginate(50)));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::table('leave_policy_employee_overrides')->updateOrInsert(['employee_id' => $data['employee_id'], 'leave_policy_id' => $data['leave_policy_id'], 'effective_from' => $data['effective_from'] ?? null], array_merge($data, ['is_active' => $request->boolean('is_active'), 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]));
        return back()->with('success', 'Leave policy override saved.');
    }

    public function update(Request $request, $id)
    {
        DB::table('leave_policy_employee_overrides')->where('id', $id)->update(array_merge($this->validated($request), ['is_active' => $request->boolean('is_active'), 'updated_at' => now()]));
        return back()->with('success', 'Leave policy override updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['employee_id' => 'required|exists:employees_new,id', 'leave_policy_id' => 'required|exists:leave_policies,id', 'effective_from' => 'nullable|date', 'effective_to' => 'nullable|date', 'remarks' => 'nullable|string']);
    }

    private function pageData($rows): array
    {
        $employees = $this->employeeOptions()->pluck('display_name', 'id')->toArray();
        $policies = DB::table('leave_policies')->orderBy('policy_name')->pluck('policy_name', 'id')->toArray();
        return [
            'accesses' => $this->accesses(), 'active' => 'leave_management', 'pageTitle' => 'Leave Policy Overrides',
            'rows' => $rows,
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'policy_name', 'label' => 'Policy'], ['key' => 'effective_from', 'label' => 'From', 'type' => 'date'], ['key' => 'effective_to', 'label' => 'To', 'type' => 'date'], ['key' => 'is_active', 'label' => 'Active', 'type' => 'badge']],
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'policy_id', 'label' => 'Policy', 'type' => 'select', 'options' => $policies], ['name' => 'active', 'label' => 'Active', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]],
            'formFields' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'leave_policy_id', 'label' => 'Policy', 'type' => 'select', 'options' => $policies], ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date'], ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date'], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1], ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12]],
            'canCreate' => true, 'canEdit' => true, 'storeRoute' => 'hrms.leave.policy_overrides.store', 'updateRoute' => 'hrms.leave.policy_overrides.update',
        ];
    }
}
