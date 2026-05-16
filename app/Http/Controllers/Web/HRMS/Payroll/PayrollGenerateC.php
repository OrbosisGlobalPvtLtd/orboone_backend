<?php

namespace App\Http\Controllers\Web\HRMS\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollGenerateC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.generate.view'), 403);

        $month = (int) ($request->month ?: now()->month);
        $year = (int) ($request->year ?: now()->year);
        $query = $this->employeeJoinedQuery('monthly_attendance_summaries')
            ->where('monthly_attendance_summaries.month', $month)
            ->where('monthly_attendance_summaries.year', $year);

        return view('hrms.payroll.generate.index', [
            'accesses' => $this->accesses(), 'active' => 'payroll', 'pageTitle' => 'Generate Payroll',
            'pageSubtitle' => 'Review attendance summaries and payroll impacts before processing.',
            'rows' => $query->orderBy('employee_display_name')->paginate(50),
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'present_days', 'label' => 'Present'], ['key' => 'paid_leave_days', 'label' => 'Paid Leave'], ['key' => 'lwp_days', 'label' => 'LWP'], ['key' => 'payable_days', 'label' => 'Payable'], ['key' => 'is_locked', 'label' => 'Locked', 'type' => 'badge']],
            'filters' => [['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => array_combine(range(1, 12), range(1, 12))], ['name' => 'year', 'label' => 'Year', 'type' => 'number']],
            'canCreate' => false,
            'summaryCards' => [
                ['label' => 'Month', 'value' => $month], ['label' => 'Year', 'value' => $year], ['label' => 'Employees', 'value' => $query->count()],
            ],
        ]);
    }

    public function process(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.generate.process'), 403);

        $request->validate(['month' => 'required|integer|min:1|max:12', 'year' => 'required|integer|min:2020|max:2099']);
        DB::table('payroll_attendance_impacts')->where('month', $request->month)->where('year', $request->year)->where('is_processed_in_payroll', 0)->update(['is_processed_in_payroll' => 1, 'processed_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Payroll impacts marked processed for selected month.');
    }
}
