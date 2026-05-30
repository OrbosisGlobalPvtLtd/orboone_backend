<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    $apiRouteFiles = [
        'auth',
        'profile',
        'hrms/employee',
        'hrms/document',
        'hrms/announcement',
        'hrms/attendance',
        'hrms/leave',
        'project_management/task',
        // Legacy Payroll retired. Enterprise Payroll is the only active payroll engine.
        'hrms/payroll',
        'hrms/enterprise_payroll',
        'mobile_app',
        'notification',
    ];

    foreach ($apiRouteFiles as $file) {
        $path = __DIR__ . "/api/v1/{$file}.php";

        if (! file_exists($path)) {
            throw new RuntimeException("Missing API route file: {$path}");
        }

        require $path;
    }
});
