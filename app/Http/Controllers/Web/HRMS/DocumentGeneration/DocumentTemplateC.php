<?php

namespace App\Http\Controllers\Web\HRMS\DocumentGeneration;

use App\Http\Controllers\Controller;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Services\HRMS\DocumentGeneration\DocumentFieldDetectorS;
use App\Services\HRMS\DocumentGeneration\DocumentTemplateS;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentTemplateC extends Controller
{
    protected $templateService;
    protected $fieldDetector;
    protected $fileResolver;

    public function __construct(
        DocumentTemplateS $templateService,
        DocumentFieldDetectorS $fieldDetector,
        HrmsFileResolverS $fileResolver
    )
    {
        $this->templateService = $templateService;
        $this->fieldDetector = $fieldDetector;
        $this->fileResolver = $fileResolver;
    }

    public function index(Request $request)
    {
        $query = DocumentTemplate::with(['createdBy']);

        if ($request->filled('type')) {
            $query->where('document_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->filled('template_type') && Schema::hasColumn('document_templates', 'template_type')) {
            $query->where('template_type', $request->template_type);
        }
        if ($request->filled('archived') && Schema::hasColumn('document_templates', 'is_archived')) {
            $query->where('is_archived', $request->archived === 'yes');
        }

        $templates = $query->latest()->paginate(15);
        $totalTemplates = DocumentTemplate::count();
        $activeTemplates = DocumentTemplate::where('is_active', true)->count();
        $docxTemplates = Schema::hasColumn('document_templates', 'template_type')
            ? DocumentTemplate::where('template_type', 'docx')->count()
            : 0;
        $generatedCount = \App\Models\HRMS\DocumentGeneration\GeneratedDocument::count();
        $archivedCount = Schema::hasColumn('document_templates', 'is_archived')
            ? DocumentTemplate::where('is_archived', true)->count()
            : 0;

        return view('hrms.document-generation.templates.index', compact(
            'templates',
            'totalTemplates',
            'activeTemplates',
            'docxTemplates',
            'generatedCount',
            'archivedCount'
        ));
    }

    public function store(Request $request)
    {
        $supportsTemplateType = Schema::hasColumn('document_templates', 'template_type');
        $supportsVersion = Schema::hasColumn('document_templates', 'version');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'template_type' => 'nullable|in:html,docx',
            'html_template' => 'nullable',
            'docx_file' => 'nullable|file|mimes:docx|max:10240',
            'version' => 'nullable|string|max:50',
        ]);

        $data = $request->except('_token');
        $templateType = $supportsTemplateType ? ($validated['template_type'] ?? 'html') : 'html';
        $data['template_type'] = $templateType;
        $baseSlug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($data['name']);
        if ($baseSlug === '') {
            $baseSlug = Str::slug($data['name']) . '-' . time();
        }
        $slug = $baseSlug;
        $suffix = 1;
        while (DocumentTemplate::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
        $data['slug'] = $slug;
        $data['is_active'] = $request->has('is_active');
        $data['is_certificate'] = $request->has('is_certificate');
        $data['requires_review'] = $request->has('requires_review');
        if (!Schema::hasColumn('document_templates', 'is_archived')) {
            unset($data['is_archived']);
        } else {
            $data['is_archived'] = $request->has('is_archived');
        }
        if (!$supportsVersion) {
            unset($data['version']);
        } else {
            $data['version'] = $request->input('version', 'v1');
        }
        if (!$supportsTemplateType) {
            unset($data['template_type']);
        }

        if ($templateType === 'docx') {
            $request->validate([
                'docx_file' => 'required|file|mimes:docx|max:10240',
            ]);
            $versionValue = $supportsVersion ? (string) ($data['version'] ?? 'v1') : 'v1';
            $versionToken = ltrim((string) preg_replace('/[^a-zA-Z0-9]/', '', $versionValue), 'vV');
            $versionToken = $versionToken === '' ? '1' : $versionToken;
            $originalName = $request->file('docx_file')->getClientOriginalName();
            
            $path = $request->file('docx_file')->storeAs(
                'document_templates/' . $data['slug'],
                $data['slug'] . '_v' . $versionToken . '.docx',
                'private'
            );
            if (Schema::hasColumn('document_templates', 'docx_file_path')) {
                $data['docx_file_path'] = $path;
            }
            $data['template_file_path'] = $path;
            $data['original_file_name'] = $originalName;
            $data['html_template'] = $data['html_template'] ?? '';

            $fields = $this->fieldDetector->detectFromDocx(Storage::disk('private')->path($path));
            if (Schema::hasColumn('document_templates', 'detected_fields')) {
                $data['detected_fields'] = $fields;
            }
            
            $invalid = $this->fieldDetector->getInvalidPlaceholders();
            $data['invalid_placeholders'] = $invalid;
            
            // Analyze placeholders
            $analysis = $this->fieldDetector->analyzePlaceholders($data['document_type'], $fields);
            $data['detected_placeholders'] = $analysis['detected'];
            $data['missing_required_placeholders'] = $analysis['missing_required'];
            $data['unknown_placeholders'] = $analysis['unknown'];
            $data['placeholder_mapping'] = $analysis['placeholder_mapping'];
        } else {
            $request->validate([
                'html_template' => 'required',
            ]);
            if (Schema::hasColumn('document_templates', 'docx_file_path')) {
                $data['docx_file_path'] = null;
            }
            $data['template_file_path'] = null;
            $data['original_file_name'] = null;
            $data['detected_placeholders'] = null;
            $data['invalid_placeholders'] = null;
            $data['missing_required_placeholders'] = null;
            $data['unknown_placeholders'] = null;
            $data['placeholder_mapping'] = null;
        }

        $this->templateService->createTemplate($data);

        $redirect = redirect()->route('hrms.document-generation.templates.index')
            ->with('success', 'Template created successfully.');

        if (!empty($invalid)) {
            $warnings = [];
            foreach ($invalid as $inv) {
                $clean = trim(str_replace(['{', '}', '||'], '', $inv));
                $warnings[] = "Invalid placeholder found: {$inv}. Use format {{" . $clean . "}}";
            }
            $redirect = $redirect->with('warning', implode(' | ', $warnings));
        }

        return $redirect;
    }

    public function update(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $supportsTemplateType = Schema::hasColumn('document_templates', 'template_type');
        $supportsVersion = Schema::hasColumn('document_templates', 'version');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'template_type' => 'nullable|in:html,docx',
            'html_template' => 'nullable',
            'docx_file' => 'nullable|file|mimes:docx|max:10240',
            'version' => 'nullable|string|max:50',
        ]);

        $data = $request->except('_token', '_method');
        $templateType = $supportsTemplateType ? ($validated['template_type'] ?? ($template->template_type ?: 'html')) : 'html';
        $data['template_type'] = $templateType;
        if ($request->name !== $template->name) {
            $data['slug'] = Str::slug($data['name']);
        } elseif ($request->filled('slug')) {
            $data['slug'] = Str::slug((string) $request->slug);
        }
        if (!empty($data['slug'])) {
            $baseSlug = $data['slug'];
            $slug = $baseSlug;
            $suffix = 1;
            while (DocumentTemplate::where('slug', $slug)->where('id', '!=', $template->id)->exists()) {
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }
            $data['slug'] = $slug;
        }
        $data['is_active'] = $request->has('is_active');
        $data['is_certificate'] = $request->has('is_certificate');
        $data['requires_review'] = $request->has('requires_review');
        if (!Schema::hasColumn('document_templates', 'is_archived')) {
            unset($data['is_archived']);
        } else {
            $data['is_archived'] = $request->has('is_archived');
        }
        if (!$supportsVersion) {
            unset($data['version']);
        } else {
            $data['version'] = $request->input('version', $template->version ?: 'v1');
        }
        if (!$supportsTemplateType) {
            unset($data['template_type']);
        }

        $invalid = [];
        if ($templateType === 'docx') {
            $fields = null;
            if ($request->hasFile('docx_file')) {
                $slug = $data['slug'] ?? $template->slug;
                $versionValue = $supportsVersion ? (string) ($data['version'] ?? ($template->version ?: 'v1')) : 'v1';
                $versionToken = ltrim((string) preg_replace('/[^a-zA-Z0-9]/', '', $versionValue), 'vV');
                $versionToken = $versionToken === '' ? '1' : $versionToken;
                $originalName = $request->file('docx_file')->getClientOriginalName();
                $path = $request->file('docx_file')->storeAs(
                    'document_templates/' . $slug,
                    $slug . '_v' . $versionToken . '.docx',
                    'private'
                );
                if (Schema::hasColumn('document_templates', 'docx_file_path')) {
                    $data['docx_file_path'] = $path;
                }
                $data['template_file_path'] = $path;
                $data['original_file_name'] = $originalName;
                
                $fields = $this->fieldDetector->detectFromDocx(Storage::disk('private')->path($path));
                if (Schema::hasColumn('document_templates', 'detected_fields')) {
                    $data['detected_fields'] = $fields;
                }
                $invalid = $this->fieldDetector->getInvalidPlaceholders();
                $data['invalid_placeholders'] = $invalid;
            } elseif (!$template->docx_file_path) {
                return redirect()->back()->withErrors(['docx_file' => 'DOCX file is required for DOCX template type.'])->withInput();
            } else {
                $fields = $template->detected_fields ?? $template->detected_placeholders ?? [];
                $invalid = $template->invalid_placeholders ?? [];
            }

            if ($fields !== null) {
                $docType = $data['document_type'] ?? $template->document_type;
                $analysis = $this->fieldDetector->analyzePlaceholders($docType, $fields);
                $data['detected_placeholders'] = $analysis['detected'];
                $data['missing_required_placeholders'] = $analysis['missing_required'];
                $data['unknown_placeholders'] = $analysis['unknown'];
                $data['placeholder_mapping'] = $analysis['placeholder_mapping'];
            }

            $data['html_template'] = $data['html_template'] ?? ($template->html_template ?: '');
        } else {
            $request->validate([
                'html_template' => 'required',
            ]);
            if (Schema::hasColumn('document_templates', 'docx_file_path') && array_key_exists('docx_file_path', $data) && empty($data['docx_file_path'])) {
                $data['docx_file_path'] = null;
            }
            $data['template_file_path'] = null;
            $data['original_file_name'] = null;
            $data['detected_placeholders'] = null;
            $data['invalid_placeholders'] = null;
            $data['missing_required_placeholders'] = null;
            $data['unknown_placeholders'] = null;
            $data['placeholder_mapping'] = null;
        }

        $this->templateService->updateTemplate($template, $data);

        $redirect = redirect()->route('hrms.document-generation.templates.index')
            ->with('success', 'Template updated successfully.');

        if (!empty($invalid)) {
            $warnings = [];
            foreach ($invalid as $inv) {
                $clean = trim(str_replace(['{', '}', '||'], '', $inv));
                $warnings[] = "Invalid placeholder found: {$inv}. Use format {{" . $clean . "}}";
            }
            $redirect = $redirect->with('warning', implode(' | ', $warnings));
        }

        return $redirect;
    }

    public function destroy($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $this->templateService->deleteTemplate($template);

        return redirect()->route('hrms.document-generation.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function toggleArchive($id)
    {
        if (!Schema::hasColumn('document_templates', 'is_archived')) {
            return redirect()->back()->with('error', 'Archive support requires latest document generation migration.');
        }

        $template = DocumentTemplate::findOrFail($id);
        $template->update([
            'is_archived' => !$template->is_archived,
            'updated_by_user_id' => auth()->id() ?? $template->updated_by_user_id,
        ]);

        return redirect()->back()->with(
            'success',
            $template->is_archived ? 'Template archived successfully.' : 'Template restored successfully.'
        );
    }

    public function detectFields($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        if (!Schema::hasColumn('document_templates', 'detected_fields')) {
            return redirect()->back()->with('error', 'Detected fields storage requires latest migration.');
        }
        if (($template->template_type ?: 'html') !== 'docx' || !$template->docx_file_path) {
            return redirect()->back()->with('error', 'Field detection is available only for DOCX templates.');
        }

        $absolute = Storage::disk('private')->path($template->docx_file_path);
        $fields = $this->fieldDetector->detectFromDocx($absolute);
        
        $invalid = $this->fieldDetector->getInvalidPlaceholders();
        
        $analysis = $this->fieldDetector->analyzePlaceholders($template->document_type, $fields);
        
        $template->update([
            'detected_fields' => $fields,
            'detected_placeholders' => $analysis['detected'],
            'invalid_placeholders' => $invalid,
            'missing_required_placeholders' => $analysis['missing_required'],
            'unknown_placeholders' => $analysis['unknown'],
            'placeholder_mapping' => $analysis['placeholder_mapping'],
        ]);

        $redirect = redirect()->back()->with('success', 'Detected and analyzed ' . count($fields) . ' placeholders from template.');
        if (!empty($invalid)) {
            $warnings = [];
            foreach ($invalid as $inv) {
                $clean = trim(str_replace(['{', '}', '||'], '', $inv));
                $warnings[] = "Invalid placeholder found: {$inv}. Use format {{" . $clean . "}}";
            }
            $redirect = $redirect->with('warning', implode(' | ', $warnings));
        }

        return $redirect;
    }

    public function downloadOriginal($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        if (!$template->docx_file_path) {
            abort(404, 'Template file not found.');
        }

        $resolved = $this->fileResolver->resolve($template->docx_file_path);
        if (!$resolved) {
            abort(404, 'Template file not found.');
        }

        return response()->download($resolved['absolute'], basename($resolved['absolute']));
    }
}
