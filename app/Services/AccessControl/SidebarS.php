<?php

namespace App\Services\AccessControl;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SidebarS
{
    public function getMenus($user)
    {
        if (!$user) {
            return [];
        }

        return Cache::remember(
            'sidebar_user_' . $user->id,
            60 * 60,
            function () use ($user) {

                $roleIds = $user->roles()->pluck('roles.id')->toArray();

                if ($user->system_role_id) {
                    $roleIds[] = $user->system_role_id;
                }

                $roleIds = array_unique($roleIds);

                if (empty($roleIds)) {
                    return $this->activeMenus();
                }

                if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                    return $this->activeMenus();
                }

                if (! Schema::hasTable('role_menu_access')) {
                    return $this->activeMenus();
                }

                $hasMenuRows = DB::table('role_menu_access')
                    ->whereIn('role_id', $roleIds)
                    ->exists();

                if (! $hasMenuRows) {
                    return $this->activeMenus();
                }

                return DB::table('menus')
                    ->join('role_menu_access', 'menus.id', '=', 'role_menu_access.menu_id')
                    ->whereIn('role_menu_access.role_id', $roleIds)
                    ->where('menus.is_active', 1)
                    ->orderBy('menus.parent_id')
                    ->orderBy('menus.sort_order')
                    ->select('menus.*')
                    ->distinct()
                    ->get()
                    ->groupBy('parent_id');
            }
        );
    }

    public function clearCache($userId)
    {
        Cache::forget('sidebar_user_' . $userId);
    }

    private function activeMenus()
    {
        return DB::table('menus')
            ->where('is_active', 1)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('parent_id');
    }
}
