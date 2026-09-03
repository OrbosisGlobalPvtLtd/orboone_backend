<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Correct the permission_key values on Employee Management submenu items
     * so they match the actual permission keys seeded in the permissions table.
     */
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $corrections = [
            'hrms.employees.pending_profiles' => 'employees.pending_profiles.view',
            'hrms.employees.probation_internship' => 'employees.probation_internship.view',
            'hrms.employees.exit' => 'employees.exit.view',
            'hrms.employees.reporting_structure' => 'employees.reporting_structure.manage',
        ];

        foreach ($corrections as $route => $permissionKey) {
            DB::table('menus')
                ->where('route', $route)
                ->update(['permission_key' => $permissionKey]);
        }
    }

    /**
     * Reverse the permission key corrections.
     */
    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $reversals = [
            'hrms.employees.pending_profiles' => 'employees_pending_profiles.view',
            'hrms.employees.probation_internship' => 'employees_probation_internship.view',
            'hrms.employees.exit' => 'employees_exit.view',
            'hrms.employees.reporting_structure' => 'employees_reporting_structure.view',
        ];

        foreach ($reversals as $route => $permissionKey) {
            DB::table('menus')
                ->where('route', $route)
                ->update(['permission_key' => $permissionKey]);
        }
    }
};
