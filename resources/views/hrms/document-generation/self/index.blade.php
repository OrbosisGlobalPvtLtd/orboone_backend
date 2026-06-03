@extends('layouts.panel', ['active' => 'my_documents'])

@section('page_title', 'My Official Documents')

@section('_head')
@include('settings.partials.styles')

<style>
    .doc-page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    /* Tab Pills Navigation */
    .tab-pill-container {
        display: flex;
        gap: 6px;
        background: #F1F5F9;
        padding: 6px;
        border-radius: 14px;
        width: fit-content;
        border: 1px solid var(--set-border);
    }

    .tab-pill {
        border: none;
        background: transparent;
        color: var(--set-muted);
        font-size: 13px;
        font-weight: 800;
        padding: 8px 18px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .tab-pill.active {
        background: #ffffff;
        color: var(--set-primary);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
    }

    /* Premium Search Bar */
    .search-input-wrapper {
        position: relative;
        width: 100%;
    }

    .search-control {
        width: 100%;
        height: 44px;
        border: 1px solid var(--set-border) !important;
        border-radius: 14px !important;
        padding: 0 44px 0 42px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--set-text) !important;
        background: #F9FAFB !important;
        outline: none;
        transition: all 0.25s ease;
    }

    .search-control:focus {
        border-color: var(--set-primary) !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, 0.08) !important;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 14px;
        color: var(--set-muted);
        font-size: 15px;
        pointer-events: none;
    }

    .search-clear-btn {
        position: absolute;
        right: 15px;
        top: 12px;
        color: var(--set-muted);
        font-size: 18px;
        cursor: pointer;
        transition: color 0.15s ease;
    }

    .search-clear-btn:hover {
        color: var(--set-text);
    }

    /* Grid Items styling */
    .doc-grid-item {
        background: #ffffff;
        border: 1px solid var(--set-border);
        border-radius: 22px;
        padding: 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(16, 24, 40, 0.02);
    }

    .doc-grid-item:hover {
        transform: translateY(-6px);
        box-shadow: var(--set-shadow);
        border-color: var(--set-primary);
    }

    .doc-visual-box {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #ffffff;
        flex-shrink: 0;
    }

    .bg-gradient-indigo { background: linear-gradient(135deg, var(--set-primary) 0%, var(--set-secondary) 100%); }
    .bg-gradient-emerald { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
    .bg-gradient-purple { background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%); }
    .bg-gradient-orange { background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%); }

    .doc-title {
        font-size: 15px;
        font-weight: 850;
        color: var(--set-text);
        line-height: 1.4;
        margin-bottom: 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 42px;
    }

    .doc-meta-badge {
        font-size: 10px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .badge-gray { background: #F1F5F9; color: var(--set-muted); }
    .badge-soft-indigo-fill { background: var(--set-soft); color: var(--set-primary); }

    .doc-metadata-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
        padding-top: 12px;
        border-top: 1px dashed var(--set-border);
    }

    .doc-meta-item {
        font-size: 11.5px;
        color: var(--set-muted);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .doc-meta-item i {
        font-size: 13px;
        color: var(--set-muted);
    }

    .btn-premium-download {
        background: #F8FAFC;
        border: 1px solid var(--set-border);
        color: var(--set-primary);
        font-weight: 800;
        font-size: 13px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 1;
        transition: all 0.2s ease;
    }

    .btn-premium-download:hover {
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary));
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2);
    }
</style>
@endsection

@section('_content')
@php
    $employeeId = Auth::user()->employee->id ?? 0;
    
    $totalOfficial = \App\Models\HRMS\DocumentGeneration\GeneratedDocument::where('employee_id', $employeeId)
        ->whereIn('status', ['generated', 'sent', 'reviewed'])
        ->count();

    $lettersCount = \App\Models\HRMS\DocumentGeneration\GeneratedDocument::where('employee_id', $employeeId)
        ->whereIn('status', ['generated', 'sent', 'reviewed'])
        ->whereIn('document_type', ['offer_letter', 'appointment_letter', 'internship_certificate'])
        ->count();

    $serviceCount = \App\Models\HRMS\DocumentGeneration\GeneratedDocument::where('employee_id', $employeeId)
        ->whereIn('status', ['generated', 'sent', 'reviewed'])
        ->whereIn('document_type', ['experience_letter', 'relieving_letter'])
        ->count();

    $otherCount = $totalOfficial - ($lettersCount + $serviceCount);
@endphp

