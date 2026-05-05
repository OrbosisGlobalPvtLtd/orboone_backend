<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeCredentialMail extends Mailable
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
            ->view('emails.employee_credentials');
    }

    
}
