<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Document\DocumentTypeM as DocumentTypeModal;
use App\Models\HRMS\Document\EmployeeDocumentM as EmployeeDocument;
use App\Models\HRMS\Document\EmployeeDocumentM as EmployeeDocumentModal;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use App\Services\Shared\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    protected $attendanceService;
    private HrmsStoragePathS $paths;
    private HrmsFileResolverS $resolver;

    public function __construct(
        AttendanceService $attendanceService,
        HrmsStoragePathS $paths,
        HrmsFileResolverS $resolver
    ) {
        $this->attendanceService = $attendanceService;
        $this->paths = $paths;
        $this->resolver = $resolver;
    }

    // ============================================================
    // EMPLOYEE DOCUMENTS
    // ============================================================
    public function uploadEmployeeDocument(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expiry_date'      => 'nullable|date'
        ]);

        $type = DocumentTypeModal::findOrFail($request->document_type_id);

        $employee = Employee::where('user_id', $user->id)->first();
        $basePath = $employee
            ? $this->paths->mapEmployeeDocumentType($employee->id, $this->paths->normalizeDocType((string) ($type->code ?: $type->name)))
            : 'hrms/company/templates/document-generation';
        $path = $request->file('file')->storeAs(
            $basePath,
            'doc_' . time() . '_' . uniqid() . '.' . $request->file('file')->getClientOriginalExtension(),
            'private'
        );

        $document = EmployeeDocumentModal::updateOrCreate(
            [
                'user_id'          => $user->id,
                'document_type_id' => $type->id,
            ],
            [
                'file_path'   => $path,
                'expiry_date' => $type->has_expiry ? $request->expiry_date : null,
                'status'      => 'verified',
                'uploaded_by' => Auth::id(),
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded successfully',
            'data'    => $document->load('type')
        ], 200);
    }

    public function listEmployeeDocuments($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $docs = EmployeeDocumentModal::with('type')
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($docs);
    }

    public function verifyEmployeeDocument(Request $request, $id, $docId)
    {
        $request->validate([
            'status'           => 'required|in:verified,rejected',
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $doc = EmployeeDocumentModal::where('user_id', $id)->find($docId);
        if (!$doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $doc->status = $request->status;
        $doc->rejection_reason = $request->status === 'rejected'
            ? $request->rejection_reason
            : null;

        $doc->save();

        return response()->json([
            'message' => 'Document status updated',
            'data'    => $doc
        ]);
    }

    public function deleteEmployeeDocument($id, $docId)
    {
        $doc = EmployeeDocumentModal::where('user_id', $id)->find($docId);
        if (!$doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        if ($doc->status === 'verified') {
            return response()->json([
                'message' => 'Verified document cannot be deleted'
            ], 403);
        }

        Storage::disk('private')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['message' => 'Document deleted']);
    }

    // ============================================================
    // COMPANY POLICY DOCUMENTS
    // ============================================================
    public function listPolicyDocuments()
    {
        $docs = EmployeeDocument::whereNull('employee_id')
            ->where('category', 'policy')
            ->get();

        return response()->json($docs);
    }

    public function uploadPolicyDocument(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('file')->storeAs(
            $this->paths->companyPolicy('general'),
            'policy_' . time() . '_' . uniqid() . '.' . $request->file('file')->getClientOriginalExtension(),
            'private'
        );

        $doc = EmployeeDocument::create([
            'employee_id' => null,
            'category'    => 'policy',
            'title'       => $request->title,
            'file_path'   => $path,
            'status'      => 'active',
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Policy document uploaded',
            'data'    => $doc,
        ], 201);
    }

    // ============================================================
    // ATTENDANCE CONFIGURATION
    // ============================================================
    public function configureGeofence(Request $request)
    {
        $request->validate([
            'office_name' => 'required|string',
            'latitude'    => 'required',
            'longitude'   => 'required',
            'radius'      => 'required|numeric',
        ]);

        return response()->json([
            'message' => 'Geofence config stub – persist to geofence settings table',
        ]);
    }

    public function configureAllowedIps(Request $request)
    {
        $request->validate([
            'ips' => 'required|array',
        ]);

        return response()->json([
            'message' => 'Allowed IPs config stub – persist to config table',
        ]);
    }
}
