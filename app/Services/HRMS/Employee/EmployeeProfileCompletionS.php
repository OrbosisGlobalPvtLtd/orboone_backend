<?php

namespace App\Services\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Services\HRMS\Document\EmployeeDocumentCompletionS;

class EmployeeProfileCompletionS
{
    protected EmployeeDocumentCompletionS $documentCompletion;

    public function __construct(EmployeeDocumentCompletionS $documentCompletion)
    {
        $this->documentCompletion = $documentCompletion;
    }

    public function buildCompletionStatus(?EmployeeM $employee, ?EmployeeProfileM $profile = null): array
    {
        if (!$employee) {
            return [
                'is_profile_completed'         => true,
                'profile_verification_status'  => 'approved',
                'rejection_reason'             => null,
                'document_verification_status' => 'verified',
                'required_documents_verified'  => true,
                'can_punch_attendance'         => true,
                'attendance_blocked'           => false,
                'next_route'                   => 'dashboard',
                'must_complete_profile'        => false,
                'completion_percentage'        => 100,
                'missing_profile_fields'       => [],
                'document_completion_status'   => [],
                'experience_type'              => 'fresher',
            ];
        }

        $isEmployee = method_exists($employee->user, 'isEmployee')
            ? (bool) $employee->user->isEmployee()
            : true;

        if (!$profile && $employee) {
            $profile = $employee->profile;
        }

        $missingProfileFields = $this->documentCompletion->missingProfileFields($profile, $employee);
        $profileFieldsCompleted = count($missingProfileFields) === 0;

        $documentStatus = $this->documentCompletion->completion($employee);

        $requiredUploaded = ($documentStatus['uploaded_count'] ?? 0) === ($documentStatus['required_count'] ?? 0)
            && ($documentStatus['required_count'] ?? 0) > 0;

        $requiredVerified = ($documentStatus['verified_count'] ?? 0) === ($documentStatus['required_count'] ?? 0)
            && ($documentStatus['required_count'] ?? 0) > 0;

        $isProfileApproved = ($profile?->profile_status ?? 'pending') === 'approved';

        $canPunchAttendance = ! $isEmployee || $isProfileApproved || (
            ($profile?->is_profile_completed ?? false) && $requiredVerified
        );

        $docVerificationStatus = 'missing';

        if (($documentStatus['rejected_count'] ?? 0) > 0) {
            $docVerificationStatus = 'rejected';
        } elseif (($documentStatus['pending_count'] ?? 0) > 0) {
            $docVerificationStatus = 'pending';
        } elseif ($requiredVerified) {
            $docVerificationStatus = 'verified';
        }

        $isProfileCompleted = $profile ? (bool) $profile->is_profile_completed : false;
        $hasRejectedDoc = ($documentStatus['rejected_count'] ?? 0) > 0;
        $isProfileRejected = ($profile?->profile_status ?? 'pending') === 'rejected';

        $mustCompleteProfile = $isEmployee ? (! $isProfileCompleted || $isProfileRejected || $hasRejectedDoc) : false;
        $attendanceBlocked = $isEmployee ? ! $canPunchAttendance : false;

        $nextRoute = 'dashboard';

        if ($mustCompleteProfile) {
            if (! $profileFieldsCompleted || ($profile?->profile_status ?? 'pending') === 'rejected') {
                $nextRoute = 'profile_completion';
            } elseif (! $requiredUploaded) {
                $nextRoute = 'document_completion';
            } else {
                $nextRoute = 'document_completion';
            }
        }

        return [
            'is_profile_completed'         => $isProfileCompleted,
            'profile_verification_status'  => $profile?->profile_status ?? 'pending',
            'rejection_reason'             => $profile?->rejection_reason,
            'document_verification_status' => $docVerificationStatus,
            'required_documents_verified'  => $requiredVerified,
            'can_punch_attendance'         => $canPunchAttendance,
            'attendance_blocked'           => $attendanceBlocked,
            'next_route'                   => $nextRoute,
            'must_complete_profile'        => $mustCompleteProfile,
            'completion_percentage'        => $this->documentCompletion->profileCompletionPercentage($profile, $employee),
            'missing_profile_fields'       => $missingProfileFields,
            'document_completion_status'   => $documentStatus,
            'experience_type'              => $profile?->experience_type ?? 'fresher',
        ];
    }
}
