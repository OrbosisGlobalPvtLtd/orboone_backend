<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\ProjectManagement\WorkReportTemplateM;
use App\Models\HRMS\ProjectManagement\WorkReportTemplateFieldM;
use App\Services\HRMS\ProjectManagement\WorkReportTemplateS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkReportTemplateC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private WorkReportTemplateS $templateService)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        abort_unless($this->userHasPermission('projects.work_report.templates.manage') || $this->canViewAll('projects.manage'), 403);

        $this->templateService->seedDefaultTemplates();
        $templates = WorkReportTemplateM::with(['fields', 'project'])->orderBy('id')->get();

        return view('hrms.projects.work-reports.templates', compact('templates'));
    }

    public function store(Request $request)
    {
        abort_unless($this->userHasPermission('projects.work_report.templates.manage') || $this->canViewAll('projects.manage'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'required|string|max:50|unique:work_report_templates,code',
            'employee_role_type' => 'required|string|max:50',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        WorkReportTemplateM::create(array_merge($validated, [
            'is_active' => 1,
            'created_by' => Auth::id(),
        ]));

        return back()->with('success', 'Work report template created successfully.');
    }

    public function storeField(Request $request, $templateId)
    {
        abort_unless($this->userHasPermission('projects.work_report.templates.manage') || $this->canViewAll('projects.manage'), 403);

        $template = WorkReportTemplateM::findOrFail($templateId);

        $validated = $request->validate([
            'field_key' => 'required|string|max:50',
            'field_label' => 'required|string|max:100',
            'field_type' => 'required|in:text,textarea,number,date,select,multiselect,checkbox,radio,url,status,duration',
            'placeholder' => 'nullable|string|max:191',
            'options_json' => 'nullable|array',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        WorkReportTemplateFieldM::create(array_merge($validated, [
            'template_id' => $template->id,
            'is_active' => 1,
        ]));

        return back()->with('success', 'Template field added successfully.');
    }
}
