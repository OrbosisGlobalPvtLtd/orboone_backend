@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'employee-policies'])

@section('_content')
<style>
    :root {
        --orbosis-gradient: linear-gradient(135deg, #4b00e8 0%, #ffb101 100%);
        --soft-bg: #f8faff;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        --border-radius: 20px;
    }

    .premium-header {
        background: var(--orbosis-gradient);
        border-radius: 0 0 var(--border-radius) var(--border-radius);
        padding: 40px 40px 80px;
        color: white;
        margin-bottom: -50px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(75, 0, 232, 0.2);
    }

    .premium-header::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .search-container {
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .search-input {
        background: transparent !important;
        border: none !important;
        color: white !important;
        padding: 15px !important;
        font-weight: 600;
    }

    .search-input::placeholder { color: rgba(255, 255, 255, 0.7); }
    .search-input:focus { outline: none; box-shadow: none; }

    .policy-card {
        background: white;
        border-radius: 20px;
        border: none;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 2px solid transparent;
    }

    .policy-card:hover {
        transform: translateY(-8px);
        border-color: #ffb101;
        box-shadow: 0 15px 35px rgba(255, 177, 1, 0.15);
    }

    .pdf-icon-bg {
        background: rgba(231, 74, 59, 0.1);
        color: #e74a3b;
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 20px;
        transition: transform 0.3s;
    }

    .policy-card:hover .pdf-icon-bg { transform: scale(1.1) rotate(-5deg); }

    .category-tag {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 6px 15px;
        border-radius: 20px;
        background: rgba(75, 0, 232, 0.1);
        color: #4b00e8;
        margin-bottom: 15px;
        display: inline-block;
    }

    .btn-view {
        background: #f8faff;
        color: #4b00e8;
        border: 2px solid #ebf0f6;
        border-radius: 12px;
        padding: 12px;
        font-weight: 700;
        transition: 0.3s;
    }

    .btn-view:hover {
        background: #4b00e8;
        color: white;
        border-color: #4b00e8;
    }

    .btn-download {
        background: #ffb101;
        color: #1a1a1a !important;
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: 800;
        transition: 0.3s;
    }

    .btn-download:hover { filter: brightness(1.1); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 177, 1, 0.4); }

    @media (max-width: 768px) {
        .premium-header { padding: 30px 20px 70px; }
    }
</style>

<div class="container-fluid p-0">
    <!-- Premium Header -->
    <div class="premium-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-12 text-center text-lg-left mb-4 mb-lg-0">
                    <h1 class="font-weight-bold mb-2" style="font-size: 2.5rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">Policy & Guidelines Hub</h1>
                    <p class="mb-0" style="font-weight: 500; font-size: 1.1rem; opacity: 0.9;">
                        Access the latest organization guidelines, HR policies, and compliance documents.
                    </p>
                </div>
                <div class="col-lg-5 col-md-12">
                    <div class="input-group overflow-hidden search-container">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0 text-white"><i class="fas fa-search" style="font-size: 1.2rem;"></i></span>
                        </div>
                        <input type="text" class="form-control search-input" placeholder="Search operational policies...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="container-fluid px-2 px-md-4 pb-5 pt-4" style="position: relative; z-index: 10;">
        <div class="row">
                        @forelse($policies as $policy)
                @php
                    $pdfUrl = 'https://orbosis.in/public/uploads/Employee%20Orientation%20&%20Company%20Policy%20Document%20(1).pdf';
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="policy-card p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="pdf-icon-bg shadow-sm">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <span class="badge badge-pill badge-light shadow-sm py-2 px-3" style="color: #858796; font-weight: 800; font-size: 0.65rem;">
                                REV: {{ $policy->updated_at->format('M Y') }}
                            </span>
                        </div>
                        
                        <div class="category-tag">{{ $policy->category ?? 'Company General' }}</div>
                        <h5 class="font-weight-bold text-dark mb-4" style="line-height: 1.5; flex-grow: 1;">{{ $policy->title }}</h5>
                        
                        <div class="row no-gutters mt-auto">
                            <div class="col-6 pr-2">
                                <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-view btn-block shadow-sm">
                                    <i class="fas fa-eye mr-1"></i> Preview
                                </a>
                            </div>
                            <div class="col-6 pl-2">
                                <a href="{{ $pdfUrl }}" download class="btn btn-download btn-block shadow-sm">
                                    <i class="fas fa-download mr-1"></i> PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 mt-5 bg-white rounded-lg shadow-sm">
                    <div class="mb-4 d-inline-block p-4" style="background: rgba(75, 0, 232, 0.05); border-radius: 50%;">
                        <i class="fas fa-folder-open text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                    <h3 class="font-weight-bold text-dark mb-2">Policy Repository is Empty</h3>
                    <p class="text-muted" style="font-size: 1.1rem;">There are currently no active policies uploaded in the system.<br>Please contact Human Resources for further assistance.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
