<?php

namespace App\Http\Controllers\Api\V1\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollApiS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EnterprisePayslipApiC extends Controller
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

        $query = EnterprisePayslipM::with('payroll')
            ->where('employee_id', $employee->id)
            ->where('is_visible_to_employee', true)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id');

        if ($request->filled('year')) {
            $query->where('year', (int) $request->query('year'));
        }

        $rows = $query->get()
            ->map(fn ($payslip) => $this->service->payslipListItem($payslip))
            ->values();

        return $this->apiResponse(true, 'Payslips fetched successfully.', $rows);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $employee = $this->service->employeeForUser($request->user()?->id);
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, null, 404);
        }

        $payslip = $this->service->visiblePayslip($employee->id, $id);
        if (! $payslip) {
            return $this->apiResponse(false, 'Payslip not found.', null, null, 404);
        }

        return $this->apiResponse(true, 'Payslip details fetched successfully.', $this->service->payslipDetail($payslip));
    }

    public function download(Request $request, int $id)
    {
        $employee = $this->service->employeeForUser($request->user()?->id);
        if (! $employee) {
            return $this->apiResponse(false, 'Employee profile not found.', null, null, 404);
        }

        $payslip = $this->service->visiblePayslip($employee->id, $id);
        if (! $payslip) {
            return $this->apiResponse(false, 'Payslip not found.', null, null, 404);
        }

        if (! $payslip->pdf_path || ! Storage::disk('public')->exists($payslip->pdf_path)) {
            return $this->apiResponse(false, 'Payslip PDF not found.', null, null, 404);
        }

        return Storage::disk('public')->download($payslip->pdf_path, basename($payslip->pdf_path));
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
