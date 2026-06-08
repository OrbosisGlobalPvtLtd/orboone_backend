<?php

namespace App\Services\HRMS\Document;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HrmsFileStorageS
{
    /**
     * Build standard trimmed employee code.
     */
    public function buildEmployeeCode(EmployeeM $employee): string
    {
        return trim(strtoupper($employee->employee_code));
    }

    /**
     * Slugify the document type to lowercase slug.
     */
    public function slugDocumentType(DocumentTypeM|string $type): string
    {
        $value = ($type instanceof DocumentTypeM) ? ($type->slug ?: ($type->code ?: $type->name)) : $type;
        $slug = strtolower(trim(str_replace(['_', ' '], '-', $value)));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-') ?: 'misc';
    }

    /**
     * Build standardized relative private path for an employee document.
     */
    public function buildEmployeeDocumentPath(EmployeeM $employee, DocumentTypeM|string $type, UploadedFile $file): string
    {
        $employeeCode = $this->buildEmployeeCode($employee);
        $docTypeSlug = $this->slugDocumentType($type);
        $docTypeCode = strtoupper($docTypeSlug);
        $timestamp = now()->format('Ymd_His');
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');

        return "hrms/employees/{$employeeCode}/documents/{$docTypeSlug}/{$employeeCode}_{$docTypeCode}_{$timestamp}.{$ext}";
    }

    /**
     * Build standardized relative private path for employee profile avatar.
     */
    public function buildProfileAvatarPath(EmployeeM $employee, UploadedFile $file): string
    {
        $employeeCode = $this->buildEmployeeCode($employee);
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');

        return "hrms/employees/{$employeeCode}/profile/avatar/{$employeeCode}_PROFILE.{$ext}";
    }

    /**
     * Store an employee document and return standard metadata array.
     */
    public function storeEmployeeDocument(EmployeeM $employee, DocumentTypeM|string $type, UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($ext, $allowedExtensions)) {
            throw new \Exception('Invalid file extension.');
        }

        if (!in_array($mime, $allowedMimes)) {
            throw new \Exception('Invalid file content MIME type.');
        }

        $path = $this->buildEmployeeDocumentPath($employee, $type, $file);
        $dir = dirname($path);
        $filename = basename($path);

        Storage::disk('private')->putFileAs($dir, $file, $filename);

        return [
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $filename,
            'file_path' => $path,
            'disk' => 'private',
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'uploaded_at' => now(),
        ];
    }

    /**
     * Replace profile avatar: deletes any old files in the directory first to prevent orphans, saves the new one.
     */
    public function replaceProfileAvatar(EmployeeM $employee, UploadedFile $file): string
    {
        $employeeCode = $this->buildEmployeeCode($employee);
        $dir = "hrms/employees/{$employeeCode}/profile/avatar";

        // 1. Delete all old files in the avatar directory to prevent naming or extension orphans
        if (Storage::disk('private')->exists($dir)) {
            foreach (Storage::disk('private')->files($dir) as $oldFile) {
                Storage::disk('private')->delete($oldFile);
            }
        }

        // 2. Save the new avatar file
        $path = $this->buildProfileAvatarPath($employee, $file);
        $filename = basename($path);
        Storage::disk('private')->putFileAs($dir, $file, $filename);

        // 3. Update the employee profile record if it exists
        $profile = $employee->profile;
        if ($profile) {
            $profile->update(['profile_image' => $path]);
        }

        return $path;
    }

    /**
     * Delete file if it exists.
     */
    public function deleteFileIfExists(?string $path, string $disk = 'private'): bool
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }

    /**
     * Archive or replace employee document based on verification status.
     */
    public function archiveOrReplaceEmployeeDocument(EmployeeM $employee, DocumentTypeM|string $type, UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($ext, $allowedExtensions)) {
            throw new \Exception('Invalid file extension.');
        }

        if (!in_array($mime, $allowedMimes)) {
            throw new \Exception('Invalid file content MIME type.');
        }

        $docType = ($type instanceof DocumentTypeM) ? $type : DocumentTypeM::where('code', $type)->orWhere('name', $type)->first();
        $docTypeId = $docType ? $docType->id : null;

        // Find existing active document for this type
        $existingQuery = EmployeeDocumentM::where('employee_id', $employee->id)
            ->where('document_type_id', $docTypeId);

        if (Schema::hasColumn('employee_documents_new', 'is_active')) {
            $existingQuery->where('is_active', 1);
        }

        $existing = $existingQuery->first();

        $path = $this->buildEmployeeDocumentPath($employee, $type, $file);
        $dir = dirname($path);
        $filename = basename($path);

        if ($existing) {
            $status = $existing->verification_status; // 'pending', 'verified', 'rejected'

            if ($status === 'verified') {
                // Case 3: Approved/verified - do NOT delete old file, keep it in storage (archive it)
                if (Schema::hasColumn('employee_documents_new', 'is_active')) {
                    $existing->is_active = false;
                    $existing->archived_at = now();
                    $existing->archived_by = Auth::id();
                    $existing->archive_reason = 'Re-uploaded by employee/admin';
                    $existing->save();
                }
            } else {
                // Case 1 & 2: Pending or Rejected - delete old physical file to clean up storage
                $this->deleteFileIfExists($existing->file_path);
            }
        }

        // Store new file
        Storage::disk('private')->putFileAs($dir, $file, $filename);

        return [
            'file_path' => $path,
            'file_name' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'uploaded_at' => now(),
        ];
    }
}
