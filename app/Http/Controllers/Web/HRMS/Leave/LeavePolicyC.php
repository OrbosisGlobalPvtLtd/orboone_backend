<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Leave\StoreLeavePolicyRequest;
use App\Models\Core\AccessM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Models\HRMS\Policy\PolicyChangeLogM;
use Illuminate\Support\Facades\Auth;

class LeavePolicyC extends Controller
{
    public function index()
    {
        $policies = LeavePolicyM::orderByDesc('is_active')->orderBy('policy_name')->get();
        $accesses = $this->accesses();

        return view('hrms.leave.policies.index', compact('policies', 'accesses'))->with('active', 'leave_management');
    }

    public function store(StoreLeavePolicyRequest $request)
    {
        $policy = LeavePolicyM::create($this->payload($request));
        PolicyChangeLogM::create([
            'policy_type' => 'leave',
            'policy_id' => $policy->id,
            'changed_by_user_id' => Auth::id(),
            'new_values' => $policy->toArray(),
            'remarks' => 'Leave policy created.',
        ]);

        return back()->with('success', 'Leave policy saved successfully.');
    }

    public function update(StoreLeavePolicyRequest $request, $id)
    {
        $policy = LeavePolicyM::findOrFail($id);
        $old = $policy->toArray();
        $policy->update($this->payload($request));

        PolicyChangeLogM::create([
            'policy_type' => 'leave',
            'policy_id' => $policy->id,
            'changed_by_user_id' => Auth::id(),
            'old_values' => $old,
            'new_values' => $policy->fresh()->toArray(),
            'remarks' => 'Leave policy updated.',
        ]);

        return back()->with('success', 'Leave policy updated successfully.');
    }

    private function payload(StoreLeavePolicyRequest $request): array
    {
        return array_merge($request->validated(), [
            'allow_monthly_balance_accumulation' => $request->boolean('allow_monthly_balance_accumulation'),
            'carry_forward_enabled' => $request->boolean('carry_forward_enabled'),
            'sandwich_enabled' => $request->boolean('sandwich_enabled'),
            'weekoff_included_in_sandwich' => $request->boolean('weekoff_included_in_sandwich'),
            'holiday_included_in_sandwich' => $request->boolean('holiday_included_in_sandwich'),
            'nov_dec_half_usage_enabled' => $request->boolean('nov_dec_half_usage_enabled'),
            'comp_off_expiry_same_month' => $request->boolean('comp_off_expiry_same_month'),
            'allow_negative_balance' => $request->boolean('allow_negative_balance'),
            'is_active' => $request->boolean('is_active', true),
        ]);
    }

    private function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;
        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }
}
