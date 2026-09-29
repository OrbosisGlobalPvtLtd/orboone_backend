<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Services\HRMS\Employee\EmployeeLifecycleService;
use App\Jobs\SendPermanentActivationNotifications;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Tests\TestCase;

class EmployeeLifecycleStageAllocationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LeavePolicyM::query()->update(['is_active' => 0]);
        LeavePolicyM::create([
            'policy_name' => 'Lifecycle Stage Policy',
            'annual_total_leaves' => 25,
            'annual_paid_leaves' => 18,
            'annual_sick_leaves' => 7,
            'monthly_leave_limit' => 2,
            'max_leave_at_once' => 15,
            'carry_forward_enabled' => false,
            'sandwich_enabled' => true,
            'weekoff_included_in_sandwich' => true,
            'holiday_included_in_sandwich' => true,
            'probation_leave_limit' => 1,
            'internship_leave_limit' => 1,
            'medical_certificate_after_days' => 2,
            'rounding_method' => 'nearest',
            'is_active' => true,
        ]);
    }

    public function test_internship_and_probation_stage_get_one_leave_allocation(): void
    {
        $intern = $this->makeEmployee('internship', '2026-01-10');
        app(EmployeeLifecycleService::class)->autoAllocateForStage($intern->id, 'internship', '2026-01-10');

        $internAlloc = LeaveAllocationM::where('employee_id', $intern->id)->where('year', 2026)->where('employment_stage', 'internship')->first();
        $this->assertNotNull($internAlloc);
        $this->assertSame(1.0, (float) $internAlloc->total_allocated);

        $probation = $this->makeEmployee('probation', '2026-02-01');
        app(EmployeeLifecycleService::class)->autoAllocateForStage($probation->id, 'probation', '2026-02-01');

        $probAlloc = LeaveAllocationM::where('employee_id', $probation->id)->where('year', 2026)->where('employment_stage', 'probation')->first();
        $this->assertNotNull($probAlloc);
        $this->assertSame(1.0, (float) $probAlloc->total_allocated);
    }

    public function test_internship_to_probation_and_probation_to_permanent_allocates_expected_values(): void
    {
        $employee = $this->makeEmployee('internship', '2026-03-01');
        $service = app(EmployeeLifecycleService::class);

        $service->autoAllocateForStage($employee->id, 'internship', '2026-03-01');
        $service->autoAllocateForStage($employee->id, 'probation', '2026-05-01');
        $service->autoAllocateForStage($employee->id, 'probation', '2026-05-01');

        $probCount = LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2026)->where('employment_stage', 'probation')->count();
        $this->assertSame(1, $probCount);

        $employee->employee_stage = 'permanent';
        $employee->confirmation_date = '2026-09-05';
        $employee->save();

        $service->autoAllocateForStage($employee->id, 'permanent', '2026-09-05');

        $perm = LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2026)->where('employment_stage', 'permanent')->first();
        $this->assertNotNull($perm);
        $this->assertSame(8.0, (float) $perm->total_allocated);
        $this->assertSame(6.0, (float) $perm->paid_allocated);
        $this->assertSame(2.0, (float) $perm->sick_allocated);
    }

    public function test_scheduled_activation_uses_effective_date_and_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 12:00:00', 'Asia/Kolkata'));
        try {
            $employee = $this->makeEmployee('probation', '2026-01-01');
            $employee->forceFill([
                'probation_status' => 'scheduled_permanent',
                'probation_end_date' => '2026-09-22',
                'confirmation_effective_date' => '2026-09-23',
            ])->save();

            $service = app(EmployeeLifecycleService::class);
            $first = $service->activatePermanent($employee, '', 'scheduled');
            $second = $service->activatePermanent($employee, '', 'scheduled');

            $this->assertSame('activated', $first['status']);
            $this->assertSame('2026-09-23', $first['effective_date']);
            $this->assertSame('skipped', $second['status']);
            $employee->refresh();
            $this->assertSame('permanent', $employee->employee_stage);
            $this->assertSame('completed', $employee->probation_status);
            $this->assertSame('2026-09-23', substr((string) $employee->confirmation_date, 0, 10));
            $this->assertSame('2026-09-23', substr((string) $employee->confirmation_effective_date, 0, 10));
            $this->assertSame(1, LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2026)->where('employment_stage', 'permanent')->count());
            $this->assertSame(1, DB::table('notifications')
                ->where('lifecycle_event_key', "permanent_activated:{$employee->id}:2026-09-23")
                ->where('user_id', $employee->user_id)
                ->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_activation_reloads_employee_under_a_row_lock(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'pgsql'], true)) {
            $this->markTestSkipped('This database driver does not expose FOR UPDATE in executed SQL.');
        }

        $employee = $this->makeEmployee('probation', '2026-01-01');
        $employee->forceFill([
            'probation_status' => 'scheduled_permanent',
            'probation_end_date' => '2026-09-22',
            'confirmation_effective_date' => '2026-09-23',
        ])->save();
        $lockedEmployeeQueries = [];
        DB::listen(function (\Illuminate\Database\Events\QueryExecuted $query) use (&$lockedEmployeeQueries) {
            $sql = strtolower($query->sql);
            if (str_contains($sql, 'employees_new') && str_contains($sql, 'for update')) {
                $lockedEmployeeQueries[] = $sql;
            }
        });

        app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'scheduled');

        $this->assertNotEmpty($lockedEmployeeQueries);
    }

    public function test_probation_ending_today_is_not_activated_but_yesterday_is(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 12:00:00', 'Asia/Kolkata'));
        try {
            $service = app(EmployeeLifecycleService::class);
            $today = $this->makeEmployee('probation', '2026-01-01');
            $today->forceFill(['probation_status' => 'ongoing', 'probation_end_date' => '2026-09-23'])->save();
            $todayResult = $service->activatePermanent($today, '', 'auto_expiry');
            $this->assertSame('skipped', $todayResult['status']);
            $this->assertSame('probation', $today->fresh()->employee_stage);

            $yesterday = $this->makeEmployee('probation', '2026-01-01');
            $yesterday->forceFill(['probation_status' => 'ongoing', 'probation_end_date' => '2026-09-22'])->save();
            $expiredResult = $service->activatePermanent($yesterday, '', 'auto_expiry');
            $this->assertSame('activated', $expiredResult['status']);
            $this->assertSame('2026-09-23', $expiredResult['effective_date']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_scheduled_confirmation_for_tomorrow_is_untouched(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 12:00:00', 'Asia/Kolkata'));
        try {
            $employee = $this->makeEmployee('probation', '2026-01-01');
            $employee->forceFill([
                'probation_status' => 'scheduled_permanent',
                'probation_end_date' => '2026-09-22',
                'confirmation_effective_date' => '2026-09-24',
            ])->save();

            $result = app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'scheduled');
            $this->assertSame('skipped', $result['status']);
            $this->assertSame('probation', $employee->fresh()->employee_stage);
            $this->assertSame(0, LeaveAllocationM::where('employee_id', $employee->id)->where('employment_stage', 'permanent')->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_historical_expiry_creates_historical_and_independent_current_year_allocations(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 12:00:00', 'Asia/Kolkata'));
        try {
            $employee = $this->makeEmployee('probation', '2025-01-01');
            $employee->forceFill([
                'probation_status' => 'ongoing',
                'probation_end_date' => '2025-11-30',
            ])->save();

            $result = app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'auto_expiry');
            $this->assertSame('activated', $result['status']);
            $this->assertSame('2025-12-01', $result['effective_date']);

            $historical = LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2025)->firstOrFail();
            $current = LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2026)->firstOrFail();
            $this->assertSame('2025-12-01', substr((string) $historical->allocation_from_date, 0, 10));
            $this->assertSame(2.0, (float) $historical->total_allocated);
            $this->assertTrue((bool) $historical->is_locked);
            $this->assertSame('2026-01-01', substr((string) $current->allocation_from_date, 0, 10));
            $this->assertSame(25.0, (float) $current->total_allocated);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_locked_historical_probation_allocation_is_preserved_during_activation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 12:00:00', 'Asia/Kolkata'));
        try {
            $employee = $this->makeEmployee('probation', '2025-01-01');
            $employee->forceFill([
                'probation_status' => 'ongoing',
                'probation_end_date' => '2025-11-30',
            ])->save();
            $locked = LeaveAllocationM::create([
                'employee_id' => $employee->id,
                'year' => 2025,
                'employment_stage' => 'probation',
                'allocation_from_date' => '2025-01-01',
                'allocation_to_date' => '2025-12-31',
                'total_allocated' => 1,
                'paid_allocated' => 1,
                'paid_used' => 0.5,
                'paid_remaining' => 0.5,
                'total_remaining' => 0.5,
                'allocation_reason' => 'Final historical probation allocation',
                'is_locked' => 1,
            ]);

            $result = app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'auto_expiry');
            $this->assertSame('activated', $result['status']);

            $locked->refresh();
            $this->assertSame('probation', $locked->employment_stage);
            $this->assertSame(1.0, (float) $locked->total_allocated);
            $this->assertSame(0.5, (float) $locked->paid_used);
            $this->assertTrue((bool) $locked->is_locked);
            $this->assertSame(1, LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2025)->count());
            $current = LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2026)->firstOrFail();
            $this->assertSame('2026-01-01', substr((string) $current->allocation_from_date, 0, 10));
            $this->assertSame(25.0, (float) $current->total_allocated);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_activation_notification_retry_reuses_the_same_in_app_record(): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasColumn('notifications', 'data')) {
            $this->markTestSkipped('The notifications table lacks the data field required for event idempotency.');
        }

        $employee = $this->makeEmployee('permanent', '2026-01-01');
        DB::table('users')->where('id', $employee->user_id)->update(['fcm_token' => 'activation-test-token']);
        $transactionBaseline = DB::transactionLevel();
        $this->mock(\App\Services\Notification\FcmNotificationS::class, function ($mock) use ($transactionBaseline) {
            $mock->shouldReceive('sendPush')->once()->andReturnUsing(function () use ($transactionBaseline) {
                $this->assertSame($transactionBaseline, DB::transactionLevel(), 'Provider delivery must run without a notification-service transaction or lock.');
                return true;
            });
        });
        $service = app(\App\Services\HRMS\Notification\NotificationS::class);
        $service->notifyPermanentActivation($employee->id, $employee->user_id, '2026-09-23');
        $service->notifyPermanentActivation($employee->id, $employee->user_id, '2026-09-23');

        $this->assertSame(1, DB::table('notifications')
            ->where('user_id', $employee->user_id)
            ->where('type', 'permanent_activated')
            ->where('data', 'like', '%"lifecycle_event_key":"permanent_activated:' . $employee->id . ':2026-09-23"%')
            ->count());
    }

    public function test_committed_activation_event_can_be_redispatched_by_the_sweep_command(): void
    {
        $employee = $this->makeEmployee('probation', '2026-01-01');
        $employee->forceFill(['probation_status' => 'ongoing', 'probation_end_date' => '2026-09-22'])->save();
        Queue::fake();

        $result = app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'auto_expiry');
        $this->assertSame('activated', $result['status']);
        $eventKey = "permanent_activated:{$employee->id}:2026-09-23";
        $this->assertDatabaseHas('notifications', [
            'user_id' => $employee->user_id,
            'lifecycle_event_key' => $eventKey,
        ]);

        // Simulate a committed durable event whose original queue dispatch did not survive.
        DB::table('notifications')->where('lifecycle_event_key', $eventKey)->update([
            'activation_delivery_status' => 'pending',
            'activation_delivery_claimed_at' => null,
        ]);
        Queue::fake();
        Artisan::call('hrms:dispatch-permanent-activation-notifications');
        Queue::assertPushed(SendPermanentActivationNotifications::class, fn ($job) => $job->eventKey === $eventKey);
    }

    public function test_failed_notification_channel_is_retried_without_duplicate_in_app_row(): void
    {
        $employee = $this->makeEmployee('permanent', '2026-01-01');
        DB::table('users')->where('id', $employee->user_id)->update(['fcm_token' => 'activation-retry-token']);
        $this->mock(\App\Services\Notification\FcmNotificationS::class, function ($mock) {
            $mock->shouldReceive('sendPush')->twice()->andReturn(false, true);
            $mock->shouldReceive('lastResponse')->once()->andReturn(['error' => 'retry test']);
        });
        $notifications = app(\App\Services\HRMS\Notification\NotificationS::class);

        try {
            $notifications->notifyPermanentActivation($employee->id, $employee->user_id, '2026-09-23');
            $this->fail('Expected the first provider attempt to fail.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('FCM permanent activation delivery failed', $exception->getMessage());
        }

        $notifications->notifyPermanentActivation($employee->id, $employee->user_id, '2026-09-23');
        $row = DB::table('notifications')
            ->where('user_id', $employee->user_id)
            ->where('lifecycle_event_key', "permanent_activated:{$employee->id}:2026-09-23")
            ->first();
        $this->assertNotNull($row);
        $this->assertSame('completed', $row->activation_delivery_status);
        $this->assertSame(2, (int) $row->activation_delivery_attempts);
        $this->assertSame(1, DB::table('notifications')->where('lifecycle_event_key', $row->lifecycle_event_key)->where('user_id', $employee->user_id)->count());
    }

    public function test_manual_activation_persists_the_same_authoritative_lifecycle_fields(): void
    {
        $employee = $this->makeEmployee('probation', '2026-01-01');
        $employee->forceFill(['probation_status' => 'ongoing', 'probation_end_date' => '2026-09-30', 'actual_salary' => 5000])->save();

        $result = app(EmployeeLifecycleService::class)->activatePermanent($employee, '2026-09-15', 'manual', $employee->user_id);

        $this->assertSame('activated', $result['status']);
        $employee->refresh();
        $this->assertSame('permanent', $employee->employee_stage);
        $this->assertSame('completed', $employee->probation_status);
        $this->assertSame('2026-09-15', substr((string) $employee->confirmation_date, 0, 10));
        $this->assertSame('2026-09-15', substr((string) $employee->confirmation_effective_date, 0, 10));
        $this->assertSame(1, (int) $employee->is_permanent);
        $salaryHistory = DB::table('employee_salary_histories')->where('employee_id', $employee->id)->whereNull('effective_to')->first();
        $this->assertNotNull($salaryHistory);
        $this->assertSame('permanent', $salaryHistory->stage);
        $this->assertSame('2026-09-15', substr((string) $salaryHistory->effective_from, 0, 10));
    }

    public function test_activation_writes_employee_and_enterprise_salary_history_at_effective_date(): void
    {
        $employee = $this->makeEmployee('probation', '2026-01-01');
        $employee->forceFill([
            'probation_status' => 'ongoing',
            'probation_end_date' => '2026-09-22',
            'actual_salary' => 5000,
        ])->save();

        $result = app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'auto_expiry');
        $this->assertSame('2026-09-23', $result['effective_date']);
        $salaryHistory = DB::table('employee_salary_histories')
            ->where('employee_id', $employee->id)
            ->whereNull('effective_to')
            ->first();
        $enterpriseHistory = DB::table('enterprise_salary_structures')
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->first();

        $this->assertNotNull($salaryHistory);
        $this->assertSame('permanent', $salaryHistory->stage);
        $this->assertSame('2026-09-23', substr((string) $salaryHistory->effective_from, 0, 10));
        $this->assertNotNull($enterpriseHistory);
        $this->assertSame('2026-09-23', substr((string) $enterpriseHistory->effective_from, 0, 10));
    }

    public function test_leave_failure_rolls_back_permanent_activation(): void
    {
        $employee = $this->makeEmployee('probation', '2026-01-01');
        $employee->forceFill(['probation_status' => 'ongoing', 'probation_end_date' => '2026-09-22'])->save();
        $this->mock(\App\Services\HRMS\Leave\LeaveAllocationService::class, function ($mock) {
            $mock->shouldReceive('generateForEmployee')->once()->andThrow(new \RuntimeException('leave failure'));
        });

        try {
            app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'auto_expiry');
            $this->fail('Expected leave allocation failure to propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('leave failure', $exception->getMessage());
        }

        $this->assertSame('probation', $employee->fresh()->employee_stage);
        $this->assertDatabaseMissing('notifications', ['lifecycle_event_key' => "permanent_activated:{$employee->id}:2026-09-23"]);
    }

    public function test_salary_failure_rolls_back_permanent_activation(): void
    {
        $employee = $this->makeEmployee('probation', '2026-01-01');
        $employee->forceFill([
            'probation_status' => 'ongoing',
            'probation_end_date' => '2026-09-22',
            'actual_salary' => 5000,
        ])->save();
        $this->mock(\App\Services\HRMS\EnterprisePayroll\EnterpriseSalaryStructureSyncS::class, function ($mock) {
            $mock->shouldReceive('syncFromEmployee')->once()->andThrow(new \RuntimeException('salary failure'));
        });

        try {
            app(EmployeeLifecycleService::class)->activatePermanent($employee, '', 'auto_expiry');
            $this->fail('Expected salary synchronization failure to propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('salary failure', $exception->getMessage());
        }

        $this->assertSame('probation', $employee->fresh()->employee_stage);
    }

    private function makeEmployee(string $stage, string $joiningDate): EmployeeM
    {
        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'Lifecycle Stage ' . uniqid(),
            'email' => 'lifecycle_stage_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-LC-' . rand(1000, 9999),
            'employment_type' => $stage === 'internship' ? 'intern' : 'full_time',
            'employee_stage' => $stage,
            'joining_date' => $joiningDate,
            'employment_status' => 'active',
            'is_active' => 1,
            'work_mode' => 'wfo',
        ]);

        DB::table('employee_profiles')->insert([
            'employee_id' => $employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $employee;
    }
}

