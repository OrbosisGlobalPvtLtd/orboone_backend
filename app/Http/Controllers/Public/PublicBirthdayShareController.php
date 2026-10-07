<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\HRMS\Birthday\BirthdayShareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PublicBirthdayShareController extends Controller
{
    protected BirthdayShareService $birthdayShareService;

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
            return response()->view('public.birthday_expired', [
                'companyWebsite' => 'https://orbosis.com',
            ], 410);
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
        $dept = $employeeData['department'] ?? 'Orbosis Global Pvt. Ltd.';
        $desig = $employeeData['designation'] ?? '';

        $width = 1200;
        $height = 630;

        $im = imagecreatetruecolor($width, $height);

        // Rich HRMS Gradient (#4F46E5 -> #7C3AED -> #C026D3)
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int) (79 + (192 - 79) * $ratio);
            $g = (int) (70 + (38 - 70) * $ratio);
            $b = (int) (229 + (211 - 229) * $ratio);
            $col = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, $width, $y, $col);
        }

        $white   = imagecolorallocate($im, 255, 255, 255);
        $gold    = imagecolorallocate($im, 255, 213, 79);  // #FFD54F
        $subText = imagecolorallocate($im, 233, 213, 255); // #E9D5FF

        // Top Header
        imagestring($im, 5, 80, 50, "ORBOSIS GLOBAL PVT. LTD. | BIRTHDAY CELEBRATION", $gold);
        imagestring($im, 5, 80, 120, "HAPPY BIRTHDAY!", $white);
        imagestring($im, 5, 80, 180, strtoupper($name), $gold);

        $deptLine = $dept . ($desig ? " | " . $desig : "");
        imagestring($im, 4, 80, 240, $deptLine, $subText);

        imagestring($im, 5, 80, 330, "Wishing you joy, success & a fantastic year ahead!", $white);
        imagestring($im, 5, 80, 390, "- Team Orbosis Global", $gold);
        imagestring($im, 4, 80, 480, "https://orbosis.com", $subText);

        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);

        return response($imageData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
