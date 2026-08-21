<?php

namespace App\Services\HRMS\Reporting;

use App\Models\HRMS\Reporting\ReportingAssignmentM;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportingManagerAssignmentService
{
    /**
     * Synchronize employee reporting manager between employees_new and reporting_assignments table.
     */
    public function syncReportingManager(
        int $employeeId,
        ?int $reportingManagerEmployeeId,
        ?string $effectiveDate = null,
        ?int $updatedBy = null
    ): ?ReportingAssignmentM {
        return DB::transaction(function () use ($employeeId, $reportingManagerEmployeeId, $effectiveDate, $updatedBy) {
            $effectiveDate = $effectiveDate ?: date('Y-m-d');
            $updatedBy = $updatedBy ?: (Auth::id() ?: 1);

            // Fetch current reporting manager from employees_new
            $currentEmp = DB::table('employees_new')->where('id', $employeeId)->first(['id', 'reporting_manager_employee_id']);
            if (!$currentEmp) {
                return null;
            }

            $previousManagerId = $currentEmp->reporting_manager_employee_id ? (int)$currentEmp->reporting_manager_employee_id : null;
            $newManagerId = $reportingManagerEmployeeId ? (int)$reportingManagerEmployeeId : null;

            // 1. Update source of truth on employees_new
            DB::table('employees_new')
                ->where('id', $employeeId)
                ->update([
                    'reporting_manager_employee_id' => $newManagerId,
                    'updated_at' => now(),
                ]);

            // Clear sidebar menu cache for affected managers
            $this->clearMenuCacheForManager($previousManagerId);
            $this->clearMenuCacheForManager($newManagerId);

            // 2. If new manager is NULL: deactivate all active assignments for this employee
            if (!$newManagerId) {
                DB::table('reporting_assignments')
                    ->where('employee_id', $employeeId)
                    ->where('is_active', 1)
                    ->update([
                        'status' => 0,
                        'is_active' => 0,
                        'end_date' => now(),
                        'updated_by' => $updatedBy,
                        'updated_at' => now(),
                    ]);

                DB::table('technical_lead_assignments')
                    ->where('employee_id', $employeeId)
                    ->where('is_active', 1)
                    ->update([
                        'is_active' => 0,
                        'relieved_at' => now(),
                        'updated_by' => $updatedBy,
                        'updated_at' => now(),
                    ]);

                return null;
            }

            // 3. If new manager is the SAME as existing active assignment manager, verify active record exists
            $activeAssignment = DB::table('reporting_assignments')
                ->where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->first();

            if ($activeAssignment && (int)$activeAssignment->supervisor_employee_id === $newManagerId) {
                return ReportingAssignmentM::find($activeAssignment->id);
            }

            // 4. Deactivate any active assignments under previous managers to avoid duplicates
            DB::table('reporting_assignments')
                ->where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->update([
                    'status' => 0,
                    'is_active' => 0,
                    'end_date' => now(),
                    'updated_by' => $updatedBy,
                    'updated_at' => now(),
                ]);

            DB::table('technical_lead_assignments')
                ->where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->update([
                    'is_active' => 0,
                    'relieved_at' => now(),
                    'updated_by' => $updatedBy,
                    'updated_at' => now(),
                ]);

            // 5. Sync legacy technical_lead_assignments table
            DB::table('technical_lead_assignments')->insert([
                'technical_lead_employee_id' => $newManagerId,
                'employee_id' => $employeeId,
                'assigned_at' => $effectiveDate,
                'is_active' => 1,
                'created_by' => $updatedBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 6. Create new active assignment record
            return ReportingAssignmentM::create([
                'supervisor_employee_id' => $newManagerId,
                'employee_id' => $employeeId,
                'start_date' => $effectiveDate,
                'status' => 1,
                'is_active' => 1,
                'created_by' => $updatedBy,
            ]);
        });
    }

    /**
     * Backfill/Synchronize existing employees_new records into reporting_assignments table.
     */
    public function syncAllExistingEmployees(): int
    {
        $employees = DB::table('employees_new')
            ->whereNotNull('reporting_manager_employee_id')
            ->get(['id', 'reporting_manager_employee_id', 'joining_date', 'created_at']);

        $syncedCount = 0;
        foreach ($employees as $emp) {
            $managerId = (int)$emp->reporting_manager_employee_id;
            $empId = (int)$emp->id;
            $joiningDate = $emp->joining_date ?: ($emp->created_at ? date('Y-m-d', strtotime($emp->created_at)) : date('Y-m-d'));

            $hasActive = DB::table('reporting_assignments')
                ->where('employee_id', $empId)
                ->where('supervisor_employee_id', $managerId)
                ->where('is_active', 1)
                ->exists();

            if (!$hasActive) {
                $this->syncReportingManager($empId, $managerId, $joiningDate);
                $syncedCount++;
            }
        }

        return $syncedCount;
    }

    /**
     * Clear menu cache for a manager user.
     */
    private function clearMenuCacheForManager(?int $managerEmployeeId): void
    {
        if (!$managerEmployeeId) {
            return;
        }

        $emp = DB::table('employees_new')->where('id', $managerEmployeeId)->first(['user_id']);
        if ($emp && $emp->user_id) {
            app(\App\Services\Core\Menu\SidebarMenuResolverS::class)->clearCache((int)$emp->user_id);
        }
    }
}
