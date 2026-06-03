<?php

namespace App\Services\HRMS\DocumentGeneration;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DocumentPdfConverterS
{
    /**
     * Convert private DOCX path into private PDF path using LibreOffice.
     */
    public function convertDocxToPdf(string $docxRelativePath, string $pdfRelativePath): array
    {
        if (!Storage::disk('private')->exists($docxRelativePath)) {
            return [
                'success' => false,
                'status' => 'docx_missing',
                'message' => 'DOCX file not found for PDF conversion.',
            ];
        }

        $binary = $this->findLibreOfficeBinary();
        if (!$binary) {
            return [
                'success' => false,
                'status' => 'libreoffice_missing',
                'message' => 'PDF conversion skipped: LibreOffice is not installed on this server.',
            ];
        }

        $docxAbsolute = Storage::disk('private')->path($docxRelativePath);
        $targetAbsolute = Storage::disk('private')->path($pdfRelativePath);
        $targetDir = dirname($targetAbsolute);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $tmpOutDir = storage_path('app/private/hrms/temp/exports/pdf');
        if (!is_dir($tmpOutDir)) {
            @mkdir($tmpOutDir, 0775, true);
        }

        $command = [
            $binary,
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            $tmpOutDir,
            $docxAbsolute,
        ];

        try {
            $process = new Process($command);
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::warning('DocumentPdfConverterS LibreOffice process failed', [
                    'docx' => $docxRelativePath,
                    'stderr' => $process->getErrorOutput(),
                    'stdout' => $process->getOutput(),
                ]);

                return [
                    'success' => false,
                    'status' => 'conversion_failed',
                    'message' => 'PDF conversion failed via LibreOffice.',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentPdfConverterS exception', [
                'docx' => $docxRelativePath,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'conversion_failed',
                'message' => 'PDF conversion failed: ' . $e->getMessage(),
            ];
        }

        $tmpPdf = $tmpOutDir . DIRECTORY_SEPARATOR . pathinfo($docxAbsolute, PATHINFO_FILENAME) . '.pdf';
        if (!is_file($tmpPdf)) {
            return [
                'success' => false,
                'status' => 'conversion_failed',
                'message' => 'PDF conversion output file not found.',
            ];
        }

        if (!@copy($tmpPdf, $targetAbsolute)) {
            return [
                'success' => false,
                'status' => 'conversion_failed',
                'message' => 'Unable to move converted PDF into private storage.',
            ];
        }

        @unlink($tmpPdf);

        return [
            'success' => true,
            'status' => 'converted',
            'message' => 'PDF converted successfully.',
            'pdf_path' => $pdfRelativePath,
        ];
    }

    private function findLibreOfficeBinary(): ?string
    {
        $candidates = [];

        if (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
            $candidates = [
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            ];
        } else {
            $candidates = [
                '/usr/bin/libreoffice',
                '/usr/local/bin/libreoffice',
                '/usr/bin/soffice',
                '/usr/local/bin/soffice',
            ];
        }

        foreach ($candidates as $binary) {
            if (is_file($binary)) {
                return $binary;
            }
        }

        return null;
    }

    public function isLibreOfficeAvailable(): bool
    {
        return $this->findLibreOfficeBinary() !== null;
    }
}
