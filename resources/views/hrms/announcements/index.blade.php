@extends('layouts.panel', ['active' => 'announcements'])

@section('page_title', 'Notice & Announcements')

@section('_head')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .ann-page {
        background: var(--orb-bg);
        padding: 18px 10px 35px;
        min-height: calc(100vh - 90px);
    }

    .ann-hero {
        border-radius: 26px;
        padding: 24px;
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        box-shadow: var(--orb-shadow);
    }

    .ann-hero h3 {
        font-weight: 800;
        margin: 0;
    }

    .ann-hero p {
        opacity: .9;
        margin: 6px 0 0;
    }

    .ann-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 22px;
        box-shadow: var(--orb-shadow);
    }

    .stat-card {
        padding: 18px;
        border-radius: 20px;
        background: #fff;
        border: 1px solid rgba(255, 255, 255, .4);
    }

    .stat-card small {
        color: var(--orb-muted);
        font-weight: 700;
    }

    .stat-card h4 {
        font-weight: 900;
        margin: 5px 0 0;
        color: var(--orb-text);
    }

    .ann-badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        text-transform: capitalize;
    }

    .badge-general {
        background: #EEF2FF;
        color: #3538CD;
    }

    .badge-holiday {
        background: #ECFDF3;
        color: #027A48;
    }

    .badge-emergency {
        background: #FEF3F2;
        color: #B42318;
    }

    .badge-policy {
        background: #F4F3FF;
        color: #5925DC;
    }

    .badge-meeting {
        background: #FFF7E6;
        color: #B54708;
    }

    .priority-low {
        background: #F2F4F7;
        color: #344054;
    }

    .priority-normal {
        background: #EEF2FF;
        color: #3538CD;
    }

    .priority-high {
        background: #FFF3CD;
        color: #B54708;
    }

    .priority-urgent {
        background: #FFE4E8;
        color: #C01048;
    }

    .target-badge {
        background: #F4F2FF;
        color: #4B00E8;
    }

    .status-on {
        background: #ECFDF3;
        color: #027A48;
    }

    .status-off {
        background: #F2F4F7;
        color: #667085;
    }

    .filter-box {
        padding: 18px;
    }

    .form-control,
    .form-select {
        border-radius: 14px;
        border-color: var(--orb-border);
        min-height: 44px;
    }

    .btn-orb {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border: 0;
        color: #fff;
        border-radius: 14px;
        font-weight: 800;
        padding: 11px 16px;
    }

    .btn-soft {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 0;
        border-radius: 12px;
        font-weight: 800;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .modal-content {
        border-radius: 24px;
        border: 0;
        overflow: hidden;
    }

    .announcement-modal {
        position: fixed !important;
        inset: 0 !important;
        z-index: 30000 !important;
        padding-left: 0 !important;
    }

    .announcement-modal .modal-dialog {
        margin: 1.75rem auto !important;
        max-width: 1040px;
        transform: none;
    }

    .announcement-modal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        max-height: calc(100vh - 56px);
        display: flex;
        flex-direction: column;
    }

    .announcement-modal .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 230px);
        padding: 26px 30px;
    }

    .announcement-modal .modal-footer {
        flex-shrink: 0;
        padding: 20px 30px;
        background: #fff;
        border-top: 1px solid var(--orb-border);
        display: flex;
        justify-content: flex-end;
        gap: 14px;
    }

    .modal-backdrop {
        z-index: 29990 !important;
    }

    body.modal-open {
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
    }

    .table-responsive {
        overflow-x: auto;
    }

    @media(max-width:768px) {
        .ann-hero {
            padding: 18px;
        }

        .ann-hero .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('_content')

<div class="ann-page">

    <div class="ann-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3>Notice & Announcements</h3>
            <p>
                Publish HR notices, holidays, policies, emergency alerts and test mobile push notifications.
            </p>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">

            @if($permissions['canPrint'])
            <a href="{{ route('announcements.print') }}"
                target="_blank"
                class="btn btn-light fw-bold rounded-pill px-4">
                <i class="fas fa-print me-1"></i>
                Print
            </a>
            @endif

            @if($permissions['canCreate'] || $permissions['canManage'])
            <button type="button"
                class="btn btn-light fw-bold rounded-pill px-4"
                data-toggle="modal"
                data-target="#announcementModal"
                data-bs-toggle="modal"
                data-bs-target="#announcementModal">

                <i class="fas fa-plus me-1"></i>
                Publish
            </button>
            @endif

        </div>
    </div>

    <div class="row g-3 mt-2">

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small>Total</small>
                <h4>{{ $stats['total'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small>Active</small>
                <h4>{{ $stats['active'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small>Urgent</small>
                <h4>{{ $stats['urgent'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small>Today</small>
                <h4>{{ $stats['today'] ?? 0 }}</h4>
            </div>
        </div>

    </div>

    <div class="ann-card mt-3 filter-box">

        <div class="row g-3">

            <div class="col-md-3">
                <select id="filterType" class="form-select">
                    <option value="">All Types</option>
                    <option value="general">General</option>
                    <option value="holiday">Holiday</option>
                    <option value="emergency">Emergency</option>
                    <option value="policy">Policy</option>
                    <option value="meeting">Meeting</option>
                </select>
            </div>

            <div class="col-md-3">
                <select id="filterPriority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>

            <div class="col-md-3">
                <select id="filterTarget" class="form-select">
                    <option value="">All Targets</option>
                    <option value="all">All</option>
                    <option value="employee">Employee</option>
                    <option value="admin">Admin</option>
                    <option value="hr">HR</option>
                </select>
            </div>

            <div class="col-md-3">
                <select id="filterStatus" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

        </div>

    </div>

    <div class="ann-card mt-3 p-3">

        <div class="table-responsive">

            <table id="announcementTable" class="table table-hover w-100 nowrap">

                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Attachment</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        @if($permissions['canUpdate'] || $permissions['canDelete'] || $permissions['canToggle'] || $permissions['canManage'])
                        <th width="140">Actions</th>
                        @endif
                    </tr>
                </thead>

            </table>

        </div>

    </div>

</div>

<div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content orb-modal">
            <div class="orb-modal-header">
                <div>
                    <h5 class="modal-title" id="modalTitle">Publish Announcement</h5>
                    <p class="orb-modal-subtitle">Post general notices, holiday updates, emergency warnings or policy changes.</p>
                </div>
                <button type="button" class="close btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1; border:0; background:transparent; font-size:24px; padding:0; outline:none; line-height:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data" id="announcementForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-body orb-modal-body">
                    <!-- Section 1: Content -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-edit"></i> Announcement Content
                        </div>
                        <div class="orb-form-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label class="orb-form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div>
                                <label class="orb-form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" rows="4" class="form-control" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Distribution Settings -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-cog"></i> Settings & Target Audience
                        </div>
                        <div class="orb-form-grid-3">
                            <div>
                                <label class="orb-form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="general">General</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="policy">Policy</option>
                                    <option value="meeting">Meeting</option>
                                </select>
                            </div>
                            <div>
                                <label class="orb-form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-control" required>
                                    <option value="low">Low</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="orb-form-label">Target Audience <span class="text-danger">*</span></label>
                                <select name="target_type" id="target_type" class="form-control" required>
                                    <option value="all">All</option>
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                    <option value="hr">HR</option>
                                    <option value="role">Specific Role</option>
                                    <option value="department">Specific Department</option>
                                    <option value="user">Specific User</option>
                                </select>
                            </div>

                            <div class="target-input d-none orb-col-span-3" id="target_role_div">
                                <label class="orb-form-label">Select Role <span class="text-danger">*</span></label>
                                <select name="target_role_id" id="target_role_id" class="form-control">
                                    <option value="">-- Choose Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="target-input d-none orb-col-span-3" id="target_department_div">
                                <label class="orb-form-label">Select Department <span class="text-danger">*</span></label>
                                <select name="target_department_id" id="target_department_id" class="form-control">
                                    <option value="">-- Choose Dept --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="target-input d-none orb-col-span-3" id="target_user_div">
                                <label class="orb-form-label">Select User <span class="text-danger">*</span></label>
                                <select name="target_user_id" id="target_user_id" class="form-control">
                                    <option value="">-- Choose User --</option>
                                    @foreach($users as $targetUser)
                                        <option value="{{ $targetUser->id }}">{{ $targetUser->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="orb-form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                            <div>
                                <label class="orb-form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>
                            <div>
                                <label class="orb-form-label">Attachment</label>
                                <input type="file" name="attachment" id="attachment" class="form-control">
                            </div>

                            <div id="currentAttachment" class="mt-2 d-none orb-col-span-3">
                                <small class="text-muted d-block mb-1">Current Attachment:</small>
                                <div id="attachmentPreview" class="p-2 border rounded-3 bg-light d-flex align-items-center gap-2">
                                    <i class="fas fa-file"></i>
                                    <a href="#" target="_blank" id="attachmentLink" class="text-primary fw-bold small text-truncate">View File</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Publishing Options -->
                    <div class="orb-form-section">
                        <div class="orb-form-section-title">
                            <i class="fas fa-toggle-on"></i> Publishing Options
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" name="is_active" id="is_active" value="1" checked>
                            <label class="custom-control-label font-weight-bold" for="is_active">Active / Publish Now</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer orb-modal-footer">
                    <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="orb-btn-primary" id="submitBtn"><i class="fas fa-paper-plane"></i> Publish Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('_script')

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {

        if (!$.fn.DataTable) {
            console.error('DataTable not loaded');
            return;
        }

        function openAnnouncementModal() {

            const modalEl = document.getElementById('announcementModal');

            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                $('#announcementModal').modal('show');
            }
        }

        const table = $('#announcementTable').DataTable({
            processing: true,
            serverSide: true,

            ajax: {
                url: "{{ route('announcements.index') }}",

                data: function(d) {
                    d.ajax_table = 1;
                    d.type = $('#filterType').val();
                    d.priority = $('#filterPriority').val();
                    d.target_type = $('#filterTarget').val();
                    d.status = $('#filterStatus').val();
                }
            },

            columns: [

                {
                    data: 'title',
                    render: function(data, type, row) {
                        return `
                        <div class="fw-bold">${data}</div>
                        <small class="text-muted">${row.description ?? ''}</small>
                    `;
                    }
                },

                {
                    data: 'type',
                    render: data => `<span class="ann-badge badge-${data}">${data}</span>`
                },

                {
                    data: 'priority',
                    render: data => `<span class="ann-badge priority-${data}">${data}</span>`
                },

                {
                    data: 'target_type',
                    render: data => `<span class="ann-badge target-badge">${data}</span>`
                },

                {
                    data: 'is_active',
                    render: data =>
                        data ?
                        `<span class="ann-badge status-on">Active</span>` : `<span class="ann-badge status-off">Inactive</span>`
                },

                {
                    data: 'created_by'
                },

                {
                    data: 'attachment_url',
                    render: function(data) {
                        if (data) {
                            return `
                                <a href="${data}" target="_blank" class="btn btn-sm btn-soft">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                            `;
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },

                {
                    data: 'created_at'
                },

                @if($permissions['canUpdate'] || $permissions['canDelete'] || $permissions['canToggle'] || $permissions['canManage']) {
                    data: null,
                    orderable: false,
                    searchable: false,

                    render: function(row) {

                        const editData = encodeURIComponent(JSON.stringify(row.edit_data ?? row));
                        let html = `<div class="d-flex gap-1">`;

                        @if($permissions['canUpdate'] || $permissions['canManage'])
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-soft editBtn"
                                    data-row="${editData}">
                                <i class="fas fa-edit"></i>
                            </button>
                        `;
                        @endif

                        @if($permissions['canToggle'] || $permissions['canManage'])
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-soft toggleBtn"
                                    data-id="${row.id}">
                                <i class="fas fa-power-off"></i>
                            </button>
                        `;
                        @endif

                        @if($permissions['canDelete'] || $permissions['canManage'])
                        html += `
                            <button type="button"
                                    class="btn btn-sm btn-danger deleteBtn"
                                    data-id="${row.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                        @endif

                        html += `</div>`;
                        return html;
                    }
                }
                @endif

            ]
        });

        $('#filterType,#filterPriority,#filterTarget,#filterStatus')
            .on('change', function() {
                table.ajax.reload();
            });

        $(document).on('click', '.editBtn', function() {

            const row = JSON.parse(decodeURIComponent($(this).attr('data-row')));

            $('#modalTitle').text('Edit Announcement');
            $('#submitBtn').text('Update Announcement');

            $('#announcementForm')
                .attr('action', "{{ url('/announcements') }}/" + row.id);

            $('#formMethod').val('PUT');

            $('#title').val(row.title ?? '');
            $('#description').val(row.description ?? '');
            $('#type').val(row.type ?? 'general');
            $('#priority').val(row.priority ?? 'normal');
            $('#target_type').val(row.target_type ?? 'all');
            $('#target_role_id').val(row.target_role_id ?? '');
            $('#target_department_id').val(row.target_department_id ?? '');
            $('#target_user_id').val(row.target_user_id ?? '');

            triggerTargetVisibility(row.target_type ?? 'all');

            $('#start_date').val(row.start_date ? row.start_date.substring(0, 10) : '');
            $('#end_date').val(row.end_date ? row.end_date.substring(0, 10) : '');

            $('#is_active').prop('checked', row.is_active == true);

            if (row.attachment_url) {
                $('#currentAttachment').removeClass('d-none');
                $('#attachmentLink').attr('href', row.attachment_url).text(row.attachment ? row.attachment.split('/').pop() : 'View File');

                // If it's an image, we could show a tiny thumbnail here if desired
                const isImage = /\.(jpg|jpeg|png|webp|gif)$/i.test(row.attachment_url);
                $('#attachmentPreview i').attr('class', isImage ? 'fas fa-image' : 'fas fa-file-pdf');
            } else {
                $('#currentAttachment').addClass('d-none');
            }

            openAnnouncementModal();
        });

        $('#announcementModal').on('hidden.bs.modal hidden', function() {

            $('#modalTitle').text('Publish Announcement');

            $('#submitBtn').text('Publish Announcement');

            $('#announcementForm')
                .attr('action', "{{ route('announcements.store') }}");

            $('#announcementForm')[0].reset();

            $('#formMethod').val('POST');

            $('#priority').val('normal');
            $('#type').val('general');
            $('#target_type').val('all');

            $('#is_active').prop('checked', true);
            $('#currentAttachment').addClass('d-none');
        });

        $(document).on('click', '.toggleBtn', function() {

            const id = $(this).data('id');

            $.ajax({

                url: "{{ url('/announcements') }}/" + id + "/toggle-status",

                type: "POST",

                data: {
                    _token: "{{ csrf_token() }}",
                    _method: "PATCH"
                },

                success: function() {
                    table.ajax.reload(null, false);
                }

            });
        });

        $(document).on('click', '.deleteBtn', function() {

            const id = $(this).data('id');

            if (!confirm('Delete this announcement?')) {
                return;
            }

            $.ajax({

                url: "{{ url('/announcements') }}/" + id,

                type: "POST",

                data: {
                    _token: "{{ csrf_token() }}",
                    _method: "DELETE"
                },

                success: function() {
                    table.ajax.reload(null, false);
                }

            });
        });

        $('#target_type').on('change', function() {
            triggerTargetVisibility($(this).val());
        });

        function triggerTargetVisibility(type) {
            $('.target-input').addClass('d-none');
            if (type === 'role') $('#target_role_div').removeClass('d-none');
            if (type === 'department') $('#target_department_div').removeClass('d-none');
            if (type === 'user') $('#target_user_div').removeClass('d-none');
        }

    });
</script>

@endsection