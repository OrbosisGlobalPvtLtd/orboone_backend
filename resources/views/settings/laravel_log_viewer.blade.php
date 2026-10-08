@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'Laravel Log Viewer')

@section('_head')
@include('settings.partials.styles')
<style>
    /* Custom Log Viewer Additions on top of Settings Theme */
    .log-stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .log-stat-card {
        background: #fff;
        border: 1px solid var(--set-border);
        border-radius: 18px;
        padding: 16px 18px;
        box-shadow: 0 4px 14px rgba(16, 24, 40, 0.04);
        transition: all 0.22s ease;
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .log-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(75, 0, 232, 0.08);
        border-color: rgba(75, 0, 232, 0.3);
    }

    .log-stat-card.active {
        background: linear-gradient(135deg, var(--set-primary), var(--set-secondary)) !important;
        border-color: var(--set-primary) !important;
        box-shadow: 0 8px 22px rgba(75, 0, 232, 0.28) !important;
    }

    .log-stat-card.active * {
        color: #ffffff !important;
    }

    .log-stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .log-stat-label {
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--set-muted);
    }

    .log-stat-icon {
        font-size: 14px;
        opacity: 0.8;
    }

    .log-stat-num {
        font-size: 26px;
        font-weight: 900;
        line-height: 1.1;
        color: var(--set-text);
        margin-bottom: 2px;
    }

    .log-stat-desc {
        font-size: 11px;
        font-weight: 600;
        color: var(--set-muted);
    }

    /* Log Stream Row */
    .log-entry-card {
        background: #ffffff;
        border: 1px solid var(--set-border);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 14px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(16, 24, 40, 0.03);
    }

    .log-entry-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 18px rgba(16, 24, 40, 0.06);
    }

    .log-entry-card.border-error {
        border-left: 5px solid #ef4444;
    }
    .log-entry-card.border-warning {
        border-left: 5px solid #f59e0b;
    }
    .log-entry-card.border-info {
        border-left: 5px solid #0284c7;
    }
    .log-entry-card.border-debug {
        border-left: 5px solid #64748b;
    }

    /* Severity Badges */
    .sev-badge {
        font-size: 11px;
        font-weight: 850;
        padding: 4px 10px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .sev-error { background: #fee2e2; color: #dc2626; }
    .sev-warning { background: #fef3c7; color: #d97706; }
    .sev-info { background: #e0f2fe; color: #0284c7; }
    .sev-debug { background: #f1f5f9; color: #475569; }

    .env-tag {
        font-size: 11px;
        font-weight: 700;
        background: #f8fafc;
        border: 1px solid var(--set-border);
        color: #475569;
        padding: 3px 8px;
        border-radius: 6px;
    }

    .time-tag {
        font-size: 12px;
        font-weight: 600;
        color: var(--set-muted);
    }

    .log-msg-text {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        padding: 10px 14px;
        border-radius: 10px;
        margin: 10px 0 0;
        word-break: break-word;
        white-space: pre-wrap;
        line-height: 1.5;
    }

    /* Terminal Window Drawer */
    .terminal-box {
        background: #0f172a;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #1e293b;
        margin-top: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    }

    .terminal-topbar {
        background: #1e293b;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #334155;
    }

    .terminal-dots {
        display: flex;
        gap: 6px;
    }
    .t-dot { width: 10px; height: 10px; border-radius: 50%; }
    .t-dot-red { background: #ef4444; }
    .t-dot-yellow { background: #f59e0b; }
    .t-dot-green { background: #10b981; }

    .terminal-content {
        padding: 16px 20px;
        max-height: 440px;
        overflow-y: auto;
    }

    .terminal-content::-webkit-scrollbar { width: 6px; }
    .terminal-content::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

    .terminal-code {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        font-size: 12px;
        line-height: 1.55;
        white-space: pre-wrap;
        word-break: break-all;
        margin: 0;
    }

    @media (max-width: 992px) {
        .log-stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 576px) {
        .log-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endsection

@section('_content')
<div class="set-page">
    <div class="set-container" style="max-width: 1280px;">

        <!-- OrboOne Theme Hero Header -->
        <div class="set-header">
            <div>
                <div class="set-kicker">
                    <i class="fas fa-terminal"></i> HRMS &bull; SYSTEM LOGS
                </div>
                <h1 class="set-title">Laravel Server Log Viewer</h1>
                <p class="set-subtitle">Browse, filter, analyze exceptions, and manage server application logs in real-time.</p>
            </div>

            <div class="set-glass-badge">
                <div style="font-size: 22px; font-weight: 900; line-height: 1;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div style="font-size: 10px; font-weight: 850; text-transform: uppercase; letter-spacing: 1px; margin-top: 6px; opacity: 0.9;">
                    Live Diagnostics
                </div>
            </div>
        </div>

        <!-- Notification Alerts -->
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <div><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="background: none; border: none; font-size: 18px; line-height: 1;">&times;</button>
            </div>
        @endif
        @if(session('fail'))
            <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between" style="border-radius: 16px; font-weight: 800; font-size: 13px;">
                <div><i class="fas fa-exclamation-circle mr-2"></i> {{ session('fail') }}</div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="background: none; border: none; font-size: 18px; line-height: 1;">&times;</button>
            </div>
        @endif

        <!-- Top Action & Filter Bar Card -->
        <div class="set-card mb-4">
            <div class="set-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-filter"></i></div>
                    <div>
                        <h5 class="set-card-title">Log Controls & Search</h5>
                        <p class="set-card-subtitle">Select log file, search exception messages, or manage log files.</p>
                    </div>
                </div>

                <!-- Action Toolbar -->
                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                    @if($selectedFileName)
                        <a href="{{ route('log-viewer.download', ['file' => $selectedFileName]) }}" class="set-btn set-btn-soft" title="Download log file">
                            <i class="fas fa-download"></i> Download
                        </a>
                        
                        <form action="{{ route('log-viewer.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all contents of this log file?');">
                            @csrf
                            <input type="hidden" name="file" value="{{ $selectedFileName }}">
                            <button type="submit" class="set-btn set-btn-soft" style="color: #d97706 !important;" title="Clear log entries">
                                <i class="fas fa-eraser"></i> Clear Log
                            </button>
                        </form>

                        @if(count($files) > 1)
                        <form action="{{ route('log-viewer.delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this log file?');">
                            @csrf
                            <input type="hidden" name="file" value="{{ $selectedFileName }}">
                            <button type="submit" class="set-btn set-btn-danger" title="Delete file">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                        @endif
                    @endif

                    <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => $currentLevel, 'search' => $currentSearch]) }}" class="set-btn" title="Refresh">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </a>
                </div>
            </div>

            <div class="set-card-body">
                <form action="{{ route('log-viewer.index') }}" method="GET" class="row align-items-end">
                    <input type="hidden" name="level" value="{{ $currentLevel }}">

                    <!-- File Selector -->
                    <div class="col-lg-5 col-md-6 mb-3 mb-lg-0">
                        <label class="set-label"><i class="fas fa-file-alt mr-1"></i> Selected Log File</label>
                        <select name="file" class="set-control select2-searchable" onchange="this.form.submit()">
                            @forelse($files as $f)
                                <option value="{{ $f['name'] }}" {{ $f['name'] === $selectedFileName ? 'selected' : '' }}>
                                    {{ $f['name'] }} ({{ $f['size_formatted'] }} &bull; {{ $f['updated_at'] }})
                                </option>
                            @empty
                                <option value="">No log files found in storage/logs</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="col-lg-5 col-md-6 mb-3 mb-lg-0">
                        <label class="set-label"><i class="fas fa-search mr-1"></i> Search Keywords / Exceptions</label>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <input type="text" name="search" class="set-control" placeholder="Search error messages, routes, file paths..." value="{{ $currentSearch }}">
                            @if($currentSearch)
                                <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => $currentLevel]) }}" class="set-btn set-btn-soft" style="padding: 8px 12px;" title="Clear search">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                            <button type="submit" class="set-btn" style="min-width: 90px; justify-content: center;">Search</button>
                        </div>
                    </div>

                    <!-- File Info Badge -->
                    <div class="col-lg-2 col-md-12 text-lg-right text-md-left">
                        @if($selectedFile)
                            <div class="d-inline-flex flex-column align-items-lg-end align-items-start">
                                <span class="set-badge mb-1"><i class="fas fa-database mr-1"></i> {{ $selectedFile['size_formatted'] }}</span>
                                <small class="text-muted font-weight-bold">{{ count($logs) }} Entries</small>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Severity Metric Cards Grid -->
        @php
            $errorTotal = $stats['error'] + $stats['critical'] + $stats['emergency'] + $stats['alert'];
            $infoTotal = $stats['info'] + $stats['notice'];
        @endphp

        <div class="log-stats-grid">
            <!-- ALL -->
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'all', 'search' => $currentSearch]) }}" class="log-stat-card {{ $currentLevel === 'all' ? 'active' : '' }}">
                <div class="log-stat-header">
                    <span class="log-stat-label">TOTAL</span>
                    <i class="fas fa-layer-group log-stat-icon text-primary"></i>
                </div>
                <div class="log-stat-num">{{ $stats['total'] }}</div>
                <div class="log-stat-desc">All entries</div>
            </a>

            <!-- ERRORS -->
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'error', 'search' => $currentSearch]) }}" class="log-stat-card {{ $currentLevel === 'error' ? 'active' : '' }}">
                <div class="log-stat-header">
                    <span class="log-stat-label" style="color: #ef4444;">ERRORS</span>
                    <i class="fas fa-exclamation-circle log-stat-icon text-danger"></i>
                </div>
                <div class="log-stat-num text-danger">{{ $errorTotal }}</div>
                <div class="log-stat-desc">Critical & errors</div>
            </a>

            <!-- WARNINGS -->
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'warning', 'search' => $currentSearch]) }}" class="log-stat-card {{ $currentLevel === 'warning' ? 'active' : '' }}">
                <div class="log-stat-header">
                    <span class="log-stat-label" style="color: #f59e0b;">WARNINGS</span>
                    <i class="fas fa-exclamation-triangle log-stat-icon text-warning"></i>
                </div>
                <div class="log-stat-num text-warning">{{ $stats['warning'] }}</div>
                <div class="log-stat-desc">Warnings</div>
            </a>

            <!-- INFO -->
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'info', 'search' => $currentSearch]) }}" class="log-stat-card {{ $currentLevel === 'info' ? 'active' : '' }}">
                <div class="log-stat-header">
                    <span class="log-stat-label" style="color: #0284c7;">INFO</span>
                    <i class="fas fa-info-circle log-stat-icon text-info"></i>
                </div>
                <div class="log-stat-num text-info">{{ $infoTotal }}</div>
                <div class="log-stat-desc">Info & notices</div>
            </a>

            <!-- DEBUG -->
            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName, 'level' => 'debug', 'search' => $currentSearch]) }}" class="log-stat-card {{ $currentLevel === 'debug' ? 'active' : '' }}">
                <div class="log-stat-header">
                    <span class="log-stat-label">DEBUG</span>
                    <i class="fas fa-bug log-stat-icon text-secondary"></i>
                </div>
                <div class="log-stat-num text-secondary">{{ $stats['debug'] }}</div>
                <div class="log-stat-desc">Debug dumps</div>
            </a>

            <!-- SYSTEM STATUS -->
            <div class="log-stat-card">
                <div class="log-stat-header">
                    <span class="log-stat-label">STATUS</span>
                    <i class="fas fa-heartbeat log-stat-icon text-success"></i>
                </div>
                <div class="log-stat-num" style="font-size: 18px; margin-top: 4px;">
                    @if($errorTotal === 0)
                        <span class="text-success"><i class="fas fa-check-circle mr-1"></i> Clean</span>
                    @else
                        <span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i> {{ $errorTotal }} Issues</span>
                    @endif
                </div>
                <div class="log-stat-desc">Parsed file</div>
            </div>
        </div>

        <!-- Log Stream Section -->
        <div class="set-card">
            <div class="set-card-header d-flex flex-wrap justify-content-between align-items-center">
                <div class="set-head-left">
                    <div class="set-icon-box"><i class="fas fa-stream"></i></div>
                    <div>
                        <h5 class="set-card-title">
                            Log Records Feed
                            @if($currentLevel !== 'all')
                                <span class="set-badge ml-2" style="text-transform: uppercase;">{{ $currentLevel }}</span>
                            @endif
                        </h5>
                        <p class="set-card-subtitle">Showing {{ count($logs) }} log events from current selection.</p>
                    </div>
                </div>

                <div>
                    <button type="button" class="set-btn set-btn-soft" style="font-size: 12px;" onclick="toggleAllDrawers()">
                        <i class="fas fa-expand-alt mr-1"></i> Toggle All Traces
                    </button>
                </div>
            </div>

            <div class="set-card-body p-3 p-md-4">
                @forelse($logs as $index => $log)
                    @php
                        $level = strtoupper($log['level']);
                        $borderClass = match($level) {
                            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'border-error',
                            'WARNING' => 'border-warning',
                            'NOTICE', 'INFO' => 'border-info',
                            'DEBUG' => 'border-debug',
                            default => 'border-error'
                        };
                        $badgeClass = match($level) {
                            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'sev-error',
                            'WARNING' => 'sev-warning',
                            'NOTICE', 'INFO' => 'sev-info',
                            'DEBUG' => 'sev-debug',
                            default => 'sev-error'
                        };
                        $iconClass = match($level) {
                            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'fa-times-circle',
                            'WARNING' => 'fa-exclamation-triangle',
                            'NOTICE', 'INFO' => 'fa-info-circle',
                            'DEBUG' => 'fa-bug',
                            default => 'fa-circle'
                        };
                        $hasDetails = !empty($log['stacktrace']) || !empty($log['context']);
                    @endphp

                    <div class="log-entry-card {{ $borderClass }}" id="log-{{ $log['id'] }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                            <!-- Left: Meta Pills -->
                            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                <span class="sev-badge {{ $badgeClass }}">
                                    <i class="fas {{ $iconClass }}"></i> {{ $level }}
                                </span>

                                <span class="env-tag">
                                    <i class="fas fa-server mr-1 opacity-50"></i>{{ $log['env'] }}
                                </span>

                                <span class="time-tag">
                                    <i class="far fa-clock mr-1"></i>{{ $log['timestamp'] }}
                                </span>
                            </div>

                            <!-- Right: Actions -->
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <button type="button" class="set-btn set-btn-soft" style="padding: 4px 10px; font-size: 11.5px;" onclick="copyMessage('{{ addslashes(str_replace(["\r", "\n"], ' ', $log['header'])) }}', this)" title="Copy message">
                                    <i class="far fa-copy"></i>
                                </button>

                                @if($hasDetails)
                                    <button type="button" class="set-btn set-btn-soft trace-btn" style="padding: 4px 12px; font-size: 11.5px;" onclick="toggleLogDrawer('drawer-{{ $log['id'] }}', this)">
                                        <i class="fas fa-code mr-1"></i> Stack Trace & Context
                                        <i class="fas fa-chevron-down ml-1 caret-ico"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Main Message -->
                        <div class="log-msg-text">{{ $log['header'] }}</div>

                        <!-- Stack Trace Drawer -->
                        @if($hasDetails)
                            <div id="drawer-{{ $log['id'] }}" class="log-drawer" style="display: none;">
                                <div class="terminal-box">
                                    <div class="terminal-topbar">
                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                            <div class="terminal-dots">
                                                <span class="t-dot t-dot-red"></span>
                                                <span class="t-dot t-dot-yellow"></span>
                                                <span class="t-dot t-dot-green"></span>
                                            </div>
                                            <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">exception_details.log</span>
                                        </div>
                                        <button type="button" class="set-btn set-btn-soft" style="padding: 2px 8px; font-size: 11px; background: rgba(255,255,255,0.1) !important; color: #fff !important; border-color: rgba(255,255,255,0.2) !important;" onclick="copyContainerText('trace-content-{{ $log['id'] }}', this)">
                                            <i class="far fa-copy mr-1"></i> Copy Full Trace
                                        </button>
                                    </div>

                                    <div class="terminal-content" id="trace-content-{{ $log['id'] }}">
                                        @if(!empty($log['context']))
                                            <div class="mb-3">
                                                <div style="font-size: 11px; font-weight: 700; color: #38bdf8; margin-bottom: 4px; font-family: monospace;">
                                                    <i class="fas fa-info-circle mr-1"></i> CONTEXT DETAILS:
                                                </div>
                                                <pre class="terminal-code" style="color: #38bdf8;">{{ $log['context'] }}</pre>
                                            </div>
                                        @endif

                                        @if(!empty($log['stacktrace']))
                                            <div>
                                                <div style="font-size: 11px; font-weight: 700; color: #4ade80; margin-bottom: 4px; font-family: monospace;">
                                                    <i class="fas fa-layer-group mr-1"></i> STACK TRACE:
                                                </div>
                                                <pre class="terminal-code" style="color: #4ade80;">{{ $log['stacktrace'] }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--set-soft); color: var(--set-primary); display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 14px;">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <h5 class="font-weight-bold" style="color: var(--set-text);">No Log Records Found</h5>
                        <p class="text-muted small mb-3">No log entries matched your filter parameters or selected log file is clean.</p>
                        @if($currentLevel !== 'all' || $currentSearch)
                            <a href="{{ route('log-viewer.index', ['file' => $selectedFileName]) }}" class="set-btn">
                                <i class="fas fa-filter mr-1"></i> Reset Filters
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<script>
    function toggleLogDrawer(id, btn) {
        const el = document.getElementById(id);
        if (!el) return;

        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
            if (btn) {
                const caret = btn.querySelector('.caret-ico');
                if (caret) caret.style.transform = 'rotate(180deg)';
            }
        } else {
            el.style.display = 'none';
            if (btn) {
                const caret = btn.querySelector('.caret-ico');
                if (caret) caret.style.transform = 'rotate(0deg)';
            }
        }
    }

    function toggleAllDrawers() {
        const drawers = document.querySelectorAll('.log-drawer');
        const buttons = document.querySelectorAll('.trace-btn');
        let anyOpen = false;

        drawers.forEach(d => {
            if (d.style.display === 'block') anyOpen = true;
        });

        drawers.forEach(d => {
            d.style.display = anyOpen ? 'none' : 'block';
        });

        buttons.forEach(b => {
            const caret = b.querySelector('.caret-ico');
            if (caret) {
                caret.style.transform = anyOpen ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    }

    function copyMessage(text, btn) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = orig;
            }, 1500);
        });
    }

    function copyContainerText(id, btn) {
        const el = document.getElementById(id);
        if (!el) return;
        const text = el.innerText || el.textContent;
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success mr-1"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = orig;
            }, 1500);
        });
    }
</script>
@endsection
