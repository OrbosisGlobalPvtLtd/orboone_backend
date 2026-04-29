@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'hr-documents'])

@section('_content')
<style>
    :root { --orb-1: #4b00e8; --orb-2: #ec4e74; --surface: #ffffff; --background: #f8fafc; }
    body { background: var(--background); font-family: 'Inter', sans-serif; }

    .page-header {
        background: linear-gradient(135deg, #4b00e8 0%, #ec4e74 100%);
        padding: 50px 40px 90px;
        border-radius: 0 0 45px 45px;
        color: white;
        box-shadow: 0 10px 30px rgba(75,0,232,0.15);
    }

    .card-orb {
        background: var(--surface);
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.03);
        margin-bottom: 2rem;
    }

    .doc-item {
        display: flex;
        align-items: center;
        padding: 22px 28px;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s;
        gap: 20px;
    }
    .doc-item:last-child { border-bottom: none; }
    .doc-item:hover { background: #fafbff; }

    .doc-icon-box {
        width: 52px; height: 52px;
        border-radius: 16px;
        background: #f1f5f9;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .doc-item:hover .doc-icon-box { background: #eef2ff; color: var(--orb-1); transform: scale(1.05); }

    .status-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status-verified { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .status-pending  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .status-rejected { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    .btn-action {
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.8rem;
        transition: all 0.3s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-view { background: #f1f5f9; color: #475569; }
    .btn-verify { background: #059669; color: white; box-shadow: 0 4px 12px rgba(5,150,105,0.2); }
    .btn-reject { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    
    .btn-action:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }

    .info-label { font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 5px; display: block; }
    .info-value { font-size: 0.95rem; font-weight: 700; color: #1e293b; }

    .offset-card { margin-top: -60px; position: relative; z-index: 10; }
</style>

<div class="container-fluid p-0">
    {{-- Header --}}
    <div class="page-header">
        <div class="container-fluid px-3 px-md-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div>
                    <a href="{{ route('hr.documents.index') }}" class="text-white small font-weight-bold mb-3 d-inline-block opacity-75 text-decoration-none">
                        <i class="fas fa-arrow-left mr-2"></i> Document Ledger
                    </a>
                    <h1 class="font-weight-bold mb-2">{{ $user->name }}</h1>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge badge-pill bg-white text-dark font-weight-bold px-3 py-2">
                            ID: {{ $user->employee->employee_id ?? 'N/A' }}
                        </span>
                        <span class="font-weight-bold"><i class="fas fa-building mr-2"></i>{{ $user->employee->department->name ?? 'Corporate' }}</span>
                    </div>
                </div>
                <div class="stat-pill" style="background:rgba(255,255,255,0.1); border-radius:20px; padding:15px 25px; border:1px solid rgba(255,255,255,0.1);">
                    <div class="h3 font-weight-bold mb-0 text-center">{{ $documents->count() }}</div>
                    <div class="small text-uppercase font-weight-bold opacity-75">Upload Sets</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="container-fluid px-3 px-md-5 offset-card">
        
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-lg mb-4 p-3 font-weight-bold">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row">
            {{-- Profile Column --}}
            <div class="col-xl-3 col-lg-4 mb-4">
                <div class="card-orb p-4 text-center">
                    @php
                        $empDetail = $user->employee->employeeDetail ?? null;
                        $photo = $empDetail->photo ?? ($empDetail->image ?? 'images/profile.png');
                    @endphp
                    <img src="{{ asset($photo) }}" class="mb-4 shadow-lg" onerror="this.src='{{ asset('images/profile.png') }}'"
                         style="width:120px; height:120px; border-radius:30px; border:5px solid #fff; object-fit:cover;">
                    
                    <h5 class="font-weight-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small font-weight-bold mb-4">{{ $user->employee->position->name ?? 'Staff' }}</p>

                    <div class="text-left border-top pt-4">
                        <div class="mb-3">
                            <span class="info-label">Corporate Email</span>
                            <div class="info-value text-truncate">{{ $user->email }}</div>
                        </div>
                        <div class="mb-3">
                            <span class="info-label">Joined Orbosis</span>
                            <div class="info-value">{{ \Carbon\Carbon::parse($user->employee->start_of_contract)->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Documents Column --}}
            <div class="col-xl-9 col-lg-8">
                <div class="card-orb overflow-hidden">
                    <div class="px-4 py-3 bg-light border-bottom">
                        <h6 class="font-weight-bold text-dark m-0">Verification Queue</h6>
                    </div>

                    @forelse($documents as $doc)
                        <div class="bg-white px-4 py-2 border-bottom" style="background:#f9faff !important;">
                            <span class="small font-weight-bold text-primary">Batch Submission: {{ $doc->created_at->format('d M, Y H:i') }}</span>
                        </div>
                        @foreach($documentColumns as $col => $label)
                            @if($doc->{$col})
                                <div class="doc-item">
                                    <div class="doc-icon-box">
                                        @if(Str::endsWith($doc->{$col}, ['.pdf']))
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        @else
                                            <i class="fas fa-file-image text-info"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold text-dark">{{ $label }}</div>
                                        <div class="small text-muted">Uploaded via {{ $doc->document_type }}</div>
                                    </div>
                                    <div class="text-right d-flex align-items-center gap-3">
                                        <span class="status-badge status-{{ $doc->status }}">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                        <div class="d-flex gap-2">
                                            <a href="{{ asset($doc->{$col}) }}" target="_blank" class="btn-action btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if($doc->status == 'pending')
                                                <form action="{{ route('hr.documents.approve', $doc->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-verify"><i class="fas fa-check"></i> Verify</button>
                                                </form>
                                                <button class="btn-action btn-reject" data-toggle="modal" data-target="#rejectModal{{ $doc->id }}">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <h5 class="text-muted">No documents available for review.</h5>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rejection Modals --}}
@foreach($documents as $doc)
    <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius:22px; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
                <form action="{{ route('hr.documents.reject', $doc->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 text-center">
                        <i class="fas fa-exclamation-circle text-danger fa-4x mb-4"></i>
                        <h4 class="font-weight-bold text-dark mb-2">Reject Documentation</h4>
                        <p class="text-muted small mb-4">Provide a specific reason for rejection to help the employee re-upload correctly.</p>
                        <textarea name="reason" class="form-control border-0 bg-light p-3" rows="4" 
                                  placeholder="e.g. Identity proof is not clear..." required style="border-radius:15px;"></textarea>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 font-weight-bold">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endsection
