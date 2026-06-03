<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Services\HRMS\Storage\HrmsStoragePathS;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentPdfS
{
    public function __construct(private HrmsStoragePathS $paths)
    {
    }

    public function generatePdf(string $htmlContent, string $documentType, string $documentNumber, string $employeeCode): string
    {
        $pdf = Pdf::loadHTML($htmlContent);

        // Can customize paper size and orientation if passed
        $pdf->setPaper('A4', 'portrait');

        $fileName = sprintf('%s-%s-%s.pdf', $documentType, $employeeCode, $documentNumber);
        $fileName = Str::slug($fileName) . '.pdf'; // Just in case

        $year = date('Y');
        $month = date('m');
        $path = $this->paths->generated((int) $year, (int) $month, 'letters') . '/' . $fileName;
        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }

    public function generatePdfToPath(string $htmlContent, string $relativePath): string
    {
        $pdf = Pdf::loadHTML($htmlContent);
        $pdf->setPaper('A4', 'portrait');

        Storage::disk('private')->put($relativePath, $pdf->output());

        return $relativePath;
    }

    public function downloadPdf(string $path, string $downloadName = null)
    {
        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'Document not found.');
        }

        return Storage::disk('private')->download($path, $downloadName);
    }

    public function streamPdf(string $path)
    {
        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'Document not found.');
        }

        $file = Storage::disk('private')->get($path);
        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }
}
