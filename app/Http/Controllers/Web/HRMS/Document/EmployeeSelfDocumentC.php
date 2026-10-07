<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Document\HrmsFileStorageS;
use App\Services\HRMS\Employee\EmployeeProfileCompletionS;
use App\Services\HRMS\Notification\NotificationS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class EmployeeSelfDocumentC extends Controller
{
    public function __construct(
        private HrmsStoragePathS $paths,
        private HrmsFileStorageS $storageService,
        private EmployeeProfileCompletionS $completionService,
        private NotificationS $notificationService
    ) {
    }

    private function getCurrentEmployee(): ?EmployeeM
    {
        return EmployeeM::with(['user', 'profile'])->where('user_id', Auth::id())->first();
    }

    private function checkAccess(?EmployeeM $employee): void
    {
        if (!$employee) {
            abort(404, 'Employee not found');
        }

        $status = $this->completionService->buildCompletionStatus($employee, $employee->profile);

        if (!$status['must_complete_profile']) {
            abort(403, 'Profile already submitted/approved. Documents cannot be modified.');
        }
    }

    private function getAllowedExtensions(DocumentTypeM $docType): array
    {
        $extensions = $docType->allowed_extensions;
        if (is_string($extensions)) {
            $decoded = json_decode($extensions, true);
            $extensions = is_array($decoded) ? $decoded : [];
        }

        if (empty($extensions)) {
            $isPhoto = str_contains(strtolower($docType->code ?? ''), 'photo') 
                || str_contains(strtolower($docType->name ?? ''), 'photo');
            $extensions = $isPhoto ? ['jpg', 'jpeg', 'png'] : ['pdf', 'jpg', 'jpeg', 'png'];
        }

        return collect($extensions)
            ->map(fn($ext) => strtolower(trim((string) $ext, " \t\n\r\0\x0B.")))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function upload(Request $request): JsonResponse
    {
        $employee = $this->getCurrentEmployee();
        $this->checkAccess($employee);

        $docType = DocumentTypeM::findOrFail($request->document_type_id);
        $allowedExtensions = $this->getAllowedExtensions($docType);
        $maxFileSizeMb = (int) ($docType->max_file_size_mb ?: 5);
        $maxFileSizeKb = max($maxFileSizeMb, 1) * 1024;

        $validator = Validator::make($request->all(), [
            'document_type_id' => 'required|exists:document_types,id',
            'file' => [
                'required',
                'file',
                'mimes:' . implode(',', $allowedExtensions),
                'max:' . $maxFileSizeKb,
            ]
        ], [
            'file.mimes' => 'Invalid file format. Allowed format(s) for ' . $docType->name . ': ' . strtoupper(implode(', ', $allowedExtensions)) . '.',
            'file.max' => 'File size must not exceed ' . $maxFileSizeMb . 'MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('file') ?: 'Validation failed.'
            ], 422);
        }

        $file = $request->file('file');
        $meta = $this->storageService->archiveOrReplaceEmployeeDocument($employee, $docType, $file);

        $search = [
            'employee_id' => $employee->id,
            'document_type_id' => $docType->id,
        ];
        if (Schema::hasColumn('employee_documents_new', 'is_active')) {
            $search['is_active'] = 1;
        }

        $oldDocument = EmployeeDocumentM::where($search)->orderByDesc('id')->first();
        $isReupload = $oldDocument && $oldDocument->verification_status === 'rejected';

        EmployeeDocumentM::updateOrCreate(
            $search,
            [
                'title' => $docType->name,
                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'verification_status' => 'pending',
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'uploaded_by_user_id' => Auth::id(),
                'uploaded_at' => now(),
                'is_required' => $docType->is_mandatory,
                'is_active' => true
            ]
        );

        if ($isReupload) {
            $employeeName = $employee->user->name ?? $employee->employee_code;
            $this->notificationService->notifyHrAndSuperAdmin(
                'Document Re-uploaded',
                $employeeName . ' has re-uploaded ' . ($oldDocument->title ?: $docType->name) . ' for verification.',
                'document_reuploaded',
                'hrms.employees.profile.view',
                ['employee' => $employee->id],
                [
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'employee_code' => $employee->employee_code,
                    'notification_type' => 'document_reuploaded',
                    'action_url' => route('hrms.employees.profile.view', ['employee' => $employee->id]),
                    'route_name' => 'hrms.employees.profile.view',
                    'route_params' => ['employee' => $employee->id],
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Document uploaded successfully.']);
    }

    public function replace(Request $request, $id): JsonResponse
    {
        $employee = $this->getCurrentEmployee();
        $this->checkAccess($employee);

        $document = EmployeeDocumentM::where('employee_id', $employee->id)->findOrFail($id);
        $docType = DocumentTypeM::findOrFail($document->document_type_id);

        $allowedExtensions = $this->getAllowedExtensions($docType);
        $maxFileSizeMb = (int) ($docType->max_file_size_mb ?: 5);
        $maxFileSizeKb = max($maxFileSizeMb, 1) * 1024;

        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'mimes:' . implode(',', $allowedExtensions),
                'max:' . $maxFileSizeKb,
            ]
        ], [
            'file.mimes' => 'Invalid file format. Allowed format(s) for ' . $docType->name . ': ' . strtoupper(implode(', ', $allowedExtensions)) . '.',
            'file.max' => 'File size must not exceed ' . $maxFileSizeMb . 'MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('file') ?: 'Validation failed.'
            ], 422);
        }

        $file = $request->file('file');
        $meta = $this->storageService->archiveOrReplaceEmployeeDocument($employee, $docType, $file);

        $isReupload = $document->verification_status === 'rejected';

        if ($document->verification_status === 'verified') {
            EmployeeDocumentM::create([
                'employee_id' => $employee->id,
                'document_type_id' => $document->document_type_id,
                'title' => $document->title,
                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'verification_status' => 'pending',
                'uploaded_by_user_id' => Auth::id(),
                'expiry_date' => $document->expiry_date,
                'is_required' => $document->is_required,
                'uploaded_at' => now(),
                'is_active' => true,
            ]);
        } else {
            $document->update([
                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'verification_status' => 'pending',
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'uploaded_by_user_id' => Auth::id(),
                'uploaded_at' => now()
            ]);
        }

        if ($isReupload) {
            $employeeName = $employee->user->name ?? $employee->employee_code;
            $this->notificationService->notifyHrAndSuperAdmin(
                'Document Re-uploaded',
                $employeeName . ' has re-uploaded ' . ($document->title ?: $docType->name) . ' for verification.',
                'document_reuploaded',
                'hrms.employees.profile.view',
                ['employee' => $employee->id],
                [
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'employee_code' => $employee->employee_code,
                    'notification_type' => 'document_reuploaded',
                    'action_url' => route('hrms.employees.profile.view', ['employee' => $employee->id]),
                    'route_name' => 'hrms.employees.profile.view',
                    'route_params' => ['employee' => $employee->id],
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Document replaced successfully.']);
    }

    public function destroy($id): JsonResponse
    {
        $employee = $this->getCurrentEmployee();
        $this->checkAccess($employee);

        $document = EmployeeDocumentM::where('employee_id', $employee->id)->findOrFail($id);

        if ($document->verification_status === 'verified') {
            return response()->json(['success' => false, 'message' => 'Cannot delete a verified document.'], 403);
        }

        $document->delete();

        return response()->json(['success' => true, 'message' => 'Document deleted successfully.']);
    }
}
