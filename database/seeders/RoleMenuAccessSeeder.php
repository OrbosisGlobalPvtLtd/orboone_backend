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
            30,31,32,33,34,35,
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
            35,     // Holiday List

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
    }
}