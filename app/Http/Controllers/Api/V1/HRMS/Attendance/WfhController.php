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
        $balance = $this->service->balance($employee, $month, $year);

        if ($employee->isPermanentWfh()) {
            $balance['is_permanent_wfh'] = true;
            $balance['show_wfh_module'] = false;
            $balance['notice'] = 'You are a Permanent Work From Home employee. No WFH approval is required.';
        } else {
            $balance['is_permanent_wfh'] = false;
            $balance['show_wfh_module'] = true;
        }

        return $this->ok('WFH balance fetched successfully.', $balance);
    }

    public function calculateDays(Request $request)
    {
        $employee = $this->employee();
        if (! $employee) {
            return $this->err('Employee profile not found.', 404);
        }

        $fromDate = $request->input('from_date') ?: $request->input('date_from') ?: $request->input('request_date');
        $toDate = $request->input('to_date') ?: $request->input('date_to') ?: $fromDate;

        if (! $fromDate || ! $toDate) {
            return $this->err('From Date and To Date are required.', 422);
        }

        try {
            $stats = $this->service->calculateRangeStats($employee, (string) $fromDate, (string) $toDate);
            return $this->ok('Working days calculated successfully.', $stats);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'data' => null,
            ], 422);
        }
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
            'request_date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'reason_category' => 'required|in:normal,personal_reason,manager_assigned,internet_issue,electricity_issue,other',
            'reason' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        try {
            $row = $this->service->apply($employee, $payload);
            return $this->ok('WFH request submitted successfully.', $row, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage(),
                'errors' => $e->errors(),
                'data' => null,
            ], 422);
        }
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
            ->when($request->filled('from'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->whereDate('from_date', '>=', $request->from)
                    ->orWhereDate('request_date', '>=', $request->from);
            }))
            ->when($request->filled('to'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->whereDate('to_date', '<=', $request->to)
                    ->orWhereDate('request_date', '<=', $request->to);
            }))
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

    public function approve(int $id, Request $request)
    {
        $row = WfhRequestM::find($id);
        if (! $row) {
            return $this->err('WFH request not found.', 404);
        }

        $partialRange = null;
        if ($request->filled('approved_from_date') && $request->filled('approved_to_date')) {
            $partialRange = [
                'approved_from_date' => $request->input('approved_from_date'),
                'approved_to_date' => $request->input('approved_to_date'),
            ];
        }

        try {
            return $this->ok('WFH request approved successfully.', $this->service->approve($row, (int) auth()->id(), $partialRange));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage(),
                'errors' => $e->errors(),
                'data' => null,
            ], 422);
        }
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
