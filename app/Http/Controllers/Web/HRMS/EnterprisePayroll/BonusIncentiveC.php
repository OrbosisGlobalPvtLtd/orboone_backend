<?php

namespace App\Http\Controllers\Web\HRMS\EnterprisePayroll;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\EnterprisePayroll\EnterpriseBonusIncentiveM;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BonusIncentiveC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private NotificationS $notificationService)
    {
    }

    public function index()
    {
        $rows = $this->employeeJoinedQuery('enterprise_bonus_incentives')
            ->orderByDesc('enterprise_bonus_incentives.created_at')
            ->get();

        return view('hrms.enterprise-payroll.bonus-incentives.index', [
            'accesses' => $this->accesses(),
            'active' => 'enterprise_payroll',
            'rows' => $rows,
            'employees' => $this->employeeOptions(),
            'canManage' => $this->userHasPermission('enterprise_bonus_incentive.manage'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees_new,id'],
            'type' => ['required', 'in:bonus,incentive'],
            'title' => ['required', 'string', 'max:255'],
            'target_range' => ['nullable', 'string', 'max:80'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'achievement_amount' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['required', 'numeric', 'min:0'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'remarks' => ['nullable', 'string'],
        ]);

        EnterpriseBonusIncentiveM::create($data + ['status' => 'pending']);

        return back()->with('success', 'Bonus or incentive entry saved.');
    }

    public function update(Request $request, int $bonusIncentive)
    {
        $record = EnterpriseBonusIncentiveM::findOrFail($bonusIncentive);
        abort_if($record->status === 'paid', 403, 'Paid bonus/incentive cannot be edited.');
        $record->update($request->only(['title', 'target_range', 'target_amount', 'achievement_amount', 'amount', 'remarks']));

        return back()->with('success', 'Bonus or incentive updated.');
    }

    public function approve(EnterpriseBonusIncentiveM $bonusIncentive)
    {
        $bonusIncentive->update([
            'status' => 'approved',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => Carbon::now('Asia/Kolkata'),
        ]);

        $this->notificationService->notifyEmployee(
            ucfirst($bonusIncentive->type) . ' Approved',
            "Your {$bonusIncentive->type} amount has been approved.",
            'enterprise_bonus_approved',
            'enterprise-payroll.self.payslips',
            [],
            ['bonus_incentive_id' => $bonusIncentive->id],
            optional($bonusIncentive->employee->user)->id
        );

        return back()->with('success', 'Bonus or incentive approved.');
    }

    public function reject(EnterpriseBonusIncentiveM $bonusIncentive)
    {
        $bonusIncentive->update(['status' => 'rejected']);

        return back()->with('success', 'Bonus or incentive rejected.');
    }
}
