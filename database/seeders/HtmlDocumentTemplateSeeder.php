<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;

class HtmlDocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Offer Letter',
                'slug' => 'offer-letter',
                'document_type' => 'offer_letter',
                'html_template' => 'hrms.document-generation.pdf-templates.offer-letter',
                'description' => 'Official job offer letter containing probation clauses, CTC breakdown, and roles.',
            ],
            [
                'name' => 'Appointment Letter',
                'slug' => 'appointment-letter',
                'document_type' => 'appointment_letter',
                'html_template' => 'hrms.document-generation.pdf-templates.appointment-letter',
                'description' => 'Detailed employee appointment contract including detailed notice periods and general code of conduct.',
            ],
            [
                'name' => 'Experience Letter',
                'slug' => 'experience-letter',
                'document_type' => 'experience_letter',
                'html_template' => 'hrms.document-generation.pdf-templates.experience-letter',
                'description' => 'Relieving and experience certificate detailing historical roles, joining, and exit dates.',
            ],
            [
                'name' => 'Relieving Letter',
                'slug' => 'relieving-letter',
                'document_type' => 'relieving_letter',
                'html_template' => 'hrms.document-generation.pdf-templates.relieving-letter',
                'description' => 'Relieving letter confirming asset returns, final handovers, and clear settlement clearance.',
            ],
            [
                'name' => 'Internship Certificate',
                'slug' => 'internship-certificate',
                'document_type' => 'internship_certificate',
                'html_template' => 'hrms.document-generation.pdf-templates.internship-certificate',
                'description' => 'Certificate issued upon successful completion of intern training projects.',
            ],
            [
                'name' => 'Salary Certificate',
                'slug' => 'salary-certificate',
                'document_type' => 'salary_certificate',
                'html_template' => 'hrms.document-generation.pdf-templates.salary-certificate',
                'description' => 'Certified monthly and annual salary statements issued for bank loans or visa applications.',
            ],
            [
                'name' => 'Warning Letter',
                'slug' => 'warning-letter',
                'document_type' => 'warning_letter',
                'html_template' => 'hrms.document-generation.pdf-templates.warning-letter',
                'description' => 'Formal disciplinary warning letter detailing performance gaps or policy infractions.',
            ],
            [
                'name' => 'Appreciation Letter',
                'slug' => 'appreciation-letter',
                'document_type' => 'appreciation_letter',
                'html_template' => 'hrms.document-generation.pdf-templates.appreciation-letter',
                'description' => 'Certificate recognizing outstanding team accomplishments and project contributions.',
            ],
            [
                'name' => 'NDA Agreement',
                'slug' => 'nda-agreement',
                'document_type' => 'nda_agreement',
                'html_template' => 'hrms.document-generation.pdf-templates.nda-agreement',
                'description' => 'Non-disclosure agreement defining confidentiality periods, intellectual property, and restrictive clauses.',
            ],
        ];

        foreach ($templates as $temp) {
            DocumentTemplate::updateOrCreate(
                ['slug' => $temp['slug']],
                [
                    'name' => $temp['name'],
                    'document_type' => $temp['document_type'],
                    'template_type' => 'html',
                    'category' => 'HR Documents',
                    'description' => $temp['description'],
                    'html_template' => $temp['html_template'],
                    'is_active' => true,
                    'version' => 'v1',
                ]
            );
        }
    }
}
