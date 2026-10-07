<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Report Dossier - {{ $summary['employee_name'] }} ({{ $summary['employee_code'] }})</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --brand-primary: {{ branding_primary_color() }};
            --brand-secondary: {{ branding_secondary_color() }};
            --brand-dark: #0F172A;
            --brand-slate: #334155;
            --brand-muted: #64748B;
            --brand-light: #F8FAFC;
            --brand-border: #E2E8F0;
            --brand-border-light: #F1F5F9;
            --success-bg: #ECFDF5;
            --success-border: #A7F3D0;
            --success-text: #065F46;
            --info-bg: #EFF6FF;
            --info-border: #BFDBFE;
            --info-text: #1E40AF;
            --warning-bg: #FFFBEB;
            --warning-border: #FDE68A;
            --warning-text: #92400E;
            --danger-bg: #FEF2F2;
            --danger-border: #FECACA;
            --danger-text: #991B1B;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 portrait;
            margin: 12mm 12mm 14mm 12mm;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--brand-dark);
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* SCREEN FLOATING ACTION BAR */
        .screen-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: #ffffff;
        }

        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .toolbar-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #E2E8F0;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-tb {
            height: 38px;
            padding: 0 18px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-tb-primary {
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(75, 0, 232, 0.35);
        }

        .btn-tb-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            color: #ffffff;
            text-decoration: none;
        }

        .btn-tb-secondary {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-tb-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            text-decoration: none;
        }

        /* DOCUMENT CONTAINER */
        .document-wrapper {
            max-width: 960px;
            margin: 24px auto 48px;
            background: #ffffff;
            padding: 36px 40px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.08);
            border: 1px solid var(--brand-border);
        }

        /* EXECUTIVE HEADER */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--brand-border);
            position: relative;
        }

        .doc-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 140px;
            height: 2px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-logo-img {
            max-height: 52px;
            max-width: 170px;
            object-fit: contain;
        }

        .brand-fallback-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: #ffffff;
            font-size: 22px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 900;
            color: var(--brand-dark);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: var(--brand-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 3px;
        }

        .doc-meta {
            text-align: right;
        }

        .doc-badge-pill {
            display: inline-block;
            background: #F1F5F9;
            border: 1px solid var(--brand-border);
            color: var(--brand-slate);
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 6px;
        }

        .doc-title-main {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 900;
            color: var(--brand-primary);
            letter-spacing: -0.01em;
            margin: 0;
            line-height: 1.2;
        }

        .doc-meta-row {
            font-size: 10.5px;
            color: var(--brand-muted);
            margin-top: 4px;
            font-weight: 600;
        }

        /* EMPLOYEE PROFILE CARD */
        .emp-dossier-card {
            background: #F8FAFC;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .emp-profile-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .emp-avatar-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: #ffffff;
            border: 2px solid #CBD5E1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 20px;
            font-weight: 900;
            color: var(--brand-primary);
            flex-shrink: 0;
        }

        .emp-avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .emp-name-title {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: var(--brand-dark);
            margin: 0;
            line-height: 1.2;
        }

        .emp-badges-line {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        .emp-code-badge {
            background: #EEF2FF;
            color: #4338CA;
            border: 1px solid #C7D2FE;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10.5px;
        }

        .emp-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(130px, 1fr));
            gap: 8px 16px;
            font-size: 11px;
            text-align: right;
        }

        .emp-detail-item .lbl {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-muted);
            letter-spacing: 0.04em;
        }

        .emp-detail-item .val {
            font-weight: 700;
            color: var(--brand-dark);
        }

        /* KPI METRICS GRID */
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .kpi-box {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 10px;
            padding: 12px 14px;
            position: relative;
            overflow: hidden;
        }

        .kpi-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 3px;
            background: var(--brand-primary);
        }

        .kpi-box.kpi-success::before { background: #10B981; }
        .kpi-box.kpi-warning::before { background: #F59E0B; }
        .kpi-box.kpi-info::before { background: #3B82F6; }

        .kpi-val {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: var(--brand-dark);
            line-height: 1.1;
        }

        .kpi-lbl {
            font-size: 10px;
            font-weight: 700;
            color: var(--brand-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 3px;
        }

        .kpi-sub {
            font-size: 9.5px;
            font-weight: 600;
            color: #64748B;
            margin-top: 2px;
        }

        /* SECTION HEADINGS */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid var(--brand-border);
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 13.5px;
            font-weight: 800;
            color: var(--brand-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .section-title i {
            color: var(--brand-primary);
            font-size: 14px;
        }

        .section-count {
            font-size: 11px;
            font-weight: 700;
            color: var(--brand-muted);
        }

        /* CHRONOLOGICAL REPORT ENTRIES */
        .report-entries-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 28px;
        }

        .report-day-card {
            background: #ffffff;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        }

        .report-day-header {
            background: #F8FAFC;
            border-bottom: 1px solid var(--brand-border);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .day-date-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .day-index-badge {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: var(--brand-primary);
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .day-date-text {
            font-family: 'Outfit', sans-serif;
            font-size: 13.5px;
            font-weight: 800;
            color: var(--brand-dark);
        }

        .day-meta-pills {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge-mode {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .badge-mode-wfo {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .badge-mode-wfh {
            background: #EFF6FF;
            color: #1E40AF;
            border: 1px solid #BFDBFE;
        }

        .badge-duration {
            background: #F1F5F9;
            color: var(--brand-slate);
            border: 1px solid var(--brand-border);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }

        .report-day-body {
            padding: 14px 16px;
        }

        .day-summary-quote {
            background: #F8FAFC;
            border-left: 3px solid var(--brand-primary);
            padding: 8px 12px;
            border-radius: 0 8px 8px 0;
            font-size: 11.5px;
            color: var(--brand-slate);
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.45;
        }

        /* PROJECTS & TASKS */
        .project-block {
            border: 1px solid var(--brand-border);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            background: #ffffff;
        }

        .project-block:last-child {
            margin-bottom: 0;
        }

        .project-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            font-weight: 800;
            color: var(--brand-dark);
            padding-bottom: 6px;
            margin-bottom: 6px;
            border-bottom: 1px dashed var(--brand-border);
        }

        .task-list-items {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .task-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 6px;
            background: #F8FAFC;
            border: 1px solid var(--brand-border-light);
        }

        .task-row.task-done {
            background: #F0FDF4;
            border-color: #DCFCE7;
        }

        .task-text {
            font-weight: 600;
            color: var(--brand-slate);
            flex-grow: 1;
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .task-row.task-done .task-text {
            color: #166534;
        }

        .task-status-tag {
            font-size: 8.5px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }

        .task-status-tag.tag-completed {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }

        .task-status-tag.tag-pending {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
        }

        /* BLOCKER & NOTES */
        .day-footer-strip {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid var(--brand-border-light);
            font-size: 10.5px;
        }

        .blocker-box {
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-grow: 1;
        }

        .blocker-box.clean {
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .blocker-box.alert {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* AUDIT & SIGN-OFF SECTION */
        .signoff-section {
            page-break-inside: avoid;
            break-inside: avoid;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            padding: 18px 20px;
            background: #F8FAFC;
            margin-top: 24px;
        }

        .signoff-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 14px;
        }

        .signoff-column {
            background: #ffffff;
            border: 1px dashed var(--brand-border);
            border-radius: 8px;
            padding: 14px 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
        }

        .signoff-title {
            font-size: 10px;
            font-weight: 800;
            color: var(--brand-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .signoff-name {
            font-weight: 700;
            font-size: 11.5px;
            color: var(--brand-dark);
            margin-top: 4px;
        }

        .signoff-line {
            border-top: 1px solid #CBD5E1;
            padding-top: 6px;
            font-size: 9.5px;
            color: #64748B;
            font-weight: 600;
        }

        /* DOCUMENT FOOTER */
        .doc-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid var(--brand-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 9.5px;
            color: var(--brand-muted);
            font-weight: 600;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #ffffff !important;
                font-size: 11px !important;
            }

            .no-print {
                display: none !important;
            }

            .document-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border-radius: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }

            .report-day-card {
                box-shadow: none !important;
                border: 1px solid #CBD5E1 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Non-printable Interactive Screen Toolbar -->
    <div class="screen-toolbar no-print">
        <div class="toolbar-info">
            <i class="fas fa-file-invoice text-primary"></i>
            <span>{{ $summary['employee_name'] }} &bull; Work Report History Dossier</span>
            <span class="toolbar-badge"><i class="fas fa-calendar-alt mr-1"></i> {{ $summary['date_range_label'] }}</span>
            <span class="toolbar-badge">{{ $summary['total_reports'] }} Days Logged</span>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn-tb btn-tb-primary">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
            <a href="{{ route('hrms.attendance.work-reports.employee-history', $employee->id) }}" class="btn-tb btn-tb-secondary">
                <i class="fas fa-arrow-left"></i> Back to History
            </a>
            <button onclick="window.close()" class="btn-tb btn-tb-secondary">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- Printable Official Document Container -->
    <div class="document-wrapper">

        <!-- 1. Executive Document Header -->
        <div class="doc-header">
            <div class="brand-block">
                @php
                    $logoUrl = branding_logo();
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ company_name() }}" class="brand-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="brand-fallback-logo" style="display:none;">
                        {{ strtoupper(substr(company_name(), 0, 1)) }}
                    </div>
                @else
                    <div class="brand-fallback-logo">
                        {{ strtoupper(substr(company_name(), 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="brand-title">{{ company_name() }}</div>
                    <div class="brand-subtitle">Enterprise HRMS &bull; Work Performance Audit</div>
                </div>
            </div>

            <div class="doc-meta">
                <span class="doc-badge-pill"><i class="fas fa-shield-alt mr-1"></i> Confidential & Proprietary</span>
                <h1 class="doc-title-main">Employee Work Report Dossier</h1>
                <div class="doc-meta-row">
                    <strong>Ref:</strong> WR-{{ $summary['employee_code'] }}-{{ now()->format('YmdHi') }}
                </div>
                <div class="doc-meta-row">
                    <strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}
                </div>
                <div class="doc-meta-row">
                    <strong>Period:</strong> {{ $summary['date_range_label'] }}
                </div>
            </div>
        </div>

        <!-- 2. Employee Profile & Departmental Snapshot -->
        <div class="emp-dossier-card">
            <div class="emp-profile-left">
                <div class="emp-avatar-wrapper">
                    @if($summary['passport_photo_url'])
                        <img src="{{ $summary['passport_photo_url'] }}" alt="{{ $summary['employee_name'] }}" onerror="this.style.display='none'; this.parentElement.innerText='{{ $summary['employee_initial'] }}';">
                    @else
                        <span>{{ $summary['employee_initial'] }}</span>
                    @endif
                </div>
                <div>
                    <h2 class="emp-name-title">{{ $summary['employee_name'] }}</h2>
                    <div class="emp-badges-line">
                        <span class="emp-code-badge"><i class="fas fa-id-badge mr-1"></i> {{ $summary['employee_code'] }}</span>
                        <span style="color: var(--brand-muted);">&bull;</span>
                        <span style="color: var(--brand-slate);"><i class="fas fa-building mr-1"></i> {{ $summary['department'] }}</span>
                        <span style="color: var(--brand-muted);">&bull;</span>
                        <span style="color: var(--brand-slate);"><i class="fas fa-briefcase mr-1"></i> {{ $summary['designation'] }}</span>
                    </div>
                </div>
            </div>

            <div class="emp-details-grid">
                <div class="emp-detail-item">
                    <div class="lbl">Reporting Manager</div>
                    <div class="val">{{ $summary['reporting_manager_name'] }}</div>
                </div>
                <div class="emp-detail-item">
                    <div class="lbl">Official Email</div>
                    <div class="val" style="word-break: break-all;">{{ $summary['employee_email'] }}</div>
                </div>
                <div class="emp-detail-item">
                    <div class="lbl">Attendance Mode</div>
                    <div class="val">{{ $summary['wfo_count'] }} WFO &bull; {{ $summary['wfh_count'] }} WFH</div>
                </div>
                <div class="emp-detail-item">
                    <div class="lbl">Submission Status</div>
                    <div class="val" style="color: #059669;"><i class="fas fa-check-circle mr-1"></i> Verified Active</div>
                </div>
            </div>
        </div>

        <!-- 3. Performance & Productivity KPI Metrics Strip -->
        <div class="kpi-strip">
            <div class="kpi-box">
                <div class="kpi-val">{{ $summary['total_reports'] }} <span style="font-size:12px; font-weight:600; color:var(--brand-muted);">Days</span></div>
                <div class="kpi-lbl">Reports Submitted</div>
                <div class="kpi-sub">Total verified logs in period</div>
            </div>

            <div class="kpi-box kpi-info">
                <div class="kpi-val">{{ $summary['total_gross_formatted'] }}</div>
                <div class="kpi-lbl">Total Work Hours</div>
                <div class="kpi-sub">Avg {{ $summary['avg_daily_formatted'] }}</div>
            </div>

            <div class="kpi-box kpi-success">
                <div class="kpi-val">{{ $summary['completed_tasks'] }} <span style="font-size:12px; font-weight:600; color:var(--brand-muted);">/ {{ $summary['total_tasks'] }}</span></div>
                <div class="kpi-lbl">Tasks Completed</div>
                <div class="kpi-sub">{{ $summary['completion_rate'] }}% Completion rate</div>
            </div>

            <div class="kpi-box {{ $summary['issues_count'] > 0 ? 'kpi-warning' : 'kpi-success' }}">
                <div class="kpi-val">{{ $summary['issues_count'] }} <span style="font-size:12px; font-weight:600; color:var(--brand-muted);">Days</span></div>
                <div class="kpi-lbl">Blockers Flagged</div>
                <div class="kpi-sub">{{ $summary['issues_count'] === 0 ? 'Zero Blockers Reported' : 'Requires TL Attention' }}</div>
            </div>
        </div>

        <!-- 4. Detailed Chronological Work Log Stream -->
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-list-check"></i> Chronological Daily Work Log Breakdown
            </h3>
            <span class="section-count">Showing {{ $workLogs->count() }} Daily Entries</span>
        </div>

        <div class="report-entries-container">
            @forelse($workLogs as $log)
                @php
                    $attendance = $log->attendance;
                    $mode = strtolower(optional($attendance)->work_mode ?? 'wfo');
                    $modeBadgeClass = $mode === 'wfh' ? 'badge-mode-wfh' : 'badge-mode-wfo';
                    $modeText = strtoupper($mode);
                    $shiftName = optional(optional($attendance)->attendanceTime)->name ?? 'General Shift';
                    $grossWork = optional($attendance)->gross_duration ?? ($log->duration_minutes ? floor($log->duration_minutes / 60) . 'h ' . ($log->duration_minutes % 60) . 'm' : '-');
                    
                    // Parse structured JSON
                    $tasks = $log->work_summary_json;
                    if (is_string($tasks)) {
                        $tasks = json_decode($tasks, true);
                    }

                    $projectsList = [];
                    $taskList = [];
                    $issues = [];
                    $notes = null;
                    $summaryDesc = $log->work_summary;

                    if (is_array($tasks)) {
                        if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                            $projectsList = $tasks['projects'];
                        } elseif (isset($tasks['requirements']) && is_array($tasks['requirements'])) {
                            $taskList = $tasks['requirements'];
                        } elseif (isset($tasks['tasks']) && is_array($tasks['tasks'])) {
                            $taskList = $tasks['tasks'];
                        }

                        if (!empty($tasks['description'])) {
                            $summaryDesc = $tasks['description'];
                        } elseif (!empty($tasks['today_work_description'])) {
                            $summaryDesc = $tasks['today_work_description'];
                        }

                        if (!empty($tasks['issues'])) {
                            $issues = is_array($tasks['issues']) ? $tasks['issues'] : [$tasks['issues']];
                        }
                        if (!empty($tasks['notes'])) {
                            $notes = $tasks['notes'];
                        }
                    }

                    // Clean summary description if it contains checkbox markers
                    if ($summaryDesc && (str_contains($summaryDesc, '☑') || str_contains($summaryDesc, '☐'))) {
                        $lines = explode("\n", $summaryDesc);
                        $cleanLines = array_filter($lines, fn($l) => !str_contains($l, '☑') && !str_contains($l, '☐') && !str_starts_with(trim($l), 'Project:'));
                        $summaryDesc = trim(implode(' ', $cleanLines));
                    }
                    if (!$summaryDesc) {
                        $summaryDesc = 'Work report submitted with project deliverables.';
                    }

                    // Filter real issues
                    $realIssues = array_filter($issues, function($item) {
                        if (!is_string($item)) return true;
                        $v = strtolower(trim($item));
                        return $v !== '' && $v !== 'no issues' && $v !== 'none';
                    });
                @endphp

                <div class="report-day-card">
                    <div class="report-day-header">
                        <div class="day-date-group">
                            <div class="day-index-badge">{{ $loop->iteration }}</div>
                            <div>
                                <span class="day-date-text">{{ $log->work_date ? $log->work_date->format('d M Y') : '-' }}</span>
                                <span style="font-size: 11px; color: var(--brand-muted); font-weight: 600; margin-left: 6px;">
                                    ({{ $log->work_date ? $log->work_date->format('l') : '' }})
                                </span>
                            </div>
                        </div>

                        <div class="day-meta-pills">
                            <span class="badge-mode {{ $modeBadgeClass }}"><i class="fas {{ $mode === 'wfh' ? 'fa-laptop-house' : 'fa-building' }} mr-1"></i> {{ $modeText }}</span>
                            <span class="badge-duration"><i class="far fa-clock mr-1"></i> Shift: {{ $shiftName }}</span>
                            <span class="badge-duration" style="font-weight: 800; color: var(--brand-dark);"><i class="fas fa-stopwatch mr-1 text-primary"></i> Gross: {{ $grossWork }}</span>
                        </div>
                    </div>

                    <div class="report-day-body">
                        <!-- Daily Summary Quote -->
                        <div class="day-summary-quote">
                            <strong style="color: var(--brand-dark);"><i class="fas fa-quote-left mr-1.5 opacity-50"></i> Summary:</strong> {{ $summaryDesc }}
                        </div>

                        <!-- Projects with Nested Tasks -->
                        @if(!empty($projectsList))
                            @foreach($projectsList as $proj)
                                @php
                                    $pName = $proj['project_name'] ?? ($proj['name'] ?? 'General Project Work');
                                    $pTasks = $proj['tasks'] ?? [];
                                @endphp
                                <div class="project-block">
                                    <div class="project-title-bar">
                                        <span><i class="fas fa-folder-open text-primary mr-1.5"></i> {{ $pName }}</span>
                                        <span style="font-size: 10px; color: var(--brand-muted);">{{ count($pTasks) }} Tasks</span>
                                    </div>
                                    <div class="task-list-items">
                                        @forelse($pTasks as $taskItem)
                                            @php
                                                $tName = $taskItem['task_name'] ?? ($taskItem['task'] ?? ($taskItem['description'] ?? ($taskItem['title'] ?? 'Task')));
                                                $isDone = (isset($taskItem['is_completed']) ? ($taskItem['is_completed'] == 1 || $taskItem['is_completed'] === true) : (isset($taskItem['completed']) ? ($taskItem['completed'] == 1 || $taskItem['completed'] === true) : true));
                                            @endphp
                                            <div class="task-row {{ $isDone ? 'task-done' : '' }}">
                                                <div class="task-text">
                                                    <i class="fas {{ $isDone ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mt-0.5" style="font-size: 11px;"></i>
                                                    <span>{{ $tName }}</span>
                                                </div>
                                                <span class="task-status-tag {{ $isDone ? 'tag-completed' : 'tag-pending' }}">
                                                    {{ $isDone ? 'COMPLETED' : 'PENDING' }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="text-muted font-italic" style="font-size:10.5px;">No individual tasks itemized.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        @elseif(!empty($taskList))
                            <div class="project-block">
                                <div class="project-title-bar">
                                    <span><i class="fas fa-tasks text-primary mr-1.5"></i> Deliverable Checklist</span>
                                    <span style="font-size: 10px; color: var(--brand-muted);">{{ count($taskList) }} Items</span>
                                </div>
                                <div class="task-list-items">
                                    @foreach($taskList as $tItem)
                                        @php
                                            $tName = is_string($tItem) ? $tItem : ($tItem['text'] ?? ($tItem['task'] ?? ($tItem['title'] ?? ($tItem['description'] ?? 'Task'))));
                                            $isDone = is_array($tItem) ? (isset($tItem['done']) ? ($tItem['done'] === true || $tItem['done'] === 'true') : true) : true;
                                        @endphp
                                        <div class="task-row {{ $isDone ? 'task-done' : '' }}">
                                            <div class="task-text">
                                                <i class="fas {{ $isDone ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mt-0.5" style="font-size: 11px;"></i>
                                                <span>{{ $tName }}</span>
                                            </div>
                                            <span class="task-status-tag {{ $isDone ? 'tag-completed' : 'tag-pending' }}">
                                                {{ $isDone ? 'COMPLETED' : 'PENDING' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Footer: Blockers & Notes -->
                        <div class="day-footer-strip">
                            @if(count($realIssues) > 0)
                                <div class="blocker-box alert">
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                    <span><strong>Blocker:</strong> {{ implode(', ', $realIssues) }}</span>
                                </div>
                            @else
                                <div class="blocker-box clean">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>No Blocker / Issue Reported</span>
                                </div>
                            @endif

                            @if($notes)
                                <div class="blocker-box" style="background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A;">
                                    <i class="fas fa-sticky-note text-warning"></i>
                                    <span><strong>Note:</strong> {{ $notes }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-5 text-center bg-light rounded-12 border">
                    <i class="fas fa-clipboard-list text-muted mb-2" style="font-size: 32px;"></i>
                    <h5 class="font-weight-bold text-dark">No Work Reports Logged</h5>
                    <p class="text-muted small mb-0">No work reports exist for this employee matching the specified criteria.</p>
                </div>
            @endforelse
        </div>

        <!-- 5. Executive Audit, Verification & Sign-Off Section -->
        <div class="signoff-section">
            <div class="section-title" style="font-size: 12px; margin-bottom: 4px;">
                <i class="fas fa-signature"></i> Audit Verification & Authorization Sign-Off
            </div>
            <p style="font-size: 10px; color: var(--brand-muted); margin: 0;">
                By signing below, the reviewer and department head confirm the accuracy of the task deliverables and work hours stated above.
            </p>

            <div class="signoff-grid">
                <!-- Employee Box -->
                <div class="signoff-column">
                    <div>
                        <div class="signoff-title">Employee Acknowledgment</div>
                        <div class="signoff-name">{{ $summary['employee_name'] }}</div>
                    </div>
                    <div class="signoff-line">
                        Signature &bull; Date: ____/____/20____
                    </div>
                </div>

                <!-- Supervisor Box -->
                <div class="signoff-column">
                    <div>
                        <div class="signoff-title">Reporting Supervisor Review</div>
                        <div class="signoff-name">{{ $summary['reporting_manager_name'] }}</div>
                    </div>
                    <div class="signoff-line">
                        Supervisor Signature &bull; Approved
                    </div>
                </div>

                <!-- HR Dept Box -->
                <div class="signoff-column">
                    <div>
                        <div class="signoff-title">HR Authorization / Stamp</div>
                        <div class="signoff-name">{{ company_name() }} HR</div>
                    </div>
                    <div class="signoff-line">
                        Official Stamp &bull; Verification Date
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Official Document Footer -->
        <div class="doc-footer">
            <div>
                <strong>{{ company_name() }}</strong> &bull; Generated via OrboOne HRMS Platform &bull; Valid without physical stamp if electronically verified.
            </div>
            <div>
                Confidential Performance Document &bull; Printed: {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>

    </div>

</body>
</html>
