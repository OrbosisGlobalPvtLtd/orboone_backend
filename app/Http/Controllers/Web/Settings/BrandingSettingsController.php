<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\Core\Branding\BrandingSettingsS;

class BrandingSettingsController extends Controller
{
    /**
     * Display the branding settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = DB::table('settings')
            ->where('group', 'branding')
            ->pluck('value', 'key')
            ->toArray();

        $defaults = BrandingSettingsS::defaults();

        $brandingData = [
            'company_name' => $settings['branding.company_name'] ?? $defaults['company_name'],
            'primary_color' => $settings['branding.primary_color'] ?? $defaults['primary_color'],
            'secondary_color' => $settings['branding.secondary_color'] ?? $defaults['secondary_color'],
            'sidebar_color' => $settings['branding.sidebar_color'] ?? $defaults['sidebar_color'],
            'header_color' => $settings['branding.header_color'] ?? $defaults['header_color'],
            'logo_path' => $settings['branding.logo_path'] ?? null,
            'favicon_path' => $settings['branding.favicon_path'] ?? null,
        ];

        return view('settings.branding', [
            'brandingData' => $brandingData,
        ]);
    }

    /**
     * Update the branding settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // 1. Check if it is a reset action
        if ($request->input('action') === 'reset') {
            // Delete all branding records
            $brandingSettings = DB::table('settings')
                ->where('group', 'branding')
                ->get();

            foreach ($brandingSettings as $setting) {
                if (($setting->key === 'branding.logo_path' || $setting->key === 'branding.favicon_path') && !empty($setting->value)) {
                    // Try to delete file if it exists in public storage
                    Storage::disk('public')->delete($setting->value);
                }
            }

            DB::table('settings')->where('group', 'branding')->delete();

            // Clear branding settings cache
            BrandingSettingsS::clearCache();

            return redirect()
                ->route('settings.branding.index')
                ->with('success', 'Branding settings have been reset to default OrboOne theme.');
        }

        // 2. Validate input fields
        $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'primary_color' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/i'],
            'secondary_color' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/i'],
            'sidebar_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/i'],
            'header_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/i'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,jpg,jpeg,svg', 'max:1024'],
        ]);

        $updates = [
            'branding.company_name' => $request->input('company_name'),
            'branding.primary_color' => $request->input('primary_color'),
            'branding.secondary_color' => $request->input('secondary_color'),
            'branding.sidebar_color' => $request->input('sidebar_color') ?: $request->input('primary_color'),
            'branding.header_color' => $request->input('header_color') ?: '#ffffff',
        ];

        // 3. Process Logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo file if it exists
            $oldLogo = DB::table('settings')->where('key', 'branding.logo_path')->value('value');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('branding/logo', 'public');
            $updates['branding.logo_path'] = $path;
        }

        // 4. Process Favicon upload
        if ($request->hasFile('favicon')) {
            // Delete old favicon file if it exists
            $oldFavicon = DB::table('settings')->where('key', 'branding.favicon_path')->value('value');
            if ($oldFavicon) {
                Storage::disk('public')->delete($oldFavicon);
            }

            $path = $request->file('favicon')->store('branding/favicon', 'public');
            $updates['branding.favicon_path'] = $path;
        }

        // 5. Update or Insert settings keys
        foreach ($updates as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'branding',
                    'type' => 'string',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // 6. Clear branding settings cache
        BrandingSettingsS::clearCache();

        return redirect()
            ->route('settings.branding.index')
            ->with('success', 'Branding settings updated successfully.');
    }
}
