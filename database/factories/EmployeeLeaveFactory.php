<?php

namespace Database\Factories;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeLeaveFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmployeeLeave::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'employee_id' => function () {
                return Employee::factory()->create()->id;
            },
            'leaves_quota' => 12,
            'used_leaves' => 0
        ];
    }
}
