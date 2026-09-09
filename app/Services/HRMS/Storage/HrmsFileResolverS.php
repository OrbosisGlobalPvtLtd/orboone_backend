<?php

namespace App\Services\HRMS\Storage;

class HrmsFileResolverS
{
    public function normalizeDbPath(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^https?://[^/]+/#i', '', $path);
        $path = preg_replace('#^storage/app/private/#i', '', $path);
        $path = ltrim($path, '/');

        return $path === '' ? null : $path;
    }

    public function resolve(?string $dbPath): ?array
    {
        $path = $this->normalizeDbPath($dbPath);
        if (! $path || str_contains($path, '..')) {
            return null;
        }

        if (! str_starts_with($path, 'hrms/')) {
            return null;
        }

        $candidates = [
            ['disk' => 'private', 'relative' => $path, 'absolute' => storage_path('app/private/' . $path)],
        ];

        // HEIC conversion check: if .heic, check if .jpg exists or convert
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['heic', 'heif'], true)) {
            $jpgRelative = preg_replace('/\.(heic|heif)$/i', '.jpg', $path);
            $jpgAbsolute = storage_path('app/private/' . $jpgRelative);
            $heicAbsolute = storage_path('app/private/' . $path);

            if (is_file($jpgAbsolute)) {
                return ['disk' => 'private', 'relative' => $jpgRelative, 'absolute' => $jpgAbsolute];
            } elseif (is_file($heicAbsolute) && function_exists('imagecreatefromstring')) {
                try {
                    $content = @file_get_contents($heicAbsolute);
                    if ($content) {
                        $img = @imagecreatefromstring($content);
                        if ($img !== false) {
                            @imagejpeg($img, $jpgAbsolute, 90);
                            @imagedestroy($img);
                            if (is_file($jpgAbsolute)) {
                                return ['disk' => 'private', 'relative' => $jpgRelative, 'absolute' => $jpgAbsolute];
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback to original file if conversion fails
                }
            }
        }

        foreach ($candidates as $candidate) {
            if (! empty($candidate['relative']) && is_file($candidate['absolute'])) {
                return $candidate;
            }
        }

        return null;
    }

    public function secureFileUrl(?string $path): string
    {
        $normalized = $this->normalizeDbPath($path);
        if (! $normalized || ! str_starts_with($normalized, 'hrms/')) {
            return '';
        }

        return url('/api/v1/file?path=' . urlencode($normalized));
    }

    /**
     * Stream file with correct headers for private disk.
     */
    public function secureFileStream(?string $dbPath): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $resolved = $this->resolve($dbPath);
        if (!$resolved) {
            return null;
        }

        $absolute = $resolved['absolute'];
        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
        $mime = mime_content_type($absolute) ?: 'application/octet-stream';
        if ($ext === 'pdf') {
            $mime = 'application/pdf';
        }

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($absolute) . '"',
        ]);
    }
}
