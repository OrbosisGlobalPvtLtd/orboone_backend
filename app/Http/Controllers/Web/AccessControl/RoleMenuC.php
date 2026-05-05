<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use App\Services\AccessControl\SidebarS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleMenuC extends Controller
{
    public function index()
    {
        $roles = DB::table('roles')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->paginate(15);

        return view('access_control.role_menus.index', compact('roles'));
    }

    public function edit($role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        if (! Schema::hasTable('role_menu_access')) {
            return back()->with('error', 'Role menu access table is missing.');
        }

        $menus = DB::table('menus')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('parent_id');

        $selectedMenuIds = DB::table('role_menu_access')
            ->where('role_id', $roleData->id)
            ->pluck('menu_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($roleData->slug === 'super_admin') {
            $selectedMenuIds = DB::table('menus')
                ->where('is_active', 1)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('access_control.role_menus.edit', [
            'role' => $roleData,
            'menus' => $menus,
            'selectedMenuIds' => $selectedMenuIds,
        ]);
    }

    public function update(Request $request, $role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        if (! Schema::hasTable('role_menu_access')) {
            return back()->with('error', 'Role menu access table is missing.');
        }

        $menuIds = collect($request->input('menu_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($roleData->slug === 'super_admin') {
            $menuIds = DB::table('menus')->where('is_active', 1)->pluck('id')->map(fn ($id) => (int) $id);
        }

        $parentMenuIds = DB::table('menus')
            ->whereIn('id', $menuIds)
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->map(fn ($id) => (int) $id);

        $menuIds = $menuIds->merge($parentMenuIds)->unique()->values();

        $validMenuIds = DB::table('menus')
            ->whereIn('id', $menuIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::transaction(function () use ($roleData, $validMenuIds) {
            DB::table('role_menu_access')->where('role_id', $roleData->id)->delete();

            $rows = collect($validMenuIds)->map(function ($menuId) use ($roleData) {
                return [
                    'role_id' => $roleData->id,
                    'menu_id' => $menuId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('role_menu_access')->insert($chunk);
            }
        });

        $userIds = DB::table('users')
            ->where('system_role_id', $roleData->id)
            ->pluck('id')
            ->merge(DB::table('user_roles')->where('role_id', $roleData->id)->pluck('user_id'))
            ->unique();

        $sidebarService = app(SidebarS::class);
        foreach ($userIds as $userId) {
            $sidebarService->clearCache($userId);
        }

        return redirect()
            ->route('role_menus.index')
            ->with('success', 'Role menu access updated successfully.');
    }

    private function findRole($role)
    {
        return DB::table('roles')->where('id', $role)->first();
    }
}
