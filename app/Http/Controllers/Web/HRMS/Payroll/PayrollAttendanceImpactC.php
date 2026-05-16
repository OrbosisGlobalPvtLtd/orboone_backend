<?php

namespace App\Http\Controllers\Web\HRMS\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollAttendanceImpactC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('payroll.attendance_impacts.view'), 403);

        $query = $this->employeeJoinedQuery('payroll_attendance_impacts');
        $this->applyCommonFilters($query, $request, ['filterMap' => ['employee_id' => 'payroll_attendance_impacts.employee_id', 'month' => 'payroll_attendance_impacts.month', 'year' => 'payroll_attendance_impacts.year', 'impact_type' => 'payroll_attendance_impacts.impact_type', 'processed' => 'payroll_attendance_impacts.is_processed_in_payroll']]);
        $types = DB::table('payroll_attendance_impacts')->whereNotNull('impact_type')->distinct()->pluck('impact_type', 'impact_type')->toArray();
        return view('hrms.payroll.attendance_impacts.index', [
            'accesses' => $this->accesses(), 'active' => 'payroll', 'pageTitle' => 'Attendance Payroll Impact',
            'rows' => $query->latest('payroll_attendance_impacts.id')->paginate(50),
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'month', 'label' => 'Month'], ['key' => 'year', 'label' => 'Year'], ['key' => 'impact_type', 'label' => 'Impact'], ['key' => 'impact_days', 'label' => 'Days'], ['key' => 'impact_amount', 'label' => 'Amount'], ['key' => 'is_processed_in_payroll', 'label' => 'Processed', 'type' => 'badge']],
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray()], ['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => array_combine(range(1, 12), range(1, 12))], ['name' => 'year', 'label' => 'Year', 'type' => 'number'], ['name' => 'impact_type', 'label' => 'Impact Type', 'type' => 'select', 'options' => $types], ['name' => 'processed', 'label' => 'Processed', 'type' => 'select', 'options' => [1 => 'Processed', 0 => 'Unprocessed']]],
            'canCreate' => false,
            'rowActions' => [['label' => 'Mark Processed', 'route' => 'hrms.payroll.attendance_impacts.process', 'icon' => 'fas fa-check', 'confirm' => 'Mark this impact as processed?']],
        ]);
    }

    public function process($id)
    {
        abort_unless($this->userHasPermission('payroll.generate.process'), 403);

        DB::table('payroll_attendance_impacts')->where('id', $id)->update(['is_processed_in_payroll' => 1, 'processed_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Payroll impact marked as processed.');
    }
}
