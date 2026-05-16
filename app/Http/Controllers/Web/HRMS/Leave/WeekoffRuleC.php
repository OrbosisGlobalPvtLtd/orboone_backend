<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeekoffRuleC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = DB::table('weekoff_rules');
        $this->applyCommonFilters($query, $request, ['filterMap' => ['weekday' => 'weekday', 'week_number' => 'week_number', 'active' => 'is_active']]);

        return view('hrms.leave.weekoff_rules.index', [
            'accesses' => $this->accesses(), 'active' => 'leave_management', 'pageTitle' => 'Weekoff Rules',
            'pageSubtitle' => 'Configure working and off days from DB, including 1st/3rd working Saturdays and 2nd/4th off Saturdays.',
            'rows' => $query->orderBy('weekday')->orderBy('week_number')->paginate(50),
            'columns' => [['key' => 'weekday', 'label' => 'Weekday'], ['key' => 'week_number', 'label' => 'Week No.'], ['key' => 'is_working', 'label' => 'Working', 'type' => 'badge'], ['key' => 'is_off', 'label' => 'Off', 'type' => 'badge'], ['key' => 'effective_from', 'label' => 'From', 'type' => 'date'], ['key' => 'effective_to', 'label' => 'To', 'type' => 'date'], ['key' => 'is_active', 'label' => 'Active', 'type' => 'badge']],
            'filters' => [['name' => 'weekday', 'label' => 'Weekday', 'type' => 'select', 'options' => [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday']], ['name' => 'week_number', 'label' => 'Week Number', 'type' => 'select', 'options' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5]], ['name' => 'active', 'label' => 'Active', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive']]],
            'formFields' => [['name' => 'weekday', 'label' => 'Weekday', 'type' => 'select', 'options' => [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday']], ['name' => 'week_number', 'label' => 'Week Number', 'type' => 'select', 'options' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5]], ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date'], ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date'], ['name' => 'is_working', 'label' => 'Working Day', 'type' => 'checkbox'], ['name' => 'is_off', 'label' => 'Week Off', 'type' => 'checkbox'], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1]],
            'canCreate' => true, 'canEdit' => true, 'storeRoute' => 'hrms.weekoff_rules.store', 'updateRoute' => 'hrms.weekoff_rules.update',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::table('weekoff_rules')->updateOrInsert(['weekday' => $data['weekday'], 'week_number' => $data['week_number'] ?? null], array_merge($data, ['is_working' => $request->boolean('is_working'), 'is_off' => $request->boolean('is_off'), 'is_active' => $request->boolean('is_active'), 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]));
        return back()->with('success', 'Weekoff rule saved.');
    }

    public function update(Request $request, $id)
    {
        DB::table('weekoff_rules')->where('id', $id)->update(array_merge($this->validated($request), ['is_working' => $request->boolean('is_working'), 'is_off' => $request->boolean('is_off'), 'is_active' => $request->boolean('is_active'), 'updated_at' => now()]));
        return back()->with('success', 'Weekoff rule updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['weekday' => 'required|integer|min:1|max:7', 'week_number' => 'nullable|integer|min:1|max:5', 'effective_from' => 'nullable|date', 'effective_to' => 'nullable|date']);
    }
}
