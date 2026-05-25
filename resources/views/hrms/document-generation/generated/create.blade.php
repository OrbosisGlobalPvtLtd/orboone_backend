@extends('layouts.panel', ['active' => 'document_generation'])

@section('page_title', 'Generate Document')

@section('_head')
<style>
.document-page {
    background: var(--orb-bg, #F6F7FB);
    padding: 24px;
    min-height: calc(100vh - 80px);
}
.orb-card {
    background: white;
    border: 1px solid #E7EAF3;
    border-radius: 22px;
    box-shadow: 0 4px 15px rgba(16,24,40,.03);
    overflow: hidden;
}
.orb-card-header {
    background: linear-gradient(135deg, #4B00E8 0%, #8600EE 100%);
    padding: 20px 24px;
    color: white;
    border-radius: 22px 22px 0 0;
}
.orb-card-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
}
.btn-orb-primary {
    background: linear-gradient(135deg, #4B00E8 0%, #8600EE 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 10px 24px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}
.btn-orb-primary:hover { opacity: 0.9; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(75,0,232,0.3); }
.btn-orb-soft {
    background: #F4F2FF;
    color: #4B00E8;
    border: none;
    border-radius: 50px;
    padding: 10px 24px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}
.btn-orb-soft:hover { background: #E9E5FF; }
.form-label {
    font-size: 11px;
    text-transform: uppercase;
    color: #667085;
    font-weight: 600;
}
.form-control, .form-select {
    height: 42px;
    border-radius: 10px;
    border: 1px solid #E7EAF3;
}
</style>
@endsection

@section('_content')
<div class="document-page">
    <div class="row">
        <div class="col-md-5">
            <div class="orb-card mb-4">
                <div class="orb-card-header">
                    <h4><i class="fas fa-magic me-2"></i> Document Setup</h4>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('hrms.document-generation.generated.create') }}" id="setupForm">
                        <div class="mb-3">
                            <label class="form-label">Select Employee</label>
                            <select name="employee_id" class="form-select" onchange="document.getElementById('setupForm').submit()">
                                <option value="">-- Candidate / Manual Entry --</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->display_name }} ({{ $emp->employee_code }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Template</label>
                            <select name="template_id" class="form-select" onchange="document.getElementById('setupForm').submit()" required>
                                <option value="">-- Choose Template --</option>
                                @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" {{ request('template_id') == $tpl->id ? 'selected' : '' }}>
                                    {{ $tpl->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    @if($selectedTemplate)
                    <hr class="my-4 border-light">
                    
                    <form method="POST" action="{{ route('hrms.document-generation.generated.store') }}" id="generateForm">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                        <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">
                        
                        <h6 class="fw-bold mb-3">Dynamic Fields Override</h6>
                        <p class="text-muted small mb-3">Fill these to override default employee data or provide missing candidate details.</p>
                        
                        <!-- Manual fields block -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Designation</label>
                                <input type="text" name="manual_fields[designation]" class="form-control" placeholder="e.g. Software Engineer">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Joining Date</label>
                                <input type="date" name="manual_fields[joining_date]" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Monthly Salary</label>
                                <input type="number" name="manual_fields[salary_monthly]" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Annual Salary (CTC)</label>
                                <input type="number" name="manual_fields[salary_annual]" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        
                        @if($selectedTemplate->fields && $selectedTemplate->fields->count() > 0)
                            <h6 class="fw-bold mb-3 mt-3">Template Specific Fields</h6>
                            @foreach($selectedTemplate->fields as $field)
                            <div class="mb-3">
                                <label class="form-label">{{ $field->field_label }}</label>
                                @if($field->field_type == 'textarea')
                                    <textarea name="manual_fields[{{ $field->field_key }}]" class="form-control" rows="3" {{ $field->is_required ? 'required' : '' }}></textarea>
                                @else
                                    <input type="text" name="manual_fields[{{ $field->field_key }}]" class="form-control" {{ $field->is_required ? 'required' : '' }}>
                                @endif
                            </div>
                            @endforeach
                        @endif

                        <div class="d-flex gap-3 mt-4">
                            <button type="button" class="btn-orb-soft w-50" onclick="previewDocument()"><i class="fas fa-eye"></i> Preview</button>
                            <button type="submit" class="btn-orb-primary w-50"><i class="fas fa-check-circle"></i> Generate PDF</button>
                        </div>
                    </form>
                    @else
                    <div class="alert alert-light border text-center p-4 mt-3" style="border-radius: 12px;">
                        <i class="fas fa-hand-pointer text-muted fs-3 mb-2"></i>
                        <p class="mb-0 text-muted">Select a template to configure dynamic fields and generate.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="orb-card h-100" style="min-height: 600px;">
                <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold"><i class="fas fa-file-pdf text-danger me-2"></i> Document Preview</h5>
                </div>
                <div class="card-body p-0 position-relative" style="background: #e5e5e5; overflow: auto;">
                    <div id="previewContainer" style="padding: 40px; display: flex; justify-content: center; width: 100%; min-height: 100%;">
                        @if($selectedTemplate)
                            <div class="bg-white shadow-sm p-5" style="width: 21cm; min-height: 29.7cm; font-family: sans-serif;" id="previewContent">
                                {!! $selectedTemplate->html_template !!}
                            </div>
                        @else
                            <div class="text-center align-self-center text-muted">
                                <i class="fas fa-file-invoice fs-1 mb-3 opacity-50"></i>
                                <h5>No Preview Available</h5>
                                <p>Select a template and click preview.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div id="previewLoader" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 10;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewDocument() {
    let form = document.getElementById('generateForm');
    let formData = new FormData(form);
    
    document.getElementById('previewLoader').classList.remove('d-none');
    document.getElementById('previewLoader').classList.add('d-flex');

    fetch("{{ route('hrms.document-generation.generated.preview') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('previewContent').innerHTML = data.html;
    })
    .catch(error => {
        alert('Error generating preview');
        console.error(error);
    })
    .finally(() => {
        document.getElementById('previewLoader').classList.add('d-none');
        document.getElementById('previewLoader').classList.remove('d-flex');
    });
}
</script>
@endpush
