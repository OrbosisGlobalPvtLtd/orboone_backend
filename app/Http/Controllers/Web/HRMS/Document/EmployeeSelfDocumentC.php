<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Services\HRMS\Employee\EmployeeProfileCompletionS;
use Illuminate\Support\Facades\Auth;

class EmployeeSelfDocumentC extends Controller
{
    private function getCurrentEmployee()
    {
        return EmployeeM::with(['user', 'profile'])->where('user_id', Auth::id())->first();
    }
    
    private function checkAccess($employee)
    {
        if (!$employee) {
            abort(404, 'Employee not found');
        }
        
        $status = app(EmployeeProfileCompletionS::class)->buildCompletionStatus($employee, $employee->profile);
        
        // Cannot edit if submitted or approved
        if (!$status['must_complete_profile']) {
            abort(403, 'Profile already submitted/approved. Documents cannot be modified.');
        }
    }

    public function upload(Request $request)
    {
        $employee = $this->getCurrentEmployee();
        $this->checkAccess($employee);
        
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'file' => 'required|file|max:5120'
        ]);
        
        $docType = DocumentTypeM::findOrFail($request->document_type_id);
        
        $file = $request->file('file');
        $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $path = $file->storeAs("employee_documents/{$employee->id}", $fileName, 'private');
        
        EmployeeDocumentM::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'document_type_id' => $docType->id,
            ],
            [
                'title' => $docType->name,
                'file_path' => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'file_mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'verification_status' => 'pending',
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'uploaded_by_user_id' => Auth::id(),
                'uploaded_at' => now(),
                'is_required' => $docType->is_mandatory
            ]
        );
        
        return response()->json(['success' => true, 'message' => 'Document uploaded successfully.']);
    }
    
    public function replace(Request $request, $id)
    {
        $employee = $this->getCurrentEmployee();
        $this->checkAccess($employee);
        
        $document = EmployeeDocumentM::where('employee_id', $employee->id)->findOrFail($id);
        
        $request->validate([
            'file' => 'required|file|max:5120'
        ]);
        
        $file = $request->file('file');
        $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $path = $file->storeAs("employee_documents/{$employee->id}", $fileName, 'private');
        
        $document->update([
            'file_path' => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'verification_status' => 'pending',
            'verified_by_user_id' => null,
            'verified_at' => null,
            'rejection_reason' => null,
            'uploaded_by_user_id' => Auth::id(),
            'uploaded_at' => now()
        ]);
        
        return response()->json(['success' => true, 'message' => 'Document replaced successfully.']);
    }
    
    public function destroy($id)
    {
        $employee = $this->getCurrentEmployee();
        $this->checkAccess($employee);
        
        $document = EmployeeDocumentM::where('employee_id', $employee->id)->findOrFail($id);
        
        if ($document->verification_status === 'verified') {
            return response()->json(['success' => false, 'message' => 'Cannot delete a verified document.'], 403);
        }
        
        $document->delete();
        
        return response()->json(['success' => true, 'message' => 'Document deleted successfully.']);
    }
}
