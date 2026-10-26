@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@php
    $isEdit = isset($task) && $task->exists;
    $title = $isEdit ? 'Modify Task Details' : 'Create New Task';
    $subtitle = $isEdit ? "Task ID: #$task->id" : 'Assign project work and track team deliverables';
    $action = $isEdit ? route('project_management.tasks.update', $task->id) : route('project_management.tasks.create');
    $buttonText = $isEdit ? 'Update Task' : 'Publish Task';
    $icon = $isEdit ? 'fa-edit' : 'fa-plus-circle';
@endphp

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endsection

@section('_content')
<style>
    :root {
        --orb-primary: {{ $branding['primary_color'] ?? '#4B00E8' }};
        --orb-secondary: {{ $branding['secondary_color'] ?? '#FF5252' }};
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .task-create-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .task-create-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .task-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 20px;
        padding: 24px 30px;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .task-form-card {
        background: var(--orb-card);
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
        overflow: hidden;
    }

    .form-section-title {
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-primary);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .custom-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--orb-text);
        margin-bottom: 8px;
        display: block;
    }

    .custom-input {
        height: 42px;
        border: 1px solid var(--orb-border);
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--orb-text);
        background: #FFFFFF;
        width: 100%;
        transition: all 0.2s ease;
    }

    .custom-input:focus {
        border-color: var(--orb-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.1);
    }

    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid var(--orb-border) !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

    .btn-submit-premium {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 36px;
        font-weight: 700;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        box-shadow: 0 8px 20px rgba(75, 0, 232, 0.2);
        cursor: pointer;
    }

    .btn-submit-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(75, 0, 232, 0.3);
        color: white;
    }

    .btn-glass-back {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #ffffff !important;
        border-radius: 50px;
        padding: 8px 18px;
        font-weight: 700;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none !important;
        transition: all 0.2s;
    }
    .btn-glass-back:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: translateY(-1px);
    }
</style>

<div class="task-create-page">
    <div class="task-create-container">

        <!-- Hero Header -->
        <div class="task-header-premium">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle" style="background: rgba(255,255,255,0.15); font-size: 20px;">
                    <i class="fas {{ $icon }}"></i>
                </div>
                <div>
                    <h3 class="font-weight-bold text-white mb-1" style="font-size: 22px;">{{ $title }}</h3>
                    <p class="text-white-50 small mb-0">{{ $subtitle }}</p>
                </div>
            </div>
            <div>
                <a href="{{ route('project_management.tasks.index') }}" class="btn-glass-back">
                    <i class="fas fa-chevron-left"></i> Back to Tasks
                </a>
            </div>
        </div>

        <div class="task-form-card p-4 p-md-5">
            <form action="{{ $action }}" method="POST" id="taskForm">
                @csrf

                <div class="form-section-title">
                    <i class="fas fa-info-circle"></i> Basic Task Details
                </div>

                <div class="row">
                    <div class="col-12 mb-4">
                        <label class="custom-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="custom-input" 
                               placeholder="e.g. Implement Task Module API" value="{{ old('title', $task->title ?? '') }}" required>
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="custom-label">Assign To Employee <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control select2-searchable" required style="width: 100%;">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                @php $uId = $emp->user_id ?? $emp->user->id ?? null; @endphp
                                @if($uId)
                                    <option value="{{ $uId }}" {{ old('user_id', $task->user_id ?? '') == $uId ? 'selected' : '' }}>
                                        {{ $emp->name ?? ($emp->user->name ?? 'Employee #' . $uId) }} ({{ $emp->employee_code ?? '' }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="custom-label">Due Date <span class="text-danger">*</span></label>
                        <x-form.date-picker 
                            name="due_date" 
                            :value="old('due_date', isset($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : date('Y-m-d'))" 
                            placeholder="Select Due Date" 
                            required="required"
                        />
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="custom-label">Task Status</label>
                        <select name="status" class="form-control select2-searchable" style="width: 100%;">
                            <option value="pending" {{ old('status', $task->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ in_array(old('status', $task->status ?? ''), ['in_progress', 'progress']) ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ old('status', $task->status ?? '') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status', $task->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="verified" {{ old('status', $task->status ?? '') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="closed" {{ old('status', $task->status ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3" style="border-color: var(--orb-border);">

                <div class="form-section-title">
                    <i class="fas fa-align-left"></i> Detailed Description & Instructions
                </div>

                <div class="form-group mb-4">
                    <textarea id="description" name="description" class="custom-input" style="height: 140px; padding: 12px;" placeholder="Describe task requirements, deliverables, and guidelines..." required>{{ old('description', isset($task->clean_description) ? $task->clean_description : '') }}</textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('project_management.tasks.index') }}" class="btn btn-light font-weight-bold" style="border-radius: 50px; padding: 10px 24px; font-size: 13.5px; border: 1px solid var(--orb-border);">
                        Cancel
                    </a>
                    <button type="submit" class="btn-submit-premium">
                        <i class="fas fa-paper-plane"></i> {{ $buttonText }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-searchable').select2({
        width: '100%'
    });
});
</script>
@endsection