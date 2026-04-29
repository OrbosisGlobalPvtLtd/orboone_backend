<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Access;

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
