<?php

namespace App\Http\Resources\HRMS\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRegularizationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'attendance_id' => $this->attendance_id,
            'request_type' => $this->request_type,
            'existing_punch_in' => $this->existing_punch_in,
            'existing_punch_out' => $this->existing_punch_out,
            'requested_punch_in' => $this->requested_punch_in,
            'requested_punch_out' => $this->requested_punch_out,
            'reason' => $this->reason,
            'attachment_path' => $this->attachment_path,
            'status' => $this->status,
            'approved_by_user_id' => $this->approved_by_user_id,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

