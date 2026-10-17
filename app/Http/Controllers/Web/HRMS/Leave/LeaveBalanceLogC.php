<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveBalanceService;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveBalanceLogC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('leave.balance_logs.view'), 403);

        $ledgerEnabled = true;
        if (! $ledgerEnabled) {
            $query = $this->employeeJoinedQuery('leave_balance_logs')
                ->leftJoin('leave_types', 'leave_types.id', '=', 'leave_balance_logs.leave_type_id')
                ->addSelect('leave_types.name as leave_type_name');
            $this->applyCommonFilters($query, $request, ['dateColumn' => 'leave_balance_logs.created_at', 'filterMap' => ['employee_id' => 'leave_balance_logs.employee_id', 'action' => 'leave_balance_logs.action']]);
            $actions = DB::table('leave_balance_logs')->whereNotNull('action')->distinct()->pluck('action', 'action')->toArray();
            $rows = $query->latest('leave_balance_logs.id')->paginate(50);
            $columns = [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'action', 'label' => 'Action'], ['key' => 'leave_type_name', 'label' => 'Type'], ['key' => 'credit', 'label' => 'Credit'], ['key' => 'debit', 'label' => 'Debit'], ['key' => 'balance_before', 'label' => 'Before'], ['key' => 'balance_after', 'label' => 'After'], ['key' => 'created_at', 'label' => 'Logged At', 'type' => 'datetime']];
        } else {
        $legacy = DB::table('leave_balance_logs')
            ->leftJoin('employees_new', 'employees_new.id', '=', 'leave_balance_logs.employee_id')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('leave_types', 'leave_types.id', '=', 'leave_balance_logs.leave_type_id')
            ->when($ledgerEnabled, fn ($query) => $query->where(function ($logs): void {
                $logs->whereNull('leave_balance_logs.leave_allocation_id')->orWhereNotIn('leave_balance_logs.leave_allocation_id',
                    DB::table('leave_allocations')->select('id')->where('uses_ledger', 1)->where('employment_stage', 'permanent'));
            }))
            ->selectRaw("leave_balance_logs.employee_id, employees_new.employee_code, COALESCE(users.name, employees_new.employee_code, 'N/A') as employee_display_name, COALESCE(leave_balance_logs.action, '') as action, COALESCE(leave_types.name, '') as bucket, COALESCE(leave_balance_logs.action, '') as type, leave_balance_logs.credit, leave_balance_logs.debit, leave_balance_logs.balance_before, leave_balance_logs.balance_after, (COALESCE(leave_balance_logs.credit, 0) - COALESCE(leave_balance_logs.debit, 0)) as amount, leave_balance_logs.created_at as date, leave_balance_logs.created_at, NULL as leave_request_id, leave_balance_logs.remarks as reason");
            $ledger = DB::table('leave_balance_transactions as t')
                ->join('leave_allocations as a', 'a.id', '=', 't.leave_allocation_id')
                ->leftJoin('employees_new', 'employees_new.id', '=', 't.employee_id')
                ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
                ->where('a.uses_ledger', 1)->where('a.employment_stage', 'permanent')
                ->selectRaw("t.employee_id, employees_new.employee_code, COALESCE(users.name, employees_new.employee_code, 'N/A') as employee_display_name, t.transaction_type as action, t.bucket as bucket, t.transaction_type as type, GREATEST(t.amount, 0) as credit, GREATEST(0 - t.amount, 0) as debit, t.balance_before, t.balance_after, t.amount, t.effective_date as date, t.created_at, t.leave_request_id, t.reason");
            $query = DB::query()->fromSub($legacy->unionAll($ledger), 'leave_log_rows')
                ->leftJoin('employees_new', 'employees_new.id', '=', 'leave_log_rows.employee_id')
                ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
                ->select('leave_log_rows.*');
        $this->applyCommonFilters($query, $request, ['dateColumn' => 'date', 'filterMap' => ['employee_id' => 'employee_id', 'action' => 'action']]);
        $actions = DB::table('leave_balance_logs')->whereNotNull('action')->distinct()->pluck('action', 'action')->toArray();
            $ledgerActions = DB::table('leave_balance_transactions')->distinct()->pluck('transaction_type', 'transaction_type')->toArray();
            $actions += $ledgerActions;
        $rows = $query->orderByDesc('date')->paginate(50);
        $columns = [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'date', 'label' => 'Effective Date'], ['key' => 'bucket', 'label' => 'Bucket / Type'], ['key' => 'type', 'label' => 'Type'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'balance_after', 'label' => 'Balance After'], ['key' => 'leave_request_id', 'label' => 'Request'], ['key' => 'reason', 'label' => 'Reason']];
        }
        if ($request->filled('employee_id')) {
            $employee = EmployeeM::query()->find((int) $request->employee_id);
            $ledger = $employee ? app(LeaveBalanceService::class)->getBalanceHistory($employee, 50) : null;
            $currentAllocation = $employee?->leaveAllocations()->where('year', now('Asia/Kolkata')->year)->first();
            if ($ledger !== null && $currentAllocation?->isLedgerBacked()) {
                $items = collect($ledger['history'])->map(fn (array $item) => (object) array_merge($item, [
                    'employee_display_name' => $employee->display_name,
                    'action' => $item['type'], 'leave_type_name' => $item['bucket'],
                    'credit' => $item['credit'], 'debit' => $item['debit'],
                ]));
                $rows = new LengthAwarePaginator($items, $ledger['pagination']['total'], $ledger['pagination']['per_page'],
                    $ledger['pagination']['current_page'], ['path' => $request->url(), 'query' => $request->query()]);
                $columns = [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'date', 'label' => 'Effective Date'], ['key' => 'bucket', 'label' => 'Bucket'], ['key' => 'type', 'label' => 'Type'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'balance_after', 'label' => 'Balance After'], ['key' => 'leave_request_id', 'label' => 'Request'], ['key' => 'reason', 'label' => 'Reason']];
            }
        }
        return view('hrms.leave.balance_logs.index', [
            'accesses' => $this->accesses(), 'active' => 'leave_management', 'pageTitle' => 'Leave Balance Logs',
            'pageSubtitle' => 'Read-only audit trail for every leave balance credit/debit.',
            'rows' => $rows,
            'columns' => $columns,
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray()], ['name' => 'action', 'label' => 'Action', 'type' => 'select', 'options' => $actions], ['name' => 'from', 'label' => 'From', 'type' => 'date'], ['name' => 'to', 'label' => 'To', 'type' => 'date']],
            'canCreate' => false,
        ]);
    }
}
