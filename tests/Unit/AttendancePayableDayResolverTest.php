<?php

namespace Tests\Unit;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Services\HRMS\Attendance\AttendancePayableDayResolver;
use Tests\TestCase;

class AttendancePayableDayResolverTest extends TestCase
{
    public function test_half_day_resolves_to_half_payable(): void
    {
        $attendance = new AttendanceM(['is_half_day' => true]);
        $attendance->setRelation('attendanceType', new AttendanceTypeM(['code' => 'half_day']));

        $resolved = app(AttendancePayableDayResolver::class)->resolve($attendance);

        $this->assertSame(0.5, $resolved['payable_day']);
        $this->assertFalse($resolved['is_unresolved']);
    }

    public function test_punch_blocked_is_unresolved_and_unpaid(): void
    {
        $attendance = new AttendanceM(['is_punch_blocked' => true]);
        $attendance->setRelation('attendanceType', new AttendanceTypeM(['code' => 'punch_blocked']));

        $resolved = app(AttendancePayableDayResolver::class)->resolve($attendance);

        $this->assertSame(0.0, $resolved['payable_day']);
        $this->assertTrue($resolved['is_unresolved']);
    }
}

