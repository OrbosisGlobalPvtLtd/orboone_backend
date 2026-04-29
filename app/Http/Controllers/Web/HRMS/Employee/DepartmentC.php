<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentC extends Controller
{
    private function generateDepartmentCode(): string
    {
        $prefix = 'DEP-';

        $lastCode = DB::table('departments')
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('code');

        $next = 1;

        if ($lastCode) {
            $next = ((int) substr($lastCode, strrpos($lastCode, '-') + 1)) + 1;
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        DB::table('departments')->insert([
            'name' => $request->name,
            'code' => $this->generateDepartmentCode(),
            'address' => $request->address,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Department added successfully.');
    }

    public function update(Request $request, $id)
    {
        $department = DB::table('departments')->where('id', $id)->first();
        abort_if(!$department, 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        DB::table('departments')->where('id', $id)->update([
            'name' => $request->name,
            'address' => $request->address,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroy($id)
    {
        $hasEmployees = DB::table('employees_new')
            ->where('department_id', $id)
            ->exists();

        $hasDesignations = DB::table('designations')
            ->where('department_id', $id)
            ->exists();

        if ($hasEmployees || $hasDesignations) {
            return back()->with('error', 'Department cannot be deleted because it is already used.');
        }

        DB::table('departments')->where('id', $id)->delete();

        return back()->with('success', 'Department deleted successfully.');
    }
}