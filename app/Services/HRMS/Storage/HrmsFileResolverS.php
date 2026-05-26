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
}
