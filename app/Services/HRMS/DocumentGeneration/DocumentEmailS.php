<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\HRMS\Notification\NotificationS; // Use existing if needed
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DocumentEmailS
{
    public function sendDocument(GeneratedDocument $document, string $emailTo, string $subject, string $body)
    {
        $pdfPath = $document->generated_pdf_path ?: $document->pdf_path;
        if (!$pdfPath || !Storage::disk('private')->exists($pdfPath)) {
            throw new \Exception("PDF document does not exist and cannot be sent.");
        }

        $pdfFile = Storage::disk('private')->path($pdfPath);

        // Simple closure based mail sending
        Mail::send([], [], function ($message) use ($emailTo, $subject, $body, $pdfFile, $document) {
            $message->to($emailTo)
                ->subject($subject)
                ->html($body)
                ->attach($pdfFile, [
                    'as' => basename($pdfPath),
                    'mime' => 'application/pdf',
                ]);
        });

        $document->update([
            'status' => 'sent',
            'sent_at' => Carbon::now(),
            'sent_by_user_id' => Auth::id() ?? 1,
            'email_to' => $emailTo,
            'email_subject' => $subject,
            'email_body' => $body,
        ]);

        // Create log
        $document->logs()->create([
            'action' => 'sent',
            'remarks' => "Emailed to {$emailTo}",
            'actor_user_id' => Auth::id() ?? 1,
        ]);

        return true;
    }
}
