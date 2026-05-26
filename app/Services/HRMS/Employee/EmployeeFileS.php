<?php

namespace App\Services\HRMS\Employee;

use App\Services\HRMS\Storage\HrmsStoragePathS;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmployeeFileS
{
    public function __construct(private HrmsStoragePathS $paths)
    {
    }

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

        $path = match ($type) {
            'profile' => $this->paths->employeeProfile($employeeId, 'avatar'),
            'resume' => $this->paths->employeeOnboarding($employeeId, 'resume'),
            'document' => $this->paths->mapEmployeeDocumentType($employeeId, $category),
            default => $this->paths->employeeHrDocument($employeeId, 'misc'),
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
