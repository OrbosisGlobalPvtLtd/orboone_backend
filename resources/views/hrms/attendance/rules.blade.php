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
.att-container{max-width:1380px;margin:0 auto;}
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

.att-table-wrap{padding:16px;}
.att-table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}

.att-table{
    width:100%;
    min-width:1120px;
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
.badge-default{background:#ede9fe;color:#5b21b6}

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

/* Modal Fix + Premium UI */
.modal-backdrop{
    z-index:1240!important;
    background:#0F172A!important;
}
.modal-backdrop.show{opacity:.58!important;}
.modal{z-index:1250!important;}

.orb-rule-modal .modal-dialog{
    max-width:860px;
}

.orb-rule-modal .modal-content{
    width:100%;
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
    .orb-rule-modal .modal-dialog{
        margin:12px;
    }
}
</style>

<div class="att-page">
    <div class="att-container">

        <div class="att-header">
            <div>
                <h3 class="att-title">Attendance Rules / Shift Timing</h3>
                <p class="att-subtitle">Shift timing controls used by attendance calculations.</p>
            </div>

            <a href="{{ route('attendances.index') }}" class="att-btn att-btn-light">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
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
                                <th>Shift Name</th>
                                <th>Punch Allowed From</th>
                                <th>Shift Start</th>
                                <th>Late After</th>
                                <th>Half Day After</th>
                                <th>Shift End</th>
                                <th>Required Minutes</th>
                                <th>Half Day Min</th>
                                <th>Lunch</th>
                                <th>Default</th>
                                <th>Active</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($attendanceTimes as $time)
                                <tr>
                                    <td>
                                        <strong>{{ $time->name }}</strong>
                                        <div class="text-muted small">{{ $time->code }}</div>
                                    </td>

                                    <td>{{ \Carbon\Carbon::parse($time->punch_allowed_from)->format('h:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($time->shift_start_time)->format('h:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($time->late_after_time)->format('h:i A') }}</td>
                                    <td>{{ $time->half_day_after_time ? \Carbon\Carbon::parse($time->half_day_after_time)->format('h:i A') : '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($time->shift_end_time)->format('h:i A') }}</td>
                                    <td>{{ $time->required_work_minutes }}</td>
                                    <td>{{ $time->half_day_min_minutes }}</td>
                                    <td>{{ $time->lunch_break_minutes }}</td>

                                    <td>
                                        <span class="att-badge {{ $time->is_default ? 'badge-default' : 'badge-muted' }}">
                                            {{ $time->is_default ? 'Default' : 'No' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="att-badge {{ $time->is_active ? 'badge-active' : 'badge-muted' }}">
                                            {{ $time->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    <td class="text-right">
                                        <button
                                            type="button"
                                            class="icon-btn text-primary"
                                            data-toggle="modal"
                                            data-target="#ruleModal{{ $time->id }}"
                                            title="Edit Rule">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">
                                        No attendance rules found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modals outside table --}}
        @foreach($attendanceTimes as $time)
            <div class="modal fade orb-rule-modal" id="ruleModal{{ $time->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('attendance.rules.update', $time) }}" class="modal-content att-modal-content">
                        @csrf
                        @method('PUT')

                        <div class="modal-header att-modal-header">
                            <div>
                                <h5 class="att-modal-title">Edit Shift Timing</h5>
                                <div class="att-modal-subtitle">{{ $time->name }} · {{ $time->code }}</div>
                            </div>

                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body att-modal-body">
                            <div class="att-modal-section">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-clock"></i> Basic Shift Details
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Shift Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $time->name) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Punch Allowed From</label>
                                        <input type="time" name="punch_allowed_from" class="form-control" value="{{ \Carbon\Carbon::parse($time->punch_allowed_from)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Shift Start</label>
                                        <input type="time" name="shift_start_time" class="form-control" value="{{ \Carbon\Carbon::parse($time->shift_start_time)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Late After</label>
                                        <input type="time" name="late_after_time" class="form-control" value="{{ \Carbon\Carbon::parse($time->late_after_time)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Half Day After</label>
                                        <input type="time" name="half_day_after_time" class="form-control" value="{{ $time->half_day_after_time ? \Carbon\Carbon::parse($time->half_day_after_time)->format('H:i') : '' }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Shift End</label>
                                        <input type="time" name="shift_end_time" class="form-control" value="{{ \Carbon\Carbon::parse($time->shift_end_time)->format('H:i') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Required Work Minutes</label>
                                        <input type="number" name="required_work_minutes" class="form-control" min="1" value="{{ old('required_work_minutes', $time->required_work_minutes) }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>Half Day Min Minutes</label>
                                        <input type="number" name="half_day_min_minutes" class="form-control" min="1" value="{{ old('half_day_min_minutes', $time->half_day_min_minutes) }}" required>
                                    </div>

                                    <div class="col-md-4 mb-0">
                                        <label>Lunch Break Minutes</label>
                                        <input type="number" name="lunch_break_minutes" class="form-control" min="0" value="{{ old('lunch_break_minutes', $time->lunch_break_minutes) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="att-modal-section mb-0">
                                <div class="att-modal-section-title">
                                    <i class="fas fa-toggle-on"></i> Shift Status
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="default{{ $time->id }}" name="is_default" value="1" {{ $time->is_default ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="default{{ $time->id }}">
                                                Default Shift
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="active{{ $time->id }}" name="is_active" value="1" {{ $time->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="active{{ $time->id }}">
                                                Active
                                            </label>
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
                                <i class="fas fa-save"></i> Save Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

    </div>
</div>
@endsection