<?php

namespace App\Services\AccessControl;

use App\Services\AccessControl\SidebarS;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionSyncService
{
    /**
     * Synchronize role_permissions for a given role based on selected menu IDs.
     */
    public function syncRolePermissionsFromMenus(int $roleId, array $menuIds): array
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        if (! $role) {
            return [];
        }

        // If super_admin, grant 100% permissions
        if ($role->slug === 'super_admin') {
            $permissionIds = DB::table('permissions')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        } else {
            $permissionIds = $this->derivePermissionsForMenuIds($menuIds);
        }

        // Atomic synchronization inside existing transaction
        DB::table('role_permissions')->where('role_id', $roleId)->delete();

        if (! empty($permissionIds)) {
            $rows = collect($permissionIds)->map(function ($permissionId) use ($roleId) {
                return [
                    'role_id' => $roleId,
                    'permission_id' => (int) $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('role_permissions')->insert($chunk);
            }
        }

        // Clear all caches for users holding this role
        $this->clearRoleAndUserCaches($roleId);

        return $permissionIds;
    }

    /**
     * Dynamically derive permission IDs for selected menu IDs without hardcoding.
     */
    public function derivePermissionsForMenuIds(array $menuIds): array
    {
        if (empty($menuIds) || ! Schema::hasTable('menus') || ! Schema::hasTable('permissions')) {
            return [];
        }

        $menus = DB::table('menus')->whereIn('id', $menuIds)->get();
        if ($menus->isEmpty()) {
            return [];
        }

        $tokens = [];
        foreach ($menus as $m) {
            // 1. module_key tokens
            if (! empty($m->module_key)) {
                $modKey = strtolower(trim($m->module_key));
                $tokens[] = $modKey;
                foreach (explode('.', $modKey) as $part) {
                    if (strlen($part) > 2) {
                        $tokens[] = $part;
                    }
                }
                foreach (explode('_', $modKey) as $part) {
                    if (strlen($part) > 2) {
                        $tokens[] = $part;
                    }
                }
            }

            // 2. route tokens
            if (! empty($m->route)) {
                $routeParts = explode('.', strtolower(trim($m->route)));
                foreach ($routeParts as $part) {
                    $partClean = str_replace(['-', '_'], '', $part);
                    if (! in_array($part, ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'delete', 'hrms', 'my', 'hr', 'record']) && strlen($part) > 2) {
                        $tokens[] = str_replace('-', '_', $part);
                        $tokens[] = $partClean;
                    }
                }
            }

            // 3. name tokens
            $nameClean = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $m->name));
            $words = explode(' ', $nameClean);
            foreach ($words as $w) {
                if (in_array($w, ['management', 'system', 'request', 'requests', 'tracking', 'time', 'list', 'summary', 'report', 'reports', 'rules', 'types', 'my', 'overview', 'details'])) {
                    continue;
                }
                if (strlen($w) > 2) {
                    $tokens[] = $w;
                }
            }
        }

        // Add singular / plural aliases
        $expanded = [];
        foreach ($tokens as $t) {
            $expanded[] = $t;
            if (str_ends_with($t, 's')) {
                $expanded[] = substr($t, 0, -1);
            } else {
                $expanded[] = $t . 's';
                $expanded[] = $t . 'es';
            }
        }

        $uniqueTokens = array_values(array_unique(array_filter($expanded)));
        if (empty($uniqueTokens)) {
            return [];
        }

        // Search permissions table dynamically
        $allPermissions = DB::table('permissions')->get();
        $matchedIds = [];

        foreach ($allPermissions as $p) {
            $key = strtolower($p->key);
            $submodule = strtolower($p->submodule ?? '');
            $module = strtolower($p->module ?? '');

            $keyParts = explode('.', $key);
            $keyPrefix = $keyParts[0] ?? '';
            $keyPrefix2 = count($keyParts) > 1 ? $keyParts[0] . '.' . $keyParts[1] : $keyPrefix;

            foreach ($uniqueTokens as $token) {
                if (
                    $keyPrefix === $token ||
                    $keyPrefix2 === $token ||
                    str_starts_with($key, $token . '.') ||
                    str_starts_with($key, $token . '_') ||
                    $submodule === $token ||
                    str_contains($submodule, $token) ||
                    ($module === $token && $module !== 'hrms' && $module !== 'core')
                ) {
                    $matchedIds[] = (int) $p->id;
                    break;
                }
            }
        }

        return array_values(array_unique($matchedIds));
    }

    /**
     * Clear all authorization, role, and navigation caches for users with the role.
     */
    public function clearRoleAndUserCaches(int $roleId): void
    {
        $userIds = DB::table('users')
            ->where('system_role_id', $roleId)
            ->pluck('id')
            ->merge(DB::table('user_roles')->where('role_id', $roleId)->pluck('user_id'))
            ->unique()
            ->all();

        // 1. Clear Spatie / App Permission Cache
        try {
            if (app()->bound('cache')) {
                app('cache')->forget('spatie.permission.cache');
                app('cache')->forget('app_permissions_cache');
            }
        } catch (\Throwable $e) {
            // Ignore cache store driver exceptions
        }

        // 2. Clear Sidebar & User Cache
        $sidebarService = app(SidebarS::class);
        foreach ($userIds as $userId) {
            try {
                $sidebarService->clearCache($userId);
                Cache::forget('user_permissions_' . $userId);
                Cache::forget('user_menus_' . $userId);
            } catch (\Throwable $e) {
                // Ignore individual cache clear errors
            }
        }
    }
}
