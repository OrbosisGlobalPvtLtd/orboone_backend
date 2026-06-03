<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use App\Models\HRMS\Employee\EmployeeM;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HtmlDocumentGenerationS
{
    protected $dataResolver;
    protected $numberService;

    public function __construct(
        DocumentDataResolverS $dataResolver,
        DocumentNumberS $numberService
    ) {
        $this->dataResolver = $dataResolver;
        $this->numberService = $numberService;
    }

    /**
     * Preview Blade HTML before actual PDF conversion.
     */
    public function previewHtml(string $documentType, ?int $employeeId, array $formData): string
    {
        $data = $this->dataResolver->resolve($employeeId, $formData);
        $data['isPreview'] = true;
        
        $viewName = "hrms.document-generation.pdf-templates." . str_replace('_', '-', $documentType);

        if (!view()->exists($viewName)) {
            throw new \InvalidArgumentException("Blade template for '{$documentType}' not found at [{$viewName}].");
        }

        return view($viewName, $data)->render();
    }

    /**
     * Render the Blade template to HTML, convert to PDF using DOMPDF,
     * save to private storage under 'generated_documents/pdf/' and create GeneratedDocument.
     */
    public function generate(string $documentType, ?int $employeeId, array $formData): GeneratedDocument
    {
        $employee = $employeeId ? EmployeeM::find($employeeId) : null;
        $data = $this->dataResolver->resolve($employeeId, $formData);

        // Resolve template view path
        $viewName = "hrms.document-generation.pdf-templates." . str_replace('_', '-', $documentType);
        if (!view()->exists($viewName)) {
            throw new \InvalidArgumentException("Blade template for '{$documentType}' not found.");
        }

        // Render Blade to raw HTML
        $html = view($viewName, $data)->render();

        // Convert HTML to PDF using DOMPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdfOutput = $pdf->output();

        // File naming convention
        $employeePart = $employee ? ($employee->employee_code ?: 'EMP') : Str::slug($formData['candidate_name'] ?? 'candidate');
        $datePart = Carbon::now()->format('Y-m-d');
        $typeSlug = str_replace('_', '-', $documentType);
        $filename = "{$typeSlug}-{$employeePart}-{$datePart}.pdf";
        $relativePath = "generated_documents/pdf/{$filename}";

        // Write PDF file to the secure private disk
        Storage::disk('private')->put($relativePath, $pdfOutput);

        // Unique Document Number
        $docNumber = $this->numberService->generate($documentType);

        // Name of employee or candidate
        $candidateName = null;
        if ($employee) {
            $candidateName = $employee->display_name;
        } else {
            $candidateName = $formData['candidate_name'] ?? null;
        }

        // Save generated document record in db
        $document = GeneratedDocument::create([
            'employee_id' => $employeeId,
            'user_id' => $employee ? $employee->user_id : null,
            'document_type' => $documentType,
            'document_number' => $docNumber,
            'document_title' => ucwords(str_replace('_', ' ', $documentType)) . ' - ' . ($candidateName ?: 'Candidate'),
            'candidate_name' => $candidateName,
            'template_key' => $typeSlug,
            'template_type' => 'html',
            'form_data' => $formData,
            'field_values' => $formData, // Fallback compatibility
            'pdf_path' => $relativePath,
            'generated_pdf_path' => $relativePath,
            'pdf_status' => 'converted',
            'status' => 'generated',
            'generated_by_user_id' => Auth::id() ?? 1,
            'generated_by' => Auth::id() ?? 1,
            'generated_at' => Carbon::now(),
        ]);

        return $document;
    }
}
