<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        // Ensure parent menu 20 "Attendance & Time Tracking" exists
        $parentAttendance = DB::table('menus')->where('id', 20)->first();
        if (! $parentAttendance) {
            DB::table('menus')->updateOrInsert(
                ['id' => 20],
                [
                    'name' => 'Attendance & Time Tracking',
                    'route' => null,
                    'icon' => 'fas fa-calendar-check',
                    'module_key' => 'attendance',
                    'parent_id' => null,
                    'sort_order' => 2,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Insert or update "Today's Attendance" menu (ID: 349)
        DB::table('menus')->updateOrInsert(
            ['route' => 'attendances.today'],
            [
                'id' => 349,
                'name' => "Today's Attendance",
                'route' => 'attendances.today',
                'icon' => 'fas fa-fingerprint',
                'module_key' => 'employee.attendance',
                'parent_id' => 20,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Insert or update "Attendance Access Control" menu (ID: 351)
        DB::table('menus')->updateOrInsert(
            ['route' => 'attendances.access-control'],
            [
                'id' => 351,
                'name' => 'Attendance Access Control',
                'route' => 'attendances.access-control',
                'icon' => 'fas fa-user-lock',
                'module_key' => 'attendance',
                'parent_id' => 20,
                'sort_order' => 10,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Auto-assign menu IDs to role_menu_access table if present
        if (Schema::hasTable('role_menu_access') && Schema::hasTable('roles')) {
            $todayMenu = DB::table('menus')->where('route', 'attendances.today')->first();
            $accessMenu = DB::table('menus')->where('route', 'attendances.access-control')->first();

            $roles = DB::table('roles')->get();
            foreach ($roles as $role) {
                $slug = strtolower((string) ($role->slug ?? $role->name ?? ''));

                // Grant Today's Attendance to all active roles
                if ($todayMenu) {
                    DB::table('role_menu_access')->updateOrInsert([
                        'role_id' => $role->id,
                        'menu_id' => $todayMenu->id,
                    ], [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Grant Access Control menu to admin / hr roles
                if ($accessMenu && in_array($slug, ['super_admin', 'admin', 'hr_admin', 'operations_admin', 'custom_admin', 'manager'], true)) {
                    DB::table('role_menu_access')->updateOrInsert([
                        'role_id' => $role->id,
                        'menu_id' => $accessMenu->id,
                    ], [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')->whereIn('route', ['attendances.today', 'attendances.access-control'])->delete();
        }
    }
};
