<?php

namespace App\Services\HRMS\Reporting;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Reporting\ReportingAssignmentM;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportingScopeS
{
    /**
     * Resolve authenticated employee ID
     */
    public function getOwnEmployeeId(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $emp = DB::table('employees_new')->where('user_id', $user->id)->first(['id']);
        return $emp ? (int)$emp->id : null;
    }

    /**
     * Check if authenticated user is Super Admin or HR Admin with global reporting access.
     */
    public function isSuperAdminOrGlobal(): bool
    {
        return app(\App\Services\HRMS\Team\TeamManagementScopeS::class)->isSuperAdminOrGlobal();
    }

    /**
     * Get active supervised employee IDs for a supervisor (Delegated to TeamManagementScopeS for Unified Team Scope).
     */
    public function getActiveSupervisedEmployeeIds(?int $supervisorEmpId = null): array
    {
        return app(\App\Services\HRMS\Team\TeamManagementScopeS::class)->getTeamEmployeeIds($supervisorEmpId);
    }

    /**
     * Scope attendance query to supervised employees.
     */
    public function scopeAttendanceQuery($query, ?int $supervisorEmpId = null)
    {
        return app(\App\Services\HRMS\Team\TeamManagementScopeS::class)->scopeTeamAttendanceQuery($query, $supervisorEmpId);
    }

    /**
     * Scope leave query to supervised employees.
     */
    public function scopeLeaveQuery($query, ?int $supervisorEmpId = null)
    {
        return app(\App\Services\HRMS\Team\TeamManagementScopeS::class)->scopeTeamLeaveQuery($query, $supervisorEmpId);
    }

    /**
     * Scope work reports query to supervised employees.
     */
    public function scopeWorkReports($query, ?int $supervisorEmpId = null)
    {
        return app(\App\Services\HRMS\Team\TeamManagementScopeS::class)->scopeTeamWorkReportsQuery($query, $supervisorEmpId);
    }

    /**
     * Scope tasks query to supervised employees.
     */
    public function scopeTasks($query, ?int $supervisorEmpId = null)
    {
        return app(\App\Services\HRMS\Team\TeamManagementScopeS::class)->scopeTeamTasksQuery($query, $supervisorEmpId);
    }

    /**
     * Assign or Transfer an employee under a Reporting Manager.
     */
    public function assignSupervisor(array $data): ReportingAssignmentM
    {
        $service = app(ReportingManagerAssignmentService::class);
        return $service->syncReportingManager(
            (int)$data['employee_id'],
            $data['supervisor_employee_id'] ? (int)$data['supervisor_employee_id'] : null,
            $data['start_date'] ?? $data['assigned_at'] ?? date('Y-m-d')
        );
    }

    /**
     * Relieve an employee from Reporting Management.
     */
    public function relieveEmployee(int $assignmentId): bool
    {
        $assignment = ReportingAssignmentM::find($assignmentId);
        if ($assignment) {
            $service = app(ReportingManagerAssignmentService::class);
            $service->syncReportingManager((int)$assignment->employee_id, null);
            return true;
        }

        $legacy = DB::table('technical_lead_assignments')->where('id', $assignmentId)->first();
        if ($legacy) {
            $service = app(ReportingManagerAssignmentService::class);
            $service->syncReportingManager((int)$legacy->employee_id, null);
        }
        return true;
    }

    /**
     * Relieve employee by Employee ID.
     */
    public function relieveEmployeeByEmpId(int $employeeId): bool
    {
        $service = app(ReportingManagerAssignmentService::class);
        $service->syncReportingManager($employeeId, null);
        return true;
    }
}
