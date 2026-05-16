<?php

namespace App\Http\Controllers\Api\V1\MobileApp;

use App\Http\Controllers\Controller;
use App\Models\HRMS\MobileApp\MobileAppVersionM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        if (! $latest || ! $latest->apk_file || ! Storage::disk('public')->exists($latest->apk_file)) {
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

        $updateAvailable = $latest->version_code > $installedVersionCode;
        $forceUpdateRequired = $installedVersionCode < $latest->min_supported_version_code
            || ($latest->is_force_update && $updateAvailable);

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
                'is_force_update' => (bool) $latest->is_force_update,
                'update_available' => $updateAvailable,
                'force_update_required' => $forceUpdateRequired,
                'release_notes' => $this->releaseNotesAsArray($latest->release_notes),
                'apk_url' => $latest->apk_url ?: asset('storage/' . $latest->apk_file),
                'apk_size' => $latest->apk_size,
                'release_date' => optional($latest->release_date)->toIso8601String(),
            ],
        ]);
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
