<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocumentModal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get employees with their documents from EmployeeDocumentModal
        $employees = Employee::withCount(['documents'])
        ->orderBy('id', 'desc')
        ->paginate(15);

        $allEmployees = Employee::orderBy('name')->get();
        $documentTypes = \App\Models\DocumentTypeModal::where('scope', 'employee')->get();

        return view('pages.employee_documents.index', compact('employees', 'allEmployees', 'documentTypes'));
    }

    public function storeGlobal(Request $request)
    {
        $request->validate([
            'employee_id'           => 'required|exists:employees,id',
            'document_type_id'      => 'required|exists:document_types,id',
            'aadhar_card'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pan_card'              => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'bank_proof'            => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'passport_photo'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'educational_documents' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        return $this->store($request, $employee);
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'position', 'user']);
        
        // Use EmployeeDocumentModal instead of EmployeeDocument
        $documents = EmployeeDocumentModal::where('employee_id', $employee->id)
            ->with('type')
            ->orderBy('id', 'desc')
            ->get();
            
        $documentTypes = \App\Models\DocumentTypeModal::where('scope', 'employee')->get();

        return view('pages.employee_documents.show', compact('employee', 'documents', 'documentTypes'));
    }

    public function store(Request $request, Employee $employee)
    {
        $rules = [
            'document_type_id'      => 'required|exists:document_types,id',
            // Mandatory Documents
            'aadhar_card'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pan_card'              => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'bank_proof'            => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'passport_photo'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'educational_documents' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Optional Documents
            'offer_letter'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'salary_slip_3_months'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'experience_letter'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'relieving_letter'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nda_agreement_mou'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        $request->validate($rules);

        $user = $employee->user;
        if (!$user) {
            return back()->with('error', 'Employee does not have an associated user account.');
        }

        $type = \App\Models\DocumentTypeModal::findOrFail($request->document_type_id);

        $upload = function ($key) use ($request, $employee) {
            if ($request->hasFile($key)) {
                $fileName = $key . '_' . time() . '.' . $request->file($key)->extension();
                return $request->file($key)->storeAs('employee_documents/' . $employee->id, $fileName, 'public');
            }
            return null;
        };

        EmployeeDocumentModal::updateOrCreate(
            ['employee_id' => $employee->id, 'document_type_id' => $type->id],
            [
                'document_type'         => $type->name,
                'passport_photo'        => $upload('passport_photo'),
                'aadhar_card'           => $upload('aadhar_card'),
                'pan_card'              => $upload('pan_card'),
                'bank_proof'            => $upload('bank_proof'),
                'educational_documents' => $upload('educational_documents'),
                'offer_letter'          => $upload('offer_letter'),
                'experience_letter'     => $upload('experience_letter'),
                'salary_slip_3_months'  => $upload('salary_slip_3_months'),
                'relieving_letter'      => $upload('relieving_letter'),
                'nda_agreement_mou'     => $upload('nda_agreement_mou'),
                'status'                => 'pending',
                'uploaded_by'           => auth()->id(),
            ]
        );

        return back()->with('success', 'Documents uploaded successfully.');
    }

    public function approve($id)
    {
        $document = EmployeeDocumentModal::findOrFail($id);
        $document->update([
            'status' => 'verified',
        ]);

        return back()->with('success', 'Document verified successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $document = EmployeeDocumentModal::findOrFail($id);
        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Document rejected.');
    }
}

