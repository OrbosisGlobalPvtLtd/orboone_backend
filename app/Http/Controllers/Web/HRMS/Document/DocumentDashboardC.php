<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;

class DocumentDashboardC extends Controller
{
    public function index()
    {
        $activeDocumentTypes = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->get();

        $mandatoryTypeIds = $activeDocumentTypes
            ->where('is_mandatory', 1)
            ->pluck('id');

        $totalEmployees = EmployeeM::count();

        $employeesWithDocuments = EmployeeM::whereHas('documents')->count();

        $verifiedEmployees = EmployeeM::whereHas('documents')
            ->whereDoesntHave('documents', function ($q) {
                $q->where('verification_status', '!=', 'verified');
            })
            ->count();

        $pendingEmployees = EmployeeM::whereHas('documents', function ($q) {
            $q->where('verification_status', 'pending');
        })
            ->orWhereDoesntHave('documents')
            ->count();

        $issueEmployees = EmployeeM::whereHas('documents', function ($q) {
            $q->where('verification_status', 'rejected');
        })->count();

        $missingMandatoryEmployees = EmployeeM::where(function ($q) use ($mandatoryTypeIds) {
            foreach ($mandatoryTypeIds as $typeId) {
                $q->orWhereDoesntHave('documents', function ($docQ) use ($typeId) {
                    $docQ->where('document_type_id', $typeId);
                });
            }
        })->count();

        $stats = [
            'total_employees' => $totalEmployees,
            'employees_with_documents' => $employeesWithDocuments,
            'verified_employees' => $verifiedEmployees,
            'pending_employees' => $pendingEmployees,
            'issue_employees' => $issueEmployees,
            'missing_mandatory_employees' => $missingMandatoryEmployees,

            'total_documents' => EmployeeDocumentM::count(),
            'expiring_documents' => EmployeeDocumentM::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
                ->count(),

            'document_types' => $activeDocumentTypes->count(),

            'pending_profiles' => EmployeeM::whereHas('profile', function ($q) {
                $q->where('profile_status', 'submitted');
            })->count(),
        ];

        $pendingEmployeesList = EmployeeM::with(['user', 'documents.documentType'])
            ->whereHas('documents', function ($q) {
                $q->where('verification_status', 'pending');
            })
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($employee) use ($mandatoryTypeIds) {
                $documents = $employee->documents;

                $employee->doc_total = $documents->count();
                $employee->doc_verified = $documents->where('verification_status', 'verified')->count();
                $employee->doc_pending = $documents->where('verification_status', 'pending')->count();

                $uploadedTypeIds = $documents->pluck('document_type_id')->filter()->unique();
                $employee->missing_mandatory = $mandatoryTypeIds->diff($uploadedTypeIds)->count();

                return $employee;
            });

        $missingMandatoryEmployeesList = EmployeeM::with(['user', 'documents.documentType'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($employee) use ($mandatoryTypeIds, $activeDocumentTypes) {
                $uploadedTypeIds = $employee->documents
                    ->pluck('document_type_id')
                    ->filter()
                    ->unique();

                $missingIds = $mandatoryTypeIds->diff($uploadedTypeIds);

                $employee->missing_documents = $activeDocumentTypes
                    ->whereIn('id', $missingIds)
                    ->pluck('name')
                    ->values();

                return $employee;
            })
            ->filter(function ($employee) {
                return $employee->missing_documents->count() > 0;
            })
            ->values();

        $expiringDocuments = EmployeeDocumentM::with(['employee.user', 'documentType'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        $recentVerifiedEmployees = EmployeeM::with(['user', 'documents.verifiedBy'])
            ->whereHas('documents', function ($q) {
                $q->where('verification_status', 'verified')
                    ->whereNotNull('verified_at');
            })
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($employee) {
                $verifiedDocs = $employee->documents
                    ->where('verification_status', 'verified')
                    ->sortByDesc('verified_at');

                $employee->verified_docs_count = $verifiedDocs->count();
                $employee->last_verified_at = optional($verifiedDocs->first())->verified_at;
                $employee->last_verified_by = optional(optional($verifiedDocs->first())->verifiedBy)->name;

                return $employee;
            });

        return view('hrms.documents.dashboard', compact(
            'stats',
            'pendingEmployeesList',
            'missingMandatoryEmployeesList',
            'expiringDocuments',
            'recentVerifiedEmployees'
        ));
    }
}
