<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayslipM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayslipService;
use App\Services\HRMS\Storage\HrmsFileResolverS;

class PayslipC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private EnterprisePayslipService $payslipService,
        private HrmsFileResolverS $resolver
    )
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

        $resolved = $this->resolver->resolve($payslip->pdf_path);
        abort_unless($resolved, 404);

        return response()->download($resolved['absolute'], basename($resolved['absolute']));
    }

    public function regenerate(EnterprisePayslipM $payslip)
    {
        abort_unless($this->userHasPermission('enterprise_payslip.generate'), 403);

        $payroll = $payslip->payroll;
        abort_unless($payroll, 404);

        $this->payslipService->generate($payroll, $this->actorId());

        \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollAuditM::create([
            'payroll_run_id' => $payroll->payroll_run_id,
            'payroll_id' => $payroll->id,
            'employee_id' => $payroll->employee_id,
            'action' => 'payslip_regenerated',
            'old_values' => ['pdf_path' => $payslip->pdf_path],
            'new_values' => ['pdf_path' => $payslip->pdf_path, 'regenerated_at' => \Carbon\Carbon::now('Asia/Kolkata')],
            'performed_by_user_id' => $this->actorId(),
        ]);

        return back()->with('success', 'Payslip regenerated successfully.');
    }

    public function preview(EnterprisePayrollM $payroll)
    {
        if (! $this->userHasPermission('enterprise_payslip.generate')) {
            abort_unless($payroll->employee_id === $this->ownEmployeeId(), 403);
        }

        $payroll->loadMissing('employee.user', 'employee.department', 'employee.designation', 'items');

        $monthName = \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F');
        $payslipNo = sprintf('ORB-EP-%04d-%02d-%05d', $payroll->year, $payroll->month, $payroll->employee_id);

        return view('hrms.enterprise-payroll.payslips.pdf', [
            'payroll' => $payroll,
            'employee' => $payroll->employee,
            'monthName' => $monthName,
            'payslipNo' => $payslipNo,
            'isPreview' => true,
        ]);
    }

    public function generateForPayroll(EnterprisePayrollM $payroll)
    {
        abort_unless($this->userHasPermission('enterprise_payslip.generate'), 403);

        $existing = EnterprisePayslipM::where('payroll_id', $payroll->id)->first();
        if ($existing) {
            return back()->with('info', 'Payslip already exists for this payroll.');
        }

        $payslip = $this->payslipService->generate($payroll, $this->actorId());

        \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollAuditM::create([
            'payroll_run_id' => $payroll->payroll_run_id,
            'payroll_id' => $payroll->id,
            'employee_id' => $payroll->employee_id,
            'action' => 'payslip_generated',
            'old_values' => [],
            'new_values' => ['pdf_path' => $payslip->pdf_path, 'generated_at' => \Carbon\Carbon::now('Asia/Kolkata')],
            'performed_by_user_id' => $this->actorId(),
        ]);

        return back()->with('success', 'Payslip PDF generated successfully.');
    }

    public function view(EnterprisePayslipM $payslip)
    {
        if (! $this->userHasPermission('enterprise_payslip.generate')) {
            abort_unless($payslip->employee_id === $this->ownEmployeeId() && $payslip->is_visible_to_employee, 403);
        }

        $resolved = $this->resolver->resolve($payslip->pdf_path);
        abort_unless($resolved, 404);

        return response()->file($resolved['absolute'], [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($resolved['absolute']) . '"',
        ]);
    }

    public function email(EnterprisePayslipM $payslip)
    {
        abort_unless($this->userHasPermission('enterprise_payslip.generate'), 403);

        $employee = $payslip->employee;
        $user = $employee ? $employee->user : null;
        $email = $user ? $user->email : null;

        if (empty($email)) {
            return back()->with('error', 'Employee email is missing.');
        }

        $resolved = $this->resolver->resolve($payslip->pdf_path);
        if (!$resolved || !file_exists($resolved['absolute'])) {
            $payroll = $payslip->payroll;
            if (!$payroll) {
                return back()->with('error', 'Payroll record not found. Cannot generate PDF.');
            }
            $this->payslipService->generate($payroll, $this->actorId());
            $resolved = $this->resolver->resolve($payslip->pdf_path);
        }

        if (!$resolved || !file_exists($resolved['absolute'])) {
            return back()->with('error', 'Payslip PDF could not be found or generated.');
        }

        try {
            \Illuminate\Support\Facades\Mail::to($email)
                ->send(new \App\Mail\HRMS\EnterprisePayroll\PayslipMail(
                    $employee,
                    $payslip->payroll->run,
                    $payslip,
                    $resolved['absolute']
                ));
            
            \App\Models\HRMS\EnterprisePayroll\EnterprisePayrollAuditM::create([
                'payroll_run_id' => $payslip->payroll->payroll_run_id,
                'payroll_id' => $payslip->payroll_id,
                'employee_id' => $payslip->employee_id,
                'action' => 'payslip_emailed',
                'old_values' => ['email' => $email],
                'new_values' => ['email' => $email, 'sent_at' => \Carbon\Carbon::now('Asia/Kolkata')],
                'performed_by_user_id' => $this->actorId(),
            ]);

            return back()->with('success', 'Payslip emailed to employee successfully.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to email payslip', [
                'payslip_id' => $payslip->id,
                'employee_id' => $payslip->employee_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
