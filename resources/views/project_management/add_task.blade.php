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
</style>

<div class="page-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('project_management.tasks.index') }}" class="btn btn-back">
                        <i class="fas fa-chevron-left mr-2"></i> Back to Dashboard
                    </a>
                </div>

                <div class="card custom-card">
                    <div class="card-header-orb">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 p-3 rounded-circle" style="background: rgba(255,255,255,0.1);">
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
                                            <input type="text" name="title" class="form-control form-control-orb" 
                                                   placeholder="e.g. Implement Task Module API" value="{{ old('title', $task->title ?? '') }}" required>
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
                                            <select name="user_id" class="form-control form-control-orb" required>
                                                <option value="">Select Employee</option>
                                                @foreach($employees as $emp)
                                                    @php $uId = $emp->user_id ?? $emp->user->id ?? null; @endphp
                                                    @if($uId)
                                                        <option value="{{ $uId }}" {{ old('user_id', $task->user_id ?? '') == $uId ? 'selected' : '' }}>
                                                            {{ $emp->name ?? ($emp->user->name ?? 'Employee #' . $uId) }}
                                                        </option>
                                                    @endif
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
                                            <input type="date" name="due_date" class="form-control form-control-orb" 
                                                   value="{{ old('due_date', isset($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : date('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group-orb">
                                        <label>Status</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text input-group-text-orb"><i class="fas fa-tasks"></i></span>
                                            </div>
                                            <select name="status" class="form-control form-control-orb">
                                                <option value="pending" {{ old('status', $task->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in_progress" {{ in_array(old('status', $task->status ?? ''), ['in_progress', 'progress']) ? 'selected' : '' }}>In Progress</option>
                                                <option value="on_hold" {{ old('status', $task->status ?? '') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                                <option value="completed" {{ old('status', $task->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="verified" {{ old('status', $task->status ?? '') == 'verified' ? 'selected' : '' }}>Verified</option>
                                                <option value="closed" {{ old('status', $task->status ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="form-section-title">
                                <i class="fas fa-align-left"></i> Detailed Instructions
                            </div>
                            <div class="form-group mb-4">
                                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Describe task instructions..." required>{{ old('description', isset($task->clean_description) ? $task->clean_description : '') }}</textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
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
@endsection