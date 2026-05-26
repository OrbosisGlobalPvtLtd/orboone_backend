<?php

namespace App\Services\HRMS\Storage;

class HrmsStoragePathS
{
    public function employeeBase(int $employeeId): string
    {
        return "hrms/employees/{$employeeId}";
    }

    public function employeeProfile(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/profile/' . $this->sanitize($type, ['avatar']);
    }

    public function employeeOnboarding(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/onboarding/' . $this->sanitize($type, ['resume', 'nda']);
    }

    public function employeeIdentity(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/identity/' . $this->sanitize($type, ['aadhaar', 'pan', 'passport', 'driving-license']);
    }

    public function employeeBanking(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/banking/' . $this->sanitize($type, ['bank-proof']);
    }

    public function employeeEducation(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/education/' . $this->sanitize($type, ['documents']);
    }

    public function employeeExperience(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/experience/' . $this->sanitize($type, [
            'offer-letter',
            'experience-letter',
            'relieving-letter',
            'salary-slips',
        ]);
    }

    public function employeeHrDocument(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/hr-documents/' . $this->sanitize($type, [
            'appointment-letters',
            'confirmation-letters',
            'salary-revisions',
            'warning-letters',
            'internship-certificates',
            'experience-certificates',
            'relieving-letters',
        ]);
    }

    public function employeeAttendance(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/leave/' . $this->sanitize($type, ['attachments']);
    }

    public function employeeLeave(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/leave/' . $this->sanitize($type, ['attachments', 'medical-certificates']);
    }

    public function employeePayroll(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/payroll/' . $this->sanitize($type, ['payslips', 'reimbursements']);
    }

    public function employeeAsset(int $employeeId, string $type): string
    {
        return $this->employeeBase($employeeId) . '/hr-documents/' . $this->sanitize($type, ['appointment-letters']);
    }

    public function announcement(int $year, int $month, string $type): string
    {
        return 'hrms/announcements/' . $year . '/' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '/' . $this->sanitize($type, ['attachments']);
    }

    public function companyPolicy(string $type): string
    {
        return 'hrms/company/policies/' . $this->sanitize($type);
    }

    public function companyLegal(string $type): string
    {
        return 'hrms/company/policies/' . $this->sanitize($type);
    }

    public function companyTemplate(string $type): string
    {
        return 'hrms/company/templates/' . $this->sanitize($type, ['document-generation', 'certificates']);
    }

    public function companyBranding(string $type): string
    {
        return 'hrms/company/branding/' . $this->sanitize($type, ['logos', 'favicons', 'signatures']);
    }

    public function companyReport(string $type): string
    {
        return 'hrms/generated/' . now()->format('Y') . '/' . now()->format('m') . '/reports/' . $this->sanitize($type);
    }

    public function generated(int $year, int $month, string $type): string
    {
        return 'hrms/generated/' . $year . '/' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '/' . $this->sanitize($type, ['letters', 'certificates', 'payroll', 'reports']);
    }

    public function apk(string $platform): string
    {
        return 'hrms/apk/' . $this->sanitize($platform, ['android']);
    }

    public function temp(string $type): string
    {
        return 'hrms/temp/' . $this->sanitize($type, ['exports', 'previews']);
    }

    public function mapEmployeeDocumentType(int $employeeId, ?string $type): string
    {
        $normalized = $this->sanitize($type ?: 'other');
        $map = [
            'resume' => $this->employeeOnboarding($employeeId, 'resume'),
            'nda' => $this->employeeOnboarding($employeeId, 'nda'),
            'offer-letter' => $this->employeeExperience($employeeId, 'offer-letter'),
            'aadhaar' => $this->employeeIdentity($employeeId, 'aadhaar'),
            'pan' => $this->employeeIdentity($employeeId, 'pan'),
            'passport' => $this->employeeIdentity($employeeId, 'passport'),
            'driving-license' => $this->employeeIdentity($employeeId, 'driving-license'),
            'bank-proof' => $this->employeeBanking($employeeId, 'bank-proof'),
            'education' => $this->employeeEducation($employeeId, 'documents'),
            'education-document' => $this->employeeEducation($employeeId, 'documents'),
            'medical-certificate' => $this->employeeLeave($employeeId, 'medical-certificates'),
            'leave-attachment' => $this->employeeLeave($employeeId, 'attachments'),
            'reimbursement' => $this->employeePayroll($employeeId, 'reimbursements'),
            'payslip' => $this->employeePayroll($employeeId, 'payslips'),
            'appointment-letter' => $this->employeeHrDocument($employeeId, 'appointment-letters'),
            'experience-letter' => $this->employeeExperience($employeeId, 'experience-letter'),
            'relieving-letter' => $this->employeeExperience($employeeId, 'relieving-letter'),
            'salary-slips' => $this->employeeExperience($employeeId, 'salary-slips'),
            'confirmation-letter' => $this->employeeHrDocument($employeeId, 'confirmation-letters'),
            'salary-revision' => $this->employeeHrDocument($employeeId, 'salary-revisions'),
            'warning-letter' => $this->employeeHrDocument($employeeId, 'warning-letters'),
            'internship-certificate' => $this->employeeHrDocument($employeeId, 'internship-certificates'),
            'experience-certificate' => $this->employeeHrDocument($employeeId, 'experience-certificates'),
            'relieving-certificate' => $this->employeeHrDocument($employeeId, 'relieving-letters'),
        ];

        return $map[$normalized] ?? $this->employeeEducation($employeeId, 'documents');
    }

    public function normalizeDocType(?string $value): string
    {
        return $this->sanitize((string) ($value ?: 'other'));
    }

    private function sanitize(string $value, ?array $allowed = null): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['_', ' '], '-', $value);
        $value = preg_replace('/[^a-z0-9\-]/', '', $value) ?: 'other';
        $value = preg_replace('/-+/', '-', $value) ?: 'other';
        $value = trim($value, '-');
        $value = $value === '' ? 'other' : $value;

        if ($allowed !== null && ! in_array($value, $allowed, true)) {
            return $allowed[0] ?? 'other';
        }

        return $value;
    }
}
