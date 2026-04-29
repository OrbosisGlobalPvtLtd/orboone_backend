<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $menus = [

            // Dashboard
            ['id'=>1, 'name'=>'Dashboard', 'route'=>'dashboard', 'icon'=>'fas fa-house', 'module_key'=>'dashboard', 'parent_id'=>null, 'sort_order'=>1],

            /*
            |--------------------------------------------------------------------------
            | ✅ Employee Management (UPDATED CLEAN HRMS STRUCTURE)
            |--------------------------------------------------------------------------
            */

            ['id'=>10, 'name'=>'Employee Management', 'route'=>null, 'icon'=>'fas fa-users-cog', 'module_key'=>'employees', 'parent_id'=>null, 'sort_order'=>10],

            ['id'=>11, 'name'=>'Employee Directory', 'route'=>'employees.index', 'icon'=>'fas fa-address-book', 'module_key'=>'employees', 'parent_id'=>10, 'sort_order'=>1],
            ['id'=>12, 'name'=>'Onboard Employee', 'route'=>'employees.create', 'icon'=>'fas fa-user-plus', 'module_key'=>'employees', 'parent_id'=>10, 'sort_order'=>2],
            ['id'=>13, 'name'=>'Pending Profiles', 'route'=>'employees.pending-profiles', 'icon'=>'fas fa-user-clock', 'module_key'=>'employees', 'parent_id'=>10, 'sort_order'=>3],
            ['id'=>15, 'name'=>'Probation / Internship', 'route'=>'employees.probation-internship', 'icon'=>'fas fa-hourglass-half', 'module_key'=>'employees', 'parent_id'=>10, 'sort_order'=>5],
            ['id'=>16, 'name'=>'Exit Employees', 'route'=>'employees.exit', 'icon'=>'fas fa-user-times', 'module_key'=>'employees', 'parent_id'=>10, 'sort_order'=>6],
            ['id'=>17, 'name'=>'Dept & Designation', 'route'=>'organization.index', 'icon'=>'fas fa-building', 'module_key'=>'employees', 'parent_id'=>10, 'sort_order'=>7],
           ['id'=>18, 'name'=>'Reporting Structure', 'route'=>'employees.reporting-structure', 'icon'=>'fas fa-sitemap', 'module_key'=>'employees', 'parent_id'=>10, 'sort_order'=>8],


            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

            ['id'=>20, 'name'=>'Attendance & Tracking', 'route'=>null, 'icon'=>'fas fa-calendar-check', 'module_key'=>'attendance', 'parent_id'=>null, 'sort_order'=>20],
            ['id'=>21, 'name'=>'Attendance Marking', 'route'=>'attendances', 'icon'=>'fas fa-fingerprint', 'module_key'=>'attendance', 'parent_id'=>20, 'sort_order'=>1],
            ['id'=>22, 'name'=>'Task Tracking', 'route'=>'task_management', 'icon'=>'fas fa-tasks', 'module_key'=>'attendance', 'parent_id'=>20, 'sort_order'=>2],
            ['id'=>23, 'name'=>'Attendance Rules', 'route'=>'attendances', 'icon'=>'fas fa-exclamation-triangle', 'module_key'=>'attendance', 'parent_id'=>20, 'sort_order'=>3],
            ['id'=>24, 'name'=>'Generate Report', 'route'=>'attendances.export-pdf', 'icon'=>'fas fa-file-pdf', 'module_key'=>'attendance', 'parent_id'=>20, 'sort_order'=>4],


            /*
            |--------------------------------------------------------------------------
            | Leave
            |--------------------------------------------------------------------------
            */

            ['id'=>30, 'name'=>'Leave Management', 'route'=>null, 'icon'=>'fas fa-calendar-alt', 'module_key'=>'leave', 'parent_id'=>null, 'sort_order'=>30],
            ['id'=>31, 'name'=>'Leave Allocation', 'route'=>'leave-allocations.index', 'icon'=>'fas fa-coins', 'module_key'=>'leave', 'parent_id'=>30, 'sort_order'=>1],
            ['id'=>32, 'name'=>'Apply for Leave', 'route'=>'leave-requests.index', 'icon'=>'fas fa-paper-plane', 'module_key'=>'leave', 'parent_id'=>30, 'sort_order'=>2],
            ['id'=>33, 'name'=>'Leave Approvals', 'route'=>'leave-approvals.index', 'icon'=>'fas fa-check-double', 'module_key'=>'leave', 'parent_id'=>30, 'sort_order'=>3],
            ['id'=>34, 'name'=>'Balance Tracker', 'route'=>'employees-leave-request.summary', 'icon'=>'fas fa-history', 'module_key'=>'leave', 'parent_id'=>30, 'sort_order'=>4],
            ['id'=>35, 'name'=>'Holiday List', 'route'=>'dashboard', 'icon'=>'fas fa-glass-cheers', 'module_key'=>'leave', 'parent_id'=>30, 'sort_order'=>5],


            /*
            |--------------------------------------------------------------------------
            | Payroll
            |--------------------------------------------------------------------------
            */

            ['id'=>40, 'name'=>'Payroll Management', 'route'=>null, 'icon'=>'fas fa-money-check-alt', 'module_key'=>'payroll', 'parent_id'=>null, 'sort_order'=>40],
            ['id'=>41, 'name'=>'Salary Structure', 'route'=>'pages.payroll.index', 'icon'=>'fas fa-layer-group', 'module_key'=>'payroll', 'parent_id'=>40, 'sort_order'=>1],
            ['id'=>42, 'name'=>'Payroll Dashboard', 'route'=>'pages.payroll.dashboard', 'icon'=>'fas fa-chart-pie', 'module_key'=>'payroll', 'parent_id'=>40, 'sort_order'=>2],
            ['id'=>43, 'name'=>'My Salary Slips', 'route'=>'pages.payroll.payslips', 'icon'=>'fas fa-file-invoice-dollar', 'module_key'=>'payroll', 'parent_id'=>40, 'sort_order'=>3],
            ['id'=>44, 'name'=>'Settlement (FNF)', 'route'=>'pages.payroll.fnf', 'icon'=>'fas fa-walking', 'module_key'=>'payroll', 'parent_id'=>40, 'sort_order'=>4],
            ['id'=>45, 'name'=>'Bonus Management', 'route'=>'pages.payroll.index', 'icon'=>'fas fa-gift', 'module_key'=>'payroll', 'parent_id'=>40, 'sort_order'=>5],


            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */

            ['id'=>50, 'name'=>'Document Management', 'route'=>null, 'icon'=>'fas fa-folder-open', 'module_key'=>'documents', 'parent_id'=>null, 'sort_order'=>50],
            ['id'=>51, 'name'=>'Compliance Management', 'route'=>'hr.documents.index', 'icon'=>'fas fa-shield-alt', 'module_key'=>'documents', 'parent_id'=>50, 'sort_order'=>1],
            ['id'=>52, 'name'=>'Upload Documents', 'route'=>'employee.documents-index', 'icon'=>'fas fa-file-upload', 'module_key'=>'documents', 'parent_id'=>50, 'sort_order'=>2],
            ['id'=>53, 'name'=>'Company Documents', 'route'=>'employee.hr-policies', 'icon'=>'fas fa-file-contract', 'module_key'=>'documents', 'parent_id'=>50, 'sort_order'=>3],


            /*
            |--------------------------------------------------------------------------
            | Others
            |--------------------------------------------------------------------------
            */

            ['id'=>60, 'name'=>'Notice / Announcement', 'route'=>'announcements', 'icon'=>'fas fa-bullhorn', 'module_key'=>'announcements', 'parent_id'=>null, 'sort_order'=>60],

            ['id'=>70, 'name'=>'Access Control', 'route'=>null, 'icon'=>'fas fa-user-shield', 'module_key'=>'access_control', 'parent_id'=>null, 'sort_order'=>70],
            ['id'=>71, 'name'=>'Roles', 'route'=>'roles.index', 'icon'=>'fas fa-user-tag', 'module_key'=>'access_control', 'parent_id'=>70, 'sort_order'=>1],
            ['id'=>72, 'name'=>'Permissions', 'route'=>'permissions.index', 'icon'=>'fas fa-key', 'module_key'=>'access_control', 'parent_id'=>70, 'sort_order'=>2],
            ['id'=>73, 'name'=>'Admin Users', 'route'=>'admins.index', 'icon'=>'fas fa-user-cog', 'module_key'=>'access_control', 'parent_id'=>70, 'sort_order'=>3],

            ['id'=>80, 'name'=>'Settings', 'route'=>null, 'icon'=>'fas fa-cogs', 'module_key'=>'settings', 'parent_id'=>null, 'sort_order'=>80],

            ['id'=>90, 'name'=>'CRM', 'route'=>'module.crm', 'icon'=>'fas fa-handshake', 'module_key'=>'crm', 'parent_id'=>null, 'sort_order'=>90],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->updateOrInsert(
                ['id' => $menu['id']],
                [
                    'name' => $menu['name'],
                    'route' => $menu['route'],
                    'icon' => $menu['icon'],
                    'module_key' => $menu['module_key'],
                    'parent_id' => $menu['parent_id'],
                    'sort_order' => $menu['sort_order'],
                    'is_active' => 1,
                    'updated_at' => $now,
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}