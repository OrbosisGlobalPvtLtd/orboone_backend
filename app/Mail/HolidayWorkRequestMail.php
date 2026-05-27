<?php

namespace App\Mail;

use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HolidayWorkRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $holidayRequest;
    public $status;
    public $actionUrl;
    public $rejectionReason;
    public $reviewerName;

    public function __construct(
        HolidayWorkRequestM $holidayRequest,
        string $status,
        ?string $actionUrl = null,
        ?string $rejectionReason = null,
        ?string $reviewerName = null
    ) {
        $this->holidayRequest = $holidayRequest;
        $this->status = strtolower($status);
        $this->actionUrl = $actionUrl;
        $this->rejectionReason = $rejectionReason;
        $this->reviewerName = $reviewerName;
    }

    public function build()
    {
        $employee = $this->holidayRequest->employee;
        $employeeName = $employee ? $employee->display_name : 'Employee';
        $employeeCode = $employee ? $employee->employee_code : 'N/A';
        $department = $employee && $employee->department ? $employee->department->name : 'N/A';
        $workedDate = $this->holidayRequest->worked_date 
            ? Carbon::parse($this->holidayRequest->worked_date)->format('d M Y') 
            : 'N/A';
        
        $workTypeMap = [
            'holiday_work' => 'Holiday Work',
            'weekoff_work' => 'Weekoff Work',
            'holiday' => 'Holiday Work',
            'weekoff' => 'Weekoff Work',
        ];
        $workType = $workTypeMap[strtolower($this->holidayRequest->work_type)] ?? 'Holiday Work';
        $workMode = strtoupper($this->holidayRequest->work_mode ?? 'WFO');
        $reason = $this->holidayRequest->reason;

        // Set subject based on status
        if ($this->status === 'submitted') {
            $subjectText = "New Work Request Submitted - {$employeeName}";
        } elseif ($this->status === 'approved') {
            $subjectText = "Work Request Approved";
        } elseif ($this->status === 'rejected') {
            $subjectText = "Work Request Rejected";
        } else {
            $subjectText = "Work Request Update";
        }

        return $this->subject($subjectText)
            ->view('emails.holiday_work_request')
            ->with([
                'employee_name' => $employeeName,
                'employee_code' => $employeeCode,
                'department' => $department,
                'worked_date' => $workedDate,
                'work_type' => $workType,
                'work_mode' => $workMode,
                'reason' => $reason,
                'rejection_reason' => $this->rejectionReason,
                'reviewer_name' => $this->reviewerName,
                'action_url' => $this->actionUrl,
                'status' => $this->status,
                'subject' => $subjectText,
            ]);
    }
}
