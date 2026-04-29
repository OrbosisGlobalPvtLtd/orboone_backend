<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Main loader file for modular API route files.
| Keep this file clean and lightweight.
|
*/

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/profile.php';
require __DIR__ . '/api/employee.php';
require __DIR__ . '/api/document.php';
require __DIR__ . '/api/attendance.php';
require __DIR__ . '/api/leave.php';
require __DIR__ . '/api/task.php';
require __DIR__ . '/api/payroll.php';
require __DIR__ . '/api/notification.php';
// require __DIR__ . '/api/notice.php';