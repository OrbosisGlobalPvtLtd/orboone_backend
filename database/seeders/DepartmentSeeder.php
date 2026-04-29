<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $departments = [
            [
                'id' => 1,
                'name' => 'Web Development',
                'code' => '001',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Mobile App Development',
                'code' => '002',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Sales',
                'code' => '003',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Digital Marketing',
                'code' => '004',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Human Resources',
                'code' => '005',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'name' => 'Business Development',
                'code' => '006',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'name' => 'Creative Operations',
                'code' => '121',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'name' => 'Accounts',
                'code' => '007',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 9,
                'name' => 'IT Development',
                'code' => '008',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 10,
                'name' => 'Engineering',
                'code' => '009',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 11,
                'name' => 'Quality Assurance',
                'code' => '010',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 12,
                'name' => 'UI/UX Design',
                'code' => '011',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 13,
                'name' => 'Product Management',
                'code' => '012',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 14,
                'name' => 'Business Growth',
                'code' => '013',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 15,
                'name' => 'Operations & Support',
                'code' => '014',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 16,
                'name' => 'DevOps & Infrastructure',
                'code' => '015',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 17,
                'name' => 'Marketing',
                'code' => '016',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 18,
                'name' => 'Finance',
                'code' => '017',
                'address' => 'Indore',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('departments')->insert($departments);
    }
}