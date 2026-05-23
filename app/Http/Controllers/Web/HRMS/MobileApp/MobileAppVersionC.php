<?php

namespace App\Http\Controllers\Web\HRMS\MobileApp;

use App\Http\Controllers\Controller;
use App\Models\HRMS\MobileApp\MobileAppVersionM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

        $version = DB::transaction(function () use ($validated, $platform, $versionCode, $file, $path, $filename) {
            MobileAppVersionM::where('platform', $platform)->update(['is_active' => false]);

            return MobileAppVersionM::create([
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

        $this->notifyAppUpdate($version);

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

        $downloadName = 'OrboOne-v' . $version->version_name . '.apk';

        $path = storage_path('app/public/' . $version->apk_file);

        return response()->download($path, $downloadName, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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

    private function notifyAppUpdate(MobileAppVersionM $version): void
    {
        $query = DB::table('users')->select('id', 'system_role_id');

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', 1);
        }

        $payload = [
            'version_name' => $version->version_name,
            'version_code' => $version->version_code,
            'changelog' => $version->release_notes,
            'release_notes' => preg_split('/\r\n|\r|\n/', (string) $version->release_notes),
            'apk_url' => $version->apk_url ?: asset('storage/' . $version->apk_file),
            'attachment_url' => $version->apk_url ?: asset('storage/' . $version->apk_file),
            'attachment_type' => 'apk',
            'attachment_name' => $version->apk_original_name ?: basename((string) $version->apk_file),
            'force_update_required' => (bool) $version->is_force_update,
        ];

        foreach ($query->get() as $user) {
            try {
                app(\App\Services\HRMS\Notification\NotificationS::class)->createNotification(
                    userId: $user->id,
                    roleId: $user->system_role_id ?? null,
                    title: 'New app update available',
                    message: 'Version ' . $version->version_name . ' is available for download.',
                    type: 'apk_update',
                    routeName: 'app_update',
                    routeParams: ['version_code' => $version->version_code],
                    data: $payload
                );
            } catch (\Throwable $e) {
                Log::error('APK update notification failed', [
                    'version_id' => $version->id,
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
