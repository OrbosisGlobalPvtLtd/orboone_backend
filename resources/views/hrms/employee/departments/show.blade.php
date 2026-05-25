@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
@include('hrms.employee.partials.styles')

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