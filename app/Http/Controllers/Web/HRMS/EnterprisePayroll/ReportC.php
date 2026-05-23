<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterpriseBonusIncentiveM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM;
use Illuminate\Http\Request;

class ReportC extends Controller
{
    use HrmsCrudPage;

    public function index()
    {
        return view('hrms.enterprise-payroll.reports.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'reports' => $this->reportTypes(),
        ]);
    }

    public function show(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, $this->reportTypes()), 404);

        $month = (int) ($request->input('month') ?: now('Asia/Kolkata')->month);
        $year = (int) ($request->input('year') ?: now('Asia/Kolkata')->year);

        $rows = match ($type) {
            'monthly-payroll', 'deduction', 'summary', 'lwp-deduction', 'attendance-impact' => EnterprisePayrollM::with('employee.user')
                ->where('month', $month)
                ->where('year', $year)
                ->orderByDesc('net_salary')
                ->get(),
            'reimbursement' => EnterpriseReimbursementM::with('employee.user')
                ->whereMonth('claim_date', $month)
                ->whereYear('claim_date', $year)
                ->latest()
                ->get(),
            'bonus-incentive' => EnterpriseBonusIncentiveM::with('employee.user')
                ->where('month', $month)
                ->where('year', $year)
                ->latest()
                ->get(),
            'employee-salary' => EnterpriseSalaryStructureM::with('employee.user')
                ->where('status', 'active')
                ->latest()
                ->get(),
            default => collect(),
        };

        return view('hrms.enterprise-payroll.reports.show', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'type' => $type,
            'title' => $this->reportTypes()[$type],
            'rows' => $rows,
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function reportTypes(): array
    {
        return [
            'monthly-payroll' => 'Monthly Payroll Report',
            'deduction' => 'Deduction Report',
            'reimbursement' => 'Reimbursement Report',
            'bonus-incentive' => 'Bonus/Incentive Report',
            'employee-salary' => 'Employee Salary Report',
            'summary' => 'Payroll Summary Report',
            'lwp-deduction' => 'LWP Deduction Report',
            'attendance-impact' => 'Attendance Payroll Impact Report',
        ];
    }
}
