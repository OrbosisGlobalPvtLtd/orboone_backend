<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AccessControl\StoreRoleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleC extends Controller
{
    public function index()
    {
        $roles = DB::table('roles')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->paginate(15);

        return view('access_control.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('access_control.roles.create');
    }

    public function store(StoreRoleRequest $request)
    {
        $slug = $this->normalizeSlug($request->slug ?: $request->name);
        if ($this->slugExists($slug)) {
            return back()->withErrors(['slug' => 'The role code has already been taken.'])->withInput();
        }

        DB::table('roles')->insert([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_system' => $request->boolean('is_system'),
            'status' => $request->boolean('status', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show($role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        return redirect()->route('roles.edit', $roleData->id);
    }

    public function edit($role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        return view('access_control.roles.edit', ['role' => $roleData]);
    }

    public function update(StoreRoleRequest $request, $role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        $slug = $this->normalizeSlug($request->slug ?: $request->name);
        if ($this->slugExists($slug, (int) $roleData->id)) {
            return back()->withErrors(['slug' => 'The role code has already been taken.'])->withInput();
        }

        DB::table('roles')->where('id', $roleData->id)->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_system' => $request->boolean('is_system'),
            'status' => $request->boolean('status'),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy($role)
    {
        $roleData = $this->findRole($role);
        abort_if(! $roleData, 404);

        if ($roleData->is_system || in_array($roleData->slug, ['super_admin'], true)) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        $assignedUsers = DB::table('users')
            ->where('system_role_id', $roleData->id)
            ->count();

        $assignedPivotUsers = DB::table('user_roles')
            ->where('role_id', $roleData->id)
            ->count();

        if (($assignedUsers + $assignedPivotUsers) > 0) {
            return back()->with('error', 'This role is assigned to users and cannot be deleted.');
        }

        DB::transaction(function () use ($roleData) {
            DB::table('role_permissions')->where('role_id', $roleData->id)->delete();

            if (DB::getSchemaBuilder()->hasTable('role_menu_access')) {
                DB::table('role_menu_access')->where('role_id', $roleData->id)->delete();
            }

            DB::table('roles')->where('id', $roleData->id)->delete();
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function print()
    {
        return redirect()->route('roles.index');
    }

    private function findRole($role)
    {
        return DB::table('roles')->where('id', $role)->first();
    }

    private function normalizeSlug(string $value): string
    {
        return Str::slug($value, '_');
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return DB::table('roles')
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
