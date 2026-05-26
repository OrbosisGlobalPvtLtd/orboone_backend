<?php

namespace App\Services\AccessControl;

use App\Services\Core\Menu\SidebarMenuResolverS;

class SidebarS
{
    public function __construct(private SidebarMenuResolverS $resolver)
    {
    }

    public function getMenus($user)
    {
        return $this->resolver->resolveForUser($user);
    }

    public function clearCache($userId)
    {
        $this->resolver->clearCache((int) $userId);
    }
}
