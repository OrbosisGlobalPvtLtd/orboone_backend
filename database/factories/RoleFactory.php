<?php

namespace Database\Factories;

use App\Models\Core\RoleM;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoleM::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->word();
        return [
            'name' => ucfirst($name),
            'slug' => strtolower($name) . '_' . uniqid(),
            'is_system' => false,
            'status' => true,
        ];
    }

    public function admin() {
        return $this->state(function($attributes) {
            return [
                'name' => 'Administrator',
                'slug' => 'admin',
                'is_system' => true,
                'status' => true,
            ];
        });
    }

    public function user() {
        return $this->state(function($attributes) {
            return [
                'name' => 'Employee',
                'slug' => 'employee',
                'is_system' => true,
                'status' => true,
            ];
        });
    }
}
