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

        /* =========================
           🔐 BASIC VALIDATION
        ========================= */

        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        $allowedExtensions = match ($type) {
            'profile' => ['jpg', 'jpeg', 'png', 'webp'],
            'resume' => ['pdf', 'doc', 'docx'],
            'document' => ['pdf', 'jpg', 'jpeg', 'png'],
            default => ['*'],
        };

        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions) && $allowedExtensions !== ['*']) {
            throw new \Exception('Invalid file type.');
        }

        // 5MB max safety
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \Exception('File size too large.');
        }

        /* =========================
           🔒 SECURE PATH
        ========================= */

        $secureHash = substr(md5($employeeId . $employeeCode), 0, 10);
        $basePath = "hrms/employees/{$secureHash}";

        $path = match ($type) {
            'profile' => "{$basePath}/profile",
            'resume' => "{$basePath}/resumes",
            'document' => "{$basePath}/documents/" . ($category ?: 'other'),
            default => "{$basePath}/other",
        };

        /* =========================
           🧠 FILE NAMING
        ========================= */

        $cleanEmployeeCode = strtoupper(str_replace('-', '', $employeeCode));

        $filename = match ($type) {
            'profile' => "IMG-{$cleanEmployeeCode}.{$extension}",

            // 👉 Resume versioning (important)
            'resume' => "RESUME-{$cleanEmployeeCode}-" . now()->format('YmdHis') . ".{$extension}",

            'document' => "DOC-{$cleanEmployeeCode}-" . time() . ".{$extension}",

            default => "{$type}-{$cleanEmployeeCode}-" . time() . ".{$extension}",
        };

        $fullPath = "{$path}/{$filename}";

        /* =========================
           ♻️ OVERWRITE PROFILE ONLY
        ========================= */

        if ($type === 'profile') {
            foreach (Storage::disk('private')->files($path) as $oldFile) {
                Storage::disk('private')->delete($oldFile);
            }
        }

        /* =========================
           📤 UPLOAD
        ========================= */

        $uploadedPath = Storage::disk('private')->putFileAs(
            $path,
            $file,
            $filename
        );

        if (!$uploadedPath || !Storage::disk('private')->exists($uploadedPath)) {
            throw new \Exception('File upload failed.');
        }

        /* =========================
           🧾 LOG (Optional but pro)
        ========================= */

        // logger()->info('File uploaded', [
        //     'employee_id' => $employeeId,
        //     'type' => $type,
        //     'path' => $uploadedPath,
        // ]);

        return $uploadedPath;
    }
}