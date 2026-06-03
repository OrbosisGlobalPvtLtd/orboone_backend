<?php

namespace App\Http\Controllers\Web\HRMS\DocumentGeneration;

use App\Http\Controllers\Controller;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\DocumentGeneration\DocumentGenerationS;
use App\Services\HRMS\DocumentGeneration\DocumentPdfS;
use App\Services\HRMS\DocumentGeneration\DocumentEmailS;
use App\Services\HRMS\DocumentGeneration\HtmlDocumentGenerationS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class GeneratedDocumentC extends Controller
{
    protected $generationService;
    protected $pdfService;
    protected $emailService;
    protected $htmlGenerationService;

    public function __construct(
        DocumentGenerationS $generationService,
        DocumentPdfS $pdfService,
        DocumentEmailS $emailService,
        HtmlDocumentGenerationS $htmlGenerationService
    ) {
        $this->generationService = $generationService;
        $this->pdfService = $pdfService;
        $this->emailService = $emailService;
        $this->htmlGenerationService = $htmlGenerationService;
    }

    public function dashboard()
    {
        $totalTemplates = DocumentTemplate::count();
        $activeTemplates = DocumentTemplate::where('is_active', true)->count();
        $generatedDocuments = GeneratedDocument::count();
        $pendingReview = GeneratedDocument::where('status', 'draft')->count();
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

        // Format class filtering (Modern HTML is the default primary view)
        $templateClass = $request->input('template_class', 'modern');
        if ($templateClass === 'modern') {
            $query->where(function ($q) {
                $q->where('template_type', 'html')
                  ->orWhereNull('template_type');
            });
        } elseif ($templateClass === 'legacy') {
            $query->where('template_type', 'docx');
        }

        $documents = $query->latest()->paginate(15);
        $employees = EmployeeM::active()->get();

        return view('hrms.document-generation.generated.index', compact('documents', 'employees'));
    }

    public function create(Request $request)
    {
        $templatesQuery = DocumentTemplate::where('is_active', true);
        if (Schema::hasColumn('document_templates', 'is_archived')) {
            $templatesQuery->where(function ($q) {
                $q->whereNull('is_archived')->orWhere('is_archived', false);
            });
        }
        $templates = $templatesQuery->get();
        $employees = EmployeeM::active()->get();
        
        $selectedTemplate = null;
        if ($request->has('template_id')) {
            $selectedTemplate = DocumentTemplate::with('fields')->find($request->template_id);
        }

        // Available HTML Document Types for new flow
        $documentTypes = [
            'offer_letter' => 'Offer Letter',
            'appointment_letter' => 'Appointment Letter',
            'experience_letter' => 'Experience Letter',
            'relieving_letter' => 'Relieving Letter',
            'internship_certificate' => 'Internship Certificate',
            'salary_certificate' => 'Salary Certificate',
            'warning_letter' => 'Warning Letter',
            'appreciation_letter' => 'Appreciation Letter',
            'nda_agreement' => 'NDA / Agreement',
        ];

        return view('hrms.document-generation.generated.create', compact('templates', 'employees', 'selectedTemplate', 'documentTypes'));
    }

    public function preview(Request $request)
    {
        if ($request->filled('document_type')) {
            try {
                $html = $this->htmlGenerationService->previewHtml(
                    $request->document_type,
                    $request->employee_id ?: null,
                    $request->input('manual_fields', [])
                );
                return response()->json(['html' => $html]);
            } catch (\Throwable $e) {
                return response()->json(['html' => '<div class="alert alert-danger">Error: ' . e($e->getMessage()) . '</div>'], 400);
            }
        }

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
        if ($request->filled('document_type')) {
            try {
                $document = $this->htmlGenerationService->generate(
                    $request->document_type,
                    $request->employee_id ?: null,
                    $request->input('manual_fields', [])
                );
                return redirect()->route('hrms.document-generation.generated.index')->with('success', 'Document generated successfully as HTML PDF.');
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'employee_id' => 'nullable|exists:employees_new,id',
        ]);

        try {
            $document = $this->generationService->generateDocument(
                $request->template_id,
                $request->employee_id,
                $request->input('manual_fields', [])
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $message = 'Document generated successfully.';
        if (($document->pdf_status ?? null) === 'libreoffice_missing') {
            $message .= ' DOCX created. PDF conversion requires LibreOffice on server.';
        } elseif (($document->pdf_status ?? null) === 'conversion_failed') {
            $message .= ' DOCX created, but PDF conversion failed.';
        }

        return redirect()->route('hrms.document-generation.generated.index')->with('success', $message);
    }

    public function show($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        return view('hrms.document-generation.generated.show', compact('document'));
    }

    public function download($id)
    {
        $document = GeneratedDocument::findOrFail($id);

        if ($document->generated_pdf_path && Storage::disk('private')->exists($document->generated_pdf_path)) {
            return $this->pdfService->downloadPdf($document->generated_pdf_path, basename($document->generated_pdf_path));
        }

        if ($document->pdf_path && Storage::disk('private')->exists($document->pdf_path)) {
            return $this->pdfService->downloadPdf($document->pdf_path, basename($document->pdf_path));
        }

        abort(404, 'PDF is not available for this document.');
    }
    
    public function streamPdf($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        $path = $document->generated_pdf_path ?: $document->pdf_path;
        if (!$path) {
            abort(404, 'PDF is not available for preview.');
        }
        return $this->pdfService->streamPdf($path);
    }

    public function downloadDocx($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        if (!$document->generated_docx_path || !Storage::disk('private')->exists($document->generated_docx_path)) {
            abort(404, 'DOCX is not available for this document.');
        }

        return Storage::disk('private')->download($document->generated_docx_path, basename($document->generated_docx_path));
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

        if ($document->generated_pdf_path && Storage::disk('private')->exists($document->generated_pdf_path)) {
            return $this->pdfService->downloadPdf($document->generated_pdf_path, basename($document->generated_pdf_path));
        }

        if ($document->pdf_path && Storage::disk('private')->exists($document->pdf_path)) {
            return $this->pdfService->downloadPdf($document->pdf_path, basename($document->pdf_path));
        }

        if ($document->generated_docx_path && Storage::disk('private')->exists($document->generated_docx_path)) {
            return Storage::disk('private')->download($document->generated_docx_path, basename($document->generated_docx_path));
        }

        abort(404, 'No downloadable file is available for this document.');
    }

    public function regenerate($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        
        try {
            if (($document->template_type ?? 'html') === 'html') {
                $html = $this->htmlGenerationService->previewHtml(
                    $document->document_type,
                    $document->employee_id ?: null,
                    $document->form_data ?: $document->field_values ?: []
                );
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'portrait');
                $output = $pdf->output();
                
                $path = $document->generated_pdf_path ?: "generated_documents/pdf/regenerated-{$document->document_number}.pdf";
                Storage::disk('private')->put($path, $output);
                
                $document->update([
                    'generated_pdf_path' => $path,
                    'pdf_path' => $path,
                    'pdf_status' => 'converted'
                ]);
                
                return redirect()->back()->with('success', 'PDF regenerated successfully.');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Regeneration failed: ' . $e->getMessage());
        }
        
        return redirect()->back()->with('error', 'Only HTML templates can be automatically regenerated.');
    }
}
