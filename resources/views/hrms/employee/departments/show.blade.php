@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
<style>
    :root {
        --primary-orb: #1560ab;
        --secondary-orb: #0099cc;
        --soft-bg: #f4f7fa;
    }

    .custom-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        background: white;
        overflow: hidden;
    }

    .card-gradient-header {
        background: linear-gradient(135deg, var(--primary-orb), var(--secondary-orb));
        color: white;
        padding: 30px 25px;
        position: relative;
    }

    .header-icon {
        position: absolute;
        right: 30px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 3.5rem;
        opacity: 0.15;
    }

    .detail-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        color: var(--secondary-orb);
        margin-bottom: 8px;
        display: block;
    }

    .detail-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        display: block;
        padding: 10px 15px;
        background: #f8f9fc;
        border-radius: 12px;
        border: 1px solid #e3e6f0;
    }

    .section-title {
        font-weight: 700;
        color: var(--primary-orb);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f4f8;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 12px;
        background: rgba(21, 96, 171, 0.1);
        padding: 8px;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .btn-orb-edit {
        background: #f6c23e;
        color: #fff !important;
        border-radius: 12px;
        padding: 12px 30px;
        font-weight: 700;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(246, 194, 62, 0.3);
    }

    .btn-orb-edit:hover {
        background: #dda20a;
        transform: translateY(-2px);
    }

    .btn-orb-delete {
        background: #e74a3b;
        color: #fff !important;
        border-radius: 12px;
        padding: 12px 30px;
        font-weight: 700;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(231, 74, 59, 0.3);
    }

    .btn-orb-delete:hover {
        background: #be2617;
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid py-4 px-4">
    <!-- Breadcrumb & Back Button -->
    <div class="mb-4">
        <a href="{{ route('hrms.departments.index') }}" class="text-muted small font-weight-bold text-decoration-none">
            <i class="fas fa-arrow-left mr-2"></i> Back to Directory
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card custom-card">
                <!-- Premium Header -->
                <div class="card-gradient-header">
                    <div class="header-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h2 class="font-weight-bold mb-1">{{ $department->name }}</h2>
                    <p class="mb-0 text-white-50">Department ID: <span class="text-white">#{{ $department->code }}</span></p>
                </div>

                <div class="card-body p-5">
                    <!-- General Information Section -->
                    <div class="mb-5">
                        <h5 class="section-title">
                            <i class="fas fa-info-circle"></i> Department Overview
                        </h5>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="detail-label">Official Name</label>
                                <div class="detail-value">
                                    <i class="fas fa-building mr-2 text-muted"></i> {{ $department->name }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="detail-label">Internal Department Code</label>
                                <div class="detail-value">
                                    <i class="fas fa-hashtag mr-2 text-muted"></i> {{ $department->code }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="mb-5">
                        <h5 class="section-title">
                            <i class="fas fa-map-marker-alt"></i> Location Details
                        </h5>
                        <div class="row">
                            <div class="col-12">
                                <label class="detail-label">Primary Office Address</label>
                                <div class="detail-value">
                                    <i class="fas fa-map-pin mr-2 text-danger"></i> {{ $department->address }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-5">

                    <!-- Actions Section -->
                    @php
                        // Robust Access Check - menu_id 11 for Departments
                        $access = collect($accesses)->where('menu_id', 11)->first();
                    @endphp

                    @if ($access && $access->status == 10)
                    <div class="d-flex flex-wrap align-items-center">
                        <a href="{{ route('hrms.departments.edit', $department->id) }}" class="btn btn-orb-edit mr-3 shadow-sm">
                            <i class="fas fa-edit mr-2"></i> Modify Department
                        </a>
                        
                        <form action="{{ route('hrms.departments.destroy', $department->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-orb-delete shadow-sm" onclick="return confirm('WARNING: Are you sure you want to delete this department? This action cannot be undone.')">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Permanently
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection