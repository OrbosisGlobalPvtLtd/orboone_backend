@extends('layouts.panel', ['active' => 'settings'])

@section('title', 'System Audit Logs')

@section('_content')
<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Header / Hero Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #faf8ff 100%); border: 1px solid rgba(75, 0, 232, 0.12) !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #4B00E8 0%, #7C3AED 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 8px 18px rgba(75, 0, 232, 0.28); flex-shrink: 0;">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="color: #1e293b; letter-spacing: -0.02em;">System Audit Logs</h3>
                        <p class="text-muted small mb-0 mt-1">Track system user activities, security changes, and model transactions.</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('logs.print') }}" class="btn btn-outline-secondary rounded-pill px-3 fw-bold btn-sm" target="_blank">
                        <i class="fas fa-print me-1"></i> Print Audit Trail
                    </a>
                    <a href="{{ route('log-viewer.index') }}" class="btn text-white rounded-pill px-3 fw-bold btn-sm shadow-sm" style="background: linear-gradient(135deg, #4B00E8 0%, #6D28D9 100%);">
                        <i class="fas fa-terminal me-1"></i> Open Laravel Log Viewer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Logs Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border: 1px solid #edf2f7 !important;">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-list-check me-2 text-primary"></i>Activity Records
            </h6>
            <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">
                Total: {{ $logs->total() }} entries
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b;">
                        <tr>
                            <th scope="col" class="px-4 py-3" style="width: 70px;">#</th>
                            <th scope="col" class="py-3">Activity Description</th>
                            <th scope="col" class="py-3 px-4 text-end" style="width: 220px;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13.5px;">
                        @forelse ($logs as $index => $log)
                        <tr>
                            <td class="px-4 text-muted fw-bold">{{ $logs->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2 py-1">
                                    <span class="badge bg-purple-soft text-purple rounded-circle p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-shield-alt" style="font-size: 11px;"></i>
                                    </span>
                                    <span class="fw-semibold text-dark text-break">{{ $log->description }}</span>
                                </div>
                            </td>
                            <td class="px-4 text-end font-monospace text-muted small">
                                <i class="far fa-clock me-1 text-primary opacity-75"></i>
                                {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
                                    <h6 class="fw-bold">No Audit Log Records Found</h6>
                                    <p class="small mb-0">No system activity events have been recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
        <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results</span>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .bg-purple-soft {
        background: #F4EEFF;
    }
    .text-purple {
        color: #5B21B6;
    }
</style>
@endsection