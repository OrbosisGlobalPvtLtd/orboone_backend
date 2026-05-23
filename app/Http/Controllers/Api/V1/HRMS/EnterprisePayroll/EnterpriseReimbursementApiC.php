<?php

namespace App\Http\Controllers\Api\V1\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollApiS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnterpriseReimbursementApiC extends Controller
{
    public function __construct(private EnterprisePayrollApiS $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $employee = $this->service->employeeForUser($request->user()?->id);
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, null, 404);
        }

        $rows = EnterpriseReimbursementM::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($item) => $this->service->reimbursementItem($item))
            ->values();

        return $this->apiResponse(true, 'Reimbursements fetched successfully.', $rows);
    }

    public function store(Request $request): JsonResponse
    {
        $employee = $this->service->employeeForUser($request->user()?->id);
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, null, 404);
        }

        try {
            $data = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'claim_date' => ['required', 'date'],
                'amount' => ['required', 'numeric', 'gt:0'],
                'attachment' => ['nullable', 'file', 'max:5120'],
                'remarks' => ['nullable', 'string'],
            ]);
        } catch (ValidationException $exception) {
            return $this->apiResponse(false, 'Validation failed.', null, $exception->errors(), 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('enterprise-reimbursements', 'public');
        }

        $reimbursement = EnterpriseReimbursementM::create([
            'employee_id' => $employee->id,
            'title' => $data['title'],
            'claim_date' => $data['claim_date'],
            'amount' => $data['amount'],
            'approved_amount' => 0,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'remarks' => $data['remarks'] ?? null,
        ]);

        return $this->apiResponse(
            true,
            'Reimbursement submitted successfully.',
            $this->service->reimbursementItem($reimbursement),
            null,
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $employee = $this->service->employeeForUser($request->user()?->id);
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, null, 404);
        }

        $reimbursement = EnterpriseReimbursementM::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (! $reimbursement) {
            return $this->apiResponse(false, 'Reimbursement not found.', null, null, 404);
        }

        return $this->apiResponse(true, 'Reimbursement details fetched successfully.', $this->service->reimbursementItem($reimbursement));
    }

    private function apiResponse(bool $success, string $message, mixed $data = null, mixed $errors = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }
}
