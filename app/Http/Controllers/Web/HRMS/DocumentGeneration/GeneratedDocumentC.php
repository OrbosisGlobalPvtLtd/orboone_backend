<?php

namespace App\Http\Controllers\Web\HRMS\DocumentGeneration;

use App\Http\Controllers\Controller;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\DocumentGeneration\DocumentGenerationS;
use App\Services\HRMS\DocumentGeneration\DocumentPdfS;
use App\Services\HRMS\DocumentGeneration\DocumentEmailS;
use App\Services\HRMS\DocumentGeneration\HtmlDocumentGenerationS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class GeneratedDocumentC extends Controller
{
    protected $generationService;
    protected $pdfService;
    protected $emailService;
    protected $htmlGenerationService;

    public function __construct(
        DocumentGenerationS $generationService,
        DocumentPdfS $pdfService,
        DocumentEmailS $emailService,
        HtmlDocumentGenerationS $htmlGenerationService
    ) {
        $this->generationService = $generationService;
        $this->pdfService = $pdfService;
        $this->emailService = $emailService;
        $this->htmlGenerationService = $htmlGenerationService;
    }

    public function dashboard()
    {
        $totalTemplates = DocumentTemplate::count();
        $activeTemplates = DocumentTemplate::where('is_active', true)->count();
        $generatedDocuments = GeneratedDocument::count();
        $pendingReview = GeneratedDocument::where('status', 'draft')->count();
        $sentDocuments = GeneratedDocument::where('status', 'sent')->count();
        $draftDocuments = GeneratedDocument::where('status', 'draft')->count();

        $recentDocuments = GeneratedDocument::with(['template', 'employee'])->latest()->take(5)->get();

        return view('hrms.document-generation.dashboard', compact(
            'totalTemplates', 'activeTemplates', 'generatedDocuments', 
            'pendingReview', 'sentDocuments', 'draftDocuments', 'recentDocuments'
        ));
    }

    public function index(Request $request)
    {
        $query = GeneratedDocument::with(['template', 'employee', 'generatedBy']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('candidate_name', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('display_name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date Range Filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Format class filtering (Modern HTML is the default primary view)
        $templateClass = $request->input('template_class', 'modern');
        if ($templateClass === 'modern') {
            $query->where(function ($q) {
                $q->where('template_type', 'html')
                  ->orWhereNull('template_type');
            });
        } elseif ($templateClass === 'legacy') {
            $query->where('template_type', 'docx');
        }

        // Metrics
        $baseQueryForMetrics = GeneratedDocument::query();
        if ($templateClass === 'modern') {
            $baseQueryForMetrics->where(function ($q) {
                $q->where('template_type', 'html')
                  ->orWhereNull('template_type');
            });
        } elseif ($templateClass === 'legacy') {
            $baseQueryForMetrics->where('template_type', 'docx');
        }

        $totalDocuments = (clone $baseQueryForMetrics)->count();
        $generatedToday = (clone $baseQueryForMetrics)->whereDate('created_at', \Carbon\Carbon::today())->count();
        $employeeDocuments = (clone $baseQueryForMetrics)->whereNotNull('employee_id')->count();
        $manualDocuments = (clone $baseQueryForMetrics)->whereNull('employee_id')->count();
        $emailedDocuments = (clone $baseQueryForMetrics)->where('status', 'sent')->count();

        $documents = $query->latest()->paginate(15);
        $employees = EmployeeM::active()->get();

        return view('hrms.document-generation.generated.index', compact(
            'documents', 'employees', 'totalDocuments', 'generatedToday', 
            'employeeDocuments', 'manualDocuments', 'emailedDocuments'
        ));
    }

    public function create(Request $request)
    {
        $templatesQuery = DocumentTemplate::where('is_active', true);
        if (Schema::hasColumn('document_templates', 'is_archived')) {
            $templatesQuery->where(function ($q) {
                $q->whereNull('is_archived')->orWhere('is_archived', false);
            });
        }
        $templates = $templatesQuery->get();
        $employees = EmployeeM::active()->get();
        
        $selectedTemplate = null;
        if ($request->has('template_id')) {
            $selectedTemplate = DocumentTemplate::with('fields')->find($request->template_id);
        }

        // Available HTML Document Types for new flow
        $documentTypes = [
            'offer_letter' => 'Offer Letter',
            'appointment_letter' => 'Appointment Letter',
            'internship_offer_letter' => 'Internship Offer Letter',
            'discontinuing_letter' => 'Discontinuing Letter',
            'experience_letter' => 'Experience Letter',
            'relieving_letter' => 'Relieving Letter',
            'internship_certificate' => 'Internship Certificate',
            'salary_certificate' => 'Salary Certificate',
            'warning_letter' => 'Warning Letter',
            'appreciation_letter' => 'Appreciation Letter',
            'nda_agreement' => 'NDA / Agreement',
        ];

        return view('hrms.document-generation.generated.create', compact('templates', 'employees', 'selectedTemplate', 'documentTypes'));
    }

    public function preview(Request $request)
    {
        if ($request->filled('document_type')) {
            try {
                $html = $this->htmlGenerationService->previewHtml(
                    $request->document_type,
                    $request->employee_id ?: null,
                    $request->input('manual_fields', [])
                );
                return response()->json(['html' => $html]);
            } catch (\Throwable $e) {
                return response()->json(['html' => '<div class="alert alert-danger">Error: ' . e($e->getMessage()) . '</div>'], 400);
            }
        }

        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
        ]);

        $html = $this->generationService->previewDocument(
            $request->template_id,
            $request->employee_id,
            $request->input('manual_fields', [])
        );

        return response()->json(['html' => $html]);
    }

    public function employeeDocumentData(Request $request, $employeeId)
    {
        $employee = EmployeeM::with(['user', 'department', 'designation', 'reportingManager', 'profile'])->findOrFail($employeeId);
        
        $resolver = app(\App\Services\HRMS\DocumentGeneration\DocumentPlaceholderResolverS::class);
        $resolved = $resolver->resolve($employee, []);

        $monthlySalary = $employee->salaryStructure?->gross_salary ?? $employee->actual_salary ?? $employee->gross_salary ?? '';
        if ($monthlySalary) {
            $monthlySalary = number_format((float)$monthlySalary, 2, '.', '');
        }
        $annualSalary = $monthlySalary ? number_format((float)$monthlySalary * 12, 2, '.', '') : '';

        return response()->json([
            'employee_id' => $employee->id,
            'employee_name' => $resolved['employee_name'] ?? '',
            'employee_code' => $employee->employee_code,
            'employee_email' => $employee->user?->email ?: $employee->email ?: '',
            'employee_mobile' => $employee->user?->phone ?: $employee->mobile ?: $employee->profile?->mobile ?: $employee->profile?->emergency_contact_number ?: '',
            'department' => $resolved['department'] ?? '',
            'designation' => $resolved['designation'] ?? '',
            'joining_date' => $employee->joining_date ? date('Y-m-d', strtotime($employee->joining_date)) : '',
            'reporting_manager_name' => $resolved['reporting_manager_name'] ?? '',
            'work_location' => $resolved['work_location'] ?? '',
            'employee_address' => $resolved['employee_address'] ?? '',
            'employee_city' => $resolved['employee_city'] ?? '',
            'employee_state' => $employee->profile?->state ?? '',
            'employee_country' => $employee->profile?->country ?? '',
            'gender' => $employee->gender ?: $employee->profile?->gender ?: '',
            'gender_pronoun_subject' => $resolved['gender_pronoun_subject'] ?? '',
            'gender_pronoun_object' => $resolved['gender_pronoun_object'] ?? '',
            'gender_pronoun_possessive' => $resolved['gender_pronoun_possessive'] ?? '',
            'monthly_salary' => $monthlySalary,
            'annual_salary' => $annualSalary,
            'company_name' => $resolved['company_name'] ?? '',
        ]);
    }

    public function store(Request $request)
    {
        // 1. Temporary log for submitted payload
        \Illuminate\Support\Facades\Log::info("Document Generation Submit Request", [
            'send_email' => $request->input('send_email'),
            'employee_email' => $request->input('employee_email'),
            'email_subject' => $request->input('email_subject'),
            'email_message' => $request->input('email_message'),
            'document_type' => $request->input('document_type'),
            'template_id' => $request->input('template_id'),
            'employee_id' => $request->input('employee_id'),
            'manual_fields_count' => is_array($request->input('manual_fields')) ? count($request->input('manual_fields')) : 0,
        ]);

        // Part 7: Validation
        $request->validate([
            'send_email' => 'nullable|boolean',
            'employee_email' => 'required_if:send_email,1|nullable|email',
            'cc_email' => 'nullable|email',
            'email_subject' => 'nullable|string|max:255',
            'email_message' => 'nullable|string',
        ]);

        $document = null;
        $messageSuffix = '';

        if ($request->filled('document_type')) {
            $docType = $request->input('document_type');
            $configs = \App\Services\HRMS\DocumentGeneration\DocumentFieldConfigS::getTemplates();
            $manualFields = $request->input('manual_fields', []);

            if (isset($configs[$docType])) {
                $rules = [];
                foreach ($configs[$docType]['fields'] as $field) {
                    if (isset($field['show_if'])) {
                        $shouldShow = true;
                        foreach ($field['show_if'] as $depKey => $depValue) {
                            if (!isset($manualFields[$depKey]) || $manualFields[$depKey] !== $depValue) {
                                $shouldShow = false;
                                break;
                            }
                        }
                        if (!$shouldShow) {
                            continue;
                        }
                    }
                    if ($field['required']) {
                        $rules["manual_fields.{$field['name']}"] = 'required';
                    }
                }
                $request->validate($rules);
            }

            // Validate uploads if provided
            $request->validate([
                'signature_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'seal_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            if ($request->hasFile('signature_image_file')) {
                $path = $request->file('signature_image_file')->store('hrms/manual-signatures', 'public');
                $manualFields['signature_image'] = asset('storage/' . $path);
            }
            if ($request->hasFile('seal_image_file')) {
                $path = $request->file('seal_image_file')->store('hrms/manual-seals', 'public');
                $manualFields['seal_image'] = asset('storage/' . $path);
            }

            try {
                $document = $this->htmlGenerationService->generate(
                    $request->document_type,
                    $request->employee_id ?: null,
                    $manualFields
                );
                $messageSuffix = ' as HTML PDF.';
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Document Generation Failed (HTML)", ['error' => $e->getMessage()]);
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        } else {
            $request->validate([
                'template_id' => 'required|exists:document_templates,id',
                'employee_id' => 'nullable|exists:employees_new,id',
            ]);

            try {
                $document = $this->generationService->generateDocument(
                    $request->template_id,
                    $request->employee_id,
                    $request->input('manual_fields', [])
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Document Generation Failed (Docx Template)", ['error' => $e->getMessage()]);
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $sendEmail = $request->input('send_email') == '1' || $request->input('send_email') === 'true' || $request->input('send_email') === true || $request->boolean('send_email');

        // Email delivery flow
        if ($document && $sendEmail) {
            \Illuminate\Support\Facades\Log::info("Email checkbox detected");
            $emailTo = $request->input('employee_email');
            $ccEmail = $request->input('cc_email');
            
            $employeeName = $document->employee ? $document->employee->display_name : ($document->candidate_name ?: 'Candidate');
            $employeeFirstName = !empty($employeeName) ? (explode(' ', trim($employeeName))[0] ?: 'Candidate') : 'Candidate';

            // Resolve company name
            $company = null;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('company_settings')) {
                    $company = \Illuminate\Support\Facades\DB::table('company_settings')->first();
                }
            } catch (\Throwable $e) {}
            
            $companyName = $company?->company_name ?: (function_exists('branding_name') ? branding_name() : 'Orbosis Global Pvt. Ltd.');
            if (empty($companyName) || $companyName === 'HRMS' || $companyName === 'Default') {
                $companyName = 'Orbosis Global Pvt. Ltd.';
            }

            $template = $this->resolveEmailTemplate($document->document_type, $employeeFirstName, $companyName);
            
            $subject = $request->filled('email_subject') ? $request->input('email_subject') : $template['subject'];
            $body = $request->filled('email_message') ? $request->input('email_message') : $template['body'];

            $parsed = $this->parseEmailTemplates($subject, $body, $employeeName, $companyName);
            $subject = $parsed['subject'];
            $body = $parsed['body'];

            \Illuminate\Support\Facades\Log::info("Email sending started", [
                'to' => $emailTo,
                'cc' => $ccEmail,
                'subject' => $subject,
            ]);

            try {
                $pdfPath = $document->generated_pdf_path ?: $document->pdf_path;
                if (!$pdfPath || !\Illuminate\Support\Facades\Storage::disk('private')->exists($pdfPath)) {
                    throw new \Exception("PDF document does not exist and cannot be sent.");
                }

                \Illuminate\Support\Facades\Log::info("PDF path exists", ['path' => $pdfPath]);
                $pdfFile = \Illuminate\Support\Facades\Storage::disk('private')->path($pdfPath);
                
                // Confirm file size > 0
                if (!is_file($pdfFile) || filesize($pdfFile) === 0) {
                    throw new \Exception("Generated PDF file is empty or missing from local path.");
                }

                $fromAddress = config('mail.from.address') ?: config('mail.mailers.smtp.username');
                $fromName = config('mail.from.name') ?: 'HR Team';
                $fallbackUsed = false;

                $mailable = new \App\Mail\QueuedDocumentMail(
                    $subject,
                    $body,
                    $pdfFile,
                    basename($pdfPath),
                    $fromAddress,
                    $fromName,
                    $ccEmail
                );

                if (config('queue.default') === 'sync') {
                    try {
                        \Illuminate\Support\Facades\Log::info("Attempting to send email synchronously with preferred sender: {$fromAddress}");
                        \Illuminate\Support\Facades\Mail::to($emailTo)->send($mailable);
                    } catch (\Throwable $mailEx) {
                        $errorMessage = $mailEx->getMessage();
                        $isRelayError = str_contains(strtolower($errorMessage), '553') || 
                                        str_contains(strtolower($errorMessage), 'relay') || 
                                        str_contains(strtolower($errorMessage), 'sender') ||
                                        str_contains(strtolower($errorMessage), 'disallowed');

                        $smtpUsername = config('mail.mailers.smtp.username');
                        if ($isRelayError && $smtpUsername && $fromAddress !== $smtpUsername) {
                            \Illuminate\Support\Facades\Log::warning("SMTP rejected preferred sender ({$fromAddress}) with error: {$errorMessage}. Falling back to MAIL_USERNAME: {$smtpUsername}");
                            $mailable->fromAddress = $smtpUsername;
                            \Illuminate\Support\Facades\Mail::to($emailTo)->send($mailable);
                        } else {
                            throw $mailEx;
                        }
                    }
                } else {
                    \Illuminate\Support\Facades\Log::info("Queueing email with preferred sender: {$fromAddress}");
                    \Illuminate\Support\Facades\Mail::to($emailTo)->queue($mailable);
                }

                $dbData = [
                    'status' => 'sent',
                    'sent_at' => \Carbon\Carbon::now(),
                    'sent_by_user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                    'email_to' => $emailTo,
                    'email_subject' => $subject,
                    'email_body' => $body,
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('generated_documents', 'email_status')) {
                    $dbData['email_status'] = 'sent';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('generated_documents', 'email_sent_at')) {
                    $dbData['email_sent_at'] = \Carbon\Carbon::now();
                }

                $document->update($dbData);

                $document->logs()->create([
                    'action' => 'sent',
                    'remarks' => "Emailed to {$emailTo}" . (!empty($ccEmail) ? " (CC: {$ccEmail})" : ""),
                    'actor_user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                ]);

                return redirect()->route('hrms.document-generation.generated.index')
                    ->with('success', "Document generated successfully{$messageSuffix} Email sent successfully to {$emailTo}.");
            } catch (\Throwable $mailEx) {
                \Illuminate\Support\Facades\Log::error("Mail send failed: " . $mailEx->getMessage(), [
                    'exception' => $mailEx
                ]);

                $dbData = [];
                if (\Illuminate\Support\Facades\Schema::hasColumn('generated_documents', 'email_status')) {
                    $dbData['email_status'] = 'failed';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('generated_documents', 'email_error')) {
                    $dbData['email_error'] = substr($mailEx->getMessage(), 0, 500);
                }
                if (!empty($dbData)) {
                    $document->update($dbData);
                }

                $document->logs()->create([
                    'action' => 'failed',
                    'remarks' => "Email failed: " . substr($mailEx->getMessage(), 0, 200),
                    'actor_user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                ]);

                return redirect()->route('hrms.document-generation.generated.index')
                    ->with('warning', "Document generated successfully{$messageSuffix} but email failed: " . $mailEx->getMessage());
            }
        }

        if ($document) {
            $document->logs()->create([
                'action' => 'generated',
                'remarks' => "Document generated. Email was not sent.",
                'actor_user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
            ]);
        }

        return redirect()->route('hrms.document-generation.generated.index')
            ->with('success', "Document generated successfully{$messageSuffix} Email was not sent.");
    }

    protected function resolveEmailTemplate($documentType, $employeeName, $companyName)
    {
        $docTypeLabel = ucwords(str_replace('_', ' ', $documentType));
        
        switch ($documentType) {
            case 'offer_letter':
                return [
                    'subject' => "Offer Letter - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your offer letter from {$companyName}.<br><br>Kindly review the document and contact HR for any clarification."
                ];
            case 'appointment_letter':
                return [
                    'subject' => "Appointment Letter - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your appointment letter from {$companyName}.<br><br>We welcome you to the organization and wish you success in your role."
                ];
            case 'experience_letter':
            case 'experience_certificate':
                return [
                    'subject' => "Experience Certificate - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your experience certificate issued by {$companyName}.<br><br>We wish you all the best for your future endeavors."
                ];
            case 'relieving_letter':
                return [
                    'subject' => "Relieving Letter - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your relieving letter from {$companyName}.<br><br>We wish you continued success in your professional journey."
                ];
            case 'salary_certificate':
                return [
                    'subject' => "Salary Certificate - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your salary certificate as requested."
                ];
            case 'internship_certificate':
                return [
                    'subject' => "Internship Certificate - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your internship certificate from {$companyName}.<br><br>We appreciate your contribution and wish you all the best."
                ];
            case 'internship_offer_letter':
                return [
                    'subject' => "Internship Offer Letter - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>We are pleased to offer you the internship opportunity at {$companyName}. Please find attached your internship offer letter detailing the terms of your engagement.<br><br>Kindly review, sign, and return the acceptance to us.<br><br>Best regards,<br>HR Team<br>{$companyName}"
                ];
            case 'discontinuing_letter':
                return [
                    'subject' => "Discontinuing Letter - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your discontinuing letter from {$companyName}.<br><br>Kindly contact HR if you have any questions regarding the final settlement process."
                ];
            default:
                return [
                    'subject' => "{$docTypeLabel} - {$companyName}",
                    'body' => "Dear {$employeeName},<br><br>Please find attached your {$docTypeLabel} issued by {$companyName}.<br><br>Regards,<br>HR Team<br>{$companyName}"
                ];
        }
    }

    protected function parseEmailTemplates($subject, $body, $employeeName, $companyName)
    {
        $employeeFirstName = !empty($employeeName) ? (explode(' ', trim($employeeName))[0] ?: 'Candidate') : 'Candidate';
        $replace = [
            '{{ employee_name }}' => $employeeName,
            '{{ employee_first_name }}' => $employeeFirstName,
            '{{ company_name }}' => $companyName,
        ];
        
        $subject = str_replace(array_keys($replace), array_values($replace), $subject);
        $body = str_replace(array_keys($replace), array_values($replace), $body);
        
        if (!str_contains($body, '<br>') && !str_contains($body, '<p>')) {
            $body = nl2br(e($body));
        }
        
        return ['subject' => $subject, 'body' => $body];
    }

    public function show($id)
    {
        return $this->streamPdf($id);
    }

    public function download($id)
    {
        $document = GeneratedDocument::findOrFail($id);

        if ($document->generated_pdf_path && Storage::disk('private')->exists($document->generated_pdf_path)) {
            return $this->pdfService->downloadPdf($document->generated_pdf_path, basename($document->generated_pdf_path));
        }

        if ($document->pdf_path && Storage::disk('private')->exists($document->pdf_path)) {
            return $this->pdfService->downloadPdf($document->pdf_path, basename($document->pdf_path));
        }

        abort(404, 'PDF is not available for this document.');
    }
    
    public function streamPdf($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        $path = $document->generated_pdf_path ?: $document->pdf_path;
        if (!$path) {
            abort(404, 'PDF is not available for preview.');
        }
        return $this->pdfService->streamPdf($path);
    }

    public function downloadDocx($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        if (!$document->generated_docx_path || !Storage::disk('private')->exists($document->generated_docx_path)) {
            abort(404, 'DOCX is not available for this document.');
        }

        return Storage::disk('private')->download($document->generated_docx_path, basename($document->generated_docx_path));
    }

    public function email(Request $request, $id)
    {
        $document = GeneratedDocument::findOrFail($id);
        
        $request->validate([
            'email_to' => 'required|email',
            'email_subject' => 'required|string',
            'email_body' => 'required|string',
        ]);

        $this->emailService->sendDocument($document, $request->email_to, $request->email_subject, $request->email_body);

        return redirect()->back()->with('success', 'Document sent successfully.');
    }

    public function review(Request $request, $id)
    {
        $document = GeneratedDocument::findOrFail($id);
        $document->update([
            'status' => 'reviewed',
            'review_note' => $request->review_note,
            'reviewed_by_user_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        
        $this->generationService->logAction($document->id, 'reviewed', $request->review_note);

        return redirect()->back()->with('success', 'Document reviewed successfully.');
    }

    public function cancel(Request $request, $id)
    {
        $document = GeneratedDocument::findOrFail($id);
        $document->update(['status' => 'cancelled']);
        
        $this->generationService->logAction($document->id, 'cancelled', $request->reason ?? 'Cancelled by user');

        return redirect()->back()->with('success', 'Document cancelled.');
    }

    // Employee Self Route
    public function selfIndex()
    {
        $documents = GeneratedDocument::where('employee_id', Auth::user()->employee->id ?? 0)
            ->whereIn('status', ['generated', 'sent', 'reviewed'])
            ->latest()->paginate(15);
            
        return view('hrms.document-generation.self.index', compact('documents'));
    }

    public function selfDownload($id)
    {
        $document = GeneratedDocument::where('employee_id', Auth::user()->employee->id ?? 0)
            ->findOrFail($id);

        if ($document->generated_pdf_path && Storage::disk('private')->exists($document->generated_pdf_path)) {
            return $this->pdfService->downloadPdf($document->generated_pdf_path, basename($document->generated_pdf_path));
        }

        if ($document->pdf_path && Storage::disk('private')->exists($document->pdf_path)) {
            return $this->pdfService->downloadPdf($document->pdf_path, basename($document->pdf_path));
        }

        if ($document->generated_docx_path && Storage::disk('private')->exists($document->generated_docx_path)) {
            return Storage::disk('private')->download($document->generated_docx_path, basename($document->generated_docx_path));
        }

        abort(404, 'No downloadable file is available for this document.');
    }

    public function selfView($id)
    {
        $document = GeneratedDocument::where('employee_id', Auth::user()->employee->id ?? 0)
            ->findOrFail($id);

        $path = $document->generated_pdf_path ?: $document->pdf_path;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404, 'PDF is not available for viewing.');
        }

        return $this->pdfService->streamPdf($path);
    }

    public function regenerate($id)
    {
        $document = GeneratedDocument::findOrFail($id);
        
        try {
            if (($document->template_type ?? 'html') === 'html') {
                $html = $this->htmlGenerationService->previewHtml(
                    $document->document_type,
                    $document->employee_id ?: null,
                    $document->form_data ?: $document->field_values ?: []
                );
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'portrait');
                $output = $pdf->output();
                
                $path = $document->generated_pdf_path ?: "generated_documents/pdf/regenerated-{$document->document_number}.pdf";
                Storage::disk('private')->put($path, $output);
                
                $document->update([
                    'generated_pdf_path' => $path,
                    'pdf_path' => $path,
                    'pdf_status' => 'converted'
                ]);
                
                return redirect()->back()->with('success', 'PDF regenerated successfully.');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Regeneration failed: ' . $e->getMessage());
        }
        
        return redirect()->back()->with('error', 'Only HTML templates can be automatically regenerated.');
    }

    public function destroy($id)
    {
        $document = GeneratedDocument::findOrFail($id);

        try {
            if ($document->generated_pdf_path && Storage::disk('private')->exists($document->generated_pdf_path)) {
                Storage::disk('private')->delete($document->generated_pdf_path);
            }
            if ($document->pdf_path && Storage::disk('private')->exists($document->pdf_path)) {
                Storage::disk('private')->delete($document->pdf_path);
            }
            if ($document->generated_docx_path && Storage::disk('private')->exists($document->generated_docx_path)) {
                Storage::disk('private')->delete($document->generated_docx_path);
            }
            
            // Delete logs relation if it exists
            if (method_exists($document, 'logs')) {
                $document->logs()->delete();
            }
            
            $document->delete();

            return redirect()->route('hrms.document-generation.generated.index')
                ->with('success', 'Document deleted successfully.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to delete generated document", ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }
}
