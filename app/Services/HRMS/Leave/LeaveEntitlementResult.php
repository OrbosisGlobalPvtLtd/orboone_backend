<?php

namespace App\Services\HRMS\Leave;

class LeaveEntitlementResult
{
    public function __construct(
        public readonly string $stage,
        public readonly float $totalAllocated,
        public readonly float $paidAllocated,
        public readonly float $sickAllocated,
        public readonly float $monthlyQuota,
        public readonly ?string $allocationFromDate = null,
        public readonly ?string $allocationToDate = null,
        public readonly ?int $policyId = null,
        public readonly bool $isPaidIntern = true
    ) {
    }

    public function toArray(): array
    {
        return [
            'stage' => $this->stage,
            'total_allocated' => $this->totalAllocated,
            'paid_allocated' => $this->paidAllocated,
            'sick_allocated' => $this->sickAllocated,
            'monthly_quota' => $this->monthlyQuota,
            'allocation_from_date' => $this->allocationFromDate,
            'allocation_to_date' => $this->allocationToDate,
            'policy_id' => $this->policyId,
            'is_paid_intern' => $this->isPaidIntern,
        ];
    }
}
