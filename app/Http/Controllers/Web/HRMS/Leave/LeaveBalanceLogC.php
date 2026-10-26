<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeaveBalanceLogC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('leave.balance_logs.view'), 403);

        $year = (int) ($request->input('year') ?: Carbon::now('Asia/Kolkata')->year);
        $currentMonth = (int) Carbon::now('Asia/Kolkata')->month;

        if ($request->has('month')) {
            $month = (string) $request->input('month');
        } elseif (! $request->filled('employee_id') && ! $request->has('reset')) {
            $month = (string) $currentMonth;
        } else {
            $month = null;
        }

        $fromDate = $request->input('from_date') ?: $request->input('from');
        $toDate = $request->input('to_date') ?: $request->input('to');

        $ledgerEnabled = true;
        if (! $ledgerEnabled) {
            $query = $this->employeeJoinedQuery('leave_balance_logs')
                ->leftJoin('leave_types', 'leave_types.id', '=', 'leave_balance_logs.leave_type_id')
                ->addSelect('leave_types.name as leave_type_name');
            $this->applyCommonFilters($query, $request, ['dateColumn' => 'leave_balance_logs.created_at', 'filterMap' => ['employee_id' => 'leave_balance_logs.employee_id', 'action' => 'leave_balance_logs.action']]);

            if ($fromDate || $toDate) {
                if ($fromDate) {
                    $query->whereDate('leave_balance_logs.created_at', '>=', $fromDate);
                }
                if ($toDate) {
                    $query->whereDate('leave_balance_logs.created_at', '<=', $toDate);
                }
            } elseif ($month !== null && $month !== '') {
                if ($month === 'all') {
                    $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
                    $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();
                    $query->whereDate('leave_balance_logs.created_at', '>=', $yearStart)
                        ->whereDate('leave_balance_logs.created_at', '<=', $yearEnd);
                } else {
                    $m = (int) $month;
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth()->toDateString();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth()->toDateString();
                    $query->whereDate('leave_balance_logs.created_at', '>=', $monthStart)
                        ->whereDate('leave_balance_logs.created_at', '<=', $monthEnd);
                }
            }

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

            if ($fromDate || $toDate) {
                if ($fromDate) {
                    $query->whereDate('date', '>=', $fromDate);
                }
                if ($toDate) {
                    $query->whereDate('date', '<=', $toDate);
                }
            } elseif ($month !== null && $month !== '') {
                if ($month === 'all') {
                    $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
                    $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();
                    $query->whereDate('date', '>=', $yearStart)
                        ->whereDate('date', '<=', $yearEnd);
                } else {
                    $m = (int) $month;
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth()->toDateString();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth()->toDateString();
                    $query->whereDate('date', '>=', $monthStart)
                        ->whereDate('date', '<=', $monthEnd);
                }
            }

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

                if ($fromDate || $toDate) {
                    $items = $items->filter(function ($item) use ($fromDate, $toDate) {
                        $itemDate = substr((string) ($item->date ?? ''), 0, 10);
                        if ($fromDate && $itemDate < $fromDate) {
                            return false;
                        }
                        if ($toDate && $itemDate > $toDate) {
                            return false;
                        }

                        return true;
                    });
                } elseif ($month !== null && $month !== '') {
                    if ($month === 'all') {
                        $items = $items->filter(function ($item) use ($year) {
                            return (int) substr((string) ($item->date ?? ''), 0, 4) === $year;
                        });
                    } else {
                        $targetPrefix = sprintf('%04d-%02d', $year, (int) $month);
                        $items = $items->filter(function ($item) use ($targetPrefix) {
                            return substr((string) ($item->date ?? ''), 0, 7) === $targetPrefix;
                        });
                    }
                }

                if ($request->filled('action') && $request->input('action') !== 'all') {
                    $items = $items->filter(fn ($item) => (string) ($item->action ?? '') === (string) $request->input('action'));
                }

                $rows = new LengthAwarePaginator($items->values(), $items->count(), $ledger['pagination']['per_page'],
                    1, ['path' => $request->url(), 'query' => $request->query()]);
                $columns = [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'date', 'label' => 'Effective Date'], ['key' => 'bucket', 'label' => 'Bucket'], ['key' => 'type', 'label' => 'Type'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'balance_after', 'label' => 'Balance After'], ['key' => 'leave_request_id', 'label' => 'Request'], ['key' => 'reason', 'label' => 'Reason']];
            }
        }

        $months = [
            'all' => 'All Months',
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        $currentYear = (int) Carbon::now('Asia/Kolkata')->year;
        $years = [];
        for ($y = 2024; $y <= 2030; $y++) {
            $years[$y] = (string) $y;
        }

        return view('hrms.leave.balance_logs.index', [
            'accesses' => $this->accesses(),
            'active' => 'leave_management',
            'pageTitle' => 'Leave Balance Logs',
            'pageSubtitle' => 'Read-only audit trail for every leave balance credit/debit.',
            'rows' => $rows,
            'columns' => $columns,
            'defaultMonth' => (string) $currentMonth,
            'filters' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray()],
                ['name' => 'action', 'label' => 'Action', 'type' => 'select', 'options' => $actions],
                ['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => $months, 'placeholder' => ''],
                ['name' => 'year', 'label' => 'Year', 'type' => 'select', 'options' => $years, 'placeholder' => '', 'default' => (string) $year],
            ],
            'canCreate' => false,
        ]);
    }
}
