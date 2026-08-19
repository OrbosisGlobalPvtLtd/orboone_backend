<?php

namespace App\Services\HRMS\Storage;

use App\Models\HRMS\Employee\EmployeeM;

class HrmsStoragePathS
{
    protected static array $codeCache = [];

    /**
     * Resolve the employee code (e.g. "OG-EMP-004") from an ID, string, or Employee model.
     */
    public function employeeCode(int|string|EmployeeM $employee): string
    {
        if ($employee instanceof EmployeeM) {
            $code = trim((string) ($employee->employee_code ?? ''));
            return $code !== '' ? strtoupper($code) : "EMP-{$employee->id}";
        }

        if (is_numeric($employee)) {
            $id = (int) $employee;
            if (isset(self::$codeCache[$id])) {
                return self::$codeCache[$id];
            }
            $code = \Illuminate\Support\Facades\DB::table('employees_new')
                ->where('id', $id)
                ->value('employee_code');

            $result = (!empty($code) && trim((string) $code) !== '') ? strtoupper(trim((string) $code)) : "EMP-{$id}";
            self::$codeCache[$id] = $result;
            return $result;
        }

        $str = trim((string) $employee);
        return $str !== '' ? strtoupper($str) : 'EMP-UNKNOWN';
    }

    public function employeeBase(int|string|EmployeeM $employee): string
    {
        $code = $this->employeeCode($employee);
        return "hrms/employees/{$code}";
    }

    public function employeeProfile(int|string|EmployeeM $employee, string $type = 'avatar'): string
    {
        return $this->employeeBase($employee) . '/profile';
    }

    public function employeeOnboarding(int|string|EmployeeM $employee, string $type): string
    {
        $san = $this->sanitize($type, ['resume', 'nda']);
        return $this->employeeBase($employee) . '/' . ($san === 'resume' ? 'resume' : 'onboarding/' . $san);
    }

    public function employeeIdentity(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/identity/' . $this->sanitize($type, ['aadhaar', 'pan', 'passport', 'driving-license']);
    }

    public function employeeBanking(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/banking/' . $this->sanitize($type, ['bank-proof']);
    }

    public function employeeEducation(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/education/' . $this->sanitize($type, ['documents']);
    }

    public function employeeExperience(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/experience/' . $this->sanitize($type, [
            'offer-letter',
            'experience-letter',
            'relieving-letter',
            'salary-slips',
        ]);
    }

    public function employeeHrDocument(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/hr-documents/' . $this->sanitize($type, [
            'appointment-letters',
            'confirmation-letters',
            'salary-revisions',
            'warning-letters',
            'internship-certificates',
            'experience-certificates',
            'relieving-letters',
        ]);
    }

    public function employeeAttendance(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/leave/' . $this->sanitize($type, ['attachments']);
    }

    public function employeeLeave(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/leave/' . $this->sanitize($type, ['attachments', 'medical-certificates']);
    }

    public function employeePayroll(int|string|EmployeeM $employee, string $type): string
    {
        $san = $this->sanitize($type, ['payslips', 'reimbursements']);
        return $this->employeeBase($employee) . '/' . ($san === 'payslips' ? 'payslips' : 'payroll/' . $san);
    }

    public function employeeAsset(int|string|EmployeeM $employee, string $type): string
    {
        return $this->employeeBase($employee) . '/hr-documents/' . $this->sanitize($type, ['appointment-letters']);
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

    public function mapEmployeeDocumentType(int|string|EmployeeM $employee, ?string $type): string
    {
        $normalized = $this->sanitize($type ?: 'other');
        $map = [
            'resume' => $this->employeeOnboarding($employee, 'resume'),
            'nda' => $this->employeeOnboarding($employee, 'nda'),
            'offer-letter' => $this->employeeExperience($employee, 'offer-letter'),
            'aadhaar' => $this->employeeIdentity($employee, 'aadhaar'),
            'pan' => $this->employeeIdentity($employee, 'pan'),
            'passport' => $this->employeeIdentity($employee, 'passport'),
            'driving-license' => $this->employeeIdentity($employee, 'driving-license'),
            'bank-proof' => $this->employeeBanking($employee, 'bank-proof'),
            'education' => $this->employeeEducation($employee, 'documents'),
            'education-document' => $this->employeeEducation($employee, 'documents'),
            'medical-certificate' => $this->employeeLeave($employee, 'medical-certificates'),
            'leave-attachment' => $this->employeeLeave($employee, 'attachments'),
            'reimbursement' => $this->employeePayroll($employee, 'reimbursements'),
            'payslip' => $this->employeePayroll($employee, 'payslips'),
            'appointment-letter' => $this->employeeHrDocument($employee, 'appointment-letters'),
            'experience-letter' => $this->employeeExperience($employee, 'experience-letter'),
            'relieving-letter' => $this->employeeExperience($employee, 'relieving-letter'),
            'salary-slips' => $this->employeeExperience($employee, 'salary-slips'),
            'confirmation-letter' => $this->employeeHrDocument($employee, 'confirmation-letters'),
            'salary-revision' => $this->employeeHrDocument($employee, 'salary-revisions'),
            'warning-letter' => $this->employeeHrDocument($employee, 'warning-letters'),
            'internship-certificate' => $this->employeeHrDocument($employee, 'internship-certificates'),
            'experience-certificate' => $this->employeeHrDocument($employee, 'experience-certificates'),
            'relieving-certificate' => $this->employeeHrDocument($employee, 'relieving-letters'),
        ];

        return $map[$normalized] ?? $this->employeeEducation($employee, 'documents');
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

