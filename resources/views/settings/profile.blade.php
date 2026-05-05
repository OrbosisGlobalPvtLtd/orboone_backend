@extends('layouts.panel', ['active' => 'profile'])

@section('page_title', 'My Profile')

@section('_content')
@php
    $employee = $profile->employee;
    $employeeProfile = $employee?->profile;
    $role = $profile->primaryRole ?: $profile->role;
    $manager = $employee?->reportingManager?->user;
    $imagePath = $employeeProfile?->profile_image;
    $avatarUrl = $imagePath ? asset('storage/'.$imagePath) : null;
    $initials = collect(explode(' ', trim($profile->name ?? 'U')))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $showProfileForm = $errors->has('name') || $errors->has('email') || $errors->has('phone') || $errors->has('profile_image') || $errors->has('address');
@endphp

<style>
    .profile-page{min-height:calc(100vh - 90px);padding:16px 10px 30px;background:#F6F7FB;}
    .profile-container{max-width:1180px;margin:0 auto;}
    .profile-header,.profile-card{background:#fff;border:1px solid #E7EAF3;border-radius:20px;box-shadow:0 10px 28px rgba(16,24,40,.06);}
    .profile-header{padding:18px;display:flex;align-items:center;gap:16px;margin-bottom:14px;}
    .profile-avatar{width:76px;height:76px;border-radius:22px;background:#F4F2FF;color:#4B00E8;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;overflow:hidden;flex:0 0 auto;}
    .profile-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
    .profile-title{margin:0;color:#101828;font-size:24px;font-weight:900;}
    .profile-subtitle{margin:4px 0 0;color:#667085;font-size:13px;font-weight:700;}
    .profile-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:14px;}
    .profile-card{padding:18px;}
    .profile-card-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px;}
    .profile-card h2{font-size:16px;font-weight:900;color:#101828;margin:0 0 14px;}
    .profile-card-head h2{margin:0;}
    .profile-info{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
    .profile-info-item{padding:12px;border:1px solid #E7EAF3;border-radius:14px;background:#FCFCFD;}
    .profile-label{display:block;color:#667085;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;}
    .profile-value{color:#101828;font-size:13px;font-weight:800;word-break:break-word;}
    .profile-control{width:100%;height:42px;border-radius:12px !important;border:1px solid #E7EAF3 !important;background:#F9FAFB !important;color:#101828 !important;font-size:13px;font-weight:700;padding:8px 12px;}
    textarea.profile-control{height:auto;min-height:92px;}
    .profile-btn{min-height:38px;border-radius:12px;padding:8px 13px;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid #E7EAF3;text-decoration:none !important;background:#fff;color:#101828;cursor:pointer;}
    .profile-btn-primary{color:#fff !important;border-color:transparent;background:linear-gradient(135deg,#4B00E8,#8600EE);}
    .profile-pill{display:inline-flex;padding:6px 9px;border-radius:999px;font-size:11px;font-weight:900;text-transform:uppercase;background:rgba(75,0,232,.08);color:#4B00E8;}
    @media(max-width:900px){.profile-grid,.profile-info{grid-template-columns:1fr;}.profile-header{align-items:flex-start;}}
</style>

<div class="profile-page">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $profile->name }}">
                @else
                    {{ $initials ?: 'U' }}
                @endif
            </div>
            <div>
                <h1 class="profile-title">{{ $profile->name }}</h1>
                <p class="profile-subtitle">{{ $profile->email }}{{ $role?->name ? ' / '.$role->name : '' }}</p>
                <span class="profile-pill">{{ ucfirst($employeeProfile?->profile_status ?? 'pending') }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="profile-grid">
            <div class="profile-card">
                <div class="profile-card-head">
                    <h2>Profile Details</h2>
                    <button type="button" class="profile-btn" id="profile-edit-toggle">
                        <i class="fas fa-pen"></i>
                        Edit
                    </button>
                </div>
                <div class="profile-info mb-4">
                    <div class="profile-info-item">
                        <span class="profile-label">Employee Code</span>
                        <span class="profile-value">{{ $employee?->employee_code ?? '-' }}</span>
                    </div>
                    <div class="profile-info-item">
                        <span class="profile-label">Role</span>
                        <span class="profile-value">{{ $role?->name ?? '-' }}</span>
                    </div>
                    <div class="profile-info-item">
                        <span class="profile-label">Department</span>
                        <span class="profile-value">{{ $employee?->department?->name ?? '-' }}</span>
                    </div>
                    <div class="profile-info-item">
                        <span class="profile-label">Designation</span>
                        <span class="profile-value">{{ $employee?->designation?->name ?? '-' }}</span>
                    </div>
                    <div class="profile-info-item">
                        <span class="profile-label">Reporting Manager</span>
                        <span class="profile-value">{{ $manager?->name ?? '-' }}</span>
                    </div>
                    <div class="profile-info-item">
                        <span class="profile-label">Completion</span>
                        <span class="profile-value">{{ $employeeProfile?->is_profile_completed ? 'Completed' : 'Pending' }}</span>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-edit-form" style="{{ $showProfileForm ? '' : 'display:none;' }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="profile-label">Name</label>
                            <input type="text" name="name" class="profile-control" value="{{ old('name', $profile->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="profile-label">Email</label>
                            <input type="email" name="email" class="profile-control" value="{{ old('email', $profile->email) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="profile-label">Phone</label>
                            <input type="text" name="phone" class="profile-control" value="{{ old('phone', $profile->phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="profile-label">Profile Image</label>
                            <input type="file" name="profile_image" class="profile-control" accept="image/*">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="profile-label">Address</label>
                            <textarea name="address" class="profile-control">{{ old('address', $employeeProfile?->address) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap" style="gap:8px;">
                        <button type="submit" class="profile-btn profile-btn-primary">
                            <i class="fas fa-save"></i>
                            Save Profile
                        </button>
                        <a href="{{ route('profile.index') }}" class="profile-btn">Cancel</a>
                    </div>
                </form>
            </div>

            <div class="profile-card" id="change-password">
                <h2>Change Password</h2>
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="profile-label">Current Password</label>
                        <input type="password" name="current_password" class="profile-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="profile-label">New Password</label>
                        <input type="password" name="password" class="profile-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="profile-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="profile-control" required>
                    </div>

                    <button type="submit" class="profile-btn profile-btn-primary">
                        <i class="fas fa-lock"></i>
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editButton = document.getElementById('profile-edit-toggle');
        var editForm = document.getElementById('profile-edit-form');

        if (editButton && editForm) {
            editButton.addEventListener('click', function () {
                editForm.style.display = 'block';
                editButton.style.display = 'none';
            });

            if (editForm.style.display !== 'none') {
                editButton.style.display = 'none';
            }
        }
    });
</script>
@endsection
