@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'employee-policies'])

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
        color: #4B00E8;
        border: 1px solid rgba(75, 0, 232, 0.12);
    }

    .policy-btn-view:hover {
        background: #4B00E8;
        color: #fff;
        border-color: #4B00E8;
    }

    .policy-btn-download {
        background: linear-gradient(135deg, #4B00E8 0%, #8600EE 100%);
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
<div class="set-page">
    <div class="policies-container">
        
        <!-- Premium Purple Gradient Hero Header -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-folder-open"></i> EMPLOYEE &bull; DOCUMENTS &amp; POLICIES
                </div>
                <h1 class="set-title">Company Documents &amp; Policies</h1>
                <p class="set-subtitle">Access organizational handbooks, forms, guidelines, templates, and HR policies.</p>
            </div>
            
            <!-- Compact Right-Aligned Search Box -->
            <div class="header-search-wrap">
                <i class="fas fa-search header-search-icon"></i>
                <input type="text" id="policySearchInput" class="header-search-input" placeholder="Search documents & policies...">
            </div>
        </div>

        @include('hrms.leave.shared.flash')

        <!-- Policies Responsive Grid -->
        <div class="row" id="policiesGrid">
            @forelse($policies as $policy)
                @php
                    $pdfUrl = 'https://orbosis.in/public/uploads/Employee%20Orientation%20&%20Company%20Policy%20Document%20(1).pdf';
                @endphp
                
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4 policy-card-item" data-title="{{ strtolower($policy->title) }}" data-category="{{ strtolower($policy->category ?? '') }}">
                    <div class="set-card h-100" style="padding: 22px; display: flex; flex-direction: column;">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="policy-icon-box shadow-sm">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <span class="policy-rev-pill">
                                Rev: {{ $policy->updated_at->format('M Y') }}
                            </span>
                        </div>

                        <!-- HSL Category Pill Badge -->
                        <div>
                            <span class="set-badge set-badge-paid" style="font-size: 9px; font-weight: 850; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 6px;">
                                {{ $policy->category ?? 'Company General' }}
                            </span>
                        </div>

                        <h5 class="policy-card-title" title="{{ $policy->title }}">
                            {{ $policy->title }}
                        </h5>
                        
                        <!-- Split Actions -->
                        <div class="policy-btn-split">
                            <a href="{{ $pdfUrl }}" target="_blank" class="policy-btn policy-btn-view">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            <a href="{{ $pdfUrl }}" download class="policy-btn policy-btn-download">
                                <i class="fas fa-download"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12" id="originalEmptyState">
                    <div class="set-card py-5 text-center">
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="background: rgba(75, 0, 232, 0.05); border-radius: 50%; width: 80px; height: 80px; color: var(--set-primary); font-size: 28px;">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h4 class="font-weight-black" style="color: var(--set-text); font-size: 18px;">Repository is Empty</h4>
                        <p class="text-muted mt-2" style="max-width: 360px; margin: 0 auto; font-size: 13px; line-height: 1.5;">
                            There are currently no active documents or policies uploaded. Please contact HR for assistance.
                        </p>
                    </div>
                </div>
            @endforelse

            <!-- Dynamic Search Empty State (initially hidden) -->
            <div class="col-12 d-none" id="searchEmptyState">
                <div class="set-card py-5 text-center">
                    <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="background: rgba(239, 68, 68, 0.05); border-radius: 50%; width: 80px; height: 80px; color: #EF4444; font-size: 28px;">
                        <i class="fas fa-search-minus"></i>
                    </div>
                    <h4 class="font-weight-black" style="color: var(--set-text); font-size: 18px;">No Matching Documents or Policies</h4>
                    <p class="text-muted mt-2" style="max-width: 360px; margin: 0 auto; font-size: 13px; line-height: 1.5;">
                        We couldn't find any documents or policies matching your search term. Try checking for spelling mistakes or simplified terms.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
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
                }
            });
        }
    });
</script>
@endsection
