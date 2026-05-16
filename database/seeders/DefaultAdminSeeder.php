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

        $defaultPassword = 'Orbosis@26';

        $admins = [
            [
                'id' => 1,
                'system_role_id' => 1,
                'name' => 'Super Admin',
                'email' => 'superadmin@orbosis.com',
            ],
            [
                'id' => 2,
                'system_role_id' => 2,
                'name' => 'Admin',
                'email' => 'admin@orbosis.com',
            ],
            [
                'id' => 3,
                'system_role_id' => 3,
                'name' => 'HR Admin',
                'email' => 'hradmin@orbosis.com',
            ],
        ];

        foreach ($admins as $admin) {
            DB::table('users')->updateOrInsert(
                [
                    'email' => $admin['email'],
                ],
                [
                    'system_role_id' => $admin['system_role_id'],
                    'name' => $admin['name'],
                    'phone' => null,
                    'email_verified_at' => $now,
                    'password' => Hash::make($defaultPassword),
                    'fcm_token' => null,
                    'must_change_password' => 0,
                    'is_active' => 1,
                    'is_web_access' => 1,
                    'is_app_access' => 1,
                    'remember_token' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
