@extends('layouts.panel', ['active' => 'projects'])

@section('page_title', 'Work Report Templates')

@section('_head')
<style>
:root {
    --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
    --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
    --orb-bg: #F8FAFC;
    --orb-card: #FFFFFF;
    --orb-border: #E2E8F0;
    --orb-text: #0F172A;
    --orb-muted: #64748B;
    --orb-soft: rgba(75, 0, 232, 0.06);
    --orb-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.prj-page {
    padding: 24px 20px 48px;
    background: var(--orb-bg);
    min-height: calc(100vh - 90px);
}

.prj-container {
    max-width: 1550px;
    margin: 0 auto;
}

.prj-hero {
    background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #ffffff;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.tpl-card {
    background: var(--orb-card);
    border: 1px solid var(--orb-border);
    border-radius: 16px;
    box-shadow: var(--orb-shadow);
    height: 100%;
    display: flex;
    flex-direction: column;
}
</style>
@endsection

@section('_content')
<div class="prj-page">
    <div class="prj-container">
        <!-- Hero Header -->
        <div class="prj-hero">
            <div>
                <h1 class="text-white font-weight-bold mb-1"><i class="fas fa-file-invoice mr-2"></i>Work Report Templates</h1>
                <p class="mb-0 opacity-90">Configure role-specific dynamic daily work report forms for Developers, Testers, Sales, HR, and Leads.</p>
            </div>
            <div>
                <button type="button" class="btn btn-light font-weight-bold px-4 py-2" style="border-radius: 12px; color: var(--orb-primary);" data-toggle="modal" data-target="#createTemplateModal">
                    <i class="fas fa-plus mr-2"></i>Create Template
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="row">
            @foreach($templates as $template)
            <div class="col-12 col-md-6 mb-4">
                <div class="tpl-card">
                    <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1">{{ $template->name }}</h5>
                            <span class="small text-muted">Code: <code>{{ $template->code }}</code></span>
                        </div>
                        <span class="badge badge-primary text-uppercase px-3 py-1 font-weight-bold" style="border-radius: 12px;">{{ $template->employee_role_type }}</span>
                    </div>
                    <div class="p-4 flex-fill">
                        <p class="text-secondary small mb-3">{{ $template->description ?? 'No template description.' }}</p>
                        
                        <div class="font-weight-bold text-dark mb-2"><i class="fas fa-list-ol mr-1 text-primary"></i> Dynamic Form Fields ({{ $template->fields->count() }})</div>
                        <div class="list-group list-group-flush mb-3">
                            @forelse($template->fields as $field)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 small border-bottom-0">
                                <div>
                                    <strong class="text-dark">{{ $field->field_label }}</strong> <code>({{ $field->field_key }})</code>
                                    @if($field->is_required)<span class="text-danger font-weight-bold">*</span>@endif
                                </div>
                                <span class="badge badge-light border text-uppercase font-weight-bold">{{ $field->field_type }}</span>
                            </div>
                            @empty
                            <div class="text-muted small py-2">No custom fields added yet.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="p-3 bg-light border-top text-right">
                        <button class="btn btn-sm btn-outline-primary font-weight-bold px-3" style="border-radius: 8px;" data-toggle="modal" data-target="#addFieldModal{{ $template->id }}">
                            <i class="fas fa-plus mr-1"></i> Add Form Field
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add Field Modal -->
            <div class="modal fade" id="addFieldModal{{ $template->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                        <form action="{{ route('projects.templates.fields.store', $template->id) }}" method="POST">
                            @csrf
                            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                                <h5 class="modal-title font-weight-bold">Add Field to: {{ $template->name }}</h5>
                                <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body p-4 text-left">
                                <div class="form-group">
                                    <label class="font-weight-bold">Field Key <span class="text-danger">*</span></label>
                                    <input type="text" name="field_key" class="form-control" placeholder="e.g. bug_ids" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Field Label <span class="text-danger">*</span></label>
                                    <input type="text" name="field_label" class="form-control" placeholder="e.g. Bug IDs / Links" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Field Type <span class="text-danger">*</span></label>
                                    <select name="field_type" class="form-control" required>
                                        <option value="text">Text Input</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="number">Number</option>
                                        <option value="select">Dropdown Select</option>
                                        <option value="url">URL Link</option>
                                        <option value="duration">Duration (Minutes)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Placeholder</label>
                                    <input type="text" name="placeholder" class="form-control" placeholder="e.g. Enter details...">
                                </div>
                                <div class="form-group form-check mb-0">
                                    <input type="checkbox" name="is_required" class="form-check-input" id="req{{ $template->id }}" value="1">
                                    <label class="form-check-label font-weight-bold" for="req{{ $template->id }}">Is Required Field</label>
                                </div>
                            </div>
                            <div class="modal-footer bg-light px-4 py-3">
                                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                                <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;">Add Field</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form action="{{ route('projects.templates.store') }}" method="POST">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, {{ $branding['primary_color'] ?? '#4B00E8' }} 0%, {{ $branding['secondary_color'] ?? '#FF5252' }} 100%);">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Create Work Report Template</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Sales Log Template" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Template Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. sales_log" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Employee Role Type <span class="text-danger">*</span></label>
                        <select name="employee_role_type" class="form-control" required>
                            <option value="developer">Developer</option>
                            <option value="tester">QA / Tester</option>
                            <option value="sales">Sales</option>
                            <option value="marketing">Marketing</option>
                            <option value="hr">HR / Admin</option>
                            <option value="team_lead">Team Lead</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief template description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn text-white px-4 font-weight-bold" style="background: var(--orb-primary); border-radius: 10px;"><i class="fas fa-save mr-1"></i> Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
