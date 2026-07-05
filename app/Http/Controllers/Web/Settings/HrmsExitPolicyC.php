<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\HrmsExitPolicyM;
use Illuminate\Http\Request;

class HrmsExitPolicyC extends Controller
{
    public function index()
    {
        $this->authorizeSuperAdmin();

        $policies = HrmsExitPolicyM::query()
            ->orderByDesc('is_active')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        return view('settings.hrms-exit-policies', compact('policies'));
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $data = $this->validated($request);
        $data['created_by_user_id'] = auth()->id();
        $data['updated_by_user_id'] = auth()->id();

        HrmsExitPolicyM::query()->create($data);

        return back()->with('success', 'Exit policy created successfully.');
    }

    public function update(Request $request, HrmsExitPolicyM $policy)
    {
        $this->authorizeSuperAdmin();

        $data = $this->validated($request);
        $data['updated_by_user_id'] = auth()->id();

        $policy->update($data);

        return back()->with('success', 'Exit policy updated successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'applies_to' => ['required', 'in:all,internship,probation,permanent,contract'],
            'exit_type' => ['nullable', 'in:resignation,termination,retirement,contract_end,mutual_separation,layoff_redundancy,absconding,deceased,other,internship_exit,internship_completed'],
            'notice_period_days' => ['required', 'integer', 'min:0', 'max:365'],
            'fnf_processing_days' => ['required', 'integer', 'min:0', 'max:365'],
            'allow_waiver' => ['nullable', 'boolean'],
            'allow_buyout' => ['nullable', 'boolean'],
            'allow_immediate_exit' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'effective_from' => ['nullable', 'date'],
        ]) + [
            'allow_waiver' => $request->boolean('allow_waiver'),
            'allow_buyout' => $request->boolean('allow_buyout'),
            'allow_immediate_exit' => $request->boolean('allow_immediate_exit'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function authorizeSuperAdmin(): void
    {
        $user = auth()->user();
        abort_if(! $user, 401);
        abort_if(! method_exists($user, 'isSuperAdmin') || ! $user->isSuperAdmin(), 403, 'Only Super Admin can manage exit policies.');
    }
}

