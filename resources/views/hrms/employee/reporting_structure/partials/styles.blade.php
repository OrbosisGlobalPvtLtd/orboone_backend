<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
        --orb-active-green: #10B981;
    }

    .eo-page {
        background: var(--orb-bg);
        min-height: calc(100vh - 120px);
        padding: 24px;
        color: var(--orb-text);
        font-family: 'Inter', sans-serif;
        width: 100%;
        box-sizing: border-box;
    }

    .eo-container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        box-sizing: border-box;
    }

    /* Premium Header/Hero Card */
    .eo-header-premium {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 26px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 14px 35px rgba(75, 0, 232, 0.15);
        position: relative;
        overflow: hidden;
    }

    .eo-header-kicker {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 8px;
    }

    .eo-header-title {
        font-size: 28px;
        font-weight: 950;
        margin: 0 0 8px 0;
        color: #fff;
    }

    .eo-header-subtitle {
        font-size: 14px;
        font-weight: 650;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
    }

    /* Main Container Card */
    .eo-card {
        background: #fff;
        border-radius: 22px;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
        overflow: hidden;
        margin-bottom: 28px;
    }

    /* Card Header */
    .eo-card-header-premium {
        padding: 24px 28px;
        border-bottom: 1px solid var(--orb-border);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .eo-card-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .eo-header-icon-circle {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: var(--orb-soft);
        color: var(--orb-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .eo-card-title-premium {
        font-size: 18px;
        font-weight: 950;
        color: var(--orb-text);
        margin: 0;
    }

    .eo-card-subtitle-premium {
        font-size: 12px;
        font-weight: 650;
        color: var(--orb-muted);
        margin: 4px 0 0 0;
    }

    /* View Controls Buttons */
    .eo-view-toggle {
        display: inline-flex;
        background: var(--orb-bg);
        padding: 4px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
    }

    .eo-view-btn {
        border: 0;
        background: transparent;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 800;
        color: var(--orb-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.15s ease;
    }

    .eo-view-btn.active {
        background: #fff;
        color: var(--orb-primary);
        box-shadow: 0 4px 10px rgba(16, 24, 40, 0.05);
    }

    /* Filter Panel */
    .eo-filter-inside {
        padding: 20px 28px;
        background: #FAFCFF;
        border-bottom: 1px solid var(--orb-border);
    }

    .eo-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .eo-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .eo-field label {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--orb-muted);
        letter-spacing: 0.5px;
    }

    .eo-control {
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--orb-border);
        padding: 0 14px;
        font-size: 13px;
        font-weight: 700;
        background: #fff;
        color: var(--orb-text);
        outline: none;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        transition: 0.15s ease;
        width: 100%;
    }

    .eo-control:focus {
        border-color: rgba(75, 0, 232, .45);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08);
    }

    .eo-btn {
        min-height: 40px;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid transparent;
        text-decoration: none !important;
        cursor: pointer;
        white-space: nowrap;
        background: #fff;
        color: #344054;
        border-color: var(--orb-border);
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
        transition: all 0.15s ease;
    }

    .eo-btn:hover {
        background: #F8FAFC;
        color: var(--orb-text);
        border-color: #D0D5DD;
    }

    .eo-filter-actions-col {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .eo-filter-actions-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .eo-btn-primary {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary)) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.2) !important;
    }

    .eo-btn-primary:hover {
        opacity: 0.92 !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .eo-btn-reset {
        background: #fff !important;
        color: #475467 !important;
    }

    /* Select2 integration styling for filter panel */
    .eo-filter-grid .select2-container {
        width: 100% !important;
    }

    .eo-filter-grid .select2-container .select2-selection--single {
        height: 40px !important;
        border-radius: 10px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04) !important;
        transition: 0.15s ease !important;
    }

    .eo-filter-grid .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--orb-text) !important;
        padding-left: 14px !important;
        padding-right: 28px !important;
    }

    .eo-filter-grid .select2-container .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 8px !important;
    }

    .eo-filter-grid .select2-container--open .select2-selection--single,
    .eo-filter-grid .select2-container--focus .select2-selection--single {
        border-color: rgba(75, 0, 232, .45) !important;
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    /* TREE TOOLBAR */
    .eo-tree-toolbar {
        padding: 12px 28px;
        background: #F1F5F9;
        border-bottom: 1px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .eo-counter-badge {
        font-size: 12px;
        font-weight: 800;
        color: var(--orb-primary);
        background: #fff;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1px solid var(--orb-border);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .eo-toolbar-right {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .eo-tool-btn {
        background: #fff;
        border: 1px solid var(--orb-border);
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 750;
        color: var(--orb-text);
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .eo-tool-btn:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, 0.3);
    }

    .eo-zoom-text {
        font-size: 12px;
        font-weight: 800;
        color: var(--orb-muted);
        min-width: 45px;
        text-align: center;
    }

    .eo-tool-divider {
        width: 1px;
        height: 20px;
        background: var(--orb-border);
        margin: 0 4px;
    }

    /* ORG CHART TREE VIEW STYLE */
    .eo-tree-container {
        padding: 40px 28px;
        overflow-x: auto;
        overflow-y: auto;
        min-height: 550px;
        background: #F8FAFC;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        position: relative;
    }

    .org-tree {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: max-content;
        min-width: 100%;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: top center;
    }

    .org-tree ul {
        display: flex;
        justify-content: center;
        padding-top: 24px;
        position: relative;
        transition: all 0.3s;
        margin: 0;
        padding-left: 0;
    }

    /* Vertical line coming DOWN from parent node card to children list */
    .org-tree ul ul::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        border-left: 2px solid #CBD5E1;
        width: 0;
        height: 24px;
        transform: translateX(-50%);
    }

    .org-tree li {
        text-align: center;
        list-style-type: none;
        position: relative;
        padding: 24px 14px 0 14px;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Connecting Horizontal Lines for Siblings */
    .org-tree li::before, .org-tree li::after {
        content: '';
        position: absolute;
        top: 0;
        right: 50%;
        border-top: 2px solid #CBD5E1;
        width: 50%;
        height: 24px;
    }

    .org-tree li::after {
        right: auto;
        left: 50%;
        border-top: 2px solid #CBD5E1;
        border-left: none;
    }

    /* Remove outer horizontal lines for first/last/only siblings */
    .org-tree li:first-child::before {
        border-top: none;
    }
    .org-tree li:last-child::after {
        border-top: none;
    }
    .org-tree li:only-child::before, .org-tree li:only-child::after {
        border-top: none;
    }

    /* Vertical stem going UP from node card to horizontal sibling line */
    .org-tree li > .eo-node-card::before {
        content: '';
        position: absolute;
        top: -24px;
        left: 50%;
        border-left: 2px solid #CBD5E1;
        width: 0;
        height: 24px;
        transform: translateX(-50%);
    }

    /* Top-level root node card has no top line */
    .org-tree > ul > li > .eo-node-card::before {
        display: none;
    }

    /* Vertical stem going DOWN from parent node card to children list */
    .org-tree li.has-children > .eo-node-card::after {
        content: '';
        position: absolute;
        bottom: -24px;
        left: 50%;
        border-left: 2px solid #CBD5E1;
        width: 0;
        height: 24px;
        transform: translateX(-50%);
    }

    /* EMPLOYEE NODE CARD */
    .eo-node-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 18px;
        padding: 16px;
        width: 250px;
        box-shadow: 0 4px 20px rgba(16, 24, 40, 0.04);
        position: relative;
        z-index: 10;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: left;
        display: inline-block;
    }

    .eo-node-card:hover {
        border-color: var(--orb-primary);
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.12);
        transform: translateY(-3px);
    }

    .eo-node-card.highlighted {
        border-color: #10B981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15), 0 12px 30px rgba(16, 185, 129, 0.15);
        animation: pulseHighlight 2s infinite;
    }

    .eo-match-pill {
        position: absolute;
        top: -10px;
        right: 14px;
        background: linear-gradient(135deg, #10B981, #059669);
        color: #fff;
        font-size: 10px;
        font-weight: 850;
        padding: 2px 8px;
        border-radius: 20px;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        z-index: 20;
    }

    @keyframes pulseHighlight {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }

    .eo-node-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    /* Avatar & Image handling */
    .eo-node-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        background: var(--orb-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: var(--orb-primary);
        font-size: 15px;
        flex-shrink: 0;
        border: 1px solid var(--orb-border);
    }

    .eo-node-info {
        flex-grow: 1;
        min-width: 0;
    }

    .eo-node-name {
        font-size: 13px;
        font-weight: 850;
        color: var(--orb-text);
        margin: 0 0 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .eo-node-code {
        font-size: 10px;
        font-weight: 800;
        color: var(--orb-muted);
    }

    .eo-node-detail-line {
        font-size: 11px;
        font-weight: 650;
        color: var(--orb-muted);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .eo-node-detail-line i {
        width: 14px;
        color: var(--orb-primary);
    }

    /* Badges & Metrics inside Node */
    .eo-node-badges {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px dashed var(--orb-border);
    }

    .eo-reportees-badge {
        font-size: 10px;
        font-weight: 800;
        color: var(--orb-primary);
        background: var(--orb-soft);
        padding: 4px 8px;
        border-radius: 8px;
    }

    .eo-profile-link {
        font-size: 11px;
        font-weight: 800;
        color: var(--orb-secondary);
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: 0.15s ease;
    }

    .eo-profile-link:hover {
        color: var(--orb-primary);
    }

    /* Expand / Collapse Branch Button */
    .eo-toggle-branch {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid var(--orb-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: var(--orb-muted);
        cursor: pointer;
        position: absolute;
        bottom: -11px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 25;
        transition: 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .eo-toggle-branch:hover {
        border-color: var(--orb-primary);
        color: var(--orb-primary);
        transform: translateX(-50%) scale(1.15);
    }

    /* Stacked Collapsible List Style */
    .eo-list-container {
        padding: 24px 28px;
        background: #fff;
    }

    .eo-list-item {
        border: 1px solid var(--orb-border);
        border-radius: 16px;
        padding: 14px 18px;
        background: #fff;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: all 0.2s ease;
    }

    .eo-list-item:hover {
        border-color: var(--orb-primary);
        box-shadow: 0 4px 16px rgba(75, 0, 232, 0.06);
        transform: translateY(-1px);
    }

    .eo-list-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .eo-list-sno {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 10px;
        background: #F1F5F9;
        color: #475467;
        font-weight: 800;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E2E8F0;
        letter-spacing: -0.3px;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }

    .eo-list-item:hover .eo-list-sno {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, 0.2);
    }

    .eo-list-emp-details {
        min-width: 0;
    }

    .eo-list-emp-details .eo-node-name {
        font-size: 14px;
        font-weight: 850;
        color: var(--orb-text);
        margin: 0 0 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .eo-list-emp-details .eo-node-code {
        font-size: 11px;
        font-weight: 700;
        color: var(--orb-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .eo-list-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    .eo-list-badge {
        font-size: 11px;
        font-weight: 750;
        padding: 6px 12px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #F8FAFC;
        border: 1px solid var(--orb-border);
        color: var(--orb-text);
        white-space: nowrap;
    }

    .eo-list-badge.badge-dept {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, 0.12);
    }

    .eo-list-badge.badge-manager {
        background: #F0FDF4;
        color: #15803D;
        border-color: rgba(21, 128, 61, 0.15);
    }

    .eo-list-badge.badge-team {
        background: #F8FAFC;
        color: #475467;
        border-color: var(--orb-border);
    }

    .eo-list-btn-profile {
        min-height: 34px;
        height: 34px;
        padding: 0 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
        background: #fff;
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .eo-list-btn-profile:hover {
        background: var(--orb-primary);
        color: #fff !important;
        border-color: var(--orb-primary);
        box-shadow: 0 4px 10px rgba(75, 0, 232, 0.15);
    }

    /* Empty state */
    .eo-empty-state {
        text-align: center;
        padding: 60px 40px;
        background: #fff;
    }

    .eo-empty-icon {
        font-size: 52px;
        color: var(--orb-muted);
        opacity: 0.4;
        margin-bottom: 16px;
    }

    .eo-empty-title {
        font-size: 16px;
        font-weight: 900;
        color: var(--orb-text);
        margin: 0 0 6px 0;
    }

    .eo-empty-sub {
        font-size: 13px;
        color: var(--orb-muted);
        margin: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .eo-filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .eo-list-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }

        .eo-list-right {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 576px) {
        .eo-page {
            padding: 12px 10px;
        }

        .eo-header-premium {
            border-radius: 18px;
            padding: 24px;
        }

        .eo-card-header-premium {
            padding: 16px 20px;
        }

        .eo-filter-grid {
            grid-template-columns: 1fr;
        }

        .eo-filter-inside {
            padding: 16px 20px;
        }

        .eo-list-container {
            padding: 16px 14px;
        }

        .eo-list-item {
            padding: 12px;
        }

        .eo-list-btn-profile {
            width: 100%;
            justify-content: center;
        }
    }
</style>
