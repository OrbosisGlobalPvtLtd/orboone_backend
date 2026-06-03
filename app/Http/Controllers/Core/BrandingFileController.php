<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BrandingFileController extends Controller
{
    /**
     * Serve a branding asset from storage.
     *
     * @param string $type
     * @param string $filename
     * @return BinaryFileResponse
     */
    public function serve(string $type, string $filename)
    {
        // 1. Validate type against whitelist
        $allowedTypes = ['logo', 'favicon', 'splash'];
        if (!in_array($type, $allowedTypes)) {
            abort(404, 'Invalid asset type.');
        }

        // 2. Sanitize filename using basename() to prevent path traversal
        $filename = basename($filename);

        // 3. Construct absolute path
        $path = storage_path("app/public/branding/{$type}/{$filename}");

        // 4. Return 404 if missing or not a file
        if (!file_exists($path) || !is_file($path)) {
            abort(404, 'Asset not found.');
        }

        // 5. Serve file response with cache control header
        $response = new BinaryFileResponse($path);
        
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        $response->headers->set('Content-Type', $mime);
        $response->headers->set('Cache-Control', 'public, max-age=86400');

        return $response;
    }
}
