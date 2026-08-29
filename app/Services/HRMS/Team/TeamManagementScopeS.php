<?php

namespace App\Services\HRMS\Team;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamManagementScopeS
{
    /**
     * Get logged-in user's Employee ID.
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
     * Check if authenticated user is Super Admin or HR Admin with global scope.
     */
    public function isSuperAdminOrGlobal(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        if (in_array($roleId, [1, 2, 3], true)) {
            return true;
        }

        $roleName = strtolower($user->role->name ?? '');
        if (in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true)) {
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin', 'hr'])) {
            return true;
        }

        if (method_exists($user, 'hasPermission')) {
            if ($user->hasPermission('leave.approvals.view_all')
                || $user->hasPermission('leave.approvals.approve')
                || $user->hasPermission('attendance.regularization.view_all')
                || $user->hasPermission('attendance.regularization.approve')
                || $user->hasPermission('attendance.records.view_all')
                || $user->hasPermission('reporting.structure.manage')
                || $user->hasPermission('reporting.view_all')
                || $user->hasPermission('projects.view_all')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get complete unified team employee IDs (Project Team Members UNION Reporting Employees).
     * Automatically removes duplicates and excludes inactive/exited employees.
     */
    public function getTeamEmployeeIds(?int $supervisorEmpId = null): array
    {
        $user = Auth::user();
        if ($user && !$this->isSuperAdminOrGlobal()) {
            $empId = $this->getOwnEmployeeId();
        } else {
            $empId = $supervisorEmpId ?? $this->getOwnEmployeeId();
        }

        if (!$empId) {
            return [];
        }

        // 1. Direct Reporting Employees (from employees_new source of truth)
        $directReportingIds = DB::table('employees_new')
            ->where('reporting_manager_employee_id', $empId)
            ->pluck('id')
            ->toArray();

        // 2. Reporting Assignments table
        $reportingAssignIds = DB::table('reporting_assignments')
            ->where('supervisor_employee_id', $empId)
            ->where('is_active', 1)
            ->pluck('employee_id')
            ->toArray();

        // 3. Legacy Technical Lead Assignments table
        $legacyAssignIds = DB::table('technical_lead_assignments')
            ->where('technical_lead_employee_id', $empId)
            ->where('is_active', 1)
            ->pluck('employee_id')
            ->toArray();

        // 4. Project Team Members (where logged-in employee is Delivery Head or Team Lead)
        $projectIdsAsDeliveryHead = DB::table('projects')
            ->where('delivery_head_employee_id', $empId)
            ->pluck('id')
            ->toArray();

        $teamIdsAsTeamLead = DB::table('project_teams')
            ->where('team_lead_employee_id', $empId)
            ->where('is_active', 1)
            ->pluck('id')
            ->toArray();

        $projectMemberIds = [];
        if (!empty($projectIdsAsDeliveryHead) || !empty($teamIdsAsTeamLead)) {
            $projectMemberIds = DB::table('project_assignments')
                ->where('is_active', 1)
                ->where(function ($q) use ($projectIdsAsDeliveryHead, $teamIdsAsTeamLead) {
                    if (!empty($projectIdsAsDeliveryHead)) {
                        $q->whereIn('project_id', $projectIdsAsDeliveryHead);
                    }
                    if (!empty($teamIdsAsTeamLead)) {
                        $q->orWhereIn('project_team_id', $teamIdsAsTeamLead);
                    }
                })
                ->pluck('employee_id')
                ->toArray();
        }

        // UNION all scopes & remove duplicate employee IDs (excluding current logged-in employee)
        $rawIds = array_values(array_diff(array_unique(array_merge(
            $directReportingIds,
            $reportingAssignIds,
            $legacyAssignIds,
            $projectMemberIds
        )), [$empId]));

        if (empty($rawIds)) {
            return [];
        }

        // Filter out inactive/exited employees using EmployeeM::active()
        return EmployeeM::active()->whereIn('id', $rawIds)->pluck('id')->toArray();
    }

    /**
     * Scope Attendance query using unified team scope.
     */
    public function scopeTeamAttendanceQuery($query, ?int $empId = null)
    {
        if ($this->isSuperAdminOrGlobal()) {
            return $query;
        }

        $teamIds = $this->getTeamEmployeeIds($empId);
        return $query->whereIn('employee_id', $teamIds);
    }

    /**
     * Scope Leave query using unified team scope.
     */
    public function scopeTeamLeaveQuery($query, ?int $empId = null)
    {
        if ($this->isSuperAdminOrGlobal()) {
            return $query;
        }

        $teamIds = $this->getTeamEmployeeIds($empId);
        return $query->whereIn('employee_id', $teamIds);
    }

    /**
     * Scope Work Reports query using unified team scope.
     */
    public function scopeTeamWorkReportsQuery($query, ?int $empId = null)
    {
        if ($this->isSuperAdminOrGlobal()) {
            return $query;
        }

        $teamIds = $this->getTeamEmployeeIds($empId);
        $column = 'employee_id';
        $from = is_string($query->from ?? null) ? $query->from : '';
        if ($from === 'attendance_work_logs' || str_contains($from, 'attendance_work_logs')) {
            $column = 'attendance_work_logs.employee_id';
        }
        return $query->whereIn($column, $teamIds);
    }

    /**
     * Scope Tasks query using unified team scope.
     */
    public function scopeTeamTasksQuery($query, ?int $empId = null)
    {
        if ($this->isSuperAdminOrGlobal()) {
            return $query;
        }

        $teamIds = $this->getTeamEmployeeIds($empId);
        return $query->whereIn('assigned_employee_id', $teamIds);
    }

    /**
     * Get Projects for unified team (Project Team Lead UNION Reporting Manager).
     */
    public function getTeamProjects(?int $empId = null)
    {
        $supervisorId = $empId ?? $this->getOwnEmployeeId();
        $teamEmpIds = $this->getTeamEmployeeIds($supervisorId);

        $query = DB::table('projects')
            ->leftJoin('employees_new as dh', 'dh.id', '=', 'projects.delivery_head_employee_id')
            ->leftJoin('users as dhu', 'dhu.id', '=', 'dh.user_id')
            ->where(function ($q) use ($supervisorId, $teamEmpIds) {
                // Projects where employee is Delivery Head
                if ($supervisorId) {
                    $q->where('projects.delivery_head_employee_id', $supervisorId);
                }
                // Projects where team members/reporting employees are assigned
                if (!empty($teamEmpIds)) {
                    $q->orWhereIn('projects.id', function ($sub) use ($teamEmpIds) {
                        $sub->select('project_id')
                            ->from('project_assignments')
                            ->whereIn('employee_id', $teamEmpIds)
                            ->where('is_active', 1);
                    });
                }
            })
            ->select('projects.*', DB::raw('COALESCE(projects.delivery_head_name, dhu.name, dh.employee_code) as delivery_head_name'))
            ->distinct();

        return $query->get();
    }
}
