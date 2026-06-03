<?php

namespace App\Mail\HRMS\EnterprisePayroll;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $employee;
    public $run;
    public $payslip;
    public $pdfPath;

    /**
     * Create a new message instance.
     *
     * @param mixed $employee
     * @param mixed $run
     * @param mixed $payslip
     * @param string $pdfPath
     */
    public function __construct($employee, $run, $payslip, $pdfPath)
    {
        $this->employee = $employee;
        $this->run = $run;
        $this->payslip = $payslip;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $monthName = \Carbon\Carbon::create(null, $this->payslip->month)->format('F');
        $fileName = ($this->employee->employee_code ?? 'OG-EMP-' . $this->employee->id) . '_PAYSLIP_' . $monthName . '_' . $this->payslip->year . '.pdf';

        return $this->subject('Payslip for ' . $monthName . ' ' . $this->payslip->year)
            ->view('emails.enterprise_payslip')
            ->with([
                'employee' => $this->employee,
                'run' => $this->run,
                'payroll' => $this->payslip->payroll,
                'payslip' => $this->payslip,
            ])
            ->attach($this->pdfPath, [
                'as' => $fileName,
                'mime' => 'application/pdf',
            ]);
    }
}
