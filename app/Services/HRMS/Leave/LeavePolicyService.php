<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeavePolicyM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeavePolicyService
{
    private ?LeavePolicyM $cachedDefault = null;
    /** @var array<int, LeavePolicyM|null> */
    private array $cachedPolicies = [];

    public function activeDefault(): LeavePolicyM
    {
        return $this->cachedDefault ??= LeavePolicyM::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->firstOrFail();
    }

    public function forEmployee(EmployeeM $employee, ?Carbon $date = null): LeavePolicyM
    {
        $date = $date ?: Carbon::now('Asia/Kolkata');

        $overridePolicyId = DB::table('leave_policy_employee_overrides')
            ->where('employee_id', $employee->id)
            ->where('is_active', 1)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_from')
                    ->orWhereDate('effective_from', '<=', $date->toDateString());
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->orderByDesc('effective_from')
            ->value('leave_policy_id');

        if ($overridePolicyId) {
            $override = $this->activePolicy((int) $overridePolicyId);
            if ($override) {
                return $override;
            }
        }

        if ($employee->leave_policy_id) {
            $policy = $this->activePolicy((int) $employee->leave_policy_id);
            if ($policy) {
                return $policy;
            }
        }

        return $this->activeDefault();
    }

    private function activePolicy(int $policyId): ?LeavePolicyM
    {
        if (! array_key_exists($policyId, $this->cachedPolicies)) {
            $this->cachedPolicies[$policyId] = LeavePolicyM::query()
                ->where('is_active', true)
                ->find($policyId);
        }

        return $this->cachedPolicies[$policyId];
    }
}
