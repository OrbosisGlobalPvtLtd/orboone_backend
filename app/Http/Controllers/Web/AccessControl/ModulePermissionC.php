<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use App\Services\AccessControl\ModulePermissionS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModulePermissionC extends Controller
{
    public function __construct(private ModulePermissionS $modulePermissionService)
    {
    }

    public function index(Request $request)
    {
        $tab = strtolower((string) $request->input('tab', 'role'));
        if (! in_array($tab, ['role', 'user', 'profile'], true)) {
            $tab = 'role';
        }

        $roles = DB::table('roles')->orderByDesc('is_system')->orderBy('name')->get();
        $users = DB::table('users')->where('is_active', 1)->orderBy('name')->get(['id', 'name', 'email']);
        $departments = DB::table('departments')->orderBy('name')->get();

        $selectedRoleId = (int) $request->input('role_id', $roles->firstWhere('slug', 'admin')->id ?? $roles->first()->id ?? 1);
        $selectedUserId = (int) $request->input('user_id', $users->first()->id ?? 0);
        $selectedDepartmentId = (int) $request->input('department_id', $departments->first()->id ?? 0);

        $matrixData = [];

        if ($tab === 'role') {
            $matrixData = $this->modulePermissionService->getRoleMatrix($selectedRoleId);
        } elseif ($tab === 'user') {
            $matrixData = $this->modulePermissionService->getUserMatrix($selectedUserId);
        } elseif ($tab === 'profile') {
            $matrixData = $this->modulePermissionService->getProfileMatrix($selectedDepartmentId);
        }

        return view('access_control.module_permissions.index', array_merge($matrixData, [
            'tab' => $tab,
            'roles' => $roles,
            'users' => $users,
            'departments' => $departments,
            'selectedRoleId' => $selectedRoleId,
            'selectedUserId' => $selectedUserId,
            'selectedDepartmentId' => $selectedDepartmentId,
        ]));
    }

    public function updateRole(Request $request, $roleId)
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        abort_if(! $role, 404, 'Role not found.');

        $menuIds = (array) $request->input('menu_ids', []);
        $permissionIds = (array) $request->input('permission_ids', []);

        $this->modulePermissionService->saveRoleMatrix((int) $roleId, $menuIds, $permissionIds);

        return redirect()
            ->route('access_control.module_permissions.index', ['tab' => 'role', 'role_id' => $roleId])
            ->with('success', "Module CRUD permissions updated successfully for Role '{$role->name}'.");
    }

    public function updateUser(Request $request, $userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        abort_if(! $user, 404, 'User not found.');

        $grants = (array) $request->input('grants', []);
        $revokes = (array) $request->input('revokes', []);

        $this->modulePermissionService->saveUserMatrix((int) $userId, $grants, $revokes);

        return redirect()
            ->route('access_control.module_permissions.index', ['tab' => 'user', 'user_id' => $userId])
            ->with('success', "User custom permission overrides updated successfully for '{$user->name}'.");
    }

    public function updateProfile(Request $request, $departmentId)
    {
        $department = DB::table('departments')->where('id', $departmentId)->first();
        abort_if(! $department, 404, 'Profile/Department not found.');

        $permissionKeys = (array) $request->input('permission_keys', []);

        $this->modulePermissionService->saveProfileMatrix((int) $departmentId, $permissionKeys);

        return redirect()
            ->route('access_control.module_permissions.index', ['tab' => 'profile', 'department_id' => $departmentId])
            ->with('success', "Profile default module CRUD permissions updated successfully for '{$department->name}'.");
    }
}
