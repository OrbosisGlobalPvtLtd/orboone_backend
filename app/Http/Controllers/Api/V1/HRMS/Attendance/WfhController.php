<?php

namespace App\Http\Controllers\Api\V1\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\WfhRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WfhController extends Controller
{
    public function __construct(private WfhRequestService $service)
    {
    }

    public function policy()
    {
        return $this->ok('WFH policy fetched successfully.', $this->service->policy());
    }

    public function balance(Request $request)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->err('Employee profile not found.', 404);
        }

        $month = (int) $request->input('month', Carbon::now('Asia/Kolkata')->month);
        $year = (int) $request->input('year', Carbon::now('Asia/Kolkata')->year);
        return $this->ok('WFH balance fetched successfully.', $this->service->balance($employee, $month, $year));
    }

    public function history(Request $request)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->err('Employee profile not found.', 404);
        }

        $rows = WfhRequestM::query()
            ->where('employee_id', $employee->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate((int) $request->input('per_page', 20));

        return $this->ok('WFH history fetched successfully.', [
            'records' => $rows->items(),
            'pagination' => [
                'total' => $rows->total(),
                'per_page' => $rows->perPage(),
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
            ],
        ]);
    }

    public function apply(Request $request)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->err('Employee profile not found.', 404);
        }

        $payload = $request->validate([
            'request_date' => 'required|date',
            'reason_category' => 'required|in:normal,personal_reason,manager_assigned,internet_issue,electricity_issue,other',
            'reason' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $row = $this->service->apply($employee, $payload);
        return $this->ok('WFH request submitted successfully.', $row, 201);
    }

    public function cancel(int $id)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->err('Employee profile not found.', 404);
        }

        $row = WfhRequestM::query()->where('employee_id', $employee->id)->find($id);
        if (! $row) {
            return $this->err('WFH request not found.', 404);
        }

        return $this->ok('WFH request cancelled successfully.', $this->service->cancel($row));
    }

    public function requests(Request $request)
    {
        $rows = WfhRequestM::query()
            ->with('employee.user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('request_type'), fn ($q) => $q->where('request_type', $request->request_type))
            ->when($request->filled('reason_category'), fn ($q) => $q->where('reason_category', $request->reason_category))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('request_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('request_date', '<=', $request->to))
            ->latest('id')
            ->paginate((int) $request->input('per_page', 30));

        return $this->ok('WFH requests fetched successfully.', [
            'records' => $rows->items(),
            'pagination' => [
                'total' => $rows->total(),
                'per_page' => $rows->perPage(),
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
            ],
        ]);
    }

    public function approve(int $id)
    {
        $row = WfhRequestM::find($id);
        if (! $row) {
            return $this->err('WFH request not found.', 404);
        }
        return $this->ok('WFH request approved successfully.', $this->service->approve($row, (int) auth()->id()));
    }

    public function reject(int $id, Request $request)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
        $row = WfhRequestM::find($id);
        if (! $row) {
            return $this->err('WFH request not found.', 404);
        }
        return $this->ok('WFH request rejected successfully.', $this->service->reject($row, (int) auth()->id(), $data['rejection_reason']));
    }

    public function markLwp(int $id, Request $request)
    {
        $data = $request->validate([
            'lwp_reason' => 'required|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $row = WfhRequestM::find($id);
        if (! $row) {
            return $this->err('WFH request not found.', 404);
        }

        return $this->ok(
            'WFH request marked as LWP successfully.',
            $this->service->markAsLwp($row, (int) auth()->id(), $data['lwp_reason'], $data['remarks'] ?? null)
        );
    }

    private function employee(): ?EmployeeM
    {
        return EmployeeM::query()->where('user_id', auth()->id())->first();
    }

    private function ok(string $message, mixed $data, int $code = 200)
    {
        return response()->json(['status' => true, 'success' => true, 'message' => $message, 'errors' => null, 'data' => $data], $code);
    }

    private function err(string $message, int $code)
    {
        return response()->json(['status' => false, 'success' => false, 'message' => $message, 'errors' => null, 'data' => null], $code);
    }
}
