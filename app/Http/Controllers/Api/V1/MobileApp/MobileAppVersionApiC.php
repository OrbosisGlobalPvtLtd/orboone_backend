<?php

namespace App\Http\Controllers\Api\V1\MobileApp;

use App\Http\Controllers\Controller;
use App\Models\HRMS\MobileApp\MobileAppVersionM;
use Illuminate\Http\Request;

class MobileAppVersionApiC extends Controller
{
    public function latest(Request $request)
    {
        $platform = strtolower($request->query('platform', 'android'));
        $installedVersionCode = (int) $request->query('version_code', 0);

        $latest = MobileAppVersionM::where('platform', $platform)
            ->where('is_active', true)
            ->orderByDesc('version_code')
            ->first();

        if (! $latest) {
            return response()->json([
                'success' => true,
                'message' => 'APK not available. Please contact admin.',
                'errors' => null,
                'data' => [
                    'update_available' => false,
                    'force_update_required' => false,
                ],
            ]);
        }

        $apkPath = $this->resolveApkPath($latest);

        if (! $apkPath) {
            return response()->json([
                'success' => true,
                'message' => 'APK file record exists but file is missing on server.',
                'errors' => null,
                'data' => [
                    'update_available' => false,
                    'force_update_required' => false,
                    'debug_apk_file' => $latest->apk_file,
                ],
            ]);
        }

        $updateAvailable = $latest->version_code > $installedVersionCode;

        $forceUpdateRequired =
            $updateAvailable &&
            (
                $latest->is_force_update ||
                $installedVersionCode < $latest->min_supported_version_code
            );

        return response()->json([
            'success' => true,
            'message' => 'Latest app version fetched successfully.',
            'errors' => null,
            'data' => [
                'app_name' => $latest->app_name,
                'platform' => $latest->platform,
                'version_name' => $latest->version_name,
                'version_code' => $latest->version_code,
                'min_supported_version_code' => $latest->min_supported_version_code,
                'update_available' => $updateAvailable,
                'force_update_required' => $forceUpdateRequired,
                'is_force_update' => (bool) $latest->is_force_update,
                'apk_url' => url('/mobile-app/download/' . $latest->id),
                'stored_apk_url' => $latest->apk_url,
                'apk_size' => $latest->apk_size,
                'release_notes' => $this->releaseNotesAsArray($latest->release_notes),
                'release_date' => optional($latest->release_date)->toDateTimeString(),
            ],
        ]);
    }

    private function resolveApkPath(MobileAppVersionM $version): ?string
    {
        $relative = ltrim($version->apk_file ?? '', '/');

        $candidates = [
            storage_path('app/private/' . $relative),
            storage_path('app/' . $relative),
            storage_path('app/public/' . $relative),
        ];

        foreach ($candidates as $path) {
            if ($relative && file_exists($path)) {
                return $path;
            }
        }

        \Log::error('Mobile App APK not found', [
            'version_id' => $version->id,
            'apk_file' => $version->apk_file,
            'checked_paths' => $candidates,
        ]);

        return null;
    }

    private function releaseNotesAsArray(?string $notes): array
    {
        if (! $notes) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $notes))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
