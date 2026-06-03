<?php

namespace App\Services\HRMS\DocumentGeneration;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class DocumentTemplateWordEngineS
{
    /**
     * Generate a DOCX file from DOCX template and resolved placeholders.
     */
    public function generate(string $templateRelativePath, string $outputRelativePath, array $data): array
    {
        if (!class_exists(TemplateProcessor::class)) {
            return [
                'success' => false,
                'message' => 'PHPWord is not installed. Please install phpoffice/phpword to enable DOCX generation.',
            ];
        }

        if (!Storage::disk('private')->exists($templateRelativePath)) {
            return [
                'success' => false,
                'message' => 'Template file is missing in private storage.',
            ];
        }

        $templateAbsolute = Storage::disk('private')->path($templateRelativePath);
        $tempTemplatePath = $this->buildTempPath('docx_template_');

        if (!copy($templateAbsolute, $tempTemplatePath)) {
            return [
                'success' => false,
                'message' => 'Unable to prepare template copy for DOCX processing.',
            ];
        }

        $this->convertBracedPlaceholdersToPhpWordTokens($tempTemplatePath);

        $targetAbsolute = Storage::disk('private')->path($outputRelativePath);
        $targetDir = dirname($targetAbsolute);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        try {
            $processor = new TemplateProcessor($tempTemplatePath);
            foreach ($data as $key => $value) {
                $processor->setValue((string) $key, (string) ($value ?? ''));
            }
            $processor->saveAs($targetAbsolute);
        } catch (\Throwable $e) {
            Log::error('DocumentTemplateWordEngineS generation failed', [
                'template' => $templateRelativePath,
                'output' => $outputRelativePath,
                'error' => $e->getMessage(),
            ]);

            @unlink($tempTemplatePath);

            return [
                'success' => false,
                'message' => 'DOCX generation failed: ' . $e->getMessage(),
            ];
        }

        @unlink($tempTemplatePath);

        return [
            'success' => true,
            'message' => 'DOCX generated successfully.',
            'docx_path' => $outputRelativePath,
        ];
    }

    private function convertBracedPlaceholdersToPhpWordTokens(string $docxPath): void
    {
        if (!class_exists(ZipArchive::class)) {
            return;
        }

        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (!is_string($entry) || !str_starts_with($entry, 'word/') || !str_ends_with($entry, '.xml')) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if (!is_string($content) || $content === '') {
                continue;
            }

            $replaced = preg_replace('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', '${$1}', $content);
            if (is_string($replaced) && $replaced !== $content) {
                $zip->addFromString($entry, $replaced);
            }
        }

        $zip->close();
    }

    private function buildTempPath(string $prefix): string
    {
        $base = storage_path('app/private/hrms/temp/exports');
        if (!is_dir($base)) {
            @mkdir($base, 0775, true);
        }

        return $base . DIRECTORY_SEPARATOR . $prefix . uniqid('', true) . '.docx';
    }
}
