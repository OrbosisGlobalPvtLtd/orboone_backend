<style>
    :root {
        --leave-primary: var(--orb-primary, #4B00E8);
        --leave-secondary: var(--orb-secondary, #8600EE);
        --leave-border: var(--orb-border, #E7EAF3);
        --leave-text: var(--orb-text, #101828);
        --leave-muted: var(--orb-muted, #667085);
        --leave-soft: var(--orb-soft, #F4F2FF);
        --leave-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    .leave-page-wrap {
        padding-bottom: 24px;
    }

    .leave-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 22px 24px;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, .26), transparent 35%),
            linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        color: #fff;
        box-shadow: 0 18px 45px rgba(75, 0, 232, .22);
        margin-bottom: 18px;
    }

    .leave-hero::after {
        content: '';
        position: absolute;
        width: 210px;
        height: 210px;
        border-radius: 50%;
        right: -90px;
        bottom: -120px;
        background: rgba(255, 255, 255, .14);
    }

    .leave-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        color: rgba(255, 255, 255, .92);
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .leave-hero-title {
        font-size: 26px;
        font-weight: 900;
        margin: 0;
        color: #fff;
        letter-spacing: -.03em;
    }

    .leave-hero-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        max-width: 780px;
        line-height: 1.6;
    }

    .leave-expire-btn {
        border: 1px solid rgba(255, 255, 255, .28) !important;
        border-radius: 14px;
        background: rgba(255, 255, 255, .16) !important;
        color: #fff !important;
        font-size: 13px;
        font-weight: 900;
        height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
    }

    .leave-expire-btn i {
        color: #fff !important;
    }

    .leave-expire-btn:hover {
        transform: translateY(-1px);
        background: rgba(255, 255, 255, .22) !important;
    }

    .leave-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 20px;
        box-shadow: var(--leave-shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        transition: all .2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(16, 24, 40, .10);
    }

    .summary-card::after {
        content: '';
        position: absolute;
        width: 74px;
        height: 74px;
        border-radius: 50%;
        right: -28px;
        bottom: -28px;
        background: var(--summary-soft);
    }

    .summary-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: var(--summary-gradient);
        box-shadow: 0 10px 22px var(--summary-shadow);
        position: relative;
        z-index: 2;
    }

    .summary-info {
        position: relative;
        z-index: 2;
    }

    .summary-info h4 {
        margin: 0;
        font-size: 26px;
        font-weight: 950;
        color: var(--leave-text);
        line-height: 1;
    }

    .summary-info p {
        margin: 5px 0 0;
        color: var(--leave-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .summary-primary {
        --summary-gradient: linear-gradient(135deg, var(--leave-primary), var(--leave-secondary));
        --summary-shadow: rgba(75, 0, 232, .22);
        --summary-soft: rgba(75, 0, 232, .08);
    }

    .summary-teal {
        --summary-gradient: linear-gradient(135deg, #0284C7, #38BDF8);
        --summary-shadow: rgba(2, 132, 199, .22);
        --summary-soft: rgba(2, 132, 199, .08);
    }

    .summary-success {
        --summary-gradient: linear-gradient(135deg, #12B76A, #039855);
        --summary-shadow: rgba(18, 183, 106, .22);
        --summary-soft: rgba(18, 183, 106, .09);
    }

    .summary-danger {
        --summary-gradient: linear-gradient(135deg, #F04438, #D92D20);
        --summary-shadow: rgba(240, 68, 56, .22);
        --summary-soft: rgba(240, 68, 56, .09);
    }

    /* 4 Responsive Comp-Off Metric Cards in a Single Row on Desktop (Image 2 Design) */
    .leave-metric-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 14px !important;
        margin-bottom: 22px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .leave-metric-card {
        background: #FFFFFF !important;
        border: 1px solid var(--leave-border, #E7EAF3) !important;
        border-radius: 16px !important;
        padding: 14px 16px 12px !important;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .055) !important;
        position: relative !important;
        overflow: hidden !important;
        min-height: 92px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .leave-metric-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 14px 30px rgba(16, 24, 40, .09) !important;
    }

    .leave-metric-card::after {
        content: "" !important;
        position: absolute !important;
        right: -22px !important;
        top: -30px !important;
        width: 82px !important;
        height: 82px !important;
        border-radius: 50% !important;
        background: var(--metric-soft, #F4F2FF) !important;
        pointer-events: none !important;
        z-index: 0 !important;
    }

    .leave-metric-card .leave-metric-top {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 8px !important;
        position: relative !important;
        z-index: 1 !important;
        width: 100% !important;
    }

    .leave-metric-card .leave-metric-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        background: var(--metric-soft, #F4F2FF) !important;
        color: var(--metric-color, var(--leave-primary)) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }

    .leave-metric-card .leave-metric-value {
        font-size: 26px !important;
        font-weight: 950 !important;
        color: #101828 !important;
        line-height: 1 !important;
        letter-spacing: -0.02em !important;
        text-align: right !important;
    }

    .leave-metric-card .leave-metric-label {
        font-size: 11px !important;
        font-weight: 900 !important;
        color: #475467 !important;
        text-transform: uppercase !important;
        margin-top: 12px !important;
        position: relative !important;
        z-index: 1 !important;
        letter-spacing: 0.03em !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .leave-metric-card .leave-metric-line {
        position: relative !important;
        height: 3px !important;
        border-radius: 999px !important;
        background: linear-gradient(90deg, var(--metric-color, var(--leave-primary)), transparent) !important;
        margin-top: 8px !important;
        width: 100% !important;
        z-index: 1 !important;
    }

    @media (max-width: 991px) {
        .leave-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }
    }

    @media (max-width: 576px) {
        .leave-metric-grid {
            grid-template-columns: 1fr !important;
            gap: 8px !important;
        }
    }

    /* Filter Form Toolbar Section */
    .filter-form-grid {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px;
        width: 100%;
    }

    .filter-item-wrap {
        flex: 1 1 0 !important;
        min-width: 170px !important;
    }

    .filter-item-wrap .select2-container {
        width: 100% !important;
    }

    .filter-actions-box {
        flex: 0 0 auto !important;
    }

    .filter-item-lbl {
        font-size: 11px !important;
        font-weight: 850 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: #475467 !important;
        margin-bottom: 6px !important;
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
        white-space: nowrap !important;
    }

    .filter-control-sm {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #CBD5E1 !important;
        font-size: 13px !important;
        color: #1E293B !important;
        padding: 0 12px !important;
        background-color: #FFFFFF !important;
        width: 100% !important;
        transition: all 0.2s ease !important;
        font-weight: 600 !important;
        box-sizing: border-box !important;
    }

    .filter-control-sm:focus {
        border-color: var(--leave-primary) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.08) !important;
        outline: none !important;
    }

    .btn-orb-search {
        height: 38px !important;
        border-radius: 10px !important;
        padding: 0 18px !important;
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 850 !important;
        font-size: 12.5px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, 0.25) !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        white-space: nowrap !important;
    }

    .btn-orb-search:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 20px rgba(75, 0, 232, 0.35) !important;
        color: #ffffff !important;
        filter: brightness(1.05) !important;
    }

    .btn-orb-reset {
        height: 38px !important;
        border-radius: 10px !important;
        padding: 0 14px !important;
        background: #ffffff !important;
        color: #475467 !important;
        border: 1px solid #CBD5E1 !important;
        font-weight: 750 !important;
        font-size: 12.5px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
        text-decoration: none !important;
    }

    .btn-orb-reset:hover {
        background: #F8FAFC !important;
        color: #0F172A !important;
    }

    /* Orbo Styled DataTables Controls & Pagination */
    .dataTables_wrapper .dataTables_length {
        display: flex;
        align-items: center;
    }

    .dataTables_wrapper .dataTables_length label {
        font-size: 12.5px;
        font-weight: 700;
        color: #475467;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .dataTables_wrapper .dataTables_length select {
        height: 36px !important;
        border-radius: 9px !important;
        border: 1px solid #CBD5E1 !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        padding: 0 28px 0 10px !important;
        color: #1E293B !important;
    }

    .dataTables_wrapper .dt-buttons {
        gap: 6px;
        display: inline-flex;
    }

    .dataTables_wrapper .dt-buttons .btn {
        height: 36px;
        border-radius: 9px !important;
        border: 1px solid #E2E8F0 !important;
        background: #FFFFFF !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: #334155 !important;
        padding: 0 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        transition: all 0.2s ease !important;
    }

    .dataTables_wrapper .dt-buttons .btn:hover {
        background: #F8FAFC !important;
        border-color: #CBD5E1 !important;
        transform: translateY(-1px) !important;
        color: var(--orb-primary, #4B00E8) !important;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 12.5px !important;
        color: #64748B !important;
        font-weight: 700 !important;
        padding-top: 10px !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 8px;
        display: flex;
        justify-content: flex-end;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 5px 11px !important;
        margin: 0 2px !important;
        border-radius: 8px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        border: 1px solid #E2E8F0 !important;
        background: #FFFFFF !important;
        color: #475467 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #F8FAFC !important;
        color: var(--orb-primary, #4B00E8) !important;
        border-color: var(--orb-primary, #4B00E8) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        color: #FFFFFF !important;
        border-color: transparent !important;
        box-shadow: 0 3px 10px rgba(75, 0, 232, 0.25) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        background: #FFFFFF !important;
        color: #94A3B8 !important;
    }

   
    .orb-table-tools-bar.eo-toolbar {
        padding: 12px 20px !important;
        background: #ffffff !important;
        border-bottom: 1px solid var(--orb-border, #E7EAF3) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
        box-sizing: border-box !important;
    }

    .eo-toolbar-left,
    .orb-table-length-box {
        display: flex !important;
        align-items: center !important;
    }

    .eo-entries-label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin: 0 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--orb-muted, #667085) !important;
        white-space: nowrap !important;
    }

    /* Select2 per-page styling matching HRMS attendance theme */
    .select2-container--per-page.select2-container {
        width: 75px !important;
        min-width: 75px !important;
        margin: 0 4px !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }

    .select2-container--per-page .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #D0D5DD !important;
        background: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05) !important;
        padding: 0 20px 0 8px !important;
        transition: all 0.2s ease !important;
    }

    .select2-container--per-page.select2-container--open .select2-selection--single,
    .select2-container--per-page.select2-container--focus .select2-selection--single {
        border-color: var(--orb-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
    }

    .select2-container--per-page .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        color: var(--orb-text, #0F172A) !important;
        padding: 0 !important;
        text-align: center !important;
        width: 100% !important;
    }

    .select2-container--per-page .select2-selection__arrow {
        height: 36px !important;
        right: 6px !important;
        width: 18px !important;
    }

    .select2-dropdown-per-page.select2-dropdown {
        border-radius: 12px !important;
        border: 1px solid #E7EAF3 !important;
        box-shadow: 0 10px 30px rgba(16, 24, 40, 0.15) !important;
        padding: 6px !important;
        min-width: 75px !important;
        z-index: 99999 !important;
        background: #ffffff !important;
    }

    .select2-dropdown-per-page .select2-results__option {
        padding: 6px 10px !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-align: center !important;
        margin-bottom: 2px !important;
        color: #101828 !important;
        transition: all 0.15s ease !important;
    }

    .select2-dropdown-per-page .select2-results__option--selected,
    .select2-dropdown-per-page .select2-results__option--highlighted {
        background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%) !important;
        color: #ffffff !important;
    }

    /* Tool buttons / Export Buttons matching HRMS attendance theme */
    .eo-toolbar-right,
    .orb-table-export-buttons {
        display: inline-flex !important;
        align-items: center !important;
    }

    .orbo-export-group {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .orbo-export-btn {
        height: 36px !important;
        padding: 0 16px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        letter-spacing: -0.2px !important;
        border: 1px solid #E2E8F0 !important;
        background: #FFFFFF !important;
        color: #0F172A !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02) !important;
        transition: all 0.18s ease-in-out !important;
        white-space: nowrap !important;
        text-decoration: none !important;
        cursor: pointer !important;
        user-select: none !important;
    }

    .orbo-export-btn:hover {
        background: #F8FAFC !important;
        color: #0F172A !important;
        border-color: #CBD5E1 !important;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.07) !important;
        transform: translateY(-1px) !important;
    }

    .orbo-export-btn:active {
        transform: translateY(0) !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    .orbo-export-btn i {
        font-size: 14px !important;
        line-height: 1 !important;
    }

    .btn-export-csv i,
    .icon-csv {
        color: #0F172A !important;
    }

    .btn-export-excel i,
    .icon-excel {
        color: #16A34A !important;
    }

    .btn-export-pdf i,
    .icon-pdf {
        color: #DC2626 !important;
    }

    .btn-export-print i,
    .icon-print {
        color: #0F172A !important;
    }

    .leave-card {
        background: #fff;
        border: 1px solid var(--leave-border);
        border-radius: 24px;
        box-shadow: var(--leave-shadow);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .leave-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--leave-border);
        background: linear-gradient(180deg, #fff, #FCFCFD);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .leave-card-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .leave-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--leave-soft);
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .leave-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        color: var(--leave-text);
    }

    .leave-card-subtitle {
        margin: 2px 0 0;
        font-size: 12px;
        color: var(--leave-muted);
        font-weight: 600;
    }

    .leave-action-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .leave-light-btn {
        border: 1px solid var(--leave-border);
        background: #fff;
        color: var(--leave-text);
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all .2s ease;
    }

    .leave-light-btn:hover {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border-color: rgba(75, 0, 232, .18);
    }

    .pending-approval-grid {
        padding: 16px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .approval-card {
        border: 1px solid var(--leave-border);
        border-radius: 18px;
        background: #fff;
        padding: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 8px 18px rgba(16, 24, 40, .04);
    }

    .approval-employee {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .approval-avatar,
    .employee-avatar {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(75, 0, 232, .12), rgba(134, 0, 238, .16));
        color: var(--leave-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        border: 1px solid rgba(75, 0, 232, .12);
        overflow: hidden !important;
    }

    .approval-avatar img,
    .employee-avatar img {
        width: 100% !important;
        height: 100% !important;
        border-radius: inherit !important;
        object-fit: cover !important;
        display: block !important;
    }

    .approval-name,
    .employee-name {
        font-size: 13px;
        font-weight: 900;
        color: var(--leave-text);
        line-height: 1.2;
    }

    .approval-meta,
    .employee-meta {
        font-size: 11px;
        color: var(--leave-muted);
        margin-top: 2px;
        font-weight: 700;
    }

    .approve-btn {
        border: 1px solid #ABEFC6;
        background: #ECFDF3;
        color: #027A48;
        border-radius: 12px;
        height: 34px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s ease;
    }

    .approve-btn:hover {
        background: #D1FADF;
        color: #027A48;
    }

    .leave-table-wrap {
        padding: 14px;
    }

    .leave-table-responsive {
        overflow-x: auto;
        border-radius: 18px;
        border: 1px solid var(--leave-border);
    }

    .leave-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--leave-text);
    }

    .leave-table thead th {
        background: #F9FAFB;
        color: #475467;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 950;
        padding: 14px;
        border-bottom: 1px solid var(--leave-border);
        white-space: nowrap;
    }

    .leave-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #F2F4F7;
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .leave-table tbody tr {
        transition: all .15s ease;
    }

    .leave-table tbody tr:hover {
        background: #FAFAFF;
    }

    .leave-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 200px;
    }

    .date-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 850;
        color: var(--leave-text);
    }

    .date-pill i {
        color: var(--leave-muted);
    }

    .earned-used {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 950;
    }

    .earned-days {
        color: #027A48;
    }

    .used-days {
        color: #B42318;
    }

    .leave-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .pill-available,
    .pill-earned {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .pill-used {
        background: var(--leave-soft);
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    .pill-expired {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .pill-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .empty-state {
        padding: 42px 18px;
        text-align: center;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .empty-state i {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--leave-soft);
        color: var(--leave-primary);
        font-size: 20px;
        margin-bottom: 12px;
    }

    @media(max-width:991px) {
        .leave-summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .pending-approval-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:767px) {
        .leave-summary-grid {
            grid-template-columns: 1fr;
        }

        .leave-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .leave-hero-title {
            font-size: 22px;
        }

        .leave-expire-btn {
            width: 100%;
        }

        .leave-card-head {
            padding: 16px;
        }

        .leave-action-wrap {
            width: 100%;
        }

        .leave-light-btn {
            flex: 1;
            justify-content: center;
        }

        .approval-card {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    .pill-reversal {
        background: #EFF8FF;
        color: #175CD3;
        border: 1px solid #B2DDFF;
    }

    /* Modern HRMS Tab Navigation Styles */
    .leave-tabs-nav-wrap {
        padding: 14px 24px 8px;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .leave-modern-tabs {
        display: flex;
        gap: 10px;
        border: none;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .leave-modern-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        height: 38px;
        padding: 0 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 750;
        color: var(--leave-primary, #4B00E8);
        background: #F4F2FF;
        border: 1px solid rgba(75, 0, 232, 0.12);
        transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        cursor: pointer;
    }

    .leave-modern-tabs .nav-link:hover {
        color: var(--leave-primary);
        background: #EDE9FE;
        border-color: rgba(75, 0, 232, .25);
    }

    .leave-modern-tabs .nav-link.active {
        color: #FFFFFF !important;
        background: linear-gradient(135deg, var(--leave-primary, #4B00E8), var(--leave-secondary, #8600EE)) !important;
        border-color: transparent !important;
        box-shadow: 0 4px 14px rgba(75, 0, 232, .28);
    }

    .leave-tab-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 999px;
        background: rgba(75, 0, 232, 0.1);
        color: var(--leave-primary, #4B00E8);
        transition: all .2s ease;
    }

    .leave-modern-tabs .nav-link.active .leave-tab-badge {
        background: rgba(255, 255, 255, .26);
        color: #FFFFFF;
    }

    .tx-amount-credit {
        color: #027A48;
        font-weight: 800;
        font-family: monospace;
        font-size: 13px;
    }

    .tx-amount-debit {
        color: #B42318;
        font-weight: 800;
        font-family: monospace;
        font-size: 13px;
    }

    .tx-reference-tag {
        display: inline-block;
        padding: 2px 7px;
        background: #F2F4F7;
        color: #344054;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        font-family: monospace;
    }

    .leave-table td.col-reason,
    .leave-table td.col-wrap {
        white-space: normal !important;
    }

    .reason-cell-wrap {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 220px;
        max-width: 360px;
        white-space: normal !important;
    }

    .co-reason-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 750;
        letter-spacing: .02em;
        width: fit-content;
    }

    .badge-manual {
        background: #F4F2FF;
        color: var(--leave-primary);
        border: 1px solid rgba(75, 0, 232, .18);
    }

    .badge-holiday {
        background: #EFF8FF;
        color: #0284C7;
        border: 1px solid #BAE6FD;
    }

    .badge-system {
        background: #F8FAFC;
        color: #475467;
        border: 1px solid #E2E8F0;
    }

    .reason-text-main {
        font-size: 13px;
        font-weight: 700;
        color: #1E293B;
        line-height: 1.45;
        word-break: break-word;
    }

    .reason-approver-meta {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11.5px;
        color: #64748B;
        font-weight: 600;
    }

    .reason-approver-meta i {
        color: #10B981;
        font-size: 11px;
    }

    /* ==========================================================================
       Leave Allocation Design System Integration
       ========================================================================== */

    .leave-view-tab {
        border: 1px solid var(--leave-border, #E7EAF3) !important;
        border-radius: 10px !important;
        padding: 9px 16px !important;
        color: var(--leave-text, #101828) !important;
        background: #fff !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        transition: all .2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .05) !important;
    }

    .leave-view-tab:hover,
    .leave-view-tab.active {
        color: #fff !important;
        border-color: transparent !important;
        background: linear-gradient(135deg, var(--leave-primary, #4B00E8), #e94069) !important;
        box-shadow: 0 7px 18px rgba(75, 0, 232, .22) !important;
    }

    .leave-view-tab .leave-tab-badge {
        font-size: 11px !important;
        font-weight: 800 !important;
        padding: 2px 7px !important;
        border-radius: 999px !important;
        background: rgba(75, 0, 232, 0.1) !important;
        color: var(--leave-primary, #4B00E8) !important;
        transition: all .2s ease !important;
    }

    .leave-view-tab:hover .leave-tab-badge,
    .leave-view-tab.active .leave-tab-badge {
        background: rgba(255, 255, 255, .26) !important;
        color: #FFFFFF !important;
    }

    /* ORB TABLE CARD - Shared Standard */
    .orb-table-card {
        background: #fff !important;
        border: 1px solid var(--leave-border, #E7EAF3) !important;
        border-radius: 24px !important;
        box-shadow: var(--leave-shadow, 0 14px 35px rgba(16, 24, 40, .07)) !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
    }

    .orb-table-head {
        padding: 18px 24px !important;
        border-bottom: 1px solid var(--leave-border, #E7EAF3) !important;
        background: #fff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        flex-wrap: wrap !important;
    }

    .orb-table-title-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    .orb-table-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 14px !important;
        background: var(--leave-soft, #F4F2FF) !important;
        color: var(--leave-primary, #4B00E8) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        flex-shrink: 0 !important;
    }

    .orb-table-title-wrap h3 {
        font-size: 17px !important;
        font-weight: 850 !important;
        color: var(--leave-text, #101828) !important;
        margin: 0 !important;
        letter-spacing: -0.02em !important;
    }

    .orb-table-title-wrap p {
        font-size: 12px !important;
        color: var(--leave-muted, #667085) !important;
        margin: 2px 0 0 0 !important;
    }

    /* Attached Filters Toolbar */
    .eo-filter-grid {
        display: grid !important;
        grid-template-columns: 2fr 1.2fr 1.2fr 1fr auto !important;
        gap: 12px !important;
        align-items: flex-end !important;
        padding: 14px 20px !important;
        background: #FAFAFB !important;
        border-bottom: 1px solid var(--leave-border, #E7EAF3) !important;
    }

    .eo-filter-grid.no-employee {
        grid-template-columns: 1.5fr 1.5fr 1fr auto !important;
    }

    @media (max-width: 1200px) {
        .eo-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 768px) {
        .eo-filter-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .eo-field {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
        min-width: 0 !important;
    }

    .eo-field label {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: var(--leave-muted, #667085) !important;
        margin: 0 !important;
        white-space: nowrap !important;
    }

    .eo-control {
        width: 100% !important;
        height: 38px !important;
        padding: 0 12px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        color: var(--leave-text, #101828) !important;
        background: #ffffff !important;
        border: 1.5px solid var(--leave-border, #E7EAF3) !important;
        border-radius: 10px !important;
        outline: none !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .eo-control:focus {
        border-color: var(--leave-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
    }

    .eo-field .select2-container {
        width: 100% !important;
    }

    .eo-field .select2-container .select2-selection--single {
        height: 38px !important;
        border-radius: 10px !important;
        border: 1.5px solid var(--leave-border, #E7EAF3) !important;
        display: flex !important;
        align-items: center !important;
        background: #FFFFFF !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .eo-field .select2-container--open .select2-selection--single,
    .eo-field .select2-container--focus .select2-selection--single {
        border-color: var(--leave-primary, #4B00E8) !important;
        box-shadow: 0 0 0 3px rgba(75, 0, 232, 0.12) !important;
    }

    .eo-field .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 12.5px !important;
        color: var(--leave-text, #101828) !important;
        font-weight: 600 !important;
        padding-left: 12px !important;
        padding-right: 24px !important;
    }

    .eo-field .select2-container .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    .eo-filter-actions-col {
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
    }

    .eo-filter-actions-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    #btnFilterSubmit {
        height: 38px !important;
        padding: 0 18px !important;
        font-size: 12.5px !important;
        font-weight: 800 !important;
        border-radius: 10px !important;
        background: linear-gradient(135deg, var(--leave-primary, #4B00E8), var(--leave-secondary, #8600EE)) !important;
        color: #ffffff !important;
        border: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.25) !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }

    #btnFilterSubmit:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(75, 0, 232, 0.35) !important;
    }

    #resetFilter {
        height: 38px !important;
        padding: 0 14px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        color: var(--leave-muted, #667085) !important;
        border: 1.5px solid var(--leave-border, #E7EAF3) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }

    #resetFilter:hover {
        background: #F4F5F7 !important;
        color: var(--leave-text, #101828) !important;
        border-color: #D0D5DD !important;
    }

    /* Employee & Avatar */
    .leave-employee {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-width: 200px !important;
    }

    .leave-avatar {
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, var(--leave-primary, #4B00E8), var(--leave-secondary, #8600EE)) !important;
        color: #fff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 900 !important;
        font-size: 13px !important;
        flex-shrink: 0 !important;
        overflow: hidden !important;
    }

    .leave-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .leave-employee-name {
        font-weight: 800 !important;
        color: var(--leave-text, #101828) !important;
        line-height: 1.25 !important;
        font-size: 13px !important;
    }

    .leave-employee-meta {
        font-size: 11px !important;
        color: var(--leave-muted, #667085) !important;
        margin-top: 2px !important;
        font-weight: 600 !important;
    }

    /* Metric & Breakdown Box */
    .leave-metric {
        font-size: 14px !important;
        font-weight: 900 !important;
        color: var(--leave-text, #101828) !important;
        line-height: 1.2 !important;
    }

    .leave-metric.text-success {
        color: #12B76A !important;
    }

    .leave-breakdown-box {
        font-size: 11px !important;
        color: var(--leave-muted, #667085) !important;
        margin-top: 4px !important;
        line-height: 1.4 !important;
        white-space: nowrap !important;
    }

    .leave-breakdown-item {
        display: inline-block !important;
        padding: 2px 6px !important;
        border-radius: 6px !important;
        background: #F8F9FA !important;
        border: 1px solid #E9ECEF !important;
        margin-right: 2px !important;
        margin-bottom: 2px !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
    }

    .leave-breakdown-item span {
        color: var(--leave-text, #101828) !important;
        font-weight: 900 !important;
    }

    .leave-btn {
        height: 42px;
        border-radius: 14px;
        border: none;
        background: linear-gradient(135deg, var(--leave-primary, #4B00E8), var(--leave-secondary, #8600EE));
        color: #fff !important;
        font-size: 13px;
        font-weight: 800;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all .2s ease;
        box-shadow: 0 4px 14px rgba(75, 0, 232, .25);
        white-space: nowrap;
        text-decoration: none !important;
    }

    .leave-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(75, 0, 232, .35);
        color: #fff !important;
    }

    .orb-table-footer {
        padding: 0 !important;
        border: 0 !important;
        background: #fff !important;
    }
</style>
