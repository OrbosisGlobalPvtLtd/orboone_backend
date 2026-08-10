<?php

namespace App\Services\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\Core\UserM;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeEligibilityS
{
    /**
     * Check if an employee is operational and fully eligible.
     * Eligible Employee =
     *  - is_active == 1 (or employment_status == 'active')
     *  - profile_status == 'approved' / is_profile_completed == 1
     *  - exit_status != 'exit_completed' / employment_status != 'exited'
     *  - employment_status != 'terminated'
     */
    public function isEligible($employee): bool
    {
        if (!$employee) {
            return false;
        }

        if ($this->isTerminated($employee)) {
            return false;
        }

        if ($this->isExitCompleted($employee)) {
            return false;
        }

        if (!$this->isActive($employee)) {
            return false;
        }

        if ($this->isProfilePending($employee)) {
            return false;
        }

        return true;
    }

    /**
     * Check if employee record is active.
     */
    public function isActive($employee): bool
    {
        if (!$employee) {
            return false;
        }

        $isActive = is_object($employee) ? ($employee->is_active ?? 1) : 1;
        $empStatus = strtolower(trim((string) (is_object($employee) ? ($employee->employment_status ?? 'active') : 'active')));

        if ((int)$isActive === 0) {
            return false;
        }

        if (in_array($empStatus, ['terminated', 'exited', 'resigned_and_exited', 'inactive'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Check if employee profile is pending completion / approval.
     */
    public function isProfilePending($employee): bool
    {
        if (!$employee) {
            return true;
        }

        $profile = null;
        if ($employee instanceof EmployeeM) {
            $profile = $employee->profile;
        } elseif (is_object($employee) && isset($employee->id)) {
            $empId = $employee->id;
            if (Schema::hasTable('employee_profiles')) {
                $profile = DB::table('employee_profiles')->where('employee_id', $empId)->first();
            }
        }

        if (!$profile) {
            return true;
        }

        $profStatus = strtolower(trim((string) ($profile->profile_status ?? 'pending')));
        $isCompleted = (bool) ($profile->is_profile_completed ?? false);

        if ($profStatus === 'approved') {
            return false;
        }

        if ($isCompleted && in_array($profStatus, ['approved', 'submitted'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Check if employee exit is completed or employment status is exited.
     */
    public function isExitCompleted($employee): bool
    {
        if (!$employee) {
            return true;
        }

        $empId = is_object($employee) ? ($employee->id ?? null) : null;
        $empStatus = strtolower(trim((string) (is_object($employee) ? ($employee->employment_status ?? '') : '')));

        if (in_array($empStatus, ['exited', 'terminated', 'resigned_and_exited'], true)) {
            return true;
        }

        if ($empId && Schema::hasTable('employee_exit_processes')) {
            $exit = DB::table('employee_exit_processes')
                ->where('employee_id', $empId)
                ->where('status', 'exit_completed')
                ->first();
            if ($exit) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if employee is terminated.
     */
    public function isTerminated($employee): bool
    {
        if (!$employee) {
            return true;
        }

        $empStatus = strtolower(trim((string) (is_object($employee) ? ($employee->employment_status ?? '') : '')));
        return $empStatus === 'terminated';
    }

    /**
     * Determine login permission and messaging.
     */
    public function canLogin($userOrEmployee): array
    {
        $employee = null;
        $user = null;

        if ($userOrEmployee instanceof UserM) {
            $user = $userOrEmployee;
            $employee = EmployeeM::where('user_id', $user->id)->first();
        } elseif ($userOrEmployee instanceof EmployeeM) {
            $employee = $userOrEmployee;
            $user = $employee->user;
        } elseif (is_numeric($userOrEmployee)) {
            $employee = EmployeeM::find($userOrEmployee);
        }

        if ($user && isset($user->is_active) && (int)$user->is_active === 0) {
            return [
                'allowed' => false,
                'code' => 'account_disabled',
                'message' => 'Your employment has ended. Please contact HR.',
            ];
        }

        if ($employee) {
            if ($this->isExitCompleted($employee) || $this->isTerminated($employee)) {
                return [
                    'allowed' => false,
                    'code' => 'employment_ended',
                    'message' => 'Your employment has ended. Please contact HR.',
                ];
            }

            if ($this->isProfilePending($employee)) {
                return [
                    'allowed' => true,
                    'code' => 'profile_pending',
                    'message' => 'Complete your profile to access HRMS services.',
                    'restricted' => true,
                ];
            }
        }

        return [
            'allowed' => true,
            'code' => 'eligible',
            'message' => 'Eligible for HRMS services.',
            'restricted' => false,
        ];
    }

    /**
     * Attendance Eligibility: Skips Profile Pending, Exit Completed, Terminated.
     */
    public function canUseAttendance($employee): bool
    {
        return $this->isEligible($employee);
    }

    /**
     * Payroll Eligibility: Skips Profile Pending, Exit Completed, Terminated.
     */
    public function canUsePayroll($employee): bool
    {
        return $this->isEligible($employee);
    }

    /**
     * Leave Application / Management Eligibility.
     */
    public function canUseLeave($employee): bool
    {
        return $this->isEligible($employee);
    }

    public function canApplyLeave($employee): bool
    {
        return $this->isEligible($employee);
    }

    /**
     * Tasks Module Eligibility.
     */
    public function canUseTasks($employee): bool
    {
        return $this->isEligible($employee);
    }

    /**
     * Team Management & Approval Chain Eligibility.
     */
    public function canUseTeamManagement($employee): bool
    {
        return $this->isEligible($employee);
    }

    /**
     * Document Module Visibility.
     */
    public function canAccessDocuments($employee, string $context = 'general'): bool
    {
        if (!$employee) {
            return false;
        }

        if ($this->isExitCompleted($employee) || $this->isTerminated($employee)) {
            return $context === 'exit_management';
        }

        return true;
    }
}
