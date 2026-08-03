<?php

namespace App\Http\Resources\HRMS\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRegularizationResource extends JsonResource
{
    public function toArray($request): array
    {
        $attendanceDate = $this->attendance?->attendance_date
            ? \Carbon\Carbon::parse($this->attendance->attendance_date)->toDateString()
            : ($this->requested_punch_in ? \Carbon\Carbon::parse($this->requested_punch_in)->toDateString() : \Carbon\Carbon::parse($this->created_at)->toDateString());

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'attendance_id' => $this->attendance_id,
            'attendance_date' => $attendanceDate,
            'request_type' => $this->request_type,
            'existing_punch_in' => $this->existing_punch_in,
            'existing_punch_out' => $this->existing_punch_out,
            'requested_punch_in' => $this->requested_punch_in,
            'requested_punch_out' => $this->requested_punch_out,
            'work_mode' => $this->attendance?->work_mode ?? $this->employee?->work_mode ?? 'wfo',
            'reason' => $this->reason,
            'attachment_path' => $this->attachment_path,
            'status' => $this->status,
            'approved_by_user_id' => $this->approved_by_user_id,
            'approved_by_name' => $this->approvedBy?->name ?? $this->approvedBy?->full_name,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

