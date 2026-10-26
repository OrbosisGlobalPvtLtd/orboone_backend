<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Leave\LeavePolicyM;

final class LeaveUnitPolicy
{
    public function isValidRequestAmount(string $amount, LeavePolicyM $policy): bool
    {
        $unit = (string) $policy->request_unit;
        if (! extension_loaded('bcmath') || ! preg_match('/^\d+(?:\.\d{1,6})?$/', $amount)
            || ! preg_match('/^\d+(?:\.\d{1,6})?$/', $unit) || bccomp($unit, '0', 6) <= 0) {
            return false;
        }

        $units = bcdiv($amount, $unit, 6);
        return bccomp($amount, '0', 6) > 0
            && bccomp($units, bcdiv($units, '1', 0), 6) === 0;
    }

    public function usable(string $available, LeavePolicyM $policy): string
    {
        $unit = (string) $policy->request_unit;
        if (bccomp($available, '0', 6) <= 0) {
            return '0.000000';
        }

        $units = bcdiv($available, $unit, 0);
        return bcmul($units, $unit, 6);
    }
}
