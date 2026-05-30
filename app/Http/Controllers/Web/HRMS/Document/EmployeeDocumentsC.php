<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentsC extends Controller
{
    public function __construct(
        private HrmsStoragePathS $paths,
        private HrmsFileResolverS $resolver
    ) {
    }

    public function index(Request $request)
    {
        $documentTypes = DocumentTypeM::where('scope', 'employee')->where('is_active', 1)->get();
        $departments = \App\Models\HRMS\Department\DepartmentM::where('is_active', 1)->orderBy('name')->get();
        $query = EmployeeM::with(['user', 'profile', 'department', 'documents.documentType']);

        $search = $request->get('search', $request->get('employee'));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->department_id);
        }

        $stage = $request->get('employee_stage', $request->get('stage'));
        if (!empty($stage)) {
            $query->where('employee_stage', $stage);
        }

        if ($request->filled('profile_status')) {
            $query->whereHas('profile', fn($q) => $q->where('profile_status', $request->profile_status));
        }

        $allEmployees = $query->latest()->get();

        $calculatedEmployees = $allEmployees->map(function ($employee) use ($documentTypes) {
            $documents = $employee->documents;
            $experienceType = strtolower(trim((string) ($employee->experience_type ?? 'fresher')));
            $requiredDocs = $documentTypes->where('is_mandatory', 1)->filter(function ($type) use ($experienceType) {
                $applies = strtolower(trim((string) ($type->applies_to ?: 'all')));
                return in_array($applies, ['all', 'both', 'employee', 'employees', $experienceType], true);
            });
            $requiredIds = $requiredDocs->pluck('id');
            $relevantDocs = $documents->whereIn('document_type_id', $requiredIds);
            
            // Unique relevant uploaded documents to find missing count
            $uploadedRelevant = $relevantDocs->where('verification_status', '!=', 'rejected')->unique('document_type_id')->count();

            // Setup required counters
            $employee->total_required_docs = $requiredDocs->count();
            $employee->uploaded_docs = $documents->count();
            $employee->verified_docs = $relevantDocs->where('verification_status', 'verified')->count();
            $employee->pending_docs = $relevantDocs->where('verification_status', 'pending')->count();
            $employee->rejected_docs = $relevantDocs->where('verification_status', 'rejected')->count();
            $employee->missing_docs = max(0, $employee->total_required_docs - $uploadedRelevant);
            $employee->expired_docs = $relevantDocs->whereNotNull('expiry_date')->filter(fn($doc) => now()->gt(\Carbon\Carbon::parse($doc->expiry_date)))->count();

            // Supporting older template keys
            $employee->doc_total = $employee->uploaded_docs;
            $employee->doc_required = $employee->total_required_docs;
            $employee->doc_verified = $employee->verified_docs;
            $employee->doc_pending = $employee->pending_docs;
            $employee->doc_rejected = $employee->rejected_docs;
            $employee->doc_missing = $employee->missing_docs;
            $employee->doc_expired = $employee->expired_docs;

            $employee->profile_status = $employee->profile->profile_status ?? 'pending';

            // Additional fields requested by the user
            $employee->name = $employee->user->name ?? '';
            $employee->email = $employee->user->email ?? '';
            $employee->code = $employee->employee_code ?? '';
            $employee->department_name = $employee->department?->name ?? '';
            $employee->stage = $employee->employee_stage ?? '';

            // Compliance status logic
            if ($employee->expired_docs > 0) {
                $employee->compliance_status = 'expired';
            } elseif ($employee->rejected_docs > 0) {
                $employee->compliance_status = 'rejected';
            } elseif ($employee->missing_docs > 0) {
                $employee->compliance_status = 'missing';
            } elseif ($employee->pending_docs > 0) {
                $employee->compliance_status = 'pending';
            } else {
                $employee->compliance_status = 'compliant';
            }

            // verification_status mapping
            $employee->verification_status = ($employee->compliance_status === 'compliant') ? 'compliant' : 'non_compliant';

            // compliance_percentage
            $employee->compliance_percentage = $employee->total_required_docs > 0
                ? round(($employee->verified_docs / $employee->total_required_docs) * 100)
                : 100;
            $employee->compliance_percentage = min(100, max(0, $employee->compliance_percentage));

            $employee->last_verified_at = $documents->where('verification_status', 'verified')->max('verified_at');
            $employee->actions = ''; // custom placeholder

            return $employee;
        });

        // Compute dashboard analytics (organization-wide stats)
        $totalEmployeesCount = $calculatedEmployees->count();
        $fullyCompliantCount = $calculatedEmployees->where('compliance_status', 'compliant')->count();
        $nonCompliantCount = $totalEmployeesCount - $fullyCompliantCount;
        $pendingVerificationCount = $calculatedEmployees->where('compliance_status', 'pending')->count();
        $missingDocsCount = $calculatedEmployees->where('compliance_status', 'missing')->count();
        $rejectedDocsCount = $calculatedEmployees->where('compliance_status', 'rejected')->count();
        $expiredDocsCount = $calculatedEmployees->where('compliance_status', 'expired')->count();
        $complianceRate = $totalEmployeesCount > 0 ? round(($fullyCompliantCount / $totalEmployeesCount) * 100) : 0;

        // Stage-wise Compliance computation
        $stages = ['internship', 'probation', 'permanent', 'exit'];
        $stageCompliance = [];
        foreach ($stages as $stg) {
            $stageEmployees = $calculatedEmployees->filter(function ($emp) use ($stg) {
                return strtolower(trim((string) ($emp->employee_stage))) === $stg;
            });
            $totalStg = $stageEmployees->count();
            $compliantStg = $stageEmployees->where('compliance_status', 'compliant')->count();
            $stageCompliance[$stg] = [
                'total' => $totalStg,
                'compliant' => $compliantStg,
                'rate' => $totalStg > 0 ? round(($compliantStg / $totalStg) * 100) : 100,
            ];
        }

        // Summary array for view
        $summary = [
            'total_employees' => $totalEmployeesCount,
            'fully_compliant' => $fullyCompliantCount,
            'non_compliant' => $nonCompliantCount,
            'pending_verification' => $pendingVerificationCount,
            'missing_documents' => $missingDocsCount,
            'rejected_documents' => $rejectedDocsCount,
            'expired_documents' => $expiredDocsCount,
            'compliance_rate' => $complianceRate,
        ];

        // Risk panels for view
        $riskPanels = [
            'high_risk' => [
                'count' => $calculatedEmployees->filter(function ($emp) {
                    return ($emp->missing_docs > 2 || $emp->rejected_docs > 0 || $emp->expired_docs > 0);
                })->count(),
                'employees' => $calculatedEmployees->filter(function ($emp) {
                    return ($emp->missing_docs > 2 || $emp->rejected_docs > 0 || $emp->expired_docs > 0);
                })->take(3)->values(),
            ],
            'missing_mandatory' => [
                'count' => $calculatedEmployees->filter(function ($emp) {
                    return $emp->missing_docs > 0;
                })->count(),
                'employees' => $calculatedEmployees->filter(function ($emp) {
                    return $emp->missing_docs > 0;
                })->take(3)->values(),
            ],
            'rejected' => [
                'count' => $calculatedEmployees->filter(function ($emp) {
                    return $emp->rejected_docs > 0;
                })->count(),
                'employees' => $calculatedEmployees->filter(function ($emp) {
                    return $emp->rejected_docs > 0;
                })->take(3)->values(),
            ],
            'expired' => [
                'count' => $calculatedEmployees->filter(function ($emp) {
                    return $emp->expired_docs > 0;
                })->count(),
                'employees' => $calculatedEmployees->filter(function ($emp) {
                    return $emp->expired_docs > 0;
                })->take(3)->values(),
            ],
        ];

        // Filter collection by compliance status
        $complianceStatusFilter = $request->get('compliance_status');
        $filtered = $calculatedEmployees;
        if (!empty($complianceStatusFilter)) {
            $filtered = $filtered->filter(function ($emp) use ($complianceStatusFilter) {
                if ($complianceStatusFilter === 'non_compliant') {
                    return $emp->compliance_status !== 'compliant';
                }
                return $emp->compliance_status === $complianceStatusFilter;
            });
        }

        // Filter collection by Risk Type
        $riskTypeFilter = $request->get('risk_type');
        if (!empty($riskTypeFilter)) {
            $filtered = $filtered->filter(function ($emp) use ($riskTypeFilter) {
                return match ($riskTypeFilter) {
                    'high_risk' => ($emp->missing_docs > 2 || $emp->rejected_docs > 0 || $emp->expired_docs > 0),
                    'missing_mandatory' => $emp->missing_docs > 0,
                    'rejected' => $emp->rejected_docs > 0,
                    'expired' => $emp->expired_docs > 0,
                    default => true,
                };
            });
        }

        // Paginate filtered results
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentPageItems = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $employees = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $filtered->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        $employees->withQueryString();

        return view('hrms.documents.employee-documents.index', compact(
            'employees',
            'departments',
            'summary',
            'riskPanels',
            'stageCompliance'
        ));
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

        $query = EmployeeDocumentM::with('documentType')
            ->where('employee_id', $employee->id);

        if (\Illuminate\Support\Facades\Schema::hasColumn('employee_documents_new', 'is_active')) {
            $query->where('is_active', 1);
        }

        $documents = $query->get()->keyBy('document_type_id');

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

        $storageService = app(\App\Services\HRMS\Document\HrmsFileStorageS::class);
        $meta = $storageService->archiveOrReplaceEmployeeDocument($employee, $documentType, $file);

        $search = [
            'employee_id' => $employee->id,
            'document_type_id' => $documentType->id,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('employee_documents_new', 'is_active')) {
            $search['is_active'] = 1;
        }

        EmployeeDocumentM::updateOrCreate(
            $search,
            [
                'title' => $request->title ?? $documentType->name,

                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],

                // Admin / HR upload = Auto Verified
                'verification_status' => 'verified',
                'verified_by_user_id' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => null,

                'uploaded_by_user_id' => Auth::id(),
                'uploaded_at' => now(),

                'expiry_date' => $request->expiry_date,
                'is_required' => $documentType->is_mandatory,
                'is_active' => true,
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
        $storageService = app(\App\Services\HRMS\Document\HrmsFileStorageS::class);

        foreach ($request->employee_ids as $employeeId) {
            $employee = EmployeeM::findOrFail($employeeId);
            $file = $request->file('file');
            $meta = $storageService->archiveOrReplaceEmployeeDocument($employee, $documentType, $file);

            EmployeeDocumentM::create([
                'employee_id' => $employeeId,
                'document_type_id' => $documentType->id,
                'title' => $request->title ?? $documentType->name,
                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
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

        $this->notifyDocumentStatus($doc->fresh(['employee.user', 'documentType']), 'document_rejected', $request->rejection_reason ?: 'Document rejected by HR');

        return back()->with('success', 'Document rejected successfully.');
    }

    public function uploadFromProfile(Request $request, $employee, $documentType)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $employeeModel = EmployeeM::findOrFail($employee);
        $typeModel = DocumentTypeM::findOrFail($documentType);

        $file = $request->file('file');
        $storageService = app(\App\Services\HRMS\Document\HrmsFileStorageS::class);
        $meta = $storageService->archiveOrReplaceEmployeeDocument($employeeModel, $typeModel, $file);

        $data = [
            'employee_id' => $employee,
            'document_type_id' => $documentType,
            'title' => $typeModel->name,
            'file_path' => $meta['file_path'],
            'file_original_name' => $meta['original_name'],
            'file_mime_type' => $meta['mime_type'],
            'file_size' => $meta['file_size'],
            'verification_status' => 'pending',
            'verified_by_user_id' => null,
            'verified_at' => null,
            'rejection_reason' => null,
            'expiry_date' => $request->expiry_date,
            'is_required' => (int) ($typeModel->is_mandatory ?? 0),
            'uploaded_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('employee_documents_new', 'uploaded_by_user_id')) {
            $data['uploaded_by_user_id'] = auth()->id();
        }

        if (Schema::hasColumn('employee_documents_new', 'is_active')) {
            $data['is_active'] = 1;
        }

        $oldQuery = DB::table('employee_documents_new')
            ->where('employee_id', $employee)
            ->where('document_type_id', $documentType);

        if (Schema::hasColumn('employee_documents_new', 'is_active')) {
            $oldQuery->where('is_active', 1);
        }

        $oldDocument = $oldQuery->orderByDesc('id')->first();

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
        // Verification flow must not auto-approve/reject profile status.
        return;
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

        return $this->resolver->secureFileUrl($path);
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

    private function normalizeTypeKey(?DocumentTypeM $type): string
    {
        if (! $type) {
            return 'misc';
        }

        return $this->paths->normalizeDocType((string) ($type->code ?: $type->name));
    }

    private function normalizeTypeKeyFromRow(?object $type): string
    {
        if (! $type) {
            return 'misc';
        }

        return $this->paths->normalizeDocType((string) (($type->code ?? null) ?: ($type->name ?? 'misc')));
    }
}
