<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            [
                'id' => 1,
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'System default role with full access across all modules and departments.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Core admin role with broad management access.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 3,
                'name' => 'HR Admin',
                'slug' => 'hr_admin',
                'description' => 'HR module management role for employee, attendance, leave, documents, announcements, and limited payroll visibility.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Finance Admin',
                'slug' => 'finance_admin',
                'description' => 'Finance and payroll-focused admin role.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 5,
                'name' => 'Project Admin',
                'slug' => 'project_admin',
                'description' => 'Project Management module admin role for future expansion.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 6,
                'name' => 'Operations Admin',
                'slug' => 'operations_admin',
                'description' => 'Operations-focused admin role for future module access and workflow control.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 7,
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Employee role with self-service access only.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 8,
                'name' => 'Custom Admin',
                'slug' => 'custom_admin',
                'description' => 'Dynamic role for future custom permission-based admin creation.',
                'is_system' => 1,
                'status' => 1,
            ],
            [
                'id' => 9,
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Manager role for team attendance, leave approvals, and team reports.',
                'is_system' => 1,
                'status' => 1,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                [
                    'name' => $role['name'],
                    'slug' => $role['slug'],
                    'description' => $role['description'],
                    'is_system' => $role['is_system'],
                    'status' => $role['status'],
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
