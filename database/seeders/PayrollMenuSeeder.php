<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Core\MenuM as Menu;
use App\Models\Core\RoleM as Role;
use App\Models\Core\AccessM as Access;

class PayrollMenuSeeder extends Seeder
{
    public function run()
    {
        $m = Menu::updateOrCreate(['name' => 'payroll'], ['is_active' => 1]);
        foreach (Role::all() as $r) {
            Access::updateOrCreate(
                ['role_id' => $r->id, 'menu_id' => $m->id],
                ['status' => 2]
            );
        }
    }
}
