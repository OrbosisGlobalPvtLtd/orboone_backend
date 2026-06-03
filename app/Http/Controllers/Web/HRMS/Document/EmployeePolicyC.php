<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Document\CompanyDocumentM as CompanyDocumentModal;
use Illuminate\Http\Request;

class EmployeePolicyC extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $policies = CompanyDocumentModal::whereJsonContains('visible_to', 'employee')
            ->orWhereNull('visible_to')
            ->latest()
            ->get();

        $accesses = \App\Models\Core\AccessM::where('role_id', $user->role_id)->get();

        return view('hrms.documents.employee-policies.index', compact('policies', 'accesses'));
    }
}
