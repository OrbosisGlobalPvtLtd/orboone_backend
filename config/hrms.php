<?php

return [
    'emails' => [
        'hr' => env('HR_EMAIL'),
        'support' => env('SUPPORT_EMAIL'),
        'info' => env('INFO_EMAIL'),
        'noreply' => env('MAIL_FROM_ADDRESS'),
    ],
    'work_schedule_shifts' => [
        'general' => [
            'shift_code' => 'general_shift',
            'policy_name' => 'Default Attendance Policy',
        ],
        'wfh' => [
            'shift_code' => 'wfh_shift',
            'policy_name' => 'WFH Attendance Policy',
        ],
        'part_day' => [
            'shift_code' => 'part_time_shift',
            'policy_name' => 'Part Time Attendance Policy',
        ],
        'part_time' => [
            'shift_code' => 'part_time_shift',
            'policy_name' => 'Part Time Attendance Policy',
        ],
        'hourly' => [
            'shift_code' => 'half_day_shift',
            'policy_name' => 'Half Day Attendance Policy',
        ],
        'half_day' => [
            'shift_code' => 'half_day_shift',
            'policy_name' => 'Half Day Attendance Policy',
        ],
        'shift_based_morning' => [
            'shift_code' => 'half_day_morning',
            'policy_name' => 'Half Day Morning Policy',
        ],
        'half_day_morning' => [
            'shift_code' => 'half_day_morning',
            'policy_name' => 'Half Day Morning Policy',
        ],
        'shift_based_evening' => [
            'shift_code' => 'half_day_evening',
            'policy_name' => 'Half Day Evening Policy',
        ],
        'half_day_evening' => [
            'shift_code' => 'half_day_evening',
            'policy_name' => 'Half Day Evening Policy',
        ],
    ],
];

