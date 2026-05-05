@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

@section('_content')
<style>
:root{
    --orb-primary:#4B00E8;
    --orb-secondary:#8600EE;
    --orb-bg:#F6F7FB;
    --orb-border:#E7EAF3;
    --orb-text:#101828;
    --orb-muted:#667085;
    --orb-soft:#F4F2FF;
    --orb-shadow:0 14px 35px rgba(16,24,40,.07);
}

.att-page{min-height:calc(100vh - 90px);padding:18px 12px 35px;background:var(--orb-bg);}
.att-container{max-width:1280px;margin:0 auto;}
.att-card{background:#fff;border:1px solid var(--orb-border);border-radius:24px;box-shadow:var(--orb-shadow);overflow:hidden;}

.att-header{
    padding:22px;
    margin-bottom:18px;
    background:linear-gradient(135deg,#fff,#f8f5ff);
    border:1px solid var(--orb-border);
    border-radius:26px;
    box-shadow:var(--orb-shadow);
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:center;
}

.att-title{font-size:26px;font-weight:950;color:var(--orb-text);margin:0;}
.att-subtitle{font-size:13px;color:var(--orb-muted);margin:5px 0 0;}

.att-btn{
    border:0;
    border-radius:14px;
    padding:10px 16px;
    font-weight:900;
    display:inline-flex;
    gap:8px;
    align-items:center;
    justify-content:center;
    text-decoration:none!important;
}

.att-btn-primary{background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));color:#fff!important;}
.att-btn-light{background:#fff;color:var(--orb-text);border:1px solid var(--orb-border);}
.att-btn-danger{background:linear-gradient(135deg,#ec4e74,#ff7675);color:#fff!important;}

.att-table-wrap{padding:16px;}
.att-table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}

.att-table{
    width:100%;
    min-width:900px;
    border-collapse:collapse!important;
}

.att-table th{
    background:#F8FAFC;
    color:#475467;
    font-size:11px;
    font-weight:950;
    text-transform:uppercase;
    padding:13px 14px;
    border-top:1px solid #EAECF0;
    border-bottom:1px solid #EAECF0;
    white-space:nowrap;
}

.att-table td{
    background:#fff;
    border-bottom:1px solid #EEF2F6;
    padding:14px;
    vertical-align:middle;
}

.att-table tbody tr:hover td{background:#FAF8FF;}

.att-badge{
    display:inline-flex;
    align-items:center;
    border-radius:999px;
    padding:6px 11px;
    font-size:11px;
    font-weight:950;
    text-transform:uppercase;
    white-space:nowrap;
}

.badge-active{background:#dcfce7;color:#166534}
.badge-muted{background:#f1f5f9;color:#475569}
.badge-paid{background:#dbeafe;color:#1e40af}
.badge-unpaid{background:#fee2e2;color:#991b1b}

.type-dot{
    width:16px;
    height:16px;
    border-radius:999px;
    display:inline-block;
    border:1px solid rgba(0,0,0,.08);
    vertical-align:middle;
    margin-right:8px;
}

.att-actions{display:flex;gap:7px;justify-content:flex-end;}

.icon-btn{
    width:37px;
    height:37px;
    border-radius:12px;
    border:1px solid var(--orb-border);
    background:#fff;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

/* Premium Modal Fix */
.modal-backdrop{
    z-index:1240!important;
    background:#0F172A!important;
}

.modal-backdrop.show{opacity:.58!important;}
.modal{z-index:1250!important;}

.orb-type-modal .modal-dialog{
    max-width:620px;
}

.att-modal-content{
    border:0;
    border-radius:24px;
    overflow:hidden;
    background:#fff!important;
    box-shadow:0 24px 70px rgba(15,23,42,.28);
}

.att-modal-header{
    padding:18px 22px;
    background:linear-gradient(135deg,#4B00E8,#8600EE);
    color:#fff;
    border-bottom:0;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.att-modal-title{
    margin:0;
    font-size:18px;
    font-weight:900;
}

.att-modal-subtitle{
    margin-top:3px;
    font-size:12px;
    color:rgba(255,255,255,.78);
}

.att-modal-header .close{
    color:#fff;
    opacity:1;
    text-shadow:none;
    outline:none;
}

.att-modal-body{
    padding:22px;
    background:#fff!important;
}

.att-modal-body label{
    font-size:11px;
    font-weight:900;
    color:#667085;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.att-modal-body .form-control{
    height:43px;
    border-radius:13px;
    border:1px solid #E4E7EC;
    font-size:13px;
    background:#fff;
}

.att-modal-body .form-control[type="color"]{
    padding:6px;
}

.att-modal-body .form-control:focus{
    border-color:var(--orb-primary);
    box-shadow:0 0 0 .15rem rgba(75,0,232,.12);
}

.att-modal-section{
    border:1px solid #EEF2F6;
    background:#FAFBFF;
    border-radius:18px;
    padding:16px;
    margin-bottom:16px;
}

.att-modal-section-title{
    font-size:13px;
    font-weight:950;
    color:var(--orb-text);
    margin-bottom:14px;
    display:flex;
    align-items:center;
    gap:8px;
}

.att-modal-section-title i{color:var(--orb-primary);}

.att-modal-footer{
    padding:16px 22px;
    background:#F8FAFC;
    border-top:1px solid #EEF2F6;
    display:flex;
    justify-content:flex-end;
    gap:10px;
}

@media(max-width:768px){
    .att-header{flex-direction:column;align-items:flex-start}
    .att-page{padding:12px 8px 25px;}
    .att-title{font-size:22px;}
    .orb-type-modal .modal-dialog{margin:12px;}
}
</style>

<div class="att-page">
    <div class="att-container">

        <div class="att-header">
            <div>
                <h3 class="att-title">Attendance Types</h3>
                <p class="att-subtitle">Manage paid/unpaid attendance statuses used by reports and approval flows.</p>
            </div>

            <button type="button" class="att-btn att-btn-primary" data-toggle="modal" data-target="#createTypeModal">
                <i class="fas fa-plus"></i> Add Type
            </button>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="att-card">
            <div class="att-table-wrap">
                <div class="att-table-responsive">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Paid</th>
                                <th>Color</th>
                                <th>Linked Attendances</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($attendanceTypes as $type)
                                <tr>
                                    <td><strong>{{ $type->name }}</strong></td>
                                    <td><code>{{ $type->code }}</code></td>

                                    <td>
                                        <span class="att-badge {{ $type->is_paid ? 'badge-paid' : 'badge-unpaid' }}">
                                            {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="type-dot" style="background:{{ $type->color ?: '#64748b' }}"></span>
                                        {{ $type->color ?: '#64748b' }}
                                    </td>

                                    <td>{{ $type->attendances_count ?? 0 }}</td>

                                    <td>
                                        <span class="att-badge {{ $type->is_active ? 'badge-active' : 'badge-muted' }}">
                                            {{ $type->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="att-actions">
                                            <button type="button" class="icon-btn text-primary" data-toggle="modal" data-target="#editTypeModal{{ $type->id }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <form method="POST" action="{{ route('attendance.types.destroy', $type) }}" onsubmit="return confirm('Delete this attendance type? In-use/system types will be deactivated.')" style="display:inline-block;margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="icon-btn text-danger" title="Delete / Deactivate">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        No attendance types found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Edit Modals Outside Table --}}
        @foreach($attendanceTypes as $type)
            <div class="modal fade orb-type-modal" id="editTypeModal{{ $type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('attendance.types.update', $type) }}" class="modal-content att-modal-content">
                        @csrf
                        @method('PUT')

                        <div class="modal-header att-modal-header">
                            <div>
                                <h5 class="att-modal-title">Edit Attendance Type</h5>
                                <div class="att-modal-subtitle">{{ $type->name }} · {{ $type->code }}</div>
                            </div>

                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body att-modal-body">
                            <div class="att-modal-section">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-tag"></i> Type Details
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Code</label>
                                        <input type="text" name="code" class="form-control" value="{{ old('code', $type->code) }}" required>
                                    </div>

                                    <div class="col-md-12 mb-0">
                                        <label>Color</label>
                                        <input type="color" name="color" class="form-control" value="{{ old('color', $type->color ?: '#64748b') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="att-modal-section mb-0">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-toggle-on"></i> Type Settings
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="paid{{ $type->id }}" name="is_paid" value="1" {{ $type->is_paid ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="paid{{ $type->id }}">Paid Type</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="active{{ $type->id }}" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="active{{ $type->id }}">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer att-modal-footer">
                            <button type="button" class="att-btn att-btn-light" data-dismiss="modal">
                                Cancel
                            </button>

                            <button class="att-btn att-btn-primary">
                                <i class="fas fa-save"></i> Save Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- Create Modal Outside Table --}}
        <div class="modal fade orb-type-modal" id="createTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="POST" action="{{ route('attendance.types.store') }}" class="modal-content att-modal-content">
                    @csrf

                    <div class="modal-header att-modal-header">
                        <div>
                            <h5 class="att-modal-title">Add Attendance Type</h5>
                            <div class="att-modal-subtitle">Create a custom attendance status.</div>
                        </div>

                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body att-modal-body">
                        <div class="att-modal-section">
                            <div class="att-modal-section-title">
                                <i class="fas fa-plus-circle"></i> Type Details
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="custom_type" required>
                                </div>

                                <div class="col-md-12 mb-0">
                                    <label>Color</label>
                                    <input type="color" name="color" class="form-control" value="{{ old('color', '#64748b') }}">
                                </div>
                            </div>
                        </div>

                        <div class="att-modal-section mb-0">
                            <div class="att-modal-section-title">
                                <i class="fas fa-toggle-on"></i> Type Settings
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="newPaid" name="is_paid" value="1">
                                        <label class="custom-control-label font-weight-bold" for="newPaid">Paid Type</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="newActive" name="is_active" value="1" checked>
                                        <label class="custom-control-label font-weight-bold" for="newActive">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer att-modal-footer">
                        <button type="button" class="att-btn att-btn-light" data-dismiss="modal">
                            Cancel
                        </button>

                        <button class="att-btn att-btn-primary">
                            <i class="fas fa-save"></i> Create Type
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection