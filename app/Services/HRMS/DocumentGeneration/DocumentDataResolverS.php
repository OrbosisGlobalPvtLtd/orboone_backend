<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Support\Facades\Auth;

class DocumentDataResolverS
{
    protected $placeholderResolver;

    public function __construct(DocumentPlaceholderResolverS $placeholderResolver)
    {
        $this->placeholderResolver = $placeholderResolver;
    }

    /**
     * Resolve final data array for Blade template rendering.
     */
    public function resolve(?int $employeeId, array $formData): array
    {
        $employee = $employeeId ? EmployeeM::find($employeeId) : null;
        $defaultNames = ['Employee Name', 'Intern Name', 'Candidate Name', ''];
        
      
        if ($employee) {
            $empName = $employee->employee_name ?: $employee->full_name ?: $employee->display_name ?: '';
            $formData['candidate_name'] = $empName;
            $formData['employee_name'] = $empName;
            $formData['intern_name'] = $empName;
        } else {
            
            $manualName = null;
            if (!empty($formData['candidate_name']) && !in_array(trim($formData['candidate_name']), $defaultNames, true)) {
                $manualName = trim($formData['candidate_name']);
            } elseif (!empty($formData['employee_name']) && !in_array(trim($formData['employee_name']), $defaultNames, true)) {
                $manualName = trim($formData['employee_name']);
            } elseif (!empty($formData['intern_name']) && !in_array(trim($formData['intern_name']), $defaultNames, true)) {
                $manualName = trim($formData['intern_name']);
            }

            if ($manualName !== null) {
                $formData['candidate_name'] = $manualName;
                $formData['employee_name'] = $manualName;
                $formData['intern_name'] = $manualName;
            }
        }
        
        // Resolve company settings, employee profile defaults, and other static details
        $resolved = $this->placeholderResolver->resolve($employee, $formData, Auth::user());

        
        foreach ($formData as $key => $value) {
            if ($value === null || (is_string($value) && trim($value) === '')) {
                if ($this->isParagraphField($key)) {
                    $resolved[$key] = ' ';
                } else {
                    
                    continue;
                }
            } else {
                // Do not overwrite signature_image and seal_image if they were already resolved to base64
                if (in_array($key, ['signature_image', 'seal_image']) && str_starts_with($resolved[$key] ?? '', 'data:image')) {
                    continue;
                }
                $resolved[$key] = $value;
            }
        }

        // Additional safety normalization for recipient names
        if (isset($manualName) && $manualName !== null) {
            $resolved['candidate_name'] = $manualName;
            $resolved['employee_name'] = $manualName;
            $resolved['intern_name'] = $manualName;
            $parts = explode(' ', trim($manualName));
            $resolved['employee_first_name'] = !empty($parts[0]) ? $parts[0] : $manualName;
            $resolved['candidate_first_name'] = $resolved['employee_first_name'];
            $resolved['intern_first_name'] = $resolved['employee_first_name'];
        } elseif (!empty($resolved['employee_name'])) {
            $resolved['candidate_name'] = $resolved['candidate_name'] ?: $resolved['employee_name'];
            $resolved['intern_name'] = $resolved['intern_name'] ?: $resolved['employee_name'];
        } elseif (!empty($resolved['candidate_name'])) {
            $resolved['employee_name'] = $resolved['candidate_name'];
            $resolved['intern_name'] = $resolved['candidate_name'];
        }

        if (empty($resolved['employee_first_name']) && !empty($resolved['employee_name'])) {
            $parts = explode(' ', trim($resolved['employee_name']));
            $resolved['employee_first_name'] = !empty($parts[0]) ? $parts[0] : null;
            $resolved['candidate_first_name'] = $resolved['employee_first_name'];
            $resolved['intern_first_name'] = $resolved['employee_first_name'];
        }

        return $resolved;
    }

    /**
     * Check if a field key is a paragraph / clause field.
     */
    private function isParagraphField(string $key): bool
    {
        $key = strtolower($key);
        $suffixes = ['_clause', '_paragraph', '_reason', '_status', '_remarks', '_summary', '_responsibilities'];
        foreach ($suffixes as $suffix) {
            if (str_ends_with($key, $suffix)) {
                return true;
            }
        }
        return in_array($key, [
            'job_responsibilities',
            'handover_clause'
        ]);
    }
}
