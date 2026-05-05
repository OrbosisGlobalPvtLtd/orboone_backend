<?php

namespace Database\Factories;

use App\Models\Core\AccessM as Access;
use App\Models\Core\MenuM as Menu;
use App\Models\Core\RoleM as Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Access::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role_id' => function () {
                return Role::factory()->create()->id;
            },
            'menu_id' => function () {
                return Menu::factory()->create()->id;
            },
            'status' => 2
        ];
    }
}
