<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentPlaceholderSeeder extends Seeder
{
    public function run()
    {
        $placeholders = [
            // Employee Group
            ['placeholder_key' => 'employee_name', 'label' => 'Employee Full Name', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'employee_name', 'resolver_key' => 'employee_name'],
            ['placeholder_key' => 'employee_first_name', 'label' => 'Employee First Name', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'employee_name', 'resolver_key' => 'employee_first_name'],
            ['placeholder_key' => 'employee_address', 'label' => 'Employee Address', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employee_profiles', 'source_column' => 'present_address', 'resolver_key' => 'employee_address'],
            ['placeholder_key' => 'employee_city', 'label' => 'Employee City', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employee_profiles', 'source_column' => 'city', 'resolver_key' => 'employee_city'],
            ['placeholder_key' => 'employee_gender_title', 'label' => 'Employee Gender Title (Mr./Ms.)', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'gender', 'resolver_key' => 'employee_gender_title'],
            ['placeholder_key' => 'designation', 'label' => 'Designation', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'designations', 'source_column' => 'name', 'resolver_key' => 'designation'],
            ['placeholder_key' => 'department', 'label' => 'Department', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'departments', 'source_column' => 'name', 'resolver_key' => 'department'],
            ['placeholder_key' => 'joining_date', 'label' => 'Joining Date', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'joining_date', 'resolver_key' => 'joining_date'],
            ['placeholder_key' => 'relieving_date', 'label' => 'Relieving Date', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employee_exit_processes', 'source_column' => 'relieving_date', 'resolver_key' => 'relieving_date'],
            ['placeholder_key' => 'internship_start_date', 'label' => 'Internship Start Date', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'joining_date', 'resolver_key' => 'internship_start_date'],
            ['placeholder_key' => 'internship_end_date', 'label' => 'Internship End Date', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'exit_date', 'resolver_key' => 'internship_end_date'],
            ['placeholder_key' => 'probation_period', 'label' => 'Probation Period', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'probation_period', 'resolver_key' => 'probation_period'],
            ['placeholder_key' => 'notice_period_probation', 'label' => 'Notice Period (Probation)', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'notice_period_probation', 'resolver_key' => 'notice_period_probation'],
            ['placeholder_key' => 'notice_period_confirmed', 'label' => 'Notice Period (Confirmed)', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'notice_period_confirmed', 'resolver_key' => 'notice_period_confirmed'],
            ['placeholder_key' => 'office_location', 'label' => 'Office Location', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employee_profiles', 'source_column' => 'work_location', 'resolver_key' => 'office_location'],
            ['placeholder_key' => 'employee_code', 'label' => 'Employee Code', 'group_name' => 'Employee', 'source_type' => 'employee', 'source_table' => 'employees_new', 'source_column' => 'employee_code', 'resolver_key' => 'employee_code'],
            ['placeholder_key' => 'discontinue_date', 'label' => 'Discontinue Date', 'group_name' => 'Document', 'source_type' => 'document', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'discontinue_date'],
            ['placeholder_key' => 'discontinue_reason', 'label' => 'Discontinue Reason', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'discontinue_reason'],
            ['placeholder_key' => 'handover_clause', 'label' => 'Handover Clause', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'handover_clause'],

            // Company Group
            ['placeholder_key' => 'company_name', 'label' => 'Company Name', 'group_name' => 'Company', 'source_type' => 'company', 'source_table' => 'company_settings', 'source_column' => 'company_name', 'resolver_key' => 'company_name'],
            ['placeholder_key' => 'company_address', 'label' => 'Company Address', 'group_name' => 'Company', 'source_type' => 'company', 'source_table' => 'company_settings', 'source_column' => 'address', 'resolver_key' => 'company_address'],
            ['placeholder_key' => 'company_city', 'label' => 'Company City', 'group_name' => 'Company', 'source_type' => 'company', 'source_table' => 'company_settings', 'source_column' => 'city', 'resolver_key' => 'company_city'],
            ['placeholder_key' => 'company_phone', 'label' => 'Company Phone', 'group_name' => 'Company', 'source_type' => 'company', 'source_table' => 'company_settings', 'source_column' => 'phone', 'resolver_key' => 'company_phone'],
            ['placeholder_key' => 'company_email', 'label' => 'Company Email', 'group_name' => 'Company', 'source_type' => 'company', 'source_table' => 'company_settings', 'source_column' => 'email', 'resolver_key' => 'company_email'],
            ['placeholder_key' => 'company_website', 'label' => 'Company Website', 'group_name' => 'Company', 'source_type' => 'company', 'source_table' => 'company_settings', 'source_column' => 'website', 'resolver_key' => 'company_website'],
            ['placeholder_key' => 'company_gstin', 'label' => 'Company GSTIN', 'group_name' => 'Company', 'source_type' => 'company', 'source_table' => 'company_settings', 'source_column' => 'gstin', 'resolver_key' => 'company_gstin'],

            // Document Group
            ['placeholder_key' => 'issue_date', 'label' => 'Document Issue Date', 'group_name' => 'Document', 'source_type' => 'document', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'issue_date'],
            ['placeholder_key' => 'offer_valid_till', 'label' => 'Offer Valid Till Date', 'group_name' => 'Document', 'source_type' => 'document', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'offer_valid_till'],
            ['placeholder_key' => 'document_title', 'label' => 'Document Title', 'group_name' => 'Document', 'source_type' => 'document', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'document_title'],
            ['placeholder_key' => 'generated_date', 'label' => 'Document Generated Date', 'group_name' => 'Document', 'source_type' => 'document', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'generated_date'],

            // Salary Group
            ['placeholder_key' => 'monthly_gross_salary', 'label' => 'Monthly Gross Salary', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => 'enterprise_salary_structures', 'source_column' => 'gross_salary', 'resolver_key' => 'monthly_gross_salary'],
            ['placeholder_key' => 'annual_gross_salary', 'label' => 'Annual Gross Salary', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => 'enterprise_salary_structures', 'source_column' => null, 'resolver_key' => 'annual_gross_salary'],
            ['placeholder_key' => 'gross_monthly_salary', 'label' => 'Gross Monthly Salary', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'gross_monthly_salary'],
            ['placeholder_key' => 'gross_annual_salary', 'label' => 'Gross Annual Salary', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'gross_annual_salary'],
            ['placeholder_key' => 'basic_monthly', 'label' => 'Basic Monthly Salary', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'basic_monthly'],
            ['placeholder_key' => 'basic_annual', 'label' => 'Basic Annual Salary', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'basic_annual'],
            ['placeholder_key' => 'hra_monthly', 'label' => 'HRA Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'hra_monthly'],
            ['placeholder_key' => 'hra_annual', 'label' => 'HRA Annual', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'hra_annual'],
            ['placeholder_key' => 'conveyance_monthly', 'label' => 'Conveyance Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'conveyance_monthly'],
            ['placeholder_key' => 'allowance_monthly', 'label' => 'Other Allowance Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'allowance_monthly'],
            ['placeholder_key' => 'special_allowance_monthly', 'label' => 'Special Allowance Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'special_allowance_monthly'],
            ['placeholder_key' => 'special_allowance_annual', 'label' => 'Special Allowance Annual', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'special_allowance_annual'],
            ['placeholder_key' => 'subtotal_a_monthly', 'label' => 'Subtotal A Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'subtotal_a_monthly'],
            ['placeholder_key' => 'subtotal_a_annual', 'label' => 'Subtotal A Annual', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'subtotal_a_annual'],
            ['placeholder_key' => 'professional_tax_monthly', 'label' => 'Professional Tax Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'professional_tax_monthly'],
            ['placeholder_key' => 'professional_tax_annual', 'label' => 'Professional Tax Annual', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'professional_tax_annual'],
            ['placeholder_key' => 'subtotal_b_monthly', 'label' => 'Subtotal B Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'subtotal_b_monthly'],
            ['placeholder_key' => 'subtotal_b_annual', 'label' => 'Subtotal B Annual', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'subtotal_b_annual'],
            ['placeholder_key' => 'ctc_monthly', 'label' => 'CTC Monthly', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'ctc_monthly'],
            ['placeholder_key' => 'ctc_annual', 'label' => 'CTC Annual', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'ctc_annual'],
            ['placeholder_key' => 'net_pay_monthly', 'label' => 'Net Pay Monthly (Take Home)', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'net_pay_monthly'],
            ['placeholder_key' => 'net_pay_annual', 'label' => 'Net Pay Annual', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'net_pay_annual'],
            ['placeholder_key' => 'annual_ctc', 'label' => 'Annual CTC', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'annual_ctc'],
            ['placeholder_key' => 'salary_in_words', 'label' => 'Salary in Words', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'salary_in_words'],
            ['placeholder_key' => 'salary', 'label' => 'Gross Salary', 'group_name' => 'Salary', 'source_type' => 'salary', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'salary'],

            // Authority Group
            ['placeholder_key' => 'hr_manager_name', 'label' => 'HR Manager Name', 'group_name' => 'Authority', 'source_type' => 'authority', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'hr_manager_name'],
            ['placeholder_key' => 'ceo_name', 'label' => 'CEO Name', 'group_name' => 'Authority', 'source_type' => 'authority', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'ceo_name'],
            ['placeholder_key' => 'reporting_manager_name', 'label' => 'Reporting Manager Name', 'group_name' => 'Authority', 'source_type' => 'authority', 'source_table' => 'employees_new', 'source_column' => 'reporting_manager_id', 'resolver_key' => 'reporting_manager_name'],
            ['placeholder_key' => 'project_manager_name', 'label' => 'Project Manager Name', 'group_name' => 'Authority', 'source_type' => 'authority', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'project_manager_name'],
            ['placeholder_key' => 'manager_name', 'label' => 'Manager Name', 'group_name' => 'Authority', 'source_type' => 'authority', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'manager_name'],
            ['placeholder_key' => 'authorized_signatory', 'label' => 'Authorized Signatory', 'group_name' => 'Authority', 'source_type' => 'authority', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'authorized_signatory'],

            // Content Block Group
            ['placeholder_key' => 'job_responsibilities', 'label' => 'Job Responsibilities', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'job_responsibilities'],
            ['placeholder_key' => 'experience_responsibilities', 'label' => 'Experience Responsibilities', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'experience_responsibilities'],
            ['placeholder_key' => 'internship_work_summary', 'label' => 'Internship Work Summary', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'internship_work_summary'],
            ['placeholder_key' => 'performance_summary', 'label' => 'Performance Summary', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'performance_summary'],
            ['placeholder_key' => 'handover_status', 'label' => 'Handover Status', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'handover_status'],
            ['placeholder_key' => 'custom_note', 'label' => 'Custom Note', 'group_name' => 'Content Block', 'source_type' => 'manual', 'source_table' => null, 'source_column' => null, 'resolver_key' => 'custom_note'],
        ];

        // Seed document_placeholders
        foreach ($placeholders as $p) {
            DB::table('document_placeholders')->updateOrInsert(
                ['placeholder_key' => $p['placeholder_key']],
                array_merge($p, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Mappings
        $mappings = [
            'offer_letter' => [
                'employee_name', 'employee_first_name', 'employee_address', 'employee_city', 'designation', 'joining_date',
                'office_location', 'annual_ctc', 'monthly_gross_salary', 'annual_gross_salary', 'gross_monthly_salary', 'gross_annual_salary',
                'basic_monthly', 'basic_annual', 'hra_monthly', 'hra_annual', 'special_allowance_monthly', 'special_allowance_annual',
                'subtotal_a_monthly', 'subtotal_a_annual', 'professional_tax_monthly', 'professional_tax_annual', 'subtotal_b_monthly',
                'subtotal_b_annual', 'ctc_monthly', 'ctc_annual', 'net_pay_monthly', 'net_pay_annual', 'salary_in_words',
                'probation_period', 'offer_valid_till', 'issue_date', 'hr_manager_name', 'company_name', 'company_address',
                'company_phone', 'company_email', 'company_website', 'company_gstin', 'job_responsibilities'
            ],
            'appointment_letter' => [
                'employee_name', 'employee_address', 'employee_city', 'designation', 'joining_date', 'reporting_manager_name',
                'project_manager_name', 'monthly_gross_salary', 'salary_in_words', 'basic_monthly', 'hra_monthly', 'conveyance_monthly',
                'allowance_monthly', 'probation_period', 'notice_period_probation', 'notice_period_confirmed', 'issue_date',
                'company_name', 'company_address', 'company_phone', 'company_email', 'company_website', 'company_gstin', 'hr_manager_name'
            ],
            'internship_offer_letter' => [
                'employee_name', 'employee_first_name', 'employee_address', 'employee_city', 'designation', 'joining_date',
                'internship_start_date', 'internship_end_date', 'office_location',
                'probation_period', 'offer_valid_till', 'issue_date', 'hr_manager_name', 'company_name', 'company_address',
                'company_phone', 'company_email', 'company_website', 'company_gstin', 'job_responsibilities', 'performance_summary'
            ],
            'internship_certificate' => [
                'employee_name', 'designation', 'internship_start_date', 'internship_end_date', 'internship_work_summary',
                'performance_summary', 'ceo_name', 'company_name'
            ],
            'relieving_letter' => [
                'employee_gender_title', 'employee_name', 'designation', 'joining_date', 'relieving_date', 'issue_date',
                'handover_status', 'ceo_name', 'company_name', 'company_phone', 'company_email', 'company_website', 'company_gstin'
            ],
            'experience_certificate' => [
                'employee_gender_title', 'employee_name', 'designation', 'joining_date', 'relieving_date', 'issue_date',
                'experience_responsibilities', 'performance_summary', 'ceo_name', 'company_name', 'company_phone', 'company_email',
                'company_website', 'company_gstin'
            ],
            'discontinuing_letter' => [
                'employee_name', 'employee_code', 'issue_date', 'discontinue_date', 'hr_manager_name', 'company_name',
                'discontinue_reason', 'handover_clause'
            ],
        ];

        // Seed mappings
        DB::table('document_type_placeholders')->truncate();
        foreach ($mappings as $docType => $keys) {
            $sort = 1;
            foreach ($keys as $key) {
                $placeholder = DB::table('document_placeholders')->where('placeholder_key', $key)->first();
                if ($placeholder) {
                    DB::table('document_type_placeholders')->insert([
                        'document_type' => $docType,
                        'placeholder_id' => $placeholder->id,
                        'is_required' => in_array($key, ['employee_name', 'designation', 'joining_date']),
                        'sort_order' => $sort++,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}
