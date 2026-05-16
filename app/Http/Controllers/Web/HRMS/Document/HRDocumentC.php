<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRDocumentC extends Controller
{
    public function index(Request $request)
    {
        $documentTypes = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $query = EmployeeM::with(['user', 'documents.documentType'])
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

        $employees->getCollection()->transform(function ($employee) {
            $documents = $employee->documents;

            $employee->doc_total = $documents->count();
            $employee->doc_verified = $documents->where('verification_status', 'verified')->count();
            $employee->doc_pending = $documents->where('verification_status', 'pending')->count();
            $employee->doc_rejected = $documents->where('verification_status', 'rejected')->count();

            $employee->doc_status = ($employee->doc_total > 0 && $employee->doc_verified === $employee->doc_total)
                ? 'verified'
                : 'pending';

            return $employee;
        });

        return view('hrms.documents.hr.index', compact('employees', 'documentTypes'));
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

        return back()->with('success', ($employee->user->name ?? 'Employee') . ' ke sabhi documents verified ho gaye.');
    }

    public function show($user)
    {
        $employee = EmployeeM::with(['user', 'documents.documentType'])
            ->where('user_id', $user)
            ->firstOrFail();

        $documentTypes = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $documents = $employee->documents->keyBy('document_type_id');

        return view('hrms.documents.hr.show', compact(
            'employee',
            'documentTypes',
            'documents'
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

        $document->employee?->profile()->update([
            'profile_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

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
        $employee = EmployeeM::with('profile')->find($employeeId);

        if (! $employee) {
            return;
        }

        $experienceType = $employee->experience_type ?? 'fresher';

        $requiredIds = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->where('is_mandatory', 1)
            ->where(function ($q) use ($experienceType) {
                $q->where('applies_to', 'all')
                    ->orWhere('applies_to', $experienceType);
            })
            ->pluck('id');

        if ($requiredIds->count() <= 0) {
            return;
        }

        $verifiedCount = EmployeeDocumentM::where('employee_id', $employee->id)
            ->whereIn('document_type_id', $requiredIds)
            ->where('verification_status', 'verified')
            ->distinct('document_type_id')
            ->count('document_type_id');

        if ($verifiedCount === $requiredIds->count()) {
            $employee->profile()->update([
                'is_profile_completed' => 1,
                'profile_status' => 'approved',
                'approved_at' => now(),
                'approved_by_user_id' => Auth::id(),
                'rejection_reason' => null,
            ]);
        }
    }
}
