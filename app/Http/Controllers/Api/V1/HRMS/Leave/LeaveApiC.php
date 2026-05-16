<?php

namespace App\Http\Controllers\Api\V1\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\CompOffM;
use App\Models\HRMS\Leave\HolidayM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Services\HRMS\Leave\LeaveApprovalService;
use App\Services\HRMS\Leave\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeaveApiC extends Controller
{
    public function __construct(
        private LeaveCalculationService $calculationService,
        private LeaveApprovalService $approvalService
    ) {
    }

    public function types()
    {
        return $this->ok('Leave types fetched successfully.', LeaveTypeM::where('is_active', true)->orderBy('name')->get());
    }

    public function dashboard()
    {
        try {
            $employee = $this->employee();
            $today = Carbon::now('Asia/Kolkata')->toDateString();
            $year = Carbon::now('Asia/Kolkata')->year;

            $statusCounts = LeaveRequestM::query()
                ->where('employee_id', $employee->id)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $summary = [
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'approved' => (int) ($statusCounts['approved'] ?? 0),
                'rejected' => (int) ($statusCounts['rejected'] ?? 0),
                'total' => (int) $statusCounts->sum(),
            ];

            $allocation = LeaveAllocationM::where('employee_id', $employee->id)
                ->where('year', $year)
                ->latest()
                ->first();

            $balances = $this->formatBalances($allocation);

            $recentRequests = LeaveRequestM::with(['leaveType:id,name,code,color', 'dates'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (LeaveRequestM $leaveRequest) => $this->formatLeaveRequest($leaveRequest))
                ->values();

            $upcomingHolidays = $this->holidayQuery()
                ->whereDate('holiday_date', '>=', $today)
                ->orderBy('holiday_date')
                ->limit(5)
                ->get()
                ->map(fn (HolidayM $holiday) => $this->formatHoliday($holiday))
                ->values();

            $teamEmployeeIds = EmployeeM::query()
                ->where('reporting_manager_employee_id', $employee->id)
                ->pluck('id');

            $teamOnLeave = $teamEmployeeIds->isEmpty()
                ? collect()
                : LeaveRequestM::with(['employee.user:id,name', 'leaveType:id,name,code,color'])
                    ->whereIn('employee_id', $teamEmployeeIds)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->orderBy('start_date')
                    ->get()
                    ->map(fn (LeaveRequestM $leaveRequest) => [
                        'id' => $leaveRequest->id,
                        'employee_id' => $leaveRequest->employee_id,
                        'employee_name' => $leaveRequest->employee?->display_name,
                        'leave_type' => $leaveRequest->leaveType,
                        'start_date' => optional($leaveRequest->start_date)->toDateString(),
                        'end_date' => optional($leaveRequest->end_date)->toDateString(),
                        'total_days' => (float) ($leaveRequest->requested_days ?? $leaveRequest->deducted_days ?? 0),
                    ])
                    ->values();

            $compOffs = $this->compOffRecords($employee->id, 5);

            return $this->ok('Leave dashboard fetched successfully.', [
                'summary' => $summary,
                'balances' => $balances,
                'recent_requests' => $recentRequests,
                'upcoming_holidays' => $upcomingHolidays,
                'team_on_leave' => $teamOnLeave,
                'comp_offs' => $compOffs,
            ]);
        } catch (HttpException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode(), null);
        } catch (\Throwable $e) {
            Log::error('API leave dashboard failed', ['error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 500, null);
        }
    }

    public function balance()
    {
        $employee = $this->employee();
        $allocation = LeaveAllocationM::where('employee_id', $employee->id)
            ->where('year', Carbon::now('Asia/Kolkata')->year)
            ->latest()
            ->first();

        return $this->ok('Leave balance fetched successfully.', [
            'allocation' => $allocation,
            'total_remaining' => (float) ($allocation->total_remaining ?? 0),
            'paid_remaining' => (float) ($allocation->paid_remaining ?? 0),
            'sick_remaining' => (float) ($allocation->sick_remaining ?? 0),
            'comp_off_remaining' => (float) ($allocation->comp_off_remaining ?? 0),
            'lwp_used' => (float) ($allocation->lwp_used ?? 0),
        ]);
    }

    public function calculate(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $employee = $this->employee();
        $leaveType = LeaveTypeM::findOrFail($data['leave_type_id']);
        $calculation = $this->calculationService->calculate($employee, $leaveType, $data);

        return $this->ok('Leave calculated successfully.', $this->calculationPayload($calculation));
    }

    public function apply(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|in:first_half,second_half',
            'reason' => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'emergency_leave' => 'nullable|boolean',
        ]);

        try {
            $employee = $this->employee();
            $leaveType = LeaveTypeM::findOrFail($data['leave_type_id']);
            $attachmentPath = $this->storeAttachment($request);
            $calculation = $this->calculationService->calculate($employee, $leaveType, array_merge($data, ['attachment_path' => $attachmentPath]));

            $leaveRequest = DB::transaction(function () use ($employee, $leaveType, $data, $attachmentPath, $calculation, $request) {
                $leaveRequest = LeaveRequestM::create([
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'leave_type_id' => $leaveType->id,
                    'reporting_manager_employee_id' => $employee->reporting_manager_employee_id,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'requested_days' => $calculation['requested_days'],
                    'deducted_days' => $calculation['deducted_days'],
                    'is_half_day' => $request->boolean('is_half_day'),
                    'half_day_type' => $data['half_day_type'] ?? null,
                    'reason' => $data['reason'],
                    'attachment_path' => $attachmentPath,
                    'status' => 'pending',
                    'sandwich_applied' => $calculation['sandwich_applied'],
                    'paid_days' => $calculation['paid_days'],
                    'sick_days' => $calculation['sick_days'],
                    'comp_off_days' => $calculation['comp_off_days'],
                    'lwp_days' => $calculation['lwp_days'],
                    'applied_from' => 'mobile',
                    'emergency_leave' => $request->boolean('emergency_leave'),
                ]);

                foreach ($calculation['dates'] as $row) {
                    $leaveRequest->dates()->create(array_merge($row, ['employee_id' => $employee->id]));
                }

                return $leaveRequest->fresh(['leaveType', 'dates']);
            });

            return $this->ok('Leave request submitted successfully.', $leaveRequest, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('API leave apply failed', ['error' => $e->getMessage()]);
            return $this->fail('Unable to submit leave request.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function myRequests(Request $request)
    {
        $employee = $this->employee();
        $requests = LeaveRequestM::with(['leaveType', 'dates'])
            ->where('employee_id', $employee->id)
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate((int) ($request->per_page ?: 20));

        return $this->ok('Leave requests fetched successfully.', $requests);
    }

    public function history(Request $request)
    {
        return $this->myRequests($request);
    }

    public function holidays()
    {
        try {
            $year = (int) (request('year') ?: Carbon::now('Asia/Kolkata')->year);

            $holidays = $this->holidayQuery()
                ->whereYear('holiday_date', $year)
                ->orderBy('holiday_date')
                ->get()
                ->map(fn (HolidayM $holiday) => $this->formatHoliday($holiday))
                ->values();

            return $this->ok('Holidays fetched successfully.', $holidays);
        } catch (\Throwable $e) {
            Log::error('API leave holidays failed', ['error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 500, null);
        }
    }

    public function compOffs()
    {
        try {
            $employee = $this->employee();

            return $this->ok('Comp offs fetched successfully.', $this->compOffRecords($employee->id));
        } catch (HttpException $e) {
            return $this->fail($e->getMessage(), $e->getStatusCode(), null);
        } catch (\Throwable $e) {
            Log::error('API leave comp offs failed', ['error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 500, null);
        }
    }

    public function show($id)
    {
        try {
            $user = auth()->user();
            $canViewAll = $this->canViewAllLeaveDetails($user);
            $employee = EmployeeM::where('user_id', auth()->id())->first();

            if (! $canViewAll && ! $employee) {
                return $this->fail('Employee profile not found.', 404, null);
            }

            $leaveRequest = LeaveRequestM::with([
                'employee.user:id,name',
                'leaveType:id,name,code,color',
                'dates',
                'approver:id,name',
                'managerApprover:id,name',
                'hrApprover:id,name',
                'canceller:id,name',
            ])
                ->when(! $canViewAll, fn ($query) => $query->where('employee_id', $employee->id))
                ->find($id);

            if (! $leaveRequest) {
                return $this->fail('Leave request not found.', 404, null);
            }

            return $this->ok('Leave request fetched successfully.', $this->formatLeaveRequest($leaveRequest, true));
        } catch (\Throwable $e) {
            Log::error('API leave show failed', ['leave_request_id' => $id, 'error' => $e->getMessage()]);
            return $this->fail($e->getMessage(), 500, null);
        }
    }

    public function calendar(Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2099',
        ]);

        $employee = $this->employee();
        $month = (int) ($request->month ?: Carbon::now('Asia/Kolkata')->month);
        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);

        $dates = DB::table('leave_request_dates')
            ->join('leave_requests', 'leave_requests.id', '=', 'leave_request_dates.leave_request_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->where('leave_request_dates.employee_id', $employee->id)
            ->whereMonth('leave_request_dates.leave_date', $month)
            ->whereYear('leave_request_dates.leave_date', $year)
            ->whereNotIn('leave_requests.status', ['rejected', 'cancelled'])
            ->select('leave_request_dates.*', 'leave_requests.status', 'leave_types.name as leave_type_name', 'leave_types.code as leave_type_code')
            ->orderBy('leave_request_dates.leave_date')
            ->get();

        return $this->ok('Leave calendar fetched successfully.', $dates);
    }

    public function teamCalendar(Request $request)
    {
        return $this->calendar($request);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:2000']);
        $leaveRequest = LeaveRequestM::where('employee_id', $this->employee()->id)->findOrFail($id);
        $leaveRequest = $this->approvalService->cancel($leaveRequest, auth()->id(), $request->reason);

        return $this->ok('Leave request cancelled successfully.', $leaveRequest);
    }

    private function employee(): EmployeeM
    {
        $employee = EmployeeM::where('user_id', auth()->id())->first();
        abort_if(! $employee, 404, 'Employee profile not found.');

        return $employee;
    }

    private function canViewAllLeaveDetails($user): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin'])) {
            return true;
        }

        return method_exists($user, 'hasPermission') && (
            $user->hasPermission('leave.approvals.view_all')
            || $user->hasPermission('leave.my_requests.view_all')
        );
    }

    private function holidayQuery()
    {
        $query = HolidayM::query()
            ->where('is_active', true)
            ->select(['id', 'title', 'holiday_date', 'holiday_type']);

        if (Schema::hasColumn('holidays', 'description')) {
            $query->addSelect('description');
        }

        return $query;
    }

    private function compOffRecords(int $employeeId, ?int $limit = null)
    {
        $query = CompOffM::query()
            ->where('employee_id', $employeeId)
            ->select([
                'id',
                'employee_id',
                'worked_date',
                'earned_days',
                'expiry_date',
                'status',
                'used_against_leave_request_id',
                'remarks',
                'created_at',
            ])
            ->orderByDesc('worked_date')
            ->orderByDesc('id');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()
            ->map(fn (CompOffM $compOff) => [
                'id' => $compOff->id,
                'employee_id' => $compOff->employee_id,
                'earned_date' => optional($compOff->worked_date)->toDateString(),
                'worked_date' => optional($compOff->worked_date)->toDateString(),
                'expiry_date' => optional($compOff->expiry_date)->toDateString(),
                'status' => $compOff->status,
                'available_days' => $compOff->status === 'earned' ? (float) $compOff->earned_days : 0.0,
                'used_days' => $compOff->status === 'used' ? (float) $compOff->earned_days : 0.0,
                'earned_days' => (float) $compOff->earned_days,
                'remarks' => $compOff->remarks,
            ])
            ->values();
    }

    private function formatBalances(?LeaveAllocationM $allocation): array
    {
        if (! $allocation) {
            return [];
        }

        return [
            [
                'type' => 'total',
                'label' => 'Total Leave',
                'allocated' => (float) ($allocation->total_allocated ?? 0),
                'used' => (float) ($allocation->total_used ?? 0),
                'remaining' => (float) ($allocation->total_remaining ?? 0),
            ],
            [
                'type' => 'paid',
                'label' => 'Paid Leave',
                'allocated' => (float) ($allocation->paid_allocated ?? 0),
                'used' => (float) ($allocation->paid_used ?? 0),
                'remaining' => (float) ($allocation->paid_remaining ?? 0),
            ],
            [
                'type' => 'sick',
                'label' => 'Sick Leave',
                'allocated' => (float) ($allocation->sick_allocated ?? 0),
                'used' => (float) ($allocation->sick_used ?? 0),
                'remaining' => (float) ($allocation->sick_remaining ?? 0),
            ],
            [
                'type' => 'comp_off',
                'label' => 'Comp Off',
                'allocated' => (float) ($allocation->comp_off_allocated ?? 0),
                'used' => (float) ($allocation->comp_off_used ?? 0),
                'remaining' => (float) ($allocation->comp_off_remaining ?? 0),
            ],
            [
                'type' => 'lwp',
                'label' => 'Leave Without Pay',
                'allocated' => 0.0,
                'used' => (float) ($allocation->lwp_used ?? 0),
                'remaining' => 0.0,
            ],
        ];
    }

    private function formatHoliday(HolidayM $holiday): array
    {
        return [
            'id' => $holiday->id,
            'title' => $holiday->title,
            'holiday_date' => optional($holiday->holiday_date)->toDateString(),
            'type' => $holiday->holiday_type,
            'holiday_type' => $holiday->holiday_type,
            'description' => $holiday->description ?? null,
        ];
    }

    private function formatLeaveRequest(LeaveRequestM $leaveRequest, bool $includeDetails = false): array
    {
        $attachmentPath = $leaveRequest->attachment_path;

        $payload = [
            'id' => $leaveRequest->id,
            'employee_id' => $leaveRequest->employee_id,
            'employee_name' => $leaveRequest->employee?->display_name,
            'leave_type' => $leaveRequest->leaveType,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'start_date' => optional($leaveRequest->start_date)->toDateString(),
            'end_date' => optional($leaveRequest->end_date)->toDateString(),
            'reason' => $leaveRequest->reason,
            'status' => $leaveRequest->status,
            'applied_at' => $this->formatDateTime($leaveRequest->created_at),
            'approved_by' => $this->formatUser($leaveRequest->approver)
                ?? $this->formatUser($leaveRequest->hrApprover)
                ?? $this->formatUser($leaveRequest->managerApprover),
            'rejection_reason' => $leaveRequest->rejection_reason,
            'total_days' => (float) ($leaveRequest->requested_days ?? $leaveRequest->deducted_days ?? 0),
            'requested_days' => (float) ($leaveRequest->requested_days ?? 0),
            'deducted_days' => (float) ($leaveRequest->deducted_days ?? 0),
            'is_half_day' => (bool) $leaveRequest->is_half_day,
            'half_day_type' => $leaveRequest->half_day_type,
            'attachments' => $attachmentPath ? [[
                'path' => $attachmentPath,
                'url' => asset($attachmentPath),
            ]] : [],
            'attachment_path' => $attachmentPath,
        ];

        if ($includeDetails) {
            $payload['dates'] = $leaveRequest->dates;
            $payload['approved_at'] = $this->formatDateTime($leaveRequest->approved_at);
            $payload['manager_approved_by'] = $this->formatUser($leaveRequest->managerApprover);
            $payload['manager_approved_at'] = $this->formatDateTime($leaveRequest->manager_approved_at);
            $payload['hr_approved_by'] = $this->formatUser($leaveRequest->hrApprover);
            $payload['hr_approved_at'] = $this->formatDateTime($leaveRequest->hr_approved_at);
            $payload['cancelled_by'] = $this->formatUser($leaveRequest->canceller);
            $payload['cancelled_at'] = $this->formatDateTime($leaveRequest->cancelled_at);
            $payload['cancel_reason'] = $leaveRequest->cancel_reason;
            $payload['paid_days'] = (float) ($leaveRequest->paid_days ?? 0);
            $payload['sick_days'] = (float) ($leaveRequest->sick_days ?? 0);
            $payload['comp_off_days'] = (float) ($leaveRequest->comp_off_days ?? 0);
            $payload['lwp_days'] = (float) ($leaveRequest->lwp_days ?? 0);
            $payload['sandwich_applied'] = (bool) $leaveRequest->sandwich_applied;
            $payload['applied_from'] = $leaveRequest->applied_from;
            $payload['emergency_leave'] = (bool) $leaveRequest->emergency_leave;
        }

        return $payload;
    }

    private function formatUser($user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    private function formatDateTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->timezone('Asia/Kolkata')->toDateTimeString();
    }

    private function calculationPayload(array $calculation): array
    {
        return [
            'requested_days' => $calculation['requested_days'],
            'deducted_days' => $calculation['deducted_days'],
            'paid_days' => $calculation['paid_days'],
            'sick_days' => $calculation['sick_days'],
            'comp_off_days' => $calculation['comp_off_days'],
            'lwp_days' => $calculation['lwp_days'],
            'sandwich_applied' => $calculation['sandwich_applied'],
            'dates' => $calculation['dates'],
        ];
    }

    private function storeAttachment(Request $request): ?string
    {
        if (! $request->hasFile('attachment')) {
            return null;
        }

        $directory = public_path('uploads/leave_attachments');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $file = $request->file('attachment');
        $fileName = 'leave_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $fileName);

        return 'uploads/leave_attachments/' . $fileName;
    }

    private function ok(string $message, $data = [], int $code = 200)
    {
        return response()->json(['success' => true, 'status' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, int $code = 400, $data = null)
    {
        return response()->json(['success' => false, 'status' => false, 'message' => $message, 'data' => $data], $code);
    }
}
