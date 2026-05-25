<?php

namespace App\Services\HRMS\DocumentGeneration;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentPdfS
{
    public function generatePdf(string $htmlContent, string $documentType, string $documentNumber, string $employeeCode): string
    {
        $pdf = Pdf::loadHTML($htmlContent);

        // Can customize paper size and orientation if passed
        $pdf->setPaper('A4', 'portrait');

        $fileName = sprintf('%s-%s-%s.pdf', $documentType, $employeeCode, $documentNumber);
        $fileName = Str::slug($fileName) . '.pdf'; // Just in case

        $year = date('Y');
        $month = date('m');
        $path = "private/generated-documents/{$year}/{$month}/{$fileName}";

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function downloadPdf(string $path, string $downloadName = null)
    {
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Document not found.');
        }

        return Storage::disk('local')->download($path, $downloadName);
    }

    public function streamPdf(string $path)
    {
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Document not found.');
        }

        $file = Storage::disk('local')->get($path);
        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }
}
