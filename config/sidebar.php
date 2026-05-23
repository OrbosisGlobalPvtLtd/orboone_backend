<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Common
    |--------------------------------------------------------------------------
    */

    [
        'section' => 'Overview',
        'items' => [
            [
                'title' => 'Dashboard',
                'icon' => 'fa-solid fa-house',
                'route' => 'dashboard',
                'permission' => null,
                'module' => null,
                'roles' => [],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HRMS - Employee / Admin menus
    |--------------------------------------------------------------------------
    */

    [
        'section' => 'HRMS',
        'items' => [
            [
                'title' => 'Employees',
                'icon' => 'fa-solid fa-users',
                'route' => 'hrms.employees.index',
                'permission' => 'employees.view',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'custom_admin'],
            ],
            [
                'title' => 'Add Employee',
                'icon' => 'fa-solid fa-user-plus',
                'route' => 'hrms.employees.create',
                'permission' => 'employees.create',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'custom_admin'],
            ],
            [
                'title' => 'Departments',
                'icon' => 'fa-solid fa-building',
                'route' => 'hrms.departments.index',
                'permission' => 'departments.manage',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'custom_admin'],
            ],
            [
                'title' => 'Designations',
                'icon' => 'fa-solid fa-id-badge',
                'route' => 'hrms.designations.index',
                'permission' => 'designations.manage',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'custom_admin'],
            ],
            [
                'title' => 'Attendance',
                'icon' => 'fa-solid fa-calendar-check',
                'route' => 'attendance.index',
                'permission' => 'attendance_records.view',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'operations_admin', 'custom_admin'],
            ],
            [
                'title' => 'Leaves',
                'icon' => 'fa-solid fa-calendar-minus',
                'route' => 'leaves.index',
                'permission' => 'leave_requests.view',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'operations_admin', 'custom_admin'],
            ],
            [
                'title' => 'Enterprise Payroll',
                'icon' => 'fa-solid fa-wallet',
                'route' => 'enterprise-payroll.dashboard',
                'permission' => 'enterprise_payroll.dashboard.view',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'finance_admin', 'hr_admin', 'custom_admin'],
            ],
            [
                'title' => 'Documents',
                'icon' => 'fa-solid fa-folder-open',
                'route' => 'documents.index',
                'permission' => 'documents.view',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'custom_admin'],
            ],
            [
                'title' => 'Announcements',
                'icon' => 'fa-solid fa-bullhorn',
                'route' => 'announcements.index',
                'permission' => 'announcements.view',
                'module' => 'hrms',
                'roles' => ['super_admin', 'admin', 'hr_admin', 'custom_admin', 'employee'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Employee Self Panel
    |--------------------------------------------------------------------------
    */

    [
        'section' => 'My Panel',
        'items' => [
            [
                'title' => 'My Profile',
                'icon' => 'fa-solid fa-user',
                'route' => 'settings.profile',
                'permission' => null,
                'module' => null,
                'roles' => ['employee', 'super_admin', 'admin', 'hr_admin', 'finance_admin', 'project_admin', 'operations_admin', 'custom_admin'],
            ],
            [
                'title' => 'My Attendance',
                'icon' => 'fa-solid fa-clock',
                'route' => 'my.attendance',
                'permission' => 'attendance_self.view',
                'module' => 'hrms',
                'roles' => ['employee'],
            ],
            [
                'title' => 'My Leaves',
                'icon' => 'fa-solid fa-calendar-days',
                'route' => 'my.leaves',
                'permission' => 'leave_self.view_balance',
                'module' => 'hrms',
                'roles' => ['employee'],
            ],
            [
                'title' => 'My Payslips',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'route' => 'enterprise-payroll.self.payslips',
                'permission' => 'enterprise_payslip.view',
                'module' => 'hrms',
                'roles' => ['employee'],
            ],
            [
                'title' => 'My Reimbursements',
                'icon' => 'fa-solid fa-receipt',
                'route' => 'enterprise-payroll.self.reimbursements',
                'permission' => 'enterprise_reimbursement.view',
                'module' => 'hrms',
                'roles' => ['employee'],
            ],
            [
                'title' => 'My Documents',
                'icon' => 'fa-solid fa-file-lines',
                'route' => 'my.documents',
                'permission' => 'documents_self.view',
                'module' => 'hrms',
                'roles' => ['employee'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Control
    |--------------------------------------------------------------------------
    */

    [
        'section' => 'Access Control',
        'items' => [
            [
                'title' => 'Roles',
                'icon' => 'fa-solid fa-user-shield',
                'route' => 'roles.index',
                'permission' => 'roles.manage',
                'module' => null,
                'roles' => ['super_admin', 'admin'],
            ],
            [
                'title' => 'Permissions',
                'icon' => 'fa-solid fa-key',
                'route' => 'permissions.index',
                'permission' => 'permissions.manage',
                'module' => null,
                'roles' => ['super_admin', 'admin'],
            ],
            [
                'title' => 'Admin Users',
                'icon' => 'fa-solid fa-user-tie',
                'route' => 'admins.index',
                'permission' => 'admins.manage',
                'module' => null,
                'roles' => ['super_admin', 'admin'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Future Business Modules
    |--------------------------------------------------------------------------
    */

    [
        'section' => 'Business Modules',
        'items' => [
            [
                'title' => 'CRM',
                'icon' => 'fa-solid fa-handshake',
                'route' => 'module.crm',
                'permission' => 'crm.view',
                'module' => 'crm',
                'roles' => ['super_admin', 'admin', 'custom_admin'],
                'badge' => 'Soon',
            ],
            [
                'title' => 'Project Management',
                'icon' => 'fa-solid fa-diagram-project',
                'route' => 'module.project-mgmt',
                'permission' => 'project_management.view',
                'module' => 'project_management',
                'roles' => ['super_admin', 'admin', 'project_admin', 'custom_admin'],
                'badge' => 'Soon',
            ],
            [
                'title' => 'Finance',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'route' => 'module.finance',
                'permission' => 'finance.view',
                'module' => 'finance',
                'roles' => ['super_admin', 'admin', 'finance_admin', 'custom_admin'],
                'badge' => 'Soon',
            ],
        ],
    ],
];
