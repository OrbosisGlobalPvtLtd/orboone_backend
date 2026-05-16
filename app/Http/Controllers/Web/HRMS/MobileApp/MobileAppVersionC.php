<?php

namespace App\Http\Controllers\Web\HRMS\MobileApp;

use App\Http\Controllers\Controller;
use App\Models\HRMS\MobileApp\MobileAppVersionM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MobileAppVersionC extends Controller
{
    public function index()
    {
        $this->authorizePermission('mobile_app_versions.view', 'mobile_app_versions.manage');

        $versions = MobileAppVersionM::with('uploader')
            ->orderByDesc('version_code')
            ->orderByDesc('release_date')
            ->get();

        $latestActive = MobileAppVersionM::where('platform', 'android')
            ->where('is_active', true)
            ->orderByDesc('version_code')
            ->first();

        $permissions = [
            'canView' => $this->hasPermission('mobile_app_versions.view') || $this->hasPermission('mobile_app_versions.manage'),
            'canManage' => $this->hasPermission('mobile_app_versions.manage'),
            'canUpload' => $this->hasPermission('mobile_app_versions.upload') || $this->hasPermission('mobile_app_versions.manage'),
            'canDelete' => $this->hasPermission('mobile_app_versions.delete') || $this->hasPermission('mobile_app_versions.manage'),
        ];

        $stats = [
            'latest_version' => optional($latestActive)->version_name ?? '-',
            'latest_version_code' => optional($latestActive)->version_code ?? '-',
            'force_update_status' => $latestActive && $latestActive->is_force_update ? 'Enabled' : 'Disabled',
            'releases_count' => $versions->count(),
        ];

        return view('hrms.mobile_app_versions.index', compact('versions', 'latestActive', 'stats', 'permissions'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('mobile_app_versions.upload', 'mobile_app_versions.manage');

        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['required', 'string', 'max:50'],
            'version_name' => ['required', 'string', 'max:100'],
            'version_code' => ['required', 'integer', 'min:1'],
            'min_supported_version_code' => ['required', 'integer', 'min:1'],
            'apk_file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (! $value || strtolower($value->getClientOriginalExtension()) !== 'apk') {
                        $fail('The APK file must have a .apk extension.');
                    }
                },
            ],
            'release_notes' => ['nullable', 'string'],
            'is_force_update' => ['nullable', 'boolean'],
        ]);

        $platform = strtolower($validated['platform'] ?: 'android');
        $versionCode = (int) $validated['version_code'];

        $duplicate = MobileAppVersionM::where('platform', $platform)
            ->where('version_code', $versionCode)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors(['version_code' => 'This version code already exists for the selected platform.']);
        }

        $latestActive = MobileAppVersionM::where('platform', $platform)
            ->where('is_active', true)
            ->orderByDesc('version_code')
            ->first();

        if ($latestActive && $versionCode < (int) $latestActive->version_code) {
            return back()->withInput()->withErrors(['version_code' => 'Version code cannot be lower than the current active version code.']);
        }

        $file = $request->file('apk_file');
        $filename = 'orboone-hrms-v' . $versionCode . '-' . now()->format('YmdHis') . '.apk';
        $path = $file->storeAs('mobile-apps', $filename, 'public');

        DB::transaction(function () use ($validated, $platform, $versionCode, $file, $path, $filename) {
            MobileAppVersionM::where('platform', $platform)->update(['is_active' => false]);

            MobileAppVersionM::create([
                'app_name' => $validated['app_name'] ?: 'OrboOne HRMS',
                'platform' => $platform,
                'version_name' => $validated['version_name'],
                'version_code' => $versionCode,
                'min_supported_version_code' => (int) $validated['min_supported_version_code'],
                'apk_file' => $path,
                'apk_original_name' => $file->getClientOriginalName(),
                'apk_size' => $file->getSize(),
                'apk_mime_type' => $file->getClientMimeType(),
                'apk_url' => asset('storage/mobile-apps/' . $filename),
                'release_notes' => $validated['release_notes'] ?? null,
                'is_force_update' => (bool) ($validated['is_force_update'] ?? false),
                'is_active' => true,
                'release_date' => now(),
                'uploaded_by_user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('hrms.mobile-app-versions.index')->with('success', 'APK release uploaded and published successfully.');
    }

    public function toggleActive($id)
    {
        $this->authorizePermission('mobile_app_versions.manage');

        $version = MobileAppVersionM::findOrFail($id);

        if (! $this->apkExists($version)) {
            return back()->with('error', 'Cannot activate this version because the APK file is missing.');
        }

        DB::transaction(function () use ($version) {
            MobileAppVersionM::where('platform', $version->platform)->update(['is_active' => false]);
            $version->update(['is_active' => true]);
        });

        return back()->with('success', 'Selected APK version is now active.');
    }

    public function destroy($id)
    {
        $this->authorizePermission('mobile_app_versions.delete', 'mobile_app_versions.manage');

        $version = MobileAppVersionM::findOrFail($id);
        $otherVersionsCount = MobileAppVersionM::where('platform', $version->platform)
            ->where('id', '!=', $version->id)
            ->count();

        if ($version->is_active && $otherVersionsCount === 0) {
            return back()->with('error', 'Cannot delete the only active APK release. Upload another version first.');
        }

        DB::transaction(function () use ($version) {
            $wasActive = (bool) $version->is_active;
            $platform = $version->platform;

            Storage::disk('public')->delete($version->apk_file);
            $version->delete();

            if ($wasActive) {
                $fallback = MobileAppVersionM::where('platform', $platform)
                    ->orderByDesc('version_code')
                    ->first();

                if ($fallback) {
                    MobileAppVersionM::where('platform', $platform)->update(['is_active' => false]);
                    $fallback->update(['is_active' => true]);
                }
            }
        });

        return back()->with('success', 'APK release deleted successfully.');
    }

    public function download($id)
    {
        $this->authorizePermission('mobile_app_versions.view', 'mobile_app_versions.manage');

        $version = MobileAppVersionM::findOrFail($id);

        return $this->downloadVersion($version, true);
    }

    public function downloadLatest()
    {
        $version = MobileAppVersionM::where('platform', 'android')
            ->where('is_active', true)
            ->orderByDesc('version_code')
            ->first();

        if (! $version || ! $this->apkExists($version)) {
            return response('APK not available. Please contact admin.', 404)
                ->header('Content-Type', 'text/plain');
        }

        return $this->downloadVersion($version, false);
    }

    private function downloadVersion(MobileAppVersionM $version, bool $backOnMissing)
    {
        if (! $this->apkExists($version)) {
            if ($backOnMissing) {
                return back()->with('error', 'APK file is missing. Please verify storage/app/public/mobile-apps and run php artisan storage:link if needed.');
            }

            return response('APK not available. Please contact admin.', 404)
                ->header('Content-Type', 'text/plain');
        }

        $downloadName = $version->apk_original_name ?: 'orboone-hrms-v' . $version->version_code . '.apk';

        return Storage::disk('public')->download($version->apk_file, $downloadName);
    }

    private function apkExists(?MobileAppVersionM $version): bool
    {
        return $version && $version->apk_file && Storage::disk('public')->exists($version->apk_file);
    }

    private function authorizePermission(string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return;
            }
        }

        abort(403);
    }

    private function hasPermission(string $permission): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permission);
    }
}
