<?php

namespace App\Services\HRMS\DocumentGeneration;

class DocumentFieldConfigS
{
    /**
     * Get field configurations for all document types.
     */
    public static function getTemplates(): array
    {
        return [
            'offer_letter' => [
                'name' => 'Offer Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'candidate_name', 'label' => 'Candidate Name', 'type' => 'text', 'required' => true, 'default' => 'Candidate Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_city', 'label' => 'City', 'type' => 'text', 'required' => false, 'default' => 'Indore', 'autofill' => 'city', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'offer_valid_till', 'label' => 'Offer Valid Till', 'type' => 'date', 'required' => true, 'default' => 'today+7', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],
                    ['name' => 'office_location', 'label' => 'Office Location', 'type' => 'text', 'required' => true, 'default' => 'Indore, Madhya Pradesh, India', 'autofill' => 'location', 'section' => 'details'],
                    ['name' => 'probation_period', 'label' => 'Probation Period', 'type' => 'text', 'required' => true, 'default' => '3 Months', 'section' => 'details'],
                    ['name' => 'working_hours', 'label' => 'Working Hours', 'type' => 'text', 'required' => false, 'default' => '10:00 AM to 7:00 PM', 'section' => 'details'],
                    ['name' => 'working_days', 'label' => 'Working Days', 'type' => 'text', 'required' => false, 'default' => 'Monday to Saturday', 'section' => 'details'],

                    // Compensation Type Selector
                    ['name' => 'compensation_type', 'label' => 'Employment Compensation Type', 'type' => 'select', 'options' => ['Paid', 'Unpaid'], 'required' => true, 'default' => 'Paid', 'section' => 'details'],

                    // Salary Section (Only shown if Paid)
                    ['name' => 'monthly_gross_salary', 'label' => 'Monthly Gross Salary (INR)', 'type' => 'number', 'required' => true, 'default' => '50000', 'autofill' => 'salary', 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],
                    ['name' => 'annual_ctc', 'label' => 'Annual CTC (INR)', 'type' => 'number', 'required' => true, 'readonly' => true, 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],
                    ['name' => 'basic_monthly', 'label' => 'Basic Monthly', 'type' => 'number', 'required' => true, 'readonly' => true, 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],
                    ['name' => 'hra_monthly', 'label' => 'HRA Monthly', 'type' => 'number', 'required' => true, 'readonly' => true, 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],
                    ['name' => 'special_allowance_monthly', 'label' => 'Special Allowance', 'type' => 'number', 'required' => true, 'readonly' => true, 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],
                    ['name' => 'professional_tax_monthly', 'label' => 'Professional Tax', 'type' => 'number', 'required' => true, 'default' => '200', 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],
                    ['name' => 'net_pay_monthly', 'label' => 'Net Pay Monthly (Approx)', 'type' => 'number', 'required' => true, 'readonly' => true, 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],

                    // Signatory Section
                    ['name' => 'hr_manager_name', 'label' => 'HR Manager Name', 'type' => 'text', 'required' => true, 'default' => 'HR Manager', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Human Resource Manager', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs / Clauses
                    ['name' => 'intro_clause', 'label' => '1. Introductory Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "{company_name} is pleased to offer you the position of {designation} with our organization. We are excited about the skills, energy, and perspective you bring to our team, and we look forward to your contribution to our ongoing growth and success.", 'section' => 'paragraphs'],
                    ['name' => 'joining_clause', 'label' => '2. Joining Details Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "We are pleased to confirm your joining with {company_name}, effective {joining_date}, with working hours from {working_hours}, {working_days}, your primary place of work will be at {office_location} office.", 'section' => 'paragraphs'],
                    ['name' => 'job_responsibilities', 'label' => '3. Job Responsibilities Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "developing, maintaining, optimizing, testing, and delivering assigned software/project tasks as per company requirements. You will also complete tasks assigned by your reporting manager and are expected to perform your duties sincerely, follow company guidelines, and work cooperatively with team members while maintaining professional conduct at all times.", 'section' => 'paragraphs'],
                    ['name' => 'probation_clause', 'label' => '4. Probation Period Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "You will be on probation for a period of {probation_period} from your date of joining. During this period, your performance and conduct will be reviewed. Upon satisfactory performance, your employment may be confirmed. The probation period may be extended if required.", 'section' => 'paragraphs'],
                    ['name' => 'working_hours_clause', 'label' => '5. Working Hours & Benefits Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "Your working hours, weekly offs, leave entitlements, holidays, and other benefits will be governed by company policy and shared with you after joining. Due to work requirements, you may occasionally need to work additional hours to meet project deadlines, without additional compensation unless specified by policy.", 'section' => 'paragraphs'],
                    ['name' => 'confidentiality_clause', 'label' => '6. Confidentiality Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "During your employment, you may have access to confidential company information. You are required to maintain strict confidentiality of all such information during and after your employment. Any misuse or unauthorized sharing of company information may result in disciplinary action.", 'section' => 'paragraphs'],
                    ['name' => 'ip_clause', 'label' => '7. Intellectual Property Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "Any work, code, designs, developments, or improvements created by you during the course of your employment shall belong solely to the Company.", 'section' => 'paragraphs'],
                    ['name' => 'verification_clause', 'label' => '8. Verification Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "This offer is subject to verification of your educational qualifications, previous employment details, and other required documents. If any information provided is found to be incorrect, the company reserves the right to withdraw this offer or terminate employment.", 'section' => 'paragraphs'],
                    ['name' => 'final_terms_clause', 'label' => '9. Final Overview Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "This offer letter provides an overview of your employment. The complete terms and conditions, including company policies, service rules, probation details, and code of conduct, will be communicated to you through a formal Appointment Letter, which will be issued after your joining and completion of joining formalities.", 'section' => 'paragraphs'],
                    ['name' => 'unpaid_clause', 'label' => 'Unpaid Clause (If Unpaid selected)', 'type' => 'textarea', 'required' => false, 'default' => "This offer is for an unpaid engagement. No salary, stipend, or monetary compensation shall be payable during this period unless separately approved in writing by the Company. The engagement is intended to provide professional exposure, learning, project experience, and practical workplace training.", 'show_if' => ['compensation_type' => 'Unpaid'], 'section' => 'paragraphs'],
                ]
            ],

            'internship_offer_letter' => [
                'name' => 'Internship Offer Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'candidate_name', 'label' => 'Candidate Name', 'type' => 'text', 'required' => true, 'default' => 'Candidate Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_city', 'label' => 'City', 'type' => 'text', 'required' => false, 'default' => 'Indore', 'autofill' => 'city', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'joining_date', 'label' => 'Commencement Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'internship_duration', 'label' => 'Internship Duration', 'type' => 'text', 'required' => true, 'default' => '3-month', 'section' => 'details'],
                    ['name' => 'internship_mode', 'label' => 'Internship Mode', 'type' => 'select', 'options' => ['Hybrid', 'Onsite', 'Remote'], 'required' => true, 'default' => 'Hybrid', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer Intern', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'office_location', 'label' => 'Office / Work Location', 'type' => 'text', 'required' => true, 'default' => 'Indore Office', 'autofill' => 'location', 'section' => 'details'],
                    ['name' => 'working_hours', 'label' => 'Working Hours', 'type' => 'text', 'required' => false, 'default' => '10:00 AM to 7:00 PM', 'section' => 'details'],
                    ['name' => 'working_days', 'label' => 'Working Days', 'type' => 'text', 'required' => false, 'default' => 'Monday to Saturday', 'section' => 'details'],
                    ['name' => 'saturday_off_clause', 'label' => 'Saturday Off Clause', 'type' => 'text', 'required' => false, 'default' => 'with second and fourth Saturdays observed as off (alternate Saturdays off)', 'section' => 'details'],

                    // Compensation Type Selector
                    ['name' => 'compensation_type', 'label' => 'Internship Compensation Type', 'type' => 'select', 'options' => ['Paid', 'Unpaid'], 'required' => true, 'default' => 'Unpaid', 'section' => 'details'],

                    // Stipend Section (Only shown if Paid)
                    ['name' => 'stipend_amount', 'label' => 'Monthly Stipend (INR)', 'type' => 'number', 'required' => true, 'default' => '18000', 'show_if' => ['compensation_type' => 'Paid'], 'section' => 'salary'],

                    // Signatory Section
                    ['name' => 'hr_manager_name', 'label' => 'HR Manager Name', 'type' => 'text', 'required' => true, 'default' => 'Vanshika Dhunna', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'HR Manager', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs / Clauses
                    ['name' => 'intro_clause', 'label' => '1. Introductory Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "We are pleased to offer you the position of {designation} with {company_name}, commencing on {joining_date}. This is a {internship_duration} full-time {compensation_type_clause} internship conducted in {internship_mode} mode at our {office_location}. The internship is designed to provide you with practical exposure to software development practices, industry methodologies, and corporate work processes.", 'section' => 'paragraphs'],
                    ['name' => 'working_hours_clause', 'label' => '2. Working Hours & Stipend Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "Your working hours will be {working_hours}, {working_days}, {saturday_off_clause}, as per company policy. {stipend_clause}", 'section' => 'paragraphs'],
                    ['name' => 'job_responsibilities', 'label' => '3. Internship Responsibilities', 'type' => 'textarea', 'required' => false, 'default' => "During the internship, you will assist in software development, testing, debugging, and application maintenance under the guidance of senior team members. You will contribute to real-world projects, participate in code reviews, and support documentation while collaborating with the team. This internship is designed to help you build practical technical skills and gain exposure to industry-standard software development practices.", 'section' => 'paragraphs'],
                    ['name' => 'completion_clause', 'label' => '4. Completion & Placement Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "Upon successful completion of the internship, you will receive an Internship Completion Certificate. Based on your performance and organizational requirements, you may be offered a full-time employment opportunity. If performance expectations are not met, the internship may be extended for further evaluation.", 'section' => 'paragraphs'],
                    ['name' => 'acceptance_clause', 'label' => '5. Acceptance Paragraph', 'type' => 'textarea', 'required' => false, 'default' => "Kindly confirm your acceptance of this offer by replying to this email.", 'section' => 'paragraphs'],
                ]
            ],

            'discontinuing_letter' => [
                'name' => 'Discontinuing Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'discontinue_date', 'label' => 'Discontinue Effective Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'hr_manager_name', 'label' => 'HR Manager Name', 'type' => 'text', 'required' => true, 'default' => 'Vanshika Dhunna', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'HR Manager', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs / Clauses
                    ['name' => 'discontinue_reason', 'label' => 'Discontinue Regret Reason', 'type' => 'textarea', 'required' => false, 'default' => "After careful review of the Company's current business requirements and financial position, we regret to inform you that the Company has decided to discontinue your employment with the Company effective {discontinue_date}.", 'section' => 'paragraphs'],
                    ['name' => 'handover_clause', 'label' => 'Handover & Final Settlement Clause', 'type' => 'textarea', 'required' => false, 'default' => "You are requested to complete all handover formalities and return any Company assets, documents, or access credentials in your possession on or before your last working day. The Company will process your final settlement and any applicable dues in accordance with Company policy and applicable laws.", 'section' => 'paragraphs'],
                ]
            ],

            'appointment_letter' => [
                'name' => 'Appointment Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_address', 'label' => 'Employee Address', 'type' => 'text', 'required' => true, 'default' => 'Indore, Madhya Pradesh, India', 'autofill' => 'address', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],
                    ['name' => 'work_location', 'label' => 'Work Location', 'type' => 'text', 'required' => true, 'default' => 'Indore, Madhya Pradesh, India', 'autofill' => 'location', 'section' => 'details'],
                    ['name' => 'probation_period', 'label' => 'Probation Period', 'type' => 'text', 'required' => true, 'default' => '6 Months', 'section' => 'details'],
                    ['name' => 'notice_period_probation', 'label' => 'Notice Period (Probation)', 'type' => 'text', 'required' => true, 'default' => '15 Days', 'section' => 'details'],
                    ['name' => 'notice_period_confirmed', 'label' => 'Notice Period (Confirmed)', 'type' => 'text', 'required' => true, 'default' => '30 Days', 'section' => 'details'],
                    ['name' => 'working_hours', 'label' => 'Working Hours', 'type' => 'text', 'required' => false, 'default' => '10:00 AM – 7:00 PM IST', 'section' => 'details'],

                    // Salary Section
                    ['name' => 'monthly_salary', 'label' => 'Monthly Gross Salary (INR)', 'type' => 'number', 'required' => true, 'default' => '60000', 'autofill' => 'salary', 'section' => 'salary'],
                    ['name' => 'salary_in_words', 'label' => 'Salary In Words', 'type' => 'text', 'required' => true, 'default' => 'Sixty Thousand Rupees Only', 'section' => 'salary'],
                    ['name' => 'basic_salary', 'label' => 'Basic Salary', 'type' => 'number', 'required' => false, 'default' => '30000', 'section' => 'salary'],
                    ['name' => 'hra', 'label' => 'HRA', 'type' => 'number', 'required' => false, 'default' => '12000', 'section' => 'salary'],
                    ['name' => 'conveyance', 'label' => 'Conveyance', 'type' => 'number', 'required' => false, 'default' => '1600', 'section' => 'salary'],
                    ['name' => 'allowances', 'label' => 'Allowances', 'type' => 'number', 'required' => false, 'default' => '16400', 'section' => 'salary'],

                    // Signatory Section
                    ['name' => 'reporting_manager_name', 'label' => 'Reporting Manager Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'autofill' => 'reporting_manager_name', 'section' => 'signatory'],
                    ['name' => 'project_manager_name', 'label' => 'Project Manager Name', 'type' => 'text', 'required' => true, 'default' => 'HR Manager', 'section' => 'signatory'],
                    ['name' => 'hr_manager_name', 'label' => 'HR Manager Name', 'type' => 'text', 'required' => true, 'default' => 'HR Manager', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Chief Executive Officer', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],
                ]
            ],

            'experience_letter' => [
                'name' => 'Experience Certificate',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_prefix', 'label' => 'Salutation Prefix', 'type' => 'select', 'options' => ['Mr.', 'Ms.', 'Mrs.'], 'required' => true, 'default' => 'Mr.', 'section' => 'recipient'],
                    ['name' => 'gender_pronoun_subject', 'label' => 'Pronoun (Subject)', 'type' => 'select', 'options' => ['he', 'she', 'they'], 'required' => true, 'default' => 'he', 'section' => 'recipient'],
                    ['name' => 'gender_pronoun_possessive', 'label' => 'Pronoun (Possessive)', 'type' => 'select', 'options' => ['his', 'her', 'their'], 'required' => true, 'default' => 'his', 'section' => 'recipient'],
                    ['name' => 'gender_pronoun_subject_capitalized', 'label' => 'Pronoun (Subject - Capitalized)', 'type' => 'select', 'options' => ['He', 'She', 'They'], 'required' => true, 'default' => 'He', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'relieving_date', 'label' => 'Relieving Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'experience_responsibilities', 'label' => 'Core Roles & Responsibilities', 'type' => 'textarea', 'required' => false, 'default' => 'managing assigned responsibilities, coordinating with team members, supporting day-to-day operations, and ensuring timely completion of work as per organizational requirements.', 'section' => 'paragraphs'],
                    ['name' => 'performance_summary', 'label' => 'Performance Summary', 'type' => 'textarea', 'required' => false, 'default' => 'Their conduct and performance throughout their tenure were found to be satisfactory.', 'section' => 'paragraphs'],
                ]
            ],

            'relieving_letter' => [
                'name' => 'Relieving Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_prefix', 'label' => 'Salutation Prefix', 'type' => 'select', 'options' => ['Mr.', 'Ms.', 'Mrs.'], 'required' => true, 'default' => 'Mr.', 'section' => 'recipient'],
                    ['name' => 'gender_pronoun_subject', 'label' => 'Pronoun (Subject)', 'type' => 'select', 'options' => ['he', 'she', 'they'], 'required' => true, 'default' => 'he', 'section' => 'recipient'],
                    ['name' => 'gender_pronoun_possessive', 'label' => 'Pronoun (Possessive)', 'type' => 'select', 'options' => ['his', 'her', 'their'], 'required' => true, 'default' => 'his', 'section' => 'recipient'],
                    ['name' => 'gender_pronoun_object', 'label' => 'Pronoun (Object)', 'type' => 'select', 'options' => ['him', 'her', 'them'], 'required' => true, 'default' => 'him', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'resignation_date', 'label' => 'Resignation Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'relieving_date', 'label' => 'Relieving Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'handover_status', 'label' => 'Handover Details & Exit Remarks', 'type' => 'textarea', 'required' => false, 'default' => 'All company assets, including laptop, security access badges, source code repositories, and work documents, have been successfully handed over to the designated team leader. Full and final settlement of accounts has been fully completed and paid.', 'section' => 'paragraphs'],
                ]
            ],

            'internship_certificate' => [
                'name' => 'Internship Certificate',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Intern Name', 'type' => 'text', 'required' => true, 'default' => 'Intern Name', 'autofill' => 'name', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'internship_start_date', 'label' => 'Internship Start Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'internship_end_date', 'label' => 'Internship End Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineering Intern', 'autofill' => 'designation', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources & Training', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'internship_work_summary', 'label' => 'Internship Work Summary', 'type' => 'textarea', 'required' => false, 'default' => 'During the internship, the candidate was trained on core technologies, developed modular product components, participated in product deployment cycles, and collaborated with senior engineering teams.', 'section' => 'paragraphs'],
                    ['name' => 'performance_summary', 'label' => 'Performance Appraisal Summary', 'type' => 'textarea', 'required' => false, 'default' => 'The candidate demonstrated strong learning abilities, analytical problem-solving skills, and deep dedication to all assigned tasks.', 'section' => 'paragraphs'],
                ]
            ],

            'salary_certificate' => [
                'name' => 'Salary Certificate',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'purpose', 'label' => 'Purpose', 'type' => 'text', 'required' => true, 'default' => 'Address Verification / Loan Application', 'section' => 'details'],

                    // Salary Section
                    ['name' => 'monthly_salary', 'label' => 'Monthly Gross Salary (INR)', 'type' => 'number', 'required' => true, 'default' => '50000', 'autofill' => 'salary', 'section' => 'salary'],
                    ['name' => 'annual_salary', 'label' => 'Annual Salary (INR)', 'type' => 'number', 'required' => true, 'readonly' => true, 'section' => 'salary'],
                    ['name' => 'salary_in_words', 'label' => 'Salary In Words', 'type' => 'text', 'required' => true, 'default' => 'Fifty Thousand Rupees Only', 'section' => 'salary'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],
                ]
            ],

            'warning_letter' => [
                'name' => 'Warning Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'warning_subject', 'label' => 'Warning Subject', 'type' => 'text', 'required' => true, 'default' => 'First Written Warning for Performance/Conduct', 'section' => 'details'],
                    ['name' => 'incident_date', 'label' => 'Incident Date', 'type' => 'date', 'required' => true, 'default' => 'yesterday', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'warning_reason', 'label' => 'Warning Reason (Infraction Details)', 'type' => 'textarea', 'required' => true, 'default' => 'This warning is issued due to repeated unexcused absences and failure to deliver key project components on scheduled deadlines.', 'section' => 'paragraphs'],
                    ['name' => 'corrective_action', 'label' => 'Corrective Action Required', 'type' => 'textarea', 'required' => true, 'default' => 'You are instructed to immediately rectify these performance gaps and display a high standard of professional conduct and discipline. Your reporting manager will review your progress weekly.', 'section' => 'paragraphs'],
                ]
            ],

            'appreciation_letter' => [
                'name' => 'Appreciation Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'achievement_title', 'label' => 'Achievement Title', 'type' => 'text', 'required' => true, 'default' => 'Exceptional Performance & Project Delivery', 'section' => 'details'],
                    ['name' => 'performance_period', 'label' => 'Performance Period', 'type' => 'text', 'required' => false, 'default' => 'Q2 2026', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Director of Engineering', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'appreciation_reason', 'label' => 'Appreciation Reason (Achievement Details)', 'type' => 'textarea', 'required' => true, 'default' => 'Your hard work and dedication during our recent product release helped us deliver outstanding results under tight timelines. Your problem-solving abilities and positive mindset have been highly inspiring to your entire team.', 'section' => 'paragraphs'],
                ]
            ],

            'nda_agreement' => [
                'name' => 'NDA Agreement',
                'fields' => [
                    // Recipient Section
                    ['name' => 'party_name', 'label' => 'Party Name (Candidate/Employee)', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'party_address', 'label' => 'Party Address', 'type' => 'text', 'required' => true, 'default' => 'Indore, Madhya Pradesh, India', 'autofill' => 'address', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'effective_date', 'label' => 'Effective Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'confidentiality_period', 'label' => 'Confidentiality Period', 'type' => 'text', 'required' => true, 'default' => '5 Years post employment termination', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Director of Operations', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],
                    ['name' => 'company_address', 'label' => 'Company Address', 'type' => 'text', 'required' => true, 'default' => 'company_address', 'section' => 'signatory'],
                ]
            ],

            'joining_letter' => [
                'name' => 'Joining Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_address', 'label' => 'Employee Address', 'type' => 'text', 'required' => true, 'default' => 'Indore, Madhya Pradesh, India', 'autofill' => 'address', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'joining_remarks', 'label' => 'Joining Remarks / Notes', 'type' => 'textarea', 'required' => false, 'default' => 'We are pleased to welcome you to our organization. Please submit your relevant documents and onboarding files to the HR department on your date of joining.', 'section' => 'paragraphs'],
                ]
            ],

            'promotion_letter' => [
                'name' => 'Promotion Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'effective_date', 'label' => 'Effective Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'current_designation', 'label' => 'Current Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'new_designation', 'label' => 'New Designation', 'type' => 'text', 'required' => true, 'default' => 'Senior Software Engineer', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],

                    // Salary Section
                    ['name' => 'monthly_salary', 'label' => 'New Monthly Gross Salary (INR)', 'type' => 'number', 'required' => true, 'default' => '75000', 'autofill' => 'salary', 'section' => 'salary'],
                    ['name' => 'salary_in_words', 'label' => 'New Salary In Words', 'type' => 'text', 'required' => true, 'default' => 'Seventy Five Thousand Rupees Only', 'section' => 'salary'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'promotion_remarks', 'label' => 'Promotion Remarks & New Roles', 'type' => 'textarea', 'required' => false, 'default' => 'Due to your exemplary performance, dedication, and positive contribution to the team, the management is pleased to promote you. In your new role, you will be responsible for leading key deliverables and guiding junior team members.', 'section' => 'paragraphs'],
                ]
            ],

            'confirmation_letter' => [
                'name' => 'Confirmation Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'confirmation_date', 'label' => 'Confirmation Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'confirmation_remarks', 'label' => 'Confirmation Remarks', 'type' => 'textarea', 'required' => false, 'default' => 'We are pleased to confirm your employment with our organization. Your probation period has been successfully completed, and you are now a permanent employee. We look forward to your continued contribution and growth with us.', 'section' => 'paragraphs'],
                ]
            ],

            'employment_verification_letter' => [
                'name' => 'Employment Verification Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],
                    ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'autofill' => 'joining_date', 'section' => 'details'],
                    ['name' => 'verification_purpose', 'label' => 'Verification Purpose / Recipient', 'type' => 'text', 'required' => true, 'default' => 'To Whomsoever It May Concern', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],
                ]
            ],

