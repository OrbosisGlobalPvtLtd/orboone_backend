<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendancePolicyOverrideC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = $this->employeeJoinedQuery('attendance_policy_employee_overrides')
            ->leftJoin('attendance_policy_rules', 'attendance_policy_rules.id', '=', 'attendance_policy_employee_overrides.attendance_policy_rule_id')
            ->addSelect('attendance_policy_rules.policy_name');
        $this->applyCommonFilters($query, $request, ['filterMap' => ['employee_id' => 'attendance_policy_employee_overrides.employee_id', 'policy_id' => 'attendance_policy_employee_overrides.attendance_policy_rule_id', 'active' => 'attendance_policy_employee_overrides.is_active']]);
        return view('hrms.attendance.policy_overrides.index', $this->pageData($query->latest('attendance_policy_employee_overrides.id')->paginate(50)));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::table('attendance_policy_employee_overrides')->updateOrInsert(['employee_id' => $data['employee_id'], 'attendance_policy_rule_id' => $data['attendance_policy_rule_id'], 'effective_from' => $data['effective_from'] ?? null], array_merge($data, ['is_active' => $request->boolean('is_active'), 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]));
        return back()->with('success', 'Attendance policy override saved.');
    }

    public function update(Request $request, $id)
    {
        DB::table('attendance_policy_employee_overrides')->where('id', $id)->update(array_merge($this->validated($request), ['is_active' => $request->boolean('is_active'), 'updated_at' => now()]));
        return back()->with('success', 'Attendance policy override updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['employee_id' => 'required|exists:employees_new,id', 'attendance_policy_rule_id' => 'required|exists:attendance_policy_rules,id', 'effective_from' => 'nullable|date', 'effective_to' => 'nullable|date', 'remarks' => 'nullable|string']);
    }

    private function pageData($rows): array
    {
        $employees = $this->employeeOptions()->pluck('display_name', 'id')->toArray();
        $policies = DB::table('attendance_policy_rules')->orderBy('policy_name')->pluck('policy_name', 'id')->toArray();
        return [
            'accesses' => $this->accesses(), 'active' => 'attendance', 'pageTitle' => 'Attendance Policy Overrides',
            'rows' => $rows,
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'policy_name', 'label' => 'Policy'], ['key' => 'effective_from', 'label' => 'From', 'type' => 'date'], ['key' => 'effective_to', 'label' => 'To', 'type' => 'date'], ['key' => 'is_active', 'label' => 'Active', 'type' => 'badge']],
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'policy_id', 'label' => 'Policy', 'type' => 'select', 'options' => $policies], ['name' => 'active', 'label' => 'Active', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]],
            'formFields' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'attendance_policy_rule_id', 'label' => 'Policy', 'type' => 'select', 'options' => $policies], ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date'], ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date'], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1], ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 12]],
            'canCreate' => true, 'canEdit' => true, 'storeRoute' => 'hrms.attendance.policy_overrides.store', 'updateRoute' => 'hrms.attendance.policy_overrides.update',
        ];
    }
}
