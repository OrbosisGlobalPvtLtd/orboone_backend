<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $legacyMenuIds = [40, 41, 42, 43, 44, 45, 141, 142, 147, 155];

        // Find any additional legacy payroll menus by module_key or route prefix
        $additionalIds = DB::table('menus')
            ->where('parent_id', 40)
            ->orWhere('module_key', 'payroll')
            ->orWhere('route', 'like', 'pages.payroll.%')
            ->orWhere('route', 'like', 'hrms.payroll.%')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allIds = array_values(array_unique(array_merge($legacyMenuIds, $additionalIds)));

        if (! empty($allIds)) {
            // Delete dependent records in relationship tables if present
            if (Schema::hasTable('role_menu_access')) {
                DB::table('role_menu_access')->whereIn('menu_id', $allIds)->delete();
            }

            if (Schema::hasTable('accesses')) {
                DB::table('accesses')->whereIn('menu_id', $allIds)->delete();
            }

            // Delete child menus first to satisfy foreign key / parent constraints
            DB::table('menus')->whereIn('id', $allIds)->whereNotNull('parent_id')->delete();

            // Delete parent legacy menus
            DB::table('menus')->whereIn('id', $allIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy Payroll is permanently retired; rollback does not re-create legacy records.
    }
};