            'noc_letter' => [
                'name' => 'No Objection Certificate (NOC)',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'department', 'label' => 'Department', 'type' => 'text', 'required' => true, 'default' => 'Engineering', 'autofill' => 'department', 'section' => 'details'],
                    ['name' => 'noc_purpose', 'label' => 'NOC Purpose / Reason', 'type' => 'text', 'required' => true, 'default' => 'higher studies / visa processing / external project collaboration', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'noc_remarks', 'label' => 'NOC Remarks', 'type' => 'textarea', 'required' => false, 'default' => 'This is to confirm that the Company has no objection to the employee pursuing their requested application. We wish them all the best in their future endeavors.', 'section' => 'paragraphs'],
                ]
            ],

            'increment_letter' => [
                'name' => 'Increment Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'effective_date', 'label' => 'Effective Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],

                    // Salary Section
                    ['name' => 'monthly_salary', 'label' => 'New Monthly Gross Salary (INR)', 'type' => 'number', 'required' => true, 'default' => '65000', 'autofill' => 'salary', 'section' => 'salary'],
                    ['name' => 'salary_in_words', 'label' => 'New Salary In Words', 'type' => 'text', 'required' => true, 'default' => 'Sixty Five Thousand Rupees Only', 'section' => 'salary'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'increment_remarks', 'label' => 'Increment Remarks', 'type' => 'textarea', 'required' => false, 'default' => 'Based on your performance review and contribution to the organization, the management is pleased to revise your annual compensation structure. We appreciate your efforts and commitment towards the company.', 'section' => 'paragraphs'],
                ]
            ],

            'transfer_letter' => [
                'name' => 'Transfer Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'effective_date', 'label' => 'Effective Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],
                    ['name' => 'current_branch', 'label' => 'Current Branch/Location', 'type' => 'text', 'required' => true, 'default' => 'Indore Head Office', 'autofill' => 'location', 'section' => 'details'],
                    ['name' => 'new_branch', 'label' => 'New Branch/Location', 'type' => 'text', 'required' => true, 'default' => 'Bhopal Branch Office', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'transfer_remarks', 'label' => 'Transfer Remarks & Handover Notes', 'type' => 'textarea', 'required' => false, 'default' => 'This is to inform you that you are being transferred to our new branch. Please complete your ongoing project handover formalities and report to the branch head at the new location on the effective date.', 'section' => 'paragraphs'],
                ]
            ],

            'resignation_acceptance_letter' => [
                'name' => 'Resignation Acceptance Letter',
                'fields' => [
                    // Recipient Section
                    ['name' => 'employee_name', 'label' => 'Employee Name', 'type' => 'text', 'required' => true, 'default' => 'Employee Name', 'autofill' => 'name', 'section' => 'recipient'],
                    ['name' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text', 'required' => true, 'default' => 'EMP001', 'autofill' => 'code', 'section' => 'recipient'],

                    // Details Section
                    ['name' => 'issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'resignation_date', 'label' => 'Resignation Request Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'relieving_date', 'label' => 'Last Working Date', 'type' => 'date', 'required' => true, 'default' => 'today', 'section' => 'details'],
                    ['name' => 'designation', 'label' => 'Designation', 'type' => 'text', 'required' => true, 'default' => 'Software Engineer', 'autofill' => 'designation', 'section' => 'details'],

                    // Signatory Section
                    ['name' => 'signatory_name', 'label' => 'Signatory Name', 'type' => 'text', 'required' => true, 'default' => 'Authorized Signatory', 'section' => 'signatory'],
                    ['name' => 'signatory_designation', 'label' => 'Signatory Designation', 'type' => 'text', 'required' => true, 'default' => 'Head of Human Resources', 'section' => 'signatory'],
                    ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'required' => true, 'default' => 'company_name', 'section' => 'signatory'],

                    // Paragraphs
                    ['name' => 'resignation_remarks', 'label' => 'Resignation Acceptance Notes', 'type' => 'textarea', 'required' => false, 'default' => 'We hereby accept your formal resignation. We appreciate your contributions during your service. Please coordinate with the IT and Admin team for asset return and clearance procedures.', 'section' => 'paragraphs'],
                ]
            ],
        ];
    }
}
