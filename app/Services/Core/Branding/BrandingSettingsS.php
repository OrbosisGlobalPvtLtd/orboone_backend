<?php

namespace App\Services\Core\Branding;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class BrandingSettingsS
{
    public const CACHE_KEY = 'branding_settings';

    /**
     * Get OrboOne HRMS theme/branding defaults.
     *
     * @return array
     */
    public static function defaults(): array
    {
        return [
            'company_name' => 'OrboOne HRMS',
            'logo_url' => null,
            'favicon_url' => null,
            'primary_color' => '#4B00E8',
            'secondary_color' => '#8600EE',
            'sidebar_color' => '#4B00E8',
            'header_color' => '#ffffff',
        ];
    }

    /**
     * Sanitize color to ensure it is a valid hex color format.
     *
     * @param string|null $color
     * @param string $default
     * @return string
     */
    public static function sanitizeColor(?string $color, string $default): string
    {
        if (empty($color)) {
            return $default;
        }

        // Validate hex format: #RGB or #RRGGBB
        if (preg_match('/^#([a-fA-F0-9]{3}){1,2}$/', $color)) {
            return $color;
        }

        return $default;
    }

    /**
     * Get the cached branding settings, or retrieve them and cache.
     *
     * @return array
     */
    public static function cache(): array
    {
        return Cache::remember(self::CACHE_KEY, 86400, function () {
            return self::getRaw();
        });
    }

    /**
     * Clear the branding settings cache.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get branding settings (with fallbacks and sanitization).
     *
     * @return array
     */
    public static function get(): array
    {
        try {
            $settings = self::cache();
        } catch (\Throwable $e) {
            $settings = self::defaults();
        }

        $defaults = self::defaults();

        $companyName = !empty($settings['branding.company_name']) 
            ? $settings['branding.company_name'] 
            : $defaults['company_name'];

        $logoUrl = null;
        if (!empty($settings['branding.logo_path'])) {
            $logoPath = $settings['branding.logo_path'];
            if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
                $logoUrl = $logoPath;
            } else {
                $logoUrl = asset('storage/' . $logoPath);
            }
        }

        $faviconUrl = null;
        if (!empty($settings['branding.favicon_path'])) {
            $faviconPath = $settings['branding.favicon_path'];
            if (str_starts_with($faviconPath, 'http://') || str_starts_with($faviconPath, 'https://')) {
                $faviconUrl = $faviconPath;
            } else {
                $faviconUrl = asset('storage/' . $faviconPath);
            }
        }

        $primaryColor = self::sanitizeColor(
            $settings['branding.primary_color'] ?? null, 
            $defaults['primary_color']
        );

        $secondaryColor = self::sanitizeColor(
            $settings['branding.secondary_color'] ?? null, 
            $defaults['secondary_color']
        );

        $sidebarColor = self::sanitizeColor(
            $settings['branding.sidebar_color'] ?? null, 
            $defaults['sidebar_color']
        );

        $headerColor = self::sanitizeColor(
            $settings['branding.header_color'] ?? null, 
            $defaults['header_color']
        );

        return [
            'company_name' => $companyName,
            'logo_url' => $logoUrl,
            'favicon_url' => $faviconUrl,
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
            'sidebar_color' => $sidebarColor,
            'header_color' => $headerColor,
        ];
    }

    /**
     * Get raw settings from settings table.
     *
     * @return array
     */
    protected static function getRaw(): array
    {
        $defaults = self::defaults();

        // Safe check if DB table exists
        try {
            if (!Schema::hasTable('settings')) {
                return [];
            }

            return DB::table('settings')
                ->where('group', 'branding')
                ->pluck('value', 'key')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
