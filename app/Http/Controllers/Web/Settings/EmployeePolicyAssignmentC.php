<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeePolicyAssignmentC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = $this->employeeJoinedQuery('employee_policy_assignments');
        $this->applyCommonFilters($query, $request, ['filterMap' => ['employee_id' => 'employee_policy_assignments.employee_id', 'policy_type' => 'employee_policy_assignments.policy_type', 'active' => 'employee_policy_assignments.is_active']]);
        return view('settings.employee_policy_assignments.index', $this->pageData($query->latest('employee_policy_assignments.id')->paginate(50)));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::table('employee_policy_assignments')->updateOrInsert(['employee_id' => $data['employee_id'], 'policy_type' => $data['policy_type'], 'policy_id' => $data['policy_id'], 'effective_from' => $data['effective_from'] ?? null], array_merge($data, ['assigned_by_user_id' => $this->actorId(), 'is_active' => $request->boolean('is_active'), 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]));
        return back()->with('success', 'Employee policy assignment saved.');
    }

    public function update(Request $request, $id)
    {
        DB::table('employee_policy_assignments')->where('id', $id)->update(array_merge($this->validated($request), ['is_active' => $request->boolean('is_active'), 'updated_at' => now()]));
        return back()->with('success', 'Employee policy assignment updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['employee_id' => 'required|exists:employees_new,id', 'policy_type' => 'required|string|max:80', 'policy_id' => 'required|integer', 'effective_from' => 'nullable|date', 'effective_to' => 'nullable|date', 'remarks' => 'nullable|string']);
    }

    private function pageData($rows): array
    {
        $employees = $this->employeeOptions()->pluck('display_name', 'id')->toArray();
        return [
            'accesses' => $this->accesses(), 'active' => 'settings', 'pageTitle' => 'Employee Policy Assignments',
            'rows' => $rows,
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'policy_type', 'label' => 'Type'], ['key' => 'policy_id', 'label' => 'Policy ID'], ['key' => 'effective_from', 'label' => 'From', 'type' => 'date'], ['key' => 'effective_to', 'label' => 'To', 'type' => 'date'], ['key' => 'is_active', 'label' => 'Active', 'type' => 'badge']],
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'policy_type', 'label' => 'Policy Type', 'type' => 'select', 'options' => ['leave' => 'Leave', 'attendance' => 'Attendance']], ['name' => 'active', 'label' => 'Active', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]],
            'formFields' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'policy_type', 'label' => 'Policy Type', 'type' => 'select', 'options' => ['leave' => 'Leave', 'attendance' => 'Attendance']], ['name' => 'policy_id', 'label' => 'Policy ID', 'type' => 'number'], ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date'], ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date'], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1], ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12]],
            'canCreate' => true, 'canEdit' => true, 'storeRoute' => 'hrms.employee_policy_assignments.store', 'updateRoute' => 'hrms.employee_policy_assignments.update',
        ];
    }
}
