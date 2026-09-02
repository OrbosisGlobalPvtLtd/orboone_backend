<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure the HR Admin role has all Employee Management permissions in role_permissions table.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions') || ! Schema::hasTable('role_permissions')) {
            return;
        }

        $role = DB::table('roles')->where('slug', 'hr_admin')->first(['id']);
        if (! $role) {
            return;
        }

        $roleId = (int) $role->id;
        $now = now();

        $employeePermissionKeys = [
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'employees.update',
            'employees.directory.view',
            'employees.performance.view',
            'employees.pending_profiles.view',
            'employees.pending_profiles.approve',
            'employees.probation_internship.view',
            'employees.probation_internship.manage',
            'employees.exit.view',
            'employees.exit.manage',
            'employees.organization.manage',
            'employees.reporting_structure.manage',
            'employee.shift.assign.manage',
            'employee.view',
            'employee_exit.view',
            'employee_exit.initiate',
            'employee_exit.update',
            'employee_exit.asset_clearance',
            'employee_exit.fnf_process',
            'employee_exit.document_generate',
            'employee_exit.complete',
            'employee_exit.cancel',
            'employee_exit.clearance.hr',
            'employee_exit.clearance.manager',
            'employee_exit.clearance.it',
            'employee_exit.clearance.admin',
            'employee_exit.clearance.finance',
            'employee_exit.clearance.asset',
            'employee_exit.clearance.security',
            'employee_exit.clearance.accounts',
            'departments.manage',
            'designations.manage',
            'organization_hierarchy.manage',
            'probation.manage',
            'hrms_exit_policy.view',
            'hrms_exit_policy.manage',
            'hrms_exit_policy.update',
            'reporting.view',
            'reporting.structure.view',
            'reporting.structure.manage',
            'reporting.supervisor.assign',
            'reporting.employee.assign',
            'reporting.history.view',
            'document_generation.view',
            'document_generation.template_create',
            'document_generation.template_edit',
            'document_generation.generate',
            'document_generation.preview',
            'document_generation.download',
            'document_generation.email',
            'document_generation.review',
            'document_generation.delete',
            'documents.view',
            'documents.upload',
            'documents.types.manage',
            'documents.company.view',
            'company_documents.manage',
            'employee_documents.view',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('key', $employeePermissionKeys)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'permission_id' => $permissionId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        // Also sync for primary role users with system_role_id = 3
        if (class_exists(\App\Services\AccessControl\PermissionSyncService::class)) {
            app(\App\Services\AccessControl\PermissionSyncService::class)->clearRoleAndUserCaches($roleId);
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // No-op to preserve standard permissions
    }
};
