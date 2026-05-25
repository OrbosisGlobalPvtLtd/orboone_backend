<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DocumentFieldResolverS
{
    public function resolveFields(string $template, ?EmployeeM $employee, array $manualFields = []): string
    {
        $data = $this->getDefaultCompanyData();

        if ($employee) {
            $employeeData = $this->getEmployeeData($employee);
            $data = array_merge($data, $employeeData);
        }

        // Merge manual overrides
        foreach ($manualFields as $key => $value) {
            if ($value !== null && $value !== '') {
                $data[$key] = $value;
            }
        }

        // Replace placeholders
        $resolvedTemplate = $template;
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $resolvedTemplate = str_replace($placeholder, $value ?? '', $resolvedTemplate);
        }

        // Log unreplaced placeholders maybe?
        return $resolvedTemplate;
    }

    private function getDefaultCompanyData(): array
    {
        return [
            'company_name' => 'Orbosis Global Pvt. Ltd.',
            'company_address' => 'Sample Address, City', // Consider fetching from settings
            'hr_name' => 'HR Department',
            'current_date' => Carbon::now()->format('d M, Y'),
        ];
    }

    private function getEmployeeData(EmployeeM $employee): array
    {
        $employee->loadMissing(['profile', 'designation', 'department', 'reportingManager', 'salaryStructure']);

        $monthlySalary = 0;
        $annualSalary = 0;
        $basic = 0;
        $hra = 0;
        $special = 0;
        $pt = 0;
        $takeHome = 0;

        if ($employee->salaryStructure) {
            $monthlySalary = $employee->salaryStructure->gross_salary;
            $annualSalary = $monthlySalary * 12;
            $basic = $employee->salaryStructure->basic;
            $hra = $employee->salaryStructure->hra;
            $special = $employee->salaryStructure->special_allowance ?? 0;
            // Additional calculations if needed
        }

        return [
            'employee_name' => $employee->employee_name ?? $employee->full_name ?? '',
            'employee_code' => $employee->employee_code ?? '',
            'designation' => $employee->designation->name ?? '',
            'department' => $employee->department->name ?? '',
            'joining_date' => $employee->joining_date ? Carbon::parse($employee->joining_date)->format('d M, Y') : '',
            'confirmation_date' => $employee->profile->confirmation_date ? Carbon::parse($employee->profile->confirmation_date)->format('d M, Y') : '',
            'probation_end_date' => $employee->probation_end_date ? Carbon::parse($employee->probation_end_date)->format('d M, Y') : '',
            'work_location' => $employee->profile->work_location ?? 'Remote',
            'reporting_manager' => $employee->reportingManager->employee_name ?? $employee->reportingManager->full_name ?? '',
            
            // Salary fields
            'salary_monthly' => number_format($monthlySalary, 2),
            'salary_annual' => number_format($annualSalary, 2),
            'basic_salary' => number_format($basic, 2),
            'hra' => number_format($hra, 2),
            'special_allowance' => number_format($special, 2),
            'professional_tax' => number_format($pt, 2),
            'take_home' => number_format($takeHome, 2),
        ];
    }
}
