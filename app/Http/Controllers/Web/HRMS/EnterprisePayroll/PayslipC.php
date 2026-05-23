<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayslipService;
use Illuminate\Support\Facades\Storage;

class PayslipC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private EnterprisePayslipService $payslipService)
    {
    }

    public function index()
    {
        $query = EnterprisePayslipM::with('employee.user', 'payroll')->latest();
        if (! $this->userHasPermission('enterprise_payslip.generate')) {
            $employeeId = $this->ownEmployeeId();
            abort_if(! $employeeId, 403);
            $query->where('employee_id', $employeeId)->where('is_visible_to_employee', 1);
        }

        return view('hrms.enterprise-payroll.payslips.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'payslips' => $query->get(),
            'self' => false,
        ]);
    }

    public function self()
    {
        $employeeId = $this->ownEmployeeId();
        abort_if(! $employeeId, 403);

        return view('hrms.enterprise-payroll.payslips.index', [
            'accesses' => $this->accesses(),
            'active' => 'employee.salary',
            'payslips' => EnterprisePayslipM::with('employee.user', 'payroll')
                ->where('employee_id', $employeeId)
                ->where('is_visible_to_employee', 1)
                ->latest()
                ->get(),
            'self' => true,
        ]);
    }

    public function generateForRun(EnterprisePayrollRunM $run)
    {
        $run->load('payrolls.employee.user');

        foreach ($run->payrolls as $payroll) {
            $this->payslipService->generate($payroll, $this->actorId());
        }

        return back()->with('success', 'Payslips generated successfully.');
    }

    public function download(EnterprisePayslipM $payslip)
    {
        if (! $this->userHasPermission('enterprise_payslip.generate')) {
            abort_unless($payslip->employee_id === $this->ownEmployeeId() && $payslip->is_visible_to_employee, 403);
        }

        abort_unless(Storage::disk('public')->exists($payslip->pdf_path), 404);

        return Storage::disk('public')->download($payslip->pdf_path, basename($payslip->pdf_path));
    }
}
