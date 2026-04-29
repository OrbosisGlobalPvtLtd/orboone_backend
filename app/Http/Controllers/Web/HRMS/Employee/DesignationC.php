<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DesignationC extends Controller
{
    private function getDepartmentShortCode($department): string
    {
        $name = strtolower($department->name ?? '');

        $map = [
            'engineering' => 'ENG',
            'web' => 'WEB',
            'mobile' => 'APP',
            'quality' => 'QA',
            'ui' => 'DES',
            'ux' => 'DES',
            'design' => 'DES',
            'human' => 'HR',
            'hr' => 'HR',
            'finance' => 'FIN',
            'accounts' => 'ACC',
            'sales' => 'SAL',
            'business' => 'BD',
            'marketing' => 'MKT',
            'product' => 'PROD',
            'project' => 'PM',
            'devops' => 'DEV',
            'operations' => 'OPS',
        ];

        foreach ($map as $keyword => $prefix) {
            if (str_contains($name, $keyword)) {
                return $prefix;
            }
        }

        $clean = preg_replace('/[^A-Za-z]/', '', $department->name ?? 'DES');

        return strtoupper(substr($clean ?: 'DES', 0, 3));
    }

    private function generateDesignationCode(int $departmentId): string
    {
        $department = DB::table('departments')->where('id', $departmentId)->first();
        abort_if(!$department, 404);

        $prefix = $this->getDepartmentShortCode($department);

        $lastCode = DB::table('designations')
            ->where('department_id', $departmentId)
            ->where('code', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->value('code');

        $next = 1;

        if ($lastCode) {
            $next = ((int) substr($lastCode, strrpos($lastCode, '-') + 1)) + 1;
        }

        return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        DB::table('designations')->insert([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'code' => $this->generateDesignationCode((int) $request->department_id),
            'description' => $request->description,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Designation added successfully.');
    }

    public function update(Request $request, $id)
    {
        $designation = DB::table('designations')->where('id', $id)->first();
        abort_if(!$designation, 404);

        $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'in:0,1'],
        ]);

        $data = [
            'department_id' => $request->department_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ];

        if ((int) $designation->department_id !== (int) $request->department_id) {
            $data['code'] = $this->generateDesignationCode((int) $request->department_id);
        }

        DB::table('designations')->where('id', $id)->update($data);

        return back()->with('success', 'Designation updated successfully.');
    }

    public function destroy($id)
    {
        $hasEmployees = DB::table('employees_new')
            ->where('designation_id', $id)
            ->exists();

        if ($hasEmployees) {
            return back()->with('error', 'Designation cannot be deleted because it is already assigned to employees.');
        }

        DB::table('designations')->where('id', $id)->delete();

        return back()->with('success', 'Designation deleted successfully.');
    }

    public function getByDepartment($departmentId)
    {
        $designations = DB::table('designations')
            ->where('department_id', $departmentId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return response()->json($designations);
    }
}