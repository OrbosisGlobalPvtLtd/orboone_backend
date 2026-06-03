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
    protected $wordEngine;
    protected $pdfConverter;

    public function __construct(
        DocumentFieldResolverS $fieldResolver,
        DocumentPlaceholderResolverS $placeholderResolver,
        DocumentPdfS $pdfService,
        DocumentTemplateWordEngineS $wordEngine,
        DocumentPdfConverterS $pdfConverter
    )
    {
        $this->fieldResolver = $fieldResolver;
        $this->placeholderResolver = $placeholderResolver;
        $this->pdfService = $pdfService;
        $this->wordEngine = $wordEngine;
        $this->pdfConverter = $pdfConverter;
    }

    public function previewDocument(int $templateId, ?int $employeeId, array $manualFields = []): string
    {
        $template = DocumentTemplate::findOrFail($templateId);
        $employee = $employeeId ? EmployeeM::find($employeeId) : null;

        $templateType = $template->template_type ?: 'html';
        if ($templateType === 'docx') {
            $resolved = $this->placeholderResolver->resolve($employee, $manualFields, Auth::user());
            $rows = '';
            foreach ($resolved as $key => $value) {
                $rows .= '<tr><td style="padding:8px;border:1px solid #e7eaf3;"><code>{{' . e($key) . '}}</code></td><td style="padding:8px;border:1px solid #e7eaf3;">' . e((string) $value) . '</td></tr>';
            }

            return '<div style="font-family:sans-serif;">'
                . '<h4 style="margin-top:0;">DOCX Placeholder Preview</h4>'
                . '<p style="color:#667085;">This template will generate a DOCX and optional PDF.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
                . '<thead><tr><th style="text-align:left;padding:8px;border:1px solid #e7eaf3;background:#f8fafc;">Placeholder</th><th style="text-align:left;padding:8px;border:1px solid #e7eaf3;background:#f8fafc;">Resolved Value</th></tr></thead>'
                . '<tbody>' . $rows . '</tbody></table>'
                . '</div>';
        }

        return $this->fieldResolver->resolveFields($template->html_template, $employee, $manualFields);
    }

    public function generateDocument(int $templateId, ?int $employeeId, array $manualFields = []): GeneratedDocument
    {
        $template = DocumentTemplate::findOrFail($templateId);
        $employee = $employeeId ? EmployeeM::find($employeeId) : null;
        $templateType = $template->template_type ?: 'html';
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

        if ($templateType === 'docx') {
            $this->generateFromDocxTemplate($document, $template, $employee, $resolvedData);
        } else {
            $this->generateFromHtmlTemplate($document, $template, $employee, $manualFields);
        }

        $document->save();

        $remarks = 'Document generated successfully.';
        if (($document->pdf_status ?? null) === 'libreoffice_missing') {
            $remarks = 'DOCX generated. PDF conversion skipped because LibreOffice is not installed.';
        } elseif (($document->pdf_status ?? null) === 'conversion_failed') {
            $remarks = 'DOCX generated. PDF conversion failed.';
        }
        $this->logAction($document->id, 'generated', $remarks);

        return $document;
    }

    private function generateFromHtmlTemplate(GeneratedDocument $document, DocumentTemplate $template, ?EmployeeM $employee, array $manualFields): void
    {
        $htmlContent = $this->fieldResolver->resolveFields((string) $template->html_template, $employee, $manualFields);
        $document->generated_html = $htmlContent;
        $employeeCode = $employee?->employee_code ?: 'CAND';
        $templateSlug = Str::slug((string) ($template->slug ?: $template->document_type ?: 'template'));
        $templateCode = strtoupper(str_replace('-', '_', Str::slug((string) ($template->document_type ?: 'DOC'))));
        $timestamp = Carbon::now()->format('Ymd_His');
        $baseName = "{$employeeCode}_{$templateCode}_{$timestamp}";
        $pdfPath = "hrms/generated_documents/{$employeeCode}/{$templateSlug}/{$baseName}.pdf";
        $this->pdfService->generatePdfToPath($htmlContent, $pdfPath);

        $document->pdf_path = $pdfPath;
        $this->setGeneratedDocumentColumn($document, 'generated_pdf_path', $pdfPath);
        $this->setGeneratedDocumentColumn($document, 'pdf_status', 'converted');
    }

    private function generateFromDocxTemplate(GeneratedDocument $document, DocumentTemplate $template, ?EmployeeM $employee, array $resolvedData): void
    {
        if (!$template->docx_file_path) {
            throw new \RuntimeException('DOCX template file is missing. Please upload a DOCX file for this template.');
        }

        $employeeCode = $employee?->employee_code ?: 'CAND';
        $templateSlug = Str::slug((string) ($template->slug ?: $template->document_type ?: 'template'));
        $templateCode = strtoupper(str_replace('-', '_', Str::slug((string) ($template->document_type ?: 'DOC'))));
        $timestamp = Carbon::now()->format('Ymd_His');
        $baseName = "{$employeeCode}_{$templateCode}_{$timestamp}";

        $docxPath = "hrms/generated_documents/{$employeeCode}/{$templateSlug}/{$baseName}.docx";
        $pdfPath = "hrms/generated_documents/{$employeeCode}/{$templateSlug}/{$baseName}.pdf";

        $docxResult = $this->wordEngine->generate((string) $template->docx_file_path, $docxPath, $resolvedData);
        if (empty($docxResult['success'])) {
            throw new \RuntimeException((string) ($docxResult['message'] ?? 'DOCX generation failed.'));
        }

        $this->setGeneratedDocumentColumn($document, 'generated_docx_path', $docxPath);

        $pdfResult = $this->pdfConverter->convertDocxToPdf($docxPath, $pdfPath);
        if (!empty($pdfResult['success'])) {
            $this->setGeneratedDocumentColumn($document, 'generated_pdf_path', $pdfPath);
            $document->pdf_path = $pdfPath;
            $this->setGeneratedDocumentColumn($document, 'pdf_status', 'converted');
            $this->setGeneratedDocumentColumn($document, 'pdf_error_message', null);
            return;
        }

        $this->setGeneratedDocumentColumn($document, 'generated_pdf_path', null);
        $document->pdf_path = null;
        $this->setGeneratedDocumentColumn($document, 'pdf_status', $pdfResult['status'] ?? 'conversion_failed');
        $this->setGeneratedDocumentColumn($document, 'pdf_error_message', $pdfResult['message'] ?? 'PDF conversion failed.');

        Log::warning('DocumentGenerationS: PDF conversion not completed for DOCX template', [
            'template_id' => $template->id,
            'doc_number' => $document->document_number,
            'status' => $document->pdf_status,
            'message' => $document->pdf_error_message,
        ]);
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
