<?php

namespace App\Http\Controllers\Web\HRMS\DocumentGeneration;

use App\Http\Controllers\Controller;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Services\HRMS\DocumentGeneration\DocumentTemplateS;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentTemplateC extends Controller
{
    protected $templateService;
    protected $fileResolver;

    public function __construct(
        DocumentTemplateS $templateService,
        HrmsFileResolverS $fileResolver
    ) {
        $this->templateService = $templateService;
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
        $docxTemplates = 0; // DOCX deprecated
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'html_template' => 'required',
            'version' => 'nullable|string|max:50',
        ]);

        $data = $request->except('_token');
        $data['template_type'] = 'html';

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

        if (Schema::hasColumn('document_templates', 'is_archived')) {
            $data['is_archived'] = $request->has('is_archived');
        } else {
            unset($data['is_archived']);
        }

        if (Schema::hasColumn('document_templates', 'version')) {
            $data['version'] = $request->input('version', 'v1');
        } else {
            unset($data['version']);
        }

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

        $this->templateService->createTemplate($data);

        return redirect()->route('hrms.document-generation.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function update(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'html_template' => 'required',
            'version' => 'nullable|string|max:50',
        ]);

        $data = $request->except('_token', '_method');
        $data['template_type'] = 'html';

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

        if (Schema::hasColumn('document_templates', 'is_archived')) {
            $data['is_archived'] = $request->has('is_archived');
        } else {
            unset($data['is_archived']);
        }

        if (Schema::hasColumn('document_templates', 'version')) {
            $data['version'] = $request->input('version', $template->version ?: 'v1');
        } else {
            unset($data['version']);
        }

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

        $this->templateService->updateTemplate($template, $data);

        return redirect()->route('hrms.document-generation.templates.index')
            ->with('success', 'Template updated successfully.');
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

    public function clone($id)
    {
        $original = DocumentTemplate::findOrFail($id);
        $clone = $original->replicate();

        $baseName = $original->name . ' - Copy';
        $name = $baseName;
        $suffix = 1;
        while (DocumentTemplate::where('name', $name)->exists()) {
            $name = $baseName . ' (' . $suffix . ')';
            $suffix++;
        }
        $clone->name = $name;

        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 1;
        while (DocumentTemplate::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
        $clone->slug = $slug;
        $clone->created_by_user_id = auth()->id();
        $clone->updated_by_user_id = auth()->id();
        $clone->save();

        // Duplicate the fields
        foreach ($original->fields as $field) {
            $newField = $field->replicate();
            $newField->template_id = $clone->id;
            $newField->save();
        }

        return redirect()->route('hrms.document-generation.templates.index')
            ->with('success', 'Template cloned successfully.');
    }
}
