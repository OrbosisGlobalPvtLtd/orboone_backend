<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class MarchAttendanceSeeder extends Seeder
{
    public function run()
    {
        $e = Employee::find(2);
        if (!$e) return;
        
        $start = Carbon::create(2026, 3, 1);
        for ($i = 0; $i < 31; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            Attendance::updateOrCreate(
                ['user_id' => $e->user_id, 'date' => $date],
                ['status' => 'Present', 'clock_in' => '09:00:00', 'clock_out' => '18:00:00']
            );
        }
    }
}
