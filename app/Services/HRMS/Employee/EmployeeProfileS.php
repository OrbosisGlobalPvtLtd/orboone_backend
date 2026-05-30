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
            && in_array($profile->profile_status, ['submitted', 'approved', null], true);

        return $isComplete ? null : (int) $employee->id;
    }

    public function isProfileCompleteForUser(int $userId): bool
    {
        return $this->getIncompleteEmployeeIdForUser($userId) === null;
    }

    public function checkAndSendAllDocumentsVerifiedEmail(int $employeeId): void
    {
        // 1. Fetch employee
        $employee = DB::table('employees_new')->where('id', $employeeId)->first();
        if (!$employee) {
            return;
        }

        $user = DB::table('users')->where('id', $employee->user_id)->first();
        if (!$user || empty($user->email)) {
            return;
        }

        // 2. Check profile status
        $profile = DB::table('employee_profiles')->where('employee_id', $employeeId)->first();
        if (!$profile || $profile->profile_status !== 'approved' || (int)($profile->is_profile_completed ?? 0) !== 1) {
            return;
        }

        // 3. Determine experience type
        $expType = 'fresher';
        $expVal = strtolower(trim((string) ($employee->experience_type ?? $profile->experience_type ?? '')));
        if (in_array($expVal, ['fresher', 'experienced'], true)) {
            $expType = $expVal;
        } else {
            $expText = strtolower(trim((string) ($employee->total_experience ?? $profile->total_experience ?? '')));
            if ($expText !== '' && preg_match('/\d+(\.\d+)?/', $expText, $matches)) {
                $expType = ((float) $matches[0]) > 0 ? 'experienced' : 'fresher';
            }
        }

        // 4. Fetch all mandatory document types
        if (!Schema::hasTable('document_types') || !Schema::hasTable('employee_documents_new')) {
            return;
        }

        $mandatoryTypes = DB::table('document_types')
            ->where('scope', 'employee')
            ->where('is_active', 1)
            ->where('is_mandatory', 1)
            ->where(function ($query) use ($expType) {
                $query->whereNull('applies_to')
                      ->orWhere('applies_to', '')
                      ->orWhere('applies_to', 'all')
                      ->orWhere('applies_to', $expType);
            })
            ->get();

        $totalRequired = $mandatoryTypes->count();
        if ($totalRequired === 0) {
            $verifiedRequired = 0;
        } else {
            $mandatoryIds = $mandatoryTypes->pluck('id');
            $verifiedRequired = DB::table('employee_documents_new')
                ->where('employee_id', $employeeId)
                ->whereIn('document_type_id', $mandatoryIds)
                ->where('verification_status', 'verified')
                ->distinct('document_type_id')
                ->count('document_type_id');
        }

        if ($verifiedRequired < $totalRequired) {
            return;
        }

        // 5. Check cache lock to avoid duplicate emails
        $cacheKey = 'all_docs_verified_email_sent_' . $employeeId;
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return;
        }

        $details = [
            'Employee Name' => $user->name,
            'Employee Code' => $employee->employee_code ?: 'N/A',
            'Verification Date' => now()->format('d M Y'),
            'Next Steps' => 'Your onboarding is now complete. Please log in to complete your daily activities and tasks.',
            'Welcome Message' => 'Welcome to the team! We are excited to have you with us.',
        ];

        \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\HrWorkflowAlertMail(
            subjectText: 'All Documents Successfully Verified',
            workflowTitle: 'All Documents Successfully Verified',
            details: $details,
            actionUrl: url('/dashboard')
        ));

        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addYears(1));
    }
}
