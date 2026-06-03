<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\Employee\EmployeeM;

class DocumentFieldResolverS
{
    public function __construct(private DocumentPlaceholderResolverS $placeholderResolver)
    {
    }

    public function resolveFields(string $template, ?EmployeeM $employee, array $manualFields = []): string
    {
        $data = $this->placeholderResolver->resolve($employee, $manualFields, auth()->user());
        $resolvedTemplate = $template;

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $resolvedTemplate = str_replace($placeholder, $value ?? '', $resolvedTemplate);
        }

        return $resolvedTemplate;
    }
}
