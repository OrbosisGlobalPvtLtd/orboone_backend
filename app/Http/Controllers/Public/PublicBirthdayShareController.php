<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\HRMS\BirthdayShareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PublicBirthdayShareController extends Controller
{
    protected $birthdayShareService;

    public function __construct(BirthdayShareService $birthdayShareService)
    {
        $this->birthdayShareService = $birthdayShareService;
    }

    /**
     * Render the public birthday celebration page with Open Graph & Twitter meta tags.
     */
    public function show(string $token)
    {
        $employeeData = $this->birthdayShareService->resolveToken($token);

        if (!$employeeData) {
            // Fallback default response for unknown/expired tokens
            $employeeData = [
                'employee_id' => 0,
                'name' => 'Orbosis Global Team Member',
                'department' => 'Orbosis Global Pvt. Ltd.',
                'designation' => '',
                'image_url' => null,
            ];
        }

        $shareUrl = route('public.birthday.share', ['token' => $token]);
        $imageUrl = route('public.birthday.image', ['token' => $token]);
        $companyWebsite = 'https://orbosis.com';

        return view('public.birthday_share', [
            'employee' => $employeeData,
            'shareUrl' => $shareUrl,
            'imageUrl' => $imageUrl,
            'companyWebsite' => $companyWebsite,
            'token' => $token,
        ]);
    }

    /**
     * Serve a public image / preview card for Open Graph crawlers (WhatsApp, Facebook, LinkedIn).
     */
    public function image(string $token)
    {
        $employeeData = $this->birthdayShareService->resolveToken($token);
        $name = $employeeData['name'] ?? 'Team Member';
        $initial = strtoupper(substr($name, 0, 1));

        // Create a 1200x630 Open Graph image with GD library
        $width = 1200;
        $height = 630;

        $im = imagecreatetruecolor($width, $height);

        // Purple gradient background colors
        $bgStart = imagecolorallocate($im, 58, 0, 181);  // #3A00B5
        $bgEnd   = imagecolorallocate($im, 134, 0, 238); // #8600EE
        $white   = imagecolorallocate($im, 255, 255, 255);
        $gold    = imagecolorallocate($im, 255, 213, 79); // #FFD54F
        $subText = imagecolorallocate($im, 235, 225, 255);

        // Fill background with gradient vertical stripes
        for ($y = 0; $y < $height; $y++) {
            $r = (int) (58 + (134 - 58) * ($y / $height));
            $g = 0;
            $b = (int) (181 + (238 - 181) * ($y / $height));
            $col = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, $width, $y, $col);
        }

        // Draw company header badge text using built-in font
        imagestring($im, 5, 50, 40, "ORBOSIS GLOBAL PVT. LTD. | BIRTHDAY CELEBRATION", $gold);
        imagestring($im, 5, 50, 100, "Happy Birthday!", $white);
        imagestring($im, 5, 50, 160, $name, $gold);

        if (!empty($employeeData['department'])) {
            imagestring($im, 4, 50, 210, $employeeData['department'], $subText);
        }

        imagestring($im, 4, 50, 280, "Wishing you joy, success & a fantastic year ahead!", $white);
        imagestring($im, 5, 50, 340, "- Team Orbosis Global", $gold);
        imagestring($im, 4, 50, 420, "https://orbosis.com", $subText);

        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);

        return response($imageData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
