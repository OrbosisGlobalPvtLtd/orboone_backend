<?php

namespace App\Services\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\HrmsExitPolicyM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class EmployeeExitPolicyS
{
    public function resolvePolicy(?EmployeeM $employee, string $exitType, ?Carbon $onDate = null): ?HrmsExitPolicyM
    {
        if (! Schema::hasTable('hrms_exit_policies')) {
            return null;
        }

        $onDate = $onDate ?: now();
        $stage = strtolower((string) ($employee?->employee_stage ?: 'all'));
        $exitType = strtolower(trim($exitType));
        $cacheKey = 'hrms_exit_policy:' . $stage . ':' . $exitType . ':' . $onDate->toDateString();

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($stage, $exitType, $onDate) {
            $query = HrmsExitPolicyM::query()
                ->where('is_active', true)
                ->where(function ($q) use ($onDate) {
                    $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $onDate->toDateString());
                })
                ->where(function ($q) use ($exitType) {
                    $q->whereNull('exit_type')->orWhere('exit_type', '')->orWhere('exit_type', $exitType);
                })
                ->where(function ($q) use ($stage) {
                    $q->where('applies_to', 'all')->orWhere('applies_to', $stage);
                });

            return $query
                ->orderByRaw("CASE WHEN applies_to = ? THEN 0 ELSE 1 END", [$stage])
                ->orderByRaw("CASE WHEN exit_type = ? THEN 0 ELSE 1 END", [$exitType])
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first();
        });
    }

    public function getNoticePeriodDays(?EmployeeM $employee, string $exitType): int
    {
        $policy = $this->resolvePolicy($employee, $exitType);
        return max(0, (int) ($policy?->notice_period_days ?? 15));
    }

    public function getFnfProcessingDays(?EmployeeM $employee, string $exitType): int
    {
        $policy = $this->resolvePolicy($employee, $exitType);
        return max(0, (int) ($policy?->fnf_processing_days ?? 15));
    }

    public function calculateLastWorkingDay(string $resignationDate, int $noticeDays): string
    {
        $base = Carbon::parse($resignationDate)->startOfDay();
        $days = max(1, $noticeDays);
        return $base->copy()->addDays($days - 1)->toDateString();
    }

    public function flags(?EmployeeM $employee, string $exitType): array
    {
        $policy = $this->resolvePolicy($employee, $exitType);
        return [
            'allow_waiver' => (bool) ($policy?->allow_waiver ?? true),
            'allow_buyout' => (bool) ($policy?->allow_buyout ?? true),
            'allow_immediate_exit' => (bool) ($policy?->allow_immediate_exit ?? true),
        ];
    }
}

