<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OrganizationC extends Controller
{
    public function index()
    {
        $departments = DB::table('departments')->get();

        $designations = DB::table('designations')
            ->leftJoin('departments', 'departments.id', '=', 'designations.department_id')
            ->select('designations.*', 'departments.name as department_name')
            ->get();

        return view('hrms.organization.index', compact('departments', 'designations'));
    }
}