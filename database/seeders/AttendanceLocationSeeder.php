<?php

namespace Database\Seeders;

use App\Models\HRMS\Attendance\AttendanceLocationM;
use Illuminate\Database\Seeder;

class AttendanceLocationSeeder extends Seeder
{
    public function run()
    {
        AttendanceLocationM::updateOrCreate(
            ['code' => 'mumbai_office'],
            [
                'name' => 'Mumbai Office',
                'latitude' => 19.0760000,
                'longitude' => 72.8777000,
                'radius_meters' => 100,
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}
