<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Department\DepartmentM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRDocumentC extends Controller
{
    public function index(Request $request)
    {
        $departments = DepartmentM::where('is_active', 1)->orderBy('name')->get();

        $documentTypes = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $query = EmployeeM::with(['user', 'profile', 'designation', 'documents.documentType'])
            ->whereHas('documents');

        if ($request->filled('employee')) {
            $search = $request->employee;

            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('document_type_id')) {
            $query->whereHas('documents', function ($q) use ($request) {
                $q->where('document_type_id', $request->document_type_id);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereDoesntHave('documents', function ($q) {
                    $q->where('verification_status', '!=', 'verified');
                });
            } else {
                $query->whereHas('documents', function ($q) use ($request) {
                    $q->where('verification_status', $request->status);
                });
            }
        } else {
            $query->whereHas('documents', function ($q) {
                $q->where('verification_status', '!=', 'verified');
            });
        }

        $employees = $query->latest()->paginate(20)->withQueryString();

        $employees->getCollection()->transform(function ($employee) use ($documentTypes) {
            $experienceType = $employee->experience_type ?? 'fresher';
            
            $requiredDocs = $documentTypes->where('is_mandatory', 1)->filter(function ($type) use ($experienceType) {
                return $type->applies_to === 'all' || $type->applies_to === $experienceType;
            });
            
            $requiredIds = $requiredDocs->pluck('id');
            $documents = $employee->documents;

            $employee->doc_total = $documents->count();
            $employee->doc_required = $requiredDocs->count();
            $employee->doc_verified = $documents->where('verification_status', 'verified')->count();
            $employee->doc_pending = $documents->where('verification_status', 'pending')->count();
            $employee->doc_rejected = $documents->where('verification_status', 'rejected')->count();

            $uploadedRelevant = $documents->whereIn('document_type_id', $requiredIds)->where('verification_status', '!=', 'rejected')->unique('document_type_id')->count();
            $employee->doc_missing = max(0, $requiredDocs->count() - $uploadedRelevant);
            
            $employee->doc_expiring = $documents->whereNotNull('expiry_date')->filter(function($doc) {
                return \Carbon\Carbon::parse($doc->expiry_date)->isFuture() && \Carbon\Carbon::parse($doc->expiry_date)->diffInDays(now()) <= 30;
            })->count();

            $employee->doc_status = ($employee->doc_missing === 0 && $employee->doc_pending === 0 && $employee->doc_rejected === 0 && $employee->doc_verified >= $employee->doc_required && $employee->doc_required > 0)
                ? 'verified'
                : 'pending';

            return $employee;
        });

        return view('hrms.documents.hr.index', compact('employees', 'documentTypes', 'departments'));
    }

    public function verifyEmployee($employee)
    {
        $employee = EmployeeM::with(['user', 'documents'])->findOrFail($employee);

        EmployeeDocumentM::where('employee_id', $employee->id)
            ->where('verification_status', '!=', 'verified')
            ->update([
                'verification_status' => 'verified',
                'verified_by_user_id' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

        $this->syncEmployeeVerification($employee->id);

        return back()->with('success', 'All documents for ' . ($employee->user->name ?? 'Employee') . ' have been verified successfully.');
    }

    public function show($user)
    {
        $employee = EmployeeM::with(['user', 'profile', 'documents.documentType', 'documents.verifiedBy'])
            ->where('user_id', $user)
            ->firstOrFail();

        $documentTypes = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $experienceType = $employee->experience_type ?? 'fresher';
        
        $requiredDocs = $documentTypes->where('is_mandatory', 1)->filter(function ($type) use ($experienceType) {
            return $type->applies_to === 'all' || $type->applies_to === $experienceType;
        });
        
        $requiredIds = $requiredDocs->pluck('id');
        $documents = $employee->documents;

        $doc_total = $documents->count();
        $doc_required = $requiredDocs->count();
        $doc_verified = $documents->where('verification_status', 'verified')->count();
        $doc_pending = $documents->where('verification_status', 'pending')->count();
        $doc_rejected = $documents->where('verification_status', 'rejected')->count();

        $uploadedRelevant = $documents->whereIn('document_type_id', $requiredIds)->where('verification_status', '!=', 'rejected')->unique('document_type_id')->count();
        $doc_missing = max(0, $requiredDocs->count() - $uploadedRelevant);

        return view('hrms.documents.hr.show', compact(
            'employee',
            'documents',
            'doc_total',
            'doc_required',
            'doc_verified',
            'doc_pending',
            'doc_rejected',
            'doc_missing'
        ));
    }

    public function approve($id)
    {
        $document = EmployeeDocumentM::findOrFail($id);

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

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $document = EmployeeDocumentM::findOrFail($id);

        $document->update([
            'verification_status' => 'rejected',
            'verified_by_user_id' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Decoupled document verification from employee profile lifecycle
        // We no longer reject the employee profile when a document is rejected.

        $this->notifyDocumentStatus($document->fresh(['employee.user', 'documentType']), 'document_rejected', $request->rejection_reason);

        return back()->with('success', 'Document rejected successfully.');
    }

    public function bulkVerify(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:employee_documents_new,id',
        ]);

        $documents = EmployeeDocumentM::whereIn('id', $request->document_ids)->get();

        foreach ($documents as $document) {
            $document->update([
                'verification_status' => 'verified',
                'verified_by_user_id' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->syncEmployeeVerification($document->employee_id);
            $this->notifyDocumentStatus($document->fresh(['employee.user', 'documentType']), 'document_approved');
        }

        return back()->with('success', 'Selected documents verified successfully.');
    }

    public function expiring(Request $request)
    {
        $days = (int) $request->get('days', 30);

        $documents = EmployeeDocumentM::with(['employee.user', 'documentType'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', Carbon::now()->addDays($days))
            ->orderBy('expiry_date')
            ->paginate(20);

        return view('hrms.documents.expiring.index', compact('documents', 'days'));
    }

    private function syncEmployeeVerification($employeeId)
    {
        // Decoupled document verification from employee profile lifecycle
        return;
    }

    private function notifyDocumentStatus(EmployeeDocumentM $document, string $type, ?string $reason = null): void
    {
        $userId = $document->employee?->user_id;
        if (! $userId) {
            return;
        }

        app(\App\Services\HRMS\Notification\NotificationS::class)->notifyEmployee(
            $type === 'document_rejected' ? 'Document Rejected' : 'Document Approved',
            $type === 'document_rejected'
                ? 'Your document ' . ($document->title ?: 'document') . ' was rejected. Please re-upload it.'
                : 'Your document ' . ($document->title ?: 'document') . ' has been approved.',
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
