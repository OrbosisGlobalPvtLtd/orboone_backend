<?php

namespace App\Http\Controllers\Api\V1\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Services\HRMS\Leave\LeaveApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveApprovalApiC extends Controller
{
    public function __construct(private LeaveApprovalService $approvalService)
    {
    }

    public function pending(Request $request)
    {
        $requests = LeaveRequestM::with(['employee.user', 'leaveType', 'dates'])
            ->where('status', 'pending')
            ->when($request->employee_id, fn ($query) => $query->where('employee_id', $request->employee_id))
            ->latest()
            ->paginate((int) ($request->per_page ?: 20));

        return $this->ok('Pending leave requests fetched successfully.', $requests);
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:2000']);

        try {
            $leaveRequest = $this->approvalService->approve(LeaveRequestM::findOrFail($id), auth()->id(), $request->note);

            return $this->ok('Leave request approved successfully.', $leaveRequest);
        } catch (\Throwable $e) {
            Log::error('API leave approve failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 422);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:2000']);

        try {
            $leaveRequest = $this->approvalService->reject(LeaveRequestM::findOrFail($id), auth()->id(), $request->reason);

            return $this->ok('Leave request rejected successfully.', $leaveRequest);
        } catch (\Throwable $e) {
            Log::error('API leave reject failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 422);
        }
    }

    private function ok(string $message, $data = [], int $code = 200)
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, int $code = 400, $data = [])
    {
        return response()->json(['status' => false, 'message' => $message, 'data' => $data], $code);
    }
}
