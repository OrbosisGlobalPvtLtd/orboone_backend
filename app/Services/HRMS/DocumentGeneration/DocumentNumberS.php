<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use Carbon\Carbon;

class DocumentNumberS
{
    /**
     * Get prefix for document type.
     */
    public function getPrefix(string $documentType): string
    {
        $map = [
            'offer_letter' => 'OFF',
            'appointment_letter' => 'APP',
            'experience_letter' => 'EXP',
            'relieving_letter' => 'REL',
            'internship_certificate' => 'INT',
            'internship_offer_letter' => 'IOL',
            'discontinuing_letter' => 'DIS',
            'salary_certificate' => 'SAL',
            'warning_letter' => 'WRN',
            'appreciation_letter' => 'APR',
            'nda_agreement' => 'NDA',
        ];

        return $map[$documentType] ?? strtoupper(substr(str_replace('_', '', $documentType), 0, 3));
    }

    /**
     * Generate unique document number.
     */
    public function generate(string $documentType): string
    {
        $prefix = $this->getPrefix($documentType);
        $datePart = Carbon::now()->format('Ym');

        do {
            $randomPart = str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $docNo = "{$prefix}-{$datePart}-{$randomPart}";
        } while (GeneratedDocument::where('document_number', $docNo)->exists());

        return $docNo;
    }
}
