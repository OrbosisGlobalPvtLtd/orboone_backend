@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'document-management'])

@section('page_title', 'Company Documents & Policies')

@section('_head')
@include('settings.partials.styles')
<style>
    .policies-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Small Circular Icon Box */
    .policy-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(239, 68, 68, 0.06);
        color: #EF4444;
        font-size: 14px;
        flex-shrink: 0;
    }

    .policy-rev-pill {
        font-size: 9px;
        font-weight: 850;
        color: #64748B;
        background: #F1F5F9;
        padding: 4px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        font-family: monospace;
    }

    /* Policy Card title truncation */
    .policy-card-title {
        font-size: 14px;
        font-weight: 850;
        color: var(--set-text);
        line-height: 1.4;
        margin-top: 12px;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 40px; /* 2 lines max */
    }

    /* Card buttons */
    .policy-btn-split {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }

    .policy-btn {
        flex: 1;
        height: 38px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .policy-btn-view {
        background: rgba(75, 0, 232, 0.05);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, 0.12);
    }

    .policy-btn-view:hover {
        background: var(--orb-primary);
        color: #fff;
        border-color: var(--orb-primary);
    }

    .policy-btn-download {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        border: none;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15);
    }

    .policy-btn-download:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25);
        color: #fff;
    }

    /* Search input inside premium header */
    .header-search-wrap {
        position: relative;
        width: 100%;
        max-width: 320px;
    }

    .header-search-input {
        width: 100%;
        height: 40px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 0.12);
        padding-left: 36px;
        padding-right: 12px;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        backdrop-filter: blur(8px);
        transition: all 0.2s ease;
    }

    .header-search-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .header-search-input:focus {
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.18);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    .header-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
    }

    @media (max-width: 767px) {
        .header-search-wrap {
            max-width: 100%;
            margin-top: 14px;
        }
    }
</style>
@endsection

@section('_content')
@php
    $isAdmin = auth()->user() && auth()->user()->isAdmin();
@endphp

<div class="set-page">
    <div class="policies-container">
        
        <!-- Premium Purple Gradient Hero Header -->
        <div class="set-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="mb-3 mb-md-0" style="flex: 1; min-width: 0; margin-right: 20px;">
                <div class="set-kicker">
                    <i class="fas fa-folder-open"></i> COMPANY &bull; DOCUMENTS &amp; POLICIES
                </div>
                <h1 class="set-title">Company Documents &amp; Policies</h1>
                <p class="set-subtitle">Publish, upload, and manage official organizational documents, templates, forms, and HR policies.</p>
            </div>
            
            <div class="d-flex flex-column flex-md-row align-items-md-center mt-3 mt-md-0" style="flex: 0 0 auto; gap: 12px; max-width: 100%;">
                <!-- Compact Search Box -->
                <div class="header-search-wrap mb-2 mb-md-0" style="flex: 0 0 240px; width: 240px !important; max-width: 240px !important;">
                    <i class="fas fa-search header-search-icon"></i>
                    <input type="text" id="policySearchInput" class="header-search-input" placeholder="Search documents & policies...">
                </div>
                
                @if($isAdmin)
                    <button class="set-btn d-inline-flex align-items-center justify-content-center" data-toggle="modal" data-target="#addPolicyModal" style="height: 40px; border-radius: 12px; white-space: nowrap; padding: 0 16px; flex: 0 0 auto; width: auto;">
                        <i class="fas fa-plus-circle mr-2"></i> Upload Document / Policy
                    </button>
                @endif
            </div>
        </div>

        @include('components.alerts')

        <!-- Policies Responsive Grid -->
        <div class="row mt-4" id="policiesGrid">
            @forelse($policies as $policy)
                @include('hrms.documents.company-documents._card', ['policy' => $policy])
            @empty
                <div class="col-12" id="originalEmptyState">
                    @include('hrms.documents.partials.empty-state', [
                        'title' => 'Repository is Empty',
                        'description' => 'There are currently no active documents or policies uploaded. Use the button above to upload.',
                        'background' => 'rgba(75, 0, 232, 0.05)',
                        'color' => 'var(--set-primary)',
                        'icon' => 'fas fa-folder-open'
                    ])
                </div>
            @endforelse

            <!-- Dynamic Search Empty State (initially hidden) -->
            <div class="col-12 d-none" id="searchEmptyState">
                @include('hrms.documents.partials.empty-state', [
                    'title' => 'No Matching Documents or Policies',
                    'description' => "We couldn't find any documents or policies matching your search term. Try checking for spelling mistakes or simplified terms.",
                    'background' => 'rgba(239, 68, 68, 0.05)',
                    'color' => '#EF4444',
                    'icon' => 'fas fa-search-minus'
                ])
            </div>
        </div>
    </div>