<div class="doc-page-container">
    <!-- Premium Branded Header Banner -->
    <div class="set-header">
        <div>
            <div class="set-kicker"><i class="fas fa-fingerprint"></i> Official Records</div>
            <h2 class="set-title">Your Verified Document Vault</h2>
            <p class="set-subtitle">Access, review, and download official company correspondence, offer letters, appointment agreements, and experience credentials issued directly by {{ branding_name() }}.</p>
        </div>
        <div class="set-glass-badge">
            <small class="d-block font-weight-bold" style="font-size: 9px; text-transform: uppercase; color: rgba(255, 255, 255, 0.75); letter-spacing: 0.05em;">Total Documents</small>
            <span class="font-weight-black fs-4" style="line-height:1.1;">{{ $totalOfficial }}</span>
        </div>
    </div>

    <!-- Live Branded Metrics Widgets -->
    <div class="row">
        <div class="col-md-4">
            <div class="set-card">
                <div class="set-card-body d-flex align-items-center" style="gap: 16px; padding: 20px;">
                    <div class="set-icon-box" style="background: var(--set-soft); color: var(--set-primary); width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-signature fa-lg"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 900; color: var(--set-text); line-height: 1.2;">{{ $lettersCount }}</div>
                        <small style="font-size: 11px; font-weight: 850; text-transform: uppercase; color: var(--set-muted); letter-spacing: 0.05em;">Contracts & Offers</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="set-card">
                <div class="set-card-body d-flex align-items-center" style="gap: 16px; padding: 20px;">
                    <div class="set-icon-box" style="background: rgba(16, 185, 129, 0.08); color: #10B981; width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-award fa-lg"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 900; color: var(--set-text); line-height: 1.2;">{{ $serviceCount }}</div>
                        <small style="font-size: 11px; font-weight: 850; text-transform: uppercase; color: var(--set-muted); letter-spacing: 0.05em;">Service & Experience</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="set-card">
                <div class="set-card-body d-flex align-items-center" style="gap: 16px; padding: 20px;">
                    <div class="set-icon-box" style="background: rgba(249, 115, 22, 0.08); color: #EA580C; width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-invoice fa-lg"></i>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: 900; color: var(--set-text); line-height: 1.2;">{{ max(0, $otherCount) }}</div>
                        <small style="font-size: 11px; font-weight: 850; text-transform: uppercase; color: var(--set-muted); letter-spacing: 0.05em;">Other Certificates</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Filter & Dynamic Search Bar -->
    <div class="row align-items-center mb-4">
        <div class="col-lg-6 col-md-7">
            <div class="tab-pill-container">
                <button class="tab-pill active" data-filter="all">
                    <i class="fas fa-th-large me-2"></i> All Docs
                </button>
                <button class="tab-pill" data-filter="contract">
                    <i class="fas fa-file-signature me-2"></i> Contracts
                </button>
                <button class="tab-pill" data-filter="service">
                    <i class="fas fa-history me-2"></i> Service
                </button>
                <button class="tab-pill" data-filter="other">
                    <i class="fas fa-ellipsis-h me-2"></i> Others
                </button>
            </div>
        </div>
        <div class="col-lg-6 col-md-5 mt-3 mt-md-0">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="docSearch" class="search-control" placeholder="Search by title, category, document number...">
                <span class="search-clear-btn" id="clearSearch" style="display: none;"><i class="fas fa-times-circle"></i></span>
            </div>
        </div>
    </div>

    <!-- Official Documents Grid -->
    <div class="row" id="documentsGrid">
        @forelse($documents as $doc)
            @php
                // Standardize category tagging for JS filters
                $category = 'other';
                $iconClass = 'fas fa-file-alt';
                $gradientClass = 'bg-gradient-orange';
                $typeName = 'Letter';

                if (in_array($doc->document_type, ['offer_letter', 'appointment_letter'])) {
                    $category = 'contract';
                    $iconClass = 'fas fa-file-signature';
                    $gradientClass = 'bg-gradient-indigo';
                    $typeName = str_replace('_', ' ', ucwords($doc->document_type));
                } elseif (in_array($doc->document_type, ['experience_letter', 'relieving_letter', 'internship_certificate'])) {
                    $category = 'service';
                    $iconClass = 'fas fa-award';
                    $gradientClass = 'bg-gradient-emerald';
                    $typeName = str_replace('_', ' ', ucwords($doc->document_type));
                } elseif ($doc->document_type === 'salary_certificate') {
                    $category = 'other';
                    $iconClass = 'fas fa-certificate';
                    $gradientClass = 'bg-gradient-purple';
                    $typeName = 'Salary Certificate';
                }
            @endphp
            <div class="col-lg-4 col-md-6 mb-4 doc-item-card" data-category="{{ $category }}" data-title="{{ strtolower($doc->document_title) }}" data-number="{{ strtolower($doc->document_number) }}" data-type="{{ strtolower($typeName) }}">
                <div class="doc-grid-item">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="doc-visual-box {{ $gradientClass }}">
                            <i class="{{ $iconClass }}"></i>
                        </div>
                        <div class="text-end">
                            <span class="doc-meta-badge badge-soft-indigo-fill">Verified</span>
                            <div class="font-monospace mt-1" style="font-size: 11px; font-weight: 800; color: var(--set-muted);">{{ $doc->document_number }}</div>
                        </div>
                    </div>

                    <div>
                        <span class="badge badge-gray text-muted mb-2 font-weight-bold" style="font-size: 9px; text-transform: uppercase;">{{ $typeName }}</span>
                        <h5 class="doc-title" title="{{ $doc->document_title }}">{{ $doc->document_title }}</h5>
                    </div>

                    <div class="doc-metadata-row">
                        <div class="doc-meta-item">
                            <i class="far fa-calendar-alt"></i>
                            <span>Issued: <strong>{{ $doc->created_at->format('d M, Y') }}</strong></span>
                        </div>
                        <div class="doc-meta-item">
                            <i class="far fa-file-pdf text-danger"></i>
                            <span>Secure PDF</span>
                        </div>
                    </div>

                    <div class="doc-action-group">
                        <a href="{{ route('hrms.document-generation.self.download', $doc->id) }}" class="btn-premium-download">
                            <i class="fas fa-cloud-download-alt"></i> Download Official Copy
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 my-4 bg-white border rounded-20" style="box-shadow: 0 4px 15px rgba(16,24,40,.02);">
                <div class="mx-auto mb-3 text-muted d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 70px; height: 70px; font-size: 32px; opacity: 0.65;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h4 class="font-weight-black text-dark mb-2">Your Document Vault is Empty</h4>
                <p class="text-muted max-w-md mx-auto mb-0" style="font-size: 13.5px;">Official corporate documents, offer contracts, or work credentials generated for you by {{ branding_name() }} will be displayed here securely.</p>
            </div>
        @endforelse
    </div>

    <!-- Laravel Pagination Controls -->
    @if($documents->hasPages())
        <div class="mt-4 pt-3 border-top d-flex justify-content-center">
            {{ $documents->links() }}
        </div>
    @endif
