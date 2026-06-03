<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // 1. Create or Update Company Branding Menu
        DB::table('menus')->updateOrInsert(
            ['id' => 84],
            [
                'name' => 'Company Branding',
                'route' => 'settings.branding.index',
                'icon' => 'fas fa-palette',
                'module_key' => 'settings',
                'parent_id' => 80, // Settings Category Parent
                'sort_order' => 9,
                'is_active' => 1,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        // 2. Register Permissions
        $permissions = [
            [
                'key' => 'settings.branding.view',
                'module' => 'settings',
                'submodule' => 'branding',
                'action' => 'view',
                'description' => 'View dynamic portal branding and UI variables configuration',
            ],
            [
                'key' => 'settings.branding.update',
                'module' => 'settings',
                'submodule' => 'branding',
                'action' => 'update',
                'description' => 'Modify corporate colors, identity, logo and favicon configuration settings',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $permission['key']],
                [
                    'module' => $permission['module'],
                    'submodule' => $permission['submodule'],
                    'action' => $permission['action'],
                    'description' => $permission['description'],
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        // 3. Associate Menu Access for Admin Roles
        $roleIdsBySlug = DB::table('roles')->pluck('id', 'slug')->toArray();
        $adminRoles = ['super_admin', 'admin', 'hr_admin'];

        foreach ($adminRoles as $slug) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if ($roleId) {
                DB::table('role_menu_access')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'menu_id' => 84, // Company Branding Menu ID
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }
        }

        // 4. Associate Permissions with Admin Roles
        $permissionIds = DB::table('permissions')
            ->whereIn('key', ['settings.branding.view', 'settings.branding.update'])
            ->pluck('id', 'key')
            ->toArray();

        // super_admin and admin get both permissions
        $fullAccessRoles = ['super_admin', 'admin'];
        foreach ($fullAccessRoles as $slug) {
            $roleId = $roleIdsBySlug[$slug] ?? null;
            if ($roleId) {
                foreach ($permissionIds as $permId) {
                    DB::table('role_permissions')->updateOrInsert(
                        [
                            'role_id' => $roleId,
                            'permission_id' => $permId,
                        ],
                        [
                            'updated_at' => $now,
                            'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                        ]
                    );
                }
            }
        }

        // hr_admin gets view-only access
        $hrAdminRoleId = $roleIdsBySlug['hr_admin'] ?? null;
        $viewPermId = $permissionIds['settings.branding.view'] ?? null;
        if ($hrAdminRoleId && $viewPermId) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role_id' => $hrAdminRoleId,
                    'permission_id' => $viewPermId,
                ],
                [
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
