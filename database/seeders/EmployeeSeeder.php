<?php

namespace Database\Seeders;

use App\Models\HRMS\Announcement\AnnouncementM as Announcement;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Employee\EmployeeProfileM as EmployeeDetail;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use App\Models\HRMS\Employee\RecruitmentM as Recruitment;
use App\Models\Core\UserM as User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->administrator()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id, 'name' => $user->name]);
        EmployeeDetail::factory()->create(['employee_id' => $employee->id, 'name' => $employee->name, 'email' => $user->email]);
        EmployeeLeave::factory()->create(['employee_id' => $employee->id]);
        
        Announcement::factory(10)->create(['created_by' => $employee->id]);
        Recruitment::factory(10)->create(['position_id' => $employee->position_id]);

        $user = User::factory()->user()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id, 'name' => $user->name]);
        EmployeeDetail::factory()->create(['employee_id' => $employee->id, 'name' => $employee->name, 'email' => $user->email]);
        EmployeeLeave::factory()->create(['employee_id' => $employee->id]);
    }
}
