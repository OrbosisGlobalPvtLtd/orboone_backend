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

        $explicitObsoleteIds = [54, 55, 56, 57, 76, 91, 93, 152, 153];

        // Also resolve semantically matching obsolete menu records
        $semanticObsoleteIds = DB::table('menus')
            ->whereIn('route', [
                'hrms.documents.types.index',
                'hrms.documents.company.index',
                'hrms.documents.expiring',
                'hrms.documents.self.index',
                'settings.notification-retention.index',
                'module.project-mgmt',
            ])
            ->orWhere(function ($query) {
                $query->where('route', 'hrms.holidays.index')
                    ->where('parent_id', 20);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allObsoleteIds = array_values(array_unique(array_merge($explicitObsoleteIds, $semanticObsoleteIds)));

        if (! empty($allObsoleteIds)) {
            // Delete dependent records in relationship tables
            if (Schema::hasTable('role_menu_access')) {
                DB::table('role_menu_access')->whereIn('menu_id', $allObsoleteIds)->delete();
            }

            if (Schema::hasTable('accesses')) {
                DB::table('accesses')->whereIn('menu_id', $allObsoleteIds)->delete();
            }

            // Delete child menus first if any exist
            DB::table('menus')->whereIn('id', $allObsoleteIds)->whereNotNull('parent_id')->delete();

            // Delete obsolete menus
            DB::table('menus')->whereIn('id', $allObsoleteIds)->delete();
        }

        // Correct route definitions for active role management menus
        DB::table('menus')
            ->where('id', 74)
            ->orWhere(function ($q) {
                $q->where('name', 'Role Permission Mapping')->where('route', 'roles.index');
            })
            ->update([
                'route' => 'role_permissions.index',
                'updated_at' => now(),
            ]);

        DB::table('menus')
            ->where('id', 75)
            ->orWhere(function ($q) {
                $q->where('name', 'Role Menu Access')->where('route', 'roles.index');
            })
            ->update([
                'route' => 'role_menus.index',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obsolete/duplicate menus are permanently retired; rollback does not recreate obsolete records.
    }
};
