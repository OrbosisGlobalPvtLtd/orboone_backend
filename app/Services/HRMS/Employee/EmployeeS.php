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

    public function createFormData(?int $includeManagerId = null): array
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
            ->leftJoin('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
            ->where('employees_new.is_active', 1)
            ->where(function ($query) use ($includeManagerId) {
                $query->where(function ($q) {
                    $q->where('employee_profiles.is_profile_completed', 1)
                      ->where('employee_profiles.profile_status', 'approved');
                });
                if ($includeManagerId) {
                    $query->orWhere('employees_new.id', $includeManagerId);
                }
            })
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
