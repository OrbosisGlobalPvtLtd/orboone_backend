<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocumentModal;
use Illuminate\Http\Request;

class EmployeePolicyController extends Controller
{
    public function indexx()
    {
        $user = auth()->user();
        $policies = CompanyDocumentModal::whereJsonContains('visible_to', 'employee')
            ->orWhereNull('visible_to')
            ->latest()
            ->get();

        $accesses = \App\Models\Access::where('role_id', $user->role_id)->get();

        return view('employee.policies-index', compact('policies', 'accesses'));
    }
}