</div>

@if($isAdmin)
<!-- Upload Document / Policy Modal -->
<div class="modal fade" id="addPolicyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border: none; border-radius: 24px; overflow: hidden; box-shadow: var(--set-shadow);">
            <form action="{{ route('documents.policies.store') }}" method="POST" enctype="multipart/form-data" style="width: 100%;">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)); border: none; padding: 24px; color: white;">
                    <div>
                        <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-file-upload mr-2"></i>Upload Document / Policy</h5>
                        <p class="mb-0 opacity-75" style="font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.9);">Publish a new policy file, template, or form for employees and admins.</p>
                    </div>
                    <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px; background: #fff;">
                    <div class="form-group mb-3">
                        <label class="set-label" for="policy_title">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="policy_title" class="form-control set-control" placeholder="e.g., Annual Leave Policy 2026" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="set-label" for="policy_category">Category <span class="text-danger">*</span></label>
                        <select name="category" id="policy_category" class="form-control set-control" required style="height: 42px;">
                            <option value="HR Policy">HR Policy</option>
                            <option value="IT Policy">IT Policy</option>
                            <option value="Finance">Finance</option>
                            <option value="Conduct">Code of Conduct</option>
                            <option value="Templates">Templates & Forms</option>
                            <option value="General">General Document</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="set-label" for="policy_file">Attachment File (PDF/Image) <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="policy_file" class="form-control set-control p-1" required accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-group mb-3">
                        <label class="set-label">Visible To (Target Audience)</label>
                        <div class="d-flex align-items-center gap-4 mt-2">
                            <div class="form-check form-check-inline mr-4">
                                <input class="form-check-input" type="checkbox" name="visible_to[]" value="employee" id="vis_emp" checked style="transform: scale(1.15);">
                                <label class="form-check-label font-weight-bold ml-2" for="vis_emp" style="font-size: 13px; color: var(--set-text); cursor: pointer;">Employees</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="visible_to[]" value="admin" id="vis_adm" checked style="transform: scale(1.15);">
                                <label class="form-check-label font-weight-bold ml-2" for="vis_adm" style="font-size: 13px; color: var(--set-text); cursor: pointer;">Admins / HR</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border: none; padding: 16px 24px; background: #F9FAFB;">
                    <button type="button" class="set-btn set-btn-soft" style="box-shadow: none;" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="set-btn"><i class="fas fa-bullhorn mr-1"></i> Publish Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('_script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('policySearchInput');
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                var query = e.target.value.toLowerCase().trim();
                var items = document.querySelectorAll('.policy-card-item');
                var visibleCount = 0;
                
                items.forEach(function(item) {
                    var title = item.getAttribute('data-title') || '';
                    var category = item.getAttribute('data-category') || '';
                    
                    if (title.indexOf(query) !== -1 || category.indexOf(query) !== -1) {
                        item.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        item.classList.add('d-none');
                    }
                });
                
                var originalEmpty = document.getElementById('originalEmptyState');
                var searchEmpty = document.getElementById('searchEmptyState');
                
                if (visibleCount === 0) {
                    if (searchEmpty) searchEmpty.classList.remove('d-none');
                    if (originalEmpty) originalEmpty.classList.add('d-none');
                } else {
                    if (searchEmpty) searchEmpty.classList.add('d-none');
                    if (originalEmpty && document.querySelectorAll('.policy-card-item').length > 0) {
                        originalEmpty.classList.add('d-none');
                    } else if (originalEmpty) {
                        originalEmpty.classList.remove('d-none');
                    }
                }
            });
        }
    });
</script>
@endsection
