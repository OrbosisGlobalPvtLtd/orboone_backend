<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Resolve Employee Role ID
        $employeeRoleId = DB::table('roles')->where('name', 'Employee')->value('id')
            ?? DB::table('roles')->where('name', 'employee')->value('id');

        // 1. My Assets Menu under Assets (parent: 330)
        $assetsMenuId = null;
        $existingAssetsMenu = DB::table('menus')->where('route', 'hrms.employee.assets.index')->first();
        if ($existingAssetsMenu) {
            $assetsMenuId = $existingAssetsMenu->id;
        } else {
            $maxSortAssets = DB::table('menus')->where('parent_id', 330)->max('sort_order') ?? 0;
            $assetsMenuId = DB::table('menus')->insertGetId([
                'parent_id' => 330,
                'name' => 'My Assets',
                'route' => 'hrms.employee.assets.index',
                'module_key' => 'employee.assets',
                'sort_order' => $maxSortAssets + 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. My Holiday Work Menu under Attendance (parent: 20)
        $holidayMenuId = null;
        $existingHolidayMenu = DB::table('menus')->where('route', 'hrms.attendance.my-holiday-work.index')->first();
        if ($existingHolidayMenu) {
            $holidayMenuId = $existingHolidayMenu->id;
        } else {
            $maxSortAttendance = DB::table('menus')->where('parent_id', 20)->max('sort_order') ?? 0;
            $holidayMenuId = DB::table('menus')->insertGetId([
                'parent_id' => 20,
                'name' => 'My Holiday Work',
                'route' => 'hrms.attendance.my-holiday-work.index',
                'module_key' => 'hrms.attendance.my-holiday-work.index',
                'sort_order' => $maxSortAttendance + 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Map role access
        if ($employeeRoleId && Schema::hasTable('role_menu_access')) {
            DB::table('role_menu_access')->insertOrIgnore([
                ['role_id' => $employeeRoleId, 'menu_id' => $assetsMenuId],
                ['role_id' => $employeeRoleId, 'menu_id' => $holidayMenuId],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $assetsMenuId = DB::table('menus')->where('route', 'hrms.employee.assets.index')->value('id');
        $holidayMenuId = DB::table('menus')->where('route', 'hrms.attendance.my-holiday-work.index')->value('id');

        if ($assetsMenuId) {
            DB::table('role_menu_access')->where('menu_id', $assetsMenuId)->delete();
            DB::table('menus')->where('id', $assetsMenuId)->delete();
        }

        if ($holidayMenuId) {
            DB::table('role_menu_access')->where('menu_id', $holidayMenuId)->delete();
            DB::table('menus')->where('id', $holidayMenuId)->delete();
        }
    }
};
