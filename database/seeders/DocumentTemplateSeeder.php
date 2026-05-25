<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use Illuminate\Support\Str;

class DocumentTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Standard Offer Letter',
                'document_type' => 'offer_letter',
                'html_template' => $this->getOfferLetterTemplate(),
                'requires_review' => true,
            ],
            [
                'name' => 'Standard Appointment Letter',
                'document_type' => 'appointment_letter',
                'html_template' => $this->getAppointmentLetterTemplate(),
            ],
            [
                'name' => 'Probation Confirmation Letter',
                'document_type' => 'confirmation_letter',
                'html_template' => $this->getConfirmationLetterTemplate(),
            ],
            [
                'name' => 'Standard Relieving Letter',
                'document_type' => 'relieving_letter',
                'html_template' => $this->getRelievingLetterTemplate(),
            ],
            [
                'name' => 'Experience Certificate',
                'document_type' => 'experience_certificate',
                'html_template' => $this->getExperienceCertificateTemplate(),
                'is_certificate' => true,
            ],
            [
                'name' => 'Internship Certificate',
                'document_type' => 'internship_certificate',
                'html_template' => $this->getInternshipCertificateTemplate(),
                'is_certificate' => true,
            ],
            [
                'name' => 'Salary Revision Letter',
                'document_type' => 'salary_revision_letter',
                'html_template' => $this->getSalaryRevisionLetterTemplate(),
            ],
            [
                'name' => 'Warning Letter',
                'document_type' => 'warning_letter',
                'html_template' => $this->getWarningLetterTemplate(),
            ]
        ];

        foreach ($templates as $template) {
            if (!DocumentTemplate::where('document_type', $template['document_type'])->exists()) {
                $template['slug'] = Str::slug($template['name']) . '-' . time();
                DocumentTemplate::create($template);
            }
        }
    }

    private function getOfferLetterTemplate()
    {
        return '<h2>Offer Letter</h2><p>Date: {{current_date}}</p><p>Dear {{employee_name}},</p><p>We are pleased to offer you the position of {{designation}} at {{company_name}}.</p><p>Your gross monthly salary will be {{salary_monthly}} (Annual: {{salary_annual}}).</p><p>Please let us know your acceptance.</p><p>Sincerely,<br>{{hr_name}}</p>';
    }

    private function getAppointmentLetterTemplate()
    {
        return '<h2>Appointment Letter</h2><p>Date: {{current_date}}</p><p>Dear {{employee_name}},</p><p>Welcome to {{company_name}}. We are pleased to appoint you as {{designation}}.</p><p>Your joining date is {{joining_date}}.</p>';
    }

    private function getConfirmationLetterTemplate()
    {
        return '<h2>Confirmation Letter</h2><p>Date: {{current_date}}</p><p>Dear {{employee_name}},</p><p>We are pleased to inform you that you have successfully completed your probation period and are now confirmed as a permanent employee of {{company_name}}.</p><p>Warm regards,<br>{{hr_name}}</p>';
    }

    private function getRelievingLetterTemplate()
    {
        return '<h2>Relieving Letter</h2><p>Date: {{current_date}}</p><p>To Whomsoever It May Concern</p><p>This is to certify that {{employee_name}} was employed with {{company_name}} as {{designation}}.</p><p>They have been relieved from their duties successfully.</p>';
    }

    private function getExperienceCertificateTemplate()
    {
        return '<div style="text-align:center;"><h2>Experience Certificate</h2><p>This is to certify that {{employee_name}} has worked with {{company_name}} as {{designation}}.</p><p>Date: {{current_date}}</p></div>';
    }

    private function getInternshipCertificateTemplate()
    {
        return '<div style="text-align:center; padding: 50px; border: 5px solid #4B00E8;"><h1>Certificate of Internship</h1><p>Awarded to</p><h2>{{employee_name}}</h2><p>For successful completion of internship at {{company_name}}.</p><p>Date: {{current_date}}</p></div>';
    }

    private function getSalaryRevisionLetterTemplate()
    {
        return '<h2>Salary Revision</h2><p>Date: {{current_date}}</p><p>Dear {{employee_name}},</p><p>We are pleased to inform you that your revised monthly salary is {{salary_monthly}}.</p>';
    }

    private function getWarningLetterTemplate()
    {
        return '<h2>Warning Letter</h2><p>Date: {{current_date}}</p><p>Dear {{employee_name}},</p><p>This letter is to inform you about your conduct.</p>';
    }
}
