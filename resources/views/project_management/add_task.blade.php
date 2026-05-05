@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@php
    $isEdit = isset($task) && $task->exists;
    $title = $isEdit ? 'Modify Task Details' : 'Create New Task';
    $subtitle = $isEdit ? "Task ID: #$task->id" : 'Assign project work and track progress';
    $action = $isEdit ? route('project_management.tasks.update', $task->id) : route('project_management.tasks.create');
    $buttonText = $isEdit ? 'Update Task Info' : 'Publish Task Now';
    $icon = $isEdit ? 'fa-edit' : 'fa-plus-circle';
@endphp

@section('_content')
<style>
    :root {
        --primary-orb: #1560ab;
        --secondary-orb: #0099cc;
        --soft-bg: #f4f7fa;
        --text-dark: #2d3436;
    }

    body {
        background-color: var(--soft-bg);
    }

    .page-container {
        padding: 40px 20px;
    }

    .custom-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        background: white;
        overflow: hidden;
    }

    .card-header-orb {
        background: linear-gradient(135deg, var(--primary-orb), var(--secondary-orb));
        padding: 25px;
        color: white;
        border: none;
    }

    .form-section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary-orb);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .form-section-title i {
        margin-right: 10px;
        font-size: 1.1rem;
    }

    .input-group-orb {
        margin-bottom: 25px;
    }

    .input-group-orb label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-dark);
        font-size: 0.9rem;
    }

    .input-group-text-orb {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-right: none;
        border-radius: 12px 0 0 12px;
        color: var(--primary-orb);
    }

    .form-control-orb {
        border: 1px solid #e3e6f0;
        border-radius: 0 12px 12px 0;
        padding: 12px 18px;
        font-size: 0.95rem;
        transition: all 0.3s;
        height: auto;
    }

    .form-control-orb:focus {
        border-color: var(--secondary-orb);
        box-shadow: 0 0 0 4px rgba(0, 153, 204, 0.08);
        outline: none;
    }

    .select-orb {
        border-radius: 0 12px 12px 0 !important;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%231560ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 18px;
    }

    .btn-submit-orb {
        background: linear-gradient(135deg, var(--primary-orb), var(--secondary-orb));
        color: white;
        border: none;
        border-radius: 50px;
        padding: 15px 45px;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        box-shadow: 0 8px 20px rgba(21, 96, 171, 0.2);
    }

    .btn-submit-orb:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(21, 96, 171, 0.3);
        color: white;
    }

    .btn-back {
        background: white;
        color: var(--primary-orb);
        border: 1px solid #e3e6f0;
        border-radius: 50px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-back:hover {
        background: #f8f9fc;
        transform: translateX(-3px);
    }
</style>

<div class="page-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Top Navigation -->
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('project_management.tasks.index') }}" class="btn btn-back">
                        <i class="fas fa-chevron-left mr-2"></i> Back to Dashboard
                    </a>
                    @if($isEdit)
                        <div class="text-muted small">
                            <i class="far fa-clock mr-1"></i> Last Edited: {{ $task->updated_at->format('M d, Y H:i') }}
                        </div>
                    @else
                        <div class="badge badge-soft-primary p-2">
                            <i class="fas fa-info-circle mr-1"></i> * Marked fields are mandatory
                        </div>
                    @endif
                </div>

                <!-- Main Card -->
                <div class="card custom-card">
                    <div class="card-header-orb">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 p-3 bg-white-5 rounded-circle" style="background: rgba(255,255,255,0.1);">
                                <i class="fas {{ $icon }} fa-2x"></i>
                            </div>
                            <div>
                                <h3 class="m-0 font-weight-bold">{{ $title }}</h3>
                                <p class="m-0 small text-white-50">{{ $subtitle }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form action="{{ $action }}" method="POST" id="taskForm">
                            @csrf
                            @if($isEdit)
                                @method('POST') {{-- Standard POST as in web.php, but you could use PUT if web.php used Route::put --}}
                                {{-- Note: Route::post('/update_task/{id}', [TaskmanagementController::class, 'update'])->name('project_management.tasks.update'); --}}
                            @endif
                            
                            <!-- Section 1: Basic Details -->
                            <div class="form-section-title">
                                <i class="fas fa-edit"></i> Basic Task Information
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="input-group-orb">
                                        <label>Task Title <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text input-group-text-orb"><i class="fas fa-heading"></i></span>
                                            </div>
                                            <input type="text" name="title" class="form-control form-control-orb @error('title') is-invalid @enderror" 
                                                   placeholder="e.g. Design UI/UX for HRMS" value="{{ old('title', $task->title ?? '') }}" required>
                                            @error('title')
                                                <div class="invalid-feedback ml-5">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group-orb">
                                        <label>Assign To <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text input-group-text-orb"><i class="fas fa-user-tag"></i></span>
                                            </div>
                                            <select name="user_id" class="form-control form-control-orb select-orb @error('user_id') is-invalid @enderror" required>
                                                <option value="">Select Employee</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->user->id }}" {{ old('user_id', $task->user_id ?? '') == $employee->user->id ? 'selected' : '' }}>
                                                        {{ $employee->employeeDetail->name ?? $employee->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group-orb">
                                        <label>Due Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text input-group-text-orb"><i class="fas fa-calendar-day"></i></span>
                                            </div>
                                            <input type="date" name="due_date" class="form-control form-control-orb @error('due_date') is-invalid @enderror" 
                                                   value="{{ old('due_date', $task->due_date ?? '') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group-orb">
                                        <label>Priority/Status</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text input-group-text-orb"><i class="fas fa-thermometer-half"></i></span>
                                            </div>
                                            <select name="status" class="form-control form-control-orb select-orb">
                                                <option value="pending" {{ old('status', $task->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in_progress" {{ old('status', $task->status ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="completed" {{ old('status', $task->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Section 2: Detailed Description -->
                            <div class="form-section-title">
                                <i class="fas fa-align-left"></i> Detailed Instructions
                            </div>
                            <div class="form-group mb-5">
                                <textarea id="description" name="description" class="form-control" placeholder="Describe the task details here...">{{ old('description', $task->description ?? '') }}</textarea>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-5 p-4 bg-light rounded-lg">
                                <p class="text-muted small m-0"><i class="fas fa-shield-alt mr-1"></i> Data is securely processed and saved.</p>
                                <button type="submit" class="btn btn-submit-orb">
                                    <i class="fas fa-paper-plane mr-2"></i> {{ $buttonText }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Standard Textarea for Plain Text --}}
<script>
    document.getElementById('taskForm').addEventListener('submit', function (event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        this.classList.add('was-validated');
    }, false);
</script>
@endsection