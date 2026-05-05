<?php

return [
    'roles' => [
        'system' => [
            'super_admin' => 'Super Admin',
            'hr_admin' => 'HR Admin',
            'finance_admin' => 'Finance Admin',
            'project_admin' => 'Project Admin',
            'operations_admin' => 'Operations Admin',
            'employee' => 'Employee',
        ],
        'web_login_allowed' => [
            'super_admin',
            'admin',
            'hr_admin',
            'finance_admin',
            'project_admin',
            'operations_admin',
            'custom_admin',
            'employee',
        ],
    ],

    'modules' => [
        'employee' => 'Employee',
        'attendance' => 'Attendance',
        'leave' => 'Leave',
        'payroll' => 'Payroll',
        'document' => 'Document',
        'announcement' => 'Announcement',
    ],

    // Central permission dictionary.
    // Keep middleware keys here instead of hardcoding across controllers/routes.
    'permissions' => [
        'employee' => [
            'employees.view',
            'employees.create',
            'employees.update',
            'departments.manage',
            'designations.manage',
            'asset_allocation.manage',
        ],
        'attendance' => [
            'attendances.view',
            'attendances.manage',
        ],
        'leave' => [
            'leave.view',
            'leave.manage',
            'leave.approve',
        ],
        'payroll' => [
            'payroll.view',
            'payroll.manage',
        ],
        'document' => [
            'employee_documents.view',
            'company_documents.manage',
            'documents_self.view',
            'documents_self.upload',
        ],
        'announcement' => [
            'announcements.view',
            'announcements.manage',
        ],
    ],

    // Role permission templates for seeders/sync jobs.
    // This is data structure only; no forced runtime overrides.
    'role_permission_templates' => [
        'super_admin' => ['*'],
        'hr_admin' => [
            'employee',
            'attendance',
            'leave',
            'document',
            'announcement',
        ],
        'finance_admin' => [
            'payroll',
            'leave',
            'document',
        ],
        'project_admin' => [
            'employee',
            'attendance',
            'leave',
            'announcement',
        ],
        'operations_admin' => [
            'employee',
            'attendance',
            'leave',
            'document',
        ],
    ],
];
