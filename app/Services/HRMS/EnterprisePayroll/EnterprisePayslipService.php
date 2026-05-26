<?php

namespace App\Services\HRMS\EnterprisePayroll;

use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM;
use App\Services\HRMS\Notification\NotificationS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EnterprisePayslipService
{
    public function __construct(
        private NotificationS $notificationService,
        private HrmsStoragePathS $paths
    )
    {
    }

    public function generate(EnterprisePayrollM $payroll, ?int $actorId = null): EnterprisePayslipM
    {
        $payroll->loadMissing('employee.user', 'employee.department', 'employee.designation', 'items');

        if (! in_array($payroll->status, ['generated', 'approved', 'locked', 'paid'], true)) {
            throw ValidationException::withMessages(['payslip' => 'Payslip can be generated only after payroll is generated or approved.']);
        }

        if ((float) $payroll->net_salary < 0 || (float) $payroll->monthly_ctc < 0) {
            throw ValidationException::withMessages(['payslip' => 'Payslip amount is invalid or employee salary data is missing.']);
        }

        $monthName = Carbon::create($payroll->year, $payroll->month, 1)->format('F');
        $payslipNo = sprintf('ORB-EP-%04d-%02d-%05d', $payroll->year, $payroll->month, $payroll->employee_id);
        $directory = $this->paths->employeePayroll((int) $payroll->employee_id, 'payslips');
        $fileName = "{$payslipNo}.pdf";
        $path = "{$directory}/{$fileName}";

        Storage::disk('private')->makeDirectory($directory);

        $pdf = Pdf::loadView('hrms.enterprise-payroll.payslips.pdf', [
            'payroll' => $payroll,
            'employee' => $payroll->employee,
            'monthName' => $monthName,
            'payslipNo' => $payslipNo,
        ])->setPaper('a4');

        Storage::disk('private')->put($path, $pdf->output());

        $payslip = EnterprisePayslipM::updateOrCreate(
            ['employee_id' => $payroll->employee_id, 'month' => $payroll->month, 'year' => $payroll->year],
            [
                'payroll_id' => $payroll->id,
                'payslip_no' => $payslipNo,
                'pdf_path' => $path,
                'pdf_url' => null,
                'generated_by_user_id' => $actorId,
                'generated_at' => Carbon::now('Asia/Kolkata'),
                'is_visible_to_employee' => true,
            ]
        );
        $payslip->pdf_url = url("/api/v1/hrms/enterprise-payroll/payslips/{$payslip->id}/download");
        $payslip->save();

        $this->notificationService->notifyEmployee(
            'Payslip Available',
            "Your payslip for {$monthName} {$payroll->year} is available.",
            'enterprise_payslip_available',
            'enterprise-payroll.self.payslips',
            [],
            ['payslip_id' => $payslip->id],
            optional($payroll->employee->user)->id
        );

        return $payslip;
    }
}
