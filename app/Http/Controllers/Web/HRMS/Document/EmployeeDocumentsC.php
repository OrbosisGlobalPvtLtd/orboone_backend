<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentsC extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeM::with(['user', 'profile', 'documents.documentType']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereHas('documents')
                    ->whereDoesntHave('documents', function ($q) {
                        $q->where('verification_status', '!=', 'verified');
                    });
            }

            if ($request->status === 'pending') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('documents')
                        ->orWhereHas('documents', function ($docQuery) {
                            $docQuery->where('verification_status', '!=', 'verified');
                        });
                });
            }
        }

        $employees = $query->latest()->paginate(20)->withQueryString();

        $employees->getCollection()->transform(function ($employee) {
            $documents = $employee->documents;

            $employee->doc_total = $documents->count();
            $employee->doc_verified = $documents->where('verification_status', 'verified')->count();
            $employee->doc_pending = $documents->where('verification_status', 'pending')->count();
            $employee->doc_rejected = $documents->where('verification_status', 'rejected')->count();

            $employee->verification_status = ($employee->doc_total > 0 && $employee->doc_verified === $employee->doc_total)
                ? 'verified'
                : 'pending';

            return $employee;
        });

        return view('hrms.documents.employee-documents.index', compact('employees'));
    }

    public function show(EmployeeM $employee)
    {
        $employee->load(['user', 'profile']);

        $rawExperience = strtolower(trim(
            $employee->experience_type
                ?? $employee->profile->experience_type
                ?? 'fresher'
        ));

        $isExperienced = in_array($rawExperience, [
            'experienced',
            'experience',
            'exp',
            'yes',
            '1',
        ]);

        $appliesTo = $isExperienced
            ? ['all', 'both', 'employee', 'employees', 'experienced', 'experience', 'exp']
            : ['all', 'both', 'employee', 'employees', 'fresher', 'freshers'];

        $documentTypes = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->where(function ($q) use ($appliesTo) {
                $q->whereNull('applies_to')
                    ->orWhere('applies_to', '')
                    ->orWhereIn(DB::raw('LOWER(TRIM(applies_to))'), $appliesTo);
            })
            ->orderBy('is_mandatory', 'desc')
            ->orderBy('name')
            ->get();

        $documents = EmployeeDocumentM::with('documentType')
            ->where('employee_id', $employee->id)
            ->get()
            ->keyBy('document_type_id');

        return view('hrms.documents.employee-documents.show', compact(
            'employee',
            'documentTypes',
            'documents'
        ));
    }

    public function store(Request $request, EmployeeM $employee)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'title' => 'nullable|string|max:150',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
            'expiry_date' => 'nullable|date',
        ]);

        $documentType = DocumentTypeM::findOrFail($request->document_type_id);

        $file = $request->file('file');

        $path = $file->store(
            'employee-documents/' . $employee->id,
            'private'
        );

        EmployeeDocumentM::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'title' => $request->title ?? $documentType->name,

                'file_path' => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'file_mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),

                // Admin / HR upload = Auto Verified
                'verification_status' => 'verified',
                'verified_by_user_id' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => null,

                'uploaded_by_user_id' => Auth::id(),
                'uploaded_at' => now(),

                'expiry_date' => $request->expiry_date,
                'is_required' => $documentType->is_mandatory,
            ]
        );

        $this->syncEmployeeVerification($employee->id);

        return back()->with(
            'success',
            'Employee document uploaded and verified successfully.'
        );
    }

    public function storeGlobal(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees_new,id',
            'document_type_id' => 'required|exists:document_types,id',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
            'title' => 'nullable|string|max:150',
            'expiry_date' => 'nullable|date',
        ]);

        $documentType = DocumentTypeM::findOrFail($request->document_type_id);

        foreach ($request->employee_ids as $employeeId) {
            $file = $request->file('file');
            $path = $file->store('employee-documents/' . $employeeId, 'private');

            EmployeeDocumentM::create([
                'employee_id' => $employeeId,
                'document_type_id' => $documentType->id,
                'title' => $request->title ?? $documentType->name,
                'file_path' => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'file_mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'verification_status' => 'pending',
                'uploaded_by_user_id' => Auth::id(),
                'expiry_date' => $request->expiry_date,
                'uploaded_at' => now(),
                'is_required' => $documentType->is_mandatory,
            ]);
        }

        return back()->with('success', 'Global document uploaded successfully.');
    }

    public function approve(EmployeeDocumentM $document)
    {
        $document->update([
            'verification_status' => 'verified',
            'verified_by_user_id' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->syncEmployeeVerification($document->employee_id);
        $this->notifyDocumentStatus($document->fresh(['employee.user', 'documentType']), 'document_approved');

        return back()->with('success', 'Document verified successfully.');
    }

    public function reject(Request $request, EmployeeDocumentM $document)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $document->update([
            'verification_status' => 'rejected',
            'verified_by_user_id' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        $document->employee?->profile()->update([
            'profile_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $this->notifyDocumentStatus($document->fresh(['employee.user', 'documentType']), 'document_rejected', $request->rejection_reason);

        return back()->with('success', 'Document rejected successfully.');
    }

    public function download(EmployeeDocumentM $document)
    {
        abort_if(! $document->file_path || ! Storage::disk('private')->exists($document->file_path), 404);

        return Storage::disk('private')->download(
            $document->file_path,
            $document->file_original_name
        );
    }

    public function verifyByHr($document)
    {
        $doc = EmployeeDocumentM::findOrFail($document);

        $doc->update([
            'verification_status' => 'verified',
            'verified_by_user_id' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => null,
            'updated_at' => now(),
        ]);

        $this->syncEmployeeVerification($doc->employee_id);
        $this->notifyDocumentStatus($doc->fresh(['employee.user', 'documentType']), 'document_verified');

        return back()->with('success', 'Document verified successfully.');
    }

    public function rejectByHr(Request $request, $document)
    {
        $doc = EmployeeDocumentM::findOrFail($document);

        $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $doc->update([
            'verification_status' => 'rejected',
            'verified_by_user_id' => null,
            'verified_at' => null,
            'rejection_reason' => $request->rejection_reason ?: 'Document rejected by HR',
            'updated_at' => now(),
        ]);

        $doc->employee?->profile()->update([
            'profile_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason ?: 'Document rejected by HR',
        ]);

        $this->notifyDocumentStatus($doc->fresh(['employee.user', 'documentType']), 'document_rejected', $request->rejection_reason ?: 'Document rejected by HR');

        return back()->with('success', 'Document rejected successfully.');
    }

    public function uploadFromProfile(Request $request, $employee, $documentType)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $employeeData = DB::table('employees_new')->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $type = DB::table('document_types')->where('id', $documentType)->first();
        abort_if(! $type, 404);

        $oldDocument = DB::table('employee_documents_new')
            ->where('employee_id', $employee)
            ->where('document_type_id', $documentType)
            ->orderByDesc('id')
            ->first();

        if ($oldDocument && ! empty($oldDocument->file_path) && Storage::disk('private')->exists($oldDocument->file_path)) {
            Storage::disk('private')->delete($oldDocument->file_path);
        }

        $file = $request->file('file');
        $path = $file->store('employee-documents/' . $employee, 'private');

        $data = [
            'employee_id' => $employee,
            'document_type_id' => $documentType,
            'title' => $type->name,
            'file_path' => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'verification_status' => 'verified',
            'verified_by_user_id' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => null,
            'expiry_date' => $request->expiry_date,
            'is_required' => (int) ($type->is_mandatory ?? 0),
            'uploaded_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('employee_documents_new', 'uploaded_by_user_id')) {
            $data['uploaded_by_user_id'] = auth()->id();
        }

        if ($oldDocument) {
            DB::table('employee_documents_new')->where('id', $oldDocument->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('employee_documents_new')->insert($data);
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    private function syncEmployeeVerification($employeeId)
    {
        $employee = DB::table('employees_new')->where('id', $employeeId)->first();

        if (! $employee) {
            return;
        }

        $experienceType = strtolower(trim($employee->experience_type ?? 'fresher'));

        if (in_array($experienceType, ['experience', 'experienced', 'exp', 'senior'])) {
            $experienceType = 'experienced';
        } else {
            $experienceType = 'fresher';
        }

        $appliesTo = ['all', 'both', 'employee', $experienceType];

        if ($experienceType === 'experienced') {
            $appliesTo = array_merge($appliesTo, ['experience', 'experienced']);
        }

        $requiredTypeIds = DB::table('document_types')
            ->where('scope', 'employee')
            ->where('is_active', 1)
            ->where('is_mandatory', 1)
            ->where(function ($q) use ($appliesTo) {
                $q->whereNull('applies_to')
                    ->orWhereIn('applies_to', $appliesTo);
            })
            ->pluck('id');

        if ($requiredTypeIds->count() <= 0) {
            return;
        }

        $verifiedRequiredDocs = DB::table('employee_documents_new')
            ->where('employee_id', $employeeId)
            ->whereIn('document_type_id', $requiredTypeIds)
            ->where('verification_status', 'verified')
            ->distinct('document_type_id')
            ->count('document_type_id');

        if ($verifiedRequiredDocs === $requiredTypeIds->count()) {
            DB::table('employee_profiles')
                ->where('employee_id', $employeeId)
                ->update([
                    'profile_status' => 'approved',
                    'is_profile_completed' => 1,
                    'profile_completed_at' => now(),
                    'approved_by_user_id' => auth()->id(),
                    'approved_at' => now(),
                    'rejection_reason' => null,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('employee_profiles')
            ->where('employee_id', $employeeId)
            ->update([
                'profile_status' => 'pending',
                'is_profile_completed' => 0,
                'updated_at' => now(),
            ]);
    }

    private function notifyDocumentStatus(EmployeeDocumentM $document, string $type, ?string $reason = null): void
    {
        $userId = $document->employee?->user_id;
        if (! $userId) {
            return;
        }

        $title = match ($type) {
            'document_rejected' => 'Document Rejected',
            'document_reuploaded' => 'Document Reupload Requested',
            'document_verified' => 'Document Verified',
            default => 'Document Approved',
        };

        $message = match ($type) {
            'document_rejected' => 'Your document ' . ($document->title ?: 'document') . ' was rejected. Please re-upload it.',
            'document_reuploaded' => 'Please re-upload ' . ($document->title ?: 'your document') . '.',
            default => 'Your document ' . ($document->title ?: 'document') . ' has been approved.',
        };

        app(\App\Services\HRMS\Notification\NotificationS::class)->notifyEmployee(
            $title,
            $message,
            $type,
            'documents',
            ['document_id' => $document->id],
            [
                'document_id' => $document->id,
                'employee_id' => $document->employee_id,
                'document_title' => $document->title,
                'rejection_reason' => $reason,
                'attachment_url' => $this->privateFileUrl($document->file_path),
                'attachment_type' => $this->attachmentType($document->file_mime_type, $document->file_original_name),
                'attachment_name' => $document->file_original_name ?: $document->title,
            ],
            $userId
        );
    }

    private function privateFileUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        return url('/api/v1/file') . '?' . http_build_query([
            'disk' => 'private',
            'path' => $path,
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
