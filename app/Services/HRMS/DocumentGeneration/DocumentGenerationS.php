<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use App\Models\HRMS\DocumentGeneration\GeneratedDocumentLog;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentGenerationS
{
    protected $fieldResolver;
    protected $pdfService;

    public function __construct(DocumentFieldResolverS $fieldResolver, DocumentPdfS $pdfService)
    {
        $this->fieldResolver = $fieldResolver;
        $this->pdfService = $pdfService;
    }

    public function previewDocument(int $templateId, ?int $employeeId, array $manualFields = []): string
    {
        $template = DocumentTemplate::findOrFail($templateId);
        $employee = $employeeId ? EmployeeM::find($employeeId) : null;

        return $this->fieldResolver->resolveFields($template->html_template, $employee, $manualFields);
    }

    public function generateDocument(int $templateId, ?int $employeeId, array $manualFields = []): GeneratedDocument
    {
        $template = DocumentTemplate::findOrFail($templateId);
        $employee = $employeeId ? EmployeeM::find($employeeId) : null;

        $htmlContent = $this->fieldResolver->resolveFields($template->html_template, $employee, $manualFields);

        $documentNumber = $this->generateUniqueDocumentNumber($template->document_type);

        $document = new GeneratedDocument([
            'employee_id' => $employeeId,
            'user_id' => $employee ? $employee->user_id : null,
            'template_id' => $template->id,
            'document_type' => $template->document_type,
            'document_number' => $documentNumber,
            'document_title' => $template->name . ' - ' . ($employee ? $employee->display_name : 'Candidate'),
            'field_values' => $manualFields,
            'generated_html' => $htmlContent,
            'status' => 'generated',
            'generated_by_user_id' => Auth::id() ?? 1,
        ]);

        $pdfPath = $this->pdfService->generatePdf(
            $htmlContent,
            $template->document_type,
            $documentNumber,
            $employee ? $employee->employee_code : 'CAND'
        );

        $document->pdf_path = $pdfPath;
        $document->save();

        $this->logAction($document->id, 'generated', 'Document generated successfully.');

        return $document;
    }

    private function generateUniqueDocumentNumber(string $documentType): string
    {
        $prefix = strtoupper(Str::limit(str_replace('_', '', $documentType), 3, ''));
        if (empty($prefix)) {
            $prefix = 'DOC';
        }
        $number = mt_rand(100000, 999999);
        $docNo = "{$prefix}-" . date('Ym') . "-{$number}";

        while (GeneratedDocument::where('document_number', $docNo)->exists()) {
            $number = mt_rand(100000, 999999);
            $docNo = "{$prefix}-" . date('Ym') . "-{$number}";
        }

        return $docNo;
    }

    public function logAction(int $documentId, string $action, string $remarks = null)
    {
        GeneratedDocumentLog::create([
            'generated_document_id' => $documentId,
            'action' => $action,
            'remarks' => $remarks,
            'actor_user_id' => Auth::id() ?? 1,
        ]);
    }
}
