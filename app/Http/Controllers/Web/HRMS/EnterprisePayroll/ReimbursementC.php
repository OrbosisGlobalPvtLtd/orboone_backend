<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterpriseReimbursementM;
use App\Services\HRMS\Notification\NotificationS;
use App\Services\HRMS\Storage\HrmsStoragePathS;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReimbursementC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private NotificationS $notificationService,
        private HrmsStoragePathS $paths
    )
    {
    }

    public function index()
    {
        $query = $this->employeeJoinedQuery('enterprise_reimbursements')
            ->orderByDesc('enterprise_reimbursements.created_at');

        if (! $this->userHasPermission('enterprise_reimbursement.manage')) {
            $employeeId = $this->ownEmployeeId();
            abort_if(! $employeeId, 403);
            $query->where('enterprise_reimbursements.employee_id', $employeeId);
        }

        return view('hrms.enterprise-payroll.reimbursements.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'rows' => $query->get(),
            'employees' => $this->employeeOptions(),
            'canManage' => $this->userHasPermission('enterprise_reimbursement.manage'),
            'self' => false,
        ]);
    }

    public function self()
    {
        $employeeId = $this->ownEmployeeId();
        abort_if(! $employeeId, 403);

        return view('hrms.enterprise-payroll.reimbursements.index', [
            'accesses' => $this->accesses(),
            'active' => 'employee.salary',
            'rows' => $this->employeeJoinedQuery('enterprise_reimbursements')
                ->where('enterprise_reimbursements.employee_id', $employeeId)
                ->orderByDesc('enterprise_reimbursements.created_at')
                ->get(),
            'employees' => collect(),
            'canManage' => false,
            'self' => true,
        ]);
    }

    public function store(Request $request)
    {
        $employeeId = $this->userHasPermission('enterprise_reimbursement.manage')
            ? $request->input('employee_id')
            : $this->ownEmployeeId();

        $data = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees_new,id'],
            'title' => ['required', 'string', 'max:255'],
            'claim_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'max:5120'],
            'remarks' => ['nullable', 'string'],
        ]);

        abort_if(! $employeeId, 403);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store($this->paths->employeePayroll((int) $employeeId, 'reimbursements'), 'private');
        }

        EnterpriseReimbursementM::create([
            'employee_id' => $employeeId,
            'title' => $data['title'],
            'claim_date' => $data['claim_date'],
            'amount' => $data['amount'],
            'approved_amount' => $data['approved_amount'] ?? 0,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'remarks' => $data['remarks'] ?? null,
        ]);

        return back()->with('success', 'Reimbursement submitted.');
    }

    public function update(Request $request, int $reimbursement)
    {
        $record = EnterpriseReimbursementM::findOrFail($reimbursement);
        abort_if($record->status === 'paid', 403, 'Paid reimbursement cannot be edited.');
        $record->update($request->only(['title', 'claim_date', 'amount', 'approved_amount', 'remarks']));

        return back()->with('success', 'Reimbursement updated.');
    }

    public function approve(Request $request, EnterpriseReimbursementM $reimbursement)
    {
        $data = $request->validate(['approved_amount' => ['nullable', 'numeric', 'min:0']]);

        $reimbursement->update([
            'status' => 'approved',
            'approved_amount' => $data['approved_amount'] ?? $reimbursement->amount,
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => Carbon::now('Asia/Kolkata'),
        ]);

        $this->notificationService->notifyEmployee(
            'Reimbursement Approved',
            'Your reimbursement claim has been approved.',
            'enterprise_reimbursement_approved',
            'enterprise-payroll.self.reimbursements',
            [],
            ['reimbursement_id' => $reimbursement->id],
            optional($reimbursement->employee->user)->id
        );

        return back()->with('success', 'Reimbursement approved.');
    }

    public function reject(Request $request, EnterpriseReimbursementM $reimbursement)
    {
        $data = $request->validate(['rejection_reason' => ['nullable', 'string']]);

        $reimbursement->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        $this->notificationService->notifyEmployee(
            'Reimbursement Rejected',
            'Your reimbursement claim has been rejected.',
            'enterprise_reimbursement_rejected',
            'enterprise-payroll.self.reimbursements',
            [],
            ['reimbursement_id' => $reimbursement->id],
            optional($reimbursement->employee->user)->id
        );

        return back()->with('success', 'Reimbursement rejected.');
    }
}
