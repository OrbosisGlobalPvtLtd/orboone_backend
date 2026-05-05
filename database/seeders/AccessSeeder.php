<?php

namespace Database\Seeders;

use App\Models\Core\AccessM as Access;
use App\Models\Core\AdminM as Admin;
use App\Models\Core\MenuM as Menu;
use App\Models\Core\RoleM as Role;
use Illuminate\Database\Seeder;

class AccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = Menu::all();
        $adminId = Role::whereName('Administrator')->first()->id;
        $userId = Role::whereName('User')->first()->id;

        foreach($menus as $menu) {
            Access::factory()->create(['role_id' => $adminId, 'menu_id' => $menu->id]);
            Access::factory()->create(['role_id' => $userId, 'menu_id' => $menu->id]);
        }

        Admin::create(['role_id' => $adminId]);
    }
}
