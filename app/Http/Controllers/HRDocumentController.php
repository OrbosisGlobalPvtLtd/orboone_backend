<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\EmployeeDocumentModal;
use App\Models\DocumentTypeModal;

class HRDocumentController extends Controller
{
    public function index()
    {
        // Fetch all users with employee profile and their documents
        $employees = User::whereHas('employee')
            ->with(['employee.department', 'documents.type'])
            ->get();
        
        // Fetch document types for compliance tracking
        $types = DocumentTypeModal::where('scope', 'employee')->get();
        
        // Access for layout
        $accesses = \App\Models\Access::where('role_id', auth()->user()->role_id)->get();
        
        return view('pages.hr.documents-all', compact('employees', 'types', 'accesses'));
    }

    public function show(User $user)
    {
        $user->load(['employee.department', 'employee.position']);
        
        // Fetch all document records for this user
        $documents = EmployeeDocumentModal::where('user_id', $user->id)
            ->with('type')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $types = DocumentTypeModal::where('scope', 'employee')->get();
        $accesses = \App\Models\Access::where('role_id', auth()->user()->role_id)->get();
        
        // Define the columns we want to check in the UI
        $documentColumns = [
            'aadhar_card'           => 'Aadhar Card',
            'pan_card'              => 'PAN Card',
            'bank_proof'            => 'Bank Proof',
            'passport_photo'        => 'Passport Photo',
            'educational_documents' => 'Educational Documents',
            'offer_letter'          => 'Offer Letter',
            'salary_slip_3_months'  => 'Salary Slip (3 Months)',
            'experience_letter'     => 'Experience Letter',
            'relieving_letter'      => 'Relieving Letter',
            'nda_agreement_mou'     => 'NDA Agreement / MOU',
        ];

        return view('employee.hr-documents', compact('user', 'documents', 'types', 'accesses', 'documentColumns'));
    }

    public function approve($id)
    {
        EmployeeDocumentModal::findOrFail($id)->update([
            'status'=>'verified'
        ]);

        return back()->with('success','Document approved');
    }

    

    public function reject(Request $request, $id)
    {
        $request->validate(['reason'=>'required']);

        EmployeeDocumentModal::findOrFail($id)->update([
            'status'=>'rejected',
            'rejection_reason'=>$request->reason
        ]);

        return back()->with('error','Document rejected');
    }
}
