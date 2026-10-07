<?php

namespace App\Services\AccessControl;

use App\Services\Core\Menu\SidebarMenuResolverS;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SidebarS
{
    public function __construct(private SidebarMenuResolverS $resolver)
    {
    }

    public function getMenus($user)
    {
        return $this->resolver->resolveForUser($user);
    }

    public function clearCache($userId): void
    {
        try {
            $this->resolver->clearCache((int) $userId);
        } catch (\Throwable $e) {
            Log::warning("Failed to clear sidebar cache for user {$userId}: " . $e->getMessage());
        }
    }

    public function clearUserCaches(array|int $userIds): void
    {
        $ids = is_array($userIds) ? $userIds : [$userIds];
        foreach ($ids as $userId) {
            if ($userId) {
                $this->clearCache((int) $userId);
            }
        }
    }

    public function clearRoleCaches(int $roleId): void
    {
        try {
            $userIds = DB::table('users')
                ->where('system_role_id', $roleId)
                ->pluck('id');

            if (Schema::hasTable('user_roles')) {
                $userIds = $userIds->merge(
                    DB::table('user_roles')->where('role_id', $roleId)->pluck('user_id')
                );
            }

            $uniqueUserIds = $userIds->unique()->filter()->all();

            $this->clearUserCaches($uniqueUserIds);
        } catch (\Throwable $e) {
            Log::warning("Failed to clear role caches for role {$roleId}: " . $e->getMessage());
        }
    }
}
