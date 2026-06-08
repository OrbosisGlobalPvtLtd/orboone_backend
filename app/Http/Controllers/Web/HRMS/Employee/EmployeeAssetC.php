<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\AssetAllocationM as AssetAllocation;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeAssetC extends Controller
{
    /**
     * Display a listing of the assets assigned to the logged-in employee.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if (!$employee) {
            abort(404, 'Employee record not found.');
        }

        $assets = AssetAllocation::where('employee_id', $employee->id)
            ->latest('issue_date')
            ->paginate(15);

        return view('hrms.employee.assets.index', compact('assets', 'employee'));
    }
}
