<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QueuedDocumentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $bodyText;
    public $pdfFile;
    public $pdfName;
    public $fromAddress;
    public $fromName;
    public $ccEmail;

    public function __construct(
        string $subjectText,
        string $bodyText,
        string $pdfFile,
        string $pdfName,
        ?string $fromAddress = null,
        ?string $fromName = null,
        ?string $ccEmail = null
    ) {
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
        $this->pdfFile = $pdfFile;
        $this->pdfName = $pdfName;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->ccEmail = $ccEmail;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectText)
            ->html($this->bodyText);

        if ($this->fromAddress) {
            $mail->from($this->fromAddress, $this->fromName ?: 'HR Team');
        }

        if ($this->ccEmail) {
            $mail->cc($this->ccEmail);
        }

        if (is_file($this->pdfFile)) {
            $mail->attach($this->pdfFile, [
                'as' => $this->pdfName,
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Queued document mail failed during queue processing', [
            'subject' => $this->subjectText,
            'pdf_file' => $this->pdfFile,
            'error' => $exception->getMessage(),
        ]);
    }
}
