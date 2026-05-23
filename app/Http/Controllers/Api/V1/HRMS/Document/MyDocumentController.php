<?php

namespace App\Http\Controllers\Api\V1\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\CompanyDocumentM;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Services\HRMS\Document\DocumentS;
use App\Services\HRMS\Document\EmployeeDocumentCompletionS;
use App\Services\HRMS\Notification\NotificationS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MyDocumentController extends Controller
{
    public function __construct(
        private EmployeeDocumentCompletionS $completionService,
        private DocumentS $documentService,
    ) {}

    public function requiredDocuments()
    {
        $employee = $this->completionService->employeeForUser((int) auth()->id());

        if (! $employee) {
            return $this->apiResponse(false, 'Employee record not found.', null, 404);
        }

        return $this->apiResponse(
            true,
            'Required documents fetched successfully.',
            $this->completionService->requiredPayload($employee)
        );
    }

    public function myDocuments()
    {
        $employee = $this->completionService->employeeForUser((int) auth()->id());

        if (! $employee) {
            return $this->apiResponse(false, 'Employee record not found.', null, 404);
        }

        $documents = $this->completionService->uploadedDocuments($employee)
            ->map(fn($document) => $this->completionService->formatDocument($document))
            ->values();

        return $this->apiResponse(true, 'Your documents fetched successfully.', [
            'uploaded_documents' => $documents,
            'completion' => $this->completionService->completion($employee),
        ]);
    }

    public function upload(Request $request)
    {
        $employee = $this->completionService->employeeForUser((int) auth()->id());

        if (! $employee) {
            return $this->apiResponse(false, 'Employee record not found.', null, 404);
        }

        $baseValidator = Validator::make($request->all(), [
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
        ]);

        if ($baseValidator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $baseValidator->errors());
        }

        $type = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', true)
            ->find($request->document_type_id);

        if (! $type) {
            return $this->apiResponse(false, 'Invalid employee document type.', null, 422, [
                'document_type_id' => ['Invalid employee document type.'],
            ]);
        }

        $allowedExtensions = $this->allowedExtensionsFor($type);
        $maxFileSizeMb = (int) ($type->max_file_size_mb ?: 5);
        $maxFileSizeKb = max($maxFileSizeMb, 1) * 1024;

        $validator = Validator::make($request->all(), [
            'document_type_id' => ['required', 'integer', Rule::exists('document_types', 'id')],
            'title' => ['nullable', 'string', 'max:200'],
            'expiry_date' => [(bool) $type->has_expiry ? 'required' : 'nullable', 'date'],
            'file' => [
                'required',
                'file',
                'mimes:' . implode(',', $allowedExtensions),
                'max:' . $maxFileSizeKb,
            ],
        ], [
            'file.mimes' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions) . '.',
            'file.max' => 'File size must not be greater than ' . $maxFileSizeMb . ' MB.',
            'expiry_date.required' => 'Expiry date is required for this document.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $validator->errors());
        }

        $verifiedExists = EmployeeDocumentM::where('employee_id', $employee->id)
            ->where('document_type_id', $type->id)
            ->where('verification_status', 'verified')
            ->exists();

        if ($verifiedExists) {
            return $this->apiResponse(false, 'Verified documents cannot be replaced.', null, 422, [
                'document_type_id' => ['This document type is already verified.'],
            ]);
        }

        $file = $request->file('file');

        $path = $this->documentService->storePrivateEmployeeDocumentFile(
            $file,
            (int) $employee->id,
            (string) ($type->code ?: $type->name)
        );

        $document = EmployeeDocumentM::create([
            'employee_id' => $employee->id,
            'document_type_id' => $type->id,
            'uploaded_by_user_id' => auth()->id(),
            'title' => $request->input('title') ?: $type->name,
            'file_path' => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'verification_status' => 'pending',
            'verified_by_user_id' => null,
            'verified_at' => null,
            'rejection_reason' => null,
            'expiry_date' => $request->input('expiry_date'),
            'is_required' => (bool) $type->is_mandatory,
            'uploaded_at' => now(),
        ]);

        $freshDocument = $document->fresh(['type', 'uploadedBy', 'verifiedBy', 'employee.user']);
        app(NotificationS::class)->notifyHrAndSuperAdmin(
            'Employee Document Uploaded',
            ($employee->user?->name ?: $employee->employee_code) . ' uploaded ' . ($freshDocument->title ?: $type->name) . ' for verification.',
            'document_uploaded',
            'documents',
            ['document_id' => $freshDocument->id, 'employee_id' => $employee->id],
            $this->documentNotificationPayload($freshDocument, [
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
                'employee_name' => $employee->user?->name ?: $employee->employee_code,
                'document_id' => $freshDocument->id,
                'document_title' => $freshDocument->title,
            ])
        );

        return $this->apiResponse(
            true,
            'Document uploaded successfully. It is pending HR verification.',
            $this->completionService->formatDocument($freshDocument),
            201
        );
    }

    private function allowedExtensionsFor(DocumentTypeM $type): array
    {
        $extensions = $type->allowed_extensions;

        if (is_string($extensions)) {
            $decoded = json_decode($extensions, true);
            $extensions = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($extensions) || empty($extensions)) {
            $extensions = ['pdf'];
        }

        return collect($extensions)
            ->map(fn($ext) => strtolower(trim((string) $ext)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
    public function submitForVerification()
    {
        $employee = $this->completionService->employeeForUser((int) auth()->id());

        if (! $employee) {
            return $this->apiResponse(false, 'Employee record not found.', null, 404);
        }

        if (! $employee->profile) {
            return $this->apiResponse(false, 'Please complete your profile before submitting documents.', null, 422, [
                'profile' => ['Profile not found.'],
            ]);
        }

        $missingProfileFields = $this->completionService->missingProfileFields($employee->profile, $employee);

        if (! empty($missingProfileFields)) {
            return $this->apiResponse(false, 'Please complete your profile before submitting documents.', null, 422, [
                'profile' => $missingProfileFields,
            ]);
        }

        $missingRequiredTypes = $this->completionService->missingRequiredTypes($employee);

        if ($missingRequiredTypes->isNotEmpty()) {
            return $this->apiResponse(false, 'Please upload all required documents before submitting for verification.', null, 422, [
                'missing_documents' => $missingRequiredTypes->pluck('name')->values(),
            ]);
        }

        $rejectedRequired = $this->completionService->rejectedRequiredDocuments($employee);

        if ($rejectedRequired->isNotEmpty()) {
            return $this->apiResponse(false, 'Please re-upload rejected required documents before submitting.', null, 422, [
                'rejected_documents' => $rejectedRequired->pluck('title')->values(),
            ]);
        }

        $completion = $this->completionService->completion($employee);

        $isAlreadySubmitted = $employee->profile
            && $employee->profile->is_profile_completed
            && $employee->profile->profile_status === 'submitted';

        if (! $isAlreadySubmitted) {
            $employee->profile->update([
                'is_profile_completed' => true,
                'profile_status'       => 'submitted',
                'profile_completed_at' => now(),
                'rejection_reason'     => null,
            ]);

            $employeeName = $employee->user?->name ?: $employee->employee_code;

            app(NotificationS::class)->notifyHrAndSuperAdmin(
                'Profile Verification Request',
                $employeeName . ' submitted profile and documents for verification.',
                'profile_submitted',
                'hrms.documents.employee.show',
                ['employee' => $employee->id],
                [
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'employee_code' => $employee->employee_code,
                ]
            );
        }

        return $this->apiResponse(true, 'Documents submitted for verification.', [
            'completion' => $completion,
        ]);
    }

    // public function submitForVerification()
    // {
    //     $employee = $this->completionService->employeeForUser((int) auth()->id());

    //     if (! $employee) {
    //         return $this->apiResponse(false, 'Employee record not found.', null, 404);
    //     }

    //     $missingProfileFields = $this->completionService->missingProfileFields($employee->profile, $employee);

    //     if (! empty($missingProfileFields)) {
    //         return $this->apiResponse(false, 'Please complete your profile before submitting documents.', null, 422, [
    //             'profile' => $missingProfileFields,
    //         ]);
    //     }

    //     $missingRequiredTypes = $this->completionService->missingRequiredTypes($employee);

    //     if ($missingRequiredTypes->isNotEmpty()) {
    //         return $this->apiResponse(false, 'Please upload all required documents before submitting for verification.', null, 422, [
    //             'missing_documents' => $missingRequiredTypes->pluck('name')->values(),
    //         ]);
    //     }

    //     $rejectedRequired = $this->completionService->rejectedRequiredDocuments($employee);

    //     if ($rejectedRequired->isNotEmpty()) {
    //         return $this->apiResponse(false, 'Please re-upload rejected required documents before submitting.', null, 422, [
    //             'rejected_documents' => $rejectedRequired->pluck('title')->values(),
    //         ]);
    //     }

    //     $completion = $this->completionService->completion($employee);

    //     $isAlreadySubmitted = $employee->profile && $employee->profile->profile_status === 'submitted';

    //     if (! $isAlreadySubmitted) {
    //         if ($employee->profile) {
    //             $employee->profile->update([
    //                 'profile_status' => 'submitted',
    //             ]);
    //         }

    //         $employeeName = $employee->user?->name ?: $employee->employee_code;

    //         app(NotificationS::class)->notifyHrAndSuperAdmin(
    //             'Profile Verification Request',
    //             $employeeName.' submitted profile and documents for verification.',
    //             'profile_document_verification',
    //             'hrms.documents.employee.show',
    //             ['employee' => $employee->id],
    //             [
    //                 'employee_id' => $employee->id,
    //                 'user_id' => $employee->user_id,
    //                 'employee_code' => $employee->employee_code,
    //             ]
    //         );
    //     }

    //     return $this->apiResponse(true, 'Documents submitted for verification.', [
    //         'completion' => $completion,
    //     ]);
    // }

    public function destroy($id)
    {
        $employee = $this->completionService->employeeForUser((int) auth()->id());

        if (! $employee) {
            return $this->apiResponse(false, 'Employee record not found.', null, 404);
        }

        $document = EmployeeDocumentM::where('employee_id', $employee->id)->find($id);

        if (! $document) {
            return $this->apiResponse(false, 'Document not found.', null, 404);
        }

        if ($document->verification_status === 'verified') {
            return $this->apiResponse(false, 'Verified documents cannot be deleted.', null, 422);
        }

        if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();

        return $this->apiResponse(true, 'Document deleted successfully.');
    }

    public function companyPolicies()
    {
        $policies = CompanyDocumentM::latest()
            ->get()
            ->filter(function ($policy) {
                $visibleTo = $policy->visible_to;

                if (empty($visibleTo)) {
                    return true;
                }

                return in_array('employee', (array) $visibleTo, true);
            })
            ->map(function ($policy) {
                $path = (string) $policy->file_path;
                $url = preg_match('/^https?:\/\//i', $path)
                    ? $path
                    : asset('storage/' . ltrim($path, '/'));

                return [
                    'id' => $policy->id,
                    'title' => $policy->title,
                    'category' => $policy->category,
                    'file_path' => $policy->file_path,
                    'file_url' => $url,
                    'download_allowed' => (bool) $policy->download_allowed,
                    'visible_to' => $policy->visible_to,
                    'uploaded_by' => $policy->uploaded_by,
                    'created_at' => optional($policy->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        return $this->apiResponse(true, 'Company policies fetched successfully.', [
            'company_policies' => $policies,
        ]);
    }

    public function uploadMyDocument(Request $request)
    {
        return $this->upload($request);
    }

    public function listPolicyDocuments()
    {
        return $this->companyPolicies();
    }

    public function myEssentialDocuments()
    {
        return $this->myDocuments();
    }

    private function apiResponse(bool $success, string $message, $data = null, int $status = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }

    private function documentNotificationPayload(EmployeeDocumentM $document, array $extra = []): array
    {
        $absoluteUrl = url('/api/v1/file') . '?' . http_build_query([
            'disk' => 'private',
            'path' => $document->file_path,
        ]);

        return array_merge($extra, [
            'attachment_url' => $absoluteUrl,
            'attachment_type' => $this->attachmentType($document->file_mime_type, $document->file_original_name),
            'attachment_name' => $document->file_original_name ?: $document->title,
            'file_mime_type' => $document->file_mime_type,
        ]);
    }

    private function attachmentType(?string $mime, ?string $name): string
    {
        $mime = strtolower((string) $mime);
        $extension = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));

        if (str_starts_with($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'image';
        }

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        return 'document';
    }
}
