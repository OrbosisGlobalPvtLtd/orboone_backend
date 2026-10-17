<?php

declare(strict_types=1);

namespace App\Services\HRMS\Leave;

use InvalidArgumentException;

final class MonthlyLeaveAccrualCalculator
{
    public function creditForMonth(string $annualEntitlement, int $eligibleMonth): string
    {
        if ($eligibleMonth < 1 || $eligibleMonth > 12) {
            throw new InvalidArgumentException('Eligible month must be between 1 and 12.');
        }

        $current = bcdiv(bcmul($annualEntitlement, (string) $eligibleMonth, 12), '12', 6);
        $previousMonth = $eligibleMonth - 1;
        $previous = bcdiv(bcmul($annualEntitlement, (string) $previousMonth, 12), '12', 6);

        return bcsub($current, $previous, 6);
    }
}
