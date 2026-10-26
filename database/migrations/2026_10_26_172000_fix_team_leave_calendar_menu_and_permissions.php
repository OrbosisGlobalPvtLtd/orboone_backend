<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix Team Leave Calendar menu permission key and role access.
     */
    public function up(): void
    {
        // 1. Update menus table permission_key for Team Leave Calendar
        if (Schema::hasTable('menus')) {
            DB::table('menus')
                ->where('route', 'hrms.leave.team_calendar.index')
                ->orWhere('id', 146)
                ->update(['permission_key' => 'leave.team_calendar.view']);
        }

        // 2. Resolve permission ID for leave.team_calendar.view
        $permId = null;
        if (Schema::hasTable('permissions')) {
            $perm = DB::table('permissions')->where('key', 'leave.team_calendar.view')->first();
            if (! $perm) {
                $permId = DB::table('permissions')->insertGetId([
                    'module' => 'hrms',
                    'submodule' => 'leave_team_calendar',
                    'action' => 'view',
                    'key' => 'leave.team_calendar.view',
                    'name' => 'View team leave calendar',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $permId = $perm->id;
            }
        }

        // Resolve menu ID
        $menuId = 146;
        if (Schema::hasTable('menus')) {
            $menu = DB::table('menus')->where('route', 'hrms.leave.team_calendar.index')->first();
            if ($menu) {
                $menuId = (int) $menu->id;
            }
        }

        if (Schema::hasTable('roles')) {
            // 3. Remove from Employee role
            $employeeRole = DB::table('roles')->where('slug', 'employee')->first();
            if ($employeeRole) {
                if ($permId && Schema::hasTable('role_permissions')) {
                    DB::table('role_permissions')
                        ->where('role_id', $employeeRole->id)
                        ->where('permission_id', $permId)
                        ->delete();
                }

                if (Schema::hasTable('role_menu_access')) {
                    DB::table('role_menu_access')
                        ->where('role_id', $employeeRole->id)
                        ->where('menu_id', $menuId)
                        ->delete();
                }
            }

            // 4. Ensure Super Admin, Admin, HR Admin, Manager have access
            $targetSlugs = ['super_admin', 'admin', 'hr_admin', 'manager'];
            $managementRoles = DB::table('roles')->whereIn('slug', $targetSlugs)->get();

            foreach ($managementRoles as $role) {
                if ($permId && Schema::hasTable('role_permissions')) {
                    DB::table('role_permissions')->updateOrInsert(
                        ['role_id' => $role->id, 'permission_id' => $permId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }

                if (Schema::hasTable('role_menu_access')) {
                    DB::table('role_menu_access')->updateOrInsert(
                        ['role_id' => $role->id, 'menu_id' => $menuId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // 5. Invalidate menu caches
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            // Ignore if cache flush fails during migrate
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')
                ->where('route', 'hrms.leave.team_calendar.index')
                ->orWhere('id', 146)
                ->update(['permission_key' => null]);
        }
    }
};
