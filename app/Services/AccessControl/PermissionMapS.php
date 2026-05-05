<?php

namespace App\Services\AccessControl;

class PermissionMapS
{
    public function roles(): array
    {
        return config('hrms_access.roles.system', []);
    }

    public function modules(): array
    {
        return config('hrms_access.modules', []);
    }

    public function permissionsByModule(): array
    {
        return config('hrms_access.permissions', []);
    }

    public function rolePermissionTemplates(): array
    {
        return config('hrms_access.role_permission_templates', []);
    }
}
