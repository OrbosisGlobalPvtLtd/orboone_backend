@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'document-management'])

@section('page_title', 'Company Documents & Policies')

@section('_head')
@include('settings.partials.styles')
<style>
    .policies-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        background: #F6F7FB;
        font-family: 'Outfit', 'Inter', system-ui, -apple-system, sans-serif;
    }

    .policies-container {
        max-width: 1440px;
        margin: 0 auto;
        width: 100%;
    }

    /* Original Theme Primary-to-Secondary Gradient Hero */
    .policies-hero {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        border-radius: 20px;
        padding: 26px 30px;
        margin-bottom: 22px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 14px 36px rgba(75, 0, 232, 0.16);
        position: relative;
        overflow: hidden;
    }

    .policies-hero::before {
        content: "";
        position: absolute;
        right: -40px;
        top: -60px;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
        pointer-events: none;
    }

    .policies-hero-info {
        flex: 1 1 320px;
        min-width: 0;
        position: relative;
        z-index: 2;
    }

    .policies-kicker {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .policies-title {
        font-size: clamp(20px, 2.6vw, 26px);
        font-weight: 900;
        margin: 0 0 4px 0;
        color: #fff;
        line-height: 1.2;
    }

    .policies-subtitle {
        font-size: clamp(12.5px, 1.1vw, 13.5px);
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        line-height: 1.45;
        max-width: 620px;
    }

    /* Actions Bar */
    .policies-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .header-search-wrap {
        position: relative;
        width: 250px;
        height: 40px;
    }

    .header-search-input {
        width: 100%;
        height: 40px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.16);
        padding-left: 36px;
        padding-right: 14px;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .header-search-input::placeholder {
        color: rgba(255, 255, 255, 0.8);
    }

    .header-search-input:focus {
        border-color: rgba(255, 255, 255, 0.7);
        background: rgba(255, 255, 255, 0.24);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.18);
    }

    .header-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.85);
        font-size: 13px;
        pointer-events: none;
    }

    .btn-hero-action {
        height: 40px;
        border-radius: 12px;
        padding: 0 18px;
        font-size: 13px;
        font-weight: 800;
        background: #ffffff;
        color: var(--orb-primary, #4B00E8);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        text-decoration: none !important;
    }

    .btn-hero-action:hover {
        background: #F8FAFC;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        color: var(--orb-primary, #4B00E8);
    }

    /* Category Filter Pills */
    .policy-filter-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 18px;
        scrollbar-width: thin;
    }

    .policy-filter-pill {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 750;
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .policy-filter-pill:hover {
        border-color: #CBD5E1;
        color: #1E293B;
        background: #F8FAFC;
    }

    .policy-filter-pill.active {
        background: var(--orb-primary, #4B00E8);
        border-color: var(--orb-primary, #4B00E8);
        color: #fff;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
    }

    /* Policy Card Grid */
    .policies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 18px;
    }

    /* Policy Card */
    .policy-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: 0 4px 16px rgba(16, 24, 40, 0.04);
        transition: all 0.22s ease;
        position: relative;
    }

    .policy-card:hover {
        transform: translateY(-3px);
        border-color: rgba(75, 0, 232, 0.25);
        box-shadow: 0 12px 28px rgba(75, 0, 232, 0.09);
    }

    .policy-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(239, 68, 68, 0.08);
        color: #EF4444;
        font-size: 16px;
        flex-shrink: 0;
    }

    .policy-rev-pill {
        font-size: 9.5px;
        font-weight: 800;
        color: #64748B;
        background: #F1F5F9;
        padding: 4px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .policy-card-title {
        font-size: 14.5px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.4;
        margin-top: 12px;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 40px;
    }

    .policy-btn-split {
        display: flex;
        gap: 8px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #F1F5F9;
    }

    .policy-btn {
        flex: 1;
        height: 38px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 750;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.18s ease;
        text-decoration: none !important;
        white-space: nowrap;
        border: none;
    }

    .policy-btn-view {
        background: #F8FAFC;
        color: #334155;
        border: 1px solid #E2E8F0;
    }

    .policy-btn-view:hover {
        background: #F1F5F9;
        color: #0F172A;
        border-color: #CBD5E1;
    }

    .policy-btn-download {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252));
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.15);
    }

    .policy-btn-download:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.25);
        color: #fff !important;
    }

    .policy-btn-disabled {
        background: #F8FAFC;
        color: #94A3B8;
        border: 1px solid #E2E8F0;
        cursor: not-allowed;
        opacity: 0.75;
    }

    /* Responsive Queries */
    @media (max-width: 768px) {
        .policies-page {
            padding: 14px 10px 30px;
        }

        .policies-hero {
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            padding: 18px 16px;
            gap: 12px;
        }

        .policies-hero-info {
            flex: none;
            width: 100%;
        }

        .policies-actions {
            flex-direction: column;
            width: 100%;
            gap: 10px;
        }

        .header-search-wrap {
            width: 100%;
            max-width: 100%;
        }

        .btn-hero-action {
            width: 100%;
        }

        .policies-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
    }
