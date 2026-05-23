<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterpriseFnfSettlementM;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FnfSettlementC extends Controller
{
    use HrmsCrudPage;

    public function index()
    {
        $rows = $this->employeeJoinedQuery('enterprise_fnf_settlements')
            ->orderByDesc('enterprise_fnf_settlements.created_at')
            ->get();

        return view('hrms.enterprise-payroll.fnf.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'rows' => $rows,
            'employees' => $this->employeeOptions(),
            'canManage' => $this->userHasPermission('enterprise_fnf.manage'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees_new,id'],
            'exit_process_id' => ['nullable', 'integer'],
            'settlement_month' => ['required', 'integer', 'between:1,12'],
            'settlement_year' => ['required', 'integer', 'min:2020'],
            'pending_salary' => ['nullable', 'numeric', 'min:0'],
            'leave_encashment' => ['nullable', 'numeric', 'min:0'],
            'reimbursement_amount' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data['pending_salary'] = (float) ($data['pending_salary'] ?? 0);
        $data['leave_encashment'] = (float) ($data['leave_encashment'] ?? 0);
        $data['reimbursement_amount'] = (float) ($data['reimbursement_amount'] ?? 0);
        $data['deductions'] = (float) ($data['deductions'] ?? 0);
        $data['final_payable'] = $data['pending_salary'] + $data['leave_encashment'] + $data['reimbursement_amount'] - $data['deductions'];
        $data['status'] = 'draft';

        EnterpriseFnfSettlementM::create($data);

        return back()->with('success', 'FNF settlement saved.');
    }

    public function approve(EnterpriseFnfSettlementM $settlement)
    {
        abort_if($settlement->status === 'locked', 403, 'Locked FNF cannot be edited.');
        $settlement->update([
            'status' => 'approved',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => Carbon::now('Asia/Kolkata'),
        ]);

        return back()->with('success', 'FNF settlement approved.');
    }
}
