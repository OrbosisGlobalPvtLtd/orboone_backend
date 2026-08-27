@extends('layouts.panel')

@section('title', 'Laravel System Log Viewer')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1" style="color: var(--text);">
                <i class="fas fa-terminal me-2 text-primary"></i>Laravel Log Viewer
            </h3>
            <p class="text-muted small mb-0">Browse, filter, and analyze system log entries directly from your browser.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($selectedFileName)
                <a href="{{ route('log-viewer.download', ['file' => $selectedFileName]) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="fas fa-download me-1"></i> Download Log
                </a>
                
                <form action="{{ route('log-viewer.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to clear this log file?');">
                    @csrf
                    <input type="hidden" name="file" value="{{ $selectedFileName }}">
                    <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill">
                        <i class="fas fa-eraser me-1"></i> Clear Log
                    </button>
                </form>

                @if(count($files) > 1)
                <form action="{{ route('log-viewer.delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this log file permanently?');">
                    @csrf
                    <input type="hidden" name="file" value="{{ $selectedFileName }}">
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                        <i class="fas fa-trash me-1"></i> Delete File
                    </button>
                </form>
                @endif
            @endif

            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => $currentLevel, 'search' => $currentSearch]) }}" class="btn btn-primary btn-sm rounded-pill">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('fail'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('fail') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Controls Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('log-viewer.index') }}" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="level" value="{{ $currentLevel }}">

                <!-- Select File -->
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Select Log File</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-file-alt text-muted"></i></span>
                        <select name="file" class="form-select form-select-sm border-start-0" onchange="this.form.submit()">
                            @forelse($files as $f)
                                <option value="{{ $f['name'] }}" {{ $f['name'] === $selectedFileName ? 'selected' : '' }}>
                                    {{ $f['name'] }} ({{ $f['size_formatted'] }} - {{ $f['updated_at'] }})
                                </option>
                            @empty
                                <option value="">No log files found</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1">Search Keywords</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Search error messages, exceptions, paths..." value="{{ $currentSearch }}">
                        @if($currentSearch)
                            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => $currentLevel]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3">Search</button>
                    </div>
                </div>

                <!-- Log File Info -->
                <div class="col-md-2 text-md-end pt-md-3">
                    @if($selectedFile)
                        <span class="badge bg-light text-dark border me-1">{{ $selectedFile['size_formatted'] }}</span>
                        <span class="badge bg-secondary">{{ count($logs) }} Entries</span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'all', 'search' => $currentSearch]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 text-center p-2 {{ $currentLevel === 'all' ? 'bg-primary text-white' : 'bg-white' }}">
                    <div class="small text-uppercase fw-bold {{ $currentLevel === 'all' ? 'text-white-50' : 'text-muted' }}">Total</div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'error', 'search' => $currentSearch]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 text-center p-2 {{ $currentLevel === 'error' ? 'bg-danger text-white' : 'bg-white' }}">
                    <div class="small text-uppercase fw-bold {{ $currentLevel === 'error' ? 'text-white-50' : 'text-danger' }}">Errors</div>
                    <div class="fs-4 fw-bold {{ $currentLevel === 'error' ? 'text-white' : 'text-danger' }}">{{ $stats['error'] + $stats['critical'] + $stats['emergency'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'warning', 'search' => $currentSearch]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 text-center p-2 {{ $currentLevel === 'warning' ? 'bg-warning text-dark' : 'bg-white' }}">
                    <div class="small text-uppercase fw-bold {{ $currentLevel === 'warning' ? 'text-dark-50' : 'text-warning' }}">Warnings</div>
                    <div class="fs-4 fw-bold {{ $currentLevel === 'warning' ? 'text-dark' : 'text-warning' }}">{{ $stats['warning'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'info', 'search' => $currentSearch]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 text-center p-2 {{ $currentLevel === 'info' ? 'bg-info text-white' : 'bg-white' }}">
                    <div class="small text-uppercase fw-bold {{ $currentLevel === 'info' ? 'text-white-50' : 'text-info' }}">Info</div>
                    <div class="fs-4 fw-bold {{ $currentLevel === 'info' ? 'text-white' : 'text-info' }}">{{ $stats['info'] + $stats['notice'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'debug', 'search' => $currentSearch]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 text-center p-2 {{ $currentLevel === 'debug' ? 'bg-secondary text-white' : 'bg-white' }}">
                    <div class="small text-uppercase fw-bold {{ $currentLevel === 'debug' ? 'text-white-50' : 'text-secondary' }}">Debug</div>
                    <div class="fs-4 fw-bold {{ $currentLevel === 'debug' ? 'text-white' : 'text-secondary' }}">{{ $stats['debug'] }}</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Log Entries Feed -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-list-ul me-2 text-secondary"></i>Log Stream
                @if($currentLevel !== 'all')
                    <span class="badge bg-primary text-capitalize ms-2">{{ $currentLevel }}</span>
                @endif
            </h6>
            <small class="text-muted">Showing {{ count($logs) }} log entries</small>
        </div>

        <div class="card-body p-0">
            @forelse($logs as $index => $log)
                @php
                    $levelClasses = [
                        'EMERGENCY' => 'bg-danger text-white',
                        'ALERT'     => 'bg-danger text-white',
                        'CRITICAL'  => 'bg-danger text-white',
                        'ERROR'     => 'bg-danger text-white',
                        'WARNING'   => 'bg-warning text-dark',
                        'NOTICE'    => 'bg-info text-white',
                        'INFO'      => 'bg-info text-white',
                        'DEBUG'     => 'bg-secondary text-white',
                    ];
                    $badgeClass = $levelClasses[$log['level']] ?? 'bg-dark text-white';
                @endphp

                <div class="p-3 border-bottom log-item hover-bg-light" id="log-{{ $log['id'] }}">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $badgeClass }} px-2 py-1 font-monospace fw-bold" style="font-size: 0.75rem;">
                                {{ $log['level'] }}
                            </span>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.75rem;">
                                {{ $log['env'] }}
                            </span>
                            <span class="text-muted font-monospace small">
                                <i class="far fa-clock me-1"></i>{{ $log['timestamp'] }}
                            </span>
                        </div>

                        <div>
                            @if(!empty($log['stacktrace']) || !empty($log['context']))
                                <button class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2 font-monospace" style="font-size: 0.75rem;" onclick="toggleDetails('details-{{ $log['id'] }}')">
                                    <i class="fas fa-code me-1"></i> Stack Trace / Context
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Header / Main Message -->
                    <div class="fw-bold font-monospace text-dark mb-1 text-break" style="font-size: 0.9rem;">
                        {{ $log['header'] }}
                    </div>

                    <!-- Context & Stack Trace Collapsible -->
                    @if(!empty($log['stacktrace']) || !empty($log['context']))
                        <div id="details-{{ $log['id'] }}" class="mt-3 display-details" style="display: none;">
                            @if(!empty($log['context']))
                                <div class="mb-2">
                                    <div class="small fw-bold text-muted mb-1"><i class="fas fa-info-circle me-1"></i>Context Details:</div>
                                    <pre class="bg-dark text-light p-3 rounded-3 font-monospace small text-wrap border border-secondary" style="max-height: 250px; overflow-y: auto;">{{ $log['context'] }}</pre>
                                </div>
                            @endif

                            @if(!empty($log['stacktrace']))
                                <div>
                                    <div class="small fw-bold text-muted mb-1"><i class="fas fa-layer-group me-1"></i>Exception Stack Trace:</div>
                                    <pre class="bg-dark text-success p-3 rounded-3 font-monospace small text-wrap border border-secondary" style="max-height: 400px; overflow-y: auto;">{{ $log['stacktrace'] }}</pre>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-check fa-3x text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-muted">No log records found</h5>
                    <p class="text-muted small mb-0">Try clearing your search query or selecting another log file / level filter.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.015);
    }
</style>

<script>
    function toggleDetails(id) {
        const el = document.getElementById(id);
        if (el) {
            if (el.style.display === 'none' || el.style.display === '') {
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
            }
        }
    }
</script>
@endsection
