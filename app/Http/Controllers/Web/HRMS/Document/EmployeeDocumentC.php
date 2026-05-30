<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentC extends Controller
{
    public function __construct(private HrmsStoragePathS $paths)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        abort_if(! $employee, 404, 'Employee profile not found.');

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

        $query = EmployeeDocumentM::where('employee_id', $employee->id);
        if (\Illuminate\Support\Facades\Schema::hasColumn('employee_documents_new', 'is_active')) {
            $query->where('is_active', 1);
        }
        $documents = $query->get()->keyBy('document_type_id');

        return view('hrms.documents.employee.index', compact(
            'employee',
            'documentTypes',
            'documents'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        abort_if(! $employee, 404, 'Employee profile not found.');

        if ($employee->profile && in_array($employee->profile->profile_status, ['submitted', 'approved'])) {
            return back()->with('error', 'Documents editing is disabled after submission.');
        }

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

                // Employee upload = always pending
                'verification_status' => 'pending',
                'uploaded_by_user_id' => $user->id,
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => null,

                'expiry_date' => $request->expiry_date,
                'uploaded_at' => now(),
                'is_required' => $documentType->is_mandatory,
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Document uploaded successfully. It is pending for HR verification.');
    }

    public function replace(Request $request, $id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        abort_if(! $employee, 404, 'Employee profile not found.');

        $document = EmployeeDocumentM::where('employee_id', $employee->id)->findOrFail($id);

        if ($employee->profile && in_array($employee->profile->profile_status, ['submitted', 'approved'])) {
            return back()->with('error', 'Documents editing is disabled after submission.');
        }

        $request->validate([
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
            'expiry_date' => 'nullable|date',
        ]);

        $file = $request->file('file');
        $documentType = DocumentTypeM::findOrFail($document->document_type_id);
        $storageService = app(\App\Services\HRMS\Document\HrmsFileStorageS::class);
        $meta = $storageService->archiveOrReplaceEmployeeDocument($employee, $documentType, $file);

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
                'uploaded_by_user_id' => $user->id,
                'expiry_date' => $request->expiry_date,
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

                // Employee replace = again pending
                'verification_status' => 'pending',
                'uploaded_by_user_id' => $user->id,
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => null,

                'expiry_date' => $request->expiry_date,
                'uploaded_at' => now(),
            ]);
        }

        return back()->with('success', 'Document replaced successfully. It is pending for HR verification.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        abort_if(! $employee, 404, 'Employee profile not found.');

        if ($employee->profile && in_array($employee->profile->profile_status, ['submitted', 'approved'])) {
            return back()->with('error', 'Documents editing is disabled after submission.');
        }

        $document = EmployeeDocumentM::where('employee_id', $employee->id)->findOrFail($id);

        if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    public function submitForVerification()
    {
        $user = Auth::user();
        $employee = $user->employee;

        abort_if(! $employee, 404, 'Employee profile not found.');

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

        $requiredDocumentTypeIds = DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->where('is_mandatory', 1)
            ->where(function ($q) use ($appliesTo) {
                $q->whereNull('applies_to')
                    ->orWhere('applies_to', '')
                    ->orWhereIn(DB::raw('LOWER(TRIM(applies_to))'), $appliesTo);
            })
            ->pluck('id');

        $uploadedDocumentTypeIds = EmployeeDocumentM::where('employee_id', $employee->id)
            ->whereIn('document_type_id', $requiredDocumentTypeIds)
            ->pluck('document_type_id');

        $missing = $requiredDocumentTypeIds->diff($uploadedDocumentTypeIds);

        if ($missing->count() > 0) {
            return back()->with('error', 'Please upload all mandatory documents before submission.');
        }

        $employee->profile()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'profile_status' => 'submitted',
                'is_profile_completed' => 0,
            ]
        );

        return back()->with('success', 'Profile and documents submitted for verification.');
    }

    private function normalizeTypeKey(?DocumentTypeM $type): string
    {
        if (! $type) {
            return 'misc';
        }

        return $this->paths->normalizeDocType((string) ($type->code ?: $type->name));
    }
}
