<?php

namespace App\Services\HRMS\Employee;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeFileS
{
    public function upload(
        UploadedFile $file,
        int $employeeId,
        string $employeeCode,
        string $type,
        ?string $category = null
    ): string {
        $basePath = "hrms/employees/{$employeeId}";

        $path = match ($type) {
            'profile' => "{$basePath}/profile",
            'resume' => "{$basePath}/resumes",
            'document' => "{$basePath}/documents/".($category ?: 'other'),
            'onboarding' => "{$basePath}/onboarding",
            'payroll' => "{$basePath}/payroll/".date('Y'),
            'exit' => "{$basePath}/exit",
            default => "{$basePath}/other",
        };

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $cleanEmployeeCode = Str::slug($employeeCode);
        $cleanOriginalName = Str::slug($originalName);
        $extension = strtolower($file->getClientOriginalExtension());

        $filename = $type.'_'.$cleanEmployeeCode.'_'.time().'_'.$cleanOriginalName.'.'.$extension;

        $uploadedPath = Storage::disk('private')->putFileAs($path, $file, $filename);

        if (! $uploadedPath || ! Storage::disk('private')->exists($uploadedPath)) {
            throw new \Exception('File upload failed. File storage/app/private path me save nahi hui.');
        }

        return $uploadedPath;
    }
}
