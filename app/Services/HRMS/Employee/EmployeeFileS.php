<?php

namespace App\Services\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Document\HrmsFileStorageS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmployeeFileS
{
    public function __construct(
        private HrmsStoragePathS $paths,
        private HrmsFileStorageS $storageService
    ) {
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

        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?: ''));
        $mime = strtolower($file->getMimeType() ?: '');

        if ($type === 'profile') {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'heic', 'heif'];
            $isImageMime = str_starts_with($mime, 'image/') || in_array($mime, ['application/octet-stream', 'binary/octet-stream']);

            if (!in_array($extension, $allowedExts) && !$isImageMime) {
                throw new \Exception('Invalid file type. Allowed profile image formats: JPG, JPEG, PNG, WEBP.');
            }
        } elseif ($type === 'resume') {
            $allowedExts = ['pdf', 'doc', 'docx'];
            $isDocMime = in_array($mime, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream', 'text/plain']);

            if (!in_array($extension, $allowedExts) && !$isDocMime) {
                throw new \Exception('Invalid file type. Allowed resume formats: PDF, DOC, DOCX.');
            }
        } elseif ($type === 'document') {
            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'heic', 'heif'];
            $isAllowedMime = str_starts_with($mime, 'image/') || in_array($mime, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream']);

            if (!in_array($extension, $allowedExts) && !$isAllowedMime) {
                throw new \Exception('Invalid file type. Allowed document formats: PDF, JPG, JPEG, PNG, WEBP, DOC, DOCX.');
            }
        }

        // 10MB max safety
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \Exception('File size too large (max 10MB).');
        }

        /* =========================
           ♻️ OVERWRITE PROFILE ONLY
        ========================= */

        if ($type === 'profile') {
            $employee = EmployeeM::find($employeeId);
            if ($employee) {
                return $this->storageService->replaceProfileAvatar($employee, $file);
            }
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
           🧹 CLEANUP OLD RESUME
        ========================= */

        if ($type === 'resume') {
            $existingProfileResume = \Illuminate\Support\Facades\DB::table('employee_profiles')
                ->where('employee_id', $employeeId)
                ->value('resume_file');

            if ($existingProfileResume && Storage::disk('private')->exists($existingProfileResume)) {
                Storage::disk('private')->delete($existingProfileResume);
            }

            if (Storage::disk('private')->exists($path)) {
                foreach (Storage::disk('private')->files($path) as $oldResumeFile) {
                    Storage::disk('private')->delete($oldResumeFile);
                }
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