</div>
@endsection

@section('_script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('docSearch');
    const clearBtn = document.getElementById('clearSearch');
    const tabs = document.querySelectorAll('.tab-pill');
    const cards = document.querySelectorAll('.doc-item-card');
    let activeFilter = 'all';
    let searchQuery = '';

    // Search and filter logic combined for perfect synchronization
    function filterCards() {
        let visibleCount = 0;
        
        cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            const cardTitle = card.getAttribute('data-title');
            const cardNumber = card.getAttribute('data-number');
            const cardType = card.getAttribute('data-type');
            
            const matchesFilter = (activeFilter === 'all' || cardCategory === activeFilter);
            const matchesSearch = (!searchQuery || 
                                   cardTitle.includes(searchQuery) || 
                                   cardNumber.includes(searchQuery) ||
                                   cardType.includes(searchQuery));
            
            if (matchesFilter && matchesSearch) {
                card.style.display = 'block';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.25s ease';
                    card.style.opacity = '1';
                }, 10);
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle empty search warning if count is 0
        let emptyState = document.getElementById('noResultsWarning');
        if (visibleCount === 0 && cards.length > 0) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.id = 'noResultsWarning';
                emptyState.className = 'col-12 text-center py-5 text-muted';
                emptyState.innerHTML = `
                    <div class="fs-1 mb-2"><i class="fas fa-search"></i></div>
                    <h5>No Matching Documents Found</h5>
                    <p class="small">Try refining your search terms or selecting "All Docs".</p>
                `;
                document.getElementById('documentsGrid').appendChild(emptyState);
            }
        } else if (emptyState) {
            emptyState.remove();
        }
    }

    // Input listener for live search
    searchInput.addEventListener('input', function(e) {
        searchQuery = e.target.value.toLowerCase().trim();
        clearBtn.style.display = searchQuery ? 'block' : 'none';
        filterCards();
    });

    // Clear search trigger
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchQuery = '';
        clearBtn.style.display = 'none';
        searchInput.focus();
        filterCards();
    });

    // Tab switcher filters
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.getAttribute('data-filter');
            filterCards();
        });
    });
});
</script>
@endsection
