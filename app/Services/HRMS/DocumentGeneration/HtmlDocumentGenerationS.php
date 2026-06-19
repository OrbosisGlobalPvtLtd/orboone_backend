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

        if ($documentType === 'internship_certificate') {
            $jpegData = $this->composeCertificateImage($data);
            $base64 = 'data:image/jpeg;base64,' . base64_encode($jpegData);
            
            return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Internship</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            background-color: #ffffff;
        }
        img {
            width: 210mm;
            height: 297mm;
            display: block;
            border: none;
            margin: 0;
            padding: 0;
        }
        @media screen {
            html, body {
                width: 100%;
                height: auto;
                background-color: transparent;
            }
            img {
                width: 100%;
                height: auto;
                aspect-ratio: 210 / 297;
            }
        }
    </style>
</head>
<body>
    <img src="' . $base64 . '" alt="Composed Certificate">
</body>
</html>';
        }
        
        $viewName = "hrms.document-generation.pdf-templates." . str_replace('_', '-', $documentType);

        if (!view()->exists($viewName)) {
            throw new \InvalidArgumentException("Blade template for '{$documentType}' not found at [{$viewName}].");
        }

        return view($viewName, $data)->render();
    }

    /**
     * Render the Blade template to HTML, convert to PDF using DOMPDF,
     * save to private storage and create GeneratedDocument.
     */
    public function generate(string $documentType, ?int $employeeId, array $formData): GeneratedDocument
    {
        $employee = $employeeId ? EmployeeM::find($employeeId) : null;
        $data = $this->dataResolver->resolve($employeeId, $formData);

        if ($documentType === 'internship_certificate') {
            $jpegData = $this->composeCertificateImage($data);
            $base64 = 'data:image/jpeg;base64,' . base64_encode($jpegData);

            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Internship</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            background-color: #ffffff;
        }
        img {
            width: 210mm;
            height: 297mm;
            display: block;
            border: none;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <img src="' . $base64 . '" alt="Composed Certificate">
</body>
</html>';
        } else {
            // Resolve template view path
            $viewName = "hrms.document-generation.pdf-templates." . str_replace('_', '-', $documentType);
            if (!view()->exists($viewName)) {
                throw new \InvalidArgumentException("Blade template for '{$documentType}' not found.");
            }

            // Render Blade to raw HTML
            $html = view($viewName, $data)->render();
        }

        // Convert HTML to PDF using DOMPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdfOutput = $pdf->output();

        // Standardized File Naming & Storage
        $employeeCode = $employee ? ($employee->employee_code ?: 'EMP-' . $employee->id) : null;
        $timestamp = Carbon::now()->format('Ymd_His');
        $docTypeUpper = strtoupper($documentType);

        if ($employeeCode) {
            $relativePath = "hrms/employees/{$employeeCode}/generated-documents/{$employeeCode}_{$docTypeUpper}_{$timestamp}.pdf";
        } else {
            $relativePath = "hrms/manual-documents/{$docTypeUpper}_{$timestamp}.pdf";
        }

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
            'template_key' => str_replace('_', '-', $documentType),
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

        // Custom Debug Logging and HTML saving ONLY for Internship Certificate
        if ($documentType === 'internship_certificate') {
            try {
                $debugDir = storage_path('app/debug');
                if (!is_dir($debugDir)) {
                    mkdir($debugDir, 0777, true);
                }
                file_put_contents($debugDir . '/internship-certificate-real-generate.html', $html);
                
                $composedImgPath = $debugDir . '/internship-certificate-composed.jpg';
                if (isset($jpegData)) {
                    file_put_contents($composedImgPath, $jpegData);
                }
                
                $fullPdfPath = storage_path('app/private/' . $relativePath);
                $pdfSize = file_exists($fullPdfPath) ? filesize($fullPdfPath) : 0;
                
                $pdfPageCount = 0;
                if (file_exists($fullPdfPath)) {
                    $pdfContent = file_get_contents($fullPdfPath);
                    $pdfPageCount = preg_match_all('/\/Type\s*\/Page\b/', $pdfContent, $matches);
                    if ($pdfPageCount == 0) {
                        $pdfPageCount = preg_match_all('/\/Type\s*\/Page\s+/', $pdfContent, $matches);
                    }
                }
                
                $finalInternName = $data['employee_name'] ?? $data['candidate_name'] ?? 'Intern Name';
                
                $template = \App\Models\HRMS\DocumentGeneration\DocumentTemplate::where('slug', $documentType)
                    ->orWhere('template_key', str_replace('_', '-', $documentType))
                    ->first();
                
                \Illuminate\Support\Facades\Log::info("INTERNSHIP CERTIFICATE GENERATION LOG", [
                    'latest_generated_document_id' => $document->id,
                    'document_type_id' => $template ? $template->id : null,
                    'document_type_name' => $template ? $template->name : ucwords(str_replace('_', ' ', $documentType)),
                    'template_key' => str_replace('_', '-', $documentType),
                    'final_intern_name' => $finalInternName,
                    'bg_image_exists' => file_exists(public_path('assets/hrms/certificates/internship-certificate-bg.jpg')) ? 'Yes' : 'No',
                    'font_exists' => file_exists(public_path('fonts/PinyonScript-Regular.ttf')) ? 'Yes' : 'No',
                    'composed_image_path' => $composedImgPath,
                    'composed_image_size' => file_exists($composedImgPath) ? filesize($composedImgPath) : 0,
                    'saved_pdf_path' => $relativePath,
                    'saved_pdf_size' => $pdfSize,
                    'pdf_page_count' => $pdfPageCount
                ]);
            } catch (\Throwable $logEx) {
                \Illuminate\Support\Facades\Log::error("Failed to log certificate details: " . $logEx->getMessage());
            }
        }

        return $document;
    }

    /**
     * Compose a high-resolution certificate image using PHP GD.
     */
    private function composeCertificateImage(array $data): string
    {
        $bgPath = public_path('assets/hrms/certificates/internship-certificate-bg.jpg');
        if (!file_exists($bgPath)) {
            throw new \Exception("Background image not found at {$bgPath}");
        }

        $image = imagecreatefromjpeg($bgPath);
        if (!$image) {
            throw new \Exception("Failed to load background image from {$bgPath}");
        }

        $colorBlack = imagecolorallocate($image, 0, 0, 0);
        $colorDarkGray = imagecolorallocate($image, 50, 50, 50);

        $fontPinyon = public_path('fonts/PinyonScript-Regular.ttf');
        $fontSans = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $fontSansBold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');

        if (!file_exists($fontPinyon)) {
            throw new \Exception("PinyonScript font not found at {$fontPinyon}");
        }
        if (!file_exists($fontSans)) {
            throw new \Exception("DejaVuSans font not found at {$fontSans}");
        }
        if (!file_exists($fontSansBold)) {
            throw new \Exception("DejaVuSans-Bold font not found at {$fontSansBold}");
        }

        // Helper to get centered X coordinate
        $getCenterX = function ($text, $fontFile, $fontSizePoints) use ($image) {
            $bbox = imagettfbbox($fontSizePoints, 0, $fontFile, $text);
            $textWidth = abs($bbox[2] - $bbox[0]);
            $imageWidth = imagesx($image);
            return (int)(($imageWidth - $textWidth) / 2);
        };

        // Helper to draw centered mixed line (normal/bold segments)
        $drawCenteredMixedLine = function ($y, $segments, $fontSizePoints, $color) use ($image, $fontSans, $fontSansBold) {
            $totalWidth = 0;
            $widths = [];
            foreach ($segments as $seg) {
                $font = $seg['bold'] ? $fontSansBold : $fontSans;
                $bbox = imagettfbbox($fontSizePoints, 0, $font, $seg['text']);
                $width = abs($bbox[2] - $bbox[0]);
                $totalWidth += $width;
                $widths[] = $width;
            }
            $x = (int)((imagesx($image) - $totalWidth) / 2);
            foreach ($segments as $i => $seg) {
                $font = $seg['bold'] ? $fontSansBold : $fontSans;
                imagettftext($image, $fontSizePoints, 0, $x, $y, $color, $font, $seg['text']);
                $x += $widths[$i];
            }
        };

        // Helper to draw centered wrapped block of text
        $drawCenteredWrappedText = function ($text, $fontSizePoints, $color, $startY, $maxWidth, $lineHeight) use ($image, $fontSans, $getCenterX) {
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';
            foreach ($words as $word) {
                $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
                $bbox = imagettfbbox($fontSizePoints, 0, $fontSans, $testLine);
                $width = abs($bbox[2] - $bbox[0]);
                if ($width > $maxWidth && $currentLine !== '') {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine) {
                $lines[] = $currentLine;
            }
            $y = $startY;
            foreach ($lines as $line) {
                $x = $getCenterX($line, $fontSans, $fontSizePoints);
                imagettftext($image, $fontSizePoints, 0, $x, $y, $color, $fontSans, $line);
                $y += $lineHeight;
            }
            return $y;
        };

        // 1. Draw Intern Name
        $internName = $data['employee_name'] ?? $data['candidate_name'] ?? 'Intern Name';
        $nameLen = strlen($internName);
        if ($nameLen > 25) {
            $nameSize = 110;
            $nameY = 1270;
        } elseif ($nameLen > 20) {
            $nameSize = 130;
            $nameY = 1250;
        } elseif ($nameLen > 15) {
            $nameSize = 160;
            $nameY = 1230;
        } else {
            $nameSize = 180;
            $nameY = 1220;
        }
        $xName = $getCenterX($internName, $fontPinyon, $nameSize);
        imagettftext($image, $nameSize, 0, $xName, $nameY, $colorBlack, $fontPinyon, $internName);

        // 2. Draw Main Text (2 lines centered with inline bold)
        $companyName = $data['company_name'] ?? 'Orbosis Global Pvt. Ltd';
        $designationText = $data['designation'] ?? 'Flutter Developer Intern';
        $startDate = $data['internship_start_date'] ?? 'Start Date';
        $endDate = $data['internship_end_date'] ?? 'End Date';

        // Line 1 segments
        $line1 = [
            ['text' => 'has successfully completed internship program at ', 'bold' => false],
            ['text' => $companyName, 'bold' => true]
        ];
        // Line 2 segments
        $line2 = [
            ['text' => 'as a ', 'bold' => false],
            ['text' => $designationText, 'bold' => true],
            ['text' => ' from ', 'bold' => false],
            ['text' => $startDate, 'bold' => true],
            ['text' => ' to ', 'bold' => false],
            ['text' => $endDate, 'bold' => true],
            ['text' => '.', 'bold' => false]
        ];

        $drawCenteredMixedLine(1678, $line1, 36, $colorBlack);
        $drawCenteredMixedLine(1758, $line2, 36, $colorBlack);

        // 3. Draw Work Summary & Performance Appraisal (centered wrapped text)
        $workText = $data['internship_work_summary']
            ?? 'During his tenure, he worked on Flutter-based development, mobile app UI implementation, API integration, and application optimization tasks, demonstrating strong problem-solving skills and attention to detail.';
        $performanceText = $data['performance_summary']
            ?? 'He showed dedication, professionalism, and a keen willingness to learn throughout the internship. We wish him all the best for his future endeavours.';

        // Combine or draw separately
        $nextY = $drawCenteredWrappedText($workText, 34, $colorDarkGray, 2033, 1819, 52);
        $drawCenteredWrappedText($performanceText, 34, $colorDarkGray, $nextY + 45, 1819, 52);

        // 4. Draw Signatory Name & Designation
        $sigName = $data['signatory_name'] ?? $data['authorized_signatory'] ?? 'Prabhat Agrawal';
        $sigDesig = $data['signatory_designation'] ?? 'CEO';

        $xSigName = $getCenterX($sigName, $fontSansBold, 54);
        imagettftext($image, 54, 0, $xSigName, 3100, $colorBlack, $fontSansBold, $sigName);

        $xSigDesig = $getCenterX($sigDesig, $fontSans, 44);
        imagettftext($image, 44, 0, $xSigDesig, 3180, $colorBlack, $fontSans, $sigDesig);

        // 5. Draw Seal & Signature
        $sealImageStr = $data['seal_image'] ?? null;
        $sigImageStr = $data['signature_image'] ?? null;

        $makeWhiteTransparent = function ($img) {
            if (!$img) {
                return null;
            }
            $width = imagesx($img);
            $height = imagesy($img);

            $transparentImg = imagecreatetruecolor($width, $height);
            imagealphablending($transparentImg, false);
            imagesavealpha($transparentImg, true);

            $transColor = imagecolorallocatealpha($transparentImg, 0, 0, 0, 127);
            imagefill($transparentImg, 0, 0, $transColor);

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($img, $x, $y);
                    $alpha = ($rgb >> 24) & 0x7F;
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // If color is close to white (brightness > 215) or already transparent, make it transparent
                    if ($alpha > 100 || ($r > 215 && $g > 215 && $b > 215)) {
                        imagesetpixel($transparentImg, $x, $y, $transColor);
                    } else {
                        $color = imagecolorallocatealpha($transparentImg, $r, $g, $b, $alpha);
                        imagesetpixel($transparentImg, $x, $y, $color);
                    }
                }
            }
            imagedestroy($img);
            return $transparentImg;
        };

        $loadImage = function ($src) use ($makeWhiteTransparent) {
            if (empty($src)) {
                return null;
            }
            try {
                $img = null;
                if (str_starts_with($src, 'data:image')) {
                    $parts = explode('base64,', $src);
                    if (isset($parts[1])) {
                        $decoded = base64_decode($parts[1]);
                        if ($decoded) {
                            $img = imagecreatefromstring($decoded);
                        }
                    }
                } elseif (file_exists($src)) {
                    $img = imagecreatefromstring(file_get_contents($src));
                } elseif (filter_var($src, FILTER_VALIDATE_URL)) {
                    $ctx = stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                    ]);
                    $content = @file_get_contents($src, false, $ctx);
                    if ($content) {
                        $img = imagecreatefromstring($content);
                    }
                }

                if ($img) {
                    return $makeWhiteTransparent($img);
                }
            } catch (\Throwable $e) {
                // Ignore load error
            }
            return null;
        };

        $drawOverlayImage = function ($destImage, $srcImage, $centerX, $centerY, $maxWidth, $maxHeight) {
            if (!$srcImage) {
                return;
            }
            $srcWidth = imagesx($srcImage);
            $srcHeight = imagesy($srcImage);
            if ($srcWidth <= 0 || $srcHeight <= 0) {
                return;
            }

            $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);
            $destWidth = (int)($srcWidth * $ratio);
            $destHeight = (int)($srcHeight * $ratio);

            $destX = (int)($centerX - ($destWidth / 2));
            $destY = (int)($centerY - ($destHeight / 2));

            imagealphablending($destImage, true);
            imagealphablending($srcImage, true);
            imagecopyresampled($destImage, $srcImage, $destX, $destY, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
        };

        $centerX = (int)(imagesx($image) / 2);
        $centerY = 2770;

        if ($sealImageStr) {
            $sealImage = $loadImage($sealImageStr);
            if ($sealImage) {
                $drawOverlayImage($image, $sealImage, $centerX, $centerY, 360, 360);
                imagedestroy($sealImage);
            }
        }

        if ($sigImageStr) {
            $sigImage = $loadImage($sigImageStr);
            if ($sigImage) {
                $drawOverlayImage($image, $sigImage, $centerX, $centerY, 300, 150);
                imagedestroy($sigImage);
            }
        }

        ob_start();
        imagejpeg($image, null, 95);
        $jpegData = ob_get_clean();
        imagedestroy($image);

        return $jpegData;
    }
}
