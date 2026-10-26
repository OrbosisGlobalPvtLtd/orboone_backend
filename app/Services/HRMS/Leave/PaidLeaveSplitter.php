<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Enums\LeaveBucket;
use App\Models\HRMS\Leave\LeavePolicyM;

final readonly class PaidLeaveSplitter
{
    public function __construct(private LeaveUnitPolicy $unitPolicy)
    {
    }

    /** @return array{pl: string, sl: string, lop: string} */
    public function split(
        LeaveBucket $selected,
        string $requested,
        string $plAvailable,
        string $slAvailable,
        LeavePolicyM $policy,
        bool $validateRequestAmount = true,
        bool $allowFallback = true
    ): array
    {
        if ($validateRequestAmount && ! $this->unitPolicy->isValidRequestAmount($requested, $policy)) {
            throw new \InvalidArgumentException('Leave request must use the request unit configured in the effective leave policy.');
        }

        $available = [
            LeaveBucket::PL->value => $this->unitPolicy->usable($plAvailable, $policy),
            LeaveBucket::SL->value => $this->unitPolicy->usable($slAvailable, $policy),
        ];
        $remaining = bcadd($requested, '0', 6);
        $split = ['PL' => '0.000000', 'SL' => '0.000000'];

        $buckets = $allowFallback ? [$selected, $selected->otherPaidBucket()] : [$selected];
        foreach ($buckets as $bucket) {
            $take = bccomp($remaining, $available[$bucket->value], 6) <= 0
                ? $remaining
                : $available[$bucket->value];
            $split[$bucket->value] = $take;
            $remaining = bcsub($remaining, $take, 6);
        }

        return ['pl' => $split['PL'], 'sl' => $split['SL'], 'lop' => $remaining];
    }
}
