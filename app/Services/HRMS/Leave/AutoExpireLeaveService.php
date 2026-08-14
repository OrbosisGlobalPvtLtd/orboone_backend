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
            ->whereDate('start_date', '<', $todayStr);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $pendingRequests = $query->get();
        $expiredCount = 0;

        foreach ($pendingRequests as $req) {
            try {
                $req->status = 'expired';
                $req->rejection_reason = 'Auto-expired due to non-approval prior to leave date.';
                $req->save();

                $expiredCount++;

                Log::info("LeaveRequest #{$req->id} for Employee #{$req->employee_id} auto-expired (start_date: {$req->start_date?->toDateString()}).");
            } catch (\Throwable $e) {
                Log::error("Failed to auto-expire LeaveRequest #{$req->id}: " . $e->getMessage());
            }
        }

        return $expiredCount;
    }
}
