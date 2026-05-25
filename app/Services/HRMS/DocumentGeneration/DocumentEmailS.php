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
        if (!$document->pdf_path || !Storage::disk('local')->exists($document->pdf_path)) {
            throw new \Exception("PDF document does not exist and cannot be sent.");
        }

        $pdfFile = Storage::disk('local')->path($document->pdf_path);

        // Simple closure based mail sending
        Mail::send([], [], function ($message) use ($emailTo, $subject, $body, $pdfFile, $document) {
            $message->to($emailTo)
                ->subject($subject)
                ->html($body)
                ->attach($pdfFile, [
                    'as' => basename($document->pdf_path),
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
