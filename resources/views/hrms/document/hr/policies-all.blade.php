@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'document-management'])

@section('page_title', 'Company Documents & Policies')

@section('_head')
@include('settings.partials.styles')
<style>
    .document-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Table hover */
    .set-table tr:hover td {
        background-color: rgba(75, 0, 232, 0.015);
    }
    
    .gap-2 {
        gap: 8px;
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="document-container">
        
        <!-- Premium Purple Gradient Hero Header -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-folder-open"></i> COMPANY &bull; DOCUMENTS &amp; POLICIES
                </div>
                <h1 class="set-title">Company Documents &amp; Policies</h1>
                <p class="set-subtitle">Publish, upload, and manage official organizational documents, templates, forms, and HR policies.</p>
            </div>
            <div>
                <button class="set-btn" data-toggle="modal" data-target="#addPolicyModal">
                    <i class="fas fa-plus-circle"></i> Upload Document / Policy
                </button>
            </div>
        </div>

        @include('components.alerts')

        <!-- Table Listing Card -->
        <div class="set-card">
            <div class="set-card-header">
                <div class="set-head-left">
                    <div class="set-icon-box">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div>
                        <h5 class="set-card-title">Active Repository</h5>
                        <p class="set-card-subtitle">Access global files, employee guidelines, handbooks, and operational compliance documents.</p>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="set-table">
                    <thead>
                        <tr>
                            <th style="border-top-left-radius: 22px; padding-left: 24px;">Document Name</th>
                            <th>Category</th>
                            <th>Visibility / Target Audience</th>
                            <th>Upload Details</th>
                            <th class="text-right" style="border-top-right-radius: 22px; padding-right: 24px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docs as $doc)
                            <tr>
                                <td style="padding-left: 24px;">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 p-3 text-danger d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; background: rgba(239, 68, 68, 0.08); width: 44px; height: 44px;">
                                            <i class="fas fa-file-pdf fa-lg"></i>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold d-block text-dark" style="font-size: 14px;">{{ $doc->title }}</span>
                                            <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem;">ID: #DOC-{{ $doc->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="set-badge">{{ $doc->category }}</span>
                                </td>
                                <td>
                                    @php
                                        $visibilities = $doc->visible_to;
                                        if (is_string($visibilities)) {
                                            $visibilities = json_decode($visibilities, true) ?? [$visibilities];
                                        }
                                        $visibilities = $visibilities ?? ['employee', 'admin'];
                                    @endphp
                                    @foreach($visibilities as $v)
                                        <span class="badge badge-pill badge-light border px-2 py-1 small mr-1" style="font-weight: 700; color: var(--set-muted);">{{ ucfirst($v) }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 700; color: var(--set-text);">By: Admin</div>
                                    <small class="text-muted" style="font-weight: 600;">{{ $doc->created_at->format('d M, Y') }}</small>
                                </td>
                                <td class="text-right" style="padding-right: 24px;">
                                    <div class="d-flex justify-content-end gap-2 align-items-center">
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-light border mr-2" style="border-radius: 10px; height: 36px; width: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                        <form action="{{ route('documents.policies.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document/policy?')" style="margin: 0; display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" style="border-radius: 10px; height: 36px; width: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-4x text-light mb-3"></i>
                                    <h5 class="text-muted font-weight-bold">No company documents or policies uploaded yet.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
                        <label class="set-label" for="policy_file">Attachment File (PDF/Image/Word) <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="policy_file" class="form-control set-control p-1" required>
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
@endsection
