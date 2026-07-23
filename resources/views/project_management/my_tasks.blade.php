@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'my_tasks'])

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('_content')
@include('hrms.employee.partials.styles')

<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
    }

    .my-task-page {
        min-height: calc(100vh - 90px);
        background: var(--orb-bg);
        padding: 24px;
        font-family: 'Outfit', sans-serif;
    }

    .task-hero {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
        border-radius: 20px;
        padding: 24px 28px;
        color: #fff;
        margin-bottom: 24px;
    }

    .stat-bar {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    @media (max-width: 1200px) {
        .stat-bar {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .stat-bar {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .stat-bar {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        padding: 14px 16px;
        text-decoration: none !important;
        color: var(--orb-text) !important;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.05);
    }

    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    /* Table custom stylings */
    .table th {
        background: #F8FAFC !important;
        color: #64748b !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .table td {
        vertical-align: middle !important;
        border-bottom: 1px solid var(--orb-border) !important;
    }

    .task-badge {
        font-size: 10px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        padding: 4px 10px !important;
        border-radius: 50px !important;
    }
    .badge-pending { background: #FFF7ED !important; color: #C2410C !important; }
    .badge-in_progress, .badge-progress { background: #EFF6FF !important; color: #1D4ED8 !important; }
    .badge-on_hold { background: #FEF3C7 !important; color: #D97706 !important; }
    .badge-completed { background: #ECFDF5 !important; color: #047857 !important; }
    .badge-verified { background: #F0FDF4 !important; color: #15803D !important; }
    .badge-closed { background: #F1F5F9 !important; color: #475467 !important; }
</style>

<div class="my-task-page">
    <div class="container-fluid max-w-1500 p-0">

        <!-- Hero Header -->
        <div class="task-hero d-flex align-items-center justify-content-between">
            <div>
                <h3 class="font-weight-bold mb-1 text-white">My Work & Tasks</h3>
                <p class="mb-0 text-white-50 small">Manage your assigned responsibilities, update progress, and post deliverables.</p>
            </div>
            <div>
                <span class="badge badge-light px-3 py-2 text-primary font-weight-bold" style="border-radius: 50px; font-size: 12px;">
                    <i class="fas fa-user-circle mr-1"></i> {{ auth()->user()->name }}
                </span>
            </div>
        </div>

        <!-- KPI Stat Bar -->
        <div class="stat-bar">
            <a href="?status=all" class="stat-card">
                <div class="stat-icon" style="background:#EEF2FF; color:#4F46E5;"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="font-weight-bold h5 mb-0">{{ $stats['total'] }}</div>
                    <div class="small text-muted font-weight-bold">TOTAL</div>
                </div>
            </a>
            <a href="?status=pending" class="stat-card">
                <div class="stat-icon" style="background:#FFF7ED; color:#C2410C;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="font-weight-bold h5 mb-0">{{ $stats['pending'] }}</div>
                    <div class="small text-muted font-weight-bold">PENDING</div>
                </div>
            </a>
            <a href="?status=in_progress" class="stat-card">
                <div class="stat-icon" style="background:#EFF6FF; color:#1D4ED8;"><i class="fas fa-spinner"></i></div>
                <div>
                    <div class="font-weight-bold h5 mb-0">{{ $stats['in_progress'] }}</div>
                    <div class="small text-muted font-weight-bold">IN PROGRESS</div>
                </div>
            </a>
            <a href="?status=on_hold" class="stat-card">
                <div class="stat-icon" style="background:#FEF3C7; color:#D97706;"><i class="fas fa-pause"></i></div>
                <div>
                    <div class="font-weight-bold h5 mb-0">{{ $stats['on_hold'] }}</div>
                    <div class="small text-muted font-weight-bold">ON HOLD</div>
                </div>
            </a>
            <a href="?status=completed" class="stat-card">
                <div class="stat-icon" style="background:#ECFDF5; color:#047857;"><i class="fas fa-check"></i></div>
                <div>
                    <div class="font-weight-bold h5 mb-0">{{ $stats['completed'] }}</div>
                    <div class="small text-muted font-weight-bold">COMPLETED</div>
                </div>
            </a>
            <a href="?status=overdue" class="stat-card">
                <div class="stat-icon" style="background:#FEF2F2; color:#DC2626;"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <div class="font-weight-bold h5 mb-0">{{ $stats['overdue'] }}</div>
                    <div class="small text-muted font-weight-bold">OVERDUE</div>
                </div>
            </a>
        </div>

        <!-- Task Table Card -->
        <div class="card border rounded-lg shadow-sm bg-white overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 text-center" style="width: 70px;">S.No.</th>
                            <th class="py-3 px-4 text-center" style="width: 100px;">Task ID</th>
                            <th class="py-3 px-4">Task Details</th>
                            <th class="py-3 px-4" style="width: 180px;">Due Date</th>
                            <th class="py-3 px-4" style="width: 180px;">Status</th>
                            <th class="py-3 px-4" style="width: 200px;">Update Status</th>
                            <th class="py-3 px-4 text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px; font-weight: 600;">
                        @forelse($tasks as $t)
                            <tr>
                                <td class="text-center text-muted font-weight-bold py-3 px-4">{{ $loop->iteration }}</td>
                                <td class="text-center font-weight-bold text-dark py-3 px-4">#{{ $t->id }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-weight-bold text-dark mb-1">{{ $t->title }}</div>
                                    <div class="text-muted small" style="max-height: 2.4em; overflow: hidden; text-overflow: ellipsis; font-weight: 500;">
                                        {{ strip_tags($t->clean_description) }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-calendar-alt text-muted mr-1"></i>
                                        <span>{{ $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('M d, Y') : '-' }}</span>
                                    </div>
                                    @if($t->is_overdue)
                                        <span class="badge badge-danger px-2 py-1 mt-1 font-weight-bold" style="font-size: 9px;">OVERDUE</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="task-badge badge-{{ $t->status }}">
                                        {{ str_replace('_', ' ', $t->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if(!in_array($t->status, ['verified', 'closed']))
                                        <select class="form-select form-select-sm" id="statusSelect-{{ $t->id }}" style="font-size: 12px; height: 32px; font-weight: 600;" onchange="updateMyTaskStatus({{ $t->id }})">
                                            <option value="pending" {{ $t->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="in_progress" {{ in_array($t->status, ['in_progress', 'progress']) ? 'selected' : '' }}>In Progress</option>
                                            <option value="on_hold" {{ $t->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                            <option value="completed" {{ $t->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                    @else
                                        <span class="badge badge-success px-3 py-1 font-weight-bold uppercase" style="font-size: 10px;">
                                            <i class="fas fa-lock mr-1"></i> Locked
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center py-3 px-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary" style="height: 32px; font-size: 11px; font-weight: 700; border-radius: 6px;" onclick="openTaskDetailModal({{ $t->id }})">
                                        <i class="fas fa-eye mr-1"></i> Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-check fa-3x text-muted mb-2"></i>
                                    <h6 class="font-weight-bold text-muted">No Tasks Found</h6>
                                    <p class="small text-secondary mb-0">You currently have no tasks matching this filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- TASK DETAIL & COMMENT MODAL -->
<div class="modal fade" id="taskDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="detailModalTitle">Task Detail</h5>
                    <p class="mb-0 text-white-50 small" id="detailModalSubtitle"></p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="card p-3 mb-3 border bg-light">
                    <h6 class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 11px;">Task Description</h6>
                    <div id="detailDescription" style="white-space: pre-line; font-size: 14px;"></div>
                </div>

                <!-- Discussion & Comment Feed -->
                <div class="card p-3 border mb-3">
                    <h6 class="font-weight-bold text-muted text-uppercase mb-3" style="font-size: 11px;">Comments & Deliverables</h6>
                    <div id="commentsFeed" style="max-height: 200px; overflow-y: auto;" class="mb-3"></div>

                    <form id="commentForm" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-2">
                            <textarea name="comment" id="commentInput" class="form-control" rows="2" placeholder="Add update or notes..." required></textarea>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <input type="file" name="attachment" id="attachmentInput" class="form-control-file" style="max-width: 250px; font-size: 12px;">
                            <button type="button" class="btn btn-sm btn-primary" onclick="submitComment()">Post Comment</button>
                        </div>
                    </form>
                </div>

                <!-- Activity Log -->
                <div class="card p-3 border">
                    <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 11px;">Activity History</h6>
                    <div id="timelineFeed" style="max-height: 150px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('_script')
<script>
let currentDetailTaskId = null;

function updateMyTaskStatus(taskId) {
    let st = $('#statusSelect-' + taskId).val();
    $.post('/hrms/task/' + taskId + '/update_status', {
        _token: '{{ csrf_token() }}',
        status: st
    }, function(res) {
        if(res.status) {
            alert('Task status updated successfully.');
            location.reload();
        } else {
            alert(res.message || 'Error updating status');
        }
    }).fail(function(err) {
        alert(err.responseJSON ? err.responseJSON.message : 'Error updating task.');
    });
}

function openTaskDetailModal(taskId) {
    currentDetailTaskId = taskId;
    $('#taskDetailModal').modal('show');
    $('#commentsFeed').html('<div class="text-center p-3 text-muted">Loading...</div>');

    $.get('/hrms/task_detail/' + taskId, function(res) {
        if(res.status) {
            let t = res.task;
            $('#detailModalTitle').text('#' + t.id + ' - ' + t.title);
            $('#detailModalSubtitle').text('Due Date: ' + t.formatted_due_date + ' | Status: ' + t.status);
            $('#detailDescription').text(t.description || 'No details provided.');

            let comments = t.updates.comments || [];
            if(comments.length === 0) {
                $('#commentsFeed').html('<div class="text-muted small">No comments yet.</div>');
            } else {
                let cHtml = '';
                comments.forEach(c => {
                    let attHtml = '';
                    if(c.attachments && c.attachments.length > 0) {
                        c.attachments.forEach(att => {
                            attHtml += `<div class="mt-1"><a href="${att.url}" target="_blank" class="small"><i class="fas fa-paperclip"></i> ${att.name}</a></div>`;
                        });
                    }
                    cHtml += `
                        <div class="p-2 mb-2 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="small text-dark">${c.user_name} (${c.role})</strong>
                                <span class="text-muted extra-small" style="font-size: 10px;">${c.created_at}</span>
                            </div>
                            <div class="small text-secondary">${c.comment}</div>
                            ${attHtml}
                        </div>
                    `;
                });
                $('#commentsFeed').html(cHtml);
            }

            let timeline = t.updates.timeline || [];
            if(timeline.length === 0) {
                $('#timelineFeed').html('<div class="text-muted small">No timeline data.</div>');
            } else {
                let tHtml = '';
                timeline.slice().reverse().forEach(tl => {
                    tHtml += `
                        <div class="mb-2 pb-1 border-bottom">
                            <div class="font-weight-bold small text-dark">${tl.event}</div>
                            <div class="extra-small text-muted">${tl.user_name} • ${tl.timestamp}</div>
                        </div>
                    `;
                });
                $('#timelineFeed').html(tHtml);
            }
        }
    });
}

function submitComment() {
    if(!currentDetailTaskId) return;
    let text = $('#commentInput').val().trim();
    if(!text) {
        alert('Please enter a comment.');
        return;
    }

    let formData = new FormData($('#commentForm')[0]);

    $.ajax({
        url: '/hrms/task/' + currentDetailTaskId + '/comment',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
            if(res.status) {
                $('#commentInput').val('');
                $('#attachmentInput').val('');
                openTaskDetailModal(currentDetailTaskId);
            }
        },
        error: function(err) {
            alert('Failed to post comment.');
        }
    });
}
</script>
@endsection
