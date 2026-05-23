<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions') || ! Schema::hasTable('role_permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')->where('key', 'enterprise_payroll_run.reopen')->value('id');
        if (! $permissionId) {
            return;
        }

        $allowedRoleIds = DB::table('roles')->where('slug', 'super_admin')->pluck('id')->all();

        DB::table('role_permissions')
            ->where('permission_id', $permissionId)
            ->when(! empty($allowedRoleIds), fn ($query) => $query->whereNotIn('role_id', $allowedRoleIds))
            ->delete();
    }

    public function down(): void
    {
        // Intentionally not re-granting reopen access to non-Super Admin roles.
    }
};
