<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('menus') && ! Schema::hasColumn('menus', 'permission_key')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->string('permission_key', 150)->nullable()->after('module_key');
            });
        }

        // Map existing menu routes/modules to default permission keys
        $menuPermissionMap = [
            'employee.shift-assignment.index' => 'employee.shift.assign.manage',
            'attendances.today' => 'attendance.my.view',
            'attendance.policies.index' => 'attendance.rules.manage',
            'attendance.rules.index' => 'attendance.rules.manage',
            'attendances.access-control' => 'attendance.access_control.manage',
            'documents.compliance.index' => 'documents.compliance.view',
            'documents.verification.index' => 'documents.verification.view',
            'documents.types.index' => 'documents.types.manage',
            'documents.policies.index' => 'documents.company.view',
            'hrms.documents.self.index' => 'documents.upload.self',
            'hrms.document-generation.dashboard' => 'document_generation.view',
            'hrms.document-generation.self.index' => 'document_generation.view',
            'settings.hrms_exit_policies.index' => 'hrms_exit_policy.view',
            'settings.system.index' => 'settings.system.manage',
            'settings.company.index' => 'settings.company.manage',
            'settings.branding.index' => 'settings.branding.view',
            'hrms.mobile-app-versions.index' => 'mobile_app_versions.view',
            'roles.index' => 'roles.manage',
            'permissions.index' => 'permissions.manage',
            'admins.index' => 'admins.manage',
            'hrms.attendance.work-reports' => 'attendance.work_reports.view_all',
            'hrms.attendance.my-work-reports' => 'attendance.work_reports.view_own',
            'enterprise-payroll.policies.index' => 'enterprise_payroll.policy.view',
            'hrms.organization.index' => 'departments.manage',
            'hrms.attendance.wfh.index' => 'attendance.wfh.view',
            'hrms.attendance.my-wfh.index' => 'attendance.wfh.own',
            'hrms.employees.index' => 'employees.view',
            'hrms.employees.create' => 'employees.create',
            'hrms.employees.pending_profiles' => 'employees_pending_profiles.view',
            'hrms.employees.probation_internship' => 'employees_probation_internship.view',
            'hrms.employees.exit' => 'employees_exit.view',
            'hrms.employees.reporting_structure' => 'employees_reporting_structure.view',
            'attendances.index' => 'attendance.dashboard.view',
            'attendances.record' => 'attendance.records.view_all',
            'attendances.pending-approval' => 'attendance.blocked.view',
            'attendance.types.index' => 'attendance.types.manage',
            'attendances.monthly-report' => 'attendance.monthly_report.view',
            'attendances.export-pdf' => 'attendance.export.view',
            'hrms.attendance.regularizations.index' => 'attendance.regularization.view',
            'hrms.attendance.holiday_work.index' => 'attendance.holiday_work.view',
            'hrms.leave.dashboard' => 'leave.dashboard.view',
            'leave-requests.index' => 'leave.my_requests.view',
            'leave-approvals.index' => 'leave.approvals.view_all',
            'hrms.leave.balances.index' => 'leave.balance.view_all',
            'leave-allocations.index' => 'leave.allocation.manage',
            'hrms.leave.history' => 'leave.history.view',
            'announcements.index' => 'announcements.view',
            'employee.announcements.index' => 'announcements.view_own',
            'hrms.assets.index' => 'asset.view',
            'projects.index' => 'projects.view_all',
            'projects.my' => 'projects.my_projects.view',
            'projects.tasks.index' => 'projects.tasks.view',
        ];

        foreach ($menuPermissionMap as $route => $permKey) {
            DB::table('menus')
                ->where('route', $route)
                ->update(['permission_key' => $permKey]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('menus') && Schema::hasColumn('menus', 'permission_key')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropColumn('permission_key');
            });
        }
    }
};
