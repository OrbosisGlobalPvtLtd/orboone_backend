<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | Role IDs
        |--------------------------------------------------------------------------
        | 1 = super_admin
        | 2 = admin
        | 3 = hr_admin
        | 4 = finance_admin
        | 5 = project_admin
        | 6 = operations_admin
        | 7 = employee
        | 8 = custom_admin
        */

        /*
        |--------------------------------------------------------------------------
        | Permission IDs from PermissionSeeder
        |--------------------------------------------------------------------------
        */

        $mapping = [

            // ============================================================
            // SUPER ADMIN -> ALL PERMISSIONS
            // ============================================================
            1 => range(1, 52),

            // ============================================================
            // ADMIN -> Broad HRMS admin access
            // ============================================================
            2 => [
                // dashboard
                1,

                // employee management
                2, 3, 4, 5, 6, 7, 8, 12, 13, 10, 11, 12,

                // self profile (optional if admin is also employee)
                13, 14,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,

                // payroll
                29, 30, 31, 32, 33, 34, 35,

                // documents
                36, 37, 38, 39, 40, 41,

                // announcements
                42, 43, 44, 45,
            ],

            // ============================================================
            // HR ADMIN -> HR focused access
            // ============================================================
            3 => [
                // dashboard
                1,

                // employee management
                2, 3, 4, 6, 7, 8, 9, 10, 11, 12,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,

                // payroll (limited)
                29, 31, 32, 35,

                // documents
                36, 37, 38, 39, 40, 41,

                // announcements
                42, 43, 44, 45,
            ],

            // ============================================================
            // FINANCE ADMIN -> payroll heavy, limited HR
            // ============================================================
            4 => [
                // dashboard
                1,

                // payroll
                29, 30, 31, 32, 33, 34, 35,

                // limited employee/payroll reference
                2, 7,

                // announcements view
                42, 45,
            ],

            // ============================================================
            // PROJECT ADMIN -> future module base
            // ============================================================
            5 => [
                1,     // dashboard
                51,    // project_management.view
                42, 45 // announcements
            ],

            // ============================================================
            // OPERATIONS ADMIN -> attendance + leave heavy
            // ============================================================
            6 => [
                1,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,

                // employee directory/basic
                2, 7, 11,

                // announcements
                42, 45,
            ],

            // ============================================================
            // EMPLOYEE -> self-service only
            // ============================================================
            7 => [
                1,      // dashboard.view

                // self profile
                13, 14,

                // directory
                7,

                // attendance self
                15, 20,

                // leave self
                23, 28,

                // payroll self
                35,

                // documents self
                40, 41,

                // announcements self
                45,
            ],

            // ============================================================
            // CUSTOM ADMIN -> start with HR-admin like base
            // later super admin can customize through UI
            // ============================================================
            8 => [
                1,

                // employee
                2, 3, 4, 6, 7, 8, 9, 10, 11, 12,

                // attendance
                15, 16, 17, 18, 19, 20, 21,

                // leave
                22, 23, 24, 25, 26, 27, 28,

                // payroll limited
                29, 31, 32, 35,

                // documents
                36, 37, 38, 39, 40, 41,

                // announcements
                42, 43, 44, 45,
            ],
        ];

        $rows = [];
        $id = 1;

        foreach ($mapping as $roleId => $permissionIds) {
            foreach (array_unique($permissionIds) as $permissionId) {
                $rows[] = [
                    'id' => $id++,
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach ($rows as $row) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role_id' => $row['role_id'],
                    'permission_id' => $row['permission_id'],
                ],
                [
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}