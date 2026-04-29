<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyDocument;
use App\Models\CompanyDocumentModal;
use App\Models\DocumentTypeModal;
use App\Models\DocumentType;
class CompanyDocumentController extends Controller
{
    public function index()
    {
        $docs = CompanyDocumentModal::latest()->get();
        $accesses = \App\Models\Access::where('role_id', auth()->user()->role_id)->get();
        return view('pages.hr.policies-all', compact('docs', 'accesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
            'visible_to' => 'nullable|array'
        ]);

        CompanyDocumentModal::create([
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $request->file('file')->store('policies', 'public'),
            'uploaded_by' => auth()->id(),
            'visible_to' => $request->visible_to ?? ['employee', 'admin'],
            'download_allowed' => $request->has('download_allowed') ? (bool)$request->download_allowed : true
        ]);

        return redirect()->route('hr.policies.index')->with('success', 'Policy uploaded successfully.');
    }

    public function destroy($id)
    {
        $doc = CompanyDocumentModal::findOrFail($id);
        // Optionally delete file from storage if you want:
        // \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return back()->with('success', 'Policy removed successfully.');
    }
}
