<?php

namespace App\Services\HRMS\Document;

use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class EmployeeDocumentCompletionS
{
    public function employeeForUser(int $userId): ?EmployeeM
    {
        return EmployeeM::with(['user', 'profile', 'documents.type'])
            ->where('user_id', $userId)
            ->first();
    }

    public function experienceType(EmployeeM $employee): string
    {
        $value = strtolower(trim((string) ($employee->profile?->experience_type ?? '')));

        if (in_array($value, ['fresher', 'experienced'], true)) {
            return $value;
        }

        $experience = strtolower(trim((string) ($employee->profile?->total_experience ?? '')));

        if ($experience !== '' && preg_match('/\d+(\.\d+)?/', $experience, $matches)) {
            return ((float) $matches[0]) > 0 ? 'experienced' : 'fresher';
        }

        return 'fresher';
    }

    public function missingProfileFields(?EmployeeProfileM $profile, ?EmployeeM $employee = null): array
    {
        if (! $profile) {
            return [
                'emergency_contact_number',
                'date_of_birth',
                'gender',
                'address',
                'highest_qualification',
                'cgpa_percentage',
                'total_experience',
                'resume_file',
                'bank_account_no',
                'bank_account_type',
                'bank_holder_name',
                'ifsc_code',
                'bank_branch',
                'experience_type',
            ];
        }

        $fields = [
            'emergency_contact_number' => $profile->emergency_contact_number,
            'date_of_birth' => $profile->date_of_birth,
            'gender' => $profile->gender,
            'address' => $profile->address,
            'highest_qualification' => $profile->highest_qualification,
            'cgpa_percentage' => $profile->cgpa_percentage,
            'total_experience' => $profile->total_experience,
            'resume_file' => $profile->resume_file,
            'bank_account_no' => $profile->bank_account_no,
            'bank_account_type' => $profile->bank_account_type,
            'bank_holder_name' => $profile->bank_holder_name,
            'ifsc_code' => $profile->ifsc_code,
            'bank_branch' => $profile->bank_branch,
            'experience_type' => $profile->experience_type,
        ];

        $missing = [];

        foreach ($fields as $key => $value) {
            if (is_null($value) || trim((string) $value) === '') {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    public function isProfileComplete(?EmployeeProfileM $profile, ?EmployeeM $employee = null): bool
    {
        return count($this->missingProfileFields($profile, $employee)) === 0;
    }

    public function profileCompletionPercentage(?EmployeeProfileM $profile, ?EmployeeM $employee = null): int
    {
        $total = 14;
        $missing = count($this->missingProfileFields($profile, $employee));

        return (int) floor((($total - $missing) / $total) * 100);
    }

    public function requiredTypes(string $experienceType): Collection
    {
        return $this->typeQuery($experienceType)
            ->where('is_mandatory', 1)
            ->orderBy('id')
            ->get();
    }

    public function optionalTypes(string $experienceType): Collection
    {
        return $this->typeQuery($experienceType)
            ->where('is_mandatory', 0)
            ->orderBy('id')
            ->get();
    }

    public function uploadedDocuments(EmployeeM $employee): Collection
    {
        $query = EmployeeDocumentM::with(['type', 'uploadedBy', 'verifiedBy'])
            ->where('employee_id', $employee->id);

        if (\Illuminate\Support\Facades\Schema::hasColumn('employee_documents_new', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query->orderByDesc('uploaded_at')
            ->orderByDesc('id')
            ->get();
    }

    public function completion(EmployeeM $employee, ?Collection $requiredTypes = null, ?Collection $documents = null): array
    {
        $experienceType = $this->experienceType($employee);
        $requiredTypes = $requiredTypes ?: $this->requiredTypes($experienceType);
        $documents = $documents ?: $this->uploadedDocuments($employee);
        $latestByType = $documents->whereNotNull('document_type_id')->unique('document_type_id')->keyBy('document_type_id');

        $verified = 0;
        $pending = 0;
        $rejected = 0;
        $missing = 0;
        $uploaded = 0;

        foreach ($requiredTypes as $type) {
            $doc = $latestByType->get($type->id);

            if (! $doc) {
                $missing++;
                continue;
            }

            $uploaded++;

            if ($doc->verification_status === 'verified') {
                $verified++;
            } elseif ($doc->verification_status === 'rejected') {
                $rejected++;
            } else {
                $pending++;
            }
        }

        $requiredCount = $requiredTypes->count();
        $profileComplete = $this->isProfileComplete($employee->profile, $employee);

        return [
            'required_count' => $requiredCount,
            'uploaded_count' => $uploaded,
            'verified_count' => $verified,
            'pending_count' => $pending,
            'rejected_count' => $rejected,
            'missing_count' => $missing,
            'can_submit_for_verification' => $requiredCount > 0 && $missing === 0 && $rejected === 0,
            'can_punch_attendance' => $profileComplete && $missing === 0 && $rejected === 0 && $verified === $requiredCount,
        ];
    }

    public function requiredPayload(EmployeeM $employee): array
    {
        $experienceType = $this->experienceType($employee);
        $requiredTypes = $this->requiredTypes($experienceType);
        $optionalTypes = $this->optionalTypes($experienceType);
        $documents = $this->uploadedDocuments($employee);

        return [
            'experience_type' => $experienceType,
            'required_documents' => $requiredTypes->map(fn ($type) => $this->formatType($type))->values(),
            'optional_documents' => $optionalTypes->map(fn ($type) => $this->formatType($type))->values(),
            'uploaded_documents' => $documents->map(fn ($doc) => $this->formatDocument($doc))->values(),
            'completion' => $this->completion($employee, $requiredTypes, $documents),
        ];
    }

    public function missingRequiredTypes(EmployeeM $employee): Collection
    {
        $experienceType = $this->experienceType($employee);
        $requiredTypes = $this->requiredTypes($experienceType);
        $documents = $this->uploadedDocuments($employee)
            ->whereNotNull('document_type_id')
            ->unique('document_type_id')
            ->keyBy('document_type_id');

        return $requiredTypes->filter(fn ($type) => ! $documents->has($type->id))->values();
    }

    public function rejectedRequiredDocuments(EmployeeM $employee): Collection
    {
        $experienceType = $this->experienceType($employee);
        $requiredTypeIds = $this->requiredTypes($experienceType)->pluck('id')->all();
        $documents = $this->uploadedDocuments($employee)->whereNotNull('document_type_id')->unique('document_type_id');

        return $documents
            ->filter(fn ($doc) => in_array($doc->document_type_id, $requiredTypeIds, true) && $doc->verification_status === 'rejected')
            ->values();
    }

    public function formatType(DocumentTypeM $type): array
    {
        return [
            'id' => $type->id,
            'name' => $type->name,
            'code' => $type->code ?? null,
            'scope' => $type->scope,
            'applies_to' => $type->applies_to ?? 'all',
            'is_mandatory' => (bool) $type->is_mandatory,
            'has_expiry' => (bool) $type->has_expiry,
            'is_active' => (bool) ($type->is_active ?? true),
            'allowed_extensions' => $this->allowedExtensions($type),
            'max_file_size_mb' => (int) ($type->max_file_size_mb ?? 5),
            'allow_multiple' => (bool) ($type->allow_multiple ?? false),
        ];
    }

    public function formatDocument(EmployeeDocumentM $document): array
    {
        return [
            'id' => $document->id,
            'employee_id' => $document->employee_id,
            'document_type_id' => $document->document_type_id,
            'document_type' => $document->type ? $this->formatType($document->type) : null,
            'title' => $document->title,
            'file_path' => $document->file_path,
            'file_url' => $this->fileUrl($document->file_path),
            'file_original_name' => $document->file_original_name,
            'file_mime_type' => $document->file_mime_type,
            'file_size' => $document->file_size,
            'verification_status' => $document->verification_status,
            'verified_by_user_id' => $document->verified_by_user_id,
            'verified_at' => optional($document->verified_at)->toDateTimeString(),
            'rejection_reason' => $document->rejection_reason,
            'expiry_date' => optional($document->expiry_date)->toDateString(),
            'is_required' => (bool) $document->is_required,
            'uploaded_at' => optional($document->uploaded_at ?: $document->created_at)->toDateTimeString(),
            'can_delete' => $document->verification_status !== 'verified',
        ];
    }

    public function fileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return url('/api/v1/file?path='.urlencode($path));
    }

    private function allowedExtensions(DocumentTypeM $type): array
    {
        $extensions = $type->allowed_extensions ?? null;

        if (is_string($extensions)) {
            $decoded = json_decode($extensions, true);
            $extensions = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($extensions) || empty($extensions)) {
            return ['pdf'];
        }

        return collect($extensions)
            ->map(fn ($ext) => strtolower(trim((string) $ext)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function typeQuery(string $experienceType)
    {
        $query = DocumentTypeM::query()->where('scope', 'employee');

        if (Schema::hasColumn('document_types', 'is_active')) {
            $query->where('is_active', 1);
        }

        if (Schema::hasColumn('document_types', 'applies_to')) {
            $query->whereIn('applies_to', ['all', $experienceType]);
        }

        return $query;
    }
}
