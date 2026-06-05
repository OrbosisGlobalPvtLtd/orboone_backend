<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use App\Models\HRMS\DocumentGeneration\GeneratedDocumentLog;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DocumentGenerationS
{
    protected $fieldResolver;
    protected $placeholderResolver;
    protected $pdfService;

    public function __construct(
        DocumentFieldResolverS $fieldResolver,
        DocumentPlaceholderResolverS $placeholderResolver,
        DocumentPdfS $pdfService
    ) {
        $this->fieldResolver = $fieldResolver;
        $this->placeholderResolver = $placeholderResolver;
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
        $templateVersion = $template->version ?: 'v1';
        $resolvedData = $this->placeholderResolver->resolve($employee, $manualFields, Auth::user());

        $documentNumber = $this->generateUniqueDocumentNumber($template->document_type);

        $documentData = [
            'employee_id' => $employeeId,
            'user_id' => $employee ? $employee->user_id : null,
            'template_id' => $template->id,
            'document_type' => $template->document_type,
            'document_number' => $documentNumber,
            'document_title' => $template->name . ' - ' . ($employee ? $employee->display_name : 'Candidate'),
            'field_values' => $manualFields,
            'status' => 'generated',
            'generated_by_user_id' => Auth::id() ?? 1,
            'template_type' => 'html',
        ];

        if ($this->generatedDocumentsHasColumn('template_version')) {
            $documentData['template_version'] = $templateVersion;
        }
        if ($this->generatedDocumentsHasColumn('generation_snapshot')) {
            $documentData['generation_snapshot'] = $resolvedData;
        }
        if ($this->generatedDocumentsHasColumn('placeholder_values')) {
            $documentData['placeholder_values'] = $resolvedData;
        }
        if ($this->generatedDocumentsHasColumn('generated_by')) {
            $documentData['generated_by'] = Auth::id() ?? 1;
        }

        $document = new GeneratedDocument($documentData);

        $this->generateFromHtmlTemplate($document, $template, $employee, $manualFields);

        $document->save();

        $remarks = 'Document generated successfully.';
        $this->logAction($document->id, 'generated', $remarks);

        return $document;
    }

    private function generateFromHtmlTemplate(GeneratedDocument $document, DocumentTemplate $template, ?EmployeeM $employee, array $manualFields): void
    {
        $htmlContent = $this->fieldResolver->resolveFields((string) $template->html_template, $employee, $manualFields);
        $document->generated_html = $htmlContent;

        $employeeCode = $employee ? ($employee->employee_code ?: 'EMP-' . $employee->id) : null;
        $timestamp = Carbon::now()->format('Ymd_His');
        $docTypeUpper = strtoupper($template->document_type);

        if ($employeeCode) {
            $pdfPath = "hrms/employees/{$employeeCode}/generated-documents/{$employeeCode}_{$docTypeUpper}_{$timestamp}.pdf";
        } else {
            $pdfPath = "hrms/manual-documents/{$docTypeUpper}_{$timestamp}.pdf";
        }

        $this->pdfService->generatePdfToPath($htmlContent, $pdfPath);

        $document->pdf_path = $pdfPath;
        $this->setGeneratedDocumentColumn($document, 'generated_pdf_path', $pdfPath);
        $this->setGeneratedDocumentColumn($document, 'pdf_status', 'converted');
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

    private function generatedDocumentsHasColumn(string $column): bool
    {
        static $cache = [];
        if (!array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('generated_documents', $column);
        }

        return $cache[$column];
    }

    private function setGeneratedDocumentColumn(GeneratedDocument $document, string $column, $value): void
    {
        if ($this->generatedDocumentsHasColumn($column)) {
            $document->{$column} = $value;
        }
    }
}
