<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\HRMS\Leave\LeaveTypeM;
use InvalidArgumentException;

enum LeaveBucket: string
{
    case PL = 'PL';
    case SL = 'SL';

    public static function fromLeaveType(LeaveTypeM|string $leaveType): self
    {
        if ($leaveType instanceof LeaveTypeM) {
            $code = strtoupper(trim((string) $leaveType->code));
            if ($code === 'PL' || $code === 'SL') {
                return self::from($code);
            }
            if ((bool) $leaveType->is_comp_off || (bool) $leaveType->is_lwp) {
                throw new InvalidArgumentException('Comp-off and LOP do not map to a paid leave bucket.');
            }
            if ((bool) $leaveType->is_sick) {
                return self::SL;
            }
            if ((bool) $leaveType->is_paid) {
                return self::PL;
            }
        } else {
            $code = strtoupper(trim($leaveType));
            if ($code === 'PL' || $code === 'SL') {
                return self::from($code);
            }
        }

        throw new InvalidArgumentException('Leave type is not a PL or SL bucket.');
    }

    public function otherPaidBucket(): self
    {
        return $this === self::PL ? self::SL : self::PL;
    }

    public function allocationColumns(): array
    {
        return match ($this) {
            self::PL => ['allocated' => 'paid_allocated', 'used' => 'paid_used', 'remaining' => 'paid_remaining'],
            self::SL => ['allocated' => 'sick_allocated', 'used' => 'sick_used', 'remaining' => 'sick_remaining'],
        };
    }
}
