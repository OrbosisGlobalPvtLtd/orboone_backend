<?php

namespace App\Services\HRMS\Employee;

use Illuminate\Support\Facades\DB;

class EmployeeS
{
    public function generateEmployeeCode(
        string $table = 'employees_new',
        string $prefix = 'OG-EMP-'
    ): string {
        $lastCode = DB::table($table)
            ->where('employee_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('employee_code');

        $next = 1;

        if ($lastCode) {
            $next = ((int) str_replace($prefix, '', $lastCode)) + 1;
        }

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function createFormData(): array
    {
        $departments = DB::table('departments')
            ->orderBy('name')
            ->get();

        $designations = DB::table('designations')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $reportingManagers = DB::table('employees_new')
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->where('employees_new.is_active', 1)
            ->select('employees_new.id', 'employees_new.employee_code', 'users.name')
            ->orderBy('users.name')
            ->get();

        $roles = DB::table('roles')
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        return [
            'departments' => $departments,
            'designations' => $designations,
            'reportingManagers' => $reportingManagers,
            'roles' => $roles,
        ];
    }
}
