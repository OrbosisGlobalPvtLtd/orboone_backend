<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\EnterprisePayroll\EnterpriseAttendanceLeaveResolverS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PayrollViolationIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payroll_resolver_reads_half_day_impact_from_attendance(): void
    {
        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'Payroll Violation Employee',
            'email' => 'payroll_violation_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-PV-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        $halfDay = AttendanceTypeM::firstOrCreate(['code' => 'half_day'], ['name' => 'Half Day', 'is_active' => 1]);

        AttendanceM::create([
            'employee_id' => $employee->id,
            'user_id' => $employee->user_id,
            'attendance_date' => '2026-06-20',
            'attendance_type_id' => $halfDay->id,
            'attendance_status' => 'half_day',
            'is_half_day' => true,
        ]);

        $resolved = app(EnterpriseAttendanceLeaveResolverS::class)->resolve($employee, 6, 2026);

        $this->assertSame(1.0, (float) $resolved['half_days']);
        $this->assertSame(0.5, (float) $resolved['unpaid_days']);
    }
}

