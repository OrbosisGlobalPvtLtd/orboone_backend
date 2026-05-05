<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing holidays
        \App\Models\HRMS\Leave\HolidayM::truncate();

        $holidays = [
            ['name' => 'Makar Sankrant', 'date' => '2026-01-14'],
            ['name' => 'Republic Day', 'date' => '2026-01-26'],
            ['name' => 'Holi', 'date' => '2026-03-04'],
            ['name' => 'Labour day', 'date' => '2026-05-01'],
            ['name' => 'Independence Day', 'date' => '2026-08-15'],
            ['name' => 'Ganesha Chaturthi', 'date' => '2026-09-14'],
            ['name' => 'Gandhi Jayanti', 'date' => '2026-10-02'],
            ['name' => 'Dussehra', 'date' => '2026-10-20'],
            ['name' => 'Diwali', 'date' => '2026-11-07'],
            ['name' => 'Diwali', 'date' => '2026-11-09'],
            ['name' => 'Christmas', 'date' => '2026-12-25'],
        ];

        foreach ($holidays as $holiday) {
            \App\Models\HRMS\Leave\HolidayM::create($holiday);
        }
    }
}
