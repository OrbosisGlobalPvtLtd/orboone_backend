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
        
        // If employee is selected, force candidate_name and employee_name to match employee name
        if ($employee) {
            $empName = $employee->employee_name ?: $employee->full_name ?: $employee->display_name ?: '';
            $formData['candidate_name'] = $empName;
            $formData['employee_name'] = $empName;
        }
        
        // Resolve company settings, employee profile defaults, and other static details
        $resolved = $this->placeholderResolver->resolve($employee, $formData, Auth::user());

        // Explicitly merge all raw form data keys to ensure they take top priority in Blade views
        foreach ($formData as $key => $value) {
            if ($value !== null && $value !== '') {
                // Do not overwrite signature_image and seal_image if they were already resolved to base64
                if (in_array($key, ['signature_image', 'seal_image']) && str_starts_with($resolved[$key] ?? '', 'data:image')) {
                    continue;
                }
                $resolved[$key] = $value;
            }
        }

        // Additional safety fallbacks for candidate vs employee names
        if (empty($resolved['candidate_name']) && !empty($formData['candidate_name'])) {
            $resolved['candidate_name'] = $formData['candidate_name'];
        }
        
        if (empty($resolved['employee_name']) && !empty($resolved['candidate_name'])) {
            $resolved['employee_name'] = $resolved['candidate_name'];
        }

        if (empty($resolved['candidate_name']) && !empty($resolved['employee_name'])) {
            $resolved['candidate_name'] = $resolved['employee_name'];
        }

        if (empty($resolved['employee_first_name']) && !empty($resolved['employee_name'])) {
            $parts = explode(' ', trim($resolved['employee_name']));
            $resolved['employee_first_name'] = !empty($parts[0]) ? $parts[0] : null;
        }

        return $resolved;
    }
}
