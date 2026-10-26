<?php

namespace App\Services\HRMS\Leave;

use App\Models\HRMS\Leave\LeaveRequestM;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoExpireLeaveService
{
    /**
     * Auto-expire any pending leave requests whose end date has passed.
     * Note: Leaves whose start date has passed but end date is today or future are NOT expired.
     * Only requests whose entire duration has ended (end_date < today) are marked as expired.
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
            'rejection_reason' => 'Auto-expired: Leave end date has passed without approval.',
            'updated_at' => Carbon::now('Asia/Kolkata'),
        ]);

        if ($affectedCount > 0) {
            Log::info("AutoExpireLeaveService: {$affectedCount} pending leave requests marked as expired (end_date < {$todayStr}).");
        }

        return $affectedCount;
    }
}
