<?php

namespace App\Http\Controllers\Web\HRMS\DocumentGeneration;

use App\Http\Controllers\Controller;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\DocumentGeneration\DocumentGenerationS;
use App\Services\HRMS\DocumentGeneration\DocumentPdfS;
use App\Services\HRMS\DocumentGeneration\DocumentEmailS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneratedDocumentC extends Controller
{
    protected $generationService;
    protected $pdfService;
    protected $emailService;

    public function __construct(DocumentGenerationS $generationService, DocumentPdfS $pdfService, DocumentEmailS $emailService)
    {
        $this->generationService = $generationService;
        $this->pdfService = $pdfService;
        $this->emailService = $emailService;
    }

    public function dashboard()
    {
        $totalTemplates = DocumentTemplate::count();
        $activeTemplates = DocumentTemplate::where('is_active', true)->count();
        $generatedDocuments = GeneratedDocument::count();
        $pendingReview = GeneratedDocument::where('status', 'draft')->count(); // Or 'review' if added later
        $sentDocuments = GeneratedDocument::where('status', 'sent')->count();
        $draftDocuments = GeneratedDocument::where('status', 'draft')->count();

        $recentDocuments = GeneratedDocument::with(['template', 'employee'])->latest()->take(5)->get();

        return view('hrms.document-generation.dashboard', compact(
            'totalTemplates', 'activeTemplates', 'generatedDocuments', 
            'pendingReview', 'sentDocuments', 'draftDocuments', 'recentDocuments'
        ));
    }

    public function index(Request $request)
    {
        $query = GeneratedDocument::with(['template', 'employee', 'generatedBy']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest()->paginate(15);
        $employees = EmployeeM::active()->get();

        return view('hrms.document-generation.generated.index', compact('documents', 'employees'));
    }

    public function create(Request $request)
    {
        $templates = DocumentTemplate::where('is_active', true)->get();
        $employees = EmployeeM::active()->get();
        
        $selectedTemplate = null;
        if ($request->has('template_id')) {
            $selectedTemplate = DocumentTemplate::with('fields')->find($request->template_id);
        }

        return view('hrms.document-generation.generated.create', compact('templates', 'employees', 'selectedTemplate'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
        ]);

        $html = $this->generationService->previewDocument(
            $request->template_id,
            $request->employee_id,
            $request->input('manual_fields', [])
        );

        return response()->json(['html' => $html]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'employee_id' => 'nullable|exists:employees_new,id', // Allow nullable for candidates
        ]);

        $document = $this->generationService->generateDocument(
            $request->template_id,
            $request->employee_id,
            $request->input('manual_fields', [])
        );

        return redirect()->route('hrms.document-generation.generated.index')
            ->with('success', 'Document generated successfully.');
    }

    public function show($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        return view('hrms.document-generation.generated.show', compact('document'));
    }

    public function download($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        return $this->pdfService->downloadPdf($document->pdf_path, basename($document->pdf_path));
    }
    
    public function streamPdf($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        return $this->pdfService->streamPdf($document->pdf_path);
    }

    public function email(Request $request, $id)
    {
        $document = GeneratedDocument::findOrFail($id);
        
        $request->validate([
            'email_to' => 'required|email',
            'email_subject' => 'required|string',
            'email_body' => 'required|string',
        ]);

        $this->emailService->sendDocument($document, $request->email_to, $request->email_subject, $request->email_body);

        return redirect()->back()->with('success', 'Document sent successfully.');
    }

    public function review(Request $request, $id)
    {
        $document = GeneratedDocument::findOrFail($id);
        $document->update([
            'status' => 'reviewed',
            'review_note' => $request->review_note,
            'reviewed_by_user_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        
        $this->generationService->logAction($document->id, 'reviewed', $request->review_note);

        return redirect()->back()->with('success', 'Document reviewed successfully.');
    }

    public function cancel(Request $request, $id)
    {
        $document = GeneratedDocument::findOrFail($id);
        $document->update(['status' => 'cancelled']);
        
        $this->generationService->logAction($document->id, 'cancelled', $request->reason ?? 'Cancelled by user');

        return redirect()->back()->with('success', 'Document cancelled.');
    }

    // Employee Self Route
    public function selfIndex()
    {
        $documents = GeneratedDocument::where('employee_id', Auth::user()->employee->id ?? 0)
            ->whereIn('status', ['generated', 'sent', 'reviewed'])
            ->latest()->paginate(15);
            
        return view('hrms.document-generation.self.index', compact('documents'));
    }

    public function selfDownload($id)
    {
        $document = GeneratedDocument::where('employee_id', Auth::user()->employee->id ?? 0)
            ->findOrFail($id);
            
        return $this->pdfService->downloadPdf($document->pdf_path, basename($document->pdf_path));
    }
}
