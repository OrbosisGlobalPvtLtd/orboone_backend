<?php

namespace App\Http\Resources\HRMS\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class HolidayWorkRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = (string) $this->status;
        $lifecycleStatus = $status;
        $lifecycleLabel = ucfirst($status);

        if ($status === 'approved' && ! $this->comp_off_generated) {
            $lifecycleStatus = 'approved_awaiting_work_completion';
            $lifecycleLabel = 'Approved Awaiting Work Completion';
        } elseif ($status === 'approved' && $this->comp_off_generated) {
            $lifecycleStatus = 'comp_off_generated';
            $lifecycleLabel = 'Comp-Off Generated';
        } elseif ($status === 'rejected') {
            $lifecycleLabel = 'Rejected';
        } elseif ($status === 'pending') {
            $lifecycleLabel = 'Pending Approval';
        }

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'attendance_id' => $this->attendance_id,
            'worked_date' => $this->worked_date ? $this->worked_date->toDateString() : null,
            'work_type' => $this->work_type,
            'work_mode' => $this->work_mode,
            'comp_off_generated' => (bool) $this->comp_off_generated,
            'comp_off_id' => $this->comp_off_id,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'attachment_path' => $this->attachment_path ?? null,
            'status' => $status,
            'lifecycle_status' => $lifecycleStatus,
            'lifecycle_label' => $lifecycleLabel,
            'approved_by_user_id' => $this->approved_by_user_id,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
