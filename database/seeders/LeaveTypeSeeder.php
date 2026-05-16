<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $types = [
            ['name' => 'Paid Leave', 'code' => 'paid_leave', 'is_paid' => 1, 'is_sick' => 0, 'is_lwp' => 0, 'is_comp_off' => 0, 'color' => '#12B76A', 'applicable_after_confirmation' => 1],
            ['name' => 'Sick Leave', 'code' => 'sick_leave', 'is_paid' => 1, 'is_sick' => 1, 'is_lwp' => 0, 'is_comp_off' => 0, 'color' => '#F79009', 'applicable_after_confirmation' => 1],
            ['name' => 'Comp Off', 'code' => 'comp_off', 'is_paid' => 1, 'is_sick' => 0, 'is_lwp' => 0, 'is_comp_off' => 1, 'color' => '#4B00E8', 'applicable_after_confirmation' => 0],
            ['name' => 'Leave Without Pay', 'code' => 'lwp', 'is_paid' => 0, 'is_sick' => 0, 'is_lwp' => 1, 'is_comp_off' => 0, 'color' => '#D92D20', 'applicable_after_confirmation' => 0],
        ];

        foreach ($types as $type) {
            DB::table('leave_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'requires_attachment' => 0,
                    'medical_certificate_after_days' => $type['code'] === 'sick_leave' ? 2 : null,
                    'max_days_per_month' => in_array($type['code'], ['paid_leave', 'sick_leave'], true) ? 2 : null,
                    'max_days_per_request' => $type['code'] === 'lwp' ? null : 15,
                    'allow_half_day' => true,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ])
            );
        }
    }
}
