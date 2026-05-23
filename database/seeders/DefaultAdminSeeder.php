<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $defaultAdmins = [
            'super_admin' => [
                'name' => 'Super Admin',
                'email' => 'superadmin@orbosis.com',
                'password' => 'Orbosis@26',
            ],

            'admin' => [
                'name' => 'Admin',
                'email' => 'admin@orbosis.com',
                'password' => 'Admin@26',
            ],

            'hr_admin' => [
                'name' => 'HR Admin',
                'email' => 'hradmin@orbosis.com',
                'password' => 'Hr@26Admin',
            ],

            'finance_admin' => [
                'name' => 'Finance Admin',
                'email' => 'financeadmin@orbosis.com',
                'password' => 'Fin@26Admin',
            ],

            'project_admin' => [
                'name' => 'Project Admin',
                'email' => 'projectadmin@orbosis.com',
                'password' => 'Proj@26Admin',
            ],

            'operations_admin' => [
                'name' => 'Operations Admin',
                'email' => 'operationsadmin@orbosis.com',
                'password' => 'Ops@26Admin',
            ],

            'custom_admin' => [
                'name' => 'Custom Admin',
                'email' => 'customadmin@orbosis.com',
                'password' => 'Cust@26Admin',
            ],

            'manager' => [
                'name' => 'Manager',
                'email' => 'manager@orbosis.com',
                'password' => 'Mng@26Admin',
            ],
        ];

        foreach ($defaultAdmins as $roleSlug => $admin) {

            $role = DB::table('roles')
                ->where('slug', $roleSlug)
                ->where('status', 1)
                ->first();

            if (!$role) {
                continue;
            }

            DB::table('users')->updateOrInsert(
                ['email' => $admin['email']],
                [
                    'system_role_id' => $role->id,
                    'name' => $admin['name'],
                    'phone' => null,
                    'email_verified_at' => $now,
                    'password' => Hash::make($admin['password']),
                    'fcm_token' => null,
                    'must_change_password' => 1,
                    'is_active' => 1,
                    'is_web_access' => 1,
                    'is_app_access' => 0,
                    'remember_token' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
