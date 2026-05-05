<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AccessControl\StorePermissionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionC extends Controller
{
    public function index()
    {
        $permissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('submodule')
            ->orderBy('key')
            ->paginate(25);

        return view('access_control.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('access_control.permissions.create', [
            'hasIsActive' => Schema::hasColumn('permissions', 'is_active'),
        ]);
    }

    public function store(StorePermissionRequest $request)
    {
        $data = $this->permissionPayload($request);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('permissions')->insert($data);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit($permission)
    {
        $permissionData = $this->findPermission($permission);
        abort_if(! $permissionData, 404);

        return view('access_control.permissions.edit', [
            'permission' => $permissionData,
            'hasIsActive' => Schema::hasColumn('permissions', 'is_active'),
        ]);
    }

    public function update(StorePermissionRequest $request, $permission)
    {
        $permissionData = $this->findPermission($permission);
        abort_if(! $permissionData, 404);

        $data = $this->permissionPayload($request);
        $data['updated_at'] = now();

        DB::table('permissions')->where('id', $permissionData->id)->update($data);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy($permission)
    {
        $permissionData = $this->findPermission($permission);
        abort_if(! $permissionData, 404);

        DB::transaction(function () use ($permissionData) {
            DB::table('role_permissions')->where('permission_id', $permissionData->id)->delete();
            DB::table('permissions')->where('id', $permissionData->id)->delete();
        });

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    private function findPermission($permission)
    {
        return DB::table('permissions')->where('id', $permission)->first();
    }

    private function permissionPayload(StorePermissionRequest $request): array
    {
        $payload = [
            'module' => strtolower($request->module_key),
            'submodule' => $request->submodule,
            'action' => $request->name,
            'key' => strtolower($request->permission_key),
            'description' => $request->description,
        ];

        if (Schema::hasColumn('permissions', 'is_active')) {
            $payload['is_active'] = $request->boolean('is_active', true);
        }

        return $payload;
    }
}
