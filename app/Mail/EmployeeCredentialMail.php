<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmployeeCredentialMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
  public $name;
    public $email;
    public $empid;
    public $password;
    public $passwordSetupUrl;

    public function __construct($name,$email,$empid,$password,$passwordSetupUrl = null)
    {
        $this->name=$name;
        $this->email=$email;
        $this->empid=$empid;
        $this->password=$password;
        $this->passwordSetupUrl=$passwordSetupUrl;
    }

    public function build()
    {
        return $this->subject('Employee Login Credentials')
            ->from(config('hrms.emails.noreply'), config('mail.from.name'))
            ->view('emails.employee_credentials');
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Employee credential mail failed during queue processing', [
            'email' => $this->email,
            'employee_code' => $this->empid,
            'error' => $exception->getMessage(),
        ]);
    }

    
}
