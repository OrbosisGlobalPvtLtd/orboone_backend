<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\EmployeeModel;
use App\Models\DocumentCategory;
use App\Models\Access;
use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $employee = EmployeeModel::with(['user', 'profile'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $documents = EmployeeDocument::where('employee_id', $employee->id)
            ->with(['category', 'verifiedBy', 'employee'])
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = DocumentCategory::orderBy('name')->get();

        $allEmployees = EmployeeModel::with(['user', 'profile'])
            ->orderBy('employee_code')
            ->get();

        $accesses = Access::where('role_id', $user->role_id)->get();

        return view('employee.documents-index', compact(
            'documents',
            'categories',
            'user',
            'employee',
            'accesses',
            'allEmployees'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'category_id' => 'nullable|exists:document_categories,id',
            'title'       => 'required|string|max:200',
            'file'        => 'required|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $exists = EmployeeDocument::where('employee_id', $request->employee_id)
            ->where('title', $request->title)
            ->whereIn('verification_status', ['pending', 'verified'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This document is already submitted and active for the selected employee.');
        }

        $file = $request->file('file');
        $fileName = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $destination = public_path('uploads/employee_docs');

        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        $file->move($destination, $fileName);
        $path = 'uploads/employee_docs/' . $fileName;

        EmployeeDocument::create([
            'employee_id'           => $request->employee_id,
            'category_id'           => $request->category_id,
            'title'                 => $request->title,
            'file_path'             => $path,
            'verification_status'   => 'pending',
            'verified_by_user_id'   => null,
            'uploaded_at'           => now(),
        ]);

        return back()->with('success', 'Document uploaded successfully! It is now pending HR verification.');
    }

    public function destroy($id)
    {
        $doc = EmployeeDocument::findOrFail($id);

        if (in_array($doc->verification_status, ['verified'])) {
            return back()->with('error', 'Verified documents cannot be deleted. Contact HR to revoke first.');
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