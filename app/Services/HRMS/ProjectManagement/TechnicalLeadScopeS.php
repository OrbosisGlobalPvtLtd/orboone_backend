<?php

namespace App\Services\HRMS\ProjectManagement;

use App\Models\HRMS\ProjectManagement\TechnicalLeadAssignmentM;
use App\Models\HRMS\ProjectManagement\ProjectM;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TechnicalLeadScopeS
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
     * Check if user is Super Admin or has global view_all permission.
     */
    public function isSuperAdminOrGlobal(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return method_exists($user, 'hasPermission') && (
            $user->hasPermission('projects.view_all') ||
            $user->hasPermission('technical_lead.manage')
        );
    }

    /**
     * Check if employee is an active Technical Lead (has supervised developers).
     */
    public function isTechnicalLead(?int $employeeId = null): bool
    {
        $empId = $employeeId ?? $this->getOwnEmployeeId();
        if (!$empId) return false;

        if ($this->isSuperAdminOrGlobal()) return true;

        return DB::table('technical_lead_assignments')
            ->where('technical_lead_employee_id', $empId)
            ->where('is_active', 1)
            ->exists();
    }

    /**
     * Get active supervised employee IDs for a Technical Lead.
     */
    public function getActiveSupervisedEmployeeIds(?int $technicalLeadEmployeeId = null): array
    {
        $empId = $technicalLeadEmployeeId ?? $this->getOwnEmployeeId();

        if ($this->isSuperAdminOrGlobal() && !$technicalLeadEmployeeId) {
            return DB::table('employees_new')->pluck('id')->map(fn($id) => (int) $id)->all();
        }

        if (!$empId) return [];

        return DB::table('technical_lead_assignments')
            ->where('technical_lead_employee_id', $empId)
            ->where('is_active', 1)
            ->pluck('employee_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get active supervised project IDs where supervised developers are assigned.
     */
    public function getSupervisedProjectIds(?int $technicalLeadEmployeeId = null): array
    {
        $memberIds = $this->getActiveSupervisedEmployeeIds($technicalLeadEmployeeId);
        if (empty($memberIds)) return [];

        return DB::table('project_assignments')
            ->whereIn('employee_id', $memberIds)
            ->where('is_active', 1)
            ->pluck('project_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Scope attendance query to supervised developers.
     */
    public function scopeAttendanceQuery($query, ?int $technicalLeadEmployeeId = null)
    {
        if ($this->isSuperAdminOrGlobal() && !$technicalLeadEmployeeId) {
            return $query;
        }

        $memberIds = $this->getActiveSupervisedEmployeeIds($technicalLeadEmployeeId);
        return $query->whereIn('employee_id', $memberIds);
    }

    /**
     * Scope leave query to supervised developers.
     */
    public function scopeLeaveQuery($query, ?int $technicalLeadEmployeeId = null)
    {
        if ($this->isSuperAdminOrGlobal() && !$technicalLeadEmployeeId) {
            return $query;
        }

        $memberIds = $this->getActiveSupervisedEmployeeIds($technicalLeadEmployeeId);
        return $query->whereIn('employee_id', $memberIds);
    }

    /**
     * Scope work reports query to supervised developers.
     */
    public function scopeWorkReports($query, ?int $technicalLeadEmployeeId = null)
    {
        if ($this->isSuperAdminOrGlobal() && !$technicalLeadEmployeeId) {
            return $query;
        }

        $memberIds = $this->getActiveSupervisedEmployeeIds($technicalLeadEmployeeId);
        return $query->whereIn('employee_id', $memberIds);
    }

    /**
     * Scope project tasks query to supervised developers.
     */
    public function scopeTasks($query, ?int $technicalLeadEmployeeId = null)
    {
        if ($this->isSuperAdminOrGlobal() && !$technicalLeadEmployeeId) {
            return $query;
        }

        $memberIds = $this->getActiveSupervisedEmployeeIds($technicalLeadEmployeeId);
        return $query->whereIn('assigned_employee_id', $memberIds);
    }

    /**
     * Assign or Transfer a developer under Technical Lead supervision.
     */
    public function assignDeveloper(array $data): TechnicalLeadAssignmentM
    {
        return DB::transaction(function () use ($data) {
            // Deactivate any existing active assignment for this developer across any TL (Transfer / Re-assign)
            TechnicalLeadAssignmentM::where('employee_id', $data['employee_id'])
                ->where('is_active', 1)
                ->update([
                    'is_active' => 0,
                    'relieved_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

            return TechnicalLeadAssignmentM::create([
                'technical_lead_employee_id' => $data['technical_lead_employee_id'],
                'employee_id' => $data['employee_id'],
                'assigned_at' => $data['assigned_at'] ?? now(),
                'is_active' => 1,
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Relieve a developer from Technical Lead supervision.
     */
    public function relieveDeveloper(int $assignmentId): bool
    {
        $assignment = TechnicalLeadAssignmentM::findOrFail($assignmentId);
        $assignment->update([
            'is_active' => 0,
            'relieved_at' => now(),
            'updated_by' => Auth::id(),
        ]);
        return true;
    }
}
