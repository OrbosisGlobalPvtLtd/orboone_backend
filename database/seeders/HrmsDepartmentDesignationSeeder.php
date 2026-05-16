<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HrmsDepartmentDesignationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasColumn('departments', 'is_active')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->tinyInteger('is_active')->default(1)->after('address');
            });
        }

        if (Schema::hasTable('designations') && ! Schema::hasColumn('designations', 'is_active')) {
            Schema::table('designations', function (Blueprint $table) {
                $table->tinyInteger('is_active')->default(1)->after('description');
            });
        }

        DB::table('departments')->update(['is_active' => 0]);

        if (Schema::hasTable('designations')) {
            DB::table('designations')->update(['is_active' => 0]);
        }

        $structure = [
            'Engineering / Development' => [
                'code' => 'ENG',
                'designations' => [
                    'Software Engineering Intern',
                    'Junior Software Engineer',
                    'Software Engineer',
                    'Senior Software Engineer',
                    'Tech Lead',
                    'Engineering Manager',
                    'Solution Architect',
                    'Technical Architect',
                    'Chief Technology Officer',
                    'React Native Intern',
                    'React Native Developer',
                    'Senior React Native Developer',
                    'Android Developer',
                    'iOS Developer',
                    'Flutter Intern',
                    'Flutter Developer',
                    'Senior Flutter Developer',
                ],
            ],

            'QA / Testing' => [
                'code' => 'QA',
                'designations' => [
                    'QA Intern',
                    'QA Engineer',
                    'Senior QA Engineer',
                    'Automation Test Engineer',
                    'QA Lead',
                    'QA Manager',
                ],
            ],

            'UI/UX Design' => [
                'code' => 'UIUX',
                'designations' => [
                    'UI/UX Intern',
                    'UI Designer',
                    'UX Designer',
                    'Senior UI/UX Designer',
                    'Design Lead',
                ],
            ],

            'Product Management' => [
                'code' => 'PROD',
                'designations' => [
                    'Product Executive',
                    'Product Manager',
                    'Senior Product Manager',
                    'Head of Product',
                ],
            ],

            'Business & Sales Growth' => [
                'code' => 'BSG',
                'designations' => [
                    'Business Analyst Intern',
                    'Business Analyst',
                    'Senior Business Analyst',
                    'Sales Executive',
                    'Business Development Intern',
                    'Business Development Executive',
                    'Business Development Manager',
                    'Sales Manager',
                    'Account Manager',
                    'Head of Sales',
                ],
            ],

            'Project & Delivery Management' => [
                'code' => 'PDM',
                'designations' => [
                    'Project Management Intern',
                    'Project Manager',
                    'Senior Project Manager',
                    'Delivery Manager',
                    'Delivery Excellence Manager',
                ],
            ],

            'Operations & Support' => [
                'code' => 'OPS',
                'designations' => [
                    'Operations Intern',
                    'Operations Executive',
                    'Associate Operations Executive',
                    'Support Executive',
                    'Customer Support Executive',
                    'Operations Manager',
                    'Head of Operations',
                ],
            ],

            'DevOps / Infrastructure' => [
                'code' => 'DEVOPS',
                'designations' => [
                    'DevOps Engineer',
                    'Senior DevOps Engineer',
                    'Cloud Engineer',
                    'Infrastructure Lead',
                ],
            ],

            'Marketing' => [
                'code' => 'MKT',
                'designations' => [
                    'Marketing Intern',
                    'Marketing Executive',
                    'Digital Marketing Intern',
                    'Digital Marketing Specialist',
                    'Social Media Intern',
                    'Social Media Manager',
                    'SEO Intern',
                    'SEO Specialist',
                    'Content Writer',
                    'Marketing Manager',
                    'Creative Operations Intern',
                    'Associate Creative Operations',
                ],
            ],

            'Human Resources' => [
                'code' => 'HR',
                'designations' => [
                    'HR Intern',
                    'HR Executive',
                    'HR Recruiter',
                    'Talent Acquisition Specialist',
                    'HR Business Partner',
                    'HR Manager',
                    'Head of HR',
                ],
            ],

            'Finance' => [
                'code' => 'FIN',
                'designations' => [
                    'Accountant',
                    'Senior Accountant',
                    'Finance Executive',
                    'Finance Manager',
                    'Chief Financial Officer',
                ],
            ],
        ];

        foreach ($structure as $departmentName => $data) {
            DB::table('departments')->updateOrInsert(
                ['code' => 'DEP-'.$data['code']],
                [
                    'name' => $departmentName,
                    'address' => 'Indore',
                    'is_active' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $departmentId = DB::table('departments')
                ->where('code', 'DEP-'.$data['code'])
                ->value('id');

            foreach ($data['designations'] as $index => $designationName) {
                DB::table('designations')->updateOrInsert(
                    [
                        'department_id' => $departmentId,
                        'name' => $designationName,
                    ],
                    [
                        'code' => $data['code'].'-'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'description' => $designationName,
                        'is_active' => 1,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}