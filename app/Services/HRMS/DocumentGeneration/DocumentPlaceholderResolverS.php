<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\Core\Branding\BrandingSettingsS;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DocumentPlaceholderResolverS
{
    /**
     * Build centralized placeholder data map for both HTML and DOCX engines.
     */
    public function resolve(?EmployeeM $employee, array $manualFields = [], ?UserM $generatedBy = null): array
    {
        $now = Carbon::now();
        $company = null;
        try {
            if (Schema::hasTable('company_settings')) {
                $company = DB::table('company_settings')->first();
            }
        } catch (\Throwable $e) {
            $company = null;
        }
        $branding = BrandingSettingsS::get();

        // 1. Resolve Employee Info
        $employeeName = $employee?->employee_name
            ?: $employee?->full_name
            ?: $employee?->display_name
            ?: '';

        $firstName = '';
        if ($employeeName) {
            $parts = explode(' ', trim($employeeName));
            $firstName = $parts[0] ?? '';
        }

        $employeeCode = $employee?->employee_code ?: 'CAND';
        $department = $employee?->department?->name ?: '';
        $designation = $employee?->designation?->name ?: '';
        
        $managerName = '';
        if ($employee) {
            $manager = $employee->reportingManager;
            if ($manager) {
                $managerName = $manager->employee_name ?: $manager->full_name ?: $manager->display_name ?: '';
            }
        }

        $joiningDate = $this->formatDate($employee?->joining_date);
        $confirmationDate = $this->formatDate($employee?->profile?->confirmation_date ?? null);
        $probationEndDate = $this->formatDate($employee?->probation_end_date ?? null);

        $workLocation = (string) ($employee?->profile?->work_location ?? 'Remote');
        $employeeAddress = (string) ($employee?->profile?->present_address ?: $employee?->profile?->address ?: '');
        $employeeCity = (string) ($employee?->profile?->city ?: $employee?->profile?->present_city ?: '');
        
        $gender = strtolower($employee?->gender ?: $employee?->profile?->gender ?: 'male');
        $genderTitle = ($gender === 'female' || $gender === 'f') ? 'Ms.' : 'Mr.';

        $relievingDate = '';
        if ($employee) {
            try {
                if (Schema::hasTable('employee_exit_processes')) {
                    $exit = DB::table('employee_exit_processes')->where('employee_id', $employee->id)->first();
                    if ($exit && !empty($exit->relieving_date)) {
                        $relievingDate = $this->formatDate($exit->relieving_date);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore exits if tables are missing
            }
        }

        $probationPeriod = (string) ($employee?->probation_period ?? '3 Months');
        $noticePeriodProbation = '15 Days';
        $noticePeriodConfirmed = '30 Days';

        // 2. Resolve Company Details
        $companyName = (string) ($company->company_name ?? branding_name());
        $companyAddress = (string) ($company->address ?? '');
        $companyCity = (string) ($company->city ?? '');
        $companyPhone = (string) ($company->phone ?? '');
        $companyEmail = (string) ($company->email ?? '');
        $companyWebsite = (string) ($company->website ?? '');
        $companyGstin = (string) ($company->gstin ?? '');

        // 3. Resolve Authority Settings
        $authorizedSignatory = (string) ($generatedBy?->name ?? 'Authorized Signatory');
        $hrManagerName = 'Harshit Singh';
        $ceoName = 'Harshit Singh';
        $projectManagerName = 'Harshit Singh';

        // 4. Resolve Salary details
        $monthlyGross = 0.0;
        if ($employee) {
            $monthlyGross = (float) ($employee->salaryStructure?->gross_salary ?? $employee->gross_salary ?? 0);
        }
        if (isset($manualFields['salary_monthly']) && is_numeric($manualFields['salary_monthly'])) {
            $monthlyGross = (float) $manualFields['salary_monthly'];
        } elseif (isset($manualFields['salary']) && is_numeric($manualFields['salary'])) {
            $monthlyGross = (float) $manualFields['salary'];
        } elseif (isset($manualFields['monthly_gross_salary']) && is_numeric($manualFields['monthly_gross_salary'])) {
            $monthlyGross = (float) $manualFields['monthly_gross_salary'];
        }

        $annualGross = $monthlyGross * 12;
        $basicMonthly = $monthlyGross * 0.50;
        $basicAnnual = $basicMonthly * 12;
        $hraMonthly = $monthlyGross * 0.20;
        $hraAnnual = $hraMonthly * 12;
        $conveyanceMonthly = $monthlyGross > 0 ? 1600.0 : 0.0;
        $conveyanceAnnual = $conveyanceMonthly * 12;
        $ptaxMonthly = $monthlyGross > 15000 ? 200.0 : 0.0;
        
        $specialAllowanceMonthly = $monthlyGross - ($basicMonthly + $hraMonthly + $conveyanceMonthly);
        if ($specialAllowanceMonthly < 0) {
            $specialAllowanceMonthly = 0.0;
        }
        $specialAllowanceAnnual = $specialAllowanceMonthly * 12;
        $netPayMonthly = $monthlyGross - $ptaxMonthly;
        $salaryInWords = $this->numberToWords((int)$monthlyGross) . ' Rupees Only';

        // 5. Build full resolved placeholder library
        $data = [
            'employee_name' => $employeeName,
            'employee_first_name' => $firstName,
            'employee_address' => $employeeAddress,
            'employee_city' => $employeeCity,
            'employee_gender_title' => $genderTitle,
            'designation' => $designation,
            'department' => $department,
            'joining_date' => $joiningDate,
            'relieving_date' => $relievingDate,
            'internship_start_date' => $joiningDate,
            'internship_end_date' => $relievingDate ?: $this->formatDate($employee?->exit_date ?? null),
            'probation_period' => $probationPeriod,
            'notice_period_probation' => $noticePeriodProbation,
            'notice_period_confirmed' => $noticePeriodConfirmed,
            'office_location' => $workLocation,
            'work_location' => $workLocation,

            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'company_city' => $companyCity,
            'company_phone' => $companyPhone,
            'company_email' => $companyEmail,
            'company_website' => $companyWebsite,
            'company_gstin' => $companyGstin,

            'issue_date' => $now->format('d M, Y'),
            'generated_date' => $now->format('d M, Y'),
            'current_date' => $now->format('d M, Y'),
            'offer_valid_till' => $now->addDays(7)->format('d M, Y'),
            'document_title' => '',

            'monthly_gross_salary' => $monthlyGross > 0 ? number_format($monthlyGross, 2, '.', '') : '',
            'annual_gross_salary' => $annualGross > 0 ? number_format($annualGross, 2, '.', '') : '',
            'basic_monthly' => $basicMonthly > 0 ? number_format($basicMonthly, 2, '.', '') : '',
            'basic_annual' => $basicAnnual > 0 ? number_format($basicAnnual, 2, '.', '') : '',
            'hra_monthly' => $hraMonthly > 0 ? number_format($hraMonthly, 2, '.', '') : '',
            'hra_annual' => $hraAnnual > 0 ? number_format($hraAnnual, 2, '.', '') : '',
            'conveyance_monthly' => $conveyanceMonthly > 0 ? number_format($conveyanceMonthly, 2, '.', '') : '',
            'allowance_monthly' => $specialAllowanceMonthly > 0 ? number_format($specialAllowanceMonthly, 2, '.', '') : '',
            'special_allowance_monthly' => $specialAllowanceMonthly > 0 ? number_format($specialAllowanceMonthly, 2, '.', '') : '',
            'special_allowance_annual' => $specialAllowanceAnnual > 0 ? number_format($specialAllowanceAnnual, 2, '.', '') : '',
            'professional_tax_monthly' => $ptaxMonthly > 0 ? number_format($ptaxMonthly, 2, '.', '') : '',
            'net_pay_monthly' => $netPayMonthly > 0 ? number_format($netPayMonthly, 2, '.', '') : '',
            'annual_ctc' => $annualGross > 0 ? number_format($annualGross, 2, '.', '') : '',
            'salary_in_words' => $monthlyGross > 0 ? $salaryInWords : '',
            'salary' => $monthlyGross > 0 ? number_format($monthlyGross, 2, '.', '') : '',
            'salary_monthly' => $monthlyGross > 0 ? number_format($monthlyGross, 2, '.', '') : '',
            'salary_annual' => $annualGross > 0 ? number_format($annualGross, 2, '.', '') : '',

            'hr_manager_name' => $hrManagerName,
            'ceo_name' => $ceoName,
            'reporting_manager_name' => $managerName,
            'project_manager_name' => $projectManagerName,
            'manager_name' => $managerName ?: $projectManagerName,
            'authorized_signatory' => $authorizedSignatory,
            'hr_name' => $authorizedSignatory,

            'job_responsibilities' => '',
            'experience_responsibilities' => '',
            'internship_work_summary' => '',
            'performance_summary' => '',
            'handover_status' => '',
            
            'orb_primary' => (string) ($branding['primary_color'] ?? '#4B00E8'),
            'orb_secondary' => (string) ($branding['secondary_color'] ?? '#FF5252'),
        ];

        // 6. Manual overrides & Content Blocks
        foreach ($manualFields as $key => $value) {
            $normalizedKey = (string) Str::of((string) $key)->trim();
            if ($normalizedKey === '') {
                continue;
            }
            if ($value === null) {
                continue;
            }
            $stringValue = is_scalar($value) ? (string) $value : '';
            $data[$normalizedKey] = $stringValue;
        }

        return $data;
    }

    private function formatDate($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d M, Y');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function numberToWords(int $num): string
    {
        $ones = [
            0 => "Zero", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 5 => "Five",
            6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine", 10 => "Ten",
            11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen",
            15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen"
        ];
        
        $tens = [
            0 => "Zero", 1 => "Ten", 2 => "Twenty", 3 => "Thirty", 4 => "Forty",
            5 => "Fifty", 6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
        ];

        if ($num < 20) {
            return $ones[$num];
        }

        $res = "";
        
        // Crores
        if ($num >= 10000000) {
            $res .= $this->numberToWords((int)($num / 10000000)) . " Crore ";
            $num %= 10000000;
        }
        
        // Lakhs
        if ($num >= 100000) {
            $res .= $this->numberToWords((int)($num / 100000)) . " Lakh ";
            $num %= 100000;
        }
        
        // Thousands
        if ($num >= 1000) {
            $res .= $this->numberToWords((int)($num / 1000)) . " Thousand ";
            $num %= 1000;
        }
        
        // Hundreds
        if ($num >= 100) {
            $res .= $this->numberToWords((int)($num / 100)) . " Hundred ";
            $num %= 100;
        }

        if ($num > 0) {
            if ($res !== "") {
                $res .= "and ";
            }
            if ($num < 20) {
                $res .= $ones[$num];
            } else {
                $res .= $tens[(int)($num / 10)];
                if ($num % 10 > 0) {
                    $res .= " " . $ones[$num % 10];
                }
            }
        }

        return trim($res);
    }
}
