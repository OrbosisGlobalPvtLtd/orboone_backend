<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM;
use App\Services\HRMS\EnterprisePayroll\EnterprisePayrollCalculatorS;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollRunC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private EnterprisePayrollCalculatorS $calculator)
    {
    }

    public function index()
    {
        $runs = EnterprisePayrollRunM::query()->latest()->get();
        $employees = \App\Models\HRMS\Employee\EmployeeM::query()->active()->with('user')->orderBy('id')->get();

        return view('hrms.enterprise-payroll.runs.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'runs' => $runs,
            'employees' => $employees,
        ]);
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'employee_id' => ['nullable', 'integer', 'exists:employees_new,id'],
        ]);

        $employeeId = isset($data['employee_id']) ? (int)$data['employee_id'] : null;

        $preview = $this->calculator->preview((int) $data['month'], (int) $data['year'], $employeeId);

        return view('hrms.enterprise-payroll.runs.preview', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'month' => (int) $data['month'],
            'year' => (int) $data['year'],
            'employee_id' => $employeeId,
            'rows' => $preview['rows'],
            'payrollErrors' => $preview['errors'],
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'employee_id' => ['nullable', 'integer', 'exists:employees_new,id'],
        ]);

        $employeeId = isset($data['employee_id']) ? (int)$data['employee_id'] : null;

        $run = $this->calculator->generate((int) $data['month'], (int) $data['year'], $this->actorId(), $employeeId);

        return redirect()->route('enterprise-payroll.runs.show', $run)->with('success', 'Enterprise payroll generated successfully.');
    }

    public function show(EnterprisePayrollRunM $run)
    {
        $run->load('payrolls.employee.user', 'payrolls.payslip');

        return view('hrms.enterprise-payroll.runs.show', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'run' => $run,
        ]);
    }

    public function approve(EnterprisePayrollRunM $run)
    {
        $this->calculator->approve($run, $this->actorId());

        return back()->with('success', 'Enterprise payroll approved.');
    }

    public function lock(EnterprisePayrollRunM $run)
    {
        $this->calculator->lock($run, $this->actorId());

        return back()->with('success', 'Enterprise payroll locked.');
    }

    public function reopen(EnterprisePayrollRunM $run)
    {
        $this->calculator->reopen($run, $this->actorId());

        return back()->with('success', 'Enterprise payroll reopened as draft.');
    }

    public function downloadReport(EnterprisePayrollRunM $run): StreamedResponse
    {
        $run->load('payrolls.employee.user');

        return response()->streamDownload(function () use ($run) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Month', 'Year', 'Gross', 'Deductions', 'Net', 'Status']);
            foreach ($run->payrolls as $payroll) {
                fputcsv($handle, [
                    optional($payroll->employee)->display_name,
                    $payroll->month,
                    $payroll->year,
                    $payroll->gross_salary,
                    $payroll->total_deductions,
                    $payroll->net_salary,
                    $payroll->status,
                ]);
            }
            fclose($handle);
        }, "enterprise-payroll-{$run->year}-{$run->month}.csv");
    }
}
