<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveBalanceLogC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('leave.balance_logs.view'), 403);

        $query = $this->employeeJoinedQuery('leave_balance_logs')
            ->leftJoin('leave_types', 'leave_types.id', '=', 'leave_balance_logs.leave_type_id')
            ->addSelect('leave_types.name as leave_type_name');
        $this->applyCommonFilters($query, $request, ['dateColumn' => 'leave_balance_logs.created_at', 'filterMap' => ['employee_id' => 'leave_balance_logs.employee_id', 'action' => 'leave_balance_logs.action']]);
        $actions = DB::table('leave_balance_logs')->whereNotNull('action')->distinct()->pluck('action', 'action')->toArray();
        return view('hrms.leave.balance_logs.index', [
            'accesses' => $this->accesses(), 'active' => 'leave_management', 'pageTitle' => 'Leave Balance Logs',
            'pageSubtitle' => 'Read-only audit trail for every leave balance credit/debit.',
            'rows' => $query->latest('leave_balance_logs.id')->paginate(50),
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'action', 'label' => 'Action'], ['key' => 'leave_type_name', 'label' => 'Type'], ['key' => 'credit', 'label' => 'Credit'], ['key' => 'debit', 'label' => 'Debit'], ['key' => 'balance_before', 'label' => 'Before'], ['key' => 'balance_after', 'label' => 'After'], ['key' => 'created_at', 'label' => 'Logged At', 'type' => 'datetime']],
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray()], ['name' => 'action', 'label' => 'Action', 'type' => 'select', 'options' => $actions], ['name' => 'from', 'label' => 'From', 'type' => 'date'], ['name' => 'to', 'label' => 'To', 'type' => 'date']],
            'canCreate' => false,
        ]);
    }
}
