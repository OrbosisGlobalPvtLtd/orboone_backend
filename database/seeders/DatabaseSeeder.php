<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DefaultAdminSeeder::class,
            EmployeeSeeder::class,
            AttendanceSeeder::class,
            AttendanceTypeSeeder::class,
            AttendanceTimeSeeder::class,
            AttendanceLocationSeeder::class,
            AttendancePolicyRuleSeeder::class,
            MenuSeeder::class,
            AccessSeeder::class,
            ScoreCategorySeeder::class,
            DocumentTypeSeeder::class,
            HolidaySeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            DepartmentSeeder::class,
            RolePermissionSeeder::class,
            DepartmentModuleAccessSeeder::class,
            LeavePolicySeeder::class,
            LeaveTypeSeeder::class,
            WeekoffRuleSeeder::class,
            MenuSeeder::class,
            RoleMenuAccessSeeder::class,
            BrandingSettingsSeeder::class,
        ]);
    }
}
