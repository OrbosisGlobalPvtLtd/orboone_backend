<?php

namespace App\Http\Controllers\Web\HRMS\DocumentGeneration;

use App\Http\Controllers\Controller;
use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use App\Services\HRMS\DocumentGeneration\DocumentTemplateS;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentTemplateC extends Controller
{
    protected $templateService;

    public function __construct(DocumentTemplateS $templateService)
    {
        $this->templateService = $templateService;
    }

    public function index(Request $request)
    {
        $query = DocumentTemplate::query();

        if ($request->filled('type')) {
            $query->where('document_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $templates = $query->latest()->paginate(15);
        $totalTemplates = DocumentTemplate::count();
        $activeTemplates = DocumentTemplate::where('is_active', true)->count();

        return view('hrms.document-generation.templates.index', compact('templates', 'totalTemplates', 'activeTemplates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'html_template' => 'required',
        ]);

        $data = $request->except('_token');
        $data['slug'] = Str::slug($data['name']) . '-' . time();
        $data['is_active'] = $request->has('is_active');
        $data['is_certificate'] = $request->has('is_certificate');
        $data['requires_review'] = $request->has('requires_review');

        $this->templateService->createTemplate($data);

        return redirect()->route('hrms.document-generation.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function update(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'html_template' => 'required',
        ]);

        $data = $request->except('_token', '_method');
        if ($request->name !== $template->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . time();
        }
        $data['is_active'] = $request->has('is_active');
        $data['is_certificate'] = $request->has('is_certificate');
        $data['requires_review'] = $request->has('requires_review');

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
}
