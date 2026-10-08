<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use App\Services\AccessControl\RbacPolicyMatrixS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RbacPolicyMatrixC extends Controller
{
    public function __construct(private RbacPolicyMatrixS $matrixService)
    {
    }

    public function index(Request $request)
    {
        $roles = DB::table('roles')->orderByDesc('is_system')->orderBy('name')->get();
        $selectedRoleId = (int) $request->input('role_id', $roles->firstWhere('slug', 'admin')->id ?? $roles->first()->id ?? 1);

        $matrix = $this->matrixService->getMatrixForRole($selectedRoleId);

        return view('access_control.policy_matrix.index', array_merge($matrix, [
            'selectedRoleId' => $selectedRoleId,
            'roles' => $roles,
        ]));
    }

    public function update(Request $request, $roleId)
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        abort_if(! $role, 404, 'Role not found.');

        $menuIds = $request->input('menu_ids', []);
        $permissionIds = $request->input('permission_ids', []);

        $this->matrixService->syncRolePolicyMatrix((int) $roleId, $menuIds, $permissionIds);

        return redirect()
            ->route('access_control.policy_matrix.index', ['role_id' => $roleId])
            ->with('success', "RBAC policy matrix & sidebar visibility updated successfully for role '{$role->name}'.");
    }
}
