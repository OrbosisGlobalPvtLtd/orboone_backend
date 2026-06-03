<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HrWorkflowAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectText,
        public string $workflowTitle,
        public array $details = [],
        public ?string $actionUrl = null,
        public ?string $replyToEmail = null
    ) {
    }

    public function build()
    {
        $bName = branding_name();
        $mail = $this->subject($bName . ' - ' . $this->subjectText)
            ->from(config('hrms.emails.noreply'), $bName)
            ->view('emails.hr_workflow_alert');

        if ($this->replyToEmail) {
            $mail->replyTo($this->replyToEmail);
        }

        return $mail;
    }
}

