<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentModuleAccessSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $departments = DB::table('departments')->pluck('id', 'name')->toArray();

        $mapping = [
            'Human Resources' => ['hrms'],
            'Accounts' => ['hrms', 'finance'],
            'Finance' => ['hrms', 'finance'],
            'Operations & Support' => ['hrms'],
            'Creative Operations' => ['hrms'],

            'Sales' => ['crm'],
            'Business Development' => ['crm'],
            'Marketing' => ['crm'],
            'Digital Marketing' => ['crm'],
            'Business Growth' => ['crm', 'project_management'],

            'Web Development' => ['project_management'],
            'Mobile App Development' => ['project_management'],
            'IT Development' => ['project_management'],
            'Engineering' => ['project_management'],
            'Quality Assurance' => ['project_management'],
            'UI/UX Design' => ['project_management'],
            'Product Management' => ['project_management'],
            'DevOps & Infrastructure' => ['project_management'],
        ];

        foreach ($mapping as $departmentName => $modules) {
            if (!isset($departments[$departmentName])) {
                $this->command->warn("Department not found: {$departmentName}");
                continue;
            }

            $departmentId = $departments[$departmentName];

            foreach ($modules as $moduleKey) {
                DB::table('department_module_access')->updateOrInsert(
                    [
                        'department_id' => $departmentId,
                        'module_key' => $moduleKey,
                    ],
                    [
                        'is_enabled' => true,
                        'updated_at' => $now,
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }
        }
    }
}