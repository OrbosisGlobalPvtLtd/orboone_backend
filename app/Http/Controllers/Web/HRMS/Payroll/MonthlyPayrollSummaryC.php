<?php

namespace App\Http\Controllers\Web\HRMS\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;

class MonthlyPayrollSummaryC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.monthly_summary.view'), 403);

        $query = $this->employeeJoinedQuery('monthly_attendance_summaries');
        $this->applyCommonFilters($query, $request, ['filterMap' => ['employee_id' => 'monthly_attendance_summaries.employee_id', 'month' => 'monthly_attendance_summaries.month', 'year' => 'monthly_attendance_summaries.year']]);
        return view('hrms.payroll.monthly_summary.index', [
            'accesses' => $this->accesses(), 'active' => 'payroll', 'pageTitle' => 'Monthly Payroll Summary',
            'rows' => $query->orderByDesc('year')->orderByDesc('month')->paginate(50),
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'month', 'label' => 'Month'], ['key' => 'year', 'label' => 'Year'], ['key' => 'payable_days', 'label' => 'Payable Days'], ['key' => 'lwp_days', 'label' => 'LWP'], ['key' => 'total_work_minutes', 'label' => 'Work Minutes'], ['key' => 'is_locked', 'label' => 'Locked', 'type' => 'badge']],
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray()], ['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => array_combine(range(1, 12), range(1, 12))], ['name' => 'year', 'label' => 'Year', 'type' => 'number']],
            'canCreate' => false,
        ]);
    }
}
