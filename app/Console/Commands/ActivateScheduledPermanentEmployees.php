<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveAllocationService;
use App\Services\HRMS\Notification\NotificationS;
use App\Services\HRMS\EnterprisePayroll\EnterpriseSalaryStructureSyncS;

class ActivateScheduledPermanentEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrms:activate-scheduled-permanent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically activates scheduled permanent employees on their confirmation effective date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $this->info("Starting scheduled permanent activations for: {$today}");

        // Find employees who are scheduled to be marked permanent on or before today
        $employees = DB::table('employees_new')
            ->where('employee_stage', 'probation')
            ->where('probation_status', 'scheduled_permanent')
            ->whereNotNull('confirmation_effective_date')
            ->where('confirmation_effective_date', '<=', $today)
            ->get();

        if ($employees->isEmpty()) {
            $this->info("No scheduled permanent activations found for today.");
            return 0;
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($employees as $employeeData) {
            DB::beginTransaction();

            try {
                $employeeId = $employeeData->id;
                $effectiveDate = $employeeData->confirmation_effective_date;

                $updateData = [
                    'employee_stage' => 'permanent',
                    'probation_status' => 'completed',
                    'confirmation_date' => $effectiveDate,
                    'permanent_activated_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('employees_new', 'is_permanent')) {
                    $updateData['is_permanent'] = 1;
                }

                if (Schema::hasColumn('employees_new', 'permanent_at')) {
                    $updateData['permanent_at'] = $effectiveDate;
                }

                // Update employee record
                DB::table('employees_new')->where('id', $employeeId)->update($updateData);

                // Load employee model for leave allocation
                $empModel = EmployeeM::find($employeeId);
                if ($empModel) {
                    $empModel->confirmation_date = $effectiveDate;
                    $empModel->employee_stage = 'permanent';
                    
                    // Trigger pro-rata permanent leave allocation
                    app(LeaveAllocationService::class)->generateForEmployee(
                        $empModel,
                        (int) Carbon::parse($effectiveDate, 'Asia/Kolkata')->year,
                        null,
                        'permanent',
                        Carbon::parse($effectiveDate, 'Asia/Kolkata')
                    );
                }

                // Sync salary structure if class exists and employee has actual_salary
                if (class_exists(EnterpriseSalaryStructureSyncS::class)) {
                    $updatedEmpData = DB::table('employees_new')->where('id', $employeeId)->first();
                    if ($updatedEmpData && isset($updatedEmpData->actual_salary)) {
                        // Set salary effective date to the confirmation effective date
                        $updatedEmpData->salary_effective_from = $effectiveDate;
                        app(EnterpriseSalaryStructureSyncS::class)->syncFromEmployee($updatedEmpData, 'Auto-synced on permanent confirmation date');
                    }
                }

                // Clear any pending probation ending soon reminders
                app(NotificationS::class)->markEmployeeLifecycleNotificationsResolved((int) $employeeId, ['probation_ending_soon', 'probation_ending_reminder']);

                // Notify HR & Super Admin & Employee
                $empName = DB::table('users')->where('id', $employeeData->user_id)->value('name') ?: 'Employee';

                app(NotificationS::class)->notifyEmployee(
                    'Permanent Confirmation Activated',
                    'Your permanent confirmation has been activated successfully.',
                    'permanent_activated',
                    null,
                    [],
                    [],
                    $employeeData->user_id
                );

                app(NotificationS::class)->notifyHrAndSuperAdmin(
                    'Permanent Confirmation Activated',
                    "Permanent confirmation has been activated for {$empName} effective from " . Carbon::parse($effectiveDate)->format('d M Y') . '.',
                    'permanent_activated',
                    null,
                    [],
                    ['employee_id' => $employeeId]
                );

                DB::commit();
                $successCount++;
                $this->info("Successfully activated employee ID: {$employeeId} ({$empName})");

            } catch (\Throwable $e) {
                DB::rollBack();
                $failedCount++;
                $this->error("Failed to activate employee ID {$employeeData->id}: " . $e->getMessage());
                Log::error("Scheduled permanent activation failed for employee ID {$employeeData->id}: " . $e->getMessage(), [
                    'exception' => $e
                ]);
            }
        }

        $this->info("Scheduled permanent activation process completed. Success: {$successCount}, Failed: {$failedCount}");
        return 0;
    }
}
