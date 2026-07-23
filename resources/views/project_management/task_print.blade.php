@extends('layouts.print')

@section('_content')
<style>
    body {
        font-family: 'Outfit', 'Helvetica Neue', Arial, sans-serif;
        color: #1e293b;
        background: #fff;
    }
    .report-header {
        border-bottom: 3px solid #4f46e5;
        padding-bottom: 12px;
        margin-bottom: 30px;
    }
    .report-title {
        font-size: 26px;
        font-weight: 800;
        color: #4f46e5;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .meta-table th {
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        padding: 4px 8px;
    }
    .meta-table td {
        font-size: 13px;
        font-weight: 600;
        padding: 4px 8px;
    }
    .task-block {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        page-break-inside: avoid;
        background: #f8fafc;
    }
    .task-id {
        font-size: 12px;
        font-weight: 800;
        color: #94a3b8;
    }
    .task-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 4px;
    }
    .progress-bar-container {
        background: #e2e8f0;
        border-radius: 10px;
        height: 10px;
        width: 100%;
        overflow: hidden;
        margin: 8px 0;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 10px;
        background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
    }
    .section-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
        margin-top: 15px;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 4px;
        margin-bottom: 8px;
    }
    .comment-item {
        border-left: 3px solid #7c3aed;
        background: #fff;
        padding: 8px 12px;
        margin-bottom: 6px;
        border-radius: 0 6px 6px 0;
        font-size: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .timeline-item {
        border-left: 2px solid #e2e8f0;
        padding-left: 12px;
        position: relative;
        font-size: 12px;
        padding-bottom: 8px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -5px;
        top: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4f46e5;
    }
    .badge-pending { background: #ffeded; color: #c2410c; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
    .badge-in-progress { background: #edf5ff; color: #1d4ed8; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
    .badge-on-hold { background: #fef8e7; color: #d97706; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
    .badge-completed { background: #edfdf5; color: #047857; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
    .badge-verified { background: #f0fdf4; color: #15803d; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; border: 1px solid #bbf7d0; }
    .badge-closed { background: #f1f5f9; color: #475467; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
</style>

<div class="container-fluid mt-2 p-2">
    <!-- Header -->
    <div class="report-header">
        <div class="row align-items-center">
            <div class="col-8">
                <div class="report-title">Task Management Progress Report</div>
                <p class="text-muted mb-0 small">Generated on {{ now()->format('d M Y \a\t h:i A') }}</p>
            </div>
            <div class="col-4 text-right">
                <table class="meta-table ml-auto">
                    <tr>
                        <th>Total Scope</th>
                        <td>{{ count($task) }} Tasks</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Overview Table -->
    <div class="card p-3 mb-4 border" style="page-break-inside: avoid;">
        <h5 class="font-weight-bold mb-3" style="color: #0f172a; font-size: 16px;">Tasks Summary Grid</h5>
        <table class="table table-striped table-bordered text-center mb-0" style="font-size: 13px;">
            <thead class="thead-dark">
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th class="text-left">Task Title</th>
                    <th>Assignee</th>
                    <th>Due Date</th>
                    <th>Completion %</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($task as $t)
                    @php
                        $progress = 10;
                        $statusBadgeClass = 'badge-pending';
                        switch(strtolower($t->status)) {
                            case 'pending': $progress = 10; $statusBadgeClass = 'badge-pending'; break;
                            case 'on_hold': $progress = 30; $statusBadgeClass = 'badge-on-hold'; break;
                            case 'in_progress':
                            case 'progress': $progress = 50; $statusBadgeClass = 'badge-in-progress'; break;
                            case 'completed': $progress = 80; $statusBadgeClass = 'badge-completed'; break;
                            case 'verified': $progress = 95; $statusBadgeClass = 'badge-verified'; break;
                            case 'closed': $progress = 100; $statusBadgeClass = 'badge-closed'; break;
                        }
                    @endphp
                    <tr>
                        <td class="font-weight-bold">#{{ $t->id }}</td>
                        <td class="text-left font-weight-bold text-dark">{{ $t->title }}</td>
                        <td>{{ $t->user->name ?? $t->employee_name ?? 'N/A' }}</td>
                        <td>{{ $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('M d, Y') : '-' }}</td>
                        <td>
                            <div class="font-weight-bold text-primary">{{ $progress }}%</div>
                        </td>
                        <td>
                            <span class="{{ $statusBadgeClass }}">{{ strtoupper(str_replace('_', ' ', $t->status)) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No tasks matching current filter scope.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Detailed Tasks breakdown -->
    @if(count($task) > 0)
        <div style="page-break-before: always;"></div>
        <h5 class="font-weight-bold mb-4" style="color: #0f172a; font-size: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Detailed Tasks Breakdown & Progress</h5>

        @foreach($task as $t)
            @php
                $progress = 10;
                $statusBadgeClass = 'badge-pending';
                switch(strtolower($t->status)) {
                    case 'pending': $progress = 10; $statusBadgeClass = 'badge-pending'; break;
                    case 'on_hold': $progress = 30; $statusBadgeClass = 'badge-on-hold'; break;
                    case 'in_progress':
                    case 'progress': $progress = 50; $statusBadgeClass = 'badge-in-progress'; break;
                    case 'completed': $progress = 80; $statusBadgeClass = 'badge-completed'; break;
                    case 'verified': $progress = 95; $statusBadgeClass = 'badge-verified'; break;
                    case 'closed': $progress = 100; $statusBadgeClass = 'badge-closed'; break;
                }
                $updates = $t->updates_data;
                $comments = $updates['comments'] ?? [];
                $timeline = $updates['timeline'] ?? [];
            @endphp
            <div class="task-block">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="task-id">TASK #{{ $t->id }}</span>
                        <div class="task-title">{{ $t->title }}</div>
                    </div>
                    <span class="{{ $statusBadgeClass }}">{{ strtoupper(str_replace('_', ' ', $t->status)) }}</span>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <small class="text-muted font-weight-bold uppercase d-block" style="font-size: 10px;">Assignee</small>
                        <strong class="text-dark small">{{ $t->user->name ?? $t->employee_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted font-weight-bold uppercase d-block" style="font-size: 10px;">Due Date</small>
                        <strong class="text-dark small">{{ $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('M d, Y') : '-' }}</strong>
                    </div>
                    <div class="col-md-4 text-right">
                        <small class="text-muted font-weight-bold uppercase d-block" style="font-size: 10px;">Task Progress</small>
                        <strong class="text-primary" style="font-size: 16px;">{{ $progress }}% Completed</strong>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: {{ $progress }}%;"></div>
                </div>

                <!-- Description -->
                <div class="section-title">Description</div>
                <div class="small text-secondary mb-3" style="white-space: pre-line; line-height: 1.4;">
                    {{ strip_tags($t->clean_description) }}
                </div>

                <!-- Comments Thread -->
                @if(count($comments) > 0)
                    <div class="section-title">Comments & Updates</div>
                    <div class="mb-3">
                        @foreach($comments as $c)
                            <div class="comment-item">
                                <div class="d-flex justify-content-between font-weight-bold text-dark extra-small mb-1" style="font-size: 10px;">
                                    <span>{{ $c['user_name'] }} ({{ $c['role'] }})</span>
                                    <span class="text-muted">{{ $c['created_at'] }}</span>
                                </div>
                                <div class="text-secondary">{{ $c['comment'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Activity Timeline -->
                @if(count($timeline) > 0)
                    <div class="section-title">Timeline & History logs</div>
                    <div>
                        @foreach(array_slice(array_reverse($timeline), 0, 5) as $tl)
                            <div class="timeline-item">
                                <span class="font-weight-bold text-dark">{{ $tl['event'] }}</span>
                                <span class="text-muted" style="font-size: 10px;"> - {{ $tl['user_name'] }} • {{ $tl['timestamp'] }}</span>
                                <div class="text-secondary mt-1" style="font-size: 11px;">{{ $tl['details'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('_script')
    <script>
        window.onload = function () {
            window.print();
        }
    </script>
@endsection