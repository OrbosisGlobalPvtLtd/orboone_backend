<?php

namespace App\Services\HRMS\ProjectManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProjectAccessScopeS
{
    /**
     * Get employee ID for currently logged-in user.
     */
    public function getOwnEmployeeId(): ?int
    {
        $userId = Auth::id();
        if (!$userId) return null;

        $employee = DB::table('employees_new')->where('user_id', $userId)->first(['id']);
        return $employee ? (int) $employee->id : null;
    }

    /**
     * Check if user is Super Admin or has global project view_all permission.
     */
    public function isSuperAdminOrGlobal(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return method_exists($user, 'hasPermission') && $user->hasPermission('projects.view_all');
    }

    /**
     * Get active assigned project IDs for an employee.
     */
    public function getEmployeeProjectIds(?int $employeeId = null): array
    {
        $empId = $employeeId ?? $this->getOwnEmployeeId();
        if (!$empId) return [];

        return DB::table('project_assignments')
            ->where('employee_id', $empId)
            ->where('is_active', 1)
            ->pluck('project_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get project IDs where employee is Delivery Head.
     */
    public function getDeliveryHeadProjectIds(?int $employeeId = null): array
    {
        $empId = $employeeId ?? $this->getOwnEmployeeId();
        if (!$empId) return [];

        return DB::table('projects')
            ->where('delivery_head_employee_id', $empId)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get project team IDs where employee is Team Lead.
     */
    public function getTeamLeadTeamIds(?int $employeeId = null): array
    {
        $empId = $employeeId ?? $this->getOwnEmployeeId();
        if (!$empId) return [];

        return DB::table('project_teams')
            ->where('team_lead_employee_id', $empId)
            ->where('is_active', 1)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get all accessible project IDs for the logged in user (Admin, Delivery Head, or Member).
     */
    public function getAccessibleProjectIds(): array
    {
        if ($this->isSuperAdminOrGlobal()) {
            return DB::table('projects')->pluck('id')->map(fn($id) => (int) $id)->all();
        }

        $empId = $this->getOwnEmployeeId();
        if (!$empId) return [];

        $assignedProjectIds = $this->getEmployeeProjectIds($empId);
        $dhProjectIds = $this->getDeliveryHeadProjectIds($empId);

        return array_values(array_unique(array_merge($assignedProjectIds, $dhProjectIds)));
    }

    /**
     * Check if logged in user can access a specific project.
     */
    public function canAccessProject(int $projectId): bool
    {
        if ($this->isSuperAdminOrGlobal()) return true;

        $accessibleIds = $this->getAccessibleProjectIds();
        return in_array($projectId, $accessibleIds, true);
    }

    /**
     * Get member employee IDs for a Team Lead (includes Team Lead's own ID).
     */
    public function getTeamMemberEmployeeIdsForLead(?int $leadEmployeeId = null, ?int $projectId = null): array
    {
        $empId = $leadEmployeeId ?? $this->getOwnEmployeeId();
        if (!$empId) return [];

        $teamIds = DB::table('project_teams')
            ->where('team_lead_employee_id', $empId)
            ->where('is_active', 1)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->pluck('id');

        $memberIds = DB::table('project_assignments')
            ->whereIn('project_team_id', $teamIds)
            ->where('is_active', 1)
            ->pluck('employee_id')
            ->map(fn($id) => (int) $id)
            ->all();

        // Always include Team Lead's own employee ID so lead sees their own attendance/work
        $memberIds[] = (int) $empId;

        return array_values(array_unique(array_filter($memberIds)));
    }

    /**
     * Scope attendance query for Team Lead / Delivery Head / Admin.
     */
    public function scopeAttendanceQuery($query, ?int $projectId = null, ?string $fromDate = null, ?string $toDate = null)
    {
        if ($this->isSuperAdminOrGlobal()) {
            return $query;
        }

        $empId = $this->getOwnEmployeeId();
        if (!$empId) {
            return $query->where('employee_id', 0);
        }

        // If Delivery Head for the project, get all project member IDs
        $dhProjectIds = $this->getDeliveryHeadProjectIds($empId);
        if (!empty($dhProjectIds) && ($projectId === null || in_array($projectId, $dhProjectIds, true))) {
            $targetProjectIds = $projectId ? [$projectId] : $dhProjectIds;
            $memberIds = DB::table('project_assignments')
                ->whereIn('project_id', $targetProjectIds)
                ->where('is_active', 1)
                ->pluck('employee_id')
                ->map(fn($id) => (int) $id)
                ->all();
            $memberIds[] = (int) $empId;
            return $query->whereIn('employee_id', array_unique($memberIds));
        }

        // Otherwise Team Lead member IDs
        $memberIds = $this->getTeamMemberEmployeeIdsForLead($empId, $projectId);
        if (empty($memberIds)) {
            return $query->where('employee_id', $empId);
        }

        return $query->whereIn('employee_id', $memberIds);
    }
}
