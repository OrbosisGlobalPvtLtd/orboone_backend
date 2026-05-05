<?php

namespace App\Services\HRMS\Employee;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeProfileS
{
    public function getIncompleteEmployeeIdForUser(int $userId): ?int
    {
        if (! Schema::hasTable('employees_new') || ! Schema::hasTable('employee_profiles')) {
            return null;
        }

        $employee = DB::table('employees_new')
            ->where('user_id', $userId)
            ->select('id')
            ->first();

        if (! $employee) {
            return null;
        }

        $profile = DB::table('employee_profiles')
            ->where('employee_id', $employee->id)
            ->select('is_profile_completed', 'profile_status')
            ->first();

        $isComplete = $profile
            && (int) ($profile->is_profile_completed ?? 0) === 1
            && in_array($profile->profile_status, ['approved', null], true);

        return $isComplete ? null : (int) $employee->id;
    }

    public function isProfileCompleteForUser(int $userId): bool
    {
        return $this->getIncompleteEmployeeIdForUser($userId) === null;
    }
}
