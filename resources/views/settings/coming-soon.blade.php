<!-- @extends('layouts.admin', ['accesses' => $accesses ?? [], 'active' => '']) -->
@extends('layouts.panel')

@section('page_title', 'Coming Soon')
@section('_content')

<style>
    /* =====================================================
       ORBOSIS — COMING SOON PAGE
       ===================================================== */
    :root {
        --cs-bg: #f4f6fb;
    }

    .cs-page-wrap {
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--cs-bg);
        padding: 40px 20px;
    }

    .cs-card {
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.07);
        max-width: 680px;
        width: 100%;
        padding: 60px 50px 50px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    /* Top accent bar */
    .cs-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: var(--cs-accent, linear-gradient(90deg, #1560ab, #0099cc));
    }

    /* Decorative blobs */
    .cs-blob {
        position: absolute;
        border-radius: 50%;
        opacity: 0.06;
        pointer-events: none;
    }
    .cs-blob-1 { width: 300px; height: 300px; background: var(--cs-color, #1560ab); top: -80px; right: -80px; }
    .cs-blob-2 { width: 200px; height: 200px; background: var(--cs-color, #1560ab); bottom: -60px; left: -60px; }

    /* Icon circle */
    .cs-icon-wrap {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 28px;
        background: var(--cs-icon-bg, rgba(21,96,171,0.1));
        font-size: 2.8rem;
        color: var(--cs-color, #1560ab);
        animation: cs-pulse 2.5s ease-in-out infinite;
        position: relative;
        z-index: 1;
    }

    @keyframes cs-pulse {
        0%, 100% { box-shadow: 0 0 0 0 var(--cs-shadow, rgba(21,96,171,0.25)); transform: scale(1); }
        50% { box-shadow: 0 0 0 18px transparent; transform: scale(1.06); }
    }

    .cs-badge {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 5px 18px;
        border-radius: 30px;
        margin-bottom: 18px;
        background: var(--cs-badge-bg, rgba(21,96,171,0.1));
        color: var(--cs-color, #1560ab);
        position: relative;
        z-index: 1;
    }

    .cs-title {
        font-size: 2.4rem;
        font-weight: 900;
        color: #1a2340;
        letter-spacing: -0.5px;
        margin-bottom: 12px;
        position: relative;
        z-index: 1;
    }

    .cs-subtitle {
        font-size: 1rem;
        color: #7a8099;
        max-width: 440px;
        margin: 0 auto 36px;
        line-height: 1.7;
        position: relative;
        z-index: 1;
    }

    /* Feature chips */
    .cs-features {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }

    .cs-feature-chip {
        display: flex;
        align-items: center;
        gap: 7px;
        background: #f7f8fc;
        border: 1px solid #e6eaf5;
        border-radius: 30px;
        padding: 7px 16px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #4a5568;
    }

    .cs-feature-chip i {
        color: var(--cs-color, #1560ab);
        font-size: 0.85rem;
    }

    /* CTA Button */
    .cs-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--cs-accent, linear-gradient(135deg, #1560ab, #0099cc));
        color: #fff !important;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 13px 32px;
        border-radius: 50px;
        text-decoration: none !important;
        letter-spacing: 0.5px;
        box-shadow: 0 6px 20px var(--cs-shadow, rgba(21,96,171,0.3));
        transition: all 0.25s ease;
        position: relative;
        z-index: 1;
    }

    .cs-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px var(--cs-shadow, rgba(21,96,171,0.4));
        filter: brightness(1.05);
    }

    .cs-divider {
        border: none;
        border-top: 1px solid #edf0f9;
        margin: 36px 0 28px;
        position: relative;
        z-index: 1;
    }

    .cs-footer-note {
        font-size: 0.75rem;
        color: #aab0c5;
        position: relative;
        z-index: 1;
    }

    .cs-footer-note a {
        color: var(--cs-color, #1560ab);
        font-weight: 700;
        text-decoration: none;
    }

    /* Progress bar animation */
    .cs-progress-wrap {
        background: #f0f2fa;
        border-radius: 10px;
        height: 8px;
        margin: 28px 0 10px;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

    .cs-progress-bar {
        height: 100%;
        border-radius: 10px;
        background: var(--cs-accent, linear-gradient(90deg, #1560ab, #0099cc));
        animation: cs-progress-anim 3s ease-in-out infinite alternate;
    }

    @keyframes cs-progress-anim {
        from { width: 35%; }
        to   { width: 70%; }
    }

    .cs-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.7rem;
        color: #aab0c5;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
</style>

@php
    // Module definitions – driven by $module variable from route
    $modules = [
        'crm' => [
            'name'      => 'CRM System',
            'subtitle'  => 'Customer Relationship Management, Lead Pipelines, Sales Funnels & Client Analytics — all in one place.',
            'icon'      => 'fas fa-handshake',
            'color'     => '#4e4bcc',
            'accent'    => 'linear-gradient(135deg, #4e4bcc, #6a5ef7)',
            'iconBg'    => 'rgba(78,75,204,0.1)',
            'shadow'    => 'rgba(78,75,204,0.3)',
            'badgeBg'   => 'rgba(78,75,204,0.1)',
            'badge'     => 'Coming Soon',
            'progress'  => '60%',
            'features'  => [
                ['icon' => 'fas fa-user-tie', 'label' => 'Lead Management'],
                ['icon' => 'fas fa-funnel-dollar', 'label' => 'Sales Pipeline'],
                ['icon' => 'fas fa-chart-bar', 'label' => 'CRM Analytics'],
                ['icon' => 'fas fa-envelope-open-text', 'label' => 'Email Campaigns'],
                ['icon' => 'fas fa-phone-alt', 'label' => 'Call Tracking'],
                ['icon' => 'fas fa-star', 'label' => 'Deal Scoring'],
            ],
        ],
        'project-mgmt' => [
            'name'      => 'Project Management',
            'subtitle'  => 'Agile Boards, Sprint Planning, Team Collaboration & Milestone Tracking across your entire organization.',
            'icon'      => 'fas fa-project-diagram',
            'color'     => '#11a67a',
            'accent'    => 'linear-gradient(135deg, #11a67a, #1cc88a)',
            'iconBg'    => 'rgba(17,166,122,0.1)',
            'shadow'    => 'rgba(17,166,122,0.3)',
            'badgeBg'   => 'rgba(17,166,122,0.1)',
            'badge'     => 'Coming Soon',
            'progress'  => '40%',
            'features'  => [
                ['icon' => 'fas fa-columns', 'label' => 'Kanban Boards'],
                ['icon' => 'fas fa-stopwatch', 'label' => 'Time Tracking'],
                ['icon' => 'fas fa-sitemap', 'label' => 'Milestones'],
                ['icon' => 'fas fa-users', 'label' => 'Team Workload'],
                ['icon' => 'fas fa-file-alt', 'label' => 'Project Reports'],
                ['icon' => 'fas fa-bell', 'label' => 'Smart Alerts'],
            ],
        ],
        'finance' => [
            'name'      => 'Finance',
            'subtitle'  => 'Automated Invoicing, Expense Management, Accounting Ledgers & Full Financial Reporting.',
            'icon'      => 'fas fa-file-invoice-dollar',
            'color'     => '#c9970a',
            'accent'    => 'linear-gradient(135deg, #c9970a, #f6c23e)',
            'iconBg'    => 'rgba(246,194,62,0.12)',
            'shadow'    => 'rgba(246,194,62,0.35)',
            'badgeBg'   => 'rgba(246,194,62,0.12)',
            'badge'     => 'Coming Soon',
            'progress'  => '30%',
            'features'  => [
                ['icon' => 'fas fa-receipt', 'label' => 'Invoicing'],
                ['icon' => 'fas fa-wallet', 'label' => 'Expense Tracking'],
                ['icon' => 'fas fa-balance-scale', 'label' => 'Ledger Accounts'],
                ['icon' => 'fas fa-piggy-bank', 'label' => 'Budgeting'],
                ['icon' => 'fas fa-chart-pie', 'label' => 'P&L Reports'],
                ['icon' => 'fas fa-university', 'label' => 'Tax Filing'],
            ],
        ],
    ];

    $m = $modules[$module] ?? $modules['crm'];
@endphp

<div class="cs-page-wrap">
    <div class="cs-card"
         style="--cs-color: {{ $m['color'] }}; --cs-accent: {{ $m['accent'] }}; --cs-icon-bg: {{ $m['iconBg'] }}; --cs-shadow: {{ $m['shadow'] }}; --cs-badge-bg: {{ $m['badgeBg'] }};">

        {{-- Decorative blobs --}}
        <div class="cs-blob cs-blob-1"></div>
        <div class="cs-blob cs-blob-2"></div>

        {{-- Badge --}}
        <div class="cs-badge">{{ $m['badge'] }}</div>

        {{-- Icon --}}
        <div class="cs-icon-wrap">
            <i class="{{ $m['icon'] }}"></i>
        </div>

        {{-- Title --}}
        <h1 class="cs-title">{{ $m['name'] }}</h1>
        <p class="cs-subtitle">{{ $m['subtitle'] }}</p>

        {{-- Feature chips --}}
        <div class="cs-features">
            @foreach ($m['features'] as $feature)
                <div class="cs-feature-chip">
                    <i class="{{ $feature['icon'] }}"></i>
                    {{ $feature['label'] }}
                </div>
            @endforeach
        </div>

        {{-- Progress --}}
        <div class="cs-progress-wrap">
            <div class="cs-progress-bar"></div>
        </div>
        <div class="cs-progress-label">
            <span>Development in Progress</span>
            <span>Stay tuned!</span>
        </div>

        <hr class="cs-divider">

        {{-- CTA --}}
        <a href="{{ route('dashboard') }}" class="cs-btn">
            <i class="fas fa-arrow-left"></i>
            Back to HRMS Dashboard
        </a>

        <p class="cs-footer-note mt-4">
            {{ branding_name() }} &mdash;
            <a href="{{ route('dashboard') }}">HRMS</a> is live now.
            More modules launching soon.
        </p>
    </div>
</div>

@endsection
