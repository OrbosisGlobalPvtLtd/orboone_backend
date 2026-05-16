<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleMenuAccessSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Role IDs
        |--------------------------------------------------------------------------
        | 1 = Super Admin
        | 2 = Admin / HR Admin
        | 3 = HR / Manager
        | 7 = Employee
        */

        $adminRoles = [1, 2, 3];

        // Admin / HR full access
        $adminMenuIds = [
            1,
            10,11,12,13,15,16,17,18,
            20,21,22,23,24,
            30,31,32,33,34,35,130,131,132,133,
            40,41,42,43,44,45,
            50,51,52,53,
            60,
            70,71,72,73,74,75,76,
            80,81,82,
            90,91,92,
        ];

        // Employee limited access
        $employeeRoleId = 7;

        $employeeMenuIds = [
            1,      // Dashboard

            20,     // Attendance & Tracking
            21,     // Attendance Marking
            22,     // Task Tracking

            30,     // Leave Management
            32,     // Apply for Leave
            34,     // Balance Tracker
            132,    // Holiday List

            40,     // Payroll Management
            43,     // My Salary Slips

            50,     // Document Management
            52,     // Upload Documents
            53,     // Company Documents

            60,     // Notice / Announcement
        ];

        foreach ($adminRoles as $roleId) {
            foreach ($adminMenuIds as $menuId) {
                DB::table('role_menu_access')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'menu_id' => $menuId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        foreach ($employeeMenuIds as $menuId) {
            DB::table('role_menu_access')->updateOrInsert(
                [
                    'role_id' => $employeeRoleId,
                    'menu_id' => $menuId,
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $allMenuIds = DB::table('menus')->where('is_active', 1)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $roleIdsBySlug = DB::table('roles')->pluck('id', 'slug')->toArray();

        $roleMenus = [
            'super_admin' => $allMenuIds,
            'hr_admin' => [
                1,
                10,11,12,13,15,16,17,18,
                20,21,22,145,23,28,29,26,134,135,24,25,136,152,153,27,
                30,31,32,137,33,146,34,35,130,131,132,138,133,139,140,
                50,51,52,53,148,149,
                60,
                80,83,143,144,150,151,
            ],
            'manager' => [
                1,
                10,11,
                20,21,145,28,26,
                30,31,32,137,33,146,34,133,
                50,52,53,
                80,83,
            ],
            'finance_admin' => [
                1,
                10,11,
                20,26,134,
                30,34,140,
                40,42,41,141,142,43,44,45,147,
                50,53,
                80,83,
            ],
            'employee' => [
                1,
                20,145,28,26,
                30,32,137,34,133,
                40,43,
                50,52,53,
                60,
                80,83,
            ],
        ];

        foreach (array_keys($roleMenus) as $slug) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if ($roleId) {
                DB::table('role_menu_access')->where('role_id', $roleId)->delete();
            }
        }

        foreach ($roleMenus as $slug => $menuIds) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach (array_unique($menuIds) as $menuId) {
                DB::table('role_menu_access')->updateOrInsert(
                    ['role_id' => $roleId, 'menu_id' => $menuId],
                    ['updated_at' => $now, 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
                );
            }
        }
    }
}
