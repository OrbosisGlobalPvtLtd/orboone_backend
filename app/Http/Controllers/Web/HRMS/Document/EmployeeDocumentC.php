<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM as DocumentTypeModal;
use App\Models\HRMS\Document\EmployeeDocumentM as EmployeeDocumentModal;
use App\Services\HRMS\Document\DocumentS;
use Illuminate\Http\Request;

class EmployeeDocumentC extends Controller
{
    private DocumentS $documentService;

    public function __construct(DocumentS $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index()
    {
        $user = auth()->user();

        $documents = EmployeeDocumentModal::where('user_id', $user->id)
            ->with('type')
            ->orderBy('created_at', 'desc')
            ->get();

        $types = DocumentTypeModal::where('scope', 'employee')->get();

        $allEmployees = \App\Models\HRMS\Employee\EmployeeM::with(['user', 'employeeDetail'])->orderBy('name')->get();

        $accesses = \App\Models\Core\AccessM::where('role_id', $user->role_id)->get();

        return view('hrms.document.employee.documents-index', compact('documents', 'types', 'user', 'accesses', 'allEmployees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'document_type_id' => 'required|exists:document_types,id',
            'file'             => 'required|mimes:pdf,jpg,jpeg,png,docx|max:5120',
        ]);

        $type = DocumentTypeModal::findOrFail($request->document_type_id);

        $exists = EmployeeDocumentModal::where('user_id', $request->user_id)
            ->where('document_type_id', $request->document_type_id)
            ->whereIn('status', ['pending', 'verified', 'approved'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This document type is already submitted and active for the selected employee.');
        }

        $path = $this->documentService->storePublicUpload($request->file('file'));

        EmployeeDocumentModal::create([
            'user_id'          => $request->user_id,
            'document_type_id' => $request->document_type_id,
            'document_type'    => $type->name,
            'file_path'        => $path,
            'uploaded_by'      => auth()->id(),
            'status'           => 'pending',
        ]);

        return back()->with('success', 'Document uploaded successfully! It is now pending HR verification.');
    }

    public function destroy($id)
    {
        $doc = EmployeeDocumentModal::findOrFail($id);

        if (in_array($doc->status, ['verified', 'approved'])) {
            return back()->with('error', 'Approved documents cannot be deleted. Contact HR to revoke first.');
        }

        if ($doc->file_path) {
            $fullPath = public_path($doc->file_path);
            if (file_exists($fullPath) && is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        $doc->delete();

        return back()->with('success', 'Document removed successfully.');
    }
}
