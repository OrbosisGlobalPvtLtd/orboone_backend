<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionC extends Controller
{
    public function index()
    {
        $roles = DB::table('roles')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->paginate(15);

        return view('access_control.role_permissions.index', compact('roles'));
    }

    public function edit($role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        $permissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('submodule')
            ->orderBy('key')
            ->get()
            ->groupBy(function ($permission) {
                return $permission->module ?: 'general';
            });

        $selectedPermissionIds = DB::table('role_permissions')
            ->where('role_id', $roleData->id)
            ->pluck('permission_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($roleData->slug === 'super_admin') {
            $selectedPermissionIds = DB::table('permissions')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('access_control.role_permissions.edit', [
            'role' => $roleData,
            'permissions' => $permissions,
            'selectedPermissionIds' => $selectedPermissionIds,
        ]);
    }

    public function update(Request $request, $role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        $permissionIds = collect($request->input('permission_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($roleData->slug === 'super_admin') {
            $permissionIds = DB::table('permissions')->pluck('id')->map(fn ($id) => (int) $id);
        }

        $validPermissionIds = DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::transaction(function () use ($roleData, $validPermissionIds) {
            DB::table('role_permissions')->where('role_id', $roleData->id)->delete();

            $rows = collect($validPermissionIds)->map(function ($permissionId) use ($roleData) {
                return [
                    'role_id' => $roleData->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('role_permissions')->insert($chunk);
            }
        });

        return redirect()
            ->route('role_permissions.index')
            ->with('success', 'Role permissions updated successfully.');
    }

    private function findRole($role)
    {
        return DB::table('roles')->where('id', $role)->first();
    }
}
