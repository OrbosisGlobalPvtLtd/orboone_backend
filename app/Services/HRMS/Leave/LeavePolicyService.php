<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeavePolicyM;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LeavePolicyService
{
    private ?LeavePolicyM $cachedDefault = null;
    /** @var array<int, LeavePolicyM|null> */
    private array $cachedPolicies = [];
    private ?string $lastResolutionReason = null;
    private ?string $lastResolutionAnomaly = null;

    public function activeDefault(): LeavePolicyM
    {
        return $this->cachedDefault ??= LeavePolicyM::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->firstOrFail();
    }

    public function forEmployee(EmployeeM $employee, ?Carbon $date = null): ?LeavePolicyM
    {
        $date = $date ?: Carbon::now('Asia/Kolkata');
        $this->lastResolutionReason = null;
        $this->lastResolutionAnomaly = null;

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
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('leave_policies')
                    ->whereColumn('leave_policies.id', 'leave_policy_employee_overrides.leave_policy_id')
                    ->where('leave_policies.is_active', 1);
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

        $default = LeavePolicyM::query()->where('is_active', true)->orderBy('id')->first();
        if ($default) {
            $futureOverrideExists = DB::table('leave_policy_employee_overrides')
                ->where('employee_id', $employee->id)
                ->where('is_active', 1)
                ->whereDate('effective_from', '>', $date->toDateString())
                ->where('leave_policy_id', $default->id)
                ->exists();
            $activePolicyCount = LeavePolicyM::query()->where('is_active', true)->count();
            if ($futureOverrideExists && $activePolicyCount === 1) {
                $this->lastResolutionAnomaly = 'policy_effective_after_period';
            }

            return $default;
        }

        $this->lastResolutionReason = "Employee #{$employee->id} has no active leave policy for {$date->toDateString()}.";
        return null;
    }

    /**
     * Resolve policies for a group of employees using the same precedence as forEmployee().
     * The queries are bounded by the supplied employee set so allocation pages do not issue
     * one override and policy query per row.
     *
     * @param Collection<int, EmployeeM>|array<int, EmployeeM> $employees
     * @return Collection<int, LeavePolicyM> keyed by employee id
     */
    public function forEmployees(Collection|array $employees, ?Carbon $date = null): Collection
    {
        $date = ($date ?? Carbon::now('Asia/Kolkata'))->copy()->setTimezone('Asia/Kolkata');
        $employees = collect($employees)->filter(static fn ($employee): bool => $employee instanceof EmployeeM)
            ->keyBy(static fn (EmployeeM $employee): int => (int) $employee->id);
        if ($employees->isEmpty()) {
            return collect();
        }

        $overrides = DB::table('leave_policy_employee_overrides')
            ->whereIn('employee_id', $employees->keys()->all())
            ->where('is_active', 1)
            ->where(static function ($query) use ($date): void {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date->toDateString());
            })
            ->where(static function ($query) use ($date): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->whereExists(static function ($query): void {
                $query->selectRaw('1')->from('leave_policies')
                    ->whereColumn('leave_policies.id', 'leave_policy_employee_overrides.leave_policy_id')
                    ->where('leave_policies.is_active', 1);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get()
            ->groupBy('employee_id');

        $policies = LeavePolicyM::query()->where('is_active', true)->orderBy('id')->get()->keyBy('id');
        $default = $policies->first();
        $resolved = collect();

        foreach ($employees as $employeeId => $employee) {
            $override = $overrides->get($employeeId)?->first();
            $policy = $override ? $policies->get((int) $override->leave_policy_id) : null;
            $policy ??= $employee->leave_policy_id
                ? $policies->get((int) $employee->leave_policy_id)
                : null;
            $policy ??= $default;

            if ($policy) {
                $resolved->put((int) $employeeId, $policy);
            }
        }

        return $resolved;
    }

    public function requireForEmployee(EmployeeM $employee, ?Carbon $date = null): LeavePolicyM
    {
        $policy = $this->forEmployee($employee, $date);
        if (! $policy) {
            throw new DomainException($this->lastResolutionReason ?? "Employee #{$employee->id} has no active leave policy.");
        }

        return $policy;
    }

    public function lastResolutionReason(): ?string
    {
        return $this->lastResolutionReason;
    }

    public function lastResolutionAnomaly(): ?string
    {
        return $this->lastResolutionAnomaly;
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