</style>
@endsection

@section('_content')
@php
    $isAdmin = auth()->user() && auth()->user()->isAdmin();
@endphp

<div class="policies-page">
    <div class="policies-container">
        
        <!-- Premium Gradient Hero Header (Theme Primary to Secondary) -->
        <div class="policies-hero">
            <div class="policies-hero-info">
                <div class="policies-kicker">
                    <i class="fas fa-folder-open"></i> COMPANY &bull; DOCUMENTS &amp; POLICIES
                </div>
                <h1 class="policies-title">Company Documents &amp; Policies</h1>
                <p class="policies-subtitle">Publish, upload, and manage official organizational documents, templates, forms, and HR policies.</p>
            </div>
            
            <div class="policies-actions">
                <!-- Search Box -->
                <div class="header-search-wrap">
                    <i class="fas fa-search header-search-icon"></i>
                    <input type="text" id="policySearchInput" class="header-search-input" placeholder="Search documents & policies...">
                </div>
                
                @if($isAdmin)
                    <button class="btn-hero-action" data-toggle="modal" data-target="#addPolicyModal">
                        <i class="fas fa-plus-circle"></i> Upload Document / Policy
                    </button>
                @endif
            </div>
        </div>

        @include('components.alerts')

        <!-- Category Filter Pills Bar -->
        <div class="policy-filter-bar" id="policyCategoryFilterBar">
            <button class="policy-filter-pill active" data-category="all">
                <i class="fas fa-layer-group"></i> All Documents
            </button>
            <button class="policy-filter-pill" data-category="hr policy">
                <i class="fas fa-user-shield"></i> HR Policy
            </button>
            <button class="policy-filter-pill" data-category="it policy">
                <i class="fas fa-laptop-code"></i> IT Policy
            </button>
            <button class="policy-filter-pill" data-category="finance">
                <i class="fas fa-coins"></i> Finance
            </button>
            <button class="policy-filter-pill" data-category="conduct">
                <i class="fas fa-gavel"></i> Code of Conduct
            </button>
            <button class="policy-filter-pill" data-category="templates">
                <i class="fas fa-file-invoice"></i> Templates &amp; Forms
            </button>
            <button class="policy-filter-pill" data-category="general">
                <i class="fas fa-folder"></i> General
            </button>
        </div>

        <!-- Policies Responsive Grid -->
        <div class="policies-grid" id="policiesGrid">
            @forelse($policies as $policy)
                @include('hrms.documents.company-documents._card', ['policy' => $policy])
            @empty
                <div style="grid-column: 1 / -1;" id="originalEmptyState">
                    @include('hrms.documents.partials.empty-state', [
                        'title' => 'Repository is Empty',
                        'description' => 'There are currently no active documents or policies uploaded. Use the button above to upload.',
                        'background' => 'rgba(75, 0, 232, 0.05)',
                        'color' => 'var(--orb-primary, #4B00E8)',
                        'icon' => 'fas fa-folder-open'
                    ])
                </div>
            @endforelse

            <!-- Dynamic Search Empty State (initially hidden) -->
            <div style="grid-column: 1 / -1;" class="d-none" id="searchEmptyState">
                @include('hrms.documents.partials.empty-state', [
                    'title' => 'No Matching Documents or Policies',
                    'description' => "We couldn't find any documents or policies matching your filters. Try adjusting the category or search terms.",
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
        <div class="modal-content" style="border: none; border-radius: 22px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <form action="{{ route('documents.policies.store') }}" method="POST" enctype="multipart/form-data" style="width: 100%;">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)); border: none; padding: 22px 24px; color: white;">
                    <div>
                        <h5 class="modal-title font-weight-bold text-white mb-1"><i class="fas fa-file-upload mr-2"></i>Upload Document / Policy</h5>
                        <p class="mb-0" style="font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.85);">Publish a new policy file, template, or form for employees and admins.</p>
                    </div>
                    <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:22px; padding:0; outline:none; line-height:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px; background: #fff;">
                    <div class="form-group mb-3">
                        <label class="set-label font-weight-bold" for="policy_title" style="font-size: 13px; color: #1E293B;">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="policy_title" class="form-control" placeholder="e.g., Annual Leave Policy 2026" required style="height: 42px; border-radius: 10px; border: 1px solid #E2E8F0; font-size: 13.5px;">
                    </div>
                    <div class="form-group mb-3">
                        <label class="set-label font-weight-bold" for="policy_category" style="font-size: 13px; color: #1E293B;">Category <span class="text-danger">*</span></label>
                        <select name="category" id="policy_category" class="form-control" required style="height: 42px; border-radius: 10px; border: 1px solid #E2E8F0; font-size: 13.5px;">
                            <option value="HR Policy">HR Policy</option>
                            <option value="IT Policy">IT Policy</option>
                            <option value="Finance">Finance</option>
                            <option value="Conduct">Code of Conduct</option>
                            <option value="Templates">Templates & Forms</option>
                            <option value="General">General Document</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="set-label font-weight-bold" for="policy_file" style="font-size: 13px; color: #1E293B;">Attachment File (PDF/Image) <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="policy_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png" style="height: 42px; border-radius: 10px; border: 1px solid #E2E8F0; padding: 6px 10px; font-size: 13px;">
                    </div>
                    <div class="form-group mb-2">
                        <label class="set-label font-weight-bold" style="font-size: 13px; color: #1E293B;">Visible To (Target Audience)</label>
                        <div class="d-flex align-items-center gap-4 mt-2">
                            <div class="form-check form-check-inline mr-4">
                                <input class="form-check-input" type="checkbox" name="visible_to[]" value="employee" id="vis_emp" checked style="transform: scale(1.15);">
                                <label class="form-check-label font-weight-bold ml-2" for="vis_emp" style="font-size: 13px; color: #334155; cursor: pointer;">Employees</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="visible_to[]" value="admin" id="vis_adm" checked style="transform: scale(1.15);">
                                <label class="form-check-label font-weight-bold ml-2" for="vis_adm" style="font-size: 13px; color: #334155; cursor: pointer;">Admins / HR</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border: none; padding: 16px 24px; background: #F8FAFC; border-top: 1px solid #F1F5F9;">
                    <button type="button" class="btn" style="background: #E2E8F0; color: #475569; border-radius: 10px; font-weight: 750; font-size: 13px; padding: 8px 18px;" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, var(--orb-primary, #4B00E8), var(--orb-secondary, #FF5252)); color: #fff; border-radius: 10px; font-weight: 750; font-size: 13px; padding: 8px 20px; box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);"><i class="fas fa-bullhorn mr-1"></i> Publish Now</button>
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
        var filterPills = document.querySelectorAll('.policy-filter-pill');
        var activeCategory = 'all';

        function filterPolicies() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            var items = document.querySelectorAll('.policy-card-item');
            var visibleCount = 0;

            items.forEach(function(item) {
                var title = item.getAttribute('data-title') || '';
                var category = item.getAttribute('data-category') || '';

                var matchesCategory = (activeCategory === 'all') || (category.indexOf(activeCategory) !== -1);
                var matchesSearch = (query === '') || (title.indexOf(query) !== -1) || (category.indexOf(query) !== -1);

                if (matchesCategory && matchesSearch) {
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
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterPolicies);
        }

        filterPills.forEach(function(pill) {
            pill.addEventListener('click', function() {
                filterPills.forEach(function(p) { p.classList.remove('active'); });
                this.classList.add('active');
                activeCategory = this.getAttribute('data-category') || 'all';
                filterPolicies();
            });
        });
    });
</script>
@endsection
