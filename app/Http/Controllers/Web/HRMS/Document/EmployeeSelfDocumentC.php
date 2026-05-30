<?php

namespace App\Http\Controllers\Web\HRMS\Document;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Document\EmployeeDocumentM;
use App\Models\HRMS\Document\DocumentTypeM;
use App\Services\HRMS\Employee\EmployeeProfileCompletionS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use Illuminate\Support\Facades\Auth;

class EmployeeSelfDocumentC extends Controller
{
    public function __construct(private HrmsStoragePathS $paths)
    {
    }

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
        $storageService = app(\App\Services\HRMS\Document\HrmsFileStorageS::class);
        $meta = $storageService->archiveOrReplaceEmployeeDocument($employee, $docType, $file);
        
        $search = [
            'employee_id' => $employee->id,
            'document_type_id' => $docType->id,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('employee_documents_new', 'is_active')) {
            $search['is_active'] = 1;
        }

        EmployeeDocumentM::updateOrCreate(
            $search,
            [
                'title' => $docType->name,
                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'verification_status' => 'pending',
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'uploaded_by_user_id' => Auth::id(),
                'uploaded_at' => now(),
                'is_required' => $docType->is_mandatory,
                'is_active' => true
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
        $docType = DocumentTypeM::findOrFail($document->document_type_id);
        $storageService = app(\App\Services\HRMS\Document\HrmsFileStorageS::class);
        $meta = $storageService->archiveOrReplaceEmployeeDocument($employee, $docType, $file);
        
        if ($document->verification_status === 'verified') {
            EmployeeDocumentM::create([
                'employee_id' => $employee->id,
                'document_type_id' => $document->document_type_id,
                'title' => $document->title,
                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'verification_status' => 'pending',
                'uploaded_by_user_id' => Auth::id(),
                'expiry_date' => $document->expiry_date,
                'is_required' => $document->is_required,
                'uploaded_at' => now(),
                'is_active' => true,
            ]);
        } else {
            $document->update([
                'file_path' => $meta['file_path'],
                'file_original_name' => $meta['original_name'],
                'file_mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'verification_status' => 'pending',
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'uploaded_by_user_id' => Auth::id(),
                'uploaded_at' => now()
            ]);
        }
        
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
