<?php

namespace Database\Factories;

use App\Models\HRMS\Department\DepartmentM as Department;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Employee\PositionM as Position;
use App\Models\Core\UserM as User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => function() {
                return User::factory()->create()->id;
            },
            'name' => $this->faker->name(),
            'start_of_contract' => $this->faker->date(),
            'end_of_contract' => $this->faker->date(),
            'department_id' => function() {
                return Department::factory()->create()->id;
            },
            'position_id' => function() {
                return Position::factory()->create()->id;
            }
        ];
    }
}
