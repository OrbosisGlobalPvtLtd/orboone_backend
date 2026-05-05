<style>
    body {
        background: #f5f7fb;
    }

    .dash-page {
        padding: 22px;
        background: #f5f7fb;
    }

    .dash-wrap {
        max-width: 1450px;
        margin: 0 auto;
    }

    .dash-hero {
        background: linear-gradient(135deg, #4B00E8 0%, #8600EE 55%, #A855F7 100%);
        color: #fff;
        padding: 24px;
        border-radius: 18px;
        box-shadow: 0 15px 35px rgba(75, 0, 232, .22);
        overflow: hidden;
        position: relative;
    }

    .dash-hero:after {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        right: -80px;
        top: -90px;
        background: rgba(255, 255, 255, .12);
        border-radius: 50%;
    }

    .dash-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .dash-eyebrow {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        opacity: .85;
        margin-bottom: 6px;
    }

    .dash-title {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.2;
    }

    .dash-subtitle {
        margin: 8px 0 0;
        font-size: 14px;
        opacity: .9;
    }

    .dash-hero-metrics {
        display: flex;
        gap: 12px;
    }

    .dash-mini {
        min-width: 115px;
        padding: 14px 16px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .16);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, .22);
    }

    .dash-mini span {
        display: block;
        font-size: 12px;
        opacity: .85;
    }

    .dash-mini strong {
        display: block;
        margin-top: 5px;
        font-size: 17px;
        text-transform: capitalize;
    }

    .dash-alert {
        margin-top: 16px;
        padding: 13px 15px;
        border-radius: 14px;
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid #fed7aa;
        font-size: 14px;
    }

    .dash-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-top: 18px;
    }

    .dash-card,
    .dash-panel {
        background: #fff;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .07);
        border: 1px solid rgba(226, 232, 240, .8);
    }

    .dash-card {
        transition: .22s ease;
    }

    .dash-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, .1);
    }

    .dash-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .dash-card-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
    }

    .dash-card-value {
        margin-top: 5px;
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .dash-card-sub {
        margin-top: 12px;
        font-size: 12px;
        color: #64748b;
    }

    .dash-icon {
        width: 46px;
        height: 46px;
        border-radius: 15px;
        background: linear-gradient(135deg, #f1edff, #ede9fe);
        color: #4B00E8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }

    .dash-section {
        margin-top: 22px;
    }

    .dash-section-title {
        font-size: 18px;
        font-weight: 800;
        margin: 0 0 13px;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dash-section-title i {
        color: #4B00E8;
    }

    .dash-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .dash-action {
        background: #fff;
        border-radius: 18px;
        padding: 18px;
        text-decoration: none;
        color: #111827;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .07);
        border: 1px solid rgba(226, 232, 240, .8);
        transition: .22s ease;
    }

    .dash-action:hover {
        transform: translateY(-3px);
        color: #4B00E8;
        text-decoration: none;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .1);
    }

    .dash-action-icon {
        width: 44px;
        height: 44px;
        border-radius: 15px;
        background: linear-gradient(135deg, #4B00E8, #8600EE);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }

    .dash-action strong {
        display: block;
        font-size: 15px;
    }

    .dash-action span {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #64748b;
    }

    .dash-stat-list {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .dash-stat {
        padding: 15px;
        border-radius: 15px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
    }

    .dash-stat span {
        display: block;
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }

    .dash-stat strong {
        display: block;
        margin-top: 5px;
        font-size: 24px;
        color: #0f172a;
    }

    .dash-two {
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 18px;
    }

    .chart-panel {
        min-height: 390px;
    }

    .chart-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 15px;
    }

    .chart-head strong {
        display: block;
        font-size: 15px;
        color: #0f172a;
    }

    .chart-head span {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-top: 3px;
    }

    .chart-legend {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .chart-legend span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #475569;
        margin: 0;
    }

    .dot {
        width: 9px;
        height: 9px;
        display: inline-block;
        border-radius: 50%;
    }

    .dot.present {
        background: #4B00E8;
    }

    .dot.late {
        background: #f59e0b;
    }

    .chart-box {
        position: relative;
        height: 310px;
        width: 100%;
    }

    .chart-box canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .dash-bar-row {
        display: grid;
        grid-template-columns: 110px 1fr 45px;
        align-items: center;
        gap: 12px;
        margin: 14px 0;
    }

    .dash-bar-label {
        font-size: 13px;
        color: #475569;
        font-weight: 600;
        word-break: break-word;
    }

    .dash-bar-track {
        height: 10px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    .dash-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #4B00E8, #8600EE);
        border-radius: 999px;
    }

    .dash-bar-value {
        text-align: right;
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .dash-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dash-table th,
    .dash-table td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
        vertical-align: middle;
    }

    .dash-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 800;
    }

    .dash-table tr:hover td {
        background: #fafafa;
    }

    .dash-empty {
        color: #64748b;
        font-size: 14px;
        padding: 8px 0;
    }

    @media(max-width:1199px) {

        .dash-grid,
        .dash-actions {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media(max-width:991px) {

        .dash-grid,
        .dash-actions,
        .dash-stat-list {
            grid-template-columns: repeat(2, 1fr);
        }

        .dash-two {
            grid-template-columns: 1fr;
        }

        .dash-hero-inner {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media(max-width:575px) {
        .dash-page {
            padding: 12px;
        }

        .dash-hero {
            padding: 18px;
            border-radius: 16px;
        }

        .dash-title {
            font-size: 24px;
        }

        .dash-hero-metrics {
            width: 100%;
            flex-direction: column;
        }

        .dash-mini {
            width: 100%;
        }

        .dash-grid,
        .dash-actions,
        .dash-stat-list {
            grid-template-columns: 1fr;
        }

        .dash-card,
        .dash-panel,
        .dash-action {
            padding: 15px;
            border-radius: 16px;
        }

        .chart-head {
            flex-direction: column;
        }

        .chart-box {
            height: 280px;
        }

        .dash-bar-row {
            grid-template-columns: 80px 1fr 35px;
            gap: 8px;
        }

        .dash-table th,
        .dash-table td {
            font-size: 13px;
            padding: 10px 8px;
        }
    }
</style>
