<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | NOTE
        |--------------------------------------------------------------------------
        | Update year according to requirement.
        | Example below uses 2026.
        */

        $holidays = [

            [
                'title' => 'Makar Sankrant',
                'holiday_date' => '2026-01-14',
                'holiday_type' => 'national',
                'is_national' => 1,
            ],

            [
                'title' => 'Republic Day',
                'holiday_date' => '2026-01-26',
                'holiday_type' => 'national',
                'is_national' => 1,
            ],

            [
                'title' => 'Holi',
                'holiday_date' => '2026-03-04',
                'holiday_type' => 'festival',
                'is_national' => 0,
            ],

            [
                'title' => 'Independence Day',
                'holiday_date' => '2026-08-15',
                'holiday_type' => 'national',
                'is_national' => 1,
            ],

            [
                'title' => 'Ganesha Chaturthi',
                'holiday_date' => '2026-09-14',
                'holiday_type' => 'festival',
                'is_national' => 0,
            ],

            [
                'title' => 'Gandhi Jayanti',
                'holiday_date' => '2026-10-02',
                'holiday_type' => 'national',
                'is_national' => 1,
            ],

            [
                'title' => 'Dussehra',
                'holiday_date' => '2026-10-20',
                'holiday_type' => 'festival',
                'is_national' => 0,
            ],

            [
                'title' => 'Diwali',
                'holiday_date' => '2026-11-07',
                'holiday_type' => 'festival',
                'is_national' => 0,
            ],

            [
                'title' => 'Diwali Holiday',
                'holiday_date' => '2026-11-09',
                'holiday_type' => 'festival',
                'is_national' => 0,
            ],

            [
                'title' => 'Christmas',
                'holiday_date' => '2026-12-25',
                'holiday_type' => 'festival',
                'is_national' => 0,
            ],

        ];

        foreach ($holidays as $holiday) {

            DB::table('holidays')->updateOrInsert(

                [
                    'holiday_date' => $holiday['holiday_date'],
                ],

                [
                    'title'                    => $holiday['title'],
                    'holiday_type'             => $holiday['holiday_type'],
                    'is_national'              => $holiday['is_national'],
                    'is_optional'              => 0,
                    'is_working_day_override'  => 0,
                    'is_active'                => 1,
                    'created_by_user_id'       => 1,
                    'updated_at'               => $now,
                    'created_at'               => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
