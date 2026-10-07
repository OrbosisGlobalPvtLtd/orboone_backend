<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Leave\LeaveRequestM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoExpireLeaveService
{
    /**
     * Auto-expire any pending leave requests whose start date has passed.
     *
     * @return int Number of leave requests auto-expired
     */
    public function expirePastPendingRequests(?int $employeeId = null): int
    {
        $todayStr = Carbon::today('Asia/Kolkata')->toDateString();

        $query = LeaveRequestM::where('status', 'pending')
            ->whereDate('end_date', '<', $todayStr);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $affectedCount = $query->update([
            'status' => 'expired',
            'rejection_reason' => 'Auto-expired due to non-approval prior to leave date.',
        ]);

        if ($affectedCount > 0) {
            Log::info("AutoExpireLeaveService: {$affectedCount} pending leave requests marked as expired (< {$todayStr}).");
        }

        return $affectedCount;
    }
}
