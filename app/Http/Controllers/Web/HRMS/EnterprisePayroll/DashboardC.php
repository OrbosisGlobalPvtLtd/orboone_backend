<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterpriseBonusIncentiveM;
use App\Models\HRMS\EnterprisePayroll\EnterprisePayrollRunM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM;
use App\Models\HRMS\EnterprisePayroll\EnterpriseSalaryStructureM;

class DashboardC extends Controller
{
    use HrmsCrudPage;

    public function index()
    {
        $latestRuns = EnterprisePayrollRunM::query()->latest()->take(8)->get();

        return view('hrms.enterprise-payroll.dashboard', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'summaryCards' => [
                ['label' => 'Active Salary Structures', 'value' => EnterpriseSalaryStructureM::where('status', 'active')->count()],
                ['label' => 'Payroll Runs', 'value' => EnterprisePayrollRunM::count()],
                ['label' => 'Approved Bonus/Incentives', 'value' => EnterpriseBonusIncentiveM::where('status', 'approved')->count()],
                ['label' => 'Approved Reimbursements', 'value' => EnterpriseReimbursementM::where('status', 'approved')->count()],
            ],
            'latestRuns' => $latestRuns,
        ]);
    }
}
