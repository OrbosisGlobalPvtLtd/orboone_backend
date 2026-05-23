<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\DocumentTypeM;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentTypeC extends Controller
{
    public function index()
    {
        $documentTypes = DocumentTypeM::latest()->get();

        return view('hrms.documents.types.index', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:150',
            'scope' => 'required|in:employee,policy',
            'applies_to' => 'nullable|in:all,fresher,experienced',

            // NEW
            'allowed_extensions' => 'nullable|array',
            'allowed_extensions.*' => 'string|max:10',

            'max_file_size_mb' => 'nullable|integer|min:1|max:100',
            'allow_multiple' => 'nullable|boolean',
        ]);

        DocumentTypeM::create([
            'name' => $request->name,
            'code' => $request->code ?: Str::slug($request->name, '_'),
            'scope' => $request->scope,
            'applies_to' => $request->applies_to ?? 'all',
            'is_mandatory' => $request->boolean('is_mandatory'),
            'has_expiry' => $request->boolean('has_expiry'),
            'is_active' => $request->boolean('is_active'),

            // NEW
            'allowed_extensions' => $request->allowed_extensions ?? ['pdf'],
            'max_file_size_mb' => $request->max_file_size_mb ?? 5,
            'allow_multiple' => $request->boolean('allow_multiple'),
        ]);

        return redirect()
            ->route('documents.types.index')
            ->with('success', 'Document type created successfully.');
    }

    public function update(Request $request, DocumentTypeM $type)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:150',
            'scope' => 'required|in:employee,policy',
            'applies_to' => 'nullable|in:all,fresher,experienced',

            // NEW
            'allowed_extensions' => 'nullable|array',
            'allowed_extensions.*' => 'string|max:10',

            'max_file_size_mb' => 'nullable|integer|min:1|max:100',
            'allow_multiple' => 'nullable|boolean',
        ]);

        $type->update([
            'name' => $request->name,
            'code' => $request->code ?: Str::slug($request->name, '_'),
            'scope' => $request->scope,
            'applies_to' => $request->applies_to ?? 'all',
            'is_mandatory' => $request->boolean('is_mandatory'),
            'has_expiry' => $request->boolean('has_expiry'),
            'is_active' => $request->boolean('is_active'),

            // NEW
            'allowed_extensions' => $request->allowed_extensions ?? ['pdf'],
            'max_file_size_mb' => $request->max_file_size_mb ?? 5,
            'allow_multiple' => $request->boolean('allow_multiple'),
        ]);

        return redirect()
            ->route('documents.types.index')
            ->with('success', 'Document type updated successfully.');
    }

    public function destroy(DocumentTypeM $type)
    {
        $type->delete();

        return back()->with('success', 'Document type deleted successfully.');
    }
    public function toggleStatus(DocumentTypeM $type)
{
    $type->update([
        'is_active' => !$type->is_active,
    ]);

    return response()->json([
        'success' => true,
        'is_active' => $type->is_active,
        'status' => $type->is_active ? 'Active' : 'Inactive',
    ]);
}
}